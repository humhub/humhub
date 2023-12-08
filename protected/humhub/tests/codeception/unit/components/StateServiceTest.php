<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidStateException;
use humhub\interfaces\StatableInterface;
use humhub\interfaces\StateServiceInterface;
use yii\base\Event;
use yii\base\InvalidConfigException;

class StateServiceTest extends Unit
{
    public function testInitStateService()
    {
        $calledEvents = [];

        $callback = static function (Event $event) use (&$calledEvents) {
            $calledEvents[$event->name] = $event->name;
        };

        Event::on(StateServiceInterface::class, StateServiceInterface::EVENT_INIT_STATES, $callback);
        Event::on(StateServiceInterface::class, StateServiceInterface::EVENT_INIT, $callback);
        Event::on(StateServiceInterface::class, StateServiceInterface::EVENT_SET_RECORD, $callback);

        $service = new StateServiceMock();

        Event::off(StateServiceInterface::class, StateServiceInterface::EVENT_INIT_STATES, $callback);
        Event::off(StateServiceInterface::class, StateServiceInterface::EVENT_INIT, $callback);
        Event::off(StateServiceInterface::class, StateServiceInterface::EVENT_SET_RECORD, $callback);

        $this->assertInstanceOf(StateServiceInterface::class, $service);
        $this->assertTrue($service->isInitStatesCalled(), "StateServiceMock::initStates() has not been called by the init procedure.");

        $this->assertArrayHasKey(StateServiceInterface::EVENT_INIT_STATES, $calledEvents, 'Event StateServiceInterface::EVENT_INIT_STATES was not fired.');
        $this->assertEquals(StateServiceInterface::EVENT_INIT_STATES, array_shift($calledEvents), 'Event StateServiceInterface::EVENT_INIT was not fired first.');

        $this->assertArrayHasKey(StateServiceInterface::EVENT_INIT, $calledEvents, 'Event StateServiceInterface::EVENT_INIT was not fired.');
        $this->assertEquals(StateServiceInterface::EVENT_INIT, array_shift($calledEvents), 'Event StateServiceInterface::EVENT_INIT was not fired second.');

        $this->assertArrayNotHasKey(StateServiceInterface::EVENT_SET_RECORD, $calledEvents, 'Event StateServiceInterface::EVENT_SET_RECORD was fired without providing a record.');
    }

