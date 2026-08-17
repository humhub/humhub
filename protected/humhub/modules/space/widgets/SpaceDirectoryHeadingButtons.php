<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;

/**
 * SpaceDirectoryHeadingButtons shows buttons on the heading of the Space Directory page
 *
 * @since 1.19
 */
class SpaceDirectoryHeadingButtons extends Menu
{
    /**
     * @inheritdoc
     */
    public $id = 'space-directory-heading-buttons';

    /**
     * @inheritdoc
     */
    public $template = 'spaceDirectoryHeadingButtons';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->user->can([CreatePublicSpace::class, CreatePrivateSpace::class])) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.chooser', 'Create Space'),
                'url' => ['/space/create/create'],
                'id' => 'create-space-button',
                'sortOrder' => 100,
                'icon' => 'plus',
                'htmlOptions' => [
                    'data-action-click' => 'ui.modal.load',
                ],
            ]));
        }

        parent::init();
    }
}
