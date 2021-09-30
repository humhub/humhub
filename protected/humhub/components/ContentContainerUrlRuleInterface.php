<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\modules\content\components\ContentContainerActiveRecord;
use yii\web\UrlManager;

/**
 * ContentContainerUrlRuleInterface is the interface that should be implemented by URL rule classes
 * which handle routes under Content Container path.
 * For example, if URL `/s/space-url-name/module-id/<title>` should be routed to `/module-id/controller/action?title=<title>`
 *
 * @author luke
 * @since 1.9
 */
interface ContentContainerUrlRuleInterface
{
    /**
     * Parses the request under Content Container (Space/User) and returns the corresponding route and parameters.
     *
     * @param ContentContainerActiveRecord $container Content Container (Space/User)
     * @param UrlManager $manager the URL manager
     * @param string $containerUrlPath Current relative URL path to the Content Container
     * @param array $urlParams Additional GET params of the current request
     * @return array|bool the parsing result. The route and the parameters are returned as an array.
     * If false, it means this rule cannot be used to parse this path info.
     */
    public function parseContentContainerRequest(ContentContainerActiveRecord $container, UrlManager $manager, string $containerUrlPath, array $urlParams);

    /**
     * Creates a URL according under the Content Container (Space/User) route and parameters.
     *
     * @param UrlManager $manager the URL manager
     * @param string $containerUrlPath Current relative URL path to the Content Container
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     * @return string|bool the created URL, or false if this rule cannot be used for creating this URL.
     */
    public function createContentContainerUrl(UrlManager $manager, string $containerUrlPath, string $route, array $params);
}
