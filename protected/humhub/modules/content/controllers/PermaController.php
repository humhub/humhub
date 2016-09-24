<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\content\models\WallEntry;
use humhub\modules\content\models\Content;
use yii\web\HttpException;

/**
 * PermaController is used to create permanent links to content.
 *
 * @package humhub.modules_core.wall.controllers
 * @since 0.5
 * @author Luke
 */
class PermaController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index', 'wall-entry']
            ]
        ];
    }

    /**
     * Redirects to given HActiveRecordContent or HActiveRecordContentAddon
     */
    public function actionIndex()
    {
        $id = (int) Yii::$app->request->get('id', "");

        $content = Content::findOne(['id' => $id]);
        if ($content !== null) {
            return $this->redirect($content->getUrl());
        }

        throw new HttpException(404, Yii::t('ContentModule.controllers_PermaController', 'Could not find requested content!'));
    }

    /**
     * On given WallEntryId redirect the user to the corresponding content object.
     *
     * This is mainly used by ActivityStream or Permalinks.
     */
    public function actionWallEntry()
    {

        // Id of wall entry
        $id = Yii::$app->request->get('id', "");

        $wallEntry = WallEntry::find()->joinWith('content')->where(['wall_entry.id' => $id])->one();

        if ($wallEntry != null) {
            $obj = $wallEntry->content; // Type of IContent
            if ($obj) {
                return $this->redirect($obj->container->createUrl(null, array('wallEntryId' => $id)));
            }
        }

        throw new HttpException(404, Yii::t('ContentModule.controllers_PermaController', 'Could not find requested permalink!'));
    }

}

?>
