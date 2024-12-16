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

    public function setUp(): void
    {
        parent::setup();
    }

    public function testHaveNoVerifiyCode()
    {
        $this->assertEquals(0, ContactHasVerification::count());
        new ClearUnusedVerifiyCode;
    }

    public function testHasVerifiyCodeAndHaveNoUnusedVerifiyCode()
    {
        User::factory()->create();
        Queue::fake();
        Notification::fake();
        UserHasContact::factory()->create();
        $this->assertEquals(
            0, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '<=' .now())
                ->count()
        );
        $this->assertEquals(
            1, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '>=', now())
                ->count()
        );
        new ClearUnusedVerifiyCode;
        $this->assertEquals(
            0, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '<=' .now())
                ->count()
        );
        $this->assertEquals(
            1, ContactHasVerification::whereNull('verified_at')
                ->where('closed_at', '>=', now())
                ->count()
        );
    }

    public function testHasUnusedVerifiyCodeAndHaveNoVerifiyCode()
    {
        User::factory()->create();
        Queue::fake();
        Notification::fake();
        $contact = UserHasContact::factory()->create();
        $contact->lastVerification
            ->update(['closed_at' => now()->subDay()->subSecond()]);
        (new ClearUnusedVerifiyCode)();
        $this->assertEquals(0, ContactHasVerification::count());
    }

    public function testHasUnusedVerifiyCodeAndHasNoVerifiyCode()
    {
        User::factory()->create();
        Queue::fake();
        Notification::fake();
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        $contact->lastVerification
            ->update(['closed_at' => now()->subDay()->subSecond()]);
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
