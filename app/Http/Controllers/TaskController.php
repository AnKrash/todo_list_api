<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Subtask;
use App\Models\Task;
use App\Services\TaskDataService;
use App\Services\TaskFilterService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private TaskFilterService $taskFilterService;

    protected TaskDataService $taskDataService;

    public function __construct(TaskFilterService $taskFilterService, TaskDataService $taskDataService)
    {
        $this->taskFilterService = $taskFilterService;
        $this->taskDataService = $taskDataService;
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
     * @param TaskRequest $request
     * @return JsonResponse
     */
    public function store(TaskRequest $request): JsonResponse
    {
        // Use the TaskDataService to create a new task
        $task = $this->taskDataService->createTask($request->validated());

        return response()->json(['task' => $task, 'message' => 'Task created successfully'], 201);
    }

    /**
     * @param TaskRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(TaskRequest $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        // Use the TaskDataService to update the task and its subtasks
        $task = $this->taskDataService->updateTask($task, $request->validated());

        return response()->json(['task' => $task, 'message' => 'Task updated successfully'], 200);
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
            throw new AuthorizationException('You are not authorized to delete this task.');
        }

        try {
            // Use the TaskDataService to delete the task and its subtasks
            $this->taskDataService->deleteTaskAndSubtasks($task);

            return new JsonResponse(['message' => 'Task and all its subtasks deleted successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
