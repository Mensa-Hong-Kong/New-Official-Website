<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use App\Models\SitePage;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $page = SitePage::firstOrCreate(['name' => 'Admission Test']);
        $content = SiteContent::firstOrCreate([
            'page_id' => $page->id,
            'name' => 'Info',
        ]);
        $content = SiteContent::firstOrCreate([
            'page_id' => $page->id,
            'name' => 'Remind',
        ]);
    }
}
