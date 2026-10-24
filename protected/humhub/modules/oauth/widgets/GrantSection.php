<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Grant;
use humhub\modules\user\widgets\security\SecuritySection;
use Yii;
use yii\db\ActiveQuery;

/**
 * A section listing grants of the current user.
 *
 * @since 1.20
 */
abstract class GrantSection extends SecuritySection
{
    /**
     * @var Grant[]|null
     */
    private ?array $grants = null;

    /**
     * @return ActiveQuery the grants of this section, for the current user
     */
    abstract protected function createQuery(): ActiveQuery;

    /**
     * @return Grant[]
     */
    protected function getGrants(): array
    {
        return $this->grants ??= $this->createQuery()->all();
    }

    /**
     * @return string[] the labels of the grant's known scopes
     */
    protected static function getScopeLabels(Grant $grant): array
    {
        $labels = [];
        foreach ($grant->getScopeIds() as $id) {
            if ($scope = ScopeRegistry::get($id)) {
                $labels[] = $scope->label;
            }
        }

        return $labels;
    }

    protected static function getUserId(): int
    {
        return (int)Yii::$app->user->id;
    }
}
