<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\Menu;
use humhub\modules\user\models\forms\Invite;
use Yii;

/**
 * PeopleHeadingButtons shows buttons on the heading of the people page
 *
 * Since 1.20 the People directory renders its entries as toolbar actions
 * ({@see \humhub\widgets\menu\Menu::getEntriesData()}, see {@see PeopleDirectory}); the
 * template is only used where the menu is still rendered as a widget.
 *
 * @since 1.11
 * @author Funkycram
 */
class PeopleHeadingButtons extends Menu
{
    /**
     * @inheritdoc
     */
    public $id = 'people-heading-buttons';

    /**
     * @inheritdoc
     */
    public $template = 'peopleHeadingButtonsTemplate';

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
                'htmlOptions' => [
                    'class' => 'btn-accent',
                    'data-action-click' => 'ui.modal.load',
                ],
            ]));
        }

        parent::init();
    }
}
