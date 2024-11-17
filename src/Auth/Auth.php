<?php

declare(strict_types=1);

namespace SdFramework\Auth;

use SdFramework\Session\Session;
use SdFramework\Database\Connection;

class Auth
{
    private Connection $db;
    private string $table = 'users';
    private array $user;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->checkSession();
    }

    private function checkSession(): void
    {
        $userId = Session::get('auth_user_id');
        if ($userId) {
            $user = $this->db->table($this->table)
                ->where('id', '=', $userId)
                ->first();
            
            if ($user) {
                $this->user = $user;
            }
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->db->table($this->table)
            ->where('email', '=', $email)
            ->first();

        if ($user && password_verify($password, $user['password'])) {
            $this->login($user);
            return true;
        }

        return false;
    }

    public function login(array $user): void
    {
        Session::regenerate();
        Session::set('auth_user_id', $user['id']);
        $this->user = $user;
    }

    public function logout(): void
    {
        Session::remove('auth_user_id');
        Session::regenerate();
        $this->user = [];
    }

    public function register(array $data): bool
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->db->table($this->table)->insert($data);
    }

    public function check(): bool
    {
        return !empty($this->user);
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?array
    {
        return $this->user ?? null;
    }

    public function id(): ?int
    {
        return $this->user['id'] ?? null;
    }
}
