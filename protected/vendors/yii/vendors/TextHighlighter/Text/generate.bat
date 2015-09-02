@echo off
rem vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:

rem Console highlighter class generator

rem PHP versions 4 and 5

rem LICENSE: This source file is subject to version 3.0 of the PHP license
rem that is available through the world-wide-web at the following URI:
rem http://www.php.net/license/3_0.txt.  If you did not receive a copy of
rem the PHP License and are unable to obtain it through the web, please
rem send a note to license@php.net so we can mail you a copy immediately.

rem @category   Text
rem @package    Text_Highlighter
rem @author     Andrey Demenev <demenev@gmail.com>
rem @copyright  2004 Andrey Demenev
rem @license    http://www.php.net/license/3_0.txt  PHP License
rem @version    CVS: $Id: generate.bat,v 1.1 2007/06/03 02:35:28 ssttoo Exp $
rem @link       http://pear.php.net/package/Text_Highlighter

set "MHL_PARAMS="
:doshift
set "MHL_PARAMS=%MHL_PARAMS% %1"
shift 
if -%1- == -- GOTO noshift
GOTO doshift
:noshift
@php_bin@ -q -d output_buffering=1 -d include_path="@php_dir@" @bin_dir@/Text/Highlighter/generate.bat %MHL_PARAMS%

GOTO finish
<?php
ob_end_clean();

if (!defined('STDOUT')) {
    define('STDOUT', fopen('php://stdout', 'wb'));
    define('STDERR', fopen('php://stderr', 'wb'));
}
require_once 'Text/Highlighter/Generator.php';
require_once 'Console/Getopt.php';

$options = Console_Getopt::getopt($argv, 'x:p:d:h', array('xml=', 'php=','dir=', 'help'));

if (PEAR::isError($options)) {
    $message = str_replace('Console_Getopt: ','',$options->message);
    usage($message);
}

$source = array();
$dest   = array();
$dir    = '';

$expectp = false;
$expectx = false;
$unexpectedx = false;
$unexpectedp = false;
$si = $di = 0;

foreach ($options[0] as $option) {
    switch ($option[0]) {
        case 'x':
        case '--xml':
            $source[$si] = $option[1];
            if ($si) {
                $di++;
            }
            $si++;
            if ($expectp) {
                $unexpectedx = true;
            }
            $expectp = true;
            $expectx = false;
            break;

        case 'p':
        case '--php':
            if ($expectx) {
                $unexpectedp = true;
            }
            $dest[$di] = $option[1];
            $expectp = false;
            $expectx = true;
            break;

        case 'd':
        case '--dir':
            $dir = $option[1];
            break;

        case 'h':
        case '--help':
            usage();
            break;
    }
}


if ($unexpectedx && !$dir) {
    usage('Unexpected -x or --xml', STDERR);
}

if ($unexpectedp) {
    usage('Unexpected -p or --php', STDERR);
}

$nsource = count($source);
$ndest = count($dest);

if (!$nsource && !$ndest) {
    $source[]='php://stdin';
    if (!$dir) {
        $dest[]='php://stdout';
    } else {
      $dest[] = null;
    }
} elseif ($expectp && !$dir && $nsource > 1) {
    usage('-x or --xml without following -p or --php', STDERR);
} elseif ($nsource == 1 && !$ndest && !$dir) {
    $dest[]='php://stdout';
}

if ($dir && substr($dir,-1)!='/' && substr($dir,-1)!=='\\' ) {
    $dir .= DIRECTORY_SEPARATOR;
}


foreach ($source as $i => $xmlfile)
{
    $gen =& new Text_Highlighter_Generator;
    $gen->setInputFile($xmlfile);
    if ($gen->hasErrors()) {
        break;
    }
    $gen->generate();
    if ($gen->hasErrors()) {
        break;
    }
    if (isset($dest[$i])) {
        $phpfile = $dest[$i];
    } else {
        $phpfile = $dir . $gen->language . '.php';
    }
    $gen->saveCode($phpfile);
    if ($gen->hasErrors()) {
        break;
    }
}
if ($gen->hasErrors()) {
    $errors = $gen->getErrors();
    foreach ($errors as $error) {
        fwrite (STDERR, $error . "\n");
    }
    exit(1);
}

exit(0);

function usage($message='', $file=STDOUT)
{
    $code = 0;
    if ($message) {
        $message .= "\n\n";
        $code = 1;
    }
    $message .= <<<MSG
Generates a highlighter class from XML source
Usage:
generate options

Options:
  -x filename, --xml=filename
        source XML file. Multiple input files can be specified, in which
        case each -x option must be followed by -p unless -d is specified
        Defaults to stdin
  -p filename, --php=filename
        destination PHP file. Defaults to stdout. If specied multiple times,
        each -p must follow -x
  -d dirname, --dir=dirname
        Default destination directory. File names will be taken from XML input
        ("lang" attribute of <highlight> tag)
  -h, --help
        This help
MSG;
    fwrite ($file, $message);
    exit($code);
}
?>
:finish
