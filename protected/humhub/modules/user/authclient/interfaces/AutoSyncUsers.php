<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

/**
 * @deprecated since 1.16 — register a dedicated sync job in your module instead.
 *   The interface is kept as an empty marker so modules still implementing it
 *   don't fatal-error; core no longer reads it.
 */
interface AutoSyncUsers
{
}
