<?php

require_once(dirname(__FILE__) . '/../vendors/cebe/markdown/Parser.php');

/**
 * HMarkdownPreview generates a plain text (no HTML) of given markdown
 * Some elements like images or links will be displayed more clearly
 * 
 * @since 0.11.1
 */
class HMarkdownPreview extends cebe\markdown\Parser
{

    /**
     * Returns a preview of given Text
     * 
     * @param type $foo
     * @return type
     */
    public static function Render($markdown) {
        
        $markdown = CHtml::encode($markdown);
        
        $parser = new self;
        $text = $parser->parse($markdown);
        
        return $text;
    }
    
    
    protected function renderParagraph($block)
    {
        return $this->renderAbsy($block['content']) . "\n";
    }

    /**
     * Parses a link indicated by `[`.
     * @marker [
     */
    protected function parseLink($markdown)
    {
        if (!in_array('parseLink', array_slice($this->context, 1)) && ($parts = $this->parseLinkOrImage($markdown)) !== false) {
            list($text, $url, $title, $offset, $key) = $parts;
            return [
                [
                    'link',
                    'text' => $this->parseInline($text),
                    'url' => $url,
                    'title' => $title,
                    'refkey' => $key,
                    'orig' => substr($markdown, 0, $offset),
                ],
                $offset
            ];
        } else {
            // remove all starting [ markers to avoid next one to be parsed as link
            $result = '[';
            $i = 1;
            while (isset($markdown[$i]) && $markdown[$i] == '[') {
                $result .= '[';
                $i++;
            }
            return [['text', $result], $i];
        }
    }

    /**
     * 
     * @param type $block
     * @marker ![
     */
    protected function parseImage($markdown)
    {
        if (($parts = $this->parseLinkOrImage(substr($markdown, 1))) !== false) {
            list($text, $url, $title, $offset, $key) = $parts;

            return [
                [
                    'image',
                    'text' => $text,
                    'url' => $url,
                    'title' => $title,
                    'refkey' => $key,
                    'orig' => substr($markdown, 0, $offset + 1),
                ],
                $offset + 1
            ];
        } else {
            // remove all starting [ markers to avoid next one to be parsed as link
            $result = '!';
            $i = 1;
            while (isset($markdown[$i]) && $markdown[$i] == '[') {
                $result .= '[';
                $i++;
            }
            return [['text', $result], $i];
        }
    }

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
            } elseif (preg_match('/^([ \n]?\[(.*?)\])?/s', $markdown, $refMatches)) {
                // reference style link
                if (empty($refMatches[2])) {
                    $key = strtolower($text);
                } else {
                    $key = strtolower($refMatches[2]);
                }
                return [
                    $text,
                    null, // url
                    null, // title
                    $offset + strlen($refMatches[0]), // offset
                    $key,
                ];
            }
        }
        return false;
    }

    protected function renderLink($block)
    {

        return "[" . $block['url'] . "]";
    }

    protected function renderImage($block)
    {

        return "[" . $block['text'] . "]";
    }

}
