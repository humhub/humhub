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
 * Date: 27.07.2017
 * Time: 13:27
 */

namespace humhub\tests\codeception\unit\components\access;


use humhub\commands\TestController;
use humhub\components\access\AccessValidator;
use humhub\components\access\ControllerAccess;
use humhub\components\access\StrictAccess;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ControllerAccessTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testLoggedInOnlyValidator()
    {
        // Guest not allowed for global loggedInOnly rule
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals('Login required for this section.', $controllerAccess->reason);

        // Guest not allowed for not action related loggedInOnly rule
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['testAction2']]
        ]]);

        $this->assertTrue($controllerAccess->run());

        // Guest not allowed for action related loggedInOnly rule
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['testAction', 'testAction2']]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals('Login required for this section.', $controllerAccess->reason);

        // User allowed for global loggedInOnly rule
        $this->becomeUser('User1');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ]]);

        $this->assertTrue($controllerAccess->run());
    }

    public function testAdminOnlyValidator()
    {
        // Guest
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_ADMIN_ONLY]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals('You need admin permissions to access this section.', $controllerAccess->reason);

        // User
        $this->becomeUser('User1');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_ADMIN_ONLY]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals('You need admin permissions to access this section.', $controllerAccess->reason);

        // Admin
        $this->becomeUser('Admin');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_ADMIN_ONLY]
        ]]);

        $this->assertTrue($controllerAccess->run());
    }

    public function testStrictModeValidator()
    {
        // Guest not allowed for global strict rule
        $this->allowGuestAccess(false);

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_STRICT]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals('Guest mode not active, please login first.', $controllerAccess->reason);

        // Guest allowed for global strict rule if guest mode active
        $this->allowGuestAccess(true);

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_STRICT]
        ]]);

        $this->assertTrue($controllerAccess->run());

        // User allowed for global strict rule if guest mode not active
        $this->allowGuestAccess(false);
        $this->becomeUser('User1');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_STRICT]
        ]]);

        $this->assertTrue($controllerAccess->run());
    }

    public function testInactiveUserValidator()
    {
        // Guests should not be affected
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_DISABLED_USER],
            [ControllerAccess::RULE_UNAPPROVED_USER]
        ]]);

        $this->assertTrue($controllerAccess->run());

        // Active user should not be affected
        $this->becomeUser('User1');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_DISABLED_USER],
            [ControllerAccess::RULE_UNAPPROVED_USER]
        ]]);

        $this->assertTrue($controllerAccess->run());

        // Disabled user should not be allowed
        $this->becomeUser('DisabledUser');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_DISABLED_USER],
            [ControllerAccess::RULE_UNAPPROVED_USER]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals(401, $controllerAccess->code );
        $this->assertEquals('Your user account is inactive, please login with an active account or contact a network administrator.', $controllerAccess->reason);

        // UnnapprovedUser
        $this->becomeUser('UnapprovedUser');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            [ControllerAccess::RULE_DISABLED_USER],
            [ControllerAccess::RULE_UNAPPROVED_USER]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals(401, $controllerAccess->code );
        $this->assertEquals('Your user account has not been approved yet, please try again later or contact a network administrator.', $controllerAccess->reason);
    }

    public function testGuestUserValidator()
    {
        $this->allowGuestAccess();

        // If no guest restriction is given the validation should pass
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => []]);
        $this->assertTrue($controllerAccess->run());

        // Set two guestaccess rules
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            ['guestAccess' => ['otherAction']],
            ['guestAccess' => ['testAction']],
        ]]);

        #$this->assertTrue($controllerAccess->run());


        // If global guest restriction is given allow for all actions
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            ['guestAccess']
        ]]);
        #$this->assertTrue($controllerAccess->run());

        // Non action related guestAccess rule should fail
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            ['guestAccess' => ['testAction2']]
        ]]);
        $this->assertFalse($controllerAccess->run());

        // Action related guestAccess rule should succeed
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            ['guestAccess' => ['testAction']]
        ]]);
        $this->assertTrue($controllerAccess->run());

        // LoggedIn users should not be affected
        $this->becomeUser('User1');
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => [
            ['guestAccess' => ['testAction2']]
        ]]);
        $this->assertTrue($controllerAccess->run());
    }

    public function testPermissionRuleValidator()
    {
        // Guest has no permission
        $accessCheck = new ControllerAccess([
            'rules' => [
                ['permissions' => [AccessTestPermission1::class]]
            ],
            'action' => 'testAction'
        ]);
        $this->assertFalse($accessCheck->run());

        // Add Permission1 to User Group but validate against Permission2
        $this->setGroupPermission(2, AccessTestPermission1::class);
        $this->becomeUser('User1');
        $accessCheck = new ControllerAccess([
            'rules' => [
                ['permissions' => [AccessTestPermission2::class]]
            ],
            'action' => 'testAction'
        ]);
        $this->assertFalse($accessCheck->run());

        // Permission2 included
        $accessCheck->rules = [
            ['permissions' => [AccessTestPermission1::class, AccessTestPermission2::class]]
        ];
        $this->assertTrue($accessCheck->run());

        // In strict mode both permission have to be granted
        $accessCheck->rules = [
            ['permissions' => [AccessTestPermission1::class, AccessTestPermission2::class], 'strict' => true]
        ];
        $this->assertFalse($accessCheck->run());

        // Check two permission rules one non action related and one global valid one. The non action related should be ignored
        $accessCheck->rules = [
            ['permissions' => [AccessTestPermission2::class], 'actions' => 'otherPermission'],
            ['permissions' => [AccessTestPermission1::class, AccessTestPermission2::class]]
        ];
        $this->assertTrue($accessCheck->run());


        // Check non strict behaviour of permissoin rule with one not allowed global rule which is overwritten by action related
        // This check passes, since only one of them has to pass.
        $accessCheck->rules = [
            ['permissions' => [AccessTestPermission2::class]],
            ['permissions' => [AccessTestPermission1::class], 'actions' => 'testAction']
        ];
        $this->assertTrue($accessCheck->run());


        // Check string permission definition
        $accessCheck->rules = [
            ['permissions' => AccessTestPermission1::class]
        ];
        $this->assertTrue($accessCheck->run());

        // Check permission rule in combination with adminOnly
        $accessCheck->rules = [
            [ControllerAccess::RULE_ADMIN_ONLY],
            ['permissions' => [AccessTestPermission2::class], 'actions' => 'otherPermission'],
            ['permissions' => [AccessTestPermission1::class, AccessTestPermission2::class]]
        ];
        $this->assertFalse($accessCheck->run());
    }

    public function testGuestRunValidation()
    {
        $this->allowGuestAccess();

        // AdminOnly overwrites guestAccess
        $accessCheck = new ControllerAccess([
            'rules' => [
                [ControllerAccess::RULE_ADMIN_ONLY],
                ['guestAccess' => ['testAction']]
            ],
            'action' => 'testAction'
        ]);

        $this->assertFalse($accessCheck->run());

        // AdminOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            ['guestAccess' => ['otherTestAction', 'testAction']],
            [ControllerAccess::RULE_ADMIN_ONLY => ['testAction']]
        ];
        $this->assertFalse($accessCheck->run());

        // AdminOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            ['guestAccess' => ['testAction']],
            [ControllerAccess::RULE_ADMIN_ONLY => ['otherTestAction']]
        ];
        $this->assertTrue($accessCheck->run());

        // LoggedInOnly setting should overwrite the guestAccess
        $accessCheck->rules = [
            ['guestAccess'],
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ];
        $this->assertFalse($accessCheck->run());

        // Non action related adminOnly and loggedInOnly
        $accessCheck->rules = [
            [ControllerAccess::RULE_ADMIN_ONLY => ['otherTestAction']],
            [ControllerAccess::RULE_LOGGED_IN_ONLY => 'otherTestAction']
        ];
        $this->assertTrue($accessCheck->run());

        // LoggedInOnly overwrites guest access
        $accessCheck->rules = [
            [ControllerAccess::RULE_ADMIN_ONLY => ['otherTestAction']],
            [ControllerAccess::RULE_LOGGED_IN_ONLY => 'testAction'],
            ['guestAccess' => 'testAction']
        ];
        $this->assertFalse($accessCheck->run());

        // Global permission rule
        $accessCheck->rules = [
            ['permissions' => [AccessTestPermission1::class]]
        ];
        $this->assertFalse($accessCheck->run());

        // Non action related permission rule
        $accessCheck->rules = [
            ['permissions' => AccessTestPermission1::class, 'actions' => ['otherTestAction']]
        ];
        $this->assertTrue($accessCheck->run());

        // Matching action related permission setting
        $accessCheck->rules = [
            ['permissions' => [AccessTestPermission1::class], 'actions' => ['otherTestAction', 'testAction']]
        ];
        $this->assertFalse($accessCheck->run());

        $this->allowGuestAccess(false);

        $accessCheck->rules = [['strict']];
        $this->assertFalse($accessCheck->run());
    }

    public function testFixedRuleValidation()
    {
        // DsiabledUser and Unapproved are fixed rules and should always be validated
        $this->becomeUser('DisabledUser');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => []]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals(401, $controllerAccess->code );
        $this->assertEquals('Your user account is inactive, please login with an active account or contact a network administrator.', $controllerAccess->reason);

        // UnnapprovedUser
        $this->becomeUser('UnapprovedUser');

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'rules' => []]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals(401, $controllerAccess->code );
        $this->assertEquals('Your user account has not been approved yet, please try again later or contact a network administrator.', $controllerAccess->reason);
    }

    public function testStrictAccess()
    {
        // Guest not allowed for global strict rule
        $this->allowGuestAccess(false);

        $controllerAccess = new StrictAccess(['action' => 'testAction', 'rules' => []]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals('Guest mode not active, please login first.', $controllerAccess->reason);

        // Guest allowed for global strict rule if guest mode active
        $this->allowGuestAccess(true);

        $controllerAccess = new StrictAccess(['action' => 'testAction', 'rules' => []]);

        $this->assertTrue($controllerAccess->run());

        // User allowed for global strict rule if guest mode not active
        $this->allowGuestAccess(false);
        $this->becomeUser('User1');

        $controllerAccess = new StrictAccess(['action' => 'testAction', 'rules' => []]);

        $this->assertTrue($controllerAccess->run());
    }

    public function testCustomOwnerRule()
    {
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'owner' => $this, 'rules' => [
            ['validateTestRule', 'return' => false]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals(404, $controllerAccess->code);
        $this->assertEquals('Not you again!', $controllerAccess->reason);

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'owner'  => $this, 'rules' => [
            ['validateTestRule', 'return' => true]
        ]]);

        $this->assertTrue($controllerAccess->run());
        $this->assertEquals(null, $controllerAccess->code);
        $this->assertEquals(null, $controllerAccess->reason);
    }

    public function validateTestRule($rule, $access)
    {
        $this->assertEquals($access->owner, $this);
        if(!$rule['return']) {
            $access->code = 404;
            $access->reason = 'Not you again!';
            return false;
        }

        return true;
    }

    public function testCustomClassRule()
    {
        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'owner'  => $this, 'rules' => [
            [TestActionValidator::class, 'return' => false]
        ]]);

        $this->assertFalse($controllerAccess->run());
        $this->assertEquals(404, $controllerAccess->code);
        $this->assertEquals('Not you again!', $controllerAccess->reason);

        $controllerAccess = new ControllerAccess(['action' => 'testAction', 'owner'  => $this, 'rules' => [
            [TestActionValidator::class, 'return' => true]
        ]]);

        $this->assertTrue($controllerAccess->run());
        $this->assertEquals(null, $controllerAccess->code);
        $this->assertEquals(null, $controllerAccess->reason);
    }
}