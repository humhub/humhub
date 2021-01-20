<?php


namespace dashboard;


use humhub\modules\content\models\Content;
use humhub\modules\dashboard\Module;
use humhub\modules\dashboard\stream\DashboardStreamQuery;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class DashboardStreamTest extends HumHubDbTestCase
{
    public function _before()
    {
        parent::_before();
        Content::deleteAll();
    }

    public function _after()
    {
        $this->disableAutoIncludeProfilePosts();
        $this->enableFriendships(false);
        parent::_after();
    }

    public function disableAutoIncludeProfilePosts()
    {
        Yii::$app->getModule('dashboard')->autoIncludeProfilePosts = null;
    }

    public function enableAutoIncludeProfilePostsAll()
    {
        Yii::$app->getModule('dashboard')->autoIncludeProfilePosts = Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ALWAYS;
    }

    public function enableAutoIncludeProfilePostsAdmin()
    {
        Yii::$app->getModule('dashboard')->autoIncludeProfilePosts = Module::STREAM_AUTO_INCLUDE_PROFILE_POSTS_ADMIN_ONLY;
    }

    /**
     * @param $visibility
     * @return Space
     */
    public function getSpaceByVisibility($visibility)
    {
        return Space::findOne(['visibility' => $visibility, 'status' => Space::STATUS_ENABLED]);
    }


    /**
     * @param $visibility
     * @return Space
     */
    public function getUserByVisibility($visibility)
    {
        return Space::findOne(['visibility' => $visibility, 'status' => User::STATUS_ENABLED]);
    }

    /**
     * @param $visibility
     * @param null $container
     * @return Content
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function createContent($visibility, $container = null, $authorUserName = 'Admin')
    {
        $this->becomeUser($authorUserName);
        $post = new Post(['message' => 'Test Content']);

        if($container) {
            $post->content->container = $container;
        }

        $post->content->visibility = $visibility;
        $this->assertTrue($post->save());
        $this->logout();
        return $post->content;
    }

    protected function fetchDashboardContent($user = null, $limit = 4)
    {
        $query = new DashboardStreamQuery(['user' => $user, 'limit' => $limit, 'activity' => false]);
        return $query->all();
    }

    protected function fetchActivityDashboardContent($user = null, $limit = 4)
    {
        $query = new DashboardStreamQuery(['user' => $user, 'limit' => $limit, 'activity' => true]);
        return $query->all();
    }
}
