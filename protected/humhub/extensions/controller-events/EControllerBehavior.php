<?php
class EControllerBehavior extends CBehavior {
	public function events() {
		return array(
			'onBeforeAction' => 'beforeAction',
			'onAfterAction' => 'afterAction',
			'onBeforeRender' => 'beforeRender',
			'onAfterRender' => 'afterRender',
		);
	}

	protected function beforeAction(EControllerEvent $event) {}

	protected function afterAction(EControllerEvent $event) {}

	protected function beforeRender(EControllerEvent $event) {}

	protected function afterRender(EControllerEvent $event) {}
}
