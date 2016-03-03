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
  <meta http-equiv="Content-Script-Type" content="text/javascript">
  <meta http-equiv="Content-Style-Type" content="text/css">
  <meta name="generator" content="phpshell">
  <link rel="shortcut icon" type="image/x-icon" href="phpshell.ico">
  <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
  <h1>Fatal Error!</h1>
  <p><b>' . $errstr . '</b></p>
  <p>in <b>' . $errfile . '</b>, line <b>' . $errline . '</b>.</p>

  <hr>

  <p>Please consult the <a href="README">README</a>, <a
  href="INSTALL">INSTALL</a>, and <a href="SECURITY">SECURITY</a> files for
  instruction on how to use PHP Shell.</p>

  <hr>

  <address>
  Copyright &copy; 2000&ndash;2012, the Phpshell-team. Get the latest
  version at <a
  href="http://phpshell.sourceforge.net/">http://phpshell.sourceforge.net/</a>.
  </address>

</body>
</html>');
    }
}

/* Installing our error handler makes PHP die on even the slightest problem.
 * This is what we want in a security critical application like this. */
set_error_handler('error_handler');

// Funktion zum analysieren der HTTP-Auth-Header
function http_digest_parse($txt) {
    // gegen fehlende Daten schützen
    $noetige_teile = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1,
        'username' => 1, 'uri' => 1, 'response' => 1);
    $daten = array();
    $schluessel = implode('|', array_keys($noetige_teile));

    $treffer = array();
    preg_match_all('@(' . $schluessel . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $treffer, PREG_SET_ORDER);

    foreach ($treffer as $t) {
        $daten[$t[1]] = $t[3] ? $t[3] : $t[4];
        unset($noetige_teile[$t[1]]);
    }

    return $noetige_teile ? false : $daten;
}

/* Initialize some variables we need again and again. */
$username = (string) filter_input(INPUT_POST, 'username');
$password = (string) filter_input(INPUT_POST, 'password');
$nounce = (string) filter_input(INPUT_POST, 'nounce');

$command = (string) filter_input(INPUT_POST, 'command');

session_start();

$realm = 'Humhub Cronjob';

// Benutzer => Passwort
$benutzer = array('username' => 'password');

//$authdigest = filter_input(INPUT_SERVER, 'PHP_AUTH_DIGEST');
if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . $realm .
            '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) .
            '"');
    $_SESSION['authenticated'] = false;
} else if (!($daten = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($benutzer[$daten['username']])) {
    $_SESSION['authenticated'] = false;
} else {
// Erzeugen einer gültigen Antwort
    $A1 = md5($daten['username'] . ':' . $realm . ':' . $benutzer[$daten['username']]);
    $A2 = md5((string) filter_input(INPUT_SERVER, 'REQUEST_METHOD') . ':' . $daten['uri']);
    $gueltige_antwort = md5($A1 . ':' . $daten['nonce'] . ':' . $daten['nc'] .
            ':' . $daten['cnonce'] . ':' . $daten['qop'] . ':' .
            $A2);

    if ($daten['response'] != $gueltige_antwort) {
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