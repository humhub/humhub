<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use yii\base\Component;

/**
 * JsMessageExtractor extracts translation messages from JavaScript/ReactJs files.
 *
 * Parses i18n.t() calls in js/jsx files.
 * Exaple: i18n.t('Module.Category', 'Hello {param}', {param: 'World'})
 *
 * @since 1.18
 */
class JsMessageExtractor extends Component
{
    /**
     * EExtracts messages from a JavaScript file.
     *
     * @param string $fileName name of the file to extract messages from
     * @param string $translatorFunctions name of the function used to translate messages
     * @param array $ignoreCategories message categories to ignore.
     * This parameter is available since version 2.0.4.
     * @return array
     */
    public function extract($fileName, $translatorFunctions, $ignoreCategories = [])
    {
        $content = file_get_contents($fileName);
        $messages = [];

        if (!is_array($translatorFunctions)) {
            $translatorFunctions = [$translatorFunctions];
        }

        foreach ($translatorFunctions as $translator) {
            $messages = array_merge_recursive($messages, $this->extractMessagesFromContent($content, $translator, $ignoreCategories));
        }

        return $messages;
    }

    /**
     * Extracts messages from JavaScript content.
     *
     * @param string $content JavaScript file content
     * @param string $translator translator function name
     * @param array $ignoreCategories categories to ignore
     * @return array extracted messages indexed by category
     */
    protected function extractMessagesFromContent($content, $translator, $ignoreCategories)
    {
        $messages = [];
        $translator = preg_quote($translator, '/');

        $pattern = '/' . $translator . '\s*\(\s*([\'"])((?:\\\\.|(?!\1).)*?)\1\s*,\s*([\'"])((?:\\\\.|(?!\3).)*?)\3/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $category = $match[2];
                $message = $match[4];

                if ($this->isCategoryIgnored($category, $ignoreCategories)) {
                    continue;
                }

                $message = $this->unescapeJsString($message);

                if (!isset($messages[$category])) {
                    $messages[$category] = [];
                }
                $messages[$category][] = $message;
            }
        }

        return $messages;
    }

    /**
     * Checks if a category should be ignored.
     *
     * @param string $category the message category
     * @param array $ignoreCategories categories to ignore
     * @return bool whether the category is ignored
     */
    protected function isCategoryIgnored($category, $ignoreCategories)
    {
        if (empty($ignoreCategories)) {
            return false;
        }

        foreach ($ignoreCategories as $pattern) {
            if ($category === $pattern || (str_ends_with($pattern, '*') && str_starts_with($category, rtrim($pattern, '*')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Unescapes JavaScript string literals.
     *
     * @param string $str JavaScript string
     * @return string unescaped string
     */
    protected function unescapeJsString($str)
    {
        $replacements = [
            '\\\'' => "'",
            '\\"' => '"',
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\\\' => '\\',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $str);
    }
}
