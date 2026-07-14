<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\gates;

use humhub\components\gates\RequestClass;
use humhub\components\gates\UserGateInterface;

/**
 * Configurable gate implementation for GateManager tests.
 */
class FakeGate implements UserGateInterface
{
    public string $id = 'fake';
    public int $sortOrder = 100;
    public bool $open = false;
    public array $route = ['/fake/gate'];
    public array $allowedRoutes = [];
    /** @var RequestClass[] request classes this gate applies to */
    public array $applies = [RequestClass::FullPage, RequestClass::Ajax];
    public bool $cacheable = true;
    public int $isOpenCalls = 0;

    public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isOpen(): bool
    {
        $this->isOpenCalls++;
        return $this->open;
    }

    public function getRoute(): array
    {
        return $this->route;
    }

    public function getAllowedRoutes(): array
    {
        return $this->allowedRoutes;
    }

    public function appliesTo(RequestClass $requestClass): bool
    {
        return in_array($requestClass, $this->applies, true);
    }

    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    public function onIntercept(): void
    {
    }
}
