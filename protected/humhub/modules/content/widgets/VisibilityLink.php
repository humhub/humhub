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
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\helpers\Url;

/**
 * The entry of a content's context menu ({@see WallEntryControls}) that switches the content
 * between public and private.
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 1.2
 */
class VisibilityLink extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $contentRecord;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $content = $this->contentRecord->content;

        if (!$this->canToggle()) {
            $this->setIsVisible(false);
            return;
        }

        $private = $content->isPrivate();

        $this->setLabel($private
            ? Yii::t('ContentModule.base', 'Change to "Public"')
            : Yii::t('ContentModule.base', 'Change to "Private"'));
        $this->setIcon($private ? 'lock-open' : 'lock');
        $this->setUrl('#');
        $this->setHtmlOptions([
            'class' => $private ? 'makePublicLink' : 'makePrivateLink',
            'data-action-click' => 'toggleVisibility',
            'data-action-url' => Url::to(['/content/content/toggle-visibility', 'id' => $content->id]),
        ]);
    }

    private function canToggle(): bool
    {
        $content = $this->contentRecord->content;

        if (!$content->canEdit()) {
            return false;
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
            return false;
        }

        // Prevent Change to "Public" if content is global and Guest access is disabled
        if (
            $content->container === null
            && $content->isPrivate()
            && !AuthHelper::isGuestAccessEnabled()
        ) {
            return false;
        }

        return true;
    }
}
