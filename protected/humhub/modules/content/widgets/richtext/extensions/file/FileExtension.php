<?php

namespace humhub\modules\content\widgets\richtext\extensions\file;

use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtensionMatch;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileUpload;
use Yii;
use humhub\components\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This LinkExtension is used to represent mentionings in the richtext as:
 *
 * [<name>](mention:<guid> "<url>")
 *
 */
class FileExtension extends RichTextLinkExtension
{
    public $key = 'file-guid';

    /**
     * @inheritDoc
     */
    public function onBeforeConvertLink(LinkParserBlock $linkBlock) : void
    {
        $guid = $this->cutExtensionKeyFromUrl($linkBlock->getUrl());

        $file = File::findOne(['guid' => $guid]);

        if(!$file) {
            $linkBlock->setResult($linkBlock->getParsedText());
            return;
        }

        $linkBlock->setBlock($linkBlock->getParsedText(), $file->getUrl(), null, $file->id);
    }

    public static function buildFileLink(File $file) : string
    {

        return static::buildLink($file->file_name, 'file-guid:'.$file->guid, $file->getUrl([], true));
    }

    public static function buildFileNotFound($name, $guid) : string
    {
        return '['.$name.'](mention:'.$guid.' "#")';
    }

    public function onBeforeConvert(string $text, string $format, array $options = []): string
    {
        return $text;
    }

    public function onPostProcess(string $text, ActiveRecord $record, ?string $attribute, array &$result): string
    {
        if($record->isNewRecord) {
            // We can't attach files to unpersisted records
            return $text;
        }

        $result[$this->key] = [];
        foreach ($this->scanExtension($text) as $match) {
            if($match->getExtensionId()) {
                if($this->attach($record, $match->getExtensionId())) {
                    $result[$this->key][] = $match->getExtensionId();
                }
            }
        }

        $text = static::replaceLinkExtension($text, 'data', function(RichTextLinkExtensionMatch $match) use($record, &$result) {
            if($match->getExtensionId()) {
                $file = $this->parseBase64Data($match->getExtensionId(), $record);
                if($file && $file->guid) {
                    $result[$this->key][] = $file->guid;
                    //return '['.$file->file_name.'](file-guid:'.$file->guid.' "'.$file->file_name.'"'.(isset($match[4]) ? $match[4] : '').')';
                    return $this->buildExtensionLink($file->file_name, $file->guid, $file->file_name, $match->getAddition());
                }
            }

            return Yii::t('ContentModule.richtexteditor', '[Invalid file]');
        });

        // Compatibility with HumHub < 1.8
        $result['files'] = $result[$this->key];

        return $text;
    }

    private function attach(ActiveRecord $record, $fileGuid)
    {
        try {
            $file = File::findOne(['guid' => $fileGuid]);

            if(!$file) {
                return false;
            }

            $record->fileManager->attach($file);

            return true;
        } catch (\Exception $e) {
            Yii::error($e);
            return false;
        }
    }

    /**
     * Parses the base64 data string and creates and attaches the file to the record
     * @param $dataStr
     * @return bool|FileUpload
     */
    private function parseBase64Data($dataStr, ActiveRecord $record)
    {
        try {
            preg_match('/^([-\w.]+\/[-\w.+]+);([^,]+),([a-zA-Z0-9\/\r\n+]*={0,2})$/s', $dataStr, $matches);

            if(!isset($matches[1]) || !isset($matches[3])) {
                return false;
            }

            $mime = $matches[1];
            $extensions = FileHelper::getExtensionsByMimeType($mime);

            if(empty($extensions)) {
                return false;
            }

            $extension = end($extensions);
            $data = $this->decode_base64($matches[3]);

            if(!$data) {
                return false;
            }

            $uploadedFile = new UploadedFile([
                'name' => 'someFile.'.$extension,
                'tempName' => $this->createTmpFile($data),
                'size' => strlen($data),
                'type' => $mime,
            ]);

            $fileUpload = new FileUpload(['show_in_stream' => 0]);
            $fileUpload->setUploadedFile($uploadedFile);

            if(!$fileUpload->save()) {
                return false;
            }

            $fileUpload->updateAttributes(['file_name' => $fileUpload->guid.'.'.$extension]);

            // Since the file is not a real upload, FileUpload won't set the content automatically
            $fileUpload->setStoredFileContent($data);
            $this->attach($record, $fileUpload);
            return $fileUpload;
        } catch (\Throwable $e) {
            Yii::error($e);
        }

        return false;
    }

    /**
     * Decodes and validates base64 string
     *
     * @param $s
     * @return bool|string
     */
    private function decode_base64($s)
    {
        // Decode the string in strict mode and check the results
        $decoded = base64_decode($s, true);

        if($decoded === false) {
            return false;
        }

        // Encode the string again
        if (base64_encode($decoded) != $s) {
            return false;
        }

        return $decoded;
    }

    /**
     * Creates a temp file with the given data
     *
     * @param $data
     * @return bool|string
     */
    private function createTmpFile($data)
    {
        $temp = tempnam(sys_get_temp_dir(), 'tmp');
        $hanlde = fopen($temp, 'wb');
        fwrite($hanlde, $data);
        fclose($hanlde);
        return $temp;
    }
}
