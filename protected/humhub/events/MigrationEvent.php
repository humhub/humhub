<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\events;

use humhub\components\Event;
use humhub\interfaces\ApplicationInterface;
use yii\base\Module;

/**
 * @property int|null $result Result of the migration:
 * ``
 *  - `ExitCode::OK`: Success;
 *  - `ExitCode::UNSPECIFIED_ERROR`: failure;
 *  - `Null`: nothing done
 * ``
 */
class MigrationEvent extends Event
{
    /**
     * @var \humhub\components\Module|ApplicationInterface|null
     */
    public ?Module $module;

    /**
     * @var string Either `up` or `uninstall`
     */
    public string $migration;

    /**
     * @var string|null Output of the MigrationController's Action
     */
    public ?string $output = null;
}
