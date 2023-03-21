<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\access;

/**
 * Class ManifestAccess is responsible to allow Public Access to the Manifest JSON and ServiceWorker JS.
 *
 * @since 1.14
 *
 * @see ControllerAccess
 * @see ManifestController
 * @see ServiceWorkerController
 * @author cs8898
 */
class ManifestAccess extends ControllerAccess
{
    /**
     * @inheritdoc
     */
    protected $fixedRules = [
        [self::RULE_DISABLED_USER],
        [self::RULE_UNAPPROVED_USER],
        [self::RULE_MAINTENANCE_MODE]
    ];
}