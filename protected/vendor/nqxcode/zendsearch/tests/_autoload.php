<?php
/**
 * Setup autoloading
 */
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new RuntimeException('This component has dependencies that are unmet.

Either build a vendor/autoloader.php that will load this components dependencies ...

OR

Install composer (http://getcomposer.org), and run the following
command in the root of this project:

    php /path/to/composer.phar install

After that, you should be able to run tests.');
} else {
    include_once __DIR__ . '/../vendor/autoload.php';
}



spl_autoload_register(function ($class) {
    if (0 !== strpos($class, 'ZendSearchTest\\')) {
        return false;
    }
    $normalized = str_replace('ZendSearchTest\\', '', $class);
    $filename   = __DIR__ . '/ZendSearch/' . str_replace(array('\\', '_'), '/', $normalized) . '.php';
    if (!file_exists($filename)) {
        return false;
    }
    return include_once $filename;
});