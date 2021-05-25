<?php

namespace dashboard\unit;

use dashboard\DashboardStreamTest;
use humhub\modules\content\models\Content;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class DashboardGuestStreamQueryTest extends DashboardStreamTest
{

    public function testGuestDoesSeePublicContentOnPublicSpace()
    {
        $content = $this->createContent(Content::VISIBILITY_PUBLIC, $this->getSpaceByVisibility(Space::VISIBILITY_ALL));
        $stream = $this->fetchDashboardContent();
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    public function testGuestDoesSeePublicContentOnPublicUserProfile()
    {
        $content = $this->createContent(Content::VISIBILITY_PUBLIC, $this->getUserByVisibility(User::VISIBILITY_ALL));
        $stream = $this->fetchDashboardContent();
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    public function testGuestDoesNotSeePrivateContentOnPublicUserProfile()
    {
        $content = $this->createContent(Content::VISIBILITY_PRIVATE, $this->getUserByVisibility(User::VISIBILITY_ALL));
        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoesSeePublicContentOnMembersOnlyUserProfile()
    {
        $content = $this->createContent(Content::VISIBILITY_PUBLIC, $this->getUserByVisibility(User::VISIBILITY_REGISTERED_ONLY));
        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoeSeePublicGlobalContent()
    {
        $content = $this->createContent(Content::VISIBILITY_PUBLIC);
        $stream = $this->fetchDashboardContent();
        static::assertCount(1, $stream);
        static::assertEquals($content->id, $stream[0]->id);
    }

    public function testGuestDoeNotSeePrivateGlobalContent()
    {
        $content = $this->createContent(Content::VISIBILITY_PRIVATE);
        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoesNotSeePublicContentOnOnlyMemberSpace()
    {
        $content = $this->createContent(Content::VISIBILITY_PRIVATE, $this->getSpaceByVisibility(Space::VISIBILITY_REGISTERED_ONLY));
        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoesNotSeePrivateContentOnPublicSpace()
    {
        $content = $this->createContent(Content::VISIBILITY_PRIVATE, $this->getSpaceByVisibility(Space::VISIBILITY_ALL));
        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoesNotSeePrivateContentOnPrivateSpace()
    {
        $content = $this->createContent(Content::VISIBILITY_PRIVATE, $this->getSpaceByVisibility(Space::VISIBILITY_NONE));
        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoesNotSeePublicContentOnPrivateSpace()
    {
        $content = $this->createContent(Content::VISIBILITY_PUBLIC, $this->getSpaceByVisibility(Space::VISIBILITY_NONE));
        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoesNotSeeArchivedContent()
    {
        $content = $this->createContent(Content::VISIBILITY_PUBLIC, $this->getSpaceByVisibility(Space::VISIBILITY_ALL));
        $content->updateAttributes( ['archived' => 1]);

        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }

    public function testGuestDoesNotSeePublicContentOfArchivedSpace()
    {
        $space = $this->getSpaceByVisibility(Space::VISIBILITY_ALL);
        $content = $this->createContent(Content::VISIBILITY_PUBLIC, $this->getSpaceByVisibility(Space::VISIBILITY_ALL));

        $space->archive();

        $stream = $this->fetchDashboardContent();
        static::assertCount(0, $stream);
    }
}
