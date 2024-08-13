<?php

namespace tests\codeception\unit;

use humhub\modules\admin\models\UserApprovalSearch;
use tests\codeception\_support\HumHubDbTestCase;

class ApprovalTest extends HumHubDbTestCase
{

    /**
     * Tests user approval for 1 user without group assignment and one user with group assignment.
     */
    public function testAdminApproval()
    {
        $this->becomeUser('Admin');

        $approvalSearch = new UserApprovalSearch();
        $users = $approvalSearch->search()->getModels();

        $this->assertEquals(2, count($users));
    }

    /**
     * Tests user approval group manager.
     */
    public function testManagerApproval()
    {
        $this->becomeUser('User2');

        $approvalSearch = new UserApprovalSearch();
        $users = $approvalSearch->search()->getModels();

        $this->assertEquals(1, count($users));
    }

    /**
     * Tests user approval for non group manager.
     */
    public function testNonManagerApproval()
    {
        $this->becomeUser('User1');

        $approvalSearch = new UserApprovalSearch();
        $users = $approvalSearch->search()->getModels();

        $this->assertEquals(0, count($users));
    }
}
