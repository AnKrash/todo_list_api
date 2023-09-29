<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subtask;

class SubTaskSeeder extends Seeder
{
    public function run()
    {
        SubTask::factory()->count(20)->create();
    }
}
