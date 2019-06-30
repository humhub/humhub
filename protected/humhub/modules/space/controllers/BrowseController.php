<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\Controller;
use humhub\components\behaviors\AccessControl;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Chooser;
use Yii;
use yii\data\Pagination;

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
                'class' => AccessControl::class,
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
        Yii::$app->response->format = 'json';

        $query = Space::find()->visible();
        $query->search(Yii::$app->request->get('keyword'));

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSizeParam' => 'limit']);

        $query->offset($pagination->offset)->limit($pagination->limit);

        return $this->asJson($this->prepareResult($query->all()));
    }

    /**
     * @param $spaces Space[] array of spaces
     * @return array
     */
    protected function prepareResult($spaces)
    {
        $target = Yii::$app->request->get('target');
        
        $json = [];
        $withChooserItem = ($target === 'chooser');
        foreach ($spaces as $space) {
            $json[] = Chooser::getSpaceResult($space, $withChooserItem);
        }

        return $json;
    }
}
