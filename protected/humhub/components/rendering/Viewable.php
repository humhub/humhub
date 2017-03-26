<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rendering;

/**
 * The Viewable interface is used for Classes that can be rendered by Renderer components.
 * A Renderer can make use of the html, json or text represenation when rendering a viewable.
 *
 * @author buddha
 */
interface Viewable
{

    /**
     * Returns an array of view parameter, required for rendering.
     *
     * @param array $params
     */
    public function getViewParams($params = []);


    /**
     * @return string viewname of this viewable
     */
    public function getViewName();

    /**
     * @return string html content representation of this viewable.
     */
    public function html();

    /**
     * @return string json content representation of this viewable.
     */
    public function json();

    /**
     * @return string text content representation of this viewable.
     */
    public function text();

}
