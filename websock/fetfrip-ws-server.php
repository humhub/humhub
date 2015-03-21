#!/usr/bin/env php
<?php

require_once(__DIR__ . '/fetfrip-comment-server.php');

$comment_server = new commentServer("0.0.0.0","8888");

try {
    $comment_server->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
