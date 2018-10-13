<?php
/**
 * Created by PhpStorm.
 * User: kingb
 * Date: 26.09.2018
 * Time: 18:59
 */

namespace humhub\modules\activity\tests\codeception\unit;


use Codeception\Module\Yii2;
use humhub\modules\activity\components\MailSummary;
use humhub\modules\activity\components\MailSummaryProcessor;
use humhub\modules\activity\models\MailSummaryForm;
use humhub\modules\comment\activities\NewComment;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\post\models\Post;
use humhub\modules\space\activities\MemberAdded;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\swiftmailer\Message;

class MailSummaryTest extends HumHubDbTestCase
{
    public function testMailSummaryDaylyProcessor()
    {
        $this->assertMailSent(0);

        $this->becomeUser('Admin');
        $post = new Post(Space::findOne(['id' => 4]), ['message' => 'Summary Test']);
        $this->assertTrue($post->save());

        // Set Weekly Interval as default
        (new MailSummaryForm(['interval' => MailSummary::INTERVAL_NONE]))->save();

        // Get sure no one receives a mail
        /*MailSummaryProcessor::process(MailSummary::INTERVAL_DAILY);
        MailSummaryProcessor::process(MailSummary::INTERVAL_WEEKLY);
        MailSummaryProcessor::process(MailSummary::INTERVAL_HOURLY);

        $this->assertMailSent(0);*/

        (new MailSummaryForm([
            'user' => User::findOne(['id' => 2]),
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => [ContentCreated::class]
        ]))->save();

        MailSummaryProcessor::process(MailSummary::INTERVAL_DAILY);

        $this->assertMailSent(1);

        /* @var $yiiModule Yii2 */
        $yiiModule = $this->getModule('Yii2');

        /* @var $mail Message */
        $mail =  $yiiModule->grabLastSentEmail();
        $test = $mail->getTo();

        $this->assertArrayHasKey('user1@example.com', $mail->getTo());
        $this->assertEquals('Your daily summary', $mail->getSubject());
    }

    public function testResetUserSettings()
    {
        $user2 = User::findOne(['id' => 3]);

        $form = new MailSummaryForm([
            'user' => $user2,
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => [],
            'limitSpaces' => [Space::findOne(['id' => 3])->guid],
            'limitSpacesMode' => MailSummaryForm::LIMIT_MODE_INCLUDE
        ]);
        $form->save();

        $form = new MailSummaryForm(['user' => $user2]);
        $form->loadCurrent();
        $this->assertEmpty($form->activities);
        $this->assertNotEmpty($form->limitSpaces);

        (new MailSummaryForm(['user' => $user2, 'interval' => MailSummary::INTERVAL_DAILY,]))->resetUserSettings();

        $form = new MailSummaryForm([
            'user' => $user2,
            'interval' => MailSummary::INTERVAL_DAILY,
        ]);

        $form->loadCurrent();
        $this->assertNotEmpty($form->activities);
        $this->assertEmpty($form->limitSpaces);
    }


    public function testSummarySpaceExcludeFilter()
    {
        // Create Post + Comment in Space3
        $this->becomeUser('Admin');
        $post = new Post(Space::findOne(['id' => 3]), ['message' => 'Summary Test']);
        $this->assertTrue($post->save());

        $this->becomeUser('User1');
        $comment = new Comment([
            'message' => 'Summary test comment!',
            'object_model' => Post::class,
            'object_id' => $post->id
        ]);
        $this->assertTrue($comment->save());

        // MemberAdded activity in Space4
        $space4 = Space::findOne(['id' => 4]);
        $space4->addMember(4);

        $summaryUser2 = $this->createSummary(User::findOne(['id' => 3]), MailSummary::INTERVAL_DAILY);
        $user2Activities = $summaryUser2->getActivities();
        $this->assertCount(3, $user2Activities);
        $this->assertContainsActivity(ContentCreated::class, $user2Activities, 'User2 must contain '.ContentCreated::class);
        $this->assertContainsActivity(NewComment::class, $user2Activities, 'User2 must contain '.NewComment::class);
        $this->assertContainsActivity(MemberAdded::class, $user2Activities, 'User2 must contain '.MemberAdded::class);

        // Set no activities by default
        (new MailSummaryForm([
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => [
                ContentCreated::class,
                NewComment::class,
                MemberAdded::class
            ],
            'limitSpaces' => [Space::findOne(['id' => 3])->guid],
            'limitSpacesMode' => MailSummaryForm::LIMIT_MODE_INCLUDE
        ]))->save();

        $user2Activities = $summaryUser2->getActivities();
        $this->assertCount(2, $user2Activities);
        $this->assertContainsActivity(ContentCreated::class, $user2Activities, 'User2 must contain '.ContentCreated::class);
        $this->assertContainsActivity(NewComment::class, $user2Activities, 'User2 must contain '.NewComment::class);

        // Set no activities by default
        (new MailSummaryForm([
            'interval' => MailSummary::INTERVAL_DAILY,
            'user' => User::findOne(['id' => 3]),
            'activities' => [
                ContentCreated::class,
                NewComment::class,
                MemberAdded::class
            ],
            'limitSpaces' => [Space::findOne(['id' => 3])->guid],
            'limitSpacesMode' => MailSummaryForm::LIMIT_MODE_EXCLUDE
        ]))->save();

        $user2Activities = $summaryUser2->getActivities();
        $this->assertCount(1, $user2Activities);
        $this->assertContainsActivity(MemberAdded::class, $user2Activities, 'User2 must contain '.MemberAdded::class);
    }


