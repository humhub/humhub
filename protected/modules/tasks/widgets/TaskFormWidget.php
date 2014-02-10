<?php

class TaskFormWidget extends HWidget {

	public $workspace;

	public function run() {
		$this->render('form', array('workspace'=>$this->workspace));
	}
}

?>