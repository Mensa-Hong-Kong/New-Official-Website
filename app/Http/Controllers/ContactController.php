<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\VerifyRequest;
use App\Models\UserHasContact;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                function (Request $request, Closure $next) {
                    $user = $request->user();
                    $contact = $request->route('contact');
                    if ($contact->user_id != $user->id) {
                        abort(403);
                    }
                    if ($contact->isVerified()) {
                        abort(410, "The {$contact->type} verified.");
                    }

                    return $next($request);
                }, only: ['sendVerifyCode', 'verify']
            ),
            new Middleware(
                function (Request $request, Closure $next) {
                    $contact = $request->route('contact');
                    if ($contact->isRequestTooFast()) {
                        abort(429, 'For each user and ip each minute only can get 1 time verify code, please again later.');
                    }
                    if ($contact->isRequestTooManyTime()) {
                        abort(429, "For each user and ip each day only can send 5 {$contact->type} verify code, please again on tomorrow.");
                    }

                    return $next($request);
                }, only: ['sendVerifyCode']
            ),
            new Middleware(
                function (Request $request, Closure $next) {
                    $contact = $request->route('contact');
                    if (! $contact->lastVerification) {
                        $contact->sendVerifyCode();
                        abort(404, 'The verify request record is not found, the new verify code sent.');
                    }
                    $error = '';
                    if ($contact->lastVerification->isClosed()) {
                        $error = 'The verify code expired';
                    }
                    if ($contact->lastVerification->isTriedTooManyTime()) {
                        $error = 'The verify code tried more than 5 times';
                    }
                    if ($error != '') {
                        if ($contact->isRequestTooManyTime()) {
                            $error .= ", this contact have sent 5 time verify code and each user and ip each day only can try 5 {$contact->type} verify code, please again on tomorrow.";
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

        return ['success' => 'The verify code sent!'];
    }

    public function verify(VerifyRequest $request, UserHasContact $contact)
    {
        $error = '';
        DB::beginTransaction();
        if ($contact->lastVerification->code != strtoupper($request->code)) {
            $contact->lastVerification->increment('tried_time');
            $error = 'The verify code is incorrect';
            if ($contact->lastVerification->isTriedTooManyTime()) {
                $error .= ', the verify code tried 5 time';
                if ($contact->isRequestTooManyTime($request->ip())) {
                    $error .= ', this contact have sent 5 time verify code and each user and ip each day only can try 5 verify code, please again on tomorrow';
                } else {
                    $error .= ', the new verify code sent';
                    $contact->sendVerifyCode();
                }
            }
            $content = ['errors' => ['failed' => "$error."]];
        } else {
            $contact->lastVerification->update(['verified_at' => now()]);
            $content = ['success' => "The {$contact->type} verifiy success."];
        }
        DB::commit();

        return response($content, $error == '' ? 200 : 422);
    }
}
