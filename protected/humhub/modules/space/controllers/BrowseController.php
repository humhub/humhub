<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use Yii;
use humhub\components\Controller;
use yii\helpers\Html;
use humhub\modules\space\widgets\Image;

/**
 * BrowseController
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class BrowseController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['search-json']
            ]
        ];
    }

    /**
     * Returns a workspace list by json
     *
     * It can be filtered by by keyword.
     */
    public function actionSearchJson()
    {
        \Yii::$app->response->format = 'json';

        $keyword = Yii::$app->request->get('keyword', "");
        $target = Yii::$app->request->get('target');
        $page = (int) Yii::$app->request->get('page', 1);
        $limit = (int) Yii::$app->request->get('limit', Yii::$app->settings->get('paginationSize'));

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => \humhub\modules\space\models\Space::className(),
            'page' => $page,
            'pageSize' => $limit
        ]);



        $json = [];
        $withChooserItem = $target === 'chooser';
        foreach ($searchResultSet->getResultInstances() as $space) {
            $json[] = self::getSpaceResult($space, $withChooserItem);
        }

        return $json;
    }

    public static function getSpaceResult($space, $withChooserItem = true, $options = [])
    {
        $spaceInfo = [];
        $spaceInfo['guid'] = $space->guid;
        $spaceInfo['title'] = Html::encode($space->name);
        $spaceInfo['tags'] = Html::encode($space->tags);
        $spaceInfo['image'] = Image::widget(['space' => $space, 'width' => 24]);
        $spaceInfo['link'] = $space->getUrl();

        if ($withChooserItem) {
            $options = array_merge(['space' => $space, 'isMember' => false, 'isFollowing' => false], $options);
            $spaceInfo['output'] = \humhub\modules\space\widgets\SpaceChooserItem::widget($options);
        }

        return $spaceInfo;
    }

}

?>