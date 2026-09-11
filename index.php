<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * DEPRECATED entry script.
 *
 * HumHub serves from `public/`. Point the web server's document root at that directory and let it
 * run `public/index.php`; everything beside it - `protected/`, `uploads/`, the Composer metadata,
 * the `.env` file - then stops being reachable over the web.
 *
 * This script exists so installations keep working until their document root has been moved, and
 * will be removed in a future version. It marks itself rather than letting HumHub infer the
 * situation from paths, because managed hosting runs its own entry scripts and path layouts where
 * such a guess would be wrong. Administration -> Information -> Prerequisites reports it.
 */
$_ENV['HUMHUB_LEGACY_ENTRY_SCRIPT'] = __DIR__;

require __DIR__ . '/public/index.php';
