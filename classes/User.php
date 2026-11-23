<?php
declare(strict_types=1);

class User extends BaseModel
{
    public function getAll(): array
    {
        return $this->db->query("SELECT id_user, username FROM users ORDER BY username")->fetchAll();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->execute([':u' => $username]);
        return $stmt->fetch() ?: null;
    }

    /** @return array|null data user bila username & password cocok */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if ($user !== null && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }
}
