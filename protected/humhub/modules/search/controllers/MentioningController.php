<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\controllers;

use humhub\components\Controller;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\space\widgets\Image as SpaceImage;
use Yii;

/**
 * Controller used for mentioning (user/space) searches
 *
 * @since 1.4
 */
class MentioningController extends Controller
{

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['login']
        ];
    }

    public function actionIndex()
    {
        /* @var $container ContentContainerActiveRecord */
        Yii::$app->response->format = 'json';

        $maxResultsNum = 6;
        $results = [];
        $keyword = (string)Yii::$app->request->get('keyword');

        // Add user results
        $users = User::find()
            ->visible()
            ->search($keyword)
            ->limit($maxResultsNum)
            ->orderBy(['user.last_login' => SORT_DESC])
            ->all();
        foreach ($users as $container) {
            $results[] = [
                'guid' => $container->guid,
                'type' => 'u',
                'name' => $container->getDisplayName(),
                'image' => UserImage::widget(['user' => $container, 'width' => 20]),
                'link' => $container->getUrl()
            ];
        }

        // Add space results if users number is not enough
        $spaceNum = $maxResultsNum - count($users);
        if ($spaceNum > 0) {
            $spaces = Space::find()
                ->visible()
                ->search($keyword)
                ->limit($spaceNum)
                ->all();
            foreach ($spaces as $container) {
                $results[] = [
                    'guid' => $container->guid,
                    'type' => 's',
                    'name' => $container->getDisplayName(),
                    'image' => SpaceImage::widget(['space' => $container, 'width' => 20]),
                    'link' => $container->getUrl()
                ];
            }
        }

        return $this->asJson($results);
    }

}
