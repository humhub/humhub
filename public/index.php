<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\services\BootstrapService;

$rootPath = dirname(__DIR__);
$protectedPath = $rootPath . '/protected';

/**
 * @var $loader \Composer\Autoload\ClassLoader
 */
$loader = require($protectedPath . '/vendor/autoload.php');

// Load Environment
$dotenv = Dotenv\Dotenv::createMutable($rootPath, '.env');
$dotenv->safeLoad();

// Load Bootstrap Helper
$loader->addClassMap(['humhub\\services\\BootstrapService' => $protectedPath . '/humhub/services/BootstrapService.php']);

$bootstrap = new BootstrapService();
$bootstrap->runWeb();
