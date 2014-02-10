<?php

class MyTasksWidget extends HWidget {

	protected $themePath = 'modules/tasks';

	/**
	 * Creates the Wall Widget
	 */
	public function run() {


		$tasks = Task::GetUsersOpenTasks();

		if (count($tasks) > 0) {
			$this->render('mytasks', array('tasks'=>$tasks));
		}
	}

}

?>