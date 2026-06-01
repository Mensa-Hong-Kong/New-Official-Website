<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\StoreRequest;
use App\Http\Requests\Admin\Contact\UpdateRequest;
use App\Http\Requests\StatusRequest;
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

    public function verify(StatusRequest $request, UserHasContact $contact)
    {
        if ($request->status != $contact->isVerified) {
            DB::beginTransaction();
            if ($request->status) {
                $contact->verified();
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
            $contact->update(['is_default' => (bool) $request->status]);
            if ($request->status && ! $contact->isVerified) {
                $contact->verified();
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
            $contact->verified();
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
                $contact->verified();
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
