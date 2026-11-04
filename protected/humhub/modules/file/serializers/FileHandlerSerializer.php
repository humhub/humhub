<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\serializers;

use humhub\components\icon\TablerIconProvider;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\handler\FileHandlerCollection;
use yii\helpers\ArrayHelper;

/**
 * The file handlers a user may use to add a file - the platform's "upload this type" entries
 * and what modules contribute ("Create document", "Import from …") - as data for the Vue
 * islands (`UploadField`, `RichTextField`): they render each one as a link carrying the
 * handler's own attributes, so its legacy action (`data-action-click`) runs as before.
 *
 * Shipped once per page in the core JS config (`file.handlers`, see
 * {@see \humhub\widgets\CoreJsConfig}) rather than with every island that offers them: they
 * depend on the user, not on what is being edited.
 *
 * @since 1.20
 */
class FileHandlerSerializer
{
    /**
     * The import and create handlers of the current user.
     */
    public static function addHandlers(): array
    {
        return array_map(
            [static::class, 'handler'],
            FileHandlerCollection::getByType([FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE]),
        );
    }

    /**
     * One handler: its label as text, its icon's Tabler name (if it declares one, as the built-in
     * and most module handlers do - `$icon`, a Font Awesome 4 name resolved like
     * {@see \humhub\widgets\Icon} does) and the link attributes it renders with.
     *
     * The label a handler returns may carry markup - an icon in front of it, a `<small>` hint -
     * which is reduced to its text; the icon comes from `$icon` instead.
     *
     * @return array{label: string, icon: string|null, attributes: array}
     */
    public static function handler(BaseFileHandler $handler): array
    {
        $attributes = $handler->getLinkAttributes();
        $label = (string)ArrayHelper::remove($attributes, 'label', '');
        unset($attributes['class']);

        if (isset($attributes['url'])) {
            $attributes['href'] = ArrayHelper::remove($attributes, 'url');
        }

        $icon = $handler->canGetProperty('icon') ? $handler->icon : null;

        return [
            'label' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($label), ENT_QUOTES | ENT_HTML5))),
            'icon' => is_string($icon) && $icon !== '' ? TablerIconProvider::resolveName($icon) : null,
            'attributes' => array_map(
                static fn($value) => is_array($value) ? json_encode($value) : (string)$value,
                $attributes,
            ),
        ];
    }
}
