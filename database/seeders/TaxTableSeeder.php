<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $taxes = [
            'GST' => '10',
            'CGST' => '18',
            'VAT' => '10',
            'IGST' => '10',
            'UTGST' => '10',
        ];

        foreach ($taxes as $key => $value) {
            \App\Models\Tax::create([
                'company_id' => $companyId,
                'tax_name' => $key,
                'rate_percent' => $value,
            ]);
        }
    }

}
