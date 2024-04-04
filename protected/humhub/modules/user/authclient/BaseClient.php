<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\services\AuthClientService;

/**
 * Extended BaseClient with additional events
 *
 * @since 1.1
 * @author luke
 */
class BaseClient extends \yii\authclient\BaseClient
{
    /**
     * @event Event an event raised on update user data.
     * @see AuthClientService::updateUser()
     */
    public const EVENT_UPDATE_USER = 'update';

    /**
     * @event Event an event raised on create user.
     * @see AuthClientService::createUser()
     */
    public const EVENT_CREATE_USER = 'create';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {

    }

    /**
     * Workaround for serialization into session during the registration process
     *
     * @return void
     */
    public function beforeSerialize(): void
    {
    }
}
