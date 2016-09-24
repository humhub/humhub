<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use Yii;
use \yii\base\Widget;
use \humhub\modules\space\models\Space;

/**
 * Picker displays a space picker instead of an input field.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * 
 * echo humhub\modules\space\widgets\Picker::widget([
 *    'inputId' => 'space_filter',
 *    'value' => $spaceGuidsString,
 *    'maxSpaces' => 3
 * ]);
 *  
 * </pre>
 *
 * @since 0.5
 * @author Luke
 */
class Picker extends Widget
{

    /**
     * @var string The id of input element which should replaced
     */
    public $inputId = "";

    /**
     * JSON Search URL - default: /space/browse/search-json
     * The token -keywordPlaceholder- will replaced by the current search query.
     *
     * @var string the search url
     */
    public $spaceSearchUrl = "";

    /**
     * @var int the maximum of spaces
     */
    public $maxSpaces = 10;

    /**
     * @var \yii\base\Model the data model associated with this widget. (Optional)
     */
    public $model = null;

    /**
     * The name can contain square brackets (e.g. 'name[1]') which is used to collect tabular data input.
     * @var string the attribute associated with this widget. (Optional)
     */
    public $attribute = null;

    /**
     * @var string the initial value of comma separated space guids
     */
    public $value = "";

    /**
     * @var string placeholder message, when no space is set
     */
    public $placeholder = null;

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
            $this->spaceSearchUrl = \yii\helpers\Url::to(['/space/browse/search-json', 'keyword' => '-keywordPlaceholder-']);

        if ($this->placeholder === null) {
            $this->placeholder = Yii::t('SpaceModule.picker', 'Add {n,plural,=1{space} other{spaces}}', ['n' => $this->maxSpaces]);
        }

        // Currently populated spaces
        $spaces = [];
        foreach (explode(",", $this->value) as $guid) {
            $space = Space::findOne(['guid' => trim($guid)]);
            if ($space != null) {
                $spaces[] = $space;
            }
        }

        return $this->render('spacePicker', array(
                    'spaceSearchUrl' => $this->spaceSearchUrl,
                    'maxSpaces' => $this->maxSpaces,
                    'value' => $this->value,
                    'spaces' => $spaces,
                    'placeholder' => $this->placeholder,
                    'inputId' => $this->inputId,
        ));
    }

}

?>
