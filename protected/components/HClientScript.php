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
 * HClientScript extends the CClientScript
 *
 * @package humhub.components
 * @since 0.5
 */
class HClientScript extends CClientScript
{

    /**
     * @var array the registered HTML code blocks (key => code)
     */
    public $htmls = array();

    /**
     * Combine Js Files
     * 
     * @var boolean
     */
    public $enableCombineJs = true;

    /**
     * Combine Js Files in Debug Mode
     * 
     * @since 0.12.0
     * @var boolean
     */
    public $enableCombineJsInDebug = false;

    /**
     * Should javascript files be minified
     * Requires enabled enableCombineJs option.
     * 
     * Only affects Javascript's without .min.js extension.
     * 
     * @since 0.12.0
     * @var boolean
     */
    public $enableMinifyJs = false;

    /**
     * Combine Css Files
     * 
     * @since 0.12.0
     * @var boolean
     */
    public $enableCombineCss = true;

    /**
     * Combine Css Files in Debug Mode
     * 
     * @since 0.12.0
     * @var boolean
     */
    public $enableCombineCssInDebug = false;

    /**
     * Should CSS files be minified
     * Requires enabled enableCombineCss option.
     * 
     * @since 0.12.0
     * @var boolean
     */
    public $enableMinifyCss = false;

    /**
     * Method to inject a javascript variable
     *
     * @param String $name
     * @param String $value
     */
    public function setJavascriptVariable($name, $value)
    {

        $jsCode = "var " . $name . " = '" . $value . "';\n";
        $this->registerScript('jsVar_' . $name, $jsCode, CClientScript::POS_BEGIN);
    }

    /**
     * Registers a piece of html code.
     * @param string $id ID that uniquely identifies this piece of HTML code
     * @param string $html the html code
     *
     * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
     */
    public function registerHtml($id, $html)
    {

        $this->hasScripts = true;

        $this->htmls[$id] = $html;

        $params = func_get_args();
        $this->recordCachingAction('clientScript', 'registerScript', $params);
        return $this;
    }

    /**
     * Inserts the scripts and other html snippets at the end of the body section.
     *
     * @param string $output the output to be inserted with scripts.
     */
    public function renderBodyEnd(&$output)
    {
        if (!isset($this->scriptFiles[self::POS_END]) && !isset($this->scripts[self::POS_END]) && !isset($this->scripts[self::POS_READY]) && !isset($this->scripts[self::POS_LOAD]) && count($this->htmls) == 0)
            return;

        $this->combineJs(self::POS_END);

        $fullPage = 0;
        $output = preg_replace('/(<\\/body\s*>)/is', '<###end###>$1', $output, 1, $fullPage);
        $html = '';
        if (isset($this->scriptFiles[self::POS_END])) {
            foreach ($this->scriptFiles[self::POS_END] as $scriptFile)
                $html.=CHtml::scriptFile($scriptFile) . "\n";
        }


        //----------------------------------------------------------------------------------------------------
        // Begin Custom
        foreach ($this->htmls as $id => $htmlSnippet) {
            $html .= $htmlSnippet;
        }
        //----------------------------------------------------------------------------------------------------

        $scripts = isset($this->scripts[self::POS_END]) ? $this->scripts[self::POS_END] : array();
        if (isset($this->scripts[self::POS_READY])) {
            if ($fullPage)
                $scripts[] = "jQuery(function($) {\n" . implode("\n", $this->scripts[self::POS_READY]) . "\n});";
            else
                $scripts[] = implode("\n", $this->scripts[self::POS_READY]);
        }
        if (isset($this->scripts[self::POS_LOAD])) {
            if ($fullPage)
                $scripts[] = "jQuery(window).on('load',function() {\n" . implode("\n", $this->scripts[self::POS_LOAD]) . "\n});";
            else
                $scripts[] = implode("\n", $this->scripts[self::POS_LOAD]);
        }
        if (!empty($scripts))
            $html.=CHtml::script(implode("\n", $scripts)) . "\n";

        if ($fullPage)
            $output = str_replace('<###end###>', $html, $output);
        else
            $output = $output . $html;
    }

    public function renderHead(&$output)
    {
        $this->combineCss();
        $this->combineJs(self::POS_HEAD);

        parent::renderHead($output);
    }

    public function renderBodyBegin(&$output)
    {
        $this->combineJs(self::POS_BEGIN);
        parent::renderBodyBegin($output);
    }

