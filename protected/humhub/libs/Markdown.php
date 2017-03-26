<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\helpers\Url;
use humhub\libs\Html;
use humhub\modules\file\models\File;

class Markdown extends \cebe\markdown\GithubMarkdown
{

    protected function handleInternalUrls($url)
    {
        // Handle urls to file
        if (substr($url, 0, 10) === "file-guid-") {
            $guid = str_replace('file-guid-', '', $url);
            $file = File::findOne(['guid' => $guid]);
            if ($file !== null) {
                return $file->getUrl();
            }
        }

        return $url;
    }

    protected function renderLink($block)
    {
        if (isset($block['refkey'])) {
            if (($ref = $this->lookupReference($block['refkey'])) !== false) {
                $block = array_merge($block, $ref);
            } else {
                return $block['orig'];
            }
        }

        $block['url'] = $this->handleInternalUrls($block['url']);

        $internalLink = false;
        $baseUrl = Url::base(true);
        if (substr($block['url'], 0, 1) == '/' || substr($block['url'], 0, strlen($baseUrl)) == $baseUrl) {
            $internalLink = true;
        }

        return Html::a($this->renderAbsy($block['text']), Html::decode($block['url']), [
            'target' => ($internalLink) ? '_self' : '_blank'
        ]);
    }

    protected function renderImage($block)
    {
        if (isset($block['refkey'])) {
            if (($ref = $this->lookupReference($block['refkey'])) !== false) {
                $block = array_merge($block, $ref);
            } else {
                return $block['orig'];
            }
        }

        $block['url'] = $this->handleInternalUrls($block['url']);

        return '<img src="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
                . ' alt="' . htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"'
                . (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
                . ($this->html5 ? '>' : ' />');
    }

    protected function renderAutoUrl($block)
    {
        return Html::a($block[1], $block[1]);
    }

    /**
     * Renders a code block
     */
    protected function renderCode($block)
    {
        $class = isset($block['language']) ? ' class="' . $block['language'] . '"' : '';

        return "<pre><code$class>" . $block['content'] . "\n" . "</code></pre>\n";
    }

    /**
     * "Dirty" hacked LinkTrait
     *
     * Try to allow also wiki urls with whitespaces etc.
     */
    protected function parseLinkOrImage($markdown)
    {
        if (strpos($markdown, ']') !== false && preg_match('/\[((?>[^\]\[]+|(?R))*)\]/', $markdown, $textMatches)) { // TODO improve bracket regex
            $text = $textMatches[1];
            $offset = strlen($textMatches[0]);
            $markdown = substr($markdown, $offset);

            $pattern = <<<REGEXP
                /(?(R) # in case of recursion match parentheses
                     \(((?>[^\s()]+)|(?R))*\)
                |      # else match a link with title
                    ^\(\s*(((?>[^\s()]+)|(?R))*)(\s+"(.*?)")?\s*\)
                )/x
REGEXP;
            if (preg_match($pattern, $markdown, $refMatches)) {
                // inline link
                return [
                    $text,
                    isset($refMatches[2]) ? $refMatches[2] : '', // url
                    empty($refMatches[5]) ? null : $refMatches[5], // title
                    $offset + strlen($refMatches[0]), // offset
                    null, // reference key
                ];
            } elseif (preg_match('/\((.*?)\)/', $markdown, $refMatches)) {

                // reference style link
                if (empty($refMatches[1])) {
                    $key = strtolower($text);
                } else {
                    $key = strtolower($refMatches[1]);
                }
                return [
                    $text,
                    $refMatches[1],
                    $text, // title
                    $offset + strlen($refMatches[0]), // offset
                    null,
                ];
            }
        }

        return false;
    }

}
