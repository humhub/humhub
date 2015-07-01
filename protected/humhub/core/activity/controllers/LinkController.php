<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\activity\controllers;

use Yii;
use humhub\components\Controller;
use humhub\core\activity\models\Activity;
use humhub\core\content\components\activerecords\Content;
use humhub\core\content\components\activerecords\ContentAddon;

/**
 * Description of LinkController
 *
 * @author luke
 */
class LinkController extends Controller
{

    public function actionInfo()
    {
        Yii::$app->response->format = 'json';

        $json = [];
        $json['success'] = 'false';

        $activityId = Yii::$app->request->get('id');
        $activity = Activity::findOne(['id' => $activityId]);

        if ($activity !== null && $activity->content->canRead()) {
            $json['success'] = 'true';
            $json['wallEntryId'] = '';

            $underlying = $activity->getUnderlyingObject();
            if ($underlying instanceof Content || $underlying instanceof ContentAddon) {
                $json['wallEntryId'] = $underlying->content->getFirstWallEntryId();
            }

            $json['permaLink'] = $activity->content->getUrl();
        }

        return $json;
    }

}
