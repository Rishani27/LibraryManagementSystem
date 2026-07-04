<?php
// ============================================================
// ULMS — Reservation Model (DTO)
// Data Layer: data/models/Reservation.php
// ============================================================

class Reservation {
    public int    $id           = 0;
    public int    $book_id      = 0;
    public int    $student_id   = 0;
    public string $reserved_at  = '';
    public string $status       = 'pending';
    public ?string $fulfilled_at = null;

    // Joined
    public string $book_title    = '';
    public string $student_name  = '';
    public string $student_username = '';

    public static function fromArray(array $data): self {
        $r = new self();
        $r->id           = (int)($data['id']          ?? 0);
        $r->book_id      = (int)($data['book_id']     ?? 0);
        $r->student_id   = (int)($data['student_id']  ?? 0);
        $r->reserved_at  =       $data['reserved_at'] ?? '';
        $r->status       =       $data['status']      ?? 'pending';
        $r->fulfilled_at =       $data['fulfilled_at'] ?? null;

        $r->book_title        = $data['book_title']        ?? '';
        $r->student_name      = $data['student_name']      ?? '';
        $r->student_username  = $data['student_username']  ?? '';
        return $r;
    }
}
