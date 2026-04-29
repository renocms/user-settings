<?php

namespace Reno\CmsUserSettings\Interfaces\Repositories;

interface UserSettingValueRepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getContextMap(int $contextId): array;

    public function getByKey(int $contextId, string $key, mixed $default = null): mixed;

    /**
     * @param array<string, mixed> $values
     */
    public function upsertMany(int $contextId, array $values, ?int $userId = null): void;
}
