<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\space\widgets\Image as SpaceImage;

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
        Yii::$app->response->format = 'json';

        $results = [];
        $keyword = (string)Yii::$app->request->get('keyword');

        // Add user results
        $query = User::find()->visible()->search($keyword);
        foreach ($query->limit(10)->all() as $container) {
            $results[] = [
                'guid' => $container->guid,
                'type' => 'u',
                'name' => $container->getDisplayName(),
                'image' => UserImage::widget(['user' => $container, 'width' => 20]),
                'link' => $container->getUrl()
            ];
        };

        // Add space results
        $query = Space::find()->visible()->search($keyword);
        foreach ($query->limit(10)->all() as $container) {
            $results[] = [
                'guid' => $container->guid,
                'type' => 's',
                'name' => $container->getDisplayName(),
                'image' => SpaceImage::widget(['space' => $container, 'width' => 20]),
                'link' => $container->getUrl()
            ];
        }
        return $this->asJson($results);
    }

}
