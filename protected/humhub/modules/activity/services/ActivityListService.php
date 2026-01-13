<?php

namespace humhub\modules\activity\services;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use Yii;

class ActivityListService
{
    public function __construct(
        private User $user,
        private ?ContentContainer $contentContainer,
        bool $useMailFilters = false
    ) {
    }

    /**
     * @return BaseActivity[]
     */
    public function getList(?int $limit = 10, ?int $lastActivityId = null): array
    {
        $result = [];

        $query = Activity::find()
            ->limit($limit)
            ->excludeUser($this->user)
            ->visible();

        if ($this->contentContainer !== null) {
            $query->contentContainer($this->contentContainer, $this->user);
        } else {
            $query->subscribedContentContainers($this->user);
        }

        foreach ($query->all() as $activity) {
            $result[] = Yii::createObject($activity->class, ['record' => $activity]);
        }

        return $result;
    }

    public function getRenderedWeb(?int $limit = 10, ?int $lastActivityId = null, bool $cached = true): array
    {
        $result = [];
        foreach ($this->getList($limit, $lastActivityId) as $activity) {
            $result[] = $activity->renderWeb();
        }

        return $result;
    }

}
