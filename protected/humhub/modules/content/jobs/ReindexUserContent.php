<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\services\SearchJobService;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\user\models\User;

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
                    (new ContentSearchService($content))->update(false);
                } else {
                    (new ContentSearchService($content))->delete(false);
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
}
