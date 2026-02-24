<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsuranceCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'State Farm',                     'short_name' => 'State Farm'],
            ['name' => 'Allstate',                       'short_name' => 'Allstate'],
            ['name' => 'Progressive',                    'short_name' => 'Progressive'],
            ['name' => 'GEICO',                          'short_name' => 'GEICO'],
            ['name' => 'USAA',                           'short_name' => 'USAA'],
            ['name' => 'Farmers Insurance',              'short_name' => 'Farmers'],
            ['name' => 'Liberty Mutual',                 'short_name' => 'Liberty Mutual'],
            ['name' => 'Nationwide',                     'short_name' => 'Nationwide'],
            ['name' => 'Travelers',                      'short_name' => 'Travelers'],
            ['name' => 'American Family Insurance',      'short_name' => 'AmFam'],
            ['name' => 'Erie Insurance',                 'short_name' => 'Erie'],
            ['name' => 'Auto-Owners Insurance',          'short_name' => 'Auto-Owners'],
            ['name' => 'The Hartford',                   'short_name' => 'Hartford'],
            ['name' => 'Chubb',                          'short_name' => 'Chubb'],
            ['name' => 'MetLife Auto & Home',            'short_name' => 'MetLife'],
            ['name' => 'AAA',                            'short_name' => 'AAA'],
            ['name' => 'Mercury Insurance',              'short_name' => 'Mercury'],
            ['name' => 'Safeco Insurance',               'short_name' => 'Safeco'],
            ['name' => 'Shelter Insurance',              'short_name' => 'Shelter'],
            ['name' => 'Texas Farm Bureau',              'short_name' => 'TX Farm Bureau'],
            ['name' => 'Bristol West',                   'short_name' => 'Bristol West'],
            ['name' => 'Infinity Insurance',             'short_name' => 'Infinity'],
            ['name' => 'Gainsco',                        'short_name' => 'Gainsco'],
            ['name' => 'National General',               'short_name' => 'National General'],
            ['name' => 'Dairyland Insurance',            'short_name' => 'Dairyland'],
        ];

        foreach ($companies as $company) {
            \App\Models\InsuranceCompany::firstOrCreate(
                ['name' => $company['name']],
                ['short_name' => $company['short_name'], 'is_active' => true]
            );
        }
    }
}
