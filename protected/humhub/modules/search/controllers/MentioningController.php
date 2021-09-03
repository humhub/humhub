<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\controllers;

use humhub\components\Controller;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\post\permissions\CreatePost;
use humhub\modules\search\Module;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\space\widgets\Image as SpaceImage;
use Yii;
use yii\web\HttpException;

/**
 * Controller used for mentioning (user/space) searches
 *
 * @since 1.4
 */
class MentioningController extends Controller
{

    /**
     * @var Module $module
     */
    public $module;

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['login']
        ];
    }

    /**
     * Find all users and spaces on mentioning request from RichText editor
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $keyword = (string)Yii::$app->request->get('keyword');

        // Find users
        $users = User::find()
            ->visible()
            ->search($keyword)
            ->limit($this->module->mentioningSearchBoxResultLimit)
            ->orderBy(['user.last_login' => SORT_DESC])
            ->all();

        $results = [];
        foreach ($users as $user) {
            $results[] = $this->getUserResult($user);
        }

        $results = $this->appendMentioningSpaceResults($keyword, $results);

        return $this->asJson($results);
    }

    /**
     * Find space members on mentioning request from RichText editor on Post form
     *
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionPostSpaceMembers()
    {
        $spaceId = (int)Yii::$app->request->get('id');
        $keyword = (string)Yii::$app->request->get('keyword');

        $space = Space::findOne(['id' => $spaceId]);
        if (!$space || !$space->can(CreatePost::class)) {
            throw new HttpException(403, 'Access denied!');
        }

        // Find space members
        $users = User::find()
            ->leftJoin('space_membership', 'user.id = space_membership.user_id')
            ->andWhere(['space_membership.space_id' => $spaceId])
            ->andWhere(['space_membership.status' => Membership::STATUS_MEMBER])
            ->visible()
            ->search($keyword)
            ->limit($this->module->mentioningSearchBoxResultLimit)
            ->orderBy(['space_membership.last_visit' => SORT_DESC])
            ->all();

        $results = [];
        foreach ($users as $user) {
            $results[] = $this->getUserResult($user);
        }

        $results = $this->appendMentioningSpaceResults($keyword, $results);

        return $this->asJson($results);
    }

    /**
     * Find users followed to the Content on mentioning request from RichText editor on Comment form
     *
     * @return \yii\web\Response
     * @throws HttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCommentContentFollowers()
    {
        $modelClass = (string)Yii::$app->request->get('model');
        $modelId = (int)Yii::$app->request->get('id');
        $keyword = (string)Yii::$app->request->get('keyword');

        /** @var \humhub\modules\comment\Module $module */
        $module = Yii::$app->getModule('comment');

        if (!class_exists($modelClass) ||
            !($object = $modelClass::findOne(['id' => $modelId])) ||
            !$module->canComment($object)) {
            throw new HttpException(403, 'Access denied!');
        }

        // Find users followed to the Content
        $users = User::find()
            ->leftJoin('user_follow', 'user.id = user_follow.user_id')
            ->andWhere(['user_follow.object_model' => $modelClass])
            ->andWhere(['user_follow.object_id' => $modelId])
            ->andWhere(['user_follow.send_notifications' => 1])
            ->visible()
            ->search($keyword)
            ->limit($this->module->mentioningSearchBoxResultLimit)
            ->orderBy(['user.last_login' => SORT_DESC])
            ->all();

        $results = [];
        foreach ($users as $user) {
            $results[] = $this->getUserResult($user);
        }

        $results = $this->appendMentioningSpaceResults($keyword, $results);

        return $this->asJson($results);
    }

    /**
     * Add space results if users number is not enough
     *
     * @param array $results
     * @return array
     */
    private function appendMentioningSpaceResults(string $keyword, array $results): array
    {
        $spaceNum = $this->module->mentioningSearchBoxResultLimit - count($results);

        if ($spaceNum <= 0) {
            // No need to add spaces because the list is already filled with max number of the results
            return $results;
        }

        $spaces = Space::find()
            ->visible()
            ->search($keyword)
            ->limit($spaceNum)
            ->all();
        foreach ($spaces as $space) {
            $results[] = $this->getSpaceResult($space);
        }

        return $results;
    }

    private function getContainerResult(ContentContainerActiveRecord $container, array $params): array
    {
        return array_merge([
            'guid' => $container->guid,
            'type' => null,
            'name' => $container->getDisplayName(),
            'image' => null,
            'link' => $container->getUrl(),
        ], $params);
    }

    private function getUserResult(User $user): array
    {
        return $this->getContainerResult($user, [
            'type' => 'u',
            'image' => UserImage::widget(['user' => $user, 'width' => 20]),
        ]);
    }

    private function getSpaceResult(Space $space): array
    {
        return $this->getContainerResult($space, [
            'type' => 's',
            'image' => SpaceImage::widget(['space' => $space, 'width' => 20]),
        ]);
    }

}
