<?php

namespace tests\codeception\_support;

use Codeception\Events;

class HumHubModuleExtension extends \Codeception\Extension
{   
    // list events to listen to
    public static $events = array(
        Events::MODULE_INIT  => 'beforeSuite',
    );

    // methods that handle events

    public function beforeSuite(\Codeception\Event\SuiteEvent $e) {
        $settings = $e->getSettings();
        #$settings['modules']['config']['WebDriver']['url'] = '';
        #$e->getSuite()->getModules()['WebDriver']->_reconfigure(['url' => 'http://localhost/codebase/humhub/v1.1-dev/index.phpxxx']);
    }
}
