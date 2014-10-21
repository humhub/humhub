<?php
/**
 * CDetailView class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDetailView displays the detail of a single data model.
 *
 * CDetailView is best used for displaying a model in a regular format (e.g. each model attribute
 * is displayed as a row in a table.) The model can be either an instance of {@link CModel}
 * or an associative array.
 *
 * CDetailView uses the {@link attributes} property to determines which model attributes
 * should be displayed and how they should be formatted.
 *
 * A typical usage of CDetailView is as follows:
 * <pre>
 * $this->widget('zii.widgets.CDetailView', array(
 *     'data'=>$model,
 *     'attributes'=>array(
 *         'title',             // title attribute (in plain text)
 *         'owner.name',        // an attribute of the related object "owner"
 *         'description:html',  // description attribute in HTML
 *         array(               // related city displayed as a link
 *             'label'=>'City',
 *             'type'=>'raw',
 *             'value'=>CHtml::link(CHtml::encode($model->city->name),
 *                                  array('city/view','id'=>$model->city->id)),
 *         ),
 *     ),
 * ));
 * </pre>
 *
 * @property CFormatter $formatter The formatter instance. Defaults to the 'format' application component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package zii.widgets
 * @since 1.1
 */
class CDetailView extends CWidget
{
	private $_formatter;

	/**
	 * @var mixed the data model whose details are to be displayed. This can be either a {@link CModel} instance
	 * (e.g. a {@link CActiveRecord} object or a {@link CFormModel} object) or an associative array.
	 */
	public $data;
	/**
	 * @var array a list of attributes to be displayed in the detail view. Each array element
	 * represents the specification for displaying one particular attribute.
	 *
	 * An attribute can be specified as a string in the format of "Name:Type:Label".
	 * Both "Type" and "Label" are optional.
	 *
	 * "Name" refers to the attribute name. It can be either a property (e.g. "title") or a sub-property (e.g. "owner.username").
	 *
	 * "Label" represents the label for the attribute display. If it is not given, "Name" will be used to generate the appropriate label.
	 *
	 * "Type" represents the type of the attribute. It determines how the attribute value should be formatted and displayed.
	 * It is defaulted to be 'text'.
	 * "Type" should be recognizable by the {@link formatter}. In particular, if "Type" is "xyz", then the "formatXyz" method
	 * of {@link formatter} will be invoked to format the attribute value for display. By default when {@link CFormatter} is used,
	 * these "Type" values are valid: raw, text, ntext, html, date, time, datetime, boolean, number, email, image, url.
	 * For more details about these types, please refer to {@link CFormatter}.
	 *
	 * An attribute can also be specified in terms of an array with the following elements:
	 * <ul>
	 * <li>label: the label associated with the attribute. If this is not specified, the following "name" element
	 * will be used to generate an appropriate label.</li>
	 * <li>name: the name of the attribute. This can be either a property or a sub-property of the model.
	 * If the below "value" element is specified, this will be ignored.</li>
	 * <li>value: the value to be displayed. If this is not specified, the above "name" element will be used
	 * to retrieve the corresponding attribute value for display. Note that this value will be formatted according
	 * to the "type" option as described below.</li>
	 * <li>type: the type of the attribute that determines how the attribute value would be formatted.
	 * Please see above for possible values.
	 * <li>cssClass: the CSS class to be used for this item. This option is available since version 1.1.3.</li>
	 * <li>template: the template used to render the attribute. If this is not specified, {@link itemTemplate}
	 * will be used instead. For more details on how to set this option, please refer to {@link itemTemplate}.
	 * This option is available since version 1.1.1.</li>
	 * <li>visible: whether the attribute is visible. If set to <code>false</code>, the table row for the attribute will not be rendered.
	 * This option is available since version 1.1.5.</li>
	 * </ul>
	 */
	public $attributes;
	/**
	 * @var string the text to be displayed when an attribute value is null. Defaults to "Not set".
	 */
	public $nullDisplay;
	/**
	 * @var string the name of the tag for rendering the detail view. Defaults to 'table'.
	 * If set to null, no tag will be rendered.
	 * @see itemTemplate
	 */
	public $tagName='table';
	/**
	 * @var string the template used to render a single attribute. Defaults to a table row.
	 * These tokens are recognized: "{class}", "{label}" and "{value}". They will be replaced
	 * with the CSS class name for the item, the label and the attribute value, respectively.
	 * @see itemCssClass
	 */
	public $itemTemplate="<tr class=\"{class}\"><th>{label}</th><td>{value}</td></tr>\n";
	/**
	 * @var array the CSS class names for the items displaying attribute values. If multiple CSS class names are given,
	 * they will be assigned to the items sequentially and repeatedly.
	 * Defaults to <code>array('odd', 'even')</code>.
	 */
	public $itemCssClass=array('odd','even');
	/**
	 * @var array the HTML options used for {@link tagName}
	 */
	public $htmlOptions=array('class'=>'detail-view');
	/**
	 * @var string the base script URL for all detail view resources (e.g. javascript, CSS file, images).
	 * Defaults to null, meaning using the integrated detail view resources (which are published as assets).
	 */
	public $baseScriptUrl;
	/**
	 * @var string the URL of the CSS file used by this detail view. Defaults to null, meaning using the integrated
	 * CSS file. If this is set false, you are responsible to explicitly include the necessary CSS file in your page.
	 */
	public $cssFile;

