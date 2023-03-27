<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $projectArray = [
            'Create Design of worksuite',
            'Install Application',
            'Modify Application',
            'Server Installation',
            'Web Installation',
            'Project Management',
            'User Management',
            'School Management',
            'Restaurant Management',
            'Examination System Project',
            'Cinema Ticket Booking System',
            'Airline Reservation System',
            'Website Copier Project',
            'Chat Application',
            'Payment Billing System',
            'Identification System',
            'Document management System',
            'Live Meeting',
            'Android task monitoring',
            'Sentiment analysis for product rating',
            'Fingerprint-based ATM system',
            'Advanced employee management system',
            'Image encryption using the AES algorithm',
            'Fingerprint voting system',
            'Weather forecasting system',
            'Android local train ticketing system',
            'Railway tracking and arrival time prediction system',
            'Android Patient Tracker',
            'Opinion mining for social networking platforms',
            'Automated payroll system with GPS tracking and image capture',
            'Data leakage detection system',
            'Credit card fraud detection',
            'AI shopping system',
            'Camera motion sensor system',
            'Bug tracker',
            'e-Learning platform',
            'Smart health prediction system',
            'Software piracy protection system',
            'Face detector',
            'Voice Recognition',
            'Chatbots'
        ];

        $startDate = now()->subMonths(fake()->numberBetween(1, 6));

        $projectName = fake()->unique(true)->randomElement($projectArray);
        /* @phpstan-ignore-line */

        return [
            'project_name' => $projectName,
            'project_summary' => fake()->paragraph,
            'start_date' => $startDate->format('Y-m-d'),
            'deadline' => $startDate->addMonths(4)->format('Y-m-d'),
            'notes' => fake()->paragraph,
            'completion_percent' => fake()->numberBetween(40, 100),
            'feedback' => fake()->realText(),
            'project_short_code' => $this->initials($projectName),
        ];
    }

    protected function initials($str): string
    {
        $ret = '';

        $array = explode(' ', $str);

        if (count($array) === 1) {
            return strtoupper(substr($str, -4));
        }

        foreach ($array as $word) {
            $ret .= strtoupper($word[0]);
        }

        return $ret;
    }

}
