
<?php
// ============================================================
// ULMS — Fine Model (DTO)
// Data Layer: data/models/Fine.php
// ============================================================

class Fine {
    public int    $id          = 0;
    public int    $loan_id     = 0;
    public int    $student_id  = 0;
    public float  $amount      = 0.0;
    public int    $overdue_days = 0;
    public string $status      = 'unpaid';
    public ?string $settled_at = null;
    public ?int   $settled_by  = null;
    public string $created_at  = '';

    // Joined
    public string $student_name     = '';
    public string $student_username = '';
    public string $book_title       = '';
    public string $due_date         = '';

    public static function fromArray(array $data): self {
        $f = new self();
        $f->id           = (int)($data['id']           ?? 0);
        $f->loan_id      = (int)($data['loan_id']      ?? 0);
        $f->student_id   = (int)($data['student_id']   ?? 0);
        $f->amount       = (float)($data['amount']     ?? 0.0);
        $f->overdue_days = (int)($data['overdue_days'] ?? 0);
        $f->status       =        $data['status']      ?? 'unpaid';
        $f->settled_at   =        $data['settled_at']  ?? null;
        $f->settled_by   = isset($data['settled_by']) ? (int)$data['settled_by'] : null;
        $f->created_at   =        $data['created_at']  ?? '';

        $f->student_name     = $data['student_name']     ?? '';
        $f->student_username = $data['student_username'] ?? '';
        $f->book_title       = $data['book_title']       ?? '';
        $f->due_date         = $data['due_date']         ?? '';
        return $f;
    }
}
