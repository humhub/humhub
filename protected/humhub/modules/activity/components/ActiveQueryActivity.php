<?php

namespace humhub\modules\activity\components;

use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\live\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;

class ActiveQueryActivity extends ActiveQuery
{
    public function init()
    {
        parent::init();

        $this->leftJoin('content', 'content.id = activity.content_id');
        $this->leftJoin('user', 'user.id = activity.created_by');
    }

    public function visible(): static
    {
        $this->andWhere([
            'OR',
            'user.id IS NULL',
            [
                'AND',
                ['!=', 'user.status', User::STATUS_NEED_APPROVAL],
                ['!=', 'user.visibility', User::VISIBILITY_HIDDEN],
            ],
        ]);

        $this->andWhere([
            'OR',
            'content.id IS NULL',
            [
                'AND',
                ['=', 'content.state', Content::STATE_PUBLISHED],
            ],
        ]);

        return $this;
    }

    public function defaultScopes(User $user): static
    {
        $this->excludeUser($user);
        $this->visible();
        $this->orderBy(['activity.id' => SORT_DESC]);

        return $this;
    }

    public function excludeUser(User $user): static
    {
        // Exclude Own Activites
        $this->andWhere(['!=', 'activity.created_by', $user->id]);

        return $this;
    }

    public function contentContainer(ContentContainer $contentContainer, User $user): static
    {
        $legitimation = $this->getContainerLegitimation($user);

        $this->andWhere(['activity.contentcontainer_id' => $contentContainer->id]);

        if (in_array($contentContainer->id, $legitimation[Content::VISIBILITY_PRIVATE]) || in_array(
            $contentContainer->id,
            $legitimation[Content::VISIBILITY_OWNER],
        )) {
            // Allow all visibilities, for members
        } else {
            // Restrict to Public Content only
            $this->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);
        }

        return $this;
    }

    public function subscribedContentContainers(User $user): static
    {
        $legitimation = $this->getContainerLegitimation($user);

        // Fix Empty Array IN Condition Query Builder Problem
        if (empty($legitimation[Content::VISIBILITY_PUBLIC])) {
            $legitimation[Content::VISIBILITY_PUBLIC][] = -1;
        }

        $this->andWhere([
            'OR',
            // Content of Private/Owner in all Visibilities
            [
                'IN',
                'activity.contentcontainer_id',
                array_merge($legitimation[Content::VISIBILITY_PRIVATE], $legitimation[Content::VISIBILITY_OWNER]),
            ],
            // Content of Public, in Public Only
            [
                'AND',
                ['IN', 'activity.contentcontainer_id', $legitimation[Content::VISIBILITY_PUBLIC]],
                ['content.visibility' => Content::VISIBILITY_PUBLIC],
            ],
        ]);

        return $this;
    }


    private function getContainerLegitimation(User $user)
    {
        /** @var Module $liveModule */
        $liveModule = Yii::$app->getModule('live');
        $containerIds = $liveModule->getLegitimateContentContainerIds($user);

        return $containerIds;
    }

    public function mailLimitContentContainer(User $user): static
    {
        // Handle defined content container mode
        $limitContainer = $this->getLimitContentContainers($user);
        if (!empty($limitContainer)) {
            $mode = ($this->getLimitContentContainerMode(
                $user,
            ) == MailSummaryForm::LIMIT_MODE_INCLUDE) ? 'IN' : 'NOT IN';
            $this->andWhere([$mode, 'activity.contentcontainer_id', $limitContainer]);

            codecept_debug($limitContainer);
        }


        return $this;
    }

    /**
     * Returns the mode (exclude, include) of given content containers
     *
     * @return int mode
     * @see MailSummaryForm
     */
    private function getLimitContentContainerMode(User $user): int
    {
        /** @var \humhub\modules\activity\Module $activityModule */
        $activityModule = Yii::$app->getModule('activity');
        $default = $activityModule->settings->get('mailSummaryLimitSpacesMode', '');
        return $activityModule->settings->user($user)->get('mailSummaryLimitSpacesMode', $default);
    }

    /**
     * Returns a list of content containers which should be included or excluded.
     *
     * @return array list of contentcontainer ids
     */
    private function getLimitContentContainers(User $user): array
    {
        /** @var \humhub\modules\activity\Module $activityModule */
        $activityModule = Yii::$app->getModule('activity');

        $spaces = [];
        $defaultLimitSpaces = $activityModule->settings->get('mailSummaryLimitSpaces', '');
        $limitSpaces = $activityModule->settings->user($user)->get('mailSummaryLimitSpaces', $defaultLimitSpaces);
        foreach (explode(',', (string)$limitSpaces) as $guid) {
            $contentContainer = ContentContainer::findOne(['guid' => $guid]);
            if ($contentContainer !== null) {
                $spaces[] = $contentContainer->id;
            }
        }

        return $spaces;
    }


    public function mailLimitTypes(User $user): static
    {
        // Handle suppressed activities
        $suppressedActivities = $this->getSuppressedActivities($user);
        if (!empty($suppressedActivities)) {
            $this->andWhere(['NOT IN', 'activity.class', $suppressedActivities]);
        }

        return $this;
    }

    private function getSuppressedActivities(User $user): array
    {
        /** @var \humhub\modules\activity\Module $activityModule */
        $activityModule = Yii::$app->getModule('activity');

        $defaultActivitySuppress = $activityModule->settings->get('mailSummaryActivitySuppress', '');
        $activitySuppress = $activityModule->settings->user($user)->get(
            'mailSummaryActivitySuppress',
            $defaultActivitySuppress,
        );
        if (empty($activitySuppress)) {
            return [];
        }

        return explode(',', trim((string)$activitySuppress));
    }

    public function enableGrouping(): static
    {
        $this->addGroupBy('activity.grouping_key');
        $this->addSelect(['activity.*', 'count(*) as _group_count']);
        return $this;
    }

    public function timeBucket(int $bucketIntervalSeconds, string|\DateTimeInterface $dateTime): static
    {
        if ($bucketIntervalSeconds <= 0) {
            throw new \InvalidArgumentException('BucketIntervalSeconds must be greater than 0.');
        }

        if ($dateTime instanceof \DateTimeInterface) {
            $dt = ($dateTime instanceof \DateTime)
                ? \DateTimeImmutable::createFromMutable($dateTime)
                : $dateTime;
        } else {
            try {
                $dt = new \DateTimeImmutable($dateTime);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid date time given: '{$dateTime}'");
            }
        }

        $startTs = (int)(floor($dt->getTimestamp() / $bucketIntervalSeconds) * $bucketIntervalSeconds);
        $startDateTime = $dt->setTimestamp($startTs);

        $endDateTime = $startDateTime->modify("+{$bucketIntervalSeconds} seconds");

        return $this->andWhere([
            'AND',
            ['>=', 'activity.created_at', $startDateTime->format('Y-m-d H:i:s')],
            ['<', 'activity.created_at', $endDateTime->format('Y-m-d H:i:s')],
        ]);
    }
}
