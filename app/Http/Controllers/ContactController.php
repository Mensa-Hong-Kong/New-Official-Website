<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\VerifyRequest;
use App\Models\ContactHasVerification;
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
            (new Middleware(
                function (Request $request, Closure $next) {
                    $user = $request->user();
                    $contact = $request->route('contact');
                    if ($contact->user_id != $user->id) {
                        abort(403);
                    }
                    if ($contact->isVerified()) {
                        abort($request->isMethod('post') ? 201 : 410, "The {$contact->type} verified.");
                    }

                    return $next($request);
                }
            ))->only(['sendVerifyCode', 'verify']),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $contact = $request->route('contact');
                    if ($contact->isRequestTooFast()) {
                        abort(429, 'For each contact each minute only can get 1 time verify code, please try again later.');
                    }
                    if ($contact->isRequestTooManyTime()) {
                        abort(429, "For each {$contact->type} each day only can send 5 verify code, please try again on tomorrow or contact us to verify by manual.");
                    }
                    if ($request->user()->isRequestTooManyTimeVerifyCode($contact->type)) {
                        abort(429, "For each user each day only can send 5 {$contact->type} verify code, please try again on tomorrow or contact us to verify by manual.");
                    }

                    return $next($request);
                }
            ))->only('sendVerifyCode'),
            (new Middleware(
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
                            abort(429, "$error, include other user(s), this {$contact->type} have sent 5 times verify code and each {$contact->type} each day only can send 5 verify code, please try again on tomorrow or contact us to verify by manual.");
                        } elseif ($request->user()->isRequestTooManyTimeVerifyCode($contact->type)) {
                            abort(429, "$error, your account have sent 5 {$contact->type} verify code and each user each day only can send 5 {$contact->type} verify code, please try again on tomorrow or contact us to verify by manual.");
                        } else {
                            $error .= ', the new verify code sent.';
                            $contact->sendVerifyCode();

                            return response([
                                'errors' => ['code' => $error],
                            ], 422);
                        }
                    }

                    return $next($request);
                }
            ))->only('verify'),
        ];
    }

    public function sendVerifyCode(Request $request, UserHasContact $contact)
    {
        $contact->sendVerifyCode();

        return ['success' => 'The verify code sent!'];
    }

    public function verify(VerifyRequest $request, UserHasContact $contact)
    {
        DB::beginTransaction();
        if ($contact->lastVerification->code != strtoupper($request->code)) {
            $isFailedTooMany = false;
            $contact->lastVerification->increment('tried_time');
            $error = 'The verify code is incorrect';
            if ($contact->lastVerification->isTriedTooManyTime()) {
                $error .= ', the verify code tried 5 time';
                if ($contact->isRequestTooManyTime()) {
                    $error .= ", include other user(s), this {$contact->type} have sent 5 times verify code and each {$contact->type} each day only can send 5 verify code, please try again on tomorrow or contact us to verify by manual";
                    $isFailedTooMany = true;
                } elseif ($request->user()->isRequestTooManyTimeVerifyCode($contact->type)) {
                    $error .= ", your account have sent 5 {$contact->type} verify code and each user each day only can send 5 {$contact->type} verify code, please try again on tomorrow or contact us to verify by manual";
                    $isFailedTooMany = true;
                } else {
                    $error .= ', the new verify code sent';
                    $contact->sendVerifyCode();
                }
            }
            $content = ['errors' => [
                'code' => "$error.",
                'isFailedTooMany' => $isFailedTooMany,
            ]];
        } else {
            $contact->lastVerification->update(['verified_at' => now()]);
            if (is_null($contact->user->{'default'.ucfirst($contact->type)})) {
                $contact->update(['is_default' => true]);
                UserHasContact::where('type', $contact->tyoe)
                    ->where('id', '!=', $contact->id)
                    ->where('user_id', $contact->user_id)
                    ->update(['is_default' => false]);
                $contactIDs = ContactHasVerification::whereNull('expired_at')
                    ->where('type', $contact->type)
                    ->where('creator_id', '!=', $contact->user_id)
                    ->whereHas(
                        'contact', function ($query) use ($contact) {
                            $query->where('contact', $contact->contact);
                        }
                    )
                    ->get('contact_id')
                    ->pluck('contact_id')
                    ->toArray();
                if (count($contactIDs)) {
                    UserHasContact::whereIn('id', $contactIDs)
                        ->update(['is_default' => false]);
                    ContactHasVerification::whereNull('expired_at')
                        ->whereIn('contact_id', $contactIDs)
                        ->update(['expired_at' => now()]);
                }
            }
            $content = ['success' => "The {$contact->type} verifiy success."];
        }
        DB::commit();

        return response($content, isset($error) ? 422 : 200);
    }
}
