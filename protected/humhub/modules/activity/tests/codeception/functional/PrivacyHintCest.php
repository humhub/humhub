<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace activity\functional;

use activity\FunctionalTester;
use humhub\modules\notification\components\NotificationPrivacy;
use humhub\modules\notification\Module;
use PHPUnit\Framework\Assert;
use Yii;
use yii\helpers\Url;

/**
 * The E-Mail Summaries pages point to the network's privacy mode while it hides content.
 */
class PrivacyHintCest
{
    public function testAdminPageShowsNoHintWithFullDetails(FunctionalTester $I)
    {
        $I->wantTo('see no privacy hint on the summary defaults with full details');
        $I->amAdmin();
        $I->amOnRoute('/activity/admin/defaults');

        $I->seeResponseCodeIs(200);
        $I->dontSeeElement('[data-privacy-hint]');
        $I->dontSee('Privacy mode:');
    }

    public function testAdminPageShowsTheHiddenContentWithALink(FunctionalTester $I)
    {
        $I->wantTo('see the privacy mode on the summary defaults with a link to the notification settings');
        $this->setPrivacy(NotificationPrivacy::Redacted);
        try {
            $I->amAdmin();
            $I->amOnRoute('/activity/admin/defaults');

            $I->seeElement('.alert-info[data-privacy-hint="redacted"]');
            $I->see('Privacy mode: Hide content. E-mail summaries are sent without content. Change it under Notifications.', '[data-privacy-hint]');
            Assert::assertSame(Url::to(['/notification/admin/defaults']), $I->grabAttributeFrom('[data-privacy-hint] a', 'href'));
        } finally {
            $this->setPrivacy(NotificationPrivacy::Full);
        }
    }

    public function testAdminPageShowsAMinimalModeOfTheConfigurationWithoutLink(FunctionalTester $I)
    {
        $I->wantTo('see a privacy mode of the configuration file on the summary defaults without a link');
        $this->module()->privacyMode = NotificationPrivacy::Minimal->value;
        try {
            $I->amAdmin();
            $I->amOnRoute('/activity/admin/defaults');

            $I->see('Privacy mode: Minimal. E-mail summaries only show the number of new activities. It is set in the configuration file.', '[data-privacy-hint="minimal"]');
            $I->dontSeeElement('[data-privacy-hint] a');
        } finally {
            $this->module()->privacyMode = null;
        }
    }

    public function testAccountPageShowsNoHintWithFullDetails(FunctionalTester $I)
    {
        $I->wantTo('see no privacy hint on my summary settings with full details');
        $I->amUser2();
        $I->amOnRoute('/activity/user');

        $I->seeResponseCodeIs(200);
        $I->dontSeeElement('[data-privacy-hint]');
        $I->dontSee('Your network sends e-mail summaries');
    }

    public function testAccountPageShowsTheHint(FunctionalTester $I)
    {
        $I->wantTo('see on my summary settings that the network hides the content');
        $this->setPrivacy(NotificationPrivacy::Redacted);
        try {
            $I->amUser2();
            $I->amOnRoute('/activity/user');

            $I->see('Your network sends e-mail summaries without content.', '.alert-info[data-privacy-hint="redacted"]');

            $this->setPrivacy(NotificationPrivacy::Minimal);
            $I->amOnRoute('/activity/user');

            $I->see('Your network sends e-mail summaries only with the number of new activities.', '[data-privacy-hint="minimal"]');
        } finally {
            $this->setPrivacy(NotificationPrivacy::Full);
        }
    }

    private function setPrivacy(NotificationPrivacy $privacy): void
    {
        $this->module()->setPrivacy($privacy);
    }

    private function module(): Module
    {
        return Yii::$app->getModule('notification');
    }
}
