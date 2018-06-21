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
    /** @var string */
    private $pdoDSN;

    public function __construct($pdoDSN)
    {
        $this->pdoDSN = $pdoDSN;
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
