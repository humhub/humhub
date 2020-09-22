<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

/**
 * Stream Wrapper for older theme versions
 *
 * @deprecated since version 1.2
 * @author Luke
 */
class Stream extends \humhub\components\Widget
{

    public static function widget($config = [])
    {
        $config['class'] = \humhub\modules\stream\widgets\StreamViewer::class;
        return parent::widget($config);
    }

}
