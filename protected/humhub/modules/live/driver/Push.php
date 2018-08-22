<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\driver;

use Firebase\JWT\JWT;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\base\InvalidConfigException;
use yii\redis\Connection;
use yii\di\Instance;
use humhub\modules\user\models\User;
use humhub\modules\live\driver\BaseDriver;
use humhub\modules\live\components\LiveEvent;
use humhub\modules\live\live\LegitimationChanged;

/**
 * Database driver for live events
 *
 * @since 1.3
 * @author Luke
 */
class Push extends BaseDriver
{

    /**
     * @var string the used Redis push channel
     */
    public $pushChannel = 'push';

    /**
     * @var string the URL to the push service
     */
    public $pushServiceUrl = '';

    /**
     * @var string the JWT secret key
     */
    public $jwtKey = '';

    /**
     * @var Connection|string|array the Redis [[Connection]] object or the application component ID of the Redis [[Connection]].
     * This can also be an array that is used to create a redis [[Connection]] instance in case you do not want do configure
     * redis connection as an application component.
     * After the Cache object is created, if you want to change this property, you should only assign it
     * with a Redis [[Connection]] object.
     */
    public $redis = 'redis';

    /**
     * Initializes the live push component.
     * This method will initialize the [[redis]] property to make sure it refers to a valid redis connection.
     * 
     * @throws \yii\base\InvalidConfigException if [[redis]] is invalid.
     */
    public function init()
    {
        parent::init();

        $this->redis = Instance::ensure($this->redis, Connection::class);

        if (empty($this->jwtKey)) {
            throw new InvalidConfigException('Push driver JWT key is not specified.');
        }
    }

    /**
     * @inheritdoc
     */
    public function send(LiveEvent $liveEvent)
    {
        $this->redis->publish($this->pushChannel, Json::encode($liveEvent->getData()));
    }

    /**
     * @inheritdoc
     */
    public function getJsConfig()
    {
        return [
            'type' => 'humhub.modules.live.push.PushClient',
            'options' => [
                'url' => $this->pushServiceUrl,
                'jwt' => $this->generateJwtAuthorization(),
            ]
        ];
    }

    /**
     * Generates an JWT authorization of the current user including 
     * the contentContainer id legitmation.
     * 
     * @return string the JWT string
     */
    public function generateJwtAuthorization()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        $user = Yii::$app->user->getIdentity();
        $token = [
            'iss' => Url::to(['/'], true),
            'sub' => Yii::$app->user->id,
            'legitmation' => Yii::$app->getModule('live')->getLegitimateContentContainerIds($user)
        ];
        return JWT::encode($token, $this->jwtKey);
    }

    /**
     * @inheritdoc
     */
    public function onContentContainerLegitimationChanged(User $user, $legitimation = [])
    {
        $this->send(new LegitimationChanged(['contentContainerId' => $user->contentcontainer_id, 'userId' => $user->id, 'legitimation' => $legitimation]));
    }

}
