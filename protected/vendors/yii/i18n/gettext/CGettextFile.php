<?php
/**
 * CGettextFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGettextFile is the base class for representing a Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n.gettext
 * @since 1.0
 */
abstract class CGettextFile extends CComponent
{
	/**
	 * Loads messages from a file.
	 * @param string $file file path
	 * @param string $context message context
	 * @return array message translations (source message => translated message)
	 */
	abstract public function load($file,$context);
	/**
	 * Saves messages to a file.
	 * @param string $file file path
	 * @param array $messages message translations (message id => translated message).
	 * Note if the message has a context, the message id must be prefixed with
	 * the context with chr(4) as the separator.
	 */
	abstract public function save($file,$messages);
}
