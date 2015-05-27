<?php

/**
 * ProfileFieldType is the base class for all Profile Field Types.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class ProfileFieldType extends CFormModel {

    /**
     * Holds all profile field types
     *
     * Array
     *  Classname => Field type title
     *
     * @var Array
     */
    public $fieldTypes = array();

    /**
     * Corresponding ProfileField Model
     *
     * @var type
     */
    public $profileField = null;

    public function init() {
        // Intercept this controller
        Yii::app()->interceptor->intercept($this);

        // Fire Event
        if ($this->hasEventHandler('onInit'))
            $this->onInit(new CEvent($this));

        return parent::init();
    }

    /**
     * This event is raised after init is performed.
     * @param CEvent $event the event parameter
     */
    public function onInit($event) {
        $this->raiseEvent('onInit', $event);
    }

    /**
     * Links a ProfileField to the ProfileFieldType.
     *
     * @param type $profileField
     */
    public function setProfileField($profileField) {
        $this->profileField = $profileField;
        $this->load();
    }

    /**
     * Returns a list of all available field type classes.
     *
     * @return Array
     */
    public function getFieldTypes() {
        $fieldTypes = array_merge(array(
            'ProfileFieldTypeNumber' => Yii::t('UserModule.models_ProfileFieldType', 'Number'),
            'ProfileFieldTypeText' => Yii::t('UserModule.models_ProfileFieldType', 'Text'),
            'ProfileFieldTypeTextArea' => Yii::t('UserModule.models_ProfileFieldType', 'Text Area'),
            'ProfileFieldTypeSelect' => Yii::t('UserModule.models_ProfileFieldType', 'Select List'),
            'ProfileFieldTypeDateTime' => Yii::t('UserModule.models_ProfileFieldType', 'Datetime'),
            'ProfileFieldTypeBirthday' => Yii::t('UserModule.models_ProfileFieldType', 'Birthday'),
                ), $this->fieldTypes);
        return $fieldTypes;
    }

    /**
     * Returns an array of instances of all available field types.
     *
     * @return Array
     */
    public function getTypeInstances($profileField = null) {

        $types = array();
        foreach ($this->getFieldTypes() as $className => $title) {
            if (Helpers::CheckClassType($className, 'ProfileFieldType')) {
                $instance = new $className;
                if ($profileField != null) {
                    $instance->profileField = $profileField;


                    // Seems current type, so try load data
                    if ($profileField->field_type_class == $className) {
                        $instance->load();
                    }
                }
                $types[] = $instance;
            }
        }
        return $types;
    }

    /**
     * Return the Form Element to edit the value of the Field
     */
    public function getFieldFormDefinition() {

        $definition = array($this->profileField->internal_name => array(
                'type' => 'text',
                'class' => 'form-control',
                'readonly' => (!$this->profileField->editable)
        ));

        return $definition;
    }

    /**
     * Returns the Edit Form for administrators this Field Type.
     *
     * This method should be overwritten by the file type class.
     *
     * @param type $definition
     * @return Array of Form Definition
     */
    public function getFormDefinition($definition = array()) {

        $definition[get_class($this)]['class'] = "fieldTypeSettings " . get_class($this);
        return $definition;
    }

    /**
     * Validates a ProfileFieldType
     *
     * This is only necessary when its linked to a profileField and the profiletype
     * has the current type of profilefieldtype
     *
     * @return boolean
     */
    public function validate($attributes = null, $clearErrors = true) {

        // Bound to a profile field?
        if ($this->profileField != null) {
            // Current Profile Field matches the selected profile field
            if ($this->profileField->field_type_class == get_class($this)) {
                return parent::validate($attributes, $clearErrors);
            }
        }

        return true;
    }

    /**
     * Saves the profile field type
     *
     * The settings/configuration for a ProfileFieldType are saved in ProfileField
     * in attribute "field_type_config" as JSON data.
     *
     * The ProfileFieldType Class itself can overwrite this behavior.
     */
    public function save() {

        $data = array();
        foreach ($this->attributeNames() as $attributeName) {

            // Dont save profile field attribute
            if ($attributeName == 'profileField')
                continue;

            $data[$attributeName] = $this->$attributeName;
        }
        $this->profileField->field_type_config = CJSON::encode($data);
        $this->profileField->save();

        // Clear Database Schema
        Yii::app()->db->schema->getTable('profile', true);
        Profile::model()->refreshMetaData();
    }

    /**
     * Loads the profile field type settings
     *
     * These settings are loaded from the underlying ProfileField.
     */
    public function load() {
        $config = CJSON::decode($this->profileField->field_type_config);
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                if (property_exists($this, $key))
                    $this->$key = $value;
            }
        }
    }

    /**
     * Deletes a Profile Field Type
     */
    public function delete() {
        // Try create column name
        if (Profile::model()->columnExists($this->profileField->internal_name)) {
            $sql = "ALTER TABLE profile DROP `" . $this->profileField->internal_name . "`;";
            $this->profileField->dbConnection->createCommand($sql)->execute();
        }
    }

    /**
     * Adds the new profile type to the profile table.
     *
     * This method should be overwritten by the child class.
     */
    public function addToProfileTable() {
        return true;
    }

    /**
     * Returns validation rules for field type.
     * The inherited field type class should pass his rules to this method.
     *
     * @param type $rules
     * @return Array rules
     */
    public function getFieldRules($rules = array()) {

        if ($this->profileField->required)
            $rules[] = array($this->profileField->internal_name, 'required');


        return $rules;
    }

    /**
     * Returns the value of a given user of this field
     *
     * @param type $user
     * @param type $raw
     * @return type
     */
    public function getUserValue($user, $raw = true) {
        $internalName = $this->profileField->internal_name;
        return $user->profile->$internalName;
    }

    public function getLabels() {
        $labels = array();
        $labels[$this->profileField->internal_name] = Yii::t($this->profileField->getTranslationCategory(), $this->profileField->title);
        return $labels;
    }

    public function addFieldType($fieldClass, $title) {
        $this->fieldTypes[$fieldClass] = $title;
    }

}