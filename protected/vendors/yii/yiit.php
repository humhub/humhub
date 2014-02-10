<?php
/**
 * Yii test script file.
 *
 * This script is meant to be included at the beginning
 * of the unit and function test bootstrap files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

// disable Yii error handling logic
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',false);

require_once(dirname(__FILE__).'/yii.php');

Yii::import('system.test.CTestCase');
Yii::import('system.test.CDbTestCase');
Yii::import('system.test.CWebTestCase');
