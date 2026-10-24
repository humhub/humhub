<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\space\activities\MemberAddedActivity;

/**
 * A 1.19 subclass of a core activity overriding the deprecated getGroupingQuery(): no grouping,
 * although its parent declares one.
 */
class TestLegacyMemberAddedActivity extends MemberAddedActivity
{
    public function getGroupingQuery(): ?ActiveQueryActivity
    {
        return null;
    }
}
