<?php
// ============================================================
// ULMS — Book Model (DTO)
// Data Layer: data/models/Book.php
// ============================================================

class Book {
    public int    $id               = 0;
    public string $title            = '';
    public string $author           = '';
    public string $isbn             = '';
    public string $category         = '';
    public int    $total_copies     = 1;
    public int    $available_copies = 1;
    public string $created_at       = '';
    public string $updated_at       = '';

    public static function fromArray(array $data): self {
        $b = new self();
        $b->id               = (int)($data['id']               ?? 0);
        $b->title            =       $data['title']            ?? '';
        $b->author           =       $data['author']           ?? '';
        $b->isbn             =       $data['isbn']             ?? '';
        $b->category         =       $data['category']         ?? '';
        $b->total_copies     = (int)($data['total_copies']     ?? 1);
        $b->available_copies = (int)($data['available_copies'] ?? 1);
        $b->created_at       =       $data['created_at']       ?? '';
        $b->updated_at       =       $data['updated_at']       ?? '';
        return $b;
    }

    public function isAvailable(): bool {
        return $this->available_copies > 0;
    }
}
