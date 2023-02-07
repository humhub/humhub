<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use humhub\modules\user\models\forms\Invite;
use Yii;

/**
 * PeopleHeadingButtons shows buttons on the heading of the people page
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
                'sortOrder' => 100,
                'icon' => 'invite',
                'htmlOptions' => ['data-action-click' => 'ui.modal.load'],
            ]));
        }

        parent::init();
    }
}
