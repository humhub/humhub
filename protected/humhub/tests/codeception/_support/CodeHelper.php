<?php

namespace tests\codeception\_support;

use Codeception\Module;

/**
 * This helper is used to populate the database with needed fixtures before any tests are run.
 * In this example, the database is populated with the demo login user, which is used in acceptance
 * and functional tests.  All fixtures will be loaded before the suite is started and unloaded after it
 * completes.
 */
class CodeHelper extends Module
{

    /**
     * Method called before any suite tests run. Loads User fixture login user
     * to use in acceptance and functional tests.
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
    {
        include __DIR__ . '/../unit/_bootstrap.php';
    }

    public function assertContainsError($model, $message)
    {
        $this->assertTrue($model->hasErrors());

        $result = false;
        foreach ($model->errors as $errorMessages) {
            if(in_array($message, $errorMessages)) {
                $result = true;
                break;
            }
        }
        $this->assertTrue($result);
    }
    
    public function assertNotContainsError($model, $message)
    {
        $result = false;
        foreach ($model->errors as $errorMessages) {
            if(in_array($message, $errorMessages)) {
                $result = true;
                break;
            }
        }
        $this->assertFalse($result);
    }

}
