<?php

/* This error handler will turn all notices, warnings, and errors into fatal
 * errors, unless they have been suppressed with the @-operator. */

function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    header("Content-Type: text/html; charset=utf-8");
    /* The @-operator (used with chdir() below) temporarely makes
     * error_reporting() return zero, and we don't want to die in that case.
     * We do note the error in the output, though. */
    if (error_reporting() == 0) {
        $_SESSION['output'] .= $errstr . "\n";
    } else {
        die('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title></title>
</head>
<body>
  <h1>Fatal Error!</h1>
  <p><b>' . $errstr . '</b></p>
  <p>in <b>' . $errfile . '</b>, line <b>' . $errline . '</b>.</p>
</body>
</html>');
    }
}

/* Installing our error handler makes PHP die on even the slightest problem.
 * This is what we want in a security critical application like this. */
set_error_handler('error_handler');

/*
 *  check HTTP-Auth-Header
 */
function http_digest_parse($input) {
    //all required data
    $requirements = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1,
        'username' => 1, 'uri' => 1, 'response' => 1);
    $data = array();
    $key = implode('|', array_keys($requirements));

    $matches = array();
    preg_match_all('@(' . $key . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $input, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $data[$match[1]] = $match[3] ? $match[3] : $match[4];
        unset($requirements[$match[1]]);
    }

    return $requirements ? false : $data;
}

/* Initialize some variables we need again and again. */
$username = (string) filter_input(INPUT_POST, 'username');
$password = (string) filter_input(INPUT_POST, 'password');
$nounce = (string) filter_input(INPUT_POST, 'nounce');

$command = (string) filter_input(INPUT_POST, 'command');

session_start();

$realm = 'Humhub Cronjob';

// User => Password
$user = array('user' => 'password');

//$authdigest = filter_input(INPUT_SERVER, 'PHP_AUTH_DIGEST');
if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . $realm .
            '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) .
            '"');
    $_SESSION['authenticated'] = false;
} else if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($user[$data['username']])) {
    $_SESSION['authenticated'] = false;
} else {
    $A1 = md5($data['username'] . ':' . $realm . ':' . $user[$data['username']]);
    $A2 = md5((string) filter_input(INPUT_SERVER, 'REQUEST_METHOD') . ':' . $data['uri']);
    $check_answer = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] .
            ':' . $data['cnonce'] . ':' . $data['qop'] . ':' .
            $A2);

    if ($data['response'] != $check_answer) {
        $_SESSION['authenticated'] = false;
    } else {
        $_SESSION['authenticated'] = true;
    }
}

/* Enforce default non-authenticated state if the above code didn't set it
 * already. */
if (!isset($_SESSION['authenticated'])) {
    $_SESSION['authenticated'] = false;
}

if (!$_SESSION['authenticated']) {
    die('Not authenticated!');
} else {
    header("Content-Type: text/html; charset=utf-8");
    echo '<pre>';
    $_SESSION['output'] = '';

    $arg = filter_input(INPUT_GET, 't');
    if ($arg != 'hourly' && $arg != 'daily') {
        return;
    }

    if (!chdir('protected')) {
        return;
    }

    $command = './yii cron/' . $arg;

    $io = array();
    $p = proc_open(
            $command, array(1 => array('pipe', 'w'),
        2 => array('pipe', 'w')), $io
    );

    /* Read output sent to stdout. */
    while (!feof($io[1])) {
        $line = fgets($io[1]);
        if (function_exists('mb_convert_encoding')) {
            /* (hopefully) fixes a strange "htmlspecialchars(): Invalid multibyte sequence in argument" error */
            $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
        }
        $_SESSION['output'] .= htmlspecialchars($line, ENT_COMPAT, 'UTF-8');
    }
    /* Read output sent to stderr. */
    while (!feof($io[2])) {
        $line = fgets($io[2]);
        if (function_exists('mb_convert_encoding')) {
            /* (hopefully) fixes a strange "htmlspecialchars(): Invalid multibyte sequence in argument" error */
            $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
        }
        $_SESSION['output'] .= htmlspecialchars($line, ENT_COMPAT, 'UTF-8');
    }

    fclose($io[1]);
    fclose($io[2]);
    proc_close($p);
    echo '</pre>';
}
echo '<pre>';
$lines = substr_count($_SESSION['output'], "\n");
$padding = str_repeat("\n", max(0, 25 - $lines));
echo rtrim($padding . $_SESSION['output']);
echo '</pre>';
?>
