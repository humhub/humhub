<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * HHtml extends the CHtml class with some extra helper functions.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.libs
 * @since 0.5
 */
class HHtml extends CHtml
{

    /**
     * Fixes the default yii ajaxLink with unregistering onClick Handlers first, before set new one.
     *
     * @param type $text
     * @param type $url
     * @param type $ajaxOptions
     * @param type $htmlOptions
     * @return type
     */
    public static function ajaxLink($text, $url, $ajaxOptions = array(), $htmlOptions = array())
    {

        // Auto set csrf token
        if (isset($ajaxOptions['data']) && is_array($ajaxOptions['data']) && !isset($ajaxOptions['data'][Yii::app()->request->csrfTokenName])) {
            $ajaxOptions['data'][Yii::app()->request->csrfTokenName] = Yii::app()->request->csrfToken;
        }

        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
            $cs = Yii::app()->getClientScript();
            $cs->registerScript('Yii.HHtml.#' . $id, "jQuery('body').off('click','#{$id}');");
        } else {
            $htmlOptions['id'] = Helpers::GetUniqeId();
        }

        return parent::ajaxLink($text, $url, $ajaxOptions, $htmlOptions);
    }

    /**
     * Generates a push button that can submit the current form in POST method.
     * @param string $label the button label
     * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
     * @param array $ajaxOptions AJAX options (see {@link ajax})
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button
     */
    public static function ajaxSubmitButton($label, $url, $ajaxOptions = array(), $htmlOptions = array())
    {
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
            $cs = Yii::app()->getClientScript();
            $cs->registerScript('Yii.HHtml.#' . $id, "jQuery('body').off('click','#{$id}');");
        } else {
            $htmlOptions['id'] = Helpers::GetUniqeId();
        }

        if (!isset($htmlOptions['name'])) {
            $htmlOptions['name'] = Helpers::GetUniqeId();
        }

        return parent::ajaxSubmitButton($label, $url, $ajaxOptions, $htmlOptions);
    }

    /**
     * Generates a push button that can initiate AJAX requests.
     * @param string $label the button label
     * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
     * @param array $ajaxOptions AJAX options (see {@link ajax})
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated button
     */
    public static function ajaxButton($label, $url, $ajaxOptions = array(), $htmlOptions = array())
    {
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
            $cs = Yii::app()->getClientScript();
            $cs->registerScript('Yii.HHtml.#' . $id, "jQuery('body').off('click','#{$id}');");
        } else {
            $htmlOptions['id'] = Helpers::GetUniqeId();
        }

        return parent::ajaxButton($label, $url, $ajaxOptions, $htmlOptions);
    }

    public static function encodeJSParam($val)
    {

        $val = str_replace("'", "\'", $val);
        $val = str_replace("'", '\"', $val);

        return $val;
    }

    /**
     * Creates a Time Ago compat stamp
     *
     * @param type $timestamp
     * @return type
     */
    public static function timeago($timestamp)
    {
        if (is_numeric($timestamp)) {
            $timestamp = date('Y-m-d H:i:s', $timestamp);
        }

        Yii::app()->clientScript->registerScript('timeago', '$(".time").timeago();');
        return '<span class="time" title="' . $timestamp . '">' . $timestamp . '</span>';
    }

    /**
     * Generates a POST Link using a Form
     *
     * @param type $text
     * @param type $url
     * @param array $htmlOptions
     * @return string
     */
    public static function postLink($text, $url = '#', $htmlOptions = array())
    {

        $id = "";
        if (!isset($htmlOptions['id'])) {
            $id = Helpers::GetUniqeId();
            $htmlOptions['id'] = $id;
        } else {
            $id = $htmlOptions['id'];
        }

        // Build Click JS
        $clickJS = '$("#postLink_' . $id . '").submit(); return true;';
        if (isset($htmlOptions['confirm'])) {
            $confirm = 'confirm(\'' . CJavaScript::quote($htmlOptions['confirm']) . '\')';
            $clickJS = "if(!$confirm) return false;" . $clickJS;
            unset($htmlOptions['confirm']);
        }

        $output = self::link($text, "#", $htmlOptions);

        // Generate this at the end of the page
        $hiddenFormHtml = "<div style='display:none'>";
        $hiddenFormHtml .= self::beginForm($url, 'POST', array('id' => 'postLink_' . $id));
        $hiddenFormHtml .= self::endForm();
        $hiddenFormHtml .= "</div>";


        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerScript($id, '$("#' . $id . '").on("click", function(){ ' . $clickJS . ' });');
        $cs->registerHtml($id, $hiddenFormHtml);

        return $output;
    }

    /**
     * Converts an given Ascii Text into a HTML Block
     * @param boolean $allowHtml transform user names in links
     * @param boolean $allowEmbed Sets if comitted video links will embedded
     *
     * Tasks:
     *      nl2br
     *      oembed urls
     */
    public static function enrichText($text)
    {

        $maxOembedCount = 3; // Maximum OEmbeds
        $oembedCount = 0; // OEmbeds used


        $text = preg_replace_callback('/http(.*?)(\s|$)/i', function ($match) use (&$oembedCount, &$maxOembedCount) {

            // Try use oembed
            if ($maxOembedCount > $oembedCount) {
                $oembed = UrlOembed::GetOembed($match[0]);
                if ($oembed) {
                    $oembedCount++;
                    return $oembed;
                }
            }

            return HHtml::link($match[0], $match[0], array('target' => '_blank'));
        }, $text);


        # breaks links!?
        #$text = nl2br($text);

        // get user and space details from guids
        $text = self::translateMentioning($text, true);

        // create image tag for emojis
        $text = self::translateEmojis($text);

        // replace all line breaks with <br> tag
        $text = str_replace("\n", "<br />\n", $text);

        return $text;
    }

    /**
     * Translate guids from users to username
     * @param strint $text Contains the complete message
     * @param boolean $buildAnchors Wrap the username with a link to the profile, if it's true
     *
     */
    public static function translateMentioning($text, $buildAnchors = true)
    {

        // add white space at the beginning to get even a mentioned user from the first character
        $text = " " . $text;

        // save hits of @ char
        $hits = substr_count($text, ' @-');

        // loop for every founded @ char
        for ($i = 0; $i < $hits; $i++) {

            // extract mention data
            $data = substr($text, strpos($text, ' @-'), 40);

            // get type (user or space)
            $type = substr($data, 3, 1);

            // extract guid
            $guid = substr($data, 4);

            if ($type == 'u') {

                // load user row from database
                $user = User::model()->findByAttributes(array('guid' => $guid));

                if ($user !== null) {
                    // make user clickable if Html is allowed
                    if ($buildAnchors == true) {
                        $link = ' <a href="' . $user->getProfileUrl() . '" target="_self">@' . $user->getDisplayName() . '</a>';
                    } else {
                        $link = " @" . $user->getDisplayName();
                    }

                    // replace guid with profile link and username
                    $text = str_replace($data, $link, $text);
                }
            } else if ($type == 's') {

                // load space row from database
                $space = Space::model()->findByAttributes(array('guid' => $guid));

                if ($space !== null) {
                    // make space clickable if Html is allowed
                    if ($buildAnchors == true) {
                        $link = ' <a href="' . $space->getUrl() . '" target="_self">@' . $space->name . '</a>';
                    } else {
                        $link = " @" . $space->name;
                    }

                    // replace guid with profile link and username
                    $text = str_replace($data, $link, $text);
                }

            }


        }

        return $text;
    }


    /**
     * Replace emojis from text to img tag
     * @param string $text Contains the complete message
     * @param string $show show smilies or remove it (for activities and notifications)
     *
     */
    public static function translateEmojis($text, $show = true)
    {
        $s = explode(";", $text);

        for ($i = 1; $i <= count($s) - 1; $i += 2) {
            if ($show == true) {
                $text = str_replace(';' . $s[$i] . ';', ' <img class="atwho-emoji" data-emoji-name=";' . $s[$i] . ';" src="' . Yii::app()->baseUrl . '/img/emoji/' . $s[$i] . '.png"/>', $text);
            } else {
                $text = str_replace(' ;' . $s[$i] . ';', '', $text);
            }
        }

        return $text;

    }


    /**
     * ActiveForm Variant of DateTime Field
     *
     * @param type $model
     * @param type $attribute
     * @param type $htmlOptions
     * @param type $pickerOptions See HHTML::dateTimeField for details.
     * @return type
     */
    public static function activeDateTimeField($model, $attribute, $htmlOptions = array(), $pickerOptions = array())
    {
        $value = self::resolveValue($model, $attribute);

        self::resolveNameID($model, $attribute, $htmlOptions);
        $name = $htmlOptions['name'];

        self::clientChange('change', $htmlOptions);

        return self::dateTimeField($name, $value, $htmlOptions, $pickerOptions);
    }

    /**
     * Standalone DateTime Field.
     * Internal Format: 2017-01-01 00:00:00
     *
     * Picker Options Attributes:
     *      pickDate = TRUE/false
     *      pickTime = true/FALSE
     *      displayFormat = Default: DD.MM.YYYY[ - HH:mm]
     *
     * @param String $name
     * @param String $value
     * @param Array $htmlOptions
     * @param Array $pickerOptions
     *
     * @return String datetimeField HTML
     */
    public static function dateTimeField($name, $value = "", $htmlOptions = array(), $pickerOptions = array())
    {
        // load js for datetimepicker component
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/js/moment-with-locales.min.js', CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/js/bootstrap-datetimepicker.js', CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/js/datetimefield-init.js', CClientScript::POS_END
        );

        // load css for datetimepicker component
        Yii::app()->clientScript->registerCssFile(
            Yii::app()->baseUrl . '/css/bootstrap-datetimepicker.css'
        );

        if (isset($pickerOptions['pickTime']) && $pickerOptions['pickTime'] == true) {
            $htmlOptions['data-options-pickTime'] = "true";
        }

        if (isset($pickerOptions['displayFormat'])) {
            $htmlOptions['data-options-displayFormat'] = $pickerOptions['displayFormat'];
        }

        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'hhtml-datetime-field';
        } else {
            $htmlOptions['class'] .= ' hhtml-datetime-field';
        }

        return self::textField($name, $value, $htmlOptions);
    }

    /**
     * Returns Stylesheet Classname based on file extension
     *
     * @return string CSS Class
     */
    public static function getMimeIconClassByExtension($ext)
    {
        // Word
        if ($ext == 'doc' || $ext == 'docx') {
            return "mime-word";
            // Excel
        } else if ($ext == 'xls' || $ext == 'xlsx') {
            return "mime-excel";
            // Powerpoint
        } else if ($ext == 'ppt' || $ext == 'pptx') {
            return "mime-excel";
            // PDF
        } else if ($ext == 'pdf') {
            return "mime-pdf";
            // Archive
        } else if ($ext == 'zip' || $ext == 'rar' || $ext == 'tar' || $ext == '7z') {
            return "mime-zip";
            // Audio
        } else if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
            return "mime-image";
            // Audio
        } else if ($ext == 'mp3' || $ext == 'aiff' || $ext == 'wav') {
            return "mime-audio";
            // Adobe Flash
        } else if ($ext == 'swf' || $ext == 'fla' || $ext == 'air') {
            return "mime-flash";
            // Adobe Photoshop
        } else if ($ext == 'psd') {
            return "mime-photoshop";
            // Adobe Illustrator
        } else if ($ext == 'ai') {
            return "mime-illustrator";
            // other file formats
        } else {
            return "mime-file";
        }
    }

}

?>
