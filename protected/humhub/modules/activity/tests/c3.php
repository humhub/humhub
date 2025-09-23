<?php

// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart

/**
 * C3 - Codeception Code Coverage
 *
 * @author tiger
 */

use Codeception\Configuration;
use Codeception\Coverage\Filter;
use Codeception\Util\FileSystem;

if (isset($_COOKIE['CODECEPTION_CODECOVERAGE'])) {
    $cookie = json_decode($_COOKIE['CODECEPTION_CODECOVERAGE'], true);

    // fix for improperly encoded JSON in Code Coverage cookie with WebDriver.
    // @see https://github.com/Codeception/Codeception/issues/874
    if (!is_array($cookie)) {
        $cookie = json_decode($cookie, true);
    }

    if ($cookie) {
        foreach ($cookie as $key => $value) {
            $_SERVER["HTTP_X_CODECEPTION_" . strtoupper($key)] = $value;
        }
    }
}

if (!function_exists('__c3_error')) {
    function __c3_error($message)
    {
        $errorLogFile = defined('C3_CODECOVERAGE_ERROR_LOG_FILE')
            ? C3_CODECOVERAGE_ERROR_LOG_FILE
            : C3_CODECOVERAGE_MEDIATE_STORAGE . DIRECTORY_SEPARATOR . 'error.txt';
        if (is_writable($errorLogFile)) {
            file_put_contents($errorLogFile, $message);
        } else {
            $message = "Could not write error to log file ($errorLogFile), original message: $message";
        }
        if (!headers_sent()) {
            header('X-Codeception-CodeCoverage-Error: ' . str_replace("\n", ' ', $message), true, 500);
        }
        setcookie('CODECEPTION_CODECOVERAGE_ERROR', $message);
    }
}

if (!array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE', $_SERVER)) {
    return;
}

