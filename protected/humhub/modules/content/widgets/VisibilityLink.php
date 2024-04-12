<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\user\helpers\AuthHelper;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * Visibility link for Wall Entries can be used to switch form public to private and vice versa.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 1.2
 */
class VisibilityLink extends Widget
{
    /**
     * @var ContentActiveRecord
     */
    public $contentRecord;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = $this->contentRecord->content;

        if (!$content->canEdit()) {
            return '';
        }

        // Prevent Change to "Public" in private spaces
        if (
            $content->container
            && $content->isPrivate()
            && (
                !$content->container->visibility
                || !$content->container->permissionManager->can(new CreatePublicContent())
            )
        ) {
            return '';
        }

        // Prevent Change to "Public" if content is global and Guest access is disabled
        if (
            $content->container === null
            && $content->isPrivate()
            && !AuthHelper::isGuestAccessEnabled()
        ) {
            return '';
        }

        return $this->render('visibilityLink', [
            'content' => $content,
            'toggleLink' => Url::to(['/content/content/toggle-visibility', 'id' => $content->id]),
        ]);
    }
}
