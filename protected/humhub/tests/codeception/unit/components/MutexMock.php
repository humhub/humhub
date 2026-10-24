<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use yii\mutex\Mutex;

class MutexMock extends Mutex
{
    /**
     * @var bool whether acquiring a lock should succeed
     */
    public bool $available = true;

    public array $acquiredLocks = [];

    public array $releasedLocks = [];

    /**
     * @var callable|null called while the lock is being acquired, to simulate
     * what a concurrent request did before this one got the lock
     */
    public $onAcquire = null;

    protected function acquireLock($name, $timeout = 0)
    {
        $this->acquiredLocks[] = $name;

        if ($this->onAcquire !== null) {
            call_user_func($this->onAcquire, $name);
        }

        return $this->available;
    }

    protected function releaseLock($name)
    {
        $this->releasedLocks[] = $name;

        return true;
    }
}
