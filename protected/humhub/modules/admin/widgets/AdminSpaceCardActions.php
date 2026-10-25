<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The header actions of the administration's space list: "Add new space".
 *
 * @since 1.20
 */
class AdminSpaceCardActions extends CardHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'admin-space-card-actions';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->user->can([CreatePublicSpace::class, CreatePrivateSpace::class])) {
            $this->addEntry(new MenuLink([
                'id' => 'add-space',
                'label' => Yii::t('AdminModule.space', 'Add new space'),
                'url' => ['/space/create'],
                'icon' => 'plus',
                'isPrimary' => true,
                'sortOrder' => 100,
                'htmlOptions' => [
                    'data-action-click' => 'ui.modal.load',
                ],
            ]));
        }

        parent::init();
    }
}
