<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateNestedSubtasks implements Rule
{
    public function passes($attribute, $value): bool
    {
        // Define your validation logic for nested subtasks here.
        // You can use recursion to validate nested subtasks.

        // For example, if you want to check that each subtask has a 'title' and 'status':
        foreach ($value as $subtask) {
            if (!isset($subtask['title']) || !isset($subtask['status'])) {
                return false;
            }

            // Recursively validate subtask's subtasks if they exist
            if (isset($subtask['subtask'])) {
                $subtaskRule = new self();
                if (!$subtaskRule->passes($attribute, $subtask['subtask'])) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return 'Validation of nested subtasks failed.';
    }
}
