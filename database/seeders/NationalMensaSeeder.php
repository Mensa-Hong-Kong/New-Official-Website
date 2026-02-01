<?php

namespace Database\Seeders;

use App\Models\NationalMensa;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the modules table with predefined data as follows. The script update the tables (e.g., modules, permissions, module_permission)
 *
 * The 'permissions' table will contain:
 * | id  | name                     | url                                   | is_active | created_at | updated_at |
 * | --- | ------------------------ | ------------------------------------- | --------- | ---------- | ---------- |
 * | 1   | Argentina                | http://www.mensa.org.ar               | 1         |            |            |
 * | 2   | Australia                | https://www.mensa.org.au              | 1         |            |            |
 * | 3   | Austria                  | https://www.mensa.at                  | 1         |            |            |
 * | 4   | Belgium                  | https://www.mensa.be/en               | 1         |            |            |
 * | 5   | Bosnia and Herzegovina   | https://mensa.ba                      | 1         |            |            |
 * | 6   | Brazil                   | https://mensa.org.br                  | 1         |            |            |
 * | 7   | Bulgaria                 | https://mensa.bg                      | 1         |            |            |
 * | 8   | Canada                   | https://mensa.ca                      | 1         |            |            |
 * | 9   | Croatia                  | https://mensa.hr                      | 1         |            |            |
 * | 10  | Cyprus                   | http://www.mensa.org.cy               | 1         |            |            |
 * | 11  | Czech Republic           | https://mensa.cz                      | 1         |            |            |
 * | 12  | Denmark                  | https://mensa.dk                      | 1         |            |            |
 * | 13  | Finland                  | https://www.mensa.fi                  | 1         |            |            |
 * | 14  | France                   | https://mensa-france.net              | 1         |            |            |
 * | 15  | Germany                  | https://www.mensa.de                  | 1         |            |            |
 * | 16  | Greece                   | https://www.mensa.org.gr              | 1         |            |            |
 * | 17  | Hungary                  | https://mensa.hu                      | 1         |            |            |
 * | 18  | India                    | https://mensaindia.org/new/index.php  | 1         |            |            |
 * | 19  | Indonesia                | https://mensa.id                      | 1         |            |            |
 * | 20  | Italy                    | https://www.mensa.it                  | 1         |            |            |
 * | 21  | Japan                    | https://www.mensa.jp                  | 1         |            |            |
 * | 22  | Luxembourg               | https://www.mensa.lu/lb               | 1         |            |            |
 * | 23  | Malaysia                 | https://mensa.my                      | 1         |            |            |
 * | 24  | Mexico                   | https://mensa.org.mx                  | 1         |            |            |
 * | 25  | Montenegro               | https://mensa.me                      | 1         |            |            |
 * | 26  | Netherlands              | https://www.mensa.nl                  | 1         |            |            |
 * | 27  | New Zealand              | https://mensa.org.nz/home-of-mensa-nz | 1         |            |            |
 * | 28  | North Macedonia          | https://www.mensa.org.mk              | 1         |            |            |
 * | 29  | Norway                   | https://www.mensa.no                  | 1         |            |            |
 * | 30  | Pakistan                 | https://mensa.pk                      | 0         |            |            |
 * | 31  | Peru                     | http://mensa.pe                       | 1         |            |            |
 * | 32  | Philippines              | https://mensaphilippines.org          | 1         |            |            |
 * | 33  | Poland                   | https://www.mensa.org.pl              | 1         |            |            |
 * | 34  | Romania                  | https://mensaromania.ro               | 1         |            |            |
 * | 35  | Serbia                   | https://www.mensa.rs                  | 1         |            |            |
 * | 36  | Singapore                | https://www.mensa.org.sg              | 1         |            |            |
 * | 37  | Slovakia                 | https://www.mensa.sk                  | 1         |            |            |
 * | 38  | Slovenia                 | https://mensa.si                      | 1         |            |            |
 * | 39  | South Africa             | https://mensa.org.za                  | 1         |            |            |
 * | 40  | South Korea              | https://www.mensakorea.org            | 1         |            |            |
 * | 41  | Spain                    | https://www.mensa.es                  | 1         |            |            |
 * | 42  | Sweden                   | https://mensa.se                      | 1         |            |            |
 * | 43  | Switzerland              | https://mensa.ch                      | 1         |            |            |
 * | 44  | Taiwan                   | https://www.mensa.tw                  | 1         |            |            |
 * | 45  | Türkiye                  | https://mensa.org.tr                  | 0         |            |            |
 * | 46  | United Kingdom           | https://mensa.org.uk                  | 1         |            |            |
 * | 47  | United States of America | https://www.us.mensa.org              | 1         |            |            |
 */
