<?php


namespace App\Services;

use App\Rules\ValidateNestedSubtasks;
use Illuminate\Support\Facades\Validator;

class TaskValidationService
{

    public function validateTaskDataWithSubtask(array $data): \Illuminate\Validation\Validator
    {
        // Define your validation rules including subtask rules

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,done',
            'priority' => 'required|integer|between:1,5',
            'user_id' => 'required|integer',
            'subtask' => ['required', 'array', new ValidateNestedSubtasks()],
        ];

        // Create and return the validator instance


        return Validator::make($data, $rules);
    }
}
