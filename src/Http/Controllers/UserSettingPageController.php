<?php

namespace Reno\CmsUserSettings\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\CmsUserSettings\Http\Requests\UserSettings\UserSettingPageShowRequest;
use Reno\CmsUserSettings\Http\Requests\UserSettings\UserSettingPageUpdateRequest;
use Reno\CmsUserSettings\Http\Resources\UserSettings\UserSettingPageResource;
use Reno\CmsUserSettings\Interfaces\Repositories\PagesRepositoryInterface;
use Reno\CmsUserSettings\Interfaces\Repositories\UserSettingValueRepositoryInterface;

class UserSettingPageController
{
    public function __construct(
        private readonly PagesRepositoryInterface $pagesRepository,
        private readonly UserSettingValueRepositoryInterface $userSettingValueRepository,
    )
    {
    }

    public function show(string $name, UserSettingPageShowRequest $request): JsonResponse
    {
        $page = $this->pagesRepository->findByName($name);
        $contextId = (int) $request->validated('context_id');
        $values = $this->userSettingValueRepository->getContextMap($contextId);

        return UserSettingPageResource::make([
            'page' => $page,
            'values' => $values,
            'context_id' => $contextId,
        ])->response();
    }

    public function update(string $name, UserSettingPageUpdateRequest $request): JsonResponse
    {
        $this->pagesRepository->findByName($name);

        $validated = $request->validated();
        $contextId = (int) $validated['context_id'];
        $values = $validated['values'];
        $userId = $request->user()?->getKey();

        $this->userSettingValueRepository->upsertMany($contextId, $values, is_int($userId) ? $userId : null);

        return response()->json([
            'data' => $this->userSettingValueRepository->getContextMap($contextId),
        ]);
    }
}
