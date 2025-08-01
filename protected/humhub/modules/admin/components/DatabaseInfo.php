<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

/**
 * @since 1.3
 */
class DatabaseInfo
{
    /**
     * @param string $pdoDSN
     */
    public function __construct(private $pdoDSN)
    {
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        $databaseName = '';
        if (preg_match('/dbname=([^;]*)/', $this->pdoDSN, $match)) {
            $databaseName = $match[1];
        }

        return $databaseName;
    }
}
