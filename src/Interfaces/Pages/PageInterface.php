<?php

namespace Reno\CmsUserSettings\Interfaces\Pages;

interface PageInterface
{
    public function getName(): string;

    public function getLabel(): string;

    public function getSchema(): array;
}
