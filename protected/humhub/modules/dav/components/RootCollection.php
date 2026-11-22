<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Sabre\DAV\Collection;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\ICollection;

/**
 * `/dav/`: the mounts the token may read. Nothing can be added here.
 *
 * @since 1.20
 */
class RootCollection extends Collection
{
    /**
     * @var array<string, ICollection> the collections built in this request
     */
    private array $built = [];

    /**
     * @param array<string, Mount> $mounts by name
     */
    public function __construct(private readonly array $mounts, private readonly ScopeAccess $access)
    {
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        $children = [];
        foreach ($this->mounts as $name => $mount) {
            if ($this->access->allows($mount->readScope)) {
                $children[] = $this->getChild($name);
            }
        }

        return $children;
    }

    /**
     * @inheritdoc
     */
    public function getChild($name)
    {
        $mount = $this->mounts[$name] ?? null;
        if ($mount === null || !$this->access->allows($mount->readScope)) {
            throw new NotFound('No such mount: ' . $name);
        }

        return $this->built[$name] ??= $mount->createCollection();
    }

    /**
     * @inheritdoc
     */
    public function childExists($name)
    {
        $mount = $this->mounts[$name] ?? null;

        return $mount !== null && $this->access->allows($mount->readScope);
    }
}
