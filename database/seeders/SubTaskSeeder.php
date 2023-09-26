<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subtask;
use App\Models\Task;

// Make sure to import the Task model if not already imported
use Exception;

// Import the Exception class

class SubTaskSeeder extends Seeder
{
    public function run()
    {

        SubTask::factory()->count(20)->create();

    }
}
