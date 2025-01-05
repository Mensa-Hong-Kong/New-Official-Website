<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\UpdateRequest;
use App\Models\ContactHasVerification;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:Edit:User')];
    }

    public function update(UpdateRequest $request, User $user, UserHasContact $contact)
    {
        DB::beginTransaction();
        $contact->update([
            'contact' => $request->{$contact->type},
            'is_default' => $request->is_default ?? false,
        ]);
        $return = [
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
            'is_verified' => $contact->is_default ? true : $request->is_verified ?? false,
            'is_default' => $contact->is_default,
        ];

        if ($return['is_verified'] != $contact->isVerified()) {
            if ($return['is_verified']) {
                $verification = ContactHasVerification::create([
                    'contact_id' => $contact->id,
                    'contact' => $contact->contact,
                    'type' => $contact->type,
                    'verified_at' => now(),
                    'creator_id' => $request->user()->id,
                    'creator_ip' => $request->ip(),
                    'middleware_should_count' => false,
                ]);
                ContactHasVerification::where('type', $contact->tpye)
                    ->where('contact', $contact->contact)
                    ->whereNot('id', $verification->id)
                    ->update(['expired_at' => now()]);
            } else {
                $contact->lastVerification()->update(['expired_at' => now()]);
            }
        }
        DB::commit();

        return $return;
    }
}
