<?php

namespace tests\codeception\unit;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Follow;

class ApprovalTest extends HumHubDbTestCase
{

    use Specify;

    /**
     * Tests user approval for 1 user without group assignment and one user with group assignment.
     */
    public function testAdminApproval()
    {
        $this->becomeUser('Admin');

        $approvalSearch = new \humhub\modules\admin\models\UserApprovalSearch();
        $users = $approvalSearch->search()->getModels();
        
        $this->assertEquals(2, count($users));
    }
    
    /**
     * Tests user approval group manager.
     */
    public function testManagerApproval()
    {
        $this->becomeUser('User2');

        $approvalSearch = new \humhub\modules\admin\models\UserApprovalSearch();
        $users = $approvalSearch->search()->getModels();
        
        $this->assertEquals(1, count($users));
    }
    
    /**
     * Tests user approval for non group manager.
     */
    public function testNonManagerApproval()
    {
        $this->becomeUser('User1');

        $approvalSearch = new \humhub\modules\admin\models\UserApprovalSearch();
        $users = $approvalSearch->search()->getModels();
        
        $this->assertEquals(0, count($users));
    }

}
