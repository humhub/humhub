<?php
class EController extends CController {
	/**
	 * This method is invoked at the beginning of {@link render()}.
	 */
	protected function beforeRender($view) {
		if ($this->hasEventHandler('onBeforeRender')) {
			$event = new EControllerEvent($this);
			$event->view = $view;
			$this->onBeforeRender($event);
			return $event->isValid;
		}
		return true;
	}

	/**
	 * This method is invoked after the specified view is rendered by calling {@link render()}.
	 */
	protected function afterRender($view, &$output) {
		if ($this->hasEventHandler('onAfterRender')) {
			$event = new EControllerEvent($this);
			$event->view = $view;
			$event->output = &$output;
			$this->onAfterRender($event);
		}
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 */
	protected function beforeAction($action) {
		if ($this->hasEventHandler('onBeforeAction')) {
			$event = new EControllerEvent($this);
			$event->action = $action;
			$this->onBeforeAction($event);
			return $event->isValid;
		}
		return true;
	}

	/**
	 * This method is invoked right after an action is executed.
	 */
	protected function afterAction($action) {
		if ($this->hasEventHandler('onAfterAction')) {
			$event = new EControllerEvent($this);
			$event->action = $action;
			$this->onAfterAction($event);
		}
	}

	/**
	 * This event is raised before the renderer.
	 * @param CEvent $event the event parameter
	 */
	public function onBeforeRender($event) {
		$this->raiseEvent('onBeforeRender',$event);
	}

	/**
	 * This event is raised after the renderer.
	 * @param CEvent $event the event parameter
	 */
	public function onAfterRender($event) {
		$this->raiseEvent('onAfterRender',$event);
	}

	/**
	 * This event is raised before an action is executed.
	 * @param CEvent $event the event parameter
	 */
	public function onBeforeAction($event) {
		$this->raiseEvent('onBeforeAction',$event);
	}

	/**
	 * This event is raised after an action is executed.
	 * @param CEvent $event the event parameter
	 */
	public function onAfterAction($event) {
		$this->raiseEvent('onAfterAction',$event);
	}

	/**
	 * Creates the action instance based on the action name.
	 * Additionally, this implementing checks attached behaviors for actions.
	 */
	public function createAction($actionID) {
		$action = parent::createAction($actionID);
		// search in behaviors
		if ($action === null) {
			foreach ($this->behaviors() as $behavior => $data) {
				$behavior = $this->{$behavior};
				if (is_subclass_of($behavior, 'IBehavior') && method_exists($behavior, 'action'.$actionID)) {
					return new CInlineAction($behavior, $actionID);
				}
			}
		}
		return $action;
	}
}
