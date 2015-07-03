<?php

namespace humhub\modules\space\widgets;

use Yii;
use \yii\base\Widget;

/**
 * SpacePickerWidget displays a space picker instead of an input field.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('application.modules_core.user.widgets.SpacePickerWidget',array(
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
class Picker extends Widget
{

    /**
     * Id of input element which should replaced
     *
     * @var type
     */
    public $inputId = "";

    /**
     * JSON Search URL - default: browse/searchJson
     *
     * The token -keywordPlaceholder- will replaced by the current search query.
     *
     * @var String Url with -keywordPlaceholder-
     */
    public $spaceSearchUrl = "";

    /**
     * Maximum spaces
     *
     * @var type
     */
    public $maxSpaces = 10;

    /**
     * @var CModel the data model associated with this widget. (Optional)
     */
    public $model = null;

    /**
     * @var string the attribute associated with this widget. (Optional)
     * The name can contain square brackets (e.g. 'name[1]') which is used to collect tabular data input.
     */
    public $attribute = null;

    /**
     * Initial value
     * Comma separated list of space guids
     *
     * @var string
     */
    public $value = "";

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        // Try to get current field value, when model & attribute attributes are specified.
        if ($this->model != null && $this->attribute != null) {
            $attribute = $this->attribute;
            $this->value = $this->model->$attribute;
        }

        if ($this->spaceSearchUrl == "")
            $this->spaceSearchUrl = \yii\helpers\Url::to(['/space/browse/search-json', ['keyword' => '-keywordPlaceholder-']]);


        return $this->render('spacePicker', array(
                    'spaceSearchUrl' => $this->spaceSearchUrl,
                    'maxSpaces' => $this->maxSpaces,
                    'currentValue' => $this->value,
                    'inputId' => $this->inputId,
        ));
    }

}

?>
