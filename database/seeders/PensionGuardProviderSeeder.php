<?php

namespace Database\Seeders;

use App\Modules\PensionAccountGuard\Models\PensionGuardProvider;
use Illuminate\Database\Seeder;

class PensionGuardProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'NEST Pensions',
                'website_url' => 'https://www.nestpensions.org.uk/',
            ],
            [
                'name' => 'The People\'s Pension',
                'website_url' => 'https://thepeoplespension.co.uk/',
            ],
            [
                'name' => 'Aviva',
                'website_url' => 'https://www.aviva.co.uk/',
            ],
            [
                'name' => 'Scottish Widows',
                'website_url' => 'https://www.scottishwidows.co.uk/',
            ],
            [
                'name' => 'Legal & General',
                'website_url' => 'https://www.legalandgeneral.com/',
            ],
            [
                'name' => 'Standard Life',
                'website_url' => 'https://www.standardlife.co.uk/',
            ],
            [
                'name' => 'NOW: Pensions',
                'website_url' => 'https://www.nowpensions.com/',
            ],
            [
                'name' => 'Aegon',
                'website_url' => 'https://www.aegon.co.uk/',
            ],
        ];

        foreach ($providers as $provider) {
            PensionGuardProvider::updateOrCreate(
                ['name' => $provider['name']],
                [
                    ...$provider,
                    'is_active' => true,
                ]
            );
        }
    }
}
