<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use Yii;

/**
 * Creating a personal access token.
 *
 * @since 1.20
 */
class PersonalAccessTokenForm extends ReauthenticationForm
{
    private const DEFAULT_LIFETIME = '30';

    public $name;
    public $scopes = [];

    /**
     * @var string|null lifetime in days, '' = never
     */
    public $lifetime;

    private ?string $token = null;

    public function init()
    {
        parent::init();

        $options = $this->getLifetimeValues();
        $this->lifetime ??= in_array(self::DEFAULT_LIFETIME, $options, true) ? self::DEFAULT_LIFETIME : reset($options);
    }

    public function rules()
    {
        return [
            [['name', 'scopes'], 'required'],
            ['name', 'string', 'max' => 100],
            ['scopes', 'each', 'rule' => ['in', 'range' => array_keys($this->getScopeOptions())]],
            ['lifetime', 'in', 'range' => $this->getLifetimeValues(), 'strict' => true],
            ...parent::rules(),
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('OauthModule.base', 'Name'),
            'scopes' => Yii::t('OauthModule.base', 'Access'),
            'lifetime' => Yii::t('OauthModule.base', 'Expiration'),
            ...parent::attributeLabels(),
        ];
    }

    public function beforeValidate()
    {
        $this->lifetime = is_scalar($this->lifetime) ? (string)$this->lifetime : null;

        return parent::beforeValidate();
    }

    /**
     * @return array<string, string> scope id => label, as allowed for personal access tokens
     */
    public function getScopeOptions(): array
    {
        $options = [];
        foreach (Module::instance()->getPatScopes() as $id) {
            if ($scope = ScopeRegistry::get($id)) {
                $options[$id] = $scope->label;
            }
        }

        return $options;
    }

    /**
     * @return array<string, string> lifetime in days ('' = never) => label
     */
    public function getLifetimeOptions(): array
    {
        $options = [];
        foreach ((new PersonalAccessTokenService())->getLifetimeOptions() as $days) {
            $options[$days === null ? '' : (string)$days] = $days === null
                ? Yii::t('OauthModule.base', 'Never')
                : Yii::t('OauthModule.base', '{n,plural,=1{1 day} other{# days}}', ['n' => $days]);
        }

        return $options;
    }

    public function create(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        [$this->token] = (new PersonalAccessTokenService())->create(
            $this->user,
            (string)$this->name,
            (array)$this->scopes,
            $this->lifetime === '' ? null : (int)$this->lifetime,
        );

        return true;
    }

    /**
     * @return string|null the created token, only available right after [[create()]]
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return string[] the lifetime option keys, as strings: numeric array keys turn into integers
     */
    private function getLifetimeValues(): array
    {
        return array_map('strval', array_keys($this->getLifetimeOptions()));
    }
}
