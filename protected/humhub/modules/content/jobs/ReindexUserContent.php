<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\services\SearchJobService;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\user\models\User;
use Yii;

class ReindexUserContent extends LongRunningActiveJob implements ExclusiveJobInterface
{
    public $userId;

    /**
     * @inhertidoc
     */
    public function getExclusiveJobId()
    {
        return 'content-search.reindex-user.' . $this->userId;
    }

    /**
     * @inhertidoc
     */
    public function run()
    {
        return $this->getService()->run(function () {
            $user = User::findOne(['id' => $this->userId]);
            if (!$user) {
                return;
            }

            $contents = Content::find()
                ->where(['created_by' => $user->id]);

            foreach ($contents->each() as $content) {
                if ($user->status === User::STATUS_ENABLED) {
                    $this->getSearchDriver()->update($content);
                } else {
                    $this->getSearchDriver()->delete($content);
                }
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $this->getService()->canRetry($attempt);
    }

    public function getService(): SearchJobService
    {
        return new SearchJobService();
    }

    private function getSearchDriver(): AbstractDriver
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('content');
        return $module->getSearchDriver();
    }
}
