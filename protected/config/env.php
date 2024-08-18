<?php

$env = [];

if (!empty($moduleAutoloadPaths = $_ENV['moduleAutoloadPaths'])) {
    $moduleAutoloadPaths = explode(',', $moduleAutoloadPaths);

    $env['params']['moduleAutoloadPaths'] = $moduleAutoloadPaths;
}

return $env;
