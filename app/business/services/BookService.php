<?php
// ============================================================
// ULMS — Book Service  (FR-05 — Member 2)
// Business Layer: business/services/BookService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/BookRepository.php';
require_once __DIR__ . '/../validators/BookValidator.php';
require_once __DIR__ . '/LogService.php';

class BookService {

    private BookRepository $repo;
    private BookValidator  $validator;
    private LogService     $logService;

    public function __construct() {
        $this->repo       = new BookRepository();
        $this->validator  = new BookValidator();
        $this->logService = new LogService();
    }

    public function addBook(array $data): array {
        if (!$this->validator->validate($data)) {
            return ['success' => false, 'message' => $this->validator->getFirstError()];
        }

        $id = $this->repo->create($data);
        $this->logService->log('BOOK_ADDED', "Added book: '{$data['title']}' by {$data['author']} (ID: $id).");

        return ['success' => true, 'message' => 'Book added successfully.', 'id' => $id];
    }

    public function updateBook(int $id, array $data): array {
        if (!$this->validator->validate($data)) {
            return ['success' => false, 'message' => $this->validator->getFirstError()];
        }

        $book = $this->repo->findById($id);
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found.'];
        }

        $this->repo->update($id, $data);
        $this->logService->log('BOOK_UPDATED', "Updated book ID $id: '{$data['title']}'.");

        return ['success' => true, 'message' => 'Book updated successfully.'];
    }

    public function deleteBook(int $id): array {
        $book = $this->repo->findById($id);
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found.'];
        }

        $this->repo->delete($id);
        $this->logService->log('BOOK_DELETED', "Deleted book: '{$book->title}' (ID: $id).");

        return ['success' => true, 'message' => "Book '{$book->title}' deleted."];
    }

    public function getBookById(int $id): ?object {
        return $this->repo->findById($id);
    }

    public function getAllBooks(int $limit = 100, int $offset = 0): array {
        return $this->repo->findAll($limit, $offset);
    }

    public function searchBooks(string $query, ?string $category = null): array {
        return $this->repo->search($query, $category);
    }

    public function filterByCategory(string $category): array {
        return $this->repo->filterByCategory($category);
    }

    public function getAllCategories(): array {
        return $this->repo->getAllCategories();
    }

    public function getBookStats(): array {
        return [
            'total'     => $this->repo->count(),
            'available' => $this->repo->countAvailable(),
        ];
    }
}
