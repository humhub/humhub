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

        $text = str_replace("\n", "<br />\n", $text);

        // get user details from guids
        $text = self::translateUserMentioning($text, true);

        return $text;
    }

    /**
     * Translate guids from users to username
     * @param strint $text Contains the complete message
     * @param boolean $buildAnchors Wrap the username with a link to the profile, if it's true
     *
     */
    public static function translateUserMentioning($text, $buildAnchors = true)
    {
        // save hits of @ char
        $hits = substr_count($text, ' @');

        // loop for every founded @ char
        for ($i = 0; $i < $hits; $i++) {

            // extract user guid
            $guid = substr($text, strpos($text, ' @'), 38);

            // load user row from database
            $user = User::model()->findByAttributes(array('guid' => substr($guid, 2)));

            if ($user !== null) {
                // make user clickable if Html is allowed
                if ($buildAnchors == true) {
                    $link = ' <a href="' . $user->getProfileUrl() . '" target="_self">' . $user->getDisplayName() . '</a>';
                } else {
                    $link = " " . $user->getDisplayName();
                }

                // replace guid with profile link and username
                $text = str_replace($guid, $link, $text);
            }
        }

        return $text;
    }

}

?>
