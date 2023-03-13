<?php
namespace App\Repository;

use App\Entity\Url;

interface UrlRepositoryInterface {
    public function get(string $labelWithId): ?Url;
    public function save(Url $entity, bool $flush = true): void;
    public function remove(Url $entity, bool $flush = true): void;
    public function removeById(string $id): void;
}