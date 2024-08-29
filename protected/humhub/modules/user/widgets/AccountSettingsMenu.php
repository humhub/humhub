<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\authclient\interfaces\PrimaryClient;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\InvalidConfigException;

/**
 * Account Settings Tab Menu
 */
class AccountSettingsMenu extends TabMenu
{
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Basic Settings'),
            'url' => ['/user/account/edit-settings'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit-settings'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Connected Accounts'),
            'url' => ['/user/account/connected-accounts'],
            'sortOrder' => 300,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'connected-accounts'),
            'isVisible' => count($this->getSecondaryAuthProviders()) !== 0,
        ]));


        parent::init();
    }

    /**
     * Returns optional authclients
     *
     * @return ClientInterface[]
     * @throws InvalidConfigException
     */
    protected function getSecondaryAuthProviders()
    {
        $clients = [];
        foreach (Yii::$app->get('authClientCollection')->getClients() as $client) {
            if (!$client instanceof BaseFormAuth && !$client instanceof PrimaryClient) {
                $clients[] = $client;
            }
        }

        return $clients;
    }

}
