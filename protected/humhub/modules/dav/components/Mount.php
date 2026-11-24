<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Closure;
use LogicException;
use Sabre\DAV\ICollection;

/**
 * What a module serves under `/dav/<name>/`: a Sabre collection, and the OAuth scopes a
 * token needs to read it and to change it.
 *
 * The collection is built only for a request that reaches it, for the authenticated user
 * (`Yii::$app->user`). It decides itself what that user may see and change, as the module's
 * web interface does: a scope is an upper bound, never a permission.
 *
 * @since 1.20
 */
final class Mount
{
    /**
     * A name is one path segment, the same for every user.
     */
    public const NAME_PATTERN = '/^[a-z0-9-]+$/';

    /**
     * @param string $name the first segment of the path, see [[NAME_PATTERN]]
     * @param string|string[] $readScope the scope a token needs for reading (`GET`, `PROPFIND` …);
     *        any of a list suffices, `full` always does
     * @param string|string[] $writeScope the scope a token needs for any other method
     * @param Closure(): ICollection $collection builds the collection; its name must be `$name`
     */
    public function __construct(
        public readonly string $name,
        public readonly string|array $readScope,
        public readonly string|array $writeScope,
        private readonly Closure $collection,
    ) {
    }

    public function createCollection(): ICollection
    {
        $collection = ($this->collection)();

        if (!$collection instanceof ICollection || $collection->getName() !== $this->name) {
            throw new LogicException('The WebDAV mount "' . $this->name . '" must build a collection of that name.');
        }

        return $collection;
    }
}
