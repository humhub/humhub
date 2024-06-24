<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Throwable;
use Yii;

/**
 * @since 1.15
 */
class DatabaseHelper
{
    public static function handleConnectionErrors(
        Throwable $ex,
        bool $print = true,
        bool $die = true,
        bool $forcePlainText = false,
    ): ?string {
        static $last = false;

        if (!$ex instanceof \yii\db\Exception) {
            return null;
        }

        if ($last) {
            return null;
        }

        $last = true;

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = end($trace);
        if ($trace && $trace['function'] === 'handleException' && $trace['args'][0] instanceof \yii\db\Exception) {
            return null;
        }

        switch ($ex->getCode()) {
            case 2002:
                $error = 'Hostname not found.';
                break;

            case 1044:
                $error = 'Database not found or not accessible.';
                break;

            case 1049:
                $error = 'Database not found.';
                break;

            default:
                $error = $ex->getMessage();
        }

        /**
         * @see https://www.php.net/manual/en/ref.pdo-odbc.connection.php
         * @see https://www.php.net/manual/en/ref.pdo-ibm.connection.php
         * @see https://www.php.net/manual/en/ref.pdo-pgsql.connection.php
         */
        $dsn = preg_replace(
            '@((?<=:|;)(?:user|uid|User ID|pwd|password)=)(.*?)(?=;(?:$|\w+=)|$)@i',
            '$1****',
            Yii::$app->db->dsn,
        );

        try {
            $additionalInfo = [get_class($ex)];
            if (isset($ex->errorInfo)) {
                if (is_array($ex->errorInfo)) {
                    $additionalInfo = array_merge($additionalInfo, $ex->errorInfo);
                } elseif (is_scalar($ex->errorInfo)) {
                    $additionalInfo[] = $ex->errorInfo;
                }
            }
            $additionalInfo = json_encode($additionalInfo, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $additionalInfo = 'N/A';
        }

        while ($ex->getPrevious()) {
            $ex = $ex->getPrevious();
        }

        $htmlMessage = defined('YII_DEBUG') && YII_DEBUG
            ? sprintf('
<h1>Invalid database configuration</h1>
<p><strong>%s</strong></p>
<p>The following connection string was used:<br><code>%s</code></p>
<br>
<h2>Technical information</h2>
<p><code>%s</code></p>
<p><pre>%s</pre></p>
', $error, $dsn, $additionalInfo, $ex)
            : sprintf('
<h1>Invalid database configuration</h1>
<p><strong>%s</strong></p>
', $error);

        $txtMessage = defined('YII_DEBUG') && YII_DEBUG
            ? sprintf('
Invalid database configuration
==============================

%s

The following connection string was used:
%s


Technical information
---------------------
%s

%s

', $error, $dsn, $additionalInfo, $ex)
            : sprintf('
Invalid database configuration
==============================

%s

The following connection string was used:
%s


Technical information
---------------------
%s

', $error, $dsn, $additionalInfo);

        if ($print) {
            if ($forcePlainText) {
                echo $txtMessage;
            } elseif (Yii::$app instanceof \yii\console\Application && Yii::$app->controller instanceof \yii\console\Controller) {
                Yii::$app->controller->stderr($txtMessage);
            } else {
                header("HTTP/1.1 500 Internal Server Error");
                echo $htmlMessage;
            }
        }

        if (!$die) {
            return $txtMessage;
        }

        die(1);
    }
}
