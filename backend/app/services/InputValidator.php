<?php

namespace App\Services;

class InputValidator {
    /**
     * Validate email format
     * @param string $email Email to validate
     * @return bool
     */
    public static function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     * @param string $password Password to validate
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePassword(string $password): array {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'"<>,.?\/]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate string length
     * @param string $str String to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @param string $fieldName Field name for error messages
     * @return array ['valid' => bool, 'error' => string or null]
     */
    public static function validateLength(string $str, int $min, int $max, string $fieldName = 'Value'): array {
        $len = strlen($str);
        if ($len < $min) {
            $error = $fieldName . ' must be at least ' . $min . ' characters';
            return ['valid' => false, 'error' => $error];
        }
        if ($len > $max) {
            $error = $fieldName . ' must not exceed ' . $max . ' characters';
            return ['valid' => false, 'error' => $error];
        }
        return ['valid' => true, 'error' => null];
    }

    /**
     * Sanitize string input (trim and remove null bytes)
     * @param string $str String to sanitize
     * @return string
     */
    public static function sanitizeString(string $str): string {
        return trim(str_replace("\0", '', $str));
    }

    /**
     * Validate username/name format
     * @param string $name Name to validate
     * @return array ['valid' => bool, 'error' => string or null]
     */
    public static function validateName(string $name): array {
        $name = self::sanitizeString($name);
        
        if (strlen($name) < 2) {
            return ['valid' => false, 'error' => 'Name must be at least 2 characters'];
        }
        if (strlen($name) > 255) {
            return ['valid' => false, 'error' => 'Name must not exceed 255 characters'];
        }
        if (!preg_match('/^[a-zA-Z\s\'-]+$/', $name)) {
            return ['valid' => false, 'error' => 'Name can only contain letters, spaces, hyphens, and apostrophes'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Validate task title
     * @param string $title Title to validate
     * @return array ['valid' => bool, 'error' => string or null]
     */
    public static function validateTaskTitle(string $title): array {
        $title = self::sanitizeString($title);
        
        if (strlen($title) < 3) {
            return ['valid' => false, 'error' => 'Title must be at least 3 characters'];
        }
        if (strlen($title) > 255) {
            return ['valid' => false, 'error' => 'Title must not exceed 255 characters'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Validate task description
     * @param string $description Description to validate
     * @return array ['valid' => bool, 'error' => string or null]
     */
    public static function validateTaskDescription(string $description): array {
        if (strlen($description) > 5000) {
            return ['valid' => false, 'error' => 'Description must not exceed 5000 characters'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Validate and sanitize task status
     * @param string $status Status to validate
     * @param array $validStatuses Valid status values
     * @return array ['valid' => bool, 'error' => string or null]
     */
    public static function validateStatus(string $status, array $validStatuses): array {
        $status = self::sanitizeString($status);
        
        if (!in_array($status, $validStatuses, true)) {
            return ['valid' => false, 'error' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Validate JSON request body
     * @param string $body Request body
     * @return array ['valid' => bool, 'data' => array or null, 'error' => string or null]
     */
    public static function validateJsonBody(string $body): array {
        if (empty($body)) {
            return ['valid' => false, 'data' => null, 'error' => 'Request body cannot be empty'];
        }

        $data = json_decode($body, true);
        if ($data === null) {
            return ['valid' => false, 'data' => null, 'error' => 'Invalid JSON format'];
        }

        if (!is_array($data)) {
            return ['valid' => false, 'data' => null, 'error' => 'Request body must be a JSON object'];
        }

        return ['valid' => true, 'data' => $data, 'error' => null];
    }

    /**
     * Validate required fields in data array
     * @param array $data Data to validate
     * @param array $requiredFields Required field names
     * @return array ['valid' => bool, 'missing' => array]
     */
    public static function validateRequiredFields(array $data, array $requiredFields): array {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }

        return ['valid' => empty($missing), 'missing' => $missing];
    }

    /**
     * Validate integer ID
     * @param mixed $id ID to validate
     * @return bool
     */
    public static function isValidId($id): bool {
        return is_numeric($id) && (int)$id > 0;
    }
}
