<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\user\models\User;
use humhub\modules\notification\components\BaseNotification;
use Yii;
use yii\di\NotInstantiableException;

/**
 * Mobile Target
 * 
 * @since 1.2
 * @author buddha
 */
class MobileTarget extends BaseTarget
{

    /**
     * @inheritdoc
     */
    public $id = 'mobile';

    /**
     * @var MobileTargetProvider
     */
    public $provider;

    public function init()
    {
        parent::init();

        try {
            $this->provider = Yii::$container->get(MobileTargetProvider::class);
        } catch (NotInstantiableException $e) {
            // No provider given
        }
    }

    /**
     * Used to forward a BaseNotification object to a BaseTarget.
     * The notification target should handle the notification by pushing a Job to
     * a Queue or directly handling the notification.
     * 
     * @param BaseNotification $notification
     */
    public function handle(BaseNotification $notification, User $user)
    {
        if($this->provider) {
            $this->provider->handle($notification, $user);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('NotificationModule.targets', 'Mobile');
    }

    public function isActive(User $user = null)
    {
        if(!$this->provider) {
            return false;
        }

        return $this->provider->isActive($user);
    }

}
