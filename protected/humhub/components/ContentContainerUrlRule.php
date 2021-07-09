<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\modules\content\components\ContentContainerActiveRecord;
use yii\web\UrlRule;
use yii\web\UrlRuleInterface;
use yii\base\BaseObject;

/**
 * Content Container URL Rule
 *
 * @author luke
 * @since 1.9
 */
abstract class ContentContainerUrlRule extends BaseObject implements UrlRuleInterface
{

    /**
     * @var string default route to content container home
     */
    protected $defaultRoute;

    /**
     * @var string Prefix of the container URLs
     */
    protected $urlPrefix;

    /**
     * @var string[] Possible prefixes for route
     */
    protected $routePrefixes = ['<contentContainer>'];

    /**
     * @var array map with Content Container guid/url pairs
     */
    public static $containerUrlMap;

    /**
     * Get Content Container by URL
     *
     * @param string $url
     * @return ContentContainerActiveRecord
     */
    abstract static protected function getContentContainerByUrl(string $url): ?ContentContainerActiveRecord;

    /**
     * Get Content Container by guid
     *
     * @param string $guid
     * @return ContentContainerActiveRecord
     */
    abstract protected static function getContentContainerByGuid(string $guid): ?ContentContainerActiveRecord;

    /**
     * Get URL map data from Content Container
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @return string|null
     */
    abstract protected static function getUrlMapFromContentContainer(ContentContainerActiveRecord $contentContainer): ?string;

    /**
     * Check if the object is of proper instance
     *
     * @param ContentContainerActiveRecord $contentContainer
     * @return bool
     */
    abstract protected static function isContentContainerInstance(ContentContainerActiveRecord $contentContainer): bool;

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if (!isset($params['cguid'])) {
            return false;
        }

        $urlPart = static::getUrlByContentContainerGuid($params['cguid']);
        if ($urlPart === null) {
            return false;
        }

        $url = $this->urlPrefix . '/' . urlencode(mb_strtolower($urlPart));
        unset($params['cguid']);

        if ($route == $this->defaultRoute) {
            $route = '';
        }

        foreach ($manager->rules as $rule) {
            if ($result = $this->createUrlByClass($rule, $manager, $url, $route, $params)) {
                return $result;
            }
            if ($result = $this->createUrlByRule($rule, $route, $params)) {
                list($route, $params) = $result;
            }
        }

