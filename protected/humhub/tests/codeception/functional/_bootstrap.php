<?php

/**
 * Included by `FixtureHelper`/`DynamicFixtureHelper` before a suite loads its fixtures (see
 * their `_beforeSuite()`), which needs an application: a fixture resolves `db` through
 * `Yii::$app`.
 *
 * Usually one is already there — the run's first suite inherits the application the
 * Codeception bootstrap created, and every suite with a Yii2 module creates one per test.
 * It is *not* there when a preceding suite's Yii2 module reset the application as it
 * finished, which is what the `api` suite does: without this, the next suite dies at its
 * very first fixture with "Failed to instantiate component or class db" and the whole run
 * aborts (`codecept run` runs the suites in one process).
 */
if (Yii::$app === null) {
    $config = require dirname(__DIR__) . '/config/functional.php';
    $applicationClass = $config['class'] ?? humhub\components\Application::class;
    unset($config['class']);

    new $applicationClass($config);
}
