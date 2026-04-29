<?php

namespace Reno\CmsUserSettings\Plugins\Menu;

use Reno\Cms\Plugins\Menu\AbstractTopMenuItem;

class UserSettingPageMenuItem extends AbstractTopMenuItem
{
    public function __construct(
        private readonly string $name,
        private readonly string $label,
    )
    {
    }

    public function getId(): string
    {
        return 'user-settings-' . $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPath(): ?string
    {
        return 'settings/user-settings/' . $this->name;
    }

    public function getParentId(): ?string
    {
        return 'settings';
    }

    public function getOrder(): int
    {
        return 100;
    }

    public function getIcon(): ?string
    {
        return null;
    }

    public function isVisible(): bool
    {
        return true;
    }
}
