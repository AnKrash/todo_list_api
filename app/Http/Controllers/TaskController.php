<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use App\Services\TaskFilterService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\TaskValidationService;

class TaskController extends Controller
{
    protected TaskValidationService $taskValidationService;
    private TaskFilterService $taskFilterService;
    public function __construct(TaskValidationService $taskValidationService,TaskFilterService $taskFilterService)
    {
        $this->taskValidationService = $taskValidationService;
        $this->taskFilterService = $taskFilterService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function index(Request $request): JsonResponse
    {
        $query = Task::with('subtasks');

        // Get the filters from the request

        $filters = $request->all();

        // Use the TaskFilterService to filter tasks

        $filteredTasks = $this->taskFilterService->filterTasks($query, $filters);

        return new JsonResponse(['tasks' => $filteredTasks], 200);
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

    public function store(Request $request,TaskValidationService $taskValidationService): JsonResponse
    {

        $validator = $taskValidationService->validateTaskDataWithSubtask($request->all());
        if ($validator->fails()) {
            // Validation failed, return error messages
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
     * @param TaskValidationService $taskValidationService
     * @return JsonResponse
     * @throws AuthorizationException
     */


    public function update(Request $request, $id, TaskValidationService $taskValidationService): JsonResponse
    {
        $task = Task::findOrFail($id);

        if (!$this->authorize('update', $task)) {
            throw new AuthorizationException('You are not authorized to update this task.');
        }

        $validator = $taskValidationService->validateTaskDataWithSubtask($request->all());

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



// Handle subtasks
        $subtaskData = $request->input('subtask');

        // Recursively update subtasks
        $this->updateSubtasks($task, $subtaskData);

        // Reload the updated Task and its Subtasks
        $task->load('subtasks');

        return new JsonResponse(['task' => $task, 'message' => 'Task updated successfully'], 200);
    }

    private function updateSubtasks(Task $task, $subtaskData): void
    {
        $task->subtasks()->delete(); // Delete existing subtasks

        foreach ($subtaskData as $subtaskDatum) {
            $subtask = new Subtask([
                'title' => $subtaskDatum['title'],
                'description' => $subtaskDatum['description'],
                'status' => $subtaskDatum['status'],
                'priority' => $subtaskDatum['priority'],
                'user_id' => $subtaskDatum['user_id'],
            ]);

            $task->subtasks()->save($subtask);

            if (isset($subtaskDatum['subtask']) && is_array($subtaskDatum['subtask'])) {
                // Recursively update nested subtasks
                $this->updateSubtasks($task, $subtaskDatum['subtask']);
            }
        }
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
     * @throws AuthorizationException
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        if (!$this->authorize('delete', $task)) {
            throw new AuthorizationException('You are not authorized to update this task.');
        }

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
