<?php

namespace Tests\Feature\Contact;

use App\Models\ContactHasVerification;
use App\Models\User;
use App\Models\UserHasContact;
use App\Schedules\ClearUnusedVerifiyCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ClearUnusedVerifiyCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setup();
    }

    public function test_have_no_verifiy_code()
    {
        $this->assertEquals(0, ContactHasVerification::count());
        new ClearUnusedVerifiyCode;
    }

    public function test_has_verifiy_code_and_have_no_unused_verifiy_code()
    {
        User::factory()->create();
        Queue::fake();
        Notification::fake();
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        $this->assertEquals(
            0, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '<='.now())
                ->count()
        );
        $this->assertEquals(
            1, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '>=', now())
                ->count()
        );
        new ClearUnusedVerifiyCode;
        $this->assertEquals(
            1, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '>=', now())
                ->count()
        );
    }

    public function test_has_unused_verifiy_code_and_have_no_verifiy_code()
    {
        User::factory()->create();
        Queue::fake();
        Notification::fake();
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        $contact->lastVerification
            ->update(['closed_at' => now()->subMonths(3)->subSecond()]);
        (new ClearUnusedVerifiyCode)();
        $this->assertEquals(0, ContactHasVerification::count());
    }

    public function test_has_unused_verifiy_code_and_has_no_verifiy_code()
    {
        User::factory()->create();
        Queue::fake();
        Notification::fake();
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        $contact->sendVerifyCode();
        $contact->lastVerification
            ->update(['closed_at' => now()->subMonths(3)->subSecond()]);
        (new ClearUnusedVerifiyCode)();
        $this->assertEquals(
            0, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '<', now()->subDay())
                ->count()
        );
        $this->assertEquals(
            1, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '>=', now()->subDay())
                ->count()
        );
    }
}
