<?php

namespace App\Models;

use PDO;

class Task {
    private $db;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE = 'done';

    // Valid statuses array
    private static array $validStatuses = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE
    ];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Validate task data
     * @param array $data Task data to validate
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validate(array $data): array {
        $errors = [];

        // Title validation
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        } else {
            if (strlen($data['title']) < 3) {
                $errors['title'] = 'Title must be at least 3 characters long';
            } elseif (strlen($data['title']) > 255) {
                $errors['title'] = 'Title must not exceed 255 characters';
            }
        }

        // Description validation (optional)
        if (isset($data['description'])) {
            if (strlen($data['description']) > 5000) {
                $errors['description'] = 'Description must not exceed 5000 characters';
            }
        }

        // Status validation (optional, defaults to 'pending')
        if (isset($data['status'])) {
            if (!self::isValidStatus($data['status'])) {
                $errors['status'] = 'Status must be one of: ' . implode(', ', self::$validStatuses);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if status is valid
     * @param string $status Status to check
     * @return bool
     */
    public static function isValidStatus(string $status): bool {
        return in_array($status, self::$validStatuses, true);
    }

    /**
     * Get all valid statuses
     * @return array
     */
    public static function getValidStatuses(): array {
        return self::$validStatuses;
    }

    /**
     * Create a new task
     * @param int $userId User ID
     * @param string $title Task title
     * @param string $description Task description
     * @param string $status Task status
     * @return int|false Task ID or false on failure
     */
    public function create(int $userId, string $title, string $description = '', string $status = self::STATUS_PENDING) {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO tasks (user_id, title, description, status, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([$userId, $title, $description, $status]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log('Task Create Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get task by ID
     * @param int $id Task ID
     * @param int $userId User ID (for authorization)
     * @return array|false Task data or false if not found
     */
    public function getById(int $id, int $userId) {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, user_id, title, description, status, created_at, updated_at 
                 FROM tasks WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([$id, $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Task GetById Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all tasks for a user
     * @param int $userId User ID
     * @param string $sortBy Column to sort by (created_at, updated_at, status)
     * @param string $order ASC or DESC
     * @return array Tasks array or empty array on failure
     */
    public function getByUserId(int $userId, string $sortBy = 'created_at', string $order = 'DESC'): array {
        try {
            $allowedSortColumns = ['created_at', 'updated_at', 'status', 'title'];
            $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

            $query = "SELECT id, user_id, title, description, status, created_at, updated_at 
                      FROM tasks WHERE user_id = ? 
                      ORDER BY $sortBy $order";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Task GetByUserId Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update task
     * @param int $id Task ID
     * @param int $userId User ID (for authorization)
     * @param array $data Fields to update
     * @return bool Success status
     */
    public function update(int $id, int $userId, array $data): bool {
        try {
            // Verify task belongs to user
            $stmt = $this->db->prepare('SELECT user_id FROM tasks WHERE id = ?');
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task || $task['user_id'] != $userId) {
                return false;
            }

            $updates = [];
            $params = [];

            if (isset($data['title'])) {
                $updates[] = 'title = ?';
                $params[] = $data['title'];
            }
            if (isset($data['description'])) {
                $updates[] = 'description = ?';
                $params[] = $data['description'];
            }
            if (isset($data['status']) && self::isValidStatus($data['status'])) {
                $updates[] = 'status = ?';
                $params[] = $data['status'];
            }

            if (empty($updates)) {
                return false;
            }

            $updates[] = 'updated_at = NOW()';
            $params[] = $id;

            $query = 'UPDATE tasks SET ' . implode(', ', $updates) . ' WHERE id = ?';
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log('Task Update Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete task
     * @param int $id Task ID
     * @param int $userId User ID (for authorization)
     * @return bool Success status
     */
    public function delete(int $id, int $userId): bool {
        try {
            // Verify task belongs to user
            $stmt = $this->db->prepare('SELECT user_id FROM tasks WHERE id = ?');
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task || $task['user_id'] != $userId) {
                return false;
            }

            $stmt = $this->db->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
            return $stmt->execute([$id, $userId]);
        } catch (\Exception $e) {
            error_log('Task Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tasks by status
     * @param int $userId User ID
     * @param string $status Status filter
     * @return array Tasks array
     */
    public function getByStatus(int $userId, string $status): array {
        if (!self::isValidStatus($status)) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT id, user_id, title, description, status, created_at, updated_at 
                 FROM tasks WHERE user_id = ? AND status = ? 
                 ORDER BY created_at DESC'
            );
            $stmt->execute([$userId, $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Task GetByStatus Error: ' . $e->getMessage());
            return [];
        }
    }
}
