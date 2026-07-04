<?php
// ULMS — ActivityLog Model (DTO)
// Data Layer: data/models/ActivityLog.php


class ActivityLog {
    public int    $id          = 0;
    public ?int   $user_id     = null;
    public string $username    = '';
    public string $role        = '';
    public string $action      = '';
    public string $description = '';
    public string $ip_address  = '';
    public string $created_at  = '';

    public static function fromArray(array $data): self {
        $l = new self();
        $l->id          = (int)($data['id']          ?? 0);
        $l->user_id     = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $l->username    =        $data['username']    ?? '';
        $l->role        =        $data['role']        ?? '';
        $l->action      =        $data['action']      ?? '';
        $l->description =        $data['description'] ?? '';
        $l->ip_address  =        $data['ip_address']  ?? '';
        $l->created_at  =        $data['created_at']  ?? '';
        return $l;
    }
}
