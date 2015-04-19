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
 * Description of ContentContainerStreamActionTest
 *
 * @author luke
 */
class DashboardStreamActionTest extends HDbTestCase
{

    public $fixtures = array(':user', ':space', ':space_membership', ':wall', ':wall_entry');

    /**
     * stream only includes PUBLIC content of users and spaces which 
     * are in Guest Mode 
     */
    public function testGuest()
    {
        /**
         * @todo implement me!
         */
    }

    /**
     * if a user follows another user, the public posts are included
     * the private not
     */
    public function testUserFollow()
    {
        $this->becomeUser('User2');

        $post1 = new Post;
        $post1->message = "Private Post";
        $post1->content->setContainer(Yii::app()->user->getModel());
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->getFirstWallEntryId();

        $post2 = new Post;
        $post2->message = "Public Post";
        $post2->content->setContainer(Yii::app()->user->getModel());
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->getFirstWallEntryId();

        $this->becomeUser('User1');
        $ids = $this->getStreamActionIds(2);

        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    /**
     * if a user follows a space is the PUBLIC  post included
     * the private not
     */
    public function testSpaceFollow()
    {
        $this->becomeUser('User2');
        $space = Space::model()->findByPk(2);

        $post1 = new Post;
        $post1->message = "Private Post";
        $post1->content->setContainer($space);
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->getFirstWallEntryId();

        $post2 = new Post;
        $post2->message = "Public Post";
        $post2->content->setContainer($space);
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->getFirstWallEntryId();


        $this->becomeUser('User1');
        $ids = $this->getStreamActionIds(2);

        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    /**
     * When member of a space, public & private content should returned.
     * When no member no content should be returned.
     */
    public function testSpaceMembership()
    {
        $space = Space::model()->findByPk(1);

        $post1 = new Post;
        $post1->message = "Private Post";
        $post1->content->setContainer($space);
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->getFirstWallEntryId();

        $post2 = new Post;
        $post2->message = "Public Post";
        $post2->content->setContainer($space);
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->getFirstWallEntryId();

        $this->assertEquals($this->getStreamActionIds(2), array($w2, $w1));

        $this->becomeUser('User2');
        $ids = $this->getStreamActionIds(2);
        $this->assertFalse(in_array($w1, $ids));
        $this->assertFalse(in_array($w2, $ids));
    }

    /**
     * Own profile content should appear with visibility Private & Public
     */
    public function testOwnContent()
    {
        $post1 = new Post;
        $post1->message = "Own Private Post";
        $post1->content->setContainer(Yii::app()->user->getModel());
        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->save();
        $w1 = $post1->content->getFirstWallEntryId();
        ;

        $post2 = new Post;
        $post2->message = "Own Public Post";
        $post2->content->setContainer(Yii::app()->user->getModel());
        $post2->content->visibility = Content::VISIBILITY_PUBLIC;
        $post2->save();
        $w2 = $post2->content->getFirstWallEntryId();
        ;

        $ids = $this->getStreamActionIds(2);
        $this->assertEquals($ids, array($w2, $w1));
    }

    private function getStreamActionIds($limit = 4)
    {
        $action = new DashboardStreamAction(Yii::app()->getController(), 'testAc');
        $action->limit = $limit;
        $action->init();

        $wallEntries = $action->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        return $wallEntryIds;
    }

}
