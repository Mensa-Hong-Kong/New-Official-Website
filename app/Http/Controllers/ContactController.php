<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\VerifyContactRequest;
use App\Models\UserHasContact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function sendVerifyCode(Request $request, UserHasContact $contact)
    {
        $user = $request->user();
        if($contact->user_id != $user->id) {
            abort(403);
        }
        if($contact->isRequestTooFast()) {
            return response([
                'errors' => ['failed' => 'For each contact each minute only can get 1 time verify code, please again later.'],
            ], 429);
        }
        if($contact->isRequestTooManyTime()) {
            return response([
                'errors' => ['failed' => 'For each contact each day only can send 5 verify code, please again on tomorrow.'],
            ], 429);
        }
        $contact->sendVerifyCode();
        return ['message' => 'The verify code sent!'];
    }

    private function failResponseByFrequentCheck($contact, $message)
    {
        if($contact->isRequestTooManyTime()) {
            return response([
                'errors' => ['failed' => "$message, this contact have sent 5 time verify code and each contact each day only can try 5 verify code, please again on tomorrow."],
            ], 422);
        }
        $contact->sendVerifyCode();
        return response([
            'errors' => ['failed' => "$message, the new verify code sent."],
        ], 422);
    }

    public function verify(VerifyContactRequest $request, UserHasContact $contact)
    {
        if(! $contact->lastVerification) {
            $contact->sendVerifyCode();
            return response([
                'errors' => ['failed' => 'The verify request record is not found, the new verify code sent.'],
            ], 422);
        }
        if($contact->isVerified()) {
            return response([
                'success' => ['message' => 'The email verified.'],
            ], 200);
        }
        if($contact->isClosed()) {
            return $this->failResponseByFrequentCheck($contact, 'The verify code expired');
        }
        if($contact->lastVerification->isTriedTooManyTime()) {
            return $this->failResponseByFrequentCheck($contact, 'The verify code tried more than 5 times');
        }
        if($contact->code != strtoupper($request->code)) {
            $contact->lastVerification->increment('tried_time');
            if($contact->lastVerification->isTriedTooManyTime()) {
                return $this->failResponseByFrequentCheck($contact, 'The verify code is incorrect, the verify code tried 5 time');
            }
            return response([
                'errors' => ['failed' => 'The verify code is incorrect.'],
            ], 422);
        }
        $contact->lastVerification->update(['verified_at' => now()]);
        return response([
            'success' => ['message' => 'The email verifiy success.'],
        ], 200);
    }
}
