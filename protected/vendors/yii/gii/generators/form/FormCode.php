<?php

class FormCode extends CCodeModel
{
	public $model;
	public $viewPath='application.views';
	public $viewName;
	public $scenario;

	private $_modelClass;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('model, viewName, scenario', 'filter', 'filter'=>'trim'),
			array('model, viewName, viewPath', 'required'),
			array('model, viewPath', 'match', 'pattern'=>'/^\w+[\.\w+]*$/', 'message'=>'{attribute} should only contain word characters and dots.'),
			array('viewName', 'match', 'pattern'=>'/^\w+[\\/\w+]*$/', 'message'=>'{attribute} should only contain word characters and slashes.'),
			array('model', 'validateModel'),
			array('viewPath', 'validateViewPath'),
			array('scenario', 'match', 'pattern'=>'/^\w+$/', 'message'=>'{attribute} should only contain word characters.'),
			array('viewPath', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'model'=>'Model Class',
			'viewName'=>'View Name',
			'viewPath'=>'View Path',
			'scenario'=>'Scenario',
		));
	}

	public function requiredTemplates()
	{
		return array(
			'form.php',
			'action.php',
		);
	}

	public function successMessage()
	{
		$output=<<<EOD
<p>The form has been generated successfully.</p>
<p>You may add the following code in an appropriate controller class to invoke the view:</p>
EOD;
		$code="<?php\n".$this->render($this->templatePath.'/action.php');
		return $output.highlight_string($code,true);
	}

	public function validateModel($attribute,$params)
	{
		if($this->hasErrors('model'))
			return;
		$class=@Yii::import($this->model,true);
		if(!is_string($class) || !$this->classExists($class))
			$this->addError('model', "Class '{$this->model}' does not exist or has syntax error.");
		elseif(!is_subclass_of($class,'CModel'))
			$this->addError('model', "'{$this->model}' must extend from CModel.");
		else
			$this->_modelClass=$class;
	}

	public function validateViewPath($attribute,$params)
	{
		if($this->hasErrors('viewPath'))
			return;
		if(Yii::getPathOfAlias($this->viewPath)===false)
			$this->addError('viewPath','View Path must be a valid path alias.');
	}

	public function prepare()
	{
		$templatePath=$this->templatePath;
		$this->files[]=new CCodeFile(
			Yii::getPathOfAlias($this->viewPath).'/'.$this->viewName.'.php',
			$this->render($templatePath.'/form.php')
		);
	}

	public function getModelClass()
	{
		return $this->_modelClass;
	}

	public function getModelAttributes()
	{
		$model=new $this->_modelClass($this->scenario);
		return $model->getSafeAttributeNames();
	}
}