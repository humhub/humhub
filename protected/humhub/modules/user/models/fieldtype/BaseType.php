<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Helpers;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * ProfileFieldType is the base class for all Profile Field Types.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 *
 * @property array $fieldFormDefinition
 * @property array $labels
 */
class BaseType extends Model
{

    /**
     * @event Event an event raised after init. Can be used to add custom field types.
     * 
     * Example config.php:
     *     'events' => [
     *         [BaseType::class, BaseType::EVENT_INIT, [Events::class, 'onFieldTypesInit']]
     *     ]
     * 
     * Example Events.php:
     *     public static function onFieldTypesInit($event) {
     *         $event->sender->addFieldType(CustomFieldType::class, "Custom field");
     *     }
     * 
     * @since 1.12
     */
    const EVENT_INIT = "fieldTypesInit";

    /**
     * Holds all profile field types
     *
     * Array
     *  Classname => Field type title
     *
     * @var array
     */
    public $fieldTypes = [];

    /**
     * Corresponding ProfileField Model
     *
     * @var ProfileField
     */
    public $profileField = null;

    /**
     * @var boolean is a virtual field (readonly)
     * @see BaseTypeVirtual
     * @since 1.6
     */
    public $isVirtual = false;

    /**
     * @var boolean can be used as directory filter (readonly)
     * @since 1.9
     */
    public $canBeDirectoryFilter = false;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();

        $this->trigger(self::EVENT_INIT);
    }

    /**
     * Links a ProfileField to the ProfileFieldType.
     *
     * @param ProfileField $profileField
     */
    public function setProfileField($profileField)
    {
        $this->profileField = $profileField;
        $this->loadFieldConfig();
    }

    /**
     * Returns a list of all available field type classes.
     *
     * @return array
     */
    public function getFieldTypes()
    {
        $fieldTypes = array_merge([
            Number::class => Yii::t('UserModule.profile', 'Number'),
            Text::class => Yii::t('UserModule.profile', 'Text'),
            TextArea::class => Yii::t('UserModule.profile', 'Text Area'),
            Select::class => Yii::t('UserModule.profile', 'Select List'),
            Date::class => Yii::t('UserModule.profile', 'Date'),
            DateTime::class => Yii::t('UserModule.profile', 'Datetime'),
            Birthday::class => Yii::t('UserModule.profile', 'Birthday'),
            CountrySelect::class => Yii::t('UserModule.profile', 'Country'),
            MarkdownEditor::class => Yii::t('UserModule.profile', 'Markdown'),
            Checkbox::class => Yii::t('UserModule.profile', 'Checkbox'),
            CheckboxList::class => Yii::t('UserModule.profile', 'Checkbox List'),
            UserEmail::class => Yii::t('UserModule.profile', 'E-mail address of the user'),
            UserName::class => Yii::t('UserModule.profile', 'Username'),
            UserMemberSince::class => Yii::t('UserModule.profile', 'Creation date of the user'),
            UserLastLogin::class => Yii::t('UserModule.profile', 'Last login date of the user'),
        ], $this->fieldTypes);

        return $fieldTypes;
    }

    /**
     * Returns additional form field item options for all field types.
     *
     * @internal
     * @return array
     */
    final public function getFieldTypeItemOptions()
    {
        $result = [];
        foreach ($this->getFieldTypes() as $field_class => $label) {
            $result[$field_class] = ['data-hidden-fields' => call_user_func($field_class.'::getHiddenFormFields')];
        }
        return $result;
    }

    /**
     * Can be used to define form fields which should be hidden when editing or creating a profile field of this type e.g:
     *
     *  - searchable
     *  - editable
     *  - required
     *  - show_at_registration
     *  - visible
     *
     * @return array
     */
    protected static function getHiddenFormFields()
    {
        return [];
    }

    /**
     * Returns an array of instances of all available field types.
     *
     * @param ProfileField|null $profileField
     * @return array
     * @throws Exception
     */
    public function getTypeInstances($profileField = null)
    {
        $types = [];
        foreach ($this->getFieldTypes() as $className => $title) {
            if (Helpers::CheckClassType($className, static::class)) {
                /** @var BaseType $instance */
                $instance = new $className;
                if ($profileField !== null) {
                    $instance->profileField = $profileField;

                    // Seems current type, so try load data
                    if ($profileField->field_type_class == $className) {
                        $instance->loadFieldConfig();
                    }
                }
                $types[] = $instance;
            }
        }

        return $types;
    }

    /**
     * Return the Form Element to edit the value of the Field
     * 
     * @param User $user If a context exists, the user is passed through here. May be null e.g. when filtering multiple users.
     * @return array
     */
    public function getFieldFormDefinition(User $user = null)
    {
        return [
            $this->profileField->internal_name => [
                'type' => 'text',
                'class' => 'form-control',
                'readonly' => (!$this->profileField->editable)
            ]
        ];
    }

    /**
     * Returns the Edit Form for administrators this Field Type.
     *
     * This method should be overwritten by the file type class.
     *
     * @param array $definition
     * @return array of Form Definition
     */
    public function getFormDefinition($definition = [])
    {
        $className = get_class($this);
        $definition[$className]['class'] = 'fieldTypeSettings ' . str_replace('\\', '_', $className);

        return $definition;
    }

    /**
     * Validates a ProfileFieldType
     *
     * This is only necessary when its linked to a profileField and the profiletype
     * has the current type of profilefieldtype
     *
     * @param ProfileField|null $attributes
     * @param bool $clearErrors
     * @return boolean
     */
    public function validate($attributes = null, $clearErrors = true)
    {

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
     * @throws Exception
     */
    public function save()
    {

        $data = [];

        foreach ($this->attributes as $attributeName => $value) {
            // Dont save profile field attribute
            if ($attributeName == 'profileField') {
                continue;
            }

            $data[$attributeName] = $this->$attributeName;
        }
        $this->profileField->field_type_config = Json::encode($data);

        if (!$this->profileField->save()) {
            throw new Exception('Could not save profile field!');
        }
        // Clear Database Schema
        Yii::$app->getDb()->getSchema()->refreshTableSchema(Profile::tableName());

        return true;
    }

    /**
     * Loads the profile field type settings
     *
     * These settings are loaded from the underlying ProfileField.
     */
    public function loadFieldConfig()
    {
        if ($this->profileField->field_type_config == '') {
            return;
        }

        $config = Json::decode($this->profileField->field_type_config);
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Deletes a Profile Field Type
     * @throws \yii\db\Exception
     */
    public function delete()
    {
        $columnName = $this->profileField->internal_name;
        if (Profile::columnExists($columnName)) {
            $db = Yii::$app->getDb();
            $query = $db->getQueryBuilder()->dropColumn(Profile::tableName(), $this->profileField->internal_name);
            $db->createCommand($query)->execute();
        } else {
            Yii::error('Could not delete profile column - not exists!');
        }
    }

    /**
     * Adds the new profile type to the profile table.
     *
     * This method should be overwritten by the child class.
     * @return bool
     */
    public function addToProfileTable()
    {
        return true;
    }

    /**
     * Returns validation rules for field type.
     * The inherited field type class should pass his rules to this method.
     *
     * @param array $rules
     * @return array rules
     */
    public function getFieldRules($rules = [])
    {

        if ($this->profileField->required) {
            $rules[] = [$this->profileField->internal_name, 'required', 'except' => Profile::SCENARIO_EDIT_ADMIN];
        }

        return $rules;
    }

    /**
     * Returns the value of a given user of this field
     *
     * @param User $user
     * @param bool $raw
     * @return string
     */
    public function getUserValue(User $user, $raw = true): ?string
    {
        $internalName = $this->profileField->internal_name;

        if ($raw) {
            return $user->profile->$internalName;
        } else {
            return Html::encode($user->profile->$internalName);
        }
    }

    /**
     * Return array of Labels for Field
     * @return array
     */
    public function getLabels()
    {
        return [
            $this->profileField->internal_name => Yii::t(
                $this->profileField->getTranslationCategory(),
                $this->profileField->title
            )
        ];
    }

    /**
     * Add new FieldType to stack
     * @param string $fieldClass
     * @param string $title
     */
    public function addFieldType($fieldClass, $title)
    {
        $this->fieldTypes[$fieldClass] = $title;
    }

    /**
     * This method is called before the field value is stored in Profile table.
     *
     * @param string $value
     * @return string|null modified value
     */
    public function beforeProfileSave($value)
    {
        if ($value == '') {
            return null;
        }

        return $value;
    }

    /**
     * Load field type default settings to the profile
     *
     * @param Profile $profile
     */
    public function loadDefaults(Profile $profile)
    {
    }
}