	/**
	 * Initializes the detail view.
	 * This method will initialize required property values.
	 */
	public function init()
	{
		if($this->data===null)
			throw new CException(Yii::t('zii','Please specify the "data" property.'));
		if($this->attributes===null)
		{
			if($this->data instanceof CModel)
				$this->attributes=$this->data->attributeNames();
			elseif(is_array($this->data))
				$this->attributes=array_keys($this->data);
			else
				throw new CException(Yii::t('zii','Please specify the "attributes" property.'));
		}
		if($this->nullDisplay===null)
			$this->nullDisplay='<span class="null">'.Yii::t('zii','Not set').'</span>';
		if(isset($this->htmlOptions['id']))
			$this->id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$this->id;

		if($this->baseScriptUrl===null)
			$this->baseScriptUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')).'/detailview';

		if($this->cssFile!==false)
		{
			if($this->cssFile===null)
				$this->cssFile=$this->baseScriptUrl.'/styles.css';
			Yii::app()->getClientScript()->registerCssFile($this->cssFile);
		}
	}

	/**
	 * Renders the detail view.
	 * This is the main entry of the whole detail view rendering.
	 */
	public function run()
	{
		$formatter=$this->getFormatter();
		if ($this->tagName!==null)
			echo CHtml::openTag($this->tagName,$this->htmlOptions);

		$i=0;
		$n=is_array($this->itemCssClass) ? count($this->itemCssClass) : 0;

		foreach($this->attributes as $attribute)
		{
			if(is_string($attribute))
			{
				if(!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/',$attribute,$matches))
					throw new CException(Yii::t('zii','The attribute must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));
				$attribute=array(
					'name'=>$matches[1],
					'type'=>isset($matches[3]) ? $matches[3] : 'text',
				);
				if(isset($matches[5]))
					$attribute['label']=$matches[5];
			}

			if(isset($attribute['visible']) && !$attribute['visible'])
				continue;

			$tr=array('{label}'=>'', '{class}'=>$n ? $this->itemCssClass[$i%$n] : '');
			if(isset($attribute['cssClass']))
				$tr['{class}']=$attribute['cssClass'].' '.($n ? $tr['{class}'] : '');

			if(isset($attribute['label']))
				$tr['{label}']=$attribute['label'];
			elseif(isset($attribute['name']))
			{
				if($this->data instanceof CModel)
					$tr['{label}']=$this->data->getAttributeLabel($attribute['name']);
				else
					$tr['{label}']=ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $attribute['name'])))));
			}

			if(!isset($attribute['type']))
				$attribute['type']='text';
			if(isset($attribute['value']))
				$value=is_object($attribute['value']) && get_class($attribute['value']) === 'Closure' ? call_user_func($attribute['value'],$this->data) : $attribute['value'];
			elseif(isset($attribute['name']))
				$value=CHtml::value($this->data,$attribute['name']);
			else
				$value=null;

			$tr['{value}']=$value===null ? $this->nullDisplay : $formatter->format($value,$attribute['type']);

			$this->renderItem($attribute, $tr);

			$i++;
		}

		if ($this->tagName!==null)
			echo CHtml::closeTag($this->tagName);
	}

	/**
	 * This method is used by run() to render item row
	 *
	 * @param array $options config options for this item/attribute from {@link attributes}
	 * @param string $templateData data that will be inserted into {@link itemTemplate}
	 * @since 1.1.11
	 */
	protected function renderItem($options,$templateData)
	{
		echo strtr(isset($options['template']) ? $options['template'] : $this->itemTemplate,$templateData);
	}

	/**
	 * @return CFormatter the formatter instance. Defaults to the 'format' application component.
	 */
	public function getFormatter()
	{
		if($this->_formatter===null)
			$this->_formatter=Yii::app()->format;
		return $this->_formatter;
	}

	/**
	 * @param CFormatter $value the formatter instance
	 */
	public function setFormatter($value)
	{
		$this->_formatter=$value;
	}
}
