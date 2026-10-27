<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\user\models\forms\Invite;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PageHeaderActions;
use Yii;

/**
 * The actions of the People page header.
 *
 * @since 1.20
 */
class PeopleHeaderActions extends PageHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'people-header-actions';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $invite = new Invite();
        if ($invite->canInviteByLink() || $invite->canInviteByEmail()) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('UserModule.base', 'Invite new people'),
                'url' => ['/user/invite'],
                'id' => 'invite-people-button',
                'sortOrder' => 100,
                'icon' => 'invite',
                'isPrimary' => true,
                'htmlOptions' => [
                    'data-action-click' => 'ui.modal.load',
                ],
            ]));
        }

        parent::init();
    }
}
