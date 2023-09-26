<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with('subtasks');

        // Filter by status
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter by priority range
        if ($request->has('priority_from')) {
            $query->where('priority', '>=', (int)$request->input('priority_from'));
        }
        if ($request->has('priority_to')) {
            $query->where('priority', '<=', (int)$request->input('priority_to'));
        }

        // Full-text search by title
        if ($request->has('title')) {
            $title = $request->input('title');
            $query->where(function ($q) use ($title) {
                $q->where('title', 'like', '%' . $title . '%');
            });
        }

        // Sorting
        if ($request->has('sort')) {
            $sortField = $request->input('sort');
            $sortDirection = $request->input('sort_direction', 'asc');
            // Handle custom sorting options
            switch ($sortField) {
                case 'created_at':
                case 'completed_at':
                case 'priority':
                    $query->orderBy($sortField, $sortDirection);
                    break;
                default:
                    // Handle unknown sort field or fallback to default sorting
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            // Default sorting if not specified
            $query->orderBy('created_at', 'desc');
        }

        $tasks = $query->get();

        return new JsonResponse(['tasks' => $tasks]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $task = Task::with('subtasks')->find($id); // enchant subtasks
        if (!$task) {
            abort(404);
        }
        return new JsonResponse(['task' => $task]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,done',
            'priority' => 'required|integer|between:1,5',
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['errors' => $validator->errors()], 400);
        }

        // Create a new Task
        $task = new Task([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'user_id' => $request->input('user_id'),
        ]);

        // Save the Task to the database
        $task->save();
        // Function to recursively create subtasks
        function createSubtasks($task, $subtaskData): void
        {
            if (isset($subtaskData['title'])) {
                $subtask = new Subtask([
                    'title' => $subtaskData['title'],
                    'description' => $subtaskData['description'],
                    'status' => $subtaskData['status'],
                    'priority' => $subtaskData['priority'],
                    'user_id' => $subtaskData['user_id'],
                    // Set other Subtask properties here
                ]);

                // Associate the Subtask with the Task
                $task->subtasks()->save($subtask);

                // Recursively create subtasks if there are nested subtasks
                if (isset($subtaskData['subtask'])) {
                    createSubtasks($subtask, $subtaskData['subtask']);
                }
            }
        }

        // Check if a Subtask is provided in the request
        if ($request->has('subtask')) {
            $subtaskData = $request->input('subtask');
            createSubtasks($task, $subtaskData);
        }

        return new JsonResponse(['task' => $task, 'message' => 'Task created successfully'], 201);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        // Validate the request data for updating a Task
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,done',
            'priority' => 'required|integer|between:1,5',
            'user_id' => 'required|integer',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['errors' => $validator->errors()], 400);
        }

        // Update the Task with the provided data
        $task->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'user_id' => $request->input('user_id'),
        ]);

        // Check if a Subtask is provided in the request
        if ($request->has('subtask')) {
            // Get the Subtask data from the request
            $subtaskData = $request->input('subtask');

            // Check if the Task has an associated Subtask
            if ($task->subtasks->isNotEmpty()) {
                // Update the existing Subtask with the provided data
                $task->subtasks[0]->update([
                    'title' => $subtaskData['title'],
                    'description' => $subtaskData['description'],
                    'status' => $subtaskData['status'],
                    'priority' => $subtaskData['priority'],
                    'user_id' => $subtaskData['user_id'],
                    // Set other Subtask properties here
                ]);
            } else {
                // If no associated Subtask exists, create a new one
                $subtask = new Subtask([
                    'title' => $subtaskData['title'],
                    'description' => $subtaskData['description'],
                    'status' => $subtaskData['status'],
                    'priority' => $subtaskData['priority'],
                    'user_id' => $subtaskData['user_id'],
                    // Set other Subtask properties here
                ]);

                // Associate the Subtask with the Task
                $task->subtasks()->save($subtask);
            }
        } elseif ($task->subtasks->isNotEmpty()) {
            // If no Subtask data is provided but the Task has an associated Subtask, delete it
            $task->subtasks[0]->delete();
        }

        // Reload the updated Task and its Subtask (if exists)
        $task->load('subtasks');

        return new JsonResponse(['task' => $task, 'message' => 'Task updated successfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function markAsDone(Request $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        if ($task->subtasks()->where('status', 'todo')->exists()) {
            return new JsonResponse(['error' => 'Cannot mark the task as done because it has unfinished subtasks.'], 400);
        }
        // Проверьте, является ли задача активной (не выполненной)
        if ($task->status === 'todo') {
            $task->status = 'done';
            $task->save();

            return new JsonResponse(['message' => 'Task marked as done successfully'], 200);
        } else {
            return new JsonResponse(['error' => 'Task is already marked as done.'], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse
    {

        $task = Task::findOrFail($id);
        // Check if the task is completed
        if ($task->status === 'done') {
            return new JsonResponse(['error' => 'Cannot delete a completed task.'], 400);
        }

        function deleteTaskAndSubtasks($task): void
        {
            // Get all subtasks for the task
            $subtasks = $task->subtasks;

            // Recursively call deleteTaskAndSubtasks for each subtask
            foreach ($subtasks as $subtask) {
                deleteTaskAndSubtasks($subtask);
            }

            // Delete the current task
            $task->delete();
        }

        // Call a recursive function to delete all subtasks and tasks
        deleteTaskAndSubtasks($task);

        return new JsonResponse(['message' => 'Task and all its subtasks deleted successfully'], 200);
    }
}
