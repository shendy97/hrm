<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractTypeTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $contracts = [
            'Adhesion Contracts',
            'Aleatory Contracts',
            'Bilateral and Unilateral Contracts',
            'Contracts under Seal',
            'Executed and Executory Contracts',
            'Express Contracts',
            'Implied Contracts',
            'Unconscionable Contracts',
            'Void and Voidable Contracts'
        ];

        foreach ($contracts as $value) {
            \App\Models\ContractType::create([
                'company_id' => $companyId,
                'name' => $value,
            ]);
        }

    }

}
