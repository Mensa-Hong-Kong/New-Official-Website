<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreRequest;
use App\Http\Requests\Contact\UpdateRequest;
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

                    return $next($request);
                }
            ))->except('store'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $contact = $request->route('contact');
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
            (new Middleware(
                function (Request $request, Closure $next) {
                    $contact = $request->route('contact');
                    if (! $contact->isVerified()) {
                        abort(428, "The {$contact->type} is not verified, cannot set this contact to default, please verify first.");
                    }
                    if ($contact->is_default) {
                        abort(201, "The {$contact->type} has already been default.");
                    }

                    return $next($request);
                }
            ))->only('setDefault'),
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
            UserHasContact::where('is_default', true)
                ->where('contact', $contact->contact)
                ->where('type', $contact->type)
                ->whereNot('id', $contact->id)
                ->update(['is_default' => false]);
            ContactHasVerification::whereNull('expired_at')
                ->whereNotNull('verified_at')
                ->where('type', $contact->type)
                ->whereNot('contact_id', $contact->id)
                ->update(['expired_at' => now()]);
            $content = ['success' => "The {$contact->type} verifiy success."];
        }
        DB::commit();

        return response($content, isset($error) ? 422 : 200);
    }

    public function setDefault(UserHasContact $contact)
    {
        DB::beginTransaction();
        UserHasContact::where('type', $contact->type)
            ->where('user_id', $contact->user_id)
            ->update(['is_default' => false]);
        $contact->update(['is_default' => true]);
        DB::commit();

        return ['success' => "The {$contact->type} changed to default!"];
    }

    public function store(StoreRequest $request)
    {
        $contact = UserHasContact::create([
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'contact' => $request->contact,
        ]);

        return [
            'success' => "The {$contact->type} create success!",
            'id' => $contact->id,
            'type' => $contact->type,
            'contact' => $contact->contact,
            'send_verify_code_url' => route('contacts.send-verify-code', ['contact' => $contact]),
            'verify_url' => route('contacts.verify', ['contact' => $contact]),
            'set_default_url' => route('contacts.set-default', ['contact' => $contact]),
            'update_url' => route('contacts.update', ['contact' => $contact]),
            'delete_url' => route('contacts.destroy', ['contact' => $contact]),
        ];
    }

    public function update(UpdateRequest $request, UserHasContact $contact)
    {
        if ($request->{$contact->type} != $contact->contact) {
            DB::beginTransaction();
            $contact->update([
                'contact' => $request->{$contact->type},
                'is_default' => false,
            ]);
            if ($contact->isVerified()) {
                $contact->lastVerification()->update(['expired_at' => now()]);
            }
            DB::commit();
        }
        $type = ucfirst($contact->type);

        return [
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
            "default_{$contact->type}_id" => $request->user()->{"default$type"}->id ?? null,
            'is_verified' => $contact->refresh()->isVerified(),
        ];
    }

    public function destroy(UserHasContact $contact)
    {
        $contact->delete();

        return ['success' => "The {$contact->type} delete success!"];
    }
}
