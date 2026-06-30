<?php
// ============================================================
// ULMS — Loan Model (DTO)
// Data Layer: data/models/Loan.php
// ============================================================

class Loan {
    public int    $id          = 0;
    public int    $book_id     = 0;
    public int    $student_id  = 0;
    public int    $issued_by   = 0;
    public string $issue_date  = '';
    public string $due_date    = '';
    public ?string $return_date = null;
    public string $status      = 'active';
    public string $created_at  = '';

    // Joined fields
    public string $book_title   = '';
    public string $book_author  = '';
    public string $student_name = '';
    public string $student_username = '';
    public string $issued_by_name = '';

    public static function fromArray(array $data): self {
        $l = new self();
        $l->id          = (int)($data['id']          ?? 0);
        $l->book_id     = (int)($data['book_id']     ?? 0);
        $l->student_id  = (int)($data['student_id']  ?? 0);
        $l->issued_by   = (int)($data['issued_by']   ?? 0);
        $l->issue_date  =       $data['issue_date']  ?? '';
        $l->due_date    =       $data['due_date']    ?? '';
        $l->return_date =       $data['return_date'] ?? null;
        $l->status      =       $data['status']      ?? 'active';
        $l->created_at  =       $data['created_at']  ?? '';

        $l->book_title        = $data['book_title']        ?? '';
        $l->book_author       = $data['book_author']       ?? '';
        $l->student_name      = $data['student_name']      ?? '';
        $l->student_username  = $data['student_username']  ?? '';
        $l->issued_by_name    = $data['issued_by_name']    ?? '';
        return $l;
    }

    public function isOverdue(): bool {
        return $this->status === 'active' && $this->due_date < date('Y-m-d');
    }
}
