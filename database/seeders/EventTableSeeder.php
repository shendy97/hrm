<?php

namespace Database\Seeders;

use App\Models\Event;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventTableSeeder extends Seeder
{

    public function run($companyId)
    {
        DB::table('events')->delete();
        DB::table('event_attendees')->delete();

        DB::statement('ALTER TABLE events AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE event_attendees AUTO_INCREMENT = 1');

        $count = config('app.seed_record_count');
        $faker = \Faker\Factory::create();

        \App\Models\Event::factory()->count((int)$count)
            ->count((int)$count)
            ->make()
            ->each(function (Event $event) use ($faker, $companyId) {
                $event->company_id = $companyId;
                $event->save();
                $employees = \App\Models\User::allEmployees(null, false, null, $companyId)->pluck('id')->toArray();
                try {
                    $randomEmployeeArray = $faker->randomElements($employees, $faker->numberBetween(1, 10));

                    foreach ($randomEmployeeArray as $employee) {
                        $eventAttendees = new \App\Models\EventAttendee();
                        $eventAttendees->user_id = $employee;
                        $eventAttendees->event_id = $event->id;
                        /* @phpstan-ignore-line */
                        $eventAttendees->save();
                    }
                } catch (Exception $e) {
                    Log::info($e);
                }
            });
    }

}
