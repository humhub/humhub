<?php

require(__DIR__ . '/../Parser.php');
require(__DIR__ . '/../Markdown.php');


$markdown = '';
$markdown = file_get_contents(__DIR__ . '/markdown-data/specs.md');
//$markdown = file_get_contents(__DIR__ . '/github-data/github-sample.md');


//ini_set('xhprof.output_dir', __DIR__ . '/xhprof');

// http://de3.php.net/manual/en/xhprof.examples.php
xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

for ($n = 0; $n < 100; $n++) {
	$pd = new \cebe\markdown\Markdown();
	$pd->parse($markdown);
}

$xhprof_data = xhprof_disable();

$XHPROF_ROOT = __DIR__ . '/../vendor/facebook/xhprof/';
include_once $XHPROF_ROOT . '/xhprof_lib/utils/xhprof_lib.php';
include_once $XHPROF_ROOT . '/xhprof_lib/utils/xhprof_runs.php';

$xhprof_runs = new XHProfRuns_Default();
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");

echo "http://localhost/xhprof/xhprof_html/index.php?run={$run_id}&source=xhprof_testing\n";
