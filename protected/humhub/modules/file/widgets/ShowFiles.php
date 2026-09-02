<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\widgets;

use humhub\components\ActiveRecord;
use humhub\helpers\ThemeHelper;
use humhub\modules\file\assets\FileVueAsset;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\serializers\FileSerializer;
use humhub\widgets\VueComponent;
use Yii;
use yii\base\Widget;

/**
 * Shows the files attached to a record - the media grid (audio/video/image previews)
 * followed by the list of all attachments. Used as a wall entry addon for content, and
 * directly by modules rendering a record's attachments elsewhere.
 *
 * Since 1.20 this widget only renders the mount point of the `<attached-files>` Vue
 * island; the markup lives in `file/vue/AttachedFiles.vue`, which the comment island
 * renders its attachments with as well.
 *
 * Beside the serialized {@see FileSerializer::file()} shape - which is exactly what the
 * HTTP API ships - each file carries two presentation hints only a server-side caller
 * can resolve, and which the API therefore does not carry:
 *
 *  - `viewUrl` ({@see FileHelper::getViewUrl()}) depends on the file handlers modules
 *    contributed for this file, which may be permission dependent - it must not end up
 *    in a caller-neutral, cacheable payload (see docs/develop/concept-api.md).
 *  - `highlight` ({@see FileHelper::isSearchHighlighted()}) only means anything while
 *    rendering a search result page.
 *
 * @since 0.5
 */
class ShowFiles extends Widget
{
    /**
     * @var ActiveRecord Object to show files from
     */
    public $object = null;

    /**
     * @var bool if set to false this widget won't be rendered
     */
    public $active = true;

    /**
     * @var bool if set to false this widget won't render file previews as images/videos/audio
     */
    public $preview = true;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (!$this->active) {
            return '';
        }

        $files = $this->object->fileManager->findStreamFiles();

        if ($files === []) {
            return '';
        }

        return VueComponent::widget([
            'name' => 'AttachedFiles',
            'assetBundle' => FileVueAsset::class,
            'options' => [
                // hideOnEdit mandatory since 1.2 - the class sits on the mount tag itself
                // because the stream's inline edit removes `.stream-entry-addons > .hideOnEdit`,
                // a direct child selector (see humhub.stream.StreamEntry.js).
                'class' => 'hideOnEdit',
            ],
            'props' => [
                'files' => array_map($this->serializeFile(...), $files),
                'galleryId' => 'gallery-' . $this->object->getUniqueId(),
                'preview' => $this->preview,
                'excludeMedia' => $this->preview
                    && (bool)Yii::$app->getModule('file')->settings->get('excludeMediaFilesPreview'),
                'fluid' => ThemeHelper::isFluid(),
            ],
        ]);
    }

    private function serializeFile(File $file): array
    {
        $data = FileSerializer::file($file);

        $viewUrl = FileHelper::getViewUrl($file);
        if ($viewUrl !== null) {
            $data['viewUrl'] = $viewUrl;
        }

        if (FileHelper::isSearchHighlighted($file)) {
            $data['highlight'] = true;
        }

        return $data;
    }
}
