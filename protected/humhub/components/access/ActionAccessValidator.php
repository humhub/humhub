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
 * Date: 28.07.2017
 * Time: 17:43
 */

namespace humhub\components\access;


/**
 * This Validator filters out non action related rules and supports a $strict mode, which will require all validator
 * related rules to pass.
 *
 * If $strict mode is set to false only one of the validator related rules have pass.
 *
 * Subclasses of ActionAccessValidator only have to extend the `validate()` function for validating a single rule.
 *
 * @package humhub\components\access
 */
abstract class ActionAccessValidator extends AccessValidator
{

    /**
     * @var bool if set to true (default) all validator related rules have to pass otherwise only one
     */
    public $strict = true;

    /**
     * Runs the validation against all validator and action related rules.
     *
     * This function will return true, if there is no action related rule for this validator.
     *
     * @param $rule array
     * @param $access ControllerAccess
     * @return boolean
     */
     public function run()
     {
         $rules = $this->filterRelatedRules();

         if(empty($rules)) {
             return true;
         }

         foreach ($rules as $rule) {
             $can = $this->validate($rule);
             if ($can && !$this->strict) {
                 return true;
             } else if (!$can && $this->strict) {
                 return false;
             }
         }

         return $this->strict;
     }

    /**
     * Filters our rules not related to the current validator and action.
     *
     * @param array|null $rules
     * @return array
     */
    protected function filterRelatedRules($rules = null)
    {
        $result = [];
        foreach (parent::filterRelatedRules($rules) as $rule) {
            if($this->isActionRelated($rule)) {
                $result[] = $rule;
            }
        }
        return $result;
    }

    protected abstract function validate($rule);
}