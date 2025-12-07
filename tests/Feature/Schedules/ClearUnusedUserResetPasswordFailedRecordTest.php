<?php

namespace Tests\Feature\Schedules;

use App\Models\ResetPasswordLog;
use App\Models\User;
use App\Models\UserHasContact;
use App\Schedules\ClearUnusedUserResetPasswordFailedRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearUnusedUserResetPasswordFailedRecordTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $contact;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->contact = UserHasContact::factory()->create();
    }

    public function test_have_no_verify_record()
    {
        $this->assertEquals(0, ResetPasswordLog::count());
        (new ClearUnusedUserResetPasswordFailedRecord)();
    }

    private function create_admin_reset_password_record_within_pass_24_hours()
    {
        ResetPasswordLog::create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
            'contact_type' => $this->contact->type,
            'user_id' => $this->user->id,
            'creator_id' => $this->user->id,
            'creator_ip' => fake()->ipv4(),
            'middleware_should_count' => false,
        ]);
    }

    private function create_success_user_reset_password_record_within_pass_24_hours()
    {
        ResetPasswordLog::create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
            'contact_type' => $this->contact->type,
            'user_id' => $this->user->id,
            'creator_id' => $this->user->id,
            'creator_ip' => fake()->ipv4(),
        ]);
    }

    private function create_failed_user_reset_password_record_within_pass_24_hours()
    {
        ResetPasswordLog::create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
            'contact_type' => $this->contact->type,
            'creator_ip' => fake()->ipv4(),
        ]);
    }

    private function create_admin_reset_password_record_without_pass_24_hours()
    {
        $log = new ResetPasswordLog;
        $log->timestamps = false;
        $log->passport_type_id = $this->user->passport_type_id;
        $log->passport_number = $this->user->passport_number;
        $log->contact_type = $this->contact->type;
        $log->user_id = $this->user->id;
        $log->creator_id = $this->user->id;
        $log->creator_ip = fake()->ipv4();
        $log->middleware_should_count = false;
        $log->created_at = now()->subDay()->subSecond();
        $log->save();
    }

    private function create_success_user_reset_password_record_without_pass_24_hours()
    {
        $log = new ResetPasswordLog;
        $log->timestamps = false;
        $log->passport_type_id = $this->user->passport_type_id;
        $log->passport_number = $this->user->passport_number;
        $log->contact_type = $this->contact->type;
        $log->user_id = $this->user->id;
        $log->creator_id = $this->user->id;
        $log->creator_ip = fake()->ipv4();
        $log->created_at = now()->subDay()->subSecond();
        $log->save();
    }

    private function create_failed_user_reset_password_record_without_pass_24_hours()
    {
        $log = new ResetPasswordLog;
        $log->timestamps = false;
        $log->passport_type_id = $this->user->passport_type_id;
        $log->passport_number = $this->user->passport_number;
        $log->contact_type = $this->contact->type;
        $log->creator_ip = fake()->ipv4();
        $log->created_at = now()->subDay()->subSecond();
        $log->save();
    }

    public function test_only_has_admin_reset_password_record_within_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_has_success_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_has_failed_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_has_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_has_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_has_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(0, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_success_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_within_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_within_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_within_pass_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_have_failed_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_failed_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_failed_user_reset_password_record_within_pass_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(1, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_success_user_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_success_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_success_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_success_user_reset_password_record_within_pass_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_success_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_failed_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_failed_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_failed_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_admin_reset_password_record_and_success_user_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(2, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_and_success_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_and_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_within_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_within_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_success_user_reset_password_record_and_failed_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_success_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_success_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_success_user_reset_password_record_within_pass_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_failed_user_reset_password_record_within_pass_24_hours_and_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_failed_user_reset_password_record_within_pass_24_hours_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_failed_user_reset_password_record_within_pass_24_hours_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_and_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(3, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_success_user_reset_password_record_and_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_within_pass_24_hours()
    {
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_success_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_failed_user_reset_password_record_within_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_admin_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_success_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(4, ResetPasswordLog::count());
    }

    public function test_only_have_no_failed_user_reset_password_record_without_pass_24_hours()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(5, ResetPasswordLog::count());
    }

    public function test_have_all()
    {
        $this->create_admin_reset_password_record_within_pass_24_hours();
        $this->create_success_user_reset_password_record_within_pass_24_hours();
        $this->create_failed_user_reset_password_record_within_pass_24_hours();
        $this->create_admin_reset_password_record_without_pass_24_hours();
        $this->create_success_user_reset_password_record_without_pass_24_hours();
        $this->create_failed_user_reset_password_record_without_pass_24_hours();
        (new ClearUnusedUserResetPasswordFailedRecord)();
        $this->assertEquals(5, ResetPasswordLog::count());
    }
}
