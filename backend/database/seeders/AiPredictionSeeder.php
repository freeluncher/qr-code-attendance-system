<?php

namespace Database\Seeders;

use App\Models\AiPrediction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiPredictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       AiPrediction::factory()->count(10)->create();
    }
}
