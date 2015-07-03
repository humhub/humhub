<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * Description of BaseStreamActionTest
 *
 * @author luke
 */
class BaseStreamActionTest extends HDbTestCase
{

    public $fixtures = array(':user', ':group', ':space', ':space_membership', ':wall', ':wall_entry');
    private $postWallEntryIds = array();

    protected function setUp()
    {
        parent::setUp();

        $post = new Post;
        $post->message = "P1";
        $post->content->setContainer(Yii::app()->user->getModel());
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();

        $post = new Post;
        $post->message = "P2";
        $post->content->setContainer(Yii::app()->user->getModel());
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();

        $post = new Post;
        $post->message = "P3";
        $post->content->setContainer(Yii::app()->user->getModel());
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();

        $post = new Post;
        $post->message = "P4";
        $post->content->setContainer(Yii::app()->user->getModel());
        $post->save();
        $this->postWallEntryIds[] = $post->content->getFirstWallEntryId();


        $this->postWallEntryIds = array_reverse($this->postWallEntryIds);
    }

    public function testModeNormal()
    {
        $baseStreamAction = new BaseStreamAction(Yii::app()->getController(), 'testAc');
        $baseStreamAction->init();

        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);
        $this->assertEquals($this->postWallEntryIds, $wallEntryIds);
    }

    public function testModeActivity()
    {
        $this->becomeUser('User2');

        // Post of User 2 - should not be included in activities
        $post = new Post;
        $post->message = "P5";
        $post->content->setContainer(Yii::app()->user->getModel());
        $post->save();

        $baseStreamAction = new BaseStreamAction(Yii::app()->getController(), 'testAc');
        $baseStreamAction->mode = BaseStreamAction::MODE_ACTIVITY;
        $baseStreamAction->init();

        $wallEntries = $baseStreamAction->getWallEntries();

        assert(count($wallEntries) == 4);

        foreach ($wallEntries as $entry) {
            assert(($entry->content->object_model == 'Activity' && $entry->content->created_by != 2));
        }
    }

    public function testStreamDisabledUser()
    {
        $this->becomeUser('User2');

        // Post of User 2 - should not be included thus he is deactivated
        $post = new Post;
        $post->message = "P5";
        $post->content->setContainer(Yii::app()->user->getModel());
        $post->save();

        $user = User::model()->findByPk(2);
        $user->status = User::STATUS_DISABLED;
        $user->save();

        $baseStreamAction = new BaseStreamAction(Yii::app()->getController(), 'test');
        $baseStreamAction->mode = BaseStreamAction::MODE_NORMAL;
        $baseStreamAction->init();

        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);
        $this->assertEquals($this->postWallEntryIds, $wallEntryIds);
    }

    public function testOrder()
    {
        
        /**
         * @todo FIXME, change time in database instead of sleeping
         */
        sleep(1);
        $post1 = new Post;
        $post1->message = "P1";
        $post1->content->setContainer(Yii::app()->user->getModel());
        $post1->save();
        $post1wallEntryId = $post1->content->getFirstWallEntryId();
        sleep(1);
        $post2 = new Post;
        $post2->message = "P2";
        $post2->content->setContainer(Yii::app()->user->getModel());
        $post2->save();
        $post2wallEntryId = $post2->content->getFirstWallEntryId();
        sleep(1);
        $post1->message = "P1b";
        $post1->save();

        $baseStreamAction = new BaseStreamAction(Yii::app()->getController(), 'test');
        $baseStreamAction->limit = 2;
        $baseStreamAction->init();
        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        $this->assertEquals(array($post2wallEntryId, $post1wallEntryId), $wallEntryIds);

        $baseStreamAction = new BaseStreamAction(Yii::app()->getController(), 'test');
        $baseStreamAction->limit = 2;
        $baseStreamAction->sort = BaseStreamAction::SORT_UPDATED_AT;

        $baseStreamAction->init();
        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        $this->assertEquals(array($post1wallEntryId, $post2wallEntryId), $wallEntryIds);
    }

    public function testFrom()
    {
        // Test From Sorting of Stream
    }

    public function testLimit()
    {
        $baseStreamAction = new BaseStreamAction(Yii::app()->getController(), 'test');
        $baseStreamAction->limit = 2;
        $baseStreamAction->init();

        $wallEntries = $baseStreamAction->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);
        $this->assertEquals(array_slice($this->postWallEntryIds, 0, 2), $wallEntryIds);
    }

}
