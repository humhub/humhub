<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\helpers\Url;

/**
 * The "Delete" entry of a content's context menu ({@see WallEntryControls}).
 *
 * Deleting someone else's content opens the admin dialog (reason, notify the author) instead
 * of the plain confirmation.
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()} for the
 * `[DeleteLink::class, [...], [...]]` form modules keep using.
 *
 * @since 0.5
 */
class DeleteLink extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $content = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $content = $this->content->content;

        if (!$content->canEdit()) {
            $this->setIsVisible(false);
            return;
        }

        $isAdmin = $content->created_by !== Yii::$app->user->id;

        $this->setLabel(Yii::t('ContentModule.base', 'Delete'));
        $this->setIcon('delete');
        $this->setUrl('#');
        $this->setHtmlOptions([
            'data-action-click' => $isAdmin ? 'adminDelete' : 'delete',
            'data-content-delete-url' => $isAdmin
                ? Url::to(['/content/content/admin-delete'])
                : Url::to(['/content/content/delete']),
        ]);
    }
}
