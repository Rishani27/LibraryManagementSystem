<?php
// ============================================================
// ULMS — Book Repository
// Data Layer: data/repositories/BookRepository.php
// ============================================================

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/Book.php';

class BookRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Book {
        $stmt = $this->db->prepare('SELECT * FROM books WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Book::fromArray($row) : null;
    }

    public function findAll(int $limit = 100, int $offset = 0): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM books ORDER BY title ASC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        return array_map([Book::class, 'fromArray'], $stmt->fetchAll());
    }

    public function count(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM books')->fetchColumn();
    }

    public function countAvailable(): int {
        return (int)$this->db->query(
            'SELECT COUNT(*) FROM books WHERE available_copies > 0'
        )->fetchColumn();
    }

    public function search(string $query, ?string $category = null): array {
        $q = '%' . $query . '%';
        if ($category) {
            $stmt = $this->db->prepare(
                'SELECT * FROM books
                 WHERE category = ?
                   AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)
                 ORDER BY title'
            );
            $stmt->execute([$category, $q, $q, $q]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT * FROM books
                 WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? OR category LIKE ?
                 ORDER BY title'
            );
            $stmt->execute([$q, $q, $q, $q]);
        }
        return array_map([Book::class, 'fromArray'], $stmt->fetchAll());
    }

    public function filterByCategory(string $category): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM books WHERE category = ? ORDER BY title'
        );
        $stmt->execute([$category]);
        return array_map([Book::class, 'fromArray'], $stmt->fetchAll());
    }

    public function getAllCategories(): array {
        $stmt = $this->db->query('SELECT DISTINCT category FROM books ORDER BY category');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO books (title, author, isbn, category, total_copies, available_copies)
             VALUES (:title, :author, :isbn, :category, :total, :available)'
        );
        $stmt->execute([
            ':title'     => $data['title'],
            ':author'    => $data['author'],
            ':isbn'      => $data['isbn']      ?? null,
            ':category'  => $data['category'],
            ':total'     => $data['total_copies'],
            ':available' => $data['total_copies'],  // initially all available
        ]);
        return (int)Database::getInstance()->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE books SET title = :title, author = :author, isbn = :isbn,
                              category = :category, total_copies = :total
             WHERE id = :id'
        );
        return $stmt->execute([
            ':title'    => $data['title'],
            ':author'   => $data['author'],
            ':isbn'     => $data['isbn'] ?? null,
            ':category' => $data['category'],
            ':total'    => $data['total_copies'],
            ':id'       => $id,
        ]);
    }

    public function decrementAvailable(int $id): bool {
        $stmt = $this->db->prepare(
            'UPDATE books SET available_copies = available_copies - 1
             WHERE id = ? AND available_copies > 0'
        );
        return $stmt->execute([$id]);
    }

    public function incrementAvailable(int $id): bool {
        $stmt = $this->db->prepare(
            'UPDATE books SET available_copies = LEAST(available_copies + 1, total_copies)
             WHERE id = ?'
        );
        return $stmt->execute([$id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM books WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
