<?php

namespace humhub\components\cache;

use yii\caching\DummyCache;

/**
 * This class represents the default cache configuration used during the initial setup
 * and bootstrap phase of the HumHub application. It behaves the same as DummyCache,
 * meaning it performs no actual caching operations, and serves as the default fallback.
 *
 * It allows the system to distinguish between an explicitly configured cache
 * and the placeholder cache used during early application initialization.
 */
class InitialCache extends DummyCache {}
