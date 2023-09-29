<?php

namespace App\Services;

class TaskFilterService
{
    /**
     * @param $query
     * @param $filters
     * @return mixed
     */
    public function filterTasks($query, $filters): mixed
    {
        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter by priority range
        if (isset($filters['priority_from'])) {
            $query->where('priority', '>=', (int)$filters['priority_from']);
        }

        if (isset($filters['priority_to'])) {
            $query->where('priority', '<=', (int)$filters['priority_to']);
        }

        // Full-text search by title
        if (isset($filters['title'])) {
            $title = $filters['title'];
            $query->where(function ($q) use ($title) {
                $q->where('title', 'like', '%' . $title . '%');
            });
        }

        // Sorting
        if (isset($filters['sort'])) {
            $sortField = $filters['sort'];
            $sortDirection = $filters['sort_direction'] ?? 'asc';
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

        return $query->get();
    }
}