        $url .= '/' . $route;

        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }

        return $url;
    }

    /**
     * Try to create URL by class rule
     *
     * @param UrlRule $rule
     * @param UrlManager $manager the URL manager
     * @param string $containerUrlPath Current relative URL path to the Content Container
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     * @return string|bool the created URL, or false if this rule cannot be used for creating this URL.
     */
    private function createUrlByClass($rule, UrlManager $manager, string $containerUrlPath, string $route, array $params)
    {
        if (!($rule instanceof ContentContainerUrlRuleInterface)) {
            return false;
        }

        return $rule->createContentContainerUrl($manager, $containerUrlPath, $route, $params);
    }

    /**
     * Try to create URL by simple rule
     *
     * @param UrlRule $rule
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     * @return array|false
     */
    private function createUrlByRule($rule, string $route, array $params)
    {
        if (!($rule instanceof UrlRule)) {
            return false;
        }

        if ($rule->route !== $route) {
            return false;
        }

        if (!($ruleParts = $this->getRuleParts($rule))) {
            return false;
        }

        $ruleRoute = [];
        foreach ($ruleParts as $r => $rulePart) {
            if (preg_match( '/^<([a-zA-Z0-9_-]+)>$/', $rulePart, $ruleParamMatch)) {
                if (!isset($params[$ruleParamMatch[1]])) {
                    return false;
                }
                $ruleRoute[] = urlencode($params[$ruleParamMatch[1]]);
                unset($params[$ruleParamMatch[1]]);
            } else {
                $ruleRoute[] = $rulePart;
            }
        }

        return [implode('/', $ruleRoute), $params];
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (strpos($pathInfo, $this->urlPrefix . '/') !== 0) {
            return false;
        }

        $parts = explode('/', $pathInfo, 3);
        if (!isset($parts[1])) {
            return false;
        }

        $contentContainer = static::getContentContainerByUrl($parts[1]);
        if (!$contentContainer) {
            return false;
        }

        if (!isset($parts[2]) || $parts[2] == '') {
            $parts[2] = $this->defaultRoute;
        }

        $params = $request->get();
        $params['cguid'] = $contentContainer->guid;

        foreach ($manager->rules as $rule) {
            if ($result = $this->parseRequestByClass($rule, $contentContainer, $manager, $parts[2], $params)) {
                return $result;
            }
            if ($result = $this->parseRequestByRule($rule, $parts[2], $params)) {
                return $result;
            }
        }

        return [$parts[2], $params];
    }

    /**
     * Try to parse by class rule
     *
     * @param UrlRule $rule
     * @param ContentContainerActiveRecord $container Content Container (Space/User)
     * @param UrlManager $manager the URL manager
     * @param string $containerUrlPath Current relative URL path to the Content Container
     * @param array $urlParams Additional GET params of the current request
     * @return array|bool the parsing result. The route and the parameters are returned as an array.
     */
    private function parseRequestByClass($rule, ContentContainerActiveRecord $contentContainer, UrlManager $manager, string $containerUrlPath, array $urlParams)
    {
        if (!($rule instanceof ContentContainerUrlRuleInterface)) {
            return false;
        }

        return $rule->parseContentContainerRequest($contentContainer, $manager, $containerUrlPath, $urlParams);
    }

    /**
     * Try to parse by simple rule
     *
     * @param UrlRule $rule
     * @param string $containerUrlPath
     * @param array $urlParams
     * @return array|false
     */
    private function parseRequestByRule($rule, string $containerUrlPath, array $urlParams)
    {
        if (!($rule instanceof UrlRule)) {
            return false;
        }

        if (!($ruleParts = $this->getRuleParts($rule))) {
            return false;
        }

        $requestParts = explode('/', $containerUrlPath);
        if (count($ruleParts) !== count($requestParts)) {
            // Skip the rule is not matched to current request
            return false;
        }

        $ruleParams = [];
        foreach ($ruleParts as $r => $rulePart) {
            if (preg_match( '/^<([a-zA-Z0-9_-]+)>$/', $rulePart, $ruleParamMatch)) {
                $ruleParams[$ruleParamMatch[1]] = $requestParts[$r];
            } elseif ($rulePart !== $requestParts[$r]) {
                // Skip the rule if at least one part is different
                return false;
            }
        }

        return [$rule->route, array_merge($urlParams, $ruleParams)];
    }

    /**
     * Gets Content Container url name by given guid
     *
     * @param string $guid
     * @return string|null the Content Container url part
     */
    public static function getUrlByContentContainerGuid(string $guid): ?string
    {
        if (array_key_exists($guid, static::$containerUrlMap)) {
            return static::$containerUrlMap[$guid];
        }

        $contentContainer = null;
        if (UrlManager::$cachedLastContainerRecord !== null && UrlManager::$cachedLastContainerRecord->guid === $guid) {
            if (static::isContentContainerInstance(UrlManager::$cachedLastContainerRecord)) {
                $contentContainer = UrlManager::$cachedLastContainerRecord;
            }
        } else {
            $contentContainer = static::getContentContainerByGuid($guid);
        }

        static::$containerUrlMap[$guid] = $contentContainer ? static::getUrlMapFromContentContainer($contentContainer) : null;

        return static::$containerUrlMap[$guid];
    }

    /**
     * Get parts of rule
     *
     * @param UrlRule $rule
     * @return string[]|false
     */
    private function getRuleParts($rule)
    {
        foreach ($this->routePrefixes as $routePrefix) {
            if (strpos($rule->name, $routePrefix . '/') === 0) {
                return explode('/', substr($rule->name, strlen($routePrefix . '/')));
            }
        }

        return false;
    }

}
