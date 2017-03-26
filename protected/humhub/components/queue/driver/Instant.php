<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\queue\driver;

use zhuravljov\yii\queue\sync\Driver;

/**
 * Instant queue driver, mainly used for testing purposes
 *
 * @since 1.2
 * @author buddha
 */
class Instant extends Driver
{

    /**
     * @inheritdoc
     */
    public $handle = true;

    /**
     * @var array
     */
    private $_messages = [];

    /**
     * Executes the jobs immediatly, serialization is done for testing purpose
     */
    public function push($job)
    {
        $this->_messages[] = $this->serialize($job);

        while (($message = array_shift($this->_messages)) !== null) {
            $job = $this->unserialize($message);
            $this->getQueue()->run($job);
        }
    }

}
