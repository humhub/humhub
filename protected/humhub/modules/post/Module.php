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
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\post\controllers';

    /**
     * @since 1.14
     * @var bool Automatically increase font size for short posts.
     */
    public bool $enableDynamicFontSize = false;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer !== null) {
            return [
                new permissions\CreatePost()
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

}
