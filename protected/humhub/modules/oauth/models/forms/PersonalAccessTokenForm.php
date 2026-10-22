<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

use humhub\modules\oauth\components\RecentAuthentication;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Creating a personal access token.
 *
 * @since 1.20
 */
class PersonalAccessTokenForm extends Model
{
    private const DEFAULT_LIFETIME = '30';

    public $name;
    public $scopes = [];

    /**
     * @var string|null lifetime in days, '' = never
     */
    public $lifetime;
    public $currentPassword;

    private ?string $token = null;
    private ?RecentAuthentication $recentAuthentication = null;

    public function __construct(private readonly User $user, $config = [])
    {
        parent::__construct($config);
    }

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
            ['currentPassword', 'validateReauthentication', 'skipOnEmpty' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('OauthModule.base', 'Name'),
            'scopes' => Yii::t('OauthModule.base', 'Access'),
            'lifetime' => Yii::t('OauthModule.base', 'Expiration'),
            'currentPassword' => Yii::t('OauthModule.base', 'Your password'),
        ];
    }

    public function beforeValidate()
    {
        $this->lifetime = is_scalar($this->lifetime) ? (string)$this->lifetime : null;

        return parent::beforeValidate();
    }

    public function requiresPassword(): bool
    {
        return $this->getRecentAuthentication()->requiresPassword();
    }

    public function validateReauthentication(string $attribute): void
    {
        $authentication = $this->getRecentAuthentication();
        if ($authentication->isThrottled()) {
            $this->addError($attribute, Yii::t('OauthModule.base', 'Too many attempts, try again later.'));
            return;
        }

        if ($authentication->check(is_string($this->currentPassword) ? $this->currentPassword : null)) {
            return;
        }

        $this->addError($attribute, $this->requiresPassword()
            ? Yii::t('OauthModule.base', 'Your password is incorrect.')
            : Yii::t('OauthModule.base', 'Please sign out and sign in again to create a token.'));
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

    private function getRecentAuthentication(): RecentAuthentication
    {
        return $this->recentAuthentication ??= RecentAuthentication::forCurrentSession($this->user);
    }
}