    public function testSummaryUserActivityFilter()
    {
        $this->becomeUser('Admin');
        $post = new Post(Space::findOne(['id' => 3]), ['message' => 'Summary Test']);
        $this->assertTrue($post->save());

        $this->becomeUser('User1');
        $comment = new Comment([
            'message' => 'Summary test comment!',
            'object_model' => Post::class,
            'object_id' => $post->id
        ]);
        $this->assertTrue($comment->save());

        // Set no activities by default
        (new MailSummaryForm([
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => []
        ]))->save();

        // Overwrite activity filter for User2
        (new MailSummaryForm([
            'interval' => MailSummary::INTERVAL_DAILY,
            'user' => User::findOne(['id' => 3]), // User2
            'activities' => [
                NewComment::class
            ]
        ]))->save();

        $summaryUser2 = $this->createSummary(User::findOne(['id' => 3]), MailSummary::INTERVAL_DAILY);
        $user2Activities = $summaryUser2->getActivities();
        $this->assertCount(1, $user2Activities);
        $this->assertContainsActivity(NewComment::class, $user2Activities, 'User2 must contain '.NewComment::class);
    }
    public function testSummaryGlobalActivityFilter()
    {
        $this->becomeUser('Admin');
        $post = new Post(Space::findOne(['id' => 3]), ['message' => 'Summary Test']);
        $this->assertTrue($post->save());

        $this->becomeUser('User1');
        $comment = new Comment([
            'message' => 'Summary test comment!',
            'object_model' => Post::class,
            'object_id' => $post->id
        ]);
        $this->assertTrue($comment->save());

        $summaryUser2 = $this->createSummary(User::findOne(['id' => 3]), MailSummary::INTERVAL_DAILY);

        // Before global filter we get ContentCreated and NewComment
        $this->assertCount(2, $summaryUser2->getActivities());

        // Set global filter only receive ContentCreated
        (new MailSummaryForm([
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => [
                ContentCreated::class
            ]
        ]))->save();

        $user2Activities = $summaryUser2->getActivities();
        $this->assertCount(1, $user2Activities);
        $this->assertContainsActivity(ContentCreated::class, $user2Activities, 'User2 must contain '.ContentCreated::class);

        (new MailSummaryForm([
            'interval' => MailSummary::INTERVAL_DAILY,
            'activities' => []
        ]))->save();

        $user2Activities = $summaryUser2->getActivities();
        $this->assertCount(0, $user2Activities);
    }

    public function testSummaryNoFilter()
    {
        $summaryAdmin = $this->createSummary(User::findOne(['id' => 1]), MailSummary::INTERVAL_DAILY);
        $summaryUser1 = $this->createSummary(User::findOne(['id' => 2]), MailSummary::INTERVAL_DAILY);
        $summaryUser2 = $this->createSummary(User::findOne(['id' => 3]), MailSummary::INTERVAL_DAILY);
        $summaryUser3 = $this->createSummary(User::findOne(['id' => 4]), MailSummary::INTERVAL_DAILY);

        $this->assertEmpty($summaryUser2->getActivities());

        $this->becomeUser('Admin');
        $post = new Post(Space::findOne(['id' => 3]), ['message' => 'Summary Test']);
        $this->assertTrue($post->save());

        $this->becomeUser('User1');
        $comment = new Comment([
            'message' => 'Summary test comment!',
            'object_model' => Post::class,
            'object_id' => $post->id
        ]);
        $this->assertTrue($comment->save());

        // Admin only gets comment activity
        $adminActivities = $summaryAdmin->getActivities();
        $this->assertCount(1, $adminActivities);
        $this->assertContainsActivity(NewComment::class, $adminActivities, 'Admin must contain '.NewComment::class);

        // Comment author only gets new content activity
        $user1Activities = $summaryUser1->getActivities();
        $this->assertCount(1, $user1Activities);
        $this->assertContainsActivity(ContentCreated::class, $user1Activities, 'User1 must contain '.ContentCreated::class);

        // Spae member gets both activities
        $user2Activities = $summaryUser2->getActivities();
        $this->assertCount(2, $user2Activities);
        $this->assertContainsActivity(ContentCreated::class, $user2Activities, 'User2 must contain '.ContentCreated::class);
        $this->assertContainsActivity(NewComment::class, $user2Activities, 'User2 must contain '.NewComment::class);

        $user3Activities = $summaryUser3->getActivities();
        $this->assertEmpty($user3Activities);
    }


    public function assertContainsActivity($activityClass, $activities, $message = null)
    {
        foreach ($activities as $activity) {
            if(get_class($activity) == $activityClass) {
                $this->assertTrue(true, $message);
                return;
            }
        }

        $this->assertTrue(false, $message);
    }

    /**
     * @param $user
     * @param $interval
     * @throws \yii\base\InvalidConfigException
     * @return MailSummary
     */
    public function createSummary($user, $interval)
    {
        return Yii::createObject([
            'class' => MailSummary::class,
            'user' => $user,
            'interval' => $interval
        ]);
    }

}