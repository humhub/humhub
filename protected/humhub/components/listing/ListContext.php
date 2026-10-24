<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\user\models\User;
use Yii;

/**
 * Who asks for a list and for what — passed to every method of a {@see ListFilter}.
 *
 * - `user`: whose list it is, `null` for a guest
 * - `purpose`: what the list is for (`directory`, `picker`, `chooser`, … or a module's own, e.g.
 *   `tasks.board`), `null` = neutral. It selects defaults and presentation and lets a module
 *   restrict a list in one context and not in another — it is never a permission.
 * - `container`: the space or profile the page lives in, if any
 *
 * @since 1.20
 */
final class ListContext
{
    public function __construct(
        public readonly ?User $user = null,
        public readonly ?string $purpose = null,
        public readonly ?ContentContainerActiveRecord $container = null,
    ) {
    }

    /**
     * The context of the current user (a guest when nobody is logged in).
     */
    public static function forCurrentUser(?string $purpose = null, ?ContentContainerActiveRecord $container = null): self
    {
        /** @var User|null $user */
        $user = Yii::$app->user->isGuest ? null : Yii::$app->user->getIdentity();

        return new self($user, $purpose, $container);
    }

    public function withPurpose(?string $purpose): self
    {
        return new self($this->user, $purpose, $this->container);
    }
}
