<?php

namespace humhub\modules\user\models\fieldtype;

use humhub\helpers\ArrayHelper;
use humhub\modules\user\models\ProfileField;
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
        $variables = ArrayHelper::map(
            $user->profile->getProfileFields(null, [static::class]),
            'internal_name',
            function (ProfileField $profileField) use ($user, $raw, $encode) {
                return $profileField->getUserValue($user, $raw, $encode);
            },
        );

        $twig = new Environment(new ArrayLoader([]));
        $twig->addExtension(new SandboxExtension(new SecurityPolicy(['if'], ['escape']), true));

        return $twig->createTemplate($this->template)->render($variables);
    }

    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
            get_class($this) => [
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
