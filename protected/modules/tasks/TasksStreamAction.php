<?php

/**
 * StreamAction returns entries of an given wall
 *
 * @author Luke
 */
class TasksStreamAction extends StreamAction {

	/**
	 * Inject Tasks Specific SQL
	 */
	protected function prepareSQL() {
		$this->sqlWhere .= " AND object_model='Task'";
		parent::prepareSQL();
	}
	
	
	
	/**
	 * Handle Task  Specific Filters
	 */
	protected function setupFilterSQL() {
		
	
		if (in_array('tasks_meAssigned', $this->filters) || in_array('tasks_open', $this->filters) ||  in_array('tasks_finished', $this->filters) || in_array('tasks_notassigned', $this->filters) || in_array('tasks_byme', $this->filters) ) {

                        $this->sqlJoin .= " LEFT JOIN task ON content.object_id=task.id AND content.object_model = 'Task'";

			if (in_array('tasks_meAssigned', $this->filters)) {
				$this->sqlJoin .= " LEFT JOIN task_user ON task.id=task_user.task_id AND task_user.user_id= '".Yii::app()->user->id."'";
				$this->sqlWhere .= " AND task_user.id is not null";
			}
			
			if (in_array('tasks_notassigned', $this->filters)) {
				$this->sqlWhere .= " AND (SELECT COUNT(*) FROM task_user WHERE task_id=task.id) = 0 ";
			}
			
			if (in_array('tasks_byme', $this->filters)) {
				$this->sqlWhere .= " AND task.created_by = '".Yii::app()->user->id."'";
			}

			if (in_array('tasks_open', $this->filters)) {
				$this->sqlWhere .= " AND task.status = '".Task::STATUS_OPEN."'";
			}

			if (in_array('tasks_finished', $this->filters)) {
				$this->sqlWhere .= " AND task.status = '".Task::STATUS_FINISHED."'";
			}
			
		}		
		
		
		parent::setupFilterSQL();
		
		
	}
	
}

?>
