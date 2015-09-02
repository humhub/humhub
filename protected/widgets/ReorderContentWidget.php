<?php
/**
 * ReorderContentWidget is a javascript widget that inserts the code to enable sorting by drag and drop for the given content.
 * It is configured by the following parameters:
 * $containerClassName the class name of the container dom element, that contains the sortable items.
 * $sortableItemClassName the class name of the sortable items, lying in the container.
 * $url the url the ajax request will be sent to. You have to implement an action, that processes the request. Use the logic provided in ReorderContentBehavior, that already implements the sorting logic.
 * $additionalAjaxParams an array of additional parameters, that will be appended to the ajax request, sent to the given url. (for example sguid or uguid)
 * For proper functionality provide these Parameters when using the widget.
 *
 * @package humhub.modules.linklist.widgets
 * @author Sebastian Stumpf
 */
class ReorderContentWidget extends HWidget {

	public $containerClassName;
	public $sortableItemClassName;
	public $url;
	public $additionalAjaxParams;
	
    public function run() {
    	$assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/resources', true, 0, defined('YII_DEBUG'));
    	Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery-ui-core-int.min.js');
        $this->render('reorderContent', array(
        	'containerClassName' => $this->containerClassName,
        	'sortableItemClassName' => $this->sortableItemClassName,
        	'url' => $this->url,
        	'additionalAjaxParams' => $this->additionalAjaxParams,
        ));
    }

}

?>