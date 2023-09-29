<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateNestedSubtasks implements Rule
{
    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        foreach ($value as $subtask) {
            if (!isset($subtask['title']) || !isset($subtask['status']) || !isset($subtask['priority']) || !is_int($subtask['priority']) || $subtask['priority'] < 1 || $subtask['priority'] > 5) {
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

    /**
     * @return string
     */
    public function message(): string
    {
        return 'Validation of nested subtasks failed.';
    }
}
