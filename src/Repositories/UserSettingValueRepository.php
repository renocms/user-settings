<?php

namespace Reno\CmsUserSettings\Repositories;

use Reno\CmsUserSettings\Interfaces\Repositories\UserSettingValueRepositoryInterface;
use Reno\CmsUserSettings\Models\UserSetting;

class UserSettingValueRepository implements UserSettingValueRepositoryInterface
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $contextSettingsCache = [];

    public function getContextMap(int $contextId): array
    {
        if (isset(self::$contextSettingsCache[$contextId])) {
            return self::$contextSettingsCache[$contextId];
        }

        $map = UserSetting::query()
            ->where('context_id', $contextId)
            ->get()
            ->mapWithKeys(function (UserSetting $setting): array {
                return [$setting->key => $this->decodeValue($setting->value)];
            })
            ->toArray();

        self::$contextSettingsCache[$contextId] = $map;

        return $map;
    }

    public function getByKey(int $contextId, string $key, mixed $default = null): mixed
    {
        $map = $this->getContextMap($contextId);

        return $map[$key] ?? $default;
    }

    public function upsertMany(int $contextId, array $values, ?int $userId = null): void
    {
        $existing = UserSetting::query()
            ->where('context_id', $contextId)
            ->get()
            ->keyBy('key');

        foreach ($values as $key => $value) {
            $encodedValue = $this->encodeValue($value);
            $model = $existing->get($key);

            if ($model instanceof UserSetting) {
                $model->update([
                    'value' => $encodedValue,
                    'edited_by' => $userId,
                    'edited_at' => now(),
                ]);
                continue;
            }

            UserSetting::query()->create([
                'context_id' => $contextId,
                'key' => $key,
                'value' => $encodedValue,
                'created_by' => $userId,
                'edited_by' => $userId,
                'edited_at' => now(),
            ]);
        }

        $this->clearContextCache($contextId);
    }

    private function clearContextCache(int $contextId): void
    {
        unset(self::$contextSettingsCache[$contextId]);
    }

    private function encodeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    private function decodeValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        if (($trimmed[0] ?? null) === '{' || ($trimmed[0] ?? null) === '[') {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }
}
