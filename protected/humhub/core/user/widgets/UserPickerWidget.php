<?php

/**
 * UserPickerWidget displays a user picker instead of an input field.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('application.modules_core.user.widgets.UserPickerWidget',array(
 *     'name'=>'users',
 *
 *     // additional javascript options for the date picker plugin
 *     'options'=>array(
 *         'showAnim'=>'fold',
 *     ),
 *     'htmlOptions'=>array(
 *         'style'=>'height:20px;'
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the userpicker plugin. Please refer to
 * the documentation for possible options (name-value pairs).
 *
 * @package humhub.modules_core.user.widgets
 * @since 0.5
 * @author Luke
 */
class UserPickerWidget extends HWidget
{

    /**
     * Id of input element which should replaced
     *
     * @var type
     */
    public $inputId = "";

    /**
     * JSON Search URL - defaults: search/json
     *
     * The token -keywordPlaceholder- will replaced by the current search query.
     *
     * @var String Url with -keywordPlaceholder-
     */
    public $userSearchUrl = "";

    /**
     * Maximum users
     *
     * @var type
     */
    public $maxUsers = 50;

    /**
     * Set guid for the current user
     *
     * @var type string
     */
    public $userGuid = "";

    /**
     * Set focus to input or not
     *
     * @var type boolean
     */
    public $focus = false;

    /**
     * @var CModel the data model associated with this widget.
     */
    public $model = null;

    /**
     * @var string the attribute associated with this widget.
     * The name can contain square brackets (e.g. 'name[1]') which is used to collect tabular data input.
     */
    public $attribute = null;


    /**
     * @var string for input placeholder attribute.
     */
    public $placeholderText = "";

    /**
     * Inits the User Picker
     *
     */
    public function init()
    {

        // Default user search for all users
        if ($this->userSearchUrl == "") {

            // provide the space id if the widget is calling from a space
            if (Yii::app()->getController()->id == 'space') {
                $this->userSearchUrl = Yii::app()->getController()->createUrl('//user/search/json', array('keyword' => '-keywordPlaceholder-', 'space_id' => Yii::app()->getController()->getSpace()->id));
            } else {
                $this->userSearchUrl = Yii::app()->getController()->createUrl('//user/search/json', array('keyword' => '-keywordPlaceholder-'));
            }
        }

        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.highlight.min.js', CClientScript::POS_END);
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/userpicker.js', (Yii::app()->request->isAjaxRequest) ? CClientScript::POS_BEGIN : CClientScript::POS_END);
    }

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {

        // Try to get current field value, when model & attribute attributes are specified.
        $currentValue = "";
        if ($this->model != null && $this->attribute != null) {
            $attribute = $this->attribute;
            $currentValue = $this->model->$attribute;
        }

        $this->render('userPicker', array(
            'userSearchUrl' => $this->userSearchUrl,
            'maxUsers' => $this->maxUsers,
            'currentValue' => $currentValue,
            'inputId' => $this->inputId,
            'focus' => $this->focus,
            'userGuid' => $this->userGuid,
            'placeholderText' => $this->placeholderText,
        ));
    }

}

?>
