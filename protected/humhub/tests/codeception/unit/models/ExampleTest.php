<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;
use Codeception\Specify;

class ExampleTest extends TestCase
{

    use Specify;

    public function testSomething()
    {
        $this->specify('test', function () {
            expect('false should be false', false)->false();
        });
    }

}
