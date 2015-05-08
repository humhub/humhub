<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\View\Helper\AbstractHelper as BaseAbstractHelper;

class FormElement extends BaseAbstractHelper
{
    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|FormElement
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render an element
     *
     * Introspects the element type and attributes to determine which
     * helper to utilize when rendering.
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        if ($element instanceof Element\Button) {
            $helper = $renderer->plugin('form_button');
            return $helper($element);
        }

        if ($element instanceof Element\Captcha) {
            $helper = $renderer->plugin('form_captcha');
            return $helper($element);
        }

        if ($element instanceof Element\Csrf) {
            $helper = $renderer->plugin('form_hidden');
            return $helper($element);
        }

        if ($element instanceof Element\Collection) {
            $helper = $renderer->plugin('form_collection');
            return $helper($element);
        }

        if ($element instanceof Element\DateTimeSelect) {
            $helper = $renderer->plugin('form_date_time_select');
            return $helper($element);
        }

        if ($element instanceof Element\DateSelect) {
            $helper = $renderer->plugin('form_date_select');
            return $helper($element);
        }

        if ($element instanceof Element\MonthSelect) {
            $helper = $renderer->plugin('form_month_select');
            return $helper($element);
        }

        $type = $element->getAttribute('type');

        if ('checkbox' == $type) {
            $helper = $renderer->plugin('form_checkbox');
            return $helper($element);
        }

        if ('color' == $type) {
            $helper = $renderer->plugin('form_color');
            return $helper($element);
        }

        if ('date' == $type) {
            $helper = $renderer->plugin('form_date');
            return $helper($element);
        }

        if ('datetime' == $type) {
            $helper = $renderer->plugin('form_date_time');
            return $helper($element);
        }

        if ('datetime-local' == $type) {
            $helper = $renderer->plugin('form_date_time_local');
            return $helper($element);
        }

        if ('email' == $type) {
            $helper = $renderer->plugin('form_email');
            return $helper($element);
        }

        if ('file' == $type) {
            $helper = $renderer->plugin('form_file');
            return $helper($element);
        }

        if ('hidden' == $type) {
            $helper = $renderer->plugin('form_hidden');
            return $helper($element);
        }

        if ('image' == $type) {
            $helper = $renderer->plugin('form_image');
            return $helper($element);
        }

        if ('month' == $type) {
            $helper = $renderer->plugin('form_month');
            return $helper($element);
        }

        if ('multi_checkbox' == $type) {
            $helper = $renderer->plugin('form_multi_checkbox');
            return $helper($element);
        }

        if ('number' == $type) {
            $helper = $renderer->plugin('form_number');
            return $helper($element);
        }

        if ('password' == $type) {
            $helper = $renderer->plugin('form_password');
            return $helper($element);
        }

        if ('radio' == $type) {
            $helper = $renderer->plugin('form_radio');
            return $helper($element);
        }

        if ('range' == $type) {
            $helper = $renderer->plugin('form_range');
            return $helper($element);
        }

        if ('reset' == $type) {
            $helper = $renderer->plugin('form_reset');
            return $helper($element);
        }

        if ('search' == $type) {
            $helper = $renderer->plugin('form_search');
            return $helper($element);
        }

        if ('select' == $type) {
            $helper = $renderer->plugin('form_select');
            return $helper($element);
        }

        if ('submit' == $type) {
            $helper = $renderer->plugin('form_submit');
            return $helper($element);
        }

        if ('tel' == $type) {
            $helper = $renderer->plugin('form_tel');
            return $helper($element);
        }

        if ('text' == $type) {
            $helper = $renderer->plugin('form_text');
            return $helper($element);
        }

        if ('textarea' == $type) {
            $helper = $renderer->plugin('form_textarea');
            return $helper($element);
        }

        if ('time' == $type) {
            $helper = $renderer->plugin('form_time');
            return $helper($element);
        }

        if ('url' == $type) {
            $helper = $renderer->plugin('form_url');
            return $helper($element);
        }

        if ('week' == $type) {
            $helper = $renderer->plugin('form_week');
            return $helper($element);
        }

        $helper = $renderer->plugin('form_input');
        return $helper($element);
    }
}
