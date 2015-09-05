<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;

/**
 * LinkController provides link informations about an Activity via JSON.
 *
 * @author luke
 */
class LinkController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['info']
            ]
        ];
    }

    /**
     * Returns a JSON Response with links of an Activity
     * 
     * @return string json
     */
    public function actionInfo()
    {
        Yii::$app->response->format = 'json';

        $json = [];
        $json['success'] = 'false';

        $activityId = Yii::$app->request->get('id');
        $activity = Activity::findOne(['id' => $activityId]);

        if ($activity !== null && $activity->content->canRead()) {
            $json['success'] = 'true';
            $json['wallEntryId'] = '0';

            $source = $activity->getSource();
            if ($source instanceof ContentActiveRecord || $source instanceof ContentAddonActiveRecord) {
                $json['wallEntryId'] = $source->content->getFirstWallEntryId();
                $json['permaLink'] = $source->content->getUrl();
            } else {
                $json['permaLink'] = $activity->content->getUrl();
            }

        }

        return $json;
    }

}
