<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace activity\functional;

use humhub\modules\activity\components\MailSummary;
use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use activity\FunctionalTester;

class ActivityLinkCest
{
    public function testSimpleActivityLink(FunctionalTester $I)
    {
        $I->wantTo('the activity link works');
        $I->amAdmin();

        (new MailSummaryForm(['interval' => MailSummary::INTERVAL_NONE]))->save();
        (new MailSummaryForm([
            'user' => User::findOne(['id' => 2]),
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => [ContentCreated::class]
        ]))->save();

        $activity = TestActivity::instance()->about(Post::findOne(1))->create();

        $I->amOnRoute('/activity/link', ['id' => $activity->record->id]);
        $I->see('Account settings');
    }

    public function testNonViewableNotification(FunctionalTester $I)
    {
        $I->wantTo('the activity link works');
        $I->amAdmin();

        (new MailSummaryForm(['interval' => MailSummary::INTERVAL_NONE]))->save();
        (new MailSummaryForm([
            'user' => User::findOne(['id' => 2]),
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => [ContentCreated::class]
        ]))->save();

        $activity = TestActivity::instance()->about(Post::findOne(1))->create();

        $I->amUser1(true);
        $I->amOnRoute('/activity/link', ['id' => $activity->record->id]);
        $I->seeResponseCodeIs(403);
    }
}
