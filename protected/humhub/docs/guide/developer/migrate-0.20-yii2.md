[Back to 0.20 Migration](dev-migrate-0.20.md)

# Yii2 Migration Notes

See Yii Migration Guide: [http://www.yiiframework.com/doc-2.0/guide-intro-upgrade-from-v1.html](http://www.yiiframework.com/doc-2.0/guide-intro-upgrade-from-v1.html)

Notes:

- Use Namespaces!
- Yii::app() -> Yii::$app
- Use [] instead of array() - Optional
- Model: Validator  
 - Use array for multiple attributes
 - Validator changes Numeric->Integer ... 
 - String validator doesn't allow Integer Types (cast!)
 - Scenarios now in separate methods secenarios()
 - User::model()->findByPk($idy); -> User::findOne(['id'=>$id); 
 - Check beforeSave/afterSave when overwriting they may have parameters
 	- Better use $insert when available instead of $this->isNewRecord	
 - Make tableName method static 
 
- Views:
	- ClientScript removed e.g. Yii::app()->clientScript->registerScriptFile
	- New Widget calls  WidgetClass::widget([options]) & echo it!
- Controllers
	-  Always return render action (also Widgets)
	-  camel case actions e.g. actionEditItem new Url: edit-item
	-  Easier: JSON Output
       Yii::$app->response->format = 'json'; return $json; 
- createUrl removed -> Url::to()
- Behaviors
	- $this->getOwner() replaced by $this->owner
- Html (CHtml)
	- reduced (e.g. no AjaxButton - use: \humhub\widgets\AjaxButton instead
	- Html::link -> confirm  changed to data-confirm
	-
	




