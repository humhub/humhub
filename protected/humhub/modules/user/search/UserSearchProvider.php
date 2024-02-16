<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\search;

use humhub\interfaces\SearchProviderInterface;
use Yii;
use yii\helpers\Url;

class UserSearchProvider implements SearchProviderInterface
{
    public ?string $keyword = null;

    protected ?array $results = null;

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Yii::t('UserModule.base', 'Profile');
    }

    /**
     * @inheritdoc
     */
    public function getAllResultsUrl(): string
    {
        return Url::to(['/people', 'keyword' => $this->keyword]);
    }

    /**
     * @inheritdoc
     */
    public function search(): void
    {
        if ($this->keyword === null) {
            return;
        }

        $this->results = ['total' => 12];
        // TODO: Implement search process here
    }

    /**
     * @inheritdoc
     */
    public function isSearched(): bool
    {
        return $this->results !== null;
    }

    /**
     * @inheritdoc
     */
    public function getTotal(): int
    {
        return isset($this->results['total']) ? (int) $this->results['total'] : 0;
    }
}
