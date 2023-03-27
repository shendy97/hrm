<?php

namespace Database\Seeders;

use App\Models\Appreciation;
use App\Models\Award;
use App\Models\AwardIcon;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppreciationSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $awardIcons = AwardIcon::all()->pluck('id')->toArray();
        $iconColors = ['#282E33', '#495E67', '#FF3838', '#3DADDD', '#387B1C', '#7B1C2E'];

        $awards = [
            [
                'award_icon_id' => $awardIcons[array_rand($awardIcons)],
                'color_code' => $iconColors[array_rand($iconColors)],
                'title' => 'Employee of the month',
                'company_id' => $companyId,
            ],
            [
                'award_icon_id' => $awardIcons[array_rand($awardIcons)],
                'color_code' => $iconColors[array_rand($iconColors)],
                'title' => 'Attendance Award',
                'company_id' => $companyId,
            ],
            [
                'award_icon_id' => $awardIcons[array_rand($awardIcons)],
                'color_code' => $iconColors[array_rand($iconColors)],
                'title' => 'Star Performer Award',
                'company_id' => $companyId,
            ],
            [
                'award_icon_id' => $awardIcons[array_rand($awardIcons)],
                'color_code' => $iconColors[array_rand($iconColors)],
                'title' => 'Employee Of The Year',
                'company_id' => $companyId,
            ],

        ];

        Award::insert($awards);

        $employees = User::allEmployees(null, false, null, $companyId)->pluck('id')->toArray();
        $awards = Award::where('company_id', $companyId)->get()->pluck('id')->toArray();

        $date = fake()->randomElement([fake()->dateTimeThisMonth()->format('Y-m-d'), fake()->dateTimeThisYear()->format('Y-m-d')]);


        for ($i = 0; $i <= 10; $i++) {
            $appreciation = new Appreciation();
            $appreciation->award_to = $employees[array_rand($employees)];
            $appreciation->award_id = $awards[array_rand($awards)];
            $appreciation->company_id = $companyId;
            $appreciation->award_date = $date;
            $appreciation->added_by = $awards[array_rand($awards)];
            $appreciation->save();
        }

    }

}
