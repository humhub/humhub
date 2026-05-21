<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\post\models\Post;

/**
 * Post Submodule
 *
 * @author Luke
 * @since 0.5
 */
class Module extends ContentContainerModule
{
    /**
     * Post title input is disabled.
     */
    public const TITLE_MODE_OFF = 'off';

    /**
     * Post title input is shown but optional.
     */
    public const TITLE_MODE_OPTIONAL = 'optional';

    /**
     * Post title input is shown and mandatory.
     */
    public const TITLE_MODE_REQUIRED = 'required';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\post\controllers';

    /**
     * @since 1.14
     * @var bool Automatically increase font size for short posts.
     */
    public bool $enableDynamicFontSize = false;

    /**
     * @since 1.15
     * @var int collapsed post block height
     */
    public int $collapsedPostHeight = 300;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer !== null) {
            return [
                new permissions\CreatePost(),
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getContentClasses(?ContentContainerActiveRecord $contentContainer = null): array
    {
        return [Post::class];
    }

    /**
     * Returns the configured post title mode, see `TITLE_MODE_*` constants.
     *
     * @since 1.19
     */
    public function getTitleMode(): string
    {
        return $this->settings->get('titleMode', self::TITLE_MODE_OFF);
    }
}