    public function testValidateState()
    {
        $service = new StateServiceMock();

        $valid = $service->validateState($result, StatableInterface::STATE_PUBLISHED);
        $this->assertTrue($valid, 'StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals(StatableInterface::STATE_PUBLISHED, $result, 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $valid = $service->validateState($result, [StatableInterface::STATE_PUBLISHED, StatableInterface::STATE_PUBLISHED]);
        $this->assertTrue($valid, 'StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals([StatableInterface::STATE_PUBLISHED], array_values($result), 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $valid = $service->validateState($result, [StatableInterface::STATE_PUBLISHED, StatableInterface::STATE_DRAFT]);
        $this->assertTrue($valid, 'StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals([StatableInterface::STATE_PUBLISHED, StatableInterface::STATE_DRAFT], array_values($result), 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $valid = $service->validateState($result, [StatableInterface::STATE_PUBLISHED => ['user.id' => 15]]);
        $this->assertTrue($valid, 'Conditional StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals([StatableInterface::STATE_PUBLISHED => ['user.id'  => 15]], $result, 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $valid = $service->validateState($result, [StatableInterface::STATE_PUBLISHED => 'user.id = 15']);
        $this->assertTrue($valid, 'Conditional StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals([StatableInterface::STATE_PUBLISHED => 'user.id = 15'], $result, 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $valid = $service->validateState($result, 'published');
        $this->assertTrue($valid, '"published" has not been validated successfully.');
        $this->assertEquals(StatableInterface::STATE_PUBLISHED, $result, '"published" was not returned correctly.');

        $valid = $service->validateState($result, ['published' => 'user.id = 15', 'draft' => 'user.id = 1']);
        $this->assertTrue($valid, 'Conditional StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals([StatableInterface::STATE_PUBLISHED => 'user.id = 15', StatableInterface::STATE_DRAFT => 'user.id = 1'], $result, 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $valid = $service->validateState($result, [StatableInterface::STATE_PUBLISHED, 'deleted' => 'user.id = 15', [StatableInterface::STATE_DRAFT => 'user.id = 1']]);
        $this->assertTrue($valid, 'Conditional StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals([StatableInterface::STATE_PUBLISHED => StatableInterface::STATE_PUBLISHED, StatableInterface::STATE_DELETED => 'user.id = 15', StatableInterface::STATE_DRAFT => 'user.id = 1'], $result, 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $valid = $service->validateState($result, ['deleted' => 'user.id = 15', [StatableInterface::STATE_DELETED => ['user.id' => 1]]]);
        $this->assertTrue($valid, 'Conditional StatableInterface::STATE_PUBLISHED has not been validated successfully.');
        $this->assertEquals([StatableInterface::STATE_DELETED => ['OR', 'user.id = 15', ['user.id' => 1]]], $result, 'StatableInterface::STATE_PUBLISHED was not returned correctly.');
    }

    public function testValidateStateInvalidArgument()
    {
        $service = new StateServiceMock();

        $errorMessage = 'If parameter $allowArray is true, argument $state must be iterable, array, Traversable. State configuration: [1,100]. int given.';

        $valid = $service->validateState($result, [StatableInterface::STATE_PUBLISHED, StatableInterface::STATE_DELETED], null, false, false);
        $this->assertFalse($valid, '$allowArray switch ignored.');
        $this->assertInstanceOf(InvalidArgumentTypeException::class, $result);
        $this->assertEquals($errorMessage, $result->getMessage(), 'StatableInterface::STATE_PUBLISHED was not returned correctly.');

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage($errorMessage);
        $service->validateState($result, [StatableInterface::STATE_PUBLISHED, StatableInterface::STATE_DELETED], null, false);
    }
    public function testValidateStateInvalidConfiguration()
    {
        $service = new StateServiceMock();

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('Invalid state configuration.');
        $service->validateState($result, [StatableInterface::STATE_PUBLISHED => ["dummy", "condition"], StatableInterface::STATE_DELETED => "dummy condition"]);
    }

    public function testValidateStateInvalidState2000()
    {
        $service = new StateServiceMock();

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('{"The selected state \'2000\' is unknown.":2000}. State configuration: [2000]');
        $service->validateState($result, 2000);
    }
    public function testValidateStateInvalidConditionEmptyArray()
    {
        $service = new StateServiceMock();

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('Invalid state configuration (empty array).');
        $service->validateState($result, [StatableInterface::STATE_PUBLISHED => []]);
    }

    public function testGetDefaultState()
    {
        $service = new StateServiceMock();

        $default = $service->getDefaultState();

        $this->assertEquals(StatableInterface::STATE_DRAFT, $default, 'Invalid default state.');
    }

    public function testAllowState()
    {
        $service = new StateServiceMock();

        $states = $service->getStates();

        $service->allowState(StatableInterface::STATE_SOFT_DELETED);
        $states['softDeleted'] = StatableInterface::STATE_SOFT_DELETED;
        $this->assertEquals($states, $service->getStates(), 'Invalid states.');

        $service->allowState(999, 'test');
        $states['test'] = 999;
        $this->assertEquals($states, $service->getStates(), 'Invalid states.');
    }

    public function testDenyState()
    {
        $service = new StateServiceMock();

        $states = $service->getStates();

        $service->denyState(StatableInterface::STATE_DELETED);

        unset($states['deleted']);
        $this->assertEquals($states, $service->getStates(), 'Invalid states.');

        $statesOrig = $states;

        $service->allowState(999, 'test1');
        $service->allowState(999, 'test2');
        $states['test1'] = 999;
        $states['test2'] = 999;
        $this->assertEquals($states, $service->getStates(), 'Invalid states.');

        $service->denyState(999);
        $this->assertEquals($statesOrig, $service->getStates(), 'Invalid states.');

        $service->denyState(888);
        $this->assertEquals($statesOrig, $service->getStates(), 'Invalid states.');
    }
}
