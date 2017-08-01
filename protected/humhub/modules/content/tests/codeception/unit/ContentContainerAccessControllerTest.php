<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 26.07.2017
 * Time: 16:13
 */

namespace humhub\modules\content\tests\codeception\unit;

use humhub\components\access\ControllerAccess;
use humhub\modules\content\components\ContentContainerControllerAccess;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ContentContainerAccessControllerTest extends HumHubDbTestCase
{
    public function testSimpleGlobalGuestAccess()
    {
        $space = Space::findOne(1);
        $this->allowGuestAccess();
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        // Controller global guestAccess
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [
                ['guestAccess']
            ],
            'action' => 'testAction']);
        $this->assertTrue($accessCheck->isGuest());

        // GuestAccess given with not matching action setting
        $accessCheck->rules = [
            ['guestAccess' => ['otherTestAction']]
        ];
        $this->assertFalse($accessCheck->run());

        // GuestAccess given with matching actoin setting
        $accessCheck->rules = [
            ['guestAccess' => ['otherTestAction', 'testAction']]
        ];
        $this->assertTrue($accessCheck->run());

        // AdminOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            ['guestAccess' => ['otherTestAction', 'testAction']],
            [ControllerAccess::RULE_ADMIN_ONLY]
        ];
        $this->assertFalse($accessCheck->run());

        // AdminOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            ['guestAccess' => ['otherTestAction', 'testAction']],
            [ControllerAccess::RULE_ADMIN_ONLY => ['testAction']]
        ];
        $this->assertFalse($accessCheck->run());

        // AdminOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            ['guestAccess' => ['otherTestAction', 'testAction']],
            [ControllerAccess::RULE_ADMIN_ONLY => ['otherTestAction']]
        ];
        $this->assertTrue($accessCheck->run());

        // LoggedInOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            [ControllerAccess::RULE_ADMIN_ONLY => ['otherTestAction']],
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ];
        $this->assertFalse($accessCheck->run());

        // LoggedInOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            [ControllerAccess::RULE_ADMIN_ONLY => ['otherTestAction']],
            [ControllerAccess::RULE_LOGGED_IN_ONLY => 'otherTestAction']
        ];
        $this->assertTrue($accessCheck->run());

        // LoggedInOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            [ControllerAccess::RULE_ADMIN_ONLY => ['otherTestAction']],
            [ControllerAccess::RULE_LOGGED_IN_ONLY => 'testAction'],
            ['guestAccess' => 'testAction']
        ];
        $this->assertFalse($accessCheck->run());

        // By default guests are allowed without further rules
        $accessCheck->rules = [];
        $this->assertTrue($accessCheck->run());

        // Global Permission setting
        $accessCheck->rules = [
            ['permission' => ContentTestPermission1::class]
        ];
        $this->assertFalse($accessCheck->run());

        // Action restricted permission setting
        $accessCheck->rules = [
            ['permission' => ContentTestPermission1::class, 'actions' => ['otherTestAction']]
        ];
        $this->assertTrue($accessCheck->run());

        // Matching action related permission setting
        $accessCheck->rules = [
            ['permission' => ContentTestPermission1::class, 'actions' => ['otherTestAction', 'testAction']]
        ];
        $this->assertFalse($accessCheck->run());
    }

    public function testStrictAccess()
    {
        $space = Space::findOne(1);
        // Controller global guestAccess
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [],
            'action' => 'testAction']);
        // Test strict behaviour
        $this->allowGuestAccess(false);
        $this->assertFalse($accessCheck->run());
    }

    public function testSpaceOnlyAccess()
    {
        $this->becomeUser('User1');
        $space = Space::findOne(1);

        // Controller global guestAccess
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [[ContentContainerControllerAccess::RULE_SPACE_ONLY]],
            'action' => 'testAction']);

        $this->assertTrue($accessCheck->run());

        $accessCheck->contentContainer = Yii::$app->user->getIdentity();
        $this->assertFalse($accessCheck->run());

        // Not related
        $accessCheck->rules = [
                [ContentContainerControllerAccess::RULE_SPACE_ONLY => ['otherAction']]
        ];

        $this->assertTrue($accessCheck->run());

        $accessCheck->rules = [
            [ContentContainerControllerAccess::RULE_SPACE_ONLY => ['otherAction', 'testAction']]
        ];

        $this->assertFalse($accessCheck->run());
    }

    public function testProfileOnlyAccess()
    {
        $this->becomeUser('User1');
        $space = Space::findOne(1);

        // Controller global guestAccess
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => Yii::$app->user->getIdentity(),
            'rules' => [[ContentContainerControllerAccess::RULE_PROFILE_ONLY]],
            'action' => 'testAction']);

        $this->assertTrue($accessCheck->run());

        $accessCheck->contentContainer = $space;
        $this->assertFalse($accessCheck->run());

        // Not related
        $accessCheck->rules = [
            [ContentContainerControllerAccess::RULE_PROFILE_ONLY => ['otherAction']]
        ];

        $this->assertTrue($accessCheck->run());

        $accessCheck->rules = [
            [ContentContainerControllerAccess::RULE_PROFILE_ONLY => ['otherAction', 'testAction']]
        ];

        $this->assertFalse($accessCheck->run());
    }

    public function testUserGroupAccess()
    {
        $this->allowGuestAccess();

        // Guest is not user
        $space = Space::findOne(1);
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'action' => 'testAction',
            'rules' => [
                ['userGroup' => [Space::USERGROUP_USER]]
            ]
        ]);

        $this->assertFalse($accessCheck->run());

        // Guest is allowed
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [['userGroup' => [Space::USERGROUP_GUEST]]],
            'action' => 'testAction']);

        $this->assertTrue($accessCheck->run());

        // User 1 is not member of Space1
        $this->becomeUser('User1');


        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [['userGroup' => [Space::USERGROUP_MEMBER]]],
            'action' => 'testAction']);

        $this->assertFalse($accessCheck->run());

        // User 1 is member of Space1
        $space3 = Space::findOne(3);

        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space3,
            'rules' => [['userGroup' => [Space::USERGROUP_MEMBER]]],
            'action' => 'testAction']);

        $this->assertTrue($accessCheck->run());

        // Since its leveled member should also be allowed to access user restricted
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space3,
            'rules' => [['userGroup' => Space::USERGROUP_USER]],
            'action' => 'testAction']);

        $this->assertTrue($accessCheck->run());

        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space3,
            'rules' => [['userGroup' => Space::USERGROUP_MODERATOR]],
            'action' => 'testAction']);

        $this->assertFalse($accessCheck->run());

        // Only non user related group provided
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => Yii::$app->user->getIdentity(),
            'rules' => [['userGroup' => Space::USERGROUP_MODERATOR]],
            'action' => 'testAction']);

        $this->assertFalse($accessCheck->run());

        // Also User related group provided
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => Yii::$app->user->getIdentity(),
            'rules' => [['userGroup' => [Space::USERGROUP_MODERATOR, User::USERGROUP_SELF]]],
            'action' => 'testAction']);

        $this->assertTrue($accessCheck->run());

        $user1 = Yii::$app->user->getIdentity();
        $this->becomeUser('User2');

        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $user1,
            'rules' => [['userGroup' => [User::USERGROUP_SELF]]],
            'action' => 'testAction']);

        $this->assertFalse($accessCheck->run());

        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $user1,
            'rules' => [['userGroup' => [User::USERGROUP_GUEST]]],
            'action' => 'testAction']);

        $this->assertTrue($accessCheck->run());
    }

    public function testLoggedInOnlyAccess()
    {
        $space = Space::findOne(1);
        $this->allowGuestAccess();

        // Controller global guestAccess
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [
                [ControllerAccess::RULE_LOGGED_IN_ONLY]
            ],
            'action' => 'testAction']);
        $this->assertFalse($accessCheck->run());

        $this->becomeUser('User1');
        $accessCheck->user = Yii::$app->user->getIdentity();
        $this->assertFalse($accessCheck->isGuest());
        $this->assertTrue($accessCheck->run());
    }

    public function testAdminOnly()
    {
        // Non space member not allowed
        $space4 = Space::findOne(4);
        $this->becomeUser('User3');
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space4,
            'rules' => [[ControllerAccess::RULE_ADMIN_ONLY]],
            'action' => 'testAction'
        ]);
        $this->assertFalse($accessCheck->run());

        // Member not allowed
        $this->becomeUser('User2');
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space4,
            'rules' => [[ControllerAccess::RULE_ADMIN_ONLY]],
            'action' => 'testAction'
        ]);
        $this->assertFalse($accessCheck->run());

        // Space Admin allowed
        $this->becomeUser('User1');
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space4,
            'rules' => [[ControllerAccess::RULE_ADMIN_ONLY]],
            'action' => 'testAction'
        ]);
        $this->assertTrue($accessCheck->run());

        // System/Space Admin allowed
        $this->becomeUser('Admin');
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space4,
            'rules' => [[ControllerAccess::RULE_ADMIN_ONLY]],
            'action' => 'testAction'
        ]);
        $this->assertTrue($accessCheck->run());

        // System Admin allowed
        $space2 = Space::findOne(2);
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space4,
            'rules' => [[ControllerAccess::RULE_ADMIN_ONLY]],
            'action' => 'testAction'
        ]);
        $this->assertTrue($accessCheck->run());
    }

    public function testInactiveUser()
    {
        $space = Space::findOne(1);
        $this->becomeUser('DisabledUser');
        $accessCheck = new ContentContainerControllerAccess(['contentContainer' => $space,'rules' => [], 'action' => 'testAction']);
        $this->assertFalse($accessCheck->run());

        $this->becomeUser('UnapprovedUser');
        $accessCheck = new ContentContainerControllerAccess(['contentContainer' => $space, 'rules' => [], 'action' => 'testAction']);
        $this->assertFalse($accessCheck->run());
    }

    public function testPermissionRule()
    {
        $space = Space::findOne(3);
        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [
                ['permission' => [ContentTestPermission1::class]]
            ],
            'action' => 'testAction'
        ]);

       # $this->assertFalse($accessCheck->run());

        $this->setGroupPermission(2, ContentTestPermission1::class);

        $this->becomeUser('User1');

        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [
                ['permission' => [ContentTestPermission2::class]]
            ],
            'action' => 'testAction'
        ]);
       # $this->assertFalse($accessCheck->run());

        $accessCheck->rules = [
            ['permission' => [ContentTestPermission1::class, ContentTestPermission2::class]]
        ];
       # $this->assertTrue($accessCheck->run());

        $accessCheck->rules = [
            ['permission' => [ContentTestPermission2::class], 'actions' => 'otherPermission'],
            ['permission' => [ContentTestPermission1::class, ContentTestPermission2::class]]
        ];
       # $this->assertTrue($accessCheck->run());

        $accessCheck->rules = [
            ['permission' => [ContentTestPermission2::class], 'actions' => 'otherPermission'],
            ['permission' => ContentTestPermission1::class]
        ];
        #$this->assertTrue($accessCheck->run());

        $accessCheck->rules = [
            [ControllerAccess::RULE_ADMIN_ONLY],
            ['permission' => [ContentTestPermission2::class], 'actions' => 'otherPermission'],
            ['permission' => [ContentTestPermission1::class, ContentTestPermission2::class]]
        ];
        #$this->assertFalse($accessCheck->run());

        // Set contentcotnainer permission
        $this->setContentContainerPermission($space, Space::USERGROUP_MEMBER, ContentTestPermission2::class);

        $accessCheck = new ContentContainerControllerAccess([
            'contentContainer' => $space,
            'rules' => [
                ['permission' => [ContentTestPermission2::class]]
            ],
            'action' => 'testAction'
        ]);
        $this->assertTrue($accessCheck->run());
    }
}