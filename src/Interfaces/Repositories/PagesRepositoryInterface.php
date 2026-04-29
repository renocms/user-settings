<?php

namespace Reno\CmsUserSettings\Interfaces\Repositories;

use Reno\CmsUserSettings\Interfaces\Pages\PageInterface;

interface PagesRepositoryInterface
{
    /**
     * @return array<PageInterface>
     */
    public function getAll(): array;

    public function findByName(string $name): PageInterface;

    public function has(string $name): bool;
}
