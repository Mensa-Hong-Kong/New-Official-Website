<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\StoreRequest;
use App\Http\Requests\Admin\Contact\UpdateRequest;
use App\Http\Requests\StatusRequest;
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

    private function verified(UserHasContact $contact)
    {
        $request = request();
        $verification = ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => $request->user()->id,
            'creator_ip' => $request->ip(),
            'middleware_should_count' => false,
        ]);
        $verifications = ContactHasVerification::where('type', $contact->type)
            ->where('contact', $contact->contact)
            ->whereNot('id', $verification->id)
            ->get(['id', 'contact_id']);
        if (count($verifications)) {
            UserHasContact::whereIn('id', $verifications->pluck('contact_id')->toArray())
                ->update(['is_default' => false]);
            if ($contact->type == 'email') {
                User::whereHas(
                    'contacts', function ($query) use ($verifications) {
                        $query->whereIn('id', $verifications->pluck('contact_id')->toArray());
                    }
                )->update(['synced_to_stripe' => false]);
            }
            ContactHasVerification::whereIn('id', $verifications->pluck('id')->toArray())
                ->update(['expired_at' => now()]);
        }
    }

    public function verify(StatusRequest $request, UserHasContact $contact)
    {
        if ($request->status != $contact->isVerified) {
            DB::beginTransaction();
            if ($request->status) {
                $this->verified($contact);
            } else {
                $contact->lastVerification()->update(['expired_at' => now()]);
                if ($contact->is_default) {
                    $contact->update(['is_default' => false]);
                }
            }
            DB::commit();
        }

        return [
            'success' => "The {$contact->type} verify status update success!",
            'status' => $contact->refresh()->isVerified,
        ];
    }

    public function default(StatusRequest $request, UserHasContact $contact)
    {
        if ($request->status != $contact->is_default) {
            DB::beginTransaction();
            $contact->update(['is_default' => $request->status]);
            if ($request->status && ! $contact->isVerified) {
                $this->verified($contact);
            }
            DB::commit();
        }

        return [
            'success' => "The {$contact->type} default status update success!",
            'status' => $contact->is_default,
        ];
    }

    public function store(StoreRequest $request)
    {
        $contact = UserHasContact::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'contact' => $request->contact,
            'is_default' => $request->is_default ?? false,
        ]);
        if (($request->is_verified ?? false) || ($contact->is_default ?? false)) {
            $this->verified($contact);
        }

        return [
            'success' => "The {$contact->type} create success!",
            'id' => $contact->id,
            'type' => $contact->type,
            'contact' => $contact->contact,
            'is_default' => $contact->is_default,
            'is_verified' => $contact->isVerified,
        ];
    }

    public function update(UpdateRequest $request, UserHasContact $contact)
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

        if ($return['is_verified'] != $contact->isVerified) {
            if ($return['is_verified']) {
                $this->verified($contact);
            } else {
                $contact->lastVerification()->update(['expired_at' => now()]);
            }
        }
        DB::commit();

        return $return;
    }

    public function destroy(UserHasContact $contact)
    {
        $contact->delete();

        return ['success' => "The {$contact->type} delete success!"];
    }
}
