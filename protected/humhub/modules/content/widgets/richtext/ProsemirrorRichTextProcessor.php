<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;


use humhub\models\UrlOembed;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileUpload;
use humhub\modules\user\models\Mentioning;
use Yii;
use yii\web\UploadedFile;

/**
 * Class ProsemirrorRichTextProcessor provides pre-processor logic for oembed and mentionings for the ProsemirrorRichText.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @since 1.3
 */
class ProsemirrorRichTextProcessor extends AbstractRichTextProcessor
{

    /**
     * Parses oembed link extensions in the form of [<url>](oembed:<url>) and preloads the given oembed dom.
     */
    public function parseOembed()
    {
        $matches = ProsemirrorRichText::scanLinkExtension($this->text, 'oembed');
        foreach ($matches as $match) {
            if(isset($match[3])) {
                UrlOembed::preload($match[3]);
            }
        }
    }

    /**
     * Parses mention link extensions in the form of [<url>](mention:<guid> "<link>") and creates mentionings records.
     */
    public function parseMentioning()
    {
        $result = [];
        $matches = ProsemirrorRichText::scanLinkExtension($this->text, 'mention');
        foreach ($matches as $match) {
            if(isset($match[3])) {
                $result = array_merge($result, Mentioning::mention($match[3], $this->record));
            }
        }

        return $result;
    }

    public function parseFiles()
    {
        $result = [];
        $matches = ProsemirrorRichText::scanLinkExtension($this->text, 'file-guid');
        foreach ($matches as $match) {
            if(isset($match[3])) {
                try {
                    $result[] = $match[3];
                    if($this->record) {
                        $this->record->fileManager->attach($match[3]);
                    }
                } catch (\Exception $e) {
                    Yii::error($e);
                }
            }
        }

        $this->text = ProsemirrorRichText::replaceLinkExtension($this->text, 'data', function($match) {
            if($this->record && isset($match[3])) {
                $file = $this->parseBase64Data($match[3]);
                if($file && $file->guid) {
                   return '['.$file->file_name.'](file-guid:'.$file->guid.' "'.$file->file_name.'"'.(isset($match[4]) ? $match[4] : '').')';
                }
            }

            return Yii::t('ContentModule.richtexteditor', '[Invalid file]');
        });

        return $result;
    }

    /**
     * Parses the base64 data string and creates and attaches the file to the record
     * @param $dataStr
     * @return bool|FileUpload
     */
    private function parseBase64Data($dataStr)
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
            $fileUpload->store->setContent($data);
            $this->record->fileManager->attach($fileUpload);
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
