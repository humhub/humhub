<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use humhub\modules\content\models\Content;
use humhub\modules\user\helpers\AuthHelper;
use Yii;

/**
 * This stream filter will only include global content and furthermore
 * only includes public content if the query is done by a guest
 *
 * @package humhub\modules\stream\models\filters
 * @since 1.16
 */
class GlobalContentStreamFilter extends StreamQueryFilter
{
    /**
     * @inheritDoc
     */
    public function apply(): void
    {
        // Limit to global content
        $this->query->andWhere(['content.contentcontainer_id' => null]);

        // Limit to public content when guest
        if (Yii::$app->user->isGuest) {
            if (AuthHelper::isGuestAccessEnabled()) {
                $this->query->andWhere('content.visibility = :visibility', [':visibility' => Content::VISIBILITY_PUBLIC]);
            } else {
                $this->query->andWhere('1=0');
            }
        }
    }
}
