<?php

namespace App\Http\Controllers;

use App\Models\UserHasContact;
use App\Http\Requests\Contact\VerifyRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Closure;

class ContactController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                function(Request $request, Closure $next) {
                    $user = $request->user();
                    $contact = $request->route('contact');
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
                    return $next($request);
                }, only: ['sendVerifyCode']
            ),
            new Middleware(
                function(Request $request, Closure $next) {
                    $user = $request->user();
                    $contact = $request->route('contact');
                    $error = '';
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
                    if($error == '') {
                        if($contact->isClosed()) {
                            $error = 'The verify code expired';
                        }
                        if($contact->lastVerification->isTriedTooManyTime()) {
                            $error = 'The verify code tried more than 5 times';
                        }
                        if($contact->isRequestTooManyTime()) {
                            $error .= ', this contact have sent 5 time verify code and each contact each day only can try 5 verify code, please again on tomorrow.';
                        } else {
                            $error .= ', the new verify code sent.';
                            $contact->sendVerifyCode();
                        }
                        return response([
                            'errors' => ['failed' => $error],
                        ], 422);
                    }
                    return $next($request);
                }, only: ['verify']
            ),
        ];
    }

    public function sendVerifyCode(Request $request, UserHasContact $contact)
    {
        $contact->sendVerifyCode();
        return ['message' => 'The verify code sent!'];
    }

    public function verify(VerifyRequest $request, UserHasContact $contact)
    {
        if($contact->code != strtoupper($request->code)) {
            $contact->lastVerification->increment('tried_time');
            $error = 'The verify code is incorrect';
            if($contact->lastVerification->isTriedTooManyTime()) {
                $error .= ', the verify code tried 5 time';
                if($contact->isRequestTooManyTime()) {
                    $error .= ', this contact have sent 5 time verify code and each contact each day only can try 5 verify code, please again on tomorrow';
                } else {
                    $error .= ', the new verify code sent';
                    $contact->sendVerifyCode();
                }
                return response([
                    'errors' => ['failed' => "$error."],
                ], 422);
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
