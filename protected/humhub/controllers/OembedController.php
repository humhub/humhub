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
    protected function getAccessRules()
    {
        return [['login']];
    }

    /**
     * Fetches oembed content for the posted urls.
     *
     * By default the media is always embedded: the editor previews a link the current user just
     * pasted. A client rendering text somebody else wrote posts `consent=1` and receives, for a
     * domain the user has not allowed, the same confirmation prompt a server-rendered richtext
     * shows ({@see UrlOembed::isAllowedDomain()}) - see `humhub.oembed.js` `load()`.
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $urls = Yii::$app->request->post('urls', []);
        $forceAllowedDomain = empty(Yii::$app->request->post('consent'));
        $result = [];
        foreach ($urls as $url) {
            $oembed = UrlOembed::getOEmbed($url, $forceAllowedDomain);
            if ($oembed) {
                $result[$url] = $oembed;
            } elseif (UrlOembed::hasOEmbedSupport($url)) {
                $result[$url] = null;
            }
        }

        return $this->asJson($result);
    }

    /**
     * Display the hidden embedded content
     */
    public function actionDisplay()
    {
        $this->forcePostRequest();

        $url = Yii::$app->request->post('url');
        if (empty($url)) {
            throw new HttpException(400, 'URL is not provided!');
        }

        $urlData = parse_url((string) $url);
        if (!isset($urlData['host'])) {
            throw new HttpException(400, 'Wrong URL!');
        }

        if (Yii::$app->request->post('alwaysShow', false)) {
            UrlOembed::saveAllowedDomain($urlData['host']);
        }

        $urlOembed = UrlOembed::findExistingOembed($url);

        return $this->asJson([
            'success' => true,
            'content' => $urlOembed ? $urlOembed->preview : UrlOembed::loadUrl($url),
        ]);
    }
}
