<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\User;
use Yii;
use yii\caching\TagDependency;

/**
 * Base type for virtual profile fields
 *
 * Virtual profile fields are read-only and can be used to display content
 * from other sources (e.g. user table).
 *
 * @since 1.6
 */
abstract class BaseTypeVirtual extends BaseType
{
    /**
     * @inheritdoc
     */
    public $type = 'hidden';

    /**
     * @inheritdoc
     */
    public $isVirtual = true;

    /**
     * @var bool whether the field value can be cached
     */
    protected bool $isCacheable = false;

    private const CACHE_PREFIX = 'profile_field_';

    /**
     * @inheritdoc
     */
    final public function getUserValue(User $user, bool $raw = true, bool $encode = true): ?string
    {
        if (!$this->isCacheable) {
            return $this->getVirtualUserValue($user, $raw, $encode);
        }

        $cacheTag = self::CACHE_PREFIX . $user->id;

        $cacheKey = $cacheTag . '_'
            . $this->profileField->id . '_'
            . intval($raw) . '_'
            . intval($encode);

        return Yii::$app->cache->getOrSet($cacheKey, function () use ($user, $raw, $encode) {
            return $this->getVirtualUserValue($user, $raw, $encode);
        }, null, new TagDependency(['tags' => $cacheTag]));
    }

    /**
     * @inheritDoc
     */
    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
            static::class => [
                'type' => 'form',
                'title' => '',
                'elements' => [],
            ]]);
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(?User $user = null, array $options = []): array
    {
        return parent::getFieldFormDefinition($user, array_merge([
            'isVisible' => false,
        ], $options));
    }

    /**
     * @inheritdoc
     */
    protected static function getHiddenFormFields()
    {
        return ['searchable', 'required', 'show_at_registration', 'editable', 'directory_filter'];
    }

    /**
     * Returns the readonly virtual value for the given User
     *
     * @param User $user
     * @param bool $raw
     * @param bool $encode
     * @return string
     */
    abstract protected function getVirtualUserValue(User $user, bool $raw = true, bool $encode = true): string;

    /**
     * @inheritDoc
     */
    public function save()
    {
        $this->profileField->editable = 0;
        $this->profileField->searchable = 0;
        $this->profileField->required = 0;
        $this->profileField->show_at_registration = 0;
        $this->profileField->directory_filter = 0;
        return parent::save();
    }

    /**
     * @since 1.18.1
     */
    public static function flushCache(User $user): void
    {
        TagDependency::invalidate(Yii::$app->cache, self::CACHE_PREFIX . $user->id);
    }
}
