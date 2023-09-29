<?php


namespace App\Services;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Support\Arr;

class TaskDataService
{
    /**
     * @param array $data
     * @return mixed
     */
    public function createTask(array $data): mixed
    {
        // Create a new Task instance with the provided data
        $task = Task::create($data);

        // Check if there are subtasks and create them if present
        if (isset($data['subtask']) && is_array($data['subtask'])) {
            $this->createSubtasks($task, $data['subtask']);
        }

        return $task;
    }

    /**
     * @param Task $task
     * @param array $subtaskData
     * @param $parentSubtask
     * @return void
     */
    private function createSubtasks(Task $task, array $subtaskData, $parentSubtask = null): void
    {
        foreach ($subtaskData as $subtaskDatum) {
            // Create a new Subtask instance with the provided data
            $subtask = new Subtask([
                'title' => $subtaskDatum['title'],
                'description' => $subtaskDatum['description'],
                'status' => $subtaskDatum['status'],
                'priority' => $subtaskDatum['priority'],
                'user_id' => $subtaskDatum['user_id'],
            ]);

            // Set the parent_subtask_id if it exists
            if ($parentSubtask !== null) {
                $subtask->parent_subtask_id = $parentSubtask->id;
            }

            // Save the subtask
            $task->subtasks()->save($subtask);

            // Recursively create subtasks if there are nested subtasks
            if (isset($subtaskDatum['subtask']) && is_array($subtaskDatum['subtask'])) {
                $this->createSubtasks($task, $subtaskDatum['subtask'], $subtask);
            }
        }
    }

    /**
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function updateTask(Task $task, array $data): Task
    {
        // Update the Task with the provided data
        $task->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'user_id' => $data['user_id'],
        ]);

        // Handle subtasks
        $subtaskData = Arr::get($data, 'subtask', []);

        // Recursively update subtasks
        $this->updateSubtasks($task, $subtaskData, null);

        // Reload the updated Task and its Subtasks
        $task->load('subtasks');

        return $task;
    }

    /**
     * @param Task $task
     * @param array $subtaskData
     * @param $parentSubtask
     * @return void
     */
    private function updateSubtasks(Task $task, array $subtaskData, $parentSubtask = null): void
    {
        foreach ($subtaskData as $subtaskDatum) {
            // Check if the "id" key is present
            if (isset($subtaskDatum['id'])) {
                $subtask = Subtask::find($subtaskDatum['id']);
            } else {
                $subtask = new Subtask();
            }

            // Update the subtask data
            $subtask->fill([
                'title' => $subtaskDatum['title'],
                'description' => $subtaskDatum['description'],
                'status' => $subtaskDatum['status'],
                'priority' => $subtaskDatum['priority'],
                'user_id' => $subtaskDatum['user_id'],
                'parent_subtask_id' => $parentSubtask ? $parentSubtask->id : null,
            ]);

            // Save the subtask
            $subtask->save();

            // Recursively update subtasks if there are nested subtasks
            if (isset($subtaskDatum['subtask']) && is_array($subtaskDatum['subtask'])) {
                $this->updateSubtasks($task, $subtaskDatum['subtask'], $subtask);
            }
        }
    }

    /**
     * @param $task
     * @return void
     */
    public function deleteTaskAndSubtasks($task): void
    {
        if ($task instanceof Task) {
            if ($task->status === 'done') {
                throw new \InvalidArgumentException('Cannot delete a completed task.');
            }

            // Delete the subtasks first if they exist
            if ($task->subtasks) {
                $task->subtasks->each(function ($subtask) {
                    $this->deleteTaskAndSubtasks($subtask);
                });
            }
        }

        // Delete the current task if it's a Task model or a Subtask model
        if ($task instanceof Task || $task instanceof Subtask) {
            $task->delete();
        }
    }
}
