<?php
// ============================================================
// ULMS — User Model (DTO)
// Data Layer: data/models/User.php
// ============================================================

class User {
    public int    $id        = 0;
    public string $username  = '';
    public string $password  = '';
    public string $full_name = '';
    public string $email     = '';
    public string $role      = 'student';
    public int    $is_active = 1;
    public string $created_at = '';
    public string $updated_at = '';

    public static function fromArray(array $data): self {
        $u = new self();
        $u->id         = (int)($data['id']         ?? 0);
        $u->username   =       $data['username']   ?? '';
        $u->password   =       $data['password']   ?? '';
        $u->full_name  =       $data['full_name']  ?? '';
        $u->email      =       $data['email']      ?? '';
        $u->role       =       $data['role']       ?? 'student';
        $u->is_active  = (int)($data['is_active']  ?? 1);
        $u->created_at =       $data['created_at'] ?? '';
        $u->updated_at =       $data['updated_at'] ?? '';
        return $u;
    }

    public function toArray(): array {
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'full_name'  => $this->full_name,
            'email'      => $this->email,
            'role'       => $this->role,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
