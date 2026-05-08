<?php

namespace Reno\CmsUserSettings;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Reno\Cms\Events\AdminApiRoutesRegistering;
use Reno\Cms\Events\JsTranslationFilesRegistering;
use Reno\Cms\Events\JavascriptRoutesRegistering;
use Reno\Cms\Events\PermissionsRegistering;
use Reno\Cms\Events\TopMenuItemsRegistering;
use Reno\CmsUserSettings\Http\Controllers\UserSettingPageController;
use Reno\CmsUserSettings\Interfaces\Repositories\PagesRepositoryInterface;
use Reno\CmsUserSettings\Interfaces\Repositories\UserSettingValueRepositoryInterface;
use Reno\CmsUserSettings\Plugins\Menu\UserSettingPageMenuItem;
use Reno\CmsUserSettings\Plugins\Routes\UserSettingPageRoute;
use Reno\CmsUserSettings\Repositories\PagesRepository;
use Reno\CmsUserSettings\Repositories\UserSettingValueRepository;

class UserSettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/user-settings.php', 'user-settings');

        $this->app->singleton(PagesRepositoryInterface::class, PagesRepository::class);
        $this->app->singleton(UserSettingValueRepositoryInterface::class, UserSettingValueRepository::class);

        $this->registerAdminApiRoutes();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cms-user-settings');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'user-settings-migrations');

        $this->publishes([
            __DIR__ . '/../config/user-settings.php' => config_path('user-settings.php'),
        ], 'cms-config');

        $this->publishes([
            __DIR__ . '/../public/build' => public_path('js/reno/cms-user-settings/build'),
        ], 'cms-assets');

        $this->registerMenuItems();
        $this->registerJavascriptRoutes();
        $this->registerPermissions();
        $this->registerJsTranslations();
    }

    private function registerMenuItems(): void
    {
        Event::listen(TopMenuItemsRegistering::class, function (TopMenuItemsRegistering $event): void {
            /** @var PagesRepositoryInterface $pagesRepository */
            $pagesRepository = app(PagesRepositoryInterface::class);

            foreach ($pagesRepository->getAll() as $page) {
                $event->add(new UserSettingPageMenuItem(
                    name: $page->getName(),
                    label: $page->getLabel(),
                ));
            }
        });
    }

    private function registerJavascriptRoutes(): void
    {
        Event::listen(JavascriptRoutesRegistering::class, function (JavascriptRoutesRegistering $event): void {
            /** @var PagesRepositoryInterface $pagesRepository */
            $pagesRepository = app(PagesRepositoryInterface::class);

            foreach ($pagesRepository->getAll() as $page) {
                $event->add(new UserSettingPageRoute($page->getName()));
            }
        });
    }

    private function registerPermissions(): void
    {
        Event::listen(PermissionsRegistering::class, function (PermissionsRegistering $event): void {
            $slug = (string) config('user-settings.permission_slug', 'settings.user-settings.manage');
            $event->addPermission($slug, 'settings');
        });
    }

    private function registerAdminApiRoutes(): void
    {
        Event::listen(AdminApiRoutesRegistering::class, function (): void {
            $permissionSlug = (string) config('user-settings.permission_slug', 'settings.user-settings.manage');

            Route::middleware("cms.permission:{$permissionSlug}")
                ->prefix('/user-settings/pages')
                ->group(function (): void {
                    Route::get('/{name}', [UserSettingPageController::class, 'show']);
                    Route::put('/{name}', [UserSettingPageController::class, 'update']);
                });
        });
    }

    private function registerJsTranslations(): void
    {
        Event::listen(JsTranslationFilesRegistering::class, function (JsTranslationFilesRegistering $event): void {
            $locale = $event->getLocale();

            $event->addFile(__DIR__ . '/../resources/lang/' . $locale . '/permissions.php');
            $event->addFile(__DIR__ . '/../resources/lang/' . $locale . '/user-settings.php');
        });
    }

}
