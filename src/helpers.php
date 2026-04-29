<?php

use Reno\Cms\Containers\ContextContainer;
use Reno\CmsUserSettings\Interfaces\Repositories\UserSettingValueRepositoryInterface;

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null, ?int $contextId = null): mixed
    {
        $resolvedContextId = $contextId;

        if ($resolvedContextId === null && app()->bound('cms.current_context')) {
            $currentContext = app('cms.current_context');
            if ($currentContext instanceof ContextContainer) {
                $resolvedContextId = $currentContext->getId();
            }
        }

        if ($resolvedContextId === null) {
            $request = request();
            $requestContextId = $request->attributes->get('cms_context_id');
            if (is_numeric($requestContextId)) {
                $resolvedContextId = (int) $requestContextId;
            }
        }

        if ($resolvedContextId === null) {
            return $default;
        }

        /** @var UserSettingValueRepositoryInterface $repository */
        $repository = app(UserSettingValueRepositoryInterface::class);

        return $repository->getByKey($resolvedContextId, $key, $default);
    }
}
