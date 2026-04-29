<?php

namespace Reno\CmsUserSettings\Http\Requests\UserSettings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Reno\Cms\Helpers\TablePrefixHelper;

class UserSettingPageShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'context_id' => [
                'required',
                'integer',
                Rule::exists(TablePrefixHelper::table('contexts'), 'id'),
            ],
        ];
    }
}
