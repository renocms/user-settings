<?php

namespace Reno\CmsUserSettings\Http\Resources\UserSettings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\FormElements\Tab;
use Reno\Cms\Interfaces\Forms\FieldInterface;
use Reno\Cms\Interfaces\Forms\FormElementInterface;
use Reno\CmsUserSettings\Interfaces\Pages\PageInterface;

class UserSettingPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array{page: PageInterface, values: array<string, mixed>, context_id: int} $payload */
        $payload = $this->resource;
        $page = $payload['page'];

        return [
            'name' => $page->getName(),
            'label' => $page->getLabel(),
            'context_id' => $payload['context_id'],
            'schema' => $this->normalizePageSchema($page->getSchema(), $page),
            'values' => $payload['values'],
        ];
    }

    /**
     * @param array<FormElementInterface> $schema
     * @return array<int, array<string, mixed>>
     */
    private function normalizePageSchema(array $schema, PageInterface $page): array
    {
        $normalized = $this->normalizeSchema($schema);

        return $this->wrapFlatSchemaInDefaultTabIfNeeded($normalized, $page);
    }

    /**
     * @param array<FormElementInterface> $schema
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSchema(array $schema): array
    {
        return array_values(array_map(
            fn (FormElementInterface $element) => $this->normalizeElement($element),
            $schema,
        ));
    }

    /**
     * Если на верхнем уровне только поля (без вкладок), объединяет их в одну вкладку с подписью страницы.
     *
     * @param array<int, array<string, mixed>> $normalized
     * @return array<int, array<string, mixed>>
     */
    private function wrapFlatSchemaInDefaultTabIfNeeded(array $normalized, PageInterface $page): array
    {
        if ($normalized === []) {
            return [];
        }

        foreach ($normalized as $item) {
            if (($item['element'] ?? '') === 'tab') {
                return $normalized;
            }
        }

        return [
            [
                'element' => 'tab',
                'name' => $page->getLabel(),
                'description' => '',
                'schema' => $normalized,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeElement(FormElementInterface $element): array
    {
        if ($element instanceof Tab) {
            return [
                'element' => 'tab',
                'name' => $element->getName(),
                'description' => $element->getDescription(),
                'schema' => $this->normalizeSchema($element->getSchema()),
            ];
        }

        if ($element instanceof FieldInterface) {
            return [
                'element' => 'field',
                'id' => $element->getKey(),
                'key' => $element->getKey(),
                'name' => $element->getName(),
                'description' => $element->getDescription(),
                'type' => $element->getFieldType()->getType(),
                'is_required' => in_array('required', $element->getValidationRules(), true),
                'sort_order' => 0,
                'js_module' => $element->getFieldType()->getJsModule(),
                'configuration' => $element->getConfiguration(),
            ];
        }

        throw new \RuntimeException('Неподдерживаемый элемент схемы: ' . $element::class);
    }
}
