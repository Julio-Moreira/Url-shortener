<?php
namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface {
    public function save(User $entity, bool $flush = true): void;
    public function remove(User $entity, bool $flush = true): void;
    public function get(string $email): ?User;
}