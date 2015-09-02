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
 * Experimental downloader of files (images)
 *
 * @package humhub.modules_core.file.libs
 * @since 0.5
 */
class RemoteFileDownloader
{

    /**
     * Attaches files by url which found in content text.
     * This is experimental and only supports image files at the moment.
     *
     * @param HActiveRecord $record to bind files to
     * @param String $text to parse for links 
     */
    public static function attachFiles($record, $text)
    {

        if (!$record instanceof HActiveRecord) {
            throw new CException("Invalid content object given!");
        }

        $max = 5;
        $count = 1;

        $text = preg_replace_callback('/http(.*?)(\s|$)/i', function($match) use (&$count, &$max, &$record) {

            if ($max > $count) {

                $url = $match[0];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
                curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

                if (HSetting::Get('enabled', 'proxy')) {
                    curl_setopt($ch, CURLOPT_PROXY, HSetting::Get('server', 'proxy'));
                    curl_setopt($ch, CURLOPT_PROXYPORT, HSetting::Get('port', 'proxy'));
                    if (defined('CURLOPT_PROXYUSERNAME')) {
                        curl_setopt($ch, CURLOPT_PROXYUSERNAME, HSetting::Get('user', 'proxy'));
                    }
                    if (defined('CURLOPT_PROXYPASSWORD')) {
                        curl_setopt($ch, CURLOPT_PROXYPASSWORD, HSetting::Get('pass', 'proxy'));
                    }
                    if (defined('CURLOPT_NOPROXY')) {
                        curl_setopt($ch, CURLOPT_NOPROXY, HSetting::Get('noproxy', 'proxy'));
                    }
                }

                $ret = curl_exec($ch);
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                list($headers, $outputContent) = explode("\r\n\r\n", $ret, 2);
                curl_close($ch);

                if ($httpCode == 200 && substr($contentType, 0, 6) == 'image/') {

                    $extension = 'img';
                    if ($contentType == 'image/jpeg' || $contentType == 'image/jpg')
                        $extension = 'jpg';
                    elseif ($contentType == 'image/gif')
                        $extension = 'gif';
                    elseif ($contentType == 'image/png')
                        $extension = 'png';

                    $file = new File();
                    $file->object_model = get_class($record);
                    $file->object_id = $record->getPrimaryKey();
                    $file->mime_type = $contentType;
                    $file->title = "Link Image";
                    $file->file_name = "LinkImage." . $extension;
                    $file->newFileContent = $outputContent;
                    $file->validate();
                    $file->save();
                }
            }
            $count++;
        }, $text);
    }

}
