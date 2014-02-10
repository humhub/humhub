<?php
/**
 * CConsoleCommandBehavior class file.
 *
 * @author Evgeny Blinov <e.a.blinov@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CConsoleCommandBehavior is a base class for behaviors that are attached to a console command component.
 *
 * @property CConsoleCommand $owner The owner model that this behavior is attached to.
 *
 * @author Evgeny Blinov <e.a.blinov@gmail.com>
 * @package system.console
 * @since 1.1.11
 */
class CConsoleCommandBehavior extends CBehavior
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
		    'onBeforeAction' => 'beforeAction',
		    'onAfterAction' => 'afterAction'
		);
	}
	/**
	 * Responds to {@link CConsoleCommand::onBeforeAction} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CConsoleCommandEvent $event event parameter
	 */
	protected function beforeAction($event)
	{
	}

	/**
	 * Responds to {@link CConsoleCommand::onAfterAction} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CConsoleCommandEvent $event event parameter
	 */
	protected function afterAction($event)
	{
	}
}