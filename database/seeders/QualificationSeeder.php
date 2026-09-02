<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Qualification;

class QualificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $qualifications = [
            'Intern',
            'Junior',
            'Middle',
            'Senior',
            'Lead',
        ];

        foreach ($qualifications as $qualification) {
            Qualification::firstOrCreate(
                ['title' => $qualification],
                ['title' => $qualification]
            );
        }
    }
}
