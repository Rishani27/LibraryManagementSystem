<?php
// ============================================================
// ULMS — User Validator
// Business Layer: business/validators/UserValidator.php
// ============================================================

class UserValidator {

    private array $errors = [];

    public function validate(array $data, bool $isUpdate = false): bool {
        $this->errors = [];

        if (empty(trim($data['full_name'] ?? ''))) {
            $this->errors[] = 'Full name is required.';
        } elseif (strlen($data['full_name']) > 100) {
            $this->errors[] = 'Full name must be 100 characters or less.';
        }

        if (!$isUpdate) {
            if (empty(trim($data['username'] ?? ''))) {
                $this->errors[] = 'Username is required.';
            } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data['username'])) {
                $this->errors[] = 'Username must be 3–50 alphanumeric characters or underscores.';
            }

            if (empty($data['password'] ?? '')) {
                $this->errors[] = 'Password is required.';
            } elseif (strlen($data['password']) < 6) {
                $this->errors[] = 'Password must be at least 6 characters.';
            }
        }

        if (empty(trim($data['email'] ?? ''))) {
            $this->errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'A valid email address is required.';
        }

        if (!in_array($data['role'] ?? '', ['admin', 'librarian', 'student'], true)) {
            $this->errors[] = 'A valid role must be selected.';
        }

        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): string {
        return $this->errors[0] ?? '';
    }
}
