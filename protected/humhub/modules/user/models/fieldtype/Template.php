<?php

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\ProfileField;
use humhub\modules\user\Module;
use Twig\Environment;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicy;
use Yii;

/**
 * Template is a virtual profile field that combines other profile fields using a Twig template.
 *
 * @package humhub\modules\user\models\fieldtype
 * @since 1.17
 */
class Template extends BaseType
{
    public $template;

    public $type = 'text';

    public $isVirtual = true;

    public function rules()
    {
        return [
            [['template'], 'string', 'max' => 255],
        ];
    }

    public function getUserValue($user, bool $raw = true, bool $encode = true): ?string
    {
        // Source fields are intentionally not limited to visible ones, so that fields hidden
        // on the profile (e.g. numeric inputs of a calculation) can still be used in the template.
        $variables = [];
        $profileFields = ProfileField::find()
            ->where(['NOT IN', 'field_type_class', [static::class]])
            ->all();

        foreach ($profileFields as $profileField) {
            $value = $profileField->getUserValue($user, $raw, $encode);
            if ($value !== null && $value !== '') {
                $variables[$profileField->internal_name] = $value;
            }
        }

        $twig = new Environment(new ArrayLoader([]));
        $twig->addExtension(new SandboxExtension(static::getSecurityPolicy(), true));

        return $twig->createTemplate($this->template)->render($variables);
    }

    /**
     * @return SecurityPolicy the sandbox policy with the Twig features allowed in templates
     * @see Module::$profileFieldTemplateAllowedTags
     * @since 1.19.1
     */
    protected static function getSecurityPolicy(): SecurityPolicy
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return new SecurityPolicy(
            $module->profileFieldTemplateAllowedTags,
            $module->profileFieldTemplateAllowedFilters,
            [],
            [],
            $module->profileFieldTemplateAllowedFunctions,
        );
    }

    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
            static::class => [
                'type' => 'form',
                'title' => Yii::t('UserModule.profile', 'Select field options'),
                'elements' => [
                    'template' => [
                        'type' => 'textarea',
                        'label' => Yii::t('UserModule.profile', 'Template'),
                        'class' => 'form-control autosize',
                        'hint' => Yii::t('UserModule.profile', 'Twig template that will be used to render this field. You can use the internal names as variables, e.g. `{{ firstname }} {{ lastname }}`'),
                    ],
                ],
            ]]);
    }

    protected static function getHiddenFormFields()
    {
        return ['searchable', 'required', 'show_at_registration', 'editable', 'directory_filter'];
    }

    public function save()
    {
        $this->profileField->editable = 0;
        $this->profileField->searchable = 0;
        $this->profileField->required = 0;
        $this->profileField->show_at_registration = 0;
        $this->profileField->directory_filter = 0;
        return parent::save();
    }
}
