<?php

require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/inline/CodeTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/inline/EmphStrongTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/inline/LinkTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/inline/StrikeoutTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/inline/UrlLinkTrait.php');

require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/CodeTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/FencedCodeTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/HeadlineTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/HtmlTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/ListTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/QuoteTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/RuleTrait.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/block/TableTrait.php');

require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/Parser.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/Markdown.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/MarkdownExtra.php');
require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/GithubMarkdown.php');

class HMarkdown extends cebe\markdown\GithubMarkdown
{

    protected function handleInternalUrls($url)
    {

        // Handle urls to file 
        if (substr($url, 0, 10) === "file-guid-") {
            $guid = str_replace('file-guid-', '', $url);
            $file = File::model()->findByAttributes(array('guid' => $guid));
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
        return '<a href="' . $block['url'] . '"'
                . (empty($block['title']) ? '' : ' title="' . $block['title'] . '"')
                . '>' . $this->renderAbsy($block['text']) . '</a>';
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
        return CHtml::link($block[1], $block[1]);
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