// Autoload Codeception classes
if (!class_exists('\\Codeception\\Codecept')) {
    if (file_exists(__DIR__ . '/codecept.phar')) {
        require_once 'phar://' . __DIR__ . '/codecept.phar/autoload.php';
    } elseif (stream_resolve_include_path(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        // Required to load some methods only available at codeception/autoload.php
        if (stream_resolve_include_path(__DIR__ . '/vendor/codeception/codeception/autoload.php')) {
            require_once __DIR__ . '/vendor/codeception/codeception/autoload.php';
        }
    } elseif (stream_resolve_include_path('Codeception/autoload.php')) {
        require_once 'Codeception/autoload.php';
    } else {
        __c3_error('Codeception is not loaded. Please check that either PHAR or Composer package can be used');
    }
}

// phpunit codecoverage shimming
if (!class_exists('PHP_CodeCoverage') and class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
    class_alias('SebastianBergmann\CodeCoverage\CodeCoverage', 'PHP_CodeCoverage');
    class_alias('SebastianBergmann\CodeCoverage\Report\Text', 'PHP_CodeCoverage_Report_Text');
    class_alias('SebastianBergmann\CodeCoverage\Report\PHP', 'PHP_CodeCoverage_Report_PHP');
    class_alias('SebastianBergmann\CodeCoverage\Report\Clover', 'PHP_CodeCoverage_Report_Clover');
    class_alias('SebastianBergmann\CodeCoverage\Report\Crap4j', 'PHP_CodeCoverage_Report_Crap4j');
    class_alias('SebastianBergmann\CodeCoverage\Report\Html\Facade', 'PHP_CodeCoverage_Report_HTML');
    class_alias('SebastianBergmann\CodeCoverage\Report\Xml\Facade', 'PHP_CodeCoverage_Report_XML');
    class_alias('SebastianBergmann\CodeCoverage\Exception', 'PHP_CodeCoverage_Exception');
}
// phpunit version
if (!class_exists('PHPUnit_Runner_Version') && class_exists('PHPUnit\Runner\Version')) {
    class_alias('PHPUnit\Runner\Version', 'PHPUnit_Runner_Version');
}

// Load Codeception Config
$configDistFile = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'codeception.dist.yml';
$configFile = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'codeception.yml';

if (isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG'])) {
    $configFile = realpath(__DIR__) . DIRECTORY_SEPARATOR . $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG'];
}
if (file_exists($configFile)) {
    // Use codeception.yml for configuration.
} elseif (file_exists($configDistFile)) {
    // Use codeception.dist.yml for configuration.
    $configFile = $configDistFile;
} else {
    __c3_error(sprintf("Codeception config file '%s' not found", $configFile));
}

try {
    Configuration::config($configFile);
} catch (Exception $e) {
    __c3_error($e->getMessage());
}

if (!defined('C3_CODECOVERAGE_MEDIATE_STORAGE')) {

    // workaround for 'zend_mm_heap corrupted' problem
    gc_disable();

    $memoryLimit = ini_get('memory_limit');
    $requiredMemory = '384M';
    if ((substr($memoryLimit, -1) === 'M' && (int)$memoryLimit < (int)$requiredMemory)
        || (substr($memoryLimit, -1) === 'K' && (int)$memoryLimit < (int)$requiredMemory * 1024)
        || (ctype_digit($memoryLimit) && (int)$memoryLimit < (int)$requiredMemory * 1024 * 1024)
    ) {
        ini_set('memory_limit', $requiredMemory);
    }

    define('C3_CODECOVERAGE_MEDIATE_STORAGE', Codeception\Configuration::logDir() . 'c3tmp');
    define('C3_CODECOVERAGE_PROJECT_ROOT', Codeception\Configuration::projectDir());
    define('C3_CODECOVERAGE_TESTNAME', $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);

    function __c3_build_html_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_HTML();
        $writer->process($codeCoverage, $path . 'html');

        if (file_exists($path . '.tar')) {
            unlink($path . '.tar');
        }

        $phar = new PharData($path . '.tar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $files = $phar->buildFromDirectory($path . 'html');
        array_map('unlink', $files);

        if (in_array('GZ', Phar::getSupportedCompression())) {
            if (file_exists($path . '.tar.gz')) {
                unlink($path . '.tar.gz');
            }

            $phar->compress(Phar::GZ);

            // close the file so that we can rename it
            unset($phar);

            unlink($path . '.tar');
            rename($path . '.tar.gz', $path . '.tar');
        }

        return $path . '.tar';
    }

    function __c3_build_clover_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_Clover();
        $writer->process($codeCoverage, $path . '.clover.xml');

        return $path . '.clover.xml';
    }

    function __c3_build_crap4j_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_Crap4j();
        $writer->process($codeCoverage, $path . '.crap4j.xml');

        return $path . '.crap4j.xml';
    }

    function __c3_build_phpunit_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_XML(PHPUnit_Runner_Version::id());
        $writer->process($codeCoverage, $path . 'phpunit');

        if (file_exists($path . '.tar')) {
            unlink($path . '.tar');
        }

        $phar = new PharData($path . '.tar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $files = $phar->buildFromDirectory($path . 'phpunit');
        array_map('unlink', $files);

        if (in_array('GZ', Phar::getSupportedCompression())) {
            if (file_exists($path . '.tar.gz')) {
                unlink($path . '.tar.gz');
            }

            $phar->compress(Phar::GZ);

            // close the file so that we can rename it
            unset($phar);

            unlink($path . '.tar');
            rename($path . '.tar.gz', $path . '.tar');
        }

        return $path . '.tar';
    }

    function __c3_send_file($filename)
    {
        if (!headers_sent()) {
            readfile($filename);
        }

        return __c3_exit();
    }

    /**
     * @param $filename
     * @param bool $lock Lock the file for writing?
     * @return [null|PHP_CodeCoverage|\SebastianBergmann\CodeCoverage\CodeCoverage, resource]
     */
    function __c3_factory($filename, $lock = false)
    {
        $file = null;
        if ($filename !== null && is_readable($filename)) {
            if ($lock) {
                $file = fopen($filename, 'r+');
                if (flock($file, LOCK_EX)) {
                    $phpCoverage = unserialize(stream_get_contents($file));
                } else {
                    __c3_error("Failed to acquire write-lock for $filename");
                }
            } else {
                $phpCoverage = unserialize(file_get_contents($filename));
            }

            return [$phpCoverage, $file];
        } else {
            $phpCoverage = new PHP_CodeCoverage();
        }

        if (isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_SUITE'])) {
            $suite = $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_SUITE'];
            try {
                $settings = Configuration::suiteSettings($suite, Configuration::config());
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
        } else {
            $settings = Configuration::config();
        }

        try {
            Filter::setup($phpCoverage)
                ->whiteList($settings)
                ->blackList($settings);
        } catch (Exception $e) {
            __c3_error($e->getMessage());
        }

        return [$phpCoverage, $file];
    }

    function __c3_exit()
    {
        if (!isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG'])) {
            exit;
        }
        return null;
    }

    function __c3_clear()
    {
        FileSystem::doEmptyDir(C3_CODECOVERAGE_MEDIATE_STORAGE);
    }
}