    /**
     * Combines and optionally compresses used CSS files on a given position.
     * If running on debug mode - the files remain changed.
     * 
     * @since 0.12.0
     */
    protected function combineCss()
    {
        if (!$this->enableCombineCss || (YII_DEBUG && !$this->enableCombineCssInDebug)) {
            return;
        }

        // Cache Id
        $cacheId = $this->getCombineHash(implode("-", array_keys($this->cssFiles)));
        $cacheFileName = $cacheId . ".css";

        // Combine when not exist yet
        if (!file_exists($this->getCombinePath() . DIRECTORY_SEPARATOR . $cacheFileName)) {
            $combined = "";
            foreach ($this->cssFiles as $src => $media) {
                $scriptFile = str_replace(Yii::app()->getBaseUrl(), Yii::getPathOfAlias('webroot'), $src);
                // If we're on root
                if (Yii::app()->getBaseUrl() == "") {
                    $scriptFile = Yii::getPathOfAlias('webroot') . $scriptFile;
                }
                if (is_file($scriptFile) && is_readable($scriptFile)) {
                    $css = file_get_contents($scriptFile);
                    $css = preg_replace_callback("#url\s*\(\s*['\"]?([^'\"\)]+)['\"]?\)#", function ($match) use ($src) {
                        return "url('" . dirname($src) . '/' . $match[1] . "')";
                    }, $css);

                    $combined .= $css;
                } else {
                    Yii::log("Could not open CSS file to combine: " . $scriptFile, CLogger::LEVEL_ERROR);
                }
            }

            if ($this->enableCombineCss) {
                Yii::import('ext.wbkrnl.CssCompressor', true);
                $combined = CssCompressor::deflate($combined);
            }

            file_put_contents($this->getCombinePath() . DIRECTORY_SEPARATOR . $cacheFileName, $combined);
        }

        // Clean old uncombined script
        $this->cssFiles = array();

        // Add combined script
        $this->registerCssFile($this->getCombineUrl() . DIRECTORY_SEPARATOR . $cacheFileName);
    }

    /**
     * Combines and optionally compresses used Javascript files on a given position.
     * If running on debug mode - the files remain changed.
     * 
     * @param integer $position the position of the JavaScript code.See CClientScript::POS_*
     * 
     * @since 0.12.0
     */
    protected function combineJs($pos)
    {
        if (!$this->enableCombineJs || (YII_DEBUG && !$this->enableCombineJsInDebug)) {
            return;
        }

        if (isset($this->scriptFiles[$pos])) {
            // Cache Id
            $cacheId = $this->getCombineHash(implode("-", $this->scriptFiles[$pos]));
            $cacheFileName = $cacheId . ".js";

            // Combine when not exist yet
            if (!file_exists($this->getCombinePath() . DIRECTORY_SEPARATOR . $cacheFileName)) {
                $combined = "";
                foreach ($this->scriptFiles[$pos] as $src => $scriptFile) {
                    $scriptFile = str_replace(Yii::app()->getBaseUrl(), Yii::getPathOfAlias('webroot'), $scriptFile);
                    // If we're on root
                    if (Yii::app()->getBaseUrl() == "") {
                        $scriptFile = Yii::getPathOfAlias('webroot') . $scriptFile;
                    }
                    if (is_file($scriptFile) && is_readable($scriptFile)) {

                        if ($this->enableMinifyJs && (strpos($scriptFile, '.min.js') === false)) {
                            Yii::import('ext.JShrink.Minifier', true);
                            $combined .= \JShrink\Minifier::minify(file_get_contents($scriptFile));
                        } else {
                            $combined .= file_get_contents($scriptFile);
                        }
                    } else {
                        Yii::log("Could not open CSS file to combine: " . $scriptFile, CLogger::LEVEL_ERROR);
                    }
                }
                file_put_contents($this->getCombinePath() . DIRECTORY_SEPARATOR . $cacheFileName, $combined);
            }

            // Clean old uncombined script
            $this->scriptFiles[$pos] = array();

            // Add combined script
            $this->registerScriptFile($this->getCombineUrl() . DIRECTORY_SEPARATOR . $cacheFileName, $pos);
        }
    }

    /**
     * Generates short hash for combined JS/CSS Files
     * Includes version number.
     * 
     * @since 0.12.0
     * @param string $string
     * @return strng
     */
    protected function getCombineHash($string)
    {
        return sprintf('%x', crc32($string . HVersion::VERSION));
    }

    /**
     * Path to store combined CSS/JS Files
     * 
     * @since 0.12.0
     * @return string
     */
    protected function getCombinePath()
    {
        $p = Yii::app()->assetManager->basePath . DIRECTORY_SEPARATOR . 'comp';
        if (!is_dir($p)) {
            mkdir($p);
        }
        return $p;
    }

    /**
     * URL of combined CSS/JS Files
     * 
     * @since 0.12.0
     * @return string
     */
    protected function getCombineUrl()
    {
        return Yii::app()->assetManager->baseUrl . DIRECTORY_SEPARATOR . 'comp';
    }

}

?>
