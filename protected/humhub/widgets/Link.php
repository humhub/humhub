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

    public static function to($text, $url = '#', $pjax = true) {
        return self::asLink($text, $url)->pjax($pjax);
    }

    public static function withAction($text, $action, $url = null, $target = null) {
        return self::asLink($text)->action($action,$url, $target);
    }

    /**
     * @param $url
     * @return $this
     */
    public function post($url)
    {
        // Note data-method automatically prevents pjax
        $this->href($url);
        $this->htmlOptions['data-method'] = 'POST';
        return $this;
    }

    /**
     * @param string $url
     * @param bool $pjax
     * @return $this
     */
    public function href($url = '#', $pjax = true)
    {
        $this->link($url);
        $this->pjax($pjax);
        return $this;
    }

    public function target($target)
    {
        $this->htmlOptions['target'] = $target;
        return $this;
    }

    public function blank()
    {
        return $this->target('_blank');
    }
}
