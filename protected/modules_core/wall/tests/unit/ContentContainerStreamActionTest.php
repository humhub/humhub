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
class ContentContainerStreamActionTest extends HDbTestCase
{

    public function testPrivateContent()
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

        $ids = $this->getStreamActionIds($space, 2);

        $this->assertTrue(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    public function testPublicContent()
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
        $ids = $this->getStreamActionIds($space, 2);

        $this->assertFalse(in_array($w1, $ids));
        $this->assertTrue(in_array($w2, $ids));
    }

    private function getStreamActionIds($container, $limit = 4)
    {
        $action = new ContentContainerStreamAction(Yii::app()->getController(), 'testAc');
        $action->contentContainer = $container;
        $action->limit = $limit;
        $action->init();

        $wallEntries = $action->getWallEntries();
        $wallEntryIds = array_map(create_function('$entry', 'return $entry->id;'), $wallEntries);

        return $wallEntryIds;
    }

}
