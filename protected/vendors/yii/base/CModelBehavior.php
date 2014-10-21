<?php
/**
 * CModelBehavior class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CModelBehavior is a base class for behaviors that are attached to a model component.
 * The model should extend from {@link CModel} or its child classes.
 *
 * @property CModel $owner The owner model that this behavior is attached to.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 */
class CModelBehavior extends CBehavior
{
	/**
	 * Declares events and the corresponding event handler methods.
	 * The default implementation returns 'onAfterConstruct', 'onBeforeValidate' and 'onAfterValidate' events and handlers.
	 * If you override this method, make sure you merge the parent result to the return value.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 * @see CBehavior::events
	 */
	public function events()
	{
		return array(
			'onAfterConstruct'=>'afterConstruct',
			'onBeforeValidate'=>'beforeValidate',
			'onAfterValidate'=>'afterValidate',
		);
	}

	/**
	 * Responds to {@link CModel::onAfterConstruct} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 */
	protected function afterConstruct($event)
	{
	}

	/**
	 * Responds to {@link CModel::onBeforeValidate} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the validation process.
	 * @param CModelEvent $event event parameter
	 */
	protected function beforeValidate($event)
	{
	}

	/**
	 * Responds to {@link CModel::onAfterValidate} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link owner}.
	 * @param CEvent $event event parameter
	 */
	protected function afterValidate($event)
	{
	}
}
