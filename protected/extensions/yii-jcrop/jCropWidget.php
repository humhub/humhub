<?php
/**
 * Displays an image with jCrop sizing controls
 */
class jCropWidget extends CWidget {

	/**
	 * Specifies the image URL that is displayed in the <img> tag
	 * @var string $imageUrl
	 */
	public $imageUrl;
	
	/**
	 * The alt text for the image
	 * @var string $imageAlt
	 */
	public $imageAlt;
	
	/**
	 * The id of the form input element for the X coordinate
	 * @var int $formElementX
	 */
	public $formElementX;

	/**
	 * The id of the form input element for the Y coordinate
	 * @var int $formElementY
	 */
	public $formElementY;

	/**
	 * The id of the form input element for the width value
	 * @var int $formElementWidth
	 */
	public $formElementWidth;

	/**
	 * The id of the form input element for the height value
	 * @var int $formElementHeight
	 */
	public $formElementHeight;

	
	/**
	 * The image id of a preview box
	 * @var string $previewId
	 */
	public $previewId;

	/**
	 * Preview box width
	 * @var int $previewWidth
	 */
	public $previewWidth;

	/**
	 * Preview box height
	 * @var int $previewHeight
	 */
	public $previewHeight;
	
	/**
	 * key => value options that are rendered into the <img> tag
	 * @var array
	 */
	public $htmlOptions = array();

	/**
	 * key => options passed to JCrop
	 * @var array
	 */
	public $jCropOptions = array();

	/**
	 * counter to keep track of the global variables and IDs
	 * @var int
	 */
	private static $_COUNT = 1;

	
	public function init() {
	}

	public function run() {

		$assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__).'/resources', true, 0, defined('YII_DEBUG'));

		Yii::app()->clientScript->registerScriptFile($assetPrefix.'/jquery.Jcrop.min.js');
		Yii::app()->clientScript->registerScriptFile($assetPrefix.'/jquery.color.js');
		Yii::app()->clientScript->registerCssFile($assetPrefix.'/jquery.Jcrop.css');

		$count = self::$_COUNT;

		if(array_key_exists('id', $this->htmlOptions)) {
			$id = $this->htmlOptions['id'];
		} else {
			$id = 'yii-jcrop';
		}
		if (!$this->previewId) {
			$this->previewId = $id.'-preview';
			$this->previewWidth = 100;
			$this->previewHeight = 100;
		}
		
		$id.= '-'.$count;
		$this->htmlOptions['id'] = $id;

		$this->jCropOptions['onChange'] = 'js:updateCoords'.$count;
		$this->jCropOptions['onSelect'] = 'js:updateCoords'.$count;
		$options=CJavaScript::encode($this->jCropOptions); 
		$js = <<<EOF
jQuery("#{$id}").Jcrop({$options});
EOF;
		$updateCoordsCode = <<<EOF
			function updateCoords{$count}(c)
			{
				jQuery('#{$this->formElementX}').val(Math.round(c.x));
				jQuery('#{$this->formElementY}').val(Math.round(c.y));
				jQuery('#{$this->formElementWidth}').val(Math.round(c.w));
				jQuery('#{$this->formElementHeight}').val(Math.round(c.h));
				var rx = {$this->previewWidth} / c.w;
				var ry = {$this->previewHeight} / c.h;
				if ($('#{$this->previewId}') != undefined) {
					$('#{$this->previewId}').css({
						width: Math.round(rx * $('#{$id}').width() ) + 'px',
						height: Math.round(ry * $('#{$id}').height()) + 'px',
						marginLeft: '-' + Math.round(rx * c.x) + 'px',
						marginTop: '-' + Math.round(ry * c.y) + 'px'
					}); 
				}
			};
EOF;

		Yii::app()->clientScript->registerScript('updateCoords'.$count,$updateCoordsCode, CClientScript::POS_BEGIN);
		Yii::app()->clientScript->registerScript('Yii.'.get_class($this).'#'.$id, $js, CClientScript::POS_LOAD);
 		$this->render('jcrop', array('url'=>$this->imageUrl, 'alt'=>$this->imageAlt, 'htmlOptions' => $this->htmlOptions, 'id'=>$id));

		self::$_COUNT++;
	}

}
