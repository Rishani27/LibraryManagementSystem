<?php
// ============================================================
// ULMS — Log Service
// Business Layer: business/services/LogService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/LogRepository.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Helper.php';

class LogService {

    private LogRepository $repo;

    public function __construct() {
        $this->repo = new LogRepository();
    }

    /** Record an activity log entry */
    public function log(string $action, string $description = '', ?int $userId = null, ?string $username = null, ?string $role = null): void {
        $this->repo->create([
            'user_id'     => $userId     ?? Session::getUserId(),
            'username'    => $username   ?? Session::getUsername() ?? 'system',
            'role'        => $role       ?? Session::getRole()     ?? '',
            'action'      => $action,
            'description' => $description,
            'ip_address'  => Helper::getIp(),
        ]);
    }

    public function getAll(int $limit = 200, int $offset = 0): array {
        return $this->repo->findAll($limit, $offset);
    }

    public function getByUser(int $userId): array {
        return $this->repo->findByUser($userId);
    }

    public function getByAction(string $action): array {
        return $this->repo->findByAction($action);
    }

    public function getByDateRange(string $from, string $to): array {
        return $this->repo->findByDateRange($from, $to);
    }

    public function count(): int {
        return $this->repo->count();
    }
}
