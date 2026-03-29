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
        if (!$file->store->has()) {
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
        if ($this->file->store->has($fileName)) {
            return;
        }

        // We need to create a Temp File with the File content, since file could also
        // located on a external object store.
        $tempFile = FileHelper::getTempFile();
        file_put_contents($tempFile, $this->file->store->getContentStream());

        $converter = $this->getConverter();

        if ($converter !== null) {
            if (Yii::$app->request->isConsoleRequest) {
                print "C";
            }

            $command = str_replace('{fileName}', $tempFile, $converter['cmd']);

            if (str_contains($command, "{outputFileName}")) {
                $tempOutFile = FileHelper::getTempFile();

                $command = str_replace('{outputFileName}', $tempOutFile, $command);
                shell_exec($command);

                $this->file->store->setContent(file_get_contents($tempOutFile), $fileName);
                unlink($tempOutFile);
            } else {
                $textContent = shell_exec($command) . "\n";
                $this->file->store->setContent($textContent, $fileName);
            }
        }

        unlink($tempFile);
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
                    if (str_contains($this->file->mime_type, (string)$except) || FileHelper::getExtension(
                        $this->file,
                    ) == $except) {
                        continue 2;
                    }
                }
            }

            if (!empty($converter['only']) && is_array($converter['only'])) {
                foreach ($converter['only'] as $only) {
                    if (str_contains($this->file->mime_type, (string)$only) || FileHelper::getExtension(
                        $this->file,
                    ) == $only) {
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
        if ($this->file->store->has($fileName)) {
            return stream_get_contents($this->file->store->getContentStream($fileName), $this->maxTextFileSize, 0);
        }

        return null;
    }

}
