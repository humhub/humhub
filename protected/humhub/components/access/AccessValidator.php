<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 30.07.2017
 * Time: 03:07
 */

namespace humhub\components\access;


use Yii;
use yii\base\InvalidParamException;
use yii\base\Object;

/**
 * AccessValidators are responsible for validating a given set of rules.
 *
 * Rules consist of an array with at leas an rule name and optional further rule settings.
 * If only a rule name is given, the rule is considered global, otherwise it may be restricted to specific actions by
 * providing an action setting e.g.:
 *
 * ```
 * // Global myRule
 * ['myRule']
 *
 * // MyRule restricted to action1 and action2
 * ['myRule' => ['action1', 'action2']]
 *
 * // Alternative action configuration
 * ['myRule', 'actions' => ['action1', 'action2']]
 * ```
 *
 * A Validator has an unique name which is used to detect related rules and can filter out non related rules by
 * means of the `filterRelatedRules()` function.
 *
 * Subclasses have to overwrite the `run()` function, which holds the actual validation logic.
 *
 * AccessValidators have access to a ControllerAccess instance, which holds the ruleset and validation state.
 *
 * This abstract validator class furthermore provides some helper functions as:
 *
 *  - `isActionRelated()`: Checks if a given rule is related to the current action
 *  - `extractActions()`: Extracts the action settings from a given rule array
 *  - `getRuleName()`: Extracts the rule name from a given rule array
 *
 * @package humhub\components\access
 */
abstract class AccessValidator extends Object
{
    /**
     * @var string the name of the valdiator
     */
    public $name;

    /**
     * @var int http error code used in case the validation failes
     */
    public $code = 403;

    /**
     * @var string validator error message
     */
    public $reason;

    /**
     * @var ControllerAccess access instance
     */
    public $access;

    /**
     * @var bool determines if this validator is only interested in action related rules or all validator related rules
     */
    public $actionFilter = true;

    public function init()
    {
        if(!$this->name) {
            $this->name = static::class;
        }

        if(empty($this->reason)) {
            $this->reason = Yii::t('error', 'You are not permitted to access this section.');
        }
    }

    /**
     * Responsible for validating the given ruleset.
     * Related rules may be filtered by means of the `filterRelatedRules()` function.
     * The whole rule set can be retrieved by calling `$this->access->rules`.
     *
     *
     * @return boolean true if validation passed otherwise true
     */
    abstract function run();

    /**
     * Filters out all rules which are not related to this validator.
     *
     * @param $rules
     * @return array
     */
    protected function filterRelatedRules($rules = null)
    {
        if($rules === null) {
            $rules = $this->access->getRules();
        }

        $result = [];
        foreach ($rules as $rule) {
            $ruleName = $this->getRuleName($rule);

            if($this->name === $ruleName) {
                $result[] = $rule;
            }

        }
        return $result;
    }

    /**
     * Checks if the current action is contained in the given $rule.
     * This is the case either if the current action is contained in the rules action settings or
     * the rule is global (no action restriction).
     *
     * @param array|string $actionArray single action id or array of action ids
     * @return bool
     */
    protected function isActionRelated($rule)
    {
        $actions = $this->extractActions($rule);

        // If no action array is given we consider the rule to be controller global
        if (empty($actions)) {
            return true;
        }

        if (!is_array($actions) && !is_string($actions)) {
            throw new InvalidParamException('Invalid rule provided!');
        }

        $actions = is_string($actions) ? [$actions] : $actions;

        return in_array($this->access->action, $actions);
    }

    /**
     * Extracts actions settings form a given rule.
     *
     * Action rules can be either set like:
     *
     * ['ruleName', 'actions' => ['action1', 'action2']]
     *
     * or in some cases:
     *
     * ['ruleName' => ['action1', 'action2']]
     *
     * @param $rule
     * @return array
     */
    protected function extractActions($rule)
    {
        $name = $this->getRuleName($rule);
        $actions = [];

        if (isset($rule['actions'])) {
            $actions = $rule['actions'];
        } else {
            $actions = isset($rule[$name]) ? $rule[$name] : $actions;
        }

        return $actions;
    }

    /**
     * Extracts the ruleName from the given array.
     *
     * @param $arr
     * @return mixed|null
     */
    protected function getRuleName($rule)
    {
        if(empty($rule)) {
            return null;
        }

        $firstKey = current(array_keys($rule));
        if(is_string($firstKey)) {
            return $firstKey;
        } else {
            return $rule[$firstKey];
        }
    }

    /**
     * @return string the error message in case the validation fails
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return int http error code used in case the validation fails
     */
    public function getCode()
    {
        return $this->code;
    }
}