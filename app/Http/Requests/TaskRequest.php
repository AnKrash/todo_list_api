<?php

namespace App\Http\Requests;

use App\Rules\ValidateNestedSubtasks;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,done',
            'priority' => 'required|integer|between:1,5',
            'user_id' => 'required|integer',
            'subtask' => ['required', 'array', new ValidateNestedSubtasks()],
        ];
    }
}
