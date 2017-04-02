<?php
namespace humhub\modules\user\controllers;

use yii\validators\Validator;

class PasswordSecurityValidator extends Validator
{
    public $low = 0;
    public $up = 0;
    public $spec = 0;
    public $digit = 0;
    public $max;

    /**
     * Validation
     *
     * Function checks whether fulfill this requirements  :
     * <ul>
     *  <li>is a string</li>
     *  <li>has the minimal number of lower case characters</li>
     *  <li>has the minimal number of upper case characters</li>
     *  <li>has the minimal number of digit characters </li>
     *  <li>has the minimal number of special characters </li>
     *  <li>has the minimal length is respected</li>
     * </ul>
     * @param CModel $object
     * @param string $attribute
     */
    public function validateAttribute($object, $attribute)
    {
        $this->checkParams();

        $value = $object->$attribute;

        // is a string
        if (!is_string($value))
        {
            $this->addError($object, 
                            $attribute, 
                            ":attribute is a :type and is must be a string.", 
                            array(':attribute' => $attribute, ':type' => gettype($value))
            );
            return; // other checks will throw errors or exception, so end validation here.
        }

        // number of lower case characters
        $found = preg_match_all('![a-z]!', $value, $whatever);
        if ($found < $this->low)
        {
            $this->addErrorInternal($object, 
                            $attribute, 
                           'Minuscule',
                            array('found' => $found, 'required' => $this->low)
            );            
        }

        // number of upper case characters
        $found = preg_match_all('![A-Z]!', $value, $whatever);
        if ($found < $this->up)
        {
            $this->addErrorInternal($object, 
                            $attribute, 
                            'Majuscule',
                            array('found' => $found, 'required' => $this->up)
            );
        }
        
        // special characters
        $found = preg_match_all('![\W]!', $value, $whatever);
        if ($found < $this->spec)
        {
            $this->addErrorInternal($object, 
                            $attribute, 
                            'caractère special', 
                            array('found' => $found, 'required' => $this->spec)
            );
        }

        // digit characters
        $found = preg_match_all('![\d]!', $value, $whatever);
        if ($found < $this->digit)
        {
            $this->addErrorInternal($object, 
                            $attribute, 
                            'Chiffre', 
                            array('found' => $found, 'required' => $this->digit)
            );
        }

    }

    /**
    * Checks the provided parameters
    * Checks if sum of required params values is greater than max
    * @throw CException if more than max
    */
    private function checkParams()
    {
        $this->max = (int) $this->max;
        if($this->max && ($this->up + $this->digit + $this->low + $this->spec) > $this->max)
	    throw new CException('Total number of required characters is greater than max : Validation is impossible !');
    }

    /**
    * Adds an error about the specified attribute to the active record.
    * This is a helper method that call addError which performs message selection and internationalization.
    *
    * Construct the message and the params array to call addError().
    *
    * @param CModel $object the data object being validated
    * @param string $attribute the attribute being validated
    * @param string $tested_param the tested property (eg 'upper case') for generating the error message
    * @param array $values values for the placeholders :is and :should in the error message - array(['found'] => <int>, ['required'] => <int>)
    *
    * @todo change message for correct message with 'max' param
    */
    private function addErrorInternal($object, $attribute,$tested_param, array $values)
    {
        $message = "Le :attribute ne contient pas assez de caractère(s) :tested_param. Trouvé :found alors qu'il doit en contenir au moins :required."; 
        $params = array(':attribute' => $attribute, ':tested_param' => $tested_param, ':found' => $values['found'], ':required' => $values['required']);
        parent::addError($object, $attribute, $message, $params);
    }

}

