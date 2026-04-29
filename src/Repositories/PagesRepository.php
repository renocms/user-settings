<?php

namespace Reno\CmsUserSettings\Repositories;

use Reno\Cms\Services\ClassesDiscoverer;
use Reno\CmsUserSettings\Interfaces\Pages\PageInterface;
use Reno\CmsUserSettings\Interfaces\Repositories\PagesRepositoryInterface;

class PagesRepository implements PagesRepositoryInterface
{
    /**
     * @var array<string, PageInterface>|null
     */
    private ?array $pages = null;

    public function __construct(
        private readonly ClassesDiscoverer $classesDiscoverer,
    )
    {
    }

    public function getAll(): array
    {
        if ($this->pages !== null) {
            return array_values($this->pages);
        }

        $path = config('user-settings.pages_path', app_path('Reno/UserSettings'));
        $discoveredClasses = $this->classesDiscoverer->discover($path);

        $pages = [];

        foreach ($discoveredClasses as $className) {
            if (!is_subclass_of($className, PageInterface::class)) {
                continue;
            }

            /** @var PageInterface $page */
            $page = app($className);
            $pages[$page->getName()] = $page;
        }

        $this->pages = $pages;

        return array_values($this->pages);
    }

    public function findByName(string $name): PageInterface
    {
        if ($this->pages === null) {
            $this->getAll();
        }

        if (!isset($this->pages[$name])) {
            throw new \RuntimeException("User setting page '{$name}' not found.");
        }

        return $this->pages[$name];
    }

    public function has(string $name): bool
    {
        if ($this->pages === null) {
            $this->getAll();
        }

        return isset($this->pages[$name]);
    }
}
