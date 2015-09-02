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
 * HForm enables bootstrap support for CForm
 *
 * Renamed Div Class "row" to "zrow"
 *
 * @package humhub.libs
 * @since 0.5
 * @author Luke
 */
class HForm extends CForm {

    public $inputElementClass = "HFormInputElement";

    /**
     * Renders the {@link buttons} in this form.
     * @return string the rendering result
     */
    public function renderButtons() {
        $output = '';
        foreach ($this->getButtons() as $button)
            $output.=$this->renderElement($button);
        return $output !== '' ? "<div class=\"form-group-buttons buttons\">" . $output . "</div>\n" : '';
    }

    // form-control

    /**
     * Renders a single element which could be an input element, a sub-form, a string, or a button.
     * @param mixed $element the form element to be rendered. This can be either a {@link CFormElement} instance
     * or a string representing the name of the form element.
     * @return string the rendering result
     */
    public function renderElement($element) {
        if (is_string($element)) {
            if (($e = $this[$element]) === null && ($e = $this->getButtons()->itemAt($element)) === null)
                return $element;
            else
                $element = $e;
        }
        if ($element->getVisible()) {
            if ($element instanceof CFormInputElement) {
                if ($element->type === 'hidden')
                    return "<div style=\"visibility:hidden\">\n" . $element->render() . "</div>\n";
                else
                    return "<div class=\"form-group field_{$element->name}\">\n" . $element->render() . "</div>\n";
            }
            elseif ($element instanceof CFormButtonElement)
                return $element->render() . "\n";
            else
                return $element->render();
        }
        return '';
    }

}

?>
