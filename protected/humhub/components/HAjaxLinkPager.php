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
 * HAjaxLinkPager extends the CLinkPager with some ajax functions.
 *
 * @package humhub.components
 * @since 0.5
 */
class HAjaxLinkPager extends CLinkPager {

    /**
     * @var String html dom id of target
     */
    public $ajaxContentTarget = "SomeCoolDivId";

    /**
     * @var ind counter
     */
    protected $ajaxIdCounter = 0;

    /**
     * Creates a page button.
     *
     * You may override this method to customize the page buttons.
     * @param string $label the text label for the button
     * @param integer $page the page number
     * @param string $class the CSS class for the page button. This could be 'page', 'first', 'last', 'next' or 'previous'.
     * @param boolean $hidden whether this page button is visible
     * @param boolean $selected whether this page button is selected
     *
     * @return string the generated button
     */
    protected function createPageButton($label, $page, $class, $hidden, $selected) {

        $this->ajaxIdCounter++; // For Unique Ajax Link IDs

        if ($hidden || $selected)
            $class.=' ' . ($hidden ? self::CSS_HIDDEN_PAGE : self::CSS_SELECTED_PAGE);

        $ajaxLink = HHtml::ajaxLink(
                        $label, $this->createPageUrl($page), array(
                    'success' => "function(html) { jQuery('" . $this->ajaxContentTarget . "').replaceWith(html); }",
                        ), array(
                    'id' => "siPaginatorLink_" . $page . "_" . $this->ajaxIdCounter,
                        )
        );

        return '<li class="' . $class . '">' . $ajaxLink . '</li>';
    }

}