if (!is_dir(C3_CODECOVERAGE_MEDIATE_STORAGE)) {
    if (mkdir(C3_CODECOVERAGE_MEDIATE_STORAGE, 0777, true) === false) {
        __c3_error('Failed to create directory "' . C3_CODECOVERAGE_MEDIATE_STORAGE . '"');
    }
}

// evaluate base path for c3-related files
$path = realpath(C3_CODECOVERAGE_MEDIATE_STORAGE) . DIRECTORY_SEPARATOR . 'codecoverage';

$requestedC3Report = (strpos($_SERVER['REQUEST_URI'], 'c3/report') !== false);

$completeReport = $currentReport = $path . '.serialized';
if ($requestedC3Report) {
    set_time_limit(0);

    $route = ltrim(strrchr(rtrim($_SERVER['REQUEST_URI'], '/'), '/'), '/');

    if ($route === 'clear') {
        __c3_clear();
        return __c3_exit();
    }

    list($codeCoverage, ) = __c3_factory($completeReport);

    switch ($route) {
        case 'html':
            try {
                __c3_send_file(__c3_build_html_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'clover':
            try {
                __c3_send_file(__c3_build_clover_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'crap4j':
            try {
                __c3_send_file(__c3_build_crap4j_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'serialized':
            try {
                __c3_send_file($completeReport);
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'phpunit':
            try {
                __c3_send_file(__c3_build_phpunit_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
    }
} else {
    list($codeCoverage, ) = __c3_factory(null);
    $codeCoverage->start(C3_CODECOVERAGE_TESTNAME);
    if (!array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG', $_SERVER)) {
        register_shutdown_function(
            function () use ($codeCoverage, $currentReport) {
                $codeCoverage->stop();
                if (!file_exists(dirname($currentReport))) { // verify directory exists
                    if (!mkdir(dirname($currentReport), 0777, true)) {
                        __c3_error("Can't write CodeCoverage report into $currentReport");
                    }
                }

                // This will either lock the existing report for writing and return it along with a file pointer,
                // or return a fresh PHP_CodeCoverage object without a file pointer. We'll merge the current request
                // into that coverage object, write it to disk, and release the lock. By doing this in the end of
                // the request, we avoid this scenario, where Request 2 overwrites the changes from Request 1:
                //
                //             Time ->
                // Request 1 [ <read>               <write>          ]
                // Request 2 [         <read>                <write> ]
                //
                // In addition, by locking the file for exclusive writing, we make sure no other request try to
                // read/write to the file at the same time as this request (leading to a corrupt file). flock() is a
                // blocking call, so it waits until an exclusive lock can be acquired before continuing.

                list($existingCodeCoverage, $file) = __c3_factory($currentReport, true);
                $existingCodeCoverage->merge($codeCoverage);

                if ($file === null) {
                    file_put_contents($currentReport, serialize($existingCodeCoverage), LOCK_EX);
                } else {
                    fseek($file, 0);
                    fwrite($file, serialize($existingCodeCoverage));
                    fflush($file);
                    flock($file, LOCK_UN);
                    fclose($file);
                }
            },
        );
    }
}

// @codeCoverageIgnoreEnd
