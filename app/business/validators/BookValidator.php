<?php
// ============================================================
// ULMS — Book Validator
// Business Layer: business/validators/BookValidator.php
// ============================================================

class BookValidator {

    private array $errors = [];

    public function validate(array $data): bool {
        $this->errors = [];

        if (empty(trim($data['title'] ?? ''))) {
            $this->errors[] = 'Book title is required.';
        }

        if (empty(trim($data['author'] ?? ''))) {
            $this->errors[] = 'Author name is required.';
        }

        if (empty(trim($data['category'] ?? ''))) {
            $this->errors[] = 'Category is required.';
        }

        $copies = $data['total_copies'] ?? 0;
        if (!is_numeric($copies) || (int)$copies < 1) {
            $this->errors[] = 'Total copies must be at least 1.';
        }

        if (!empty($data['isbn']) && !preg_match('/^[0-9\-]{10,17}$/', $data['isbn'])) {
            $this->errors[] = 'ISBN must be 10–17 digits (hyphens allowed).';
        }

        return empty($this->errors);
    }

    public function getErrors(): array { return $this->errors; }
    public function getFirstError(): string { return $this->errors[0] ?? ''; }
}
