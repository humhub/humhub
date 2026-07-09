<?php

namespace humhub\modules\space\activities;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use humhub\modules\space\components\BaseSpaceActivity;
use Yii;

class MemberRemovedActivity extends BaseSpaceActivity implements ConfigurableActivityInterface
{
    public static function getTitle(): string
    {
        return Yii::t('SpaceModule.activities', 'Space member left');
    }

    public static function getDescription(): string
    {
        return Yii::t('SpaceModule.activities', 'Whenever a member leaves one of your spaces.');
    }

    protected function getMessage(array $params): string
    {
        $isGrouped = $this->groupCount > 1;
        $isInSpace = $this->inSpaceContext();

        return match (true) {
            $isGrouped && $isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayNames} left this Space.',
                $params,
            ),
            $isGrouped && !$isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayNames} left the Space {spaceName}.',
                $params,
            ),
            !$isGrouped && $isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayName} left this Space.',
                $params,
            ),
            !$isGrouped && !$isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayName} left the Space {spaceName}.',
                $params,
            ),
        };
    }

    public function getUrl(bool $scheme = true): ?string
    {
        // Inside the space the space link is redundant, so a single membership
        // change links to the member's profile. Grouped entries reference
        // multiple members, and outside the space the entry highlights the
        // space ("left the Space {spaceName}") — both keep the space link.
        if ($this->groupCount > 1 || !$this->inSpaceContext()) {
            return parent::getUrl($scheme);
        }

        return $this->user->getUrl($scheme);
    }

    public function getGroupingQuery(): ActiveQueryActivity
    {
        return Activity::find()
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id]);
    }
}
