<?php
// ============================================================
// ULMS — Borrow Validator
// Business Layer: business/validators/BorrowValidator.php
// ============================================================

class BorrowValidator {

    private array $errors = [];

    public function validate(array $data): bool {
        $this->errors = [];

        if (empty($data['book_id']) || (int)$data['book_id'] < 1) {
            $this->errors[] = 'A valid book must be selected.';
        }

        if (empty($data['student_id']) || (int)$data['student_id'] < 1) {
            $this->errors[] = 'A valid student must be selected.';
        }

        return empty($this->errors);
    }

    public function getErrors(): array { return $this->errors; }
    public function getFirstError(): string { return $this->errors[0] ?? ''; }
}
