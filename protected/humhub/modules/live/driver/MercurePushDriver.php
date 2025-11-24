<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\driver;

use Firebase\JWT\JWT;
use humhub\modules\live\assets\LiveMercureAsset;
use humhub\modules\live\live\LegitimationChanged;
use humhub\modules\user\models\User;
use humhub\modules\live\components\LiveEvent;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\Jwt\FactoryTokenProvider;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Symfony\Component\Mercure\Update;
use Throwable;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use Yii;

/**
 * Mercure Push driver for live events
 *
 * @since 1.18
 * @author Luke
 */
class MercurePushDriver extends BaseDriver
{
    public string $hubUrl = 'https://localhost/.well-known/mercure';
    public string $jwtKey = '';
    public string $topicPrefix = '/humhub/live/';
    public bool $verifySsl = true;

    protected ?Hub $hub = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->hubUrl)) {
            throw new InvalidConfigException('Mercure driver hub URL is not specified.');
        }

        if (empty($this->jwtKey)) {
            throw new InvalidConfigException('Mercure driver JWT key is not specified.');
        }

        Yii::$app->view->registerAssetBundle(LiveMercureAsset::class);

        $jwFactory = new LcobucciFactory($this->jwtKey);
        $provider = new FactoryTokenProvider($jwFactory, publish: ['*']);
        $client = $this->verifySsl
            ? null
            : new CurlHttpClient([
                'verify_peer' => false,
                'verify_host' => false,
            ]);

        $this->hub = new Hub($this->hubUrl, $provider, null, null, $client);
    }

    /**
     * @inheritdoc
     */
    public function send(LiveEvent $liveEvent)
    {
        $update = new Update($this->topicPrefix . $liveEvent->contentContainerId, json_encode($liveEvent->getData()));
        $this->hub->publish($update);
    }

    /**
     * @inheritdoc
     */
    public function getJsConfig()
    {
        return [
            'type' => 'humhub.modules.live.mercure.MercureClient',
            'options' => [
                'url' => $this->hubUrl,
                'jwt' => $this->generateJwtAuthorization(),
                'topics' => $this->getTopics(),
            ],
        ];
    }

    /**
     * Generates an JWT authorization of the current user including
     * the contentContainer id legitmation.
     *
     * @return string the JWT string
     * @throws Throwable
     */
    protected function generateJwtAuthorization()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        $token = [
            'iss' => Url::to(['/'], true),
            'sub' => Yii::$app->user->id,
            'mercure' => [
                'subscribe' => $this->getTopics(),
            ],
        ];
        return JWT::encode($token, $this->jwtKey, 'HS256');
    }

    /**
     * @inheritdoc
     */
    public function onContentContainerLegitimationChanged(User $user, $legitimation = [])
    {
        $this->send(new LegitimationChanged([
            'contentContainerId' => $user->contentcontainer_id,
            'userId' => $user->id,
            'legitimation' => $legitimation,
        ]));
    }

    protected function getTopics(): array
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        $topics = [];
        $legitimation = Yii::$app->getModule('live')->getLegitimateContentContainerIds(Yii::$app->user->getIdentity());
        foreach ($legitimation as $ids) {
            foreach ($ids as $id) {
                $topics[] = $this->topicPrefix . $id;
            }
        }

        return $topics;
    }
}
