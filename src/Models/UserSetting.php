<?php

namespace Reno\CmsUserSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Reno\Cms\Helpers\TablePrefixHelper;

class UserSetting extends Model
{
    protected $fillable = [
        'context_id',
        'key',
        'value',
        'created_by',
        'edited_by',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public static function getTableName(): string
    {
        return TablePrefixHelper::table('user_settings');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(\Reno\Cms\Models\Context::class);
    }
}
