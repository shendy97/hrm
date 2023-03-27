<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $setting = Company::find($companyId);
        $admin = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->where('users.company_id', $companyId)
            ->select('users.id')
            ->first();

        $count = config('app.seed_record_count');
        $faker = \Faker\Factory::create();

        Contract::factory()
            ->count((int)$count)
            ->make()
            ->each(function (Contract $contract) use ($faker, $admin, $setting, $companyId) {
                $contract->company_id = $companyId;
                $contract->contract_type_id = $faker->randomElement($this->getContractType($companyId));
                /* @phpstan-ignore-line */
                $contract->client_id = $this->getClient($companyId);
                /* @phpstan-ignore-line */
                $contract->added_by = $admin->id;
                /* @phpstan-ignore-line */
                $contract->currency_id = $setting->currency_id;
                $contract->contract_number = Contract::where('company_id', $companyId)->count() + 1;
                /* @phpstan-ignore-line */
                $contract->save();
            });

    }

    public function getContractType($companyId)
    {
        return \App\Models\ContractType::inRandomOrder()
            ->where('company_id', $companyId)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    public function getClient($companyId)
    {
        return \App\Models\User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('client_details', 'users.id', '=', 'client_details.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'client_details.company_name', 'users.email', 'users.created_at')
            ->where('roles.name', 'client')
            ->where('users.company_id', $companyId)
            ->inRandomOrder()
            ->get()->pluck('id')->first();
    }

}
