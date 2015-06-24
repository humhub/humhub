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
 * HActiveRecordContentTest
 *
 * @package humhub.tests.unit.components
 * @since 0.9
 * @group core
 * @author luke
 */
class HActiveRecordContentTest extends CDbTestCase
{

    public $fixtures = array(':space', ':space_membership', ':user_follow', ':user', ':post', ':content');

    public function testRelatedContentRecord()
    {
        // Create Post
        $post = new Post();
        $post->message = "Test";
        $post->content->container = Yii::app()->user->getModel();
        $this->assertTrue(isset($post->content) && $post->content instanceof Content);
        $this->assertTrue($post->validate());
        $this->assertTrue($post->save());
        $this->assertEquals(1, Content::model()->countByAttributes(array('object_model' => 'Post', 'object_id' => $post->getPrimaryKey())));
    }

    public function testUserRelated()
    {
        Yii::app()->user->setId(1);

        // Check invalid calls
        $posts = Post::model()->userRelated(array())->findAll();
        $this->assertCount(0, $posts);
        $posts = Post::model()->userRelated(array('asdf'))->findAll();
        $this->assertCount(0, $posts);

        // Check mine posts
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_MINE))->findAll();
        $postIds = array_map(create_function('$post', 'return $post->id;'), $posts);
        sort($postIds);
        $this->assertEquals(array(1, 2, 7, 8), $postIds);

        // Check user profile posts include
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_OWN_PROFILE))->findAll();
        $postIds = array_map(create_function('$post', 'return $post->id;'), $posts);
        sort($postIds);
        $this->assertEquals(array(1, 2), $postIds);

        // Check user profile and mine
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_OWN_PROFILE, HActiveRecordContent::SCOPE_USER_RELEATED_MINE))->findAll();
        $postIds = array_map(create_function('$post', 'return $post->id;'), $posts);
        sort($postIds);
        $this->assertEquals(array(1, 2, 7, 8), $postIds);

        // All users space membership posts
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_SPACES))->findAll();
        $postIds = array_map(create_function('$post', 'return $post->id;'), $posts);
        sort($postIds);
        $this->assertEquals(array(7, 8, 9), $postIds);

        // All followed space posts (public)
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_FOLLOWED_SPACES))->findAll();
        $postIds = array_map(create_function('$post', 'return $post->id;'), $posts);
        sort($postIds);
        $this->assertEquals(array(10), $postIds);

        // All followed user profile posts (public)
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_FOLLOWED_USERS))->findAll();
        $postIds = array_map(create_function('$post', 'return $post->id;'), $posts);
        sort($postIds);
        $this->assertEquals(array(4), $postIds);

        // All together
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_FOLLOWED_USERS, HActiveRecordContent::SCOPE_USER_RELEATED_FOLLOWED_SPACES, HActiveRecordContent::SCOPE_USER_RELEATED_MINE, HActiveRecordContent::SCOPE_USER_RELEATED_OWN_PROFILE, HActiveRecordContent::SCOPE_USER_RELEATED_SPACES))->findAll();
        $postIds = array_map(create_function('$post', 'return $post->id;'), $posts);
        sort($postIds);
        $this->assertEquals(array(1, 2, 4, 7, 8, 9, 10), $postIds);

        Yii::app()->user->setId(2);
        $posts = Post::model()->userRelated(array(HActiveRecordContent::SCOPE_USER_RELEATED_FOLLOWED_USERS, HActiveRecordContent::SCOPE_USER_RELEATED_FOLLOWED_SPACES))->findAll();
        $this->assertCount(0, $posts);
    }

    public function testContentSelectorUser()
    {
        $user = User::model()->findByPk(3);

        // Checks Content Selector only return public posts on other user profiles
        Yii::app()->user->setId(1);
        $posts = Post::model()->contentContainer($user)->findAll();
        $this->assertTrue(count($posts) == 1);
        $this->assertTrue($posts[0]->message == "User 3 Profile Post Public");

        // Check Content Selector returns private & public pusts on own user profile
        Yii::app()->user->setId(3);
        $posts = Post::model()->contentContainer($user)->findAll();
        $this->assertTrue(count($posts) == 2);

        $space = Space::model()->findByPk(1);

        // Check space member can read all posts (public & private)
        Yii::app()->user->setId(1);
        $posts = Post::model()->contentContainer($space)->findAll();
        $this->assertTrue(count($posts) == 3);

        // Check non space member can only see public posts
        Yii::app()->user->setId(2);
        $posts = Post::model()->contentContainer($space)->findAll();
        $this->assertTrue(count($posts) == 2);
    }

}
