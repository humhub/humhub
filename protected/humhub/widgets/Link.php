<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 13.06.2017
 * Time: 22:32
 */

namespace humhub\widgets;


use humhub\components\Widget;
use humhub\libs\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Helper class for creating links.
 *
 * @package humhub\widgets
 */
class Link extends Button
{

    public $_link = true;


    public function to($url)
    {
        $this->link($url);
    }
}