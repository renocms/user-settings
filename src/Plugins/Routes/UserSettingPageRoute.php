<?php

namespace Reno\CmsUserSettings\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class UserSettingPageRoute implements JavascriptRouteInterface
{
    public function __construct(
        private readonly string $name,
    )
    {
    }

    public function getName(): string
    {
        return 'user-settings-' . $this->name;
    }

    public function getPath(): string
    {
        return 'settings/user-settings/' . $this->name;
    }

    public function getJsModule(): string
    {
        return '/vendor/reno/cms-user-settings/build/components/user-settings/UserSettingPage.js';
    }

    public function getMeta(): array
    {
        return [
            'page_name' => $this->name,
        ];
    }
}
