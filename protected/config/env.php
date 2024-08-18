<?php

$env = [];

if (!empty($moduleAutoloadPaths = $_ENV['moduleAutoloadPaths'])) {
    $moduleAutoloadPaths = explode(',', $moduleAutoloadPaths);

    $common['params']['moduleAutoloadPaths'] = $moduleAutoloadPaths;
}

return $env;
