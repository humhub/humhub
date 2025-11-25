<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\driver;

use Firebase\JWT\JWT;
use humhub\modules\content\models\Content;
use humhub\modules\live\assets\LiveMercureAsset;
use humhub\modules\live\live\LegitimationChanged;
use humhub\modules\live\Module;
use humhub\modules\user\models\User;
use humhub\modules\live\components\LiveEvent;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\Jwt\FactoryTokenProvider;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Symfony\Component\Mercure\Update;
use yii\base\InvalidConfigException;
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
    public string $jwtKeySubscriber = '';
    public string $jwtKeyPublisher = '';
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

        if (empty($this->jwtKeyPublisher) || empty($this->jwtKeySubscriber)) {
            throw new InvalidConfigException('Mercure driver JWT keys are not specified.');
        }

        Yii::$app->view->registerAssetBundle(LiveMercureAsset::class);

        $jwFactory = new LcobucciFactory($this->jwtKeyPublisher);
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
        $update = new Update(
            $this->topicPrefix . $liveEvent->visibility . '-' . $liveEvent->contentContainerId,
            json_encode($liveEvent->getData()),
            true,
        );
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
                'jwt' => $this->generateJwtAuthorizationSubscriber(),
            ],
        ];
    }


    protected function generateJwtAuthorizationSubscriber()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        $token = [
            'mercure' => [
                'subscribe' => $this->getTopics(),
                'publish' => [],
            ],
            'exp' => time() + 60 * 60 * 6,
        ];

        return JWT::encode($token, $this->jwtKeySubscriber, 'HS256');
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

        /** @var Module $liveModule */
        $liveModule = Yii::$app->getModule('live');
        $legitimation = $liveModule->getLegitimateContentContainerIds(Yii::$app->user->getIdentity());

        $topics = [];

        $topicSuffixes = [
            Content::VISIBILITY_OWNER => [
                Content::VISIBILITY_OWNER,
                Content::VISIBILITY_PUBLIC,
                Content::VISIBILITY_PRIVATE,
            ],
            Content::VISIBILITY_PRIVATE => [Content::VISIBILITY_PUBLIC, Content::VISIBILITY_PRIVATE],
            Content::VISIBILITY_PUBLIC => [Content::VISIBILITY_PUBLIC],
        ];

        foreach ($legitimation as $visibility => $containerIds) {
            $visibilitiesToAdd = $topicSuffixes[$visibility];
            foreach ($containerIds as $containerId) {
                foreach ($visibilitiesToAdd as $topicVisibility) {
                    $topics[] = $this->topicPrefix . $topicVisibility . '-' . $containerId;
                }
            }
        }

        return $topics;
    }
}
