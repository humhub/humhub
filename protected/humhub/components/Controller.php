<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;

/**
 * Description of Controller
 *
 * @author luke
 */
class Controller extends \yii\web\Controller
{

    public $subLayout;

    public function init()
    {
        parent::init();
    }

    public function forcePostRequest()
    {
        if (\Yii::$app->request->method != 'POST') {
            print "Invalid method!";
            die();
        }
    }

    /**
     * Create Redirect for AJAX Requests which output goes into HTML content.
     * Is an alternative method to redirect, for ajax responses.
     */
    public function htmlRedirect($url = "")
    {
        return $this->renderPartial('@humhub/views/htmlRedirect.php', array('url' => $url));
    }

}