class NationalMensaSeeder extends Seeder
{
    public function run(): void
    {
        $nations = [
            [
                'name' => 'Argentina',
                'url' => 'http://www.mensa.org.ar',
                'is_active' => true,
            ],
            [
                'name' => 'Australia',
                'url' => 'https://www.mensa.org.au',
                'is_active' => true,
            ],
            [
                'name' => 'Austria',
                'url' => 'https://www.mensa.at',
                'is_active' => true,
            ],
            [
                'name' => 'Belgium',
                'url' => 'https://www.mensa.be/en',
                'is_active' => true,
            ],
            [
                'name' => 'Bosnia and Herzegovina',
                'url' => 'https://mensa.ba',
                'is_active' => true,
            ],
            [
                'name' => 'Brazil',
                'url' => 'https://mensa.org.br',
                'is_active' => true,
            ],
            [
                'name' => 'Bulgaria',
                'url' => 'https://mensa.bg',
                'is_active' => true,
            ],
            [
                'name' => 'Canada',
                'url' => 'https://mensa.ca',
                'is_active' => true,
            ],
            [
                'name' => 'Croatia',
                'url' => 'https://mensa.hr',
                'is_active' => true,
            ],
            [
                'name' => 'Cyprus',
                'url' => 'http://www.mensa.org.cy',
                'is_active' => true,
            ],
            [
                'name' => 'Czech Republic',
                'url' => 'https://mensa.cz',
                'is_active' => true,
            ],
            [
                'name' => 'Denmark',
                'url' => 'https://mensa.dk',
                'is_active' => true,
            ],
            [
                'name' => 'Finland',
                'url' => 'https://www.mensa.fi',
                'is_active' => true,
            ],
            [
                'name' => 'France',
                'url' => 'https://mensa-france.net',
                'is_active' => true,
            ],
            [
                'name' => 'Germany',
                'url' => 'https://www.mensa.de',
                'is_active' => true,
            ],
            [
                'name' => 'Greece',
                'url' => 'https://www.mensa.org.gr',
                'is_active' => true,
            ],
            [
                'name' => 'Hungary',
                'url' => 'https://mensa.hu',
                'is_active' => true,
            ],
            [
                'name' => 'India',
                'url' => 'https://mensaindia.org/new/index.php',
                'is_active' => true,
            ],
            [
                'name' => 'Indonesia',
                'url' => 'https://mensa.id',
                'is_active' => true,
            ],
            [
                'name' => 'Italy',
                'url' => 'https://www.mensa.it',
                'is_active' => true,
            ],
            [
                'name' => 'Japan',
                'url' => 'https://www.mensa.jp',
                'is_active' => true,
            ],
            [
                'name' => 'Luxembourg',
                'url' => 'https://www.mensa.lu/lb',
                'is_active' => true,
            ],
            [
                'name' => 'Malaysia',
                'url' => 'https://mensa.my',
                'is_active' => true,
            ],
            [
                'name' => 'Mexico',
                'url' => 'https://mensa.org.mx',
                'is_active' => true,
            ],
            [
                'name' => 'Montenegro',
                'url' => 'https://mensa.me',
                'is_active' => true,
            ],
            [
                'name' => 'Netherlands',
                'url' => 'https://www.mensa.nl',
                'is_active' => true,
            ],
            [
                'name' => 'New Zealand',
                'url' => 'https://mensa.org.nz/home-of-mensa-nz',
                'is_active' => true,
            ],
            [
                'name' => 'North Macedonia',
                'url' => 'https://www.mensa.org.mk',
                'is_active' => true,
            ],
            [
                'name' => 'Norway',
                'url' => 'https://www.mensa.no',
                'is_active' => true,
            ],
            [
                'name' => 'Pakistan',
                'url' => 'https://mensa.pk',
                'is_active' => true,
            ],
            [
                'name' => 'Peru',
                'url' => 'http://mensa.pe',
                'is_active' => true,
            ],
            [
                'name' => 'Philippines',
                'url' => 'https://mensaphilippines.org',
                'is_active' => true,
            ],
            [
                'name' => 'Poland',
                'url' => 'https://www.mensa.org.pl',
                'is_active' => true,
            ],
            [
                'name' => 'Romania',
                'url' => 'https://mensaromania.ro',
                'is_active' => true,
            ],
            [
                'name' => 'Serbia',
                'url' => 'https://www.mensa.rs',
                'is_active' => true,
            ],
            [
                'name' => 'Singapore',
                'url' => 'https://www.mensa.org.sg',
                'is_active' => true,
            ],
            [
                'name' => 'Slovakia',
                'url' => 'https://www.mensa.sk',
                'is_active' => true,
            ],
            [
                'name' => 'Slovenia',
                'url' => 'https://mensa.si',
                'is_active' => true,
            ],
            [
                'name' => 'South Africa',
                'url' => 'https://mensa.org.za',
                'is_active' => true,
            ],
            [
                'name' => 'South Korea',
                'url' => 'https://www.mensakorea.org',
                'is_active' => true,
            ],
            [
                'name' => 'Spain',
                'url' => 'https://www.mensa.es',
                'is_active' => true,
            ],
            [
                'name' => 'Sweden',
                'url' => 'https://mensa.se',
                'is_active' => true,
            ],
            [
                'name' => 'Switzerland',
                'url' => 'https://mensa.ch',
                'is_active' => true,
            ],
            [
                'name' => 'Taiwan',
                'url' => 'https://www.mensa.tw',
                'is_active' => true,
            ],
            [
                'name' => 'Türkiye',
                'url' => 'https://mensa.org.tr',
                'is_active' => true,
            ],
            [
                'name' => 'United Kingdom',
                'url' => 'https://mensa.org.uk',
                'is_active' => true,
            ],
            [
                'name' => 'United States of America',
                'url' => 'https://www.us.mensa.org',
                'is_active' => true,
            ],
        ];
        foreach ($nations as $nation) {
            NationalMensa::firstOrCreate(
                ['name' => $nation['name']],
                [
                    'url' => $nation['url'],
                    'is_active' => $nation['is_active'],
                ]
            );
        }
    }
}
