<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\swiftmailer;

use Yii;

/**
 * Logger is a SwiftMailer plugin, which allows passing of the SwiftMailer internal logs to the
 * Yii logging mechanism. Each native SwiftMailer log message will be converted into Yii 'info' log entry.
 *
 * In order to catch logs written by this class, you need to setup a log route for 'yii\swiftmailer\Logger::add' category.
 * For example:
 *
 * ~~~
 * 'log' => [
 *     'targets' => [
 *         [
 *             'class' => 'yii\log\FileTarget',
 *             'categories' => ['yii\swiftmailer\Logger::add'],
 *         ],
 *     ],
 * ],
 * ~~~
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Logger implements \Swift_Plugins_Logger
{
    /**
     * @inheritdoc
     */
    public function add($entry)
    {
        Yii::info($entry, __METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        // do nothing
    }

    /**
     * @inheritdoc
     */
    public function dump()
    {
        return '';
    }
}