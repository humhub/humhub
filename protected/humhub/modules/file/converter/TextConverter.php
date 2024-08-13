<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use Yii;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;

/**
 * Text Converter
 *
 * @since 1.3
 * @author Luke
 */
class TextConverter extends BaseConverter
{

    /**
     * List of installed text conversion converters
     *
     * Example:
     *
     * ```
     * [
     *      'cmd' => '/usr/bin/pdftotext -q -enc UTF-8 {fileName} {outputFileName}',
     *      'only' => ['pdf']
     * ],
     * [
     *      'cmd' => '/usr/bin/java -jar /path/to/tika-app-1.16.jar --text {fileName} 2>/dev/null',
     *      'except' => ['image/']
     * ]
     * ```
     *
     * @var array
     */
    public $converter = [];

    /**
     * @var int maximum text file size in byte
     */
    public $maxTextFileSize = 3.2e+7;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return 'text';
    }

    /**
     * @inheritdoc
     */
    protected function canConvert(File $file)
    {
        $originalFile = $file->store->get();

        if (!is_file($originalFile)) {
            return false;
        }

        if ($this->getConverter() === null) {
            // No text converter found for given file
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function convert($fileName)
    {
        $convertedFile = $this->file->store->get($fileName);

        if (is_file($convertedFile)) {
            return;
        }

        $textContent = '';
        $converter = $this->getConverter();

        if ($converter !== null) {
            if (Yii::$app->request->isConsoleRequest) {
                print "C";
            }

            $command = str_replace('{fileName}', $this->file->store->get(), $converter['cmd']);
            if (strpos($command, "{outputFileName}") !== false) {
                $command = str_replace('{outputFileName}', $convertedFile, $command);
                shell_exec($command);
            } else {
                $textContent = shell_exec($command) . "\n";
                file_put_contents($convertedFile, $textContent);
            }
        }
    }

    /**
     * Returns the first matching converter for the file
     *
     * @return array the converter definitions
     */
    public function getConverter()
    {
        foreach ($this->converter as $converter) {
            // Check Exceptions
            if (!empty($converter['except']) && is_array($converter['except'])) {
                foreach ($converter['except'] as $except) {
                    if (strpos($this->file->mime_type, $except) !== false || FileHelper::getExtension($this->file) == $except) {
                        continue 2;
                    }
                }
            }

            if (!empty($converter['only']) && is_array($converter['only'])) {
                foreach ($converter['only'] as $only) {
                    if (strpos($this->file->mime_type, $only) !== false || FileHelper::getExtension($this->file) == $only) {
                        return $converter;
                    }
                }
            } else {
                // Valid for all file types
                return $converter;
            }
        }

        return null;
    }

    /**
     * Returns the file content as text
     *
     * @return string the text output
     */
    public function getContentAsText()
    {
        $fileName = $this->getFilename();

        $convertedFile = $this->file->store->get($fileName);

        if (is_file($convertedFile)) {

            // Reduce file size to max text length
            if (filesize($convertedFile) > $this->maxTextFileSize) {
                $fp = fopen($convertedFile, "r+");
                ftruncate($fp, $this->maxTextFileSize);
                fclose($fp);
            }

            return file_get_contents($convertedFile);
        }

        return null;
    }

}
