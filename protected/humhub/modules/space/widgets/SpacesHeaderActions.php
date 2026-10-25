<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PageHeaderActions;
use Yii;

/**
 * The actions of the Spaces page header.
 *
 * @since 1.20
 */
class SpacesHeaderActions extends PageHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'spaces-header-actions';

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
                'isPrimary' => true,
                'htmlOptions' => [
                    'data-action-click' => 'ui.modal.load',
                ],
            ]));
        }

        parent::init();
    }
}
