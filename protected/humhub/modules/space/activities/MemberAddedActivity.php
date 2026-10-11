<?php

namespace humhub\modules\space\activities;

use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\notification\components\Grouping;
use humhub\modules\space\components\BaseSpaceActivity;
use Yii;

class MemberAddedActivity extends BaseSpaceActivity implements ConfigurableActivityInterface
{
    public static function getTitle(): string
    {
        return Yii::t('SpaceModule.activities', 'Space member joined');
    }

    public static function getDescription(): string
    {
        return Yii::t('SpaceModule.activities', 'Whenever a new member joined one of your spaces.');
    }

    protected function getMessage(array $params): string
    {
        $isGrouped = $this->groupCount > 1;
        $isInSpace = $this->inSpaceContext();

        return match (true) {
            $isGrouped && $isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayNames} joined this Space.',
                $params,
            ),
            $isGrouped && !$isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayNames} joined the Space {spaceName}.',
                $params,
            ),
            !$isGrouped && $isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayName} joined this Space.',
                $params,
            ),
            !$isGrouped && !$isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayName} joined the Space {spaceName}.',
                $params,
            ),
        };
    }

    /**
     * @inheritdoc
     */
    protected function getRedactedMessage(array $params): ?string
    {
        return Yii::t('SpaceModule.base', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} joined a Space.', $params);
    }

    public function getUrl(bool $scheme = false): ?string
    {
        // Inside the space the space link is redundant, so a single membership
        // change links to the member's profile. Grouped entries reference
        // multiple members, and outside the space the entry highlights the
        // space ("joined the Space {spaceName}") — both keep the space link.
        if ($this->groupCount > 1 || !$this->inSpaceContext()) {
            return parent::getUrl($scheme);
        }

        return $this->user->getUrl($scheme);
    }

    /**
     * The memberships of one space.
     *
     * @inheritdoc
     */
    public static function grouping(): ?Grouping
    {
        return Grouping::byContainer();
    }
}
