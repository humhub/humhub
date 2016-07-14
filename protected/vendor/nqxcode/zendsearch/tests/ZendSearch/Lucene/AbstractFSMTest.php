<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearchTest\Lucene;

use ZendSearch\Lucene;
use Zend\Search;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class AbstractFSMTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $doorFSM = new testFSMClass();

        $this->assertTrue($doorFSM instanceof Lucene\AbstractFSM);
        $this->assertEquals($doorFSM->getState(), testFSMClass::OPENED);
    }

    public function testSetState()
    {
        $doorFSM = new testFSMClass();

        $this->assertEquals($doorFSM->getState(), testFSMClass::OPENED);

        $doorFSM->setState(testFSMClass::CLOSED_AND_LOCKED);
        $this->assertEquals($doorFSM->getState(), testFSMClass::CLOSED_AND_LOCKED );

        $wrongStateExceptionCatched = false;
        try {
            $doorFSM->setState(testFSMClass::OPENED_AND_LOCKED);
        } catch(\ZendSearch\Lucene\Exception\InvalidArgumentException $e) {
            $wrongStateExceptionCatched = true;
        }
        $this->assertTrue($wrongStateExceptionCatched);
    }

    public function testReset()
    {
        $doorFSM = new testFSMClass();

        $doorFSM->setState(testFSMClass::CLOSED_AND_LOCKED);
        $this->assertEquals($doorFSM->getState(), testFSMClass::CLOSED_AND_LOCKED);

        $doorFSM->reset();
        $this->assertEquals($doorFSM->getState(), testFSMClass::OPENED);
    }

    public function testProcess()
    {
        $doorFSM = new testFSMClass();

        $doorFSM->process(testFSMClass::CLOSE);
        $this->assertEquals($doorFSM->getState(), testFSMClass::CLOSED);

        $doorFSM->process(testFSMClass::LOCK);
        $this->assertEquals($doorFSM->getState(), testFSMClass::CLOSED_AND_LOCKED);

        $doorFSM->process(testFSMClass::UNLOCK);
        $this->assertEquals($doorFSM->getState(), testFSMClass::CLOSED);

        $doorFSM->process(testFSMClass::OPEN);
        $this->assertEquals($doorFSM->getState(), testFSMClass::OPENED);

        $wrongInputExceptionCatched = false;
        try {
            $doorFSM->process(testFSMClass::LOCK);
        } catch(\ZendSearch\Lucene\Exception\ExceptionInterface $e) {
            $wrongInputExceptionCatched = true;
        }
        $this->assertTrue($wrongInputExceptionCatched);
    }

    public function testActions()
    {
        $doorFSM = new testFSMClass();

        $this->assertFalse($doorFSM->actionTracer->action2Passed /* 'closed' state entry action*/);
        $doorFSM->process(testFSMClass::CLOSE);
        $this->assertTrue($doorFSM->actionTracer->action2Passed);

        $this->assertFalse($doorFSM->actionTracer->action8Passed /* 'closed' state exit action*/);
        $doorFSM->process(testFSMClass::LOCK);
        $this->assertTrue($doorFSM->actionTracer->action8Passed);

        $this->assertFalse($doorFSM->actionTracer->action4Passed /* 'closed&locked' state +'unlock' input action */);
        $doorFSM->process(testFSMClass::UNLOCK);
        $this->assertTrue($doorFSM->actionTracer->action4Passed);

        $this->assertFalse($doorFSM->actionTracer->action6Passed /* 'locked' -> 'opened' transition action action */);
        $doorFSM->process(testFSMClass::OPEN);
        $this->assertTrue($doorFSM->actionTracer->action6Passed);
    }
}

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 */
class FSMData
{
    public $action1Passed = false;
    public $action2Passed = false;
    public $action3Passed = false;
    public $action4Passed = false;
    public $action5Passed = false;
    public $action6Passed = false;
    public $action7Passed = false;
    public $action8Passed = false;

    public function action1()  { $this->action1Passed = true; }
    public function action2()  { $this->action2Passed = true; }
    public function action3()  { $this->action3Passed = true; }
    public function action4()  { $this->action4Passed = true; }
    public function action5()  { $this->action5Passed = true; }
    public function action6()  { $this->action6Passed = true; }
    public function action7()  { $this->action7Passed = true; }
    public function action8()  { $this->action8Passed = true; }
}

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 */
class testFSMClass extends Lucene\AbstractFSM
{
    const OPENED            = 0;
    const CLOSED            = 1;
    const CLOSED_AND_LOCKED = 2;

    const OPENED_AND_LOCKED = 3; // Wrong state, should not be used


    const OPEN   = 0;
    const CLOSE  = 1;
    const LOCK   = 3;
    const UNLOCK = 4;

    /**
     * Object to trace FSM actions
     *
     * @var FSMData
     */
    public $actionTracer;

    public function __construct()
    {
        $this->actionTracer = new FSMData();

        $this->addStates(array(self::OPENED, self::CLOSED, self::CLOSED_AND_LOCKED));
        $this->addInputSymbols(array(self::OPEN, self::CLOSE, self::LOCK, self::UNLOCK));

        $unlockAction     = new Lucene\FSMAction($this->actionTracer, 'action4');
        $openAction       = new Lucene\FSMAction($this->actionTracer, 'action6');
        $closeEntryAction = new Lucene\FSMAction($this->actionTracer, 'action2');
        $closeExitAction  = new Lucene\FSMAction($this->actionTracer, 'action8');

        $this->addRules(array( array(self::OPENED,            self::CLOSE,  self::CLOSED),
                               array(self::CLOSED,            self::OPEN,   self::OPEN),
                               array(self::CLOSED,            self::LOCK,   self::CLOSED_AND_LOCKED),
                               array(self::CLOSED_AND_LOCKED, self::UNLOCK, self::CLOSED, $unlockAction),
                             ));

        $this->addInputAction(self::CLOSED_AND_LOCKED, self::UNLOCK, $unlockAction);

        $this->addTransitionAction(self::CLOSED, self::OPENED, $openAction);

        $this->addEntryAction(self::CLOSED, $closeEntryAction);

        $this->addExitAction(self::CLOSED, $closeExitAction);
    }
}
