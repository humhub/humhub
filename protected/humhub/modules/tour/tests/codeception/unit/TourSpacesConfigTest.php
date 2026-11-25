<?php

namespace tests\codeception\unit\modules\tour;

use humhub\modules\space\models\Space;
use humhub\modules\tour\TourConfig;
use tests\codeception\_support\HumHubDbTestCase;

class TourSpacesConfigTest extends HumHubDbTestCase
{
    public function testSpaceAdminGetsGuideInManagedSpace()
    {
        $this->becomeUser('User1');

        $config = $this->getSpacesTourConfig();

        $this->assertNotNull($config);
        $this->assertSame(
            Space::findOne(['id' => 2])->createUrl('/space/space', ['tour' => true]),
            TourConfig::getStartUrl($config),
        );
    }

    public function testSpaceMemberGetsGuide()
    {
        // User2 is a member of spaces but manages none of them
        $this->becomeUser('User2');

        $this->assertSpacesTourInVisibleSpace($this->getSpacesTourConfig());
    }

    public function testUserWithoutMembershipGetsGuide()
    {
        // User3 is not a member of any space
        $this->becomeUser('User3');

        $this->assertSpacesTourInVisibleSpace($this->getSpacesTourConfig());
    }

    public function testGuideIsHiddenWithoutVisibleSpace()
    {
        Space::updateAll(['visibility' => Space::VISIBILITY_NONE]);
        $this->becomeUser('User3');

        $this->assertNull($this->getSpacesTourConfig());
    }

    private function getSpacesTourConfig(): ?array
    {
        return TourConfig::get()[TourConfig::TOUR_ID_SPACES] ?? null;
    }

    private function assertSpacesTourInVisibleSpace(?array $config): void
    {
        $this->assertNotNull($config, 'Spaces guide is not available');

        $startUrl = TourConfig::getStartUrl($config);
        $this->assertNotNull($startUrl);

        $space = null;
        foreach (Space::find()->all() as $candidate) {
            if ($candidate->createUrl('/space/space', ['tour' => true]) === $startUrl) {
                $space = $candidate;
            }
        }
        $this->assertNotNull($space, 'Start URL does not point to a space');
        $this->assertNotEquals(Space::VISIBILITY_NONE, $space->visibility);
        $this->assertEquals(Space::STATUS_ENABLED, $space->status);
    }
}
