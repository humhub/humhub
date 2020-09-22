<?php
/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 29.04.2019
 * Time: 16:04
 */

namespace humhub\libs;


interface UrlOembedClient
{
    /**
     * Fetches a given $url and returns the oembed result array.
     *
     * The resulting array should at least contain an `html` with a html preview and a `type` field.
     *
     * @param string $url
     * @return array|null
     */
    public function fetchUrl($url);
}