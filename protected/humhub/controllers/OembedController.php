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
use yii\web\HttpException;

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

    /**
     * Confirm to show directly content of the domain/source
     */
    public function actionConfirmUrl()
    {
        $this->forcePostRequest();

        $url = Yii::$app->request->post('url');
        if (empty($url)) {
            throw new HttpException(400, 'URL is not provided!');
        }

        $url = parse_url($url);
        if (!isset($url['host'])) {
            throw new HttpException(400, 'Wrong URL!');
        }

        UrlOembed::saveAllowedDomain($url['host']);

        return $this->asJson([
            'success' => true,
        ]);
    }
}
