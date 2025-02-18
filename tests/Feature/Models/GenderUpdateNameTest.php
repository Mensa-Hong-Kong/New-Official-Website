<?php

namespace Tests\Feature\Models;

use App\Models\Gender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenderUpdateNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_have_no_change()
    {
        $gender = Gender::inRandomOrder()->first();
        $id = $gender->id;
        User::factory()->state(['gender_id' => $id])->create();
        $name = $gender->name;
        $gender = $gender->updateName($name);
        $this->assertEquals($id, $gender->id);
        $this->assertEquals($name, $gender->name);
    }

    public function test_name_change_and_only_has_one_user_using_and_have_no_other_row_has_same_name()
    {
        $gender = Gender::inRandomOrder()->first();
        $id = $gender->id;
        User::factory()->state(['gender_id' => $id])->create();
        $gender = $gender->updateName('abc');
        $this->assertEquals($id, $gender->id);
        $this->assertEquals('abc', $gender->name);
    }

    public function test_name_change_and_only_has_one_user_using_and_has_other_row_has_same_name()
    {
        $gender = Gender::inRandomOrder()->first();
        $id = $gender->id;
        User::factory()->state(['gender_id' => $id])->create();
        $otherGender = Gender::inRandomOrder()->whereNot('name', $gender->name)->first();
        $gender = $gender->updateName($otherGender->name);
        $this->assertEquals($otherGender->id, $gender->id);
        $this->assertEquals($otherGender->name, $gender->name);
        $this->assertFalse(Gender::where('id', $id)->exists());
    }

    public function test_name_change_and_has_more_than_one_user_using_and_have_no_other_row_has_same_name()
    {
        $gender = Gender::inRandomOrder()->first();
        $id = $gender->id;
        User::factory()->state(['gender_id' => $id])->create();
        User::factory()->state(['gender_id' => $id])->create();
        $gender = $gender->updateName('abc');
        $this->assertNotEquals($id, $gender->id);
        $this->assertEquals('abc', $gender->name);
        $this->assertTrue(Gender::where('id', $id)->exists());
    }

    public function test_name_change_and_has_more_than_one_user_using_and_has_other_row_has_same_name()
    {
        $gender = Gender::inRandomOrder()->first();
        $id = $gender->id;
        User::factory()->state(['gender_id' => $id])->create();
        User::factory()->state(['gender_id' => $id])->create();
        $otherGender = Gender::inRandomOrder()->whereNot('name', $gender->name)->first();
        $gender = $gender->updateName($otherGender->name);
        $this->assertEquals($otherGender->id, $gender->id);
        $this->assertEquals($otherGender->name, $gender->name);
        $this->assertTrue(Gender::where('id', $id)->exists());
    }
}
