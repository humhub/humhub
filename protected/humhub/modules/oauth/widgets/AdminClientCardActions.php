<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The header actions of the administration's client table: "Add client".
 *
 * @since 1.20
 */
class AdminClientCardActions extends CardHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'oauth-admin-client-card-actions';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'id' => 'add-client',
            'label' => Yii::t('OauthModule.base', 'Add client'),
            'url' => ['/oauth/admin/client'],
            'icon' => 'plus',
            'isPrimary' => true,
            'sortOrder' => 100,
        ]));

        parent::init();
    }
}
