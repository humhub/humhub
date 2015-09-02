<?php

class ModelGenerator extends CCodeGenerator
{
	public $codeModel='gii.generators.model.ModelCode';

	/**
	 * Provides autocomplete table names
	 * @param string $db the database connection component id
	 * @return string the json array of tablenames that contains the entered term $q
	 */
	public function actionGetTableNames($db)
	{
		if(Yii::app()->getRequest()->getIsAjaxRequest())
		{
			$all = array();
			if(!empty($db) && Yii::app()->hasComponent($db)!==false && (Yii::app()->getComponent($db) instanceof CDbConnection))
				$all=array_keys(Yii::app()->{$db}->schema->getTables());

			echo json_encode($all);
		}
		else
			throw new CHttpException(404,'The requested page does not exist.');
	}
}