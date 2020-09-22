<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\controllers;


use humhub\components\Controller;
use humhub\models\UrlOembed;
use Yii;

/**
 * @since 1.3
 */
class OembedController extends Controller
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [['login']];
    }

    /**
     * Fetches oembed content for the posted urls.
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $urls = Yii::$app->request->post('urls', []);
        $result = [];
        foreach ($urls as $url) {
            $oembed = UrlOembed::getOEmbed($url);
            if ($oembed) {
                $result[$url] = $oembed;
            }
        }

        return $this->asJson($result);
    }
}
