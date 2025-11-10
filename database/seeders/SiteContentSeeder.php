<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use App\Models\SitePage;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the site_contents table and site_pages table with predefined data.
 * 
 * The 'site_contents' table will contain:
 * | id  | page_id | name   | content | created_at | updated_at |
 * | --- | ------- | ------ | ------- | ---------- | ---------- |
 * | 1   | 1       | Info   | ...     | ...        | ...        |
 * | 2   | 1       | Remind | ...     | ...        | ...        |
 * 
 * The 'site_pages' table will contain:
 * | id  | name            | created_at | updated_at |
 * | --- | --------------- | ---------- | ---------- |
 * | 1   | Admission Test  | ...        | ...        |
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $page = SitePage::firstOrCreate(['name' => 'Admission Test']);
        $content = SiteContent::firstOrCreate([
            'page_id' => $page->id,
            'name' => 'Info',
        ]);
        $content->update(['content' => '<p style="text-align:center;"><span style="color:hsl(240,75%,60%);">To qualify for membership, applicants must score in the top 2% of the population on the admission test.</span><br><span style="color:hsl(240,75%,60%);">There is no other basis for membership, whoever you are, whatever you do.</span><br><br><span style="color:hsl(240,75%,60%);">No preparation is needed for the test, but you must be aged 14 or over to take the test and residing in Hong Kong.</span><br><span style="color:hsl(240,75%,60%);">If you are under 14 and have already taken a qualified IQ test, you may apply for membership using prior evidence.</span><br><br><br><span style="color:hsl(240,75%,60%);">For enquiries regarding admission test, please contact us at </span><a href="mailto:test@mensa.org.hk"><span style="color:hsl(240,75%,60%);">test@mensa.org.hk</span></a><span style="color:hsl(240,75%,60%);">.</span><br><span style="color:hsl(240,75%,60%);">For enquiries regarding application for admission via prior evidence (for those under 14 years old), please contact us at </span><a href="mailto:admission@mensa.org.hk"><span style="color:hsl(240,75%,60%);">admission@mensa.org.hk</span></a><span style="color:hsl(240,75%,60%);">.</span></p><p style="text-align:center;"><span style="color:hsl(0,75%,60%);">Note: please read the rules below before registering for the test</span></p>']);
        $content = SiteContent::firstOrCreate([
            'page_id' => $page->id,
            'name' => 'Remind',
        ]);
        $content->update(['content' => '<p>Notes</p><ul><li>Admission test fee: $300</li><li>Reservation is required three days (72 hours) before the test date, and admission is on a first-come-first-served basis. No walk-ins are allowed.</li><li>Each candidate is permitted to reschedule his/her test date up to TWO times. There will be an administrative charge of HK$200 for each subsequent reschedulling of test date.</li><li>No-show without a valid reason and evidence will incur an administrative charge of HK$200 for each subsequent rescheduling of the test date.</li><li>All tests take place in Central. You will receive a confirmation email with the exact venue of the test only after you have (1) paid for the test AND (2) submitted your personal information.</li><li>All admission tests are on Saturdays at 2:30 p.m. HK time (save for extra sessions during summer months, which will be at 3:30 p.m.). The time and date of the admission tests will appear differently if you are viewing this at a different time zone.</li><li>Candidates should arrive 20 minutes before the test session. Latecomers may be denied entry.</li><li>Please bring your own (1) pencil, (2) ticket QR code, and your (3) Hong Kong/Macau/(Mainland) Resident ID card. Other identity documentation will NOT be accepted unless special permission is obtained beforehand.</li><li>If you are qualified to join Mensa and are a Hong Kong resident, the membership dues are HK$400 per annum ($200 if you are under 21 when fees become due).</li><li>If you are qualified but are NOT a Hong Kong resident, you will be regstered as the direct member of mensa International.</li><li>If you do not qualify for membership for the first time, you are entitled to re-take the test free of charge once at any time between 6 months and 18 months after the date of your test, retaking the test within 6 months of the first attempt is strictly prohibited under any circumstances.</li><li>A person is subject to a maximum of 2 attempts in the admission test of Hong Kong Mensa in a lifetime. Any further attempts shall be disqualified immediately with no refund of the test fee.</li><li>You will be notified when the result is out. Since this test only serves as an entry test for Hong Kong Mensa, it should NOT be taken as a comprehensive IQ test. The exact score will not be disclosed. You will only be informed about whether you are qualified for membership.</li><li>There will be no refund of any paid test fees.</li></ul><p style="text-align:center;"><span style="color:hsl(0, 75%, 60%);">Mensa Hong Kong reserves the right to update the rules without prior notice. Any changes will take effect immediately upon posting on this website. Mensa Hong Kong reserves the right to the final interpretation of the admission test rules.</span></p>']);
    }
}
