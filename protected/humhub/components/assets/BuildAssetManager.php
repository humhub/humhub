<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\assets;

use Yii;

/**
 * AssetManager used by the `yii asset` build only.
 *
 * Many HumHub AssetBundles declare `sourcePath = '@humhub/resources'`. Publishing
 * them via Yii's default AssetManager would copy the entire resources tree into
 * `@humhub/resources/build/<hash>` — a subdirectory of the source, which Yii's
 * FileHelper rejects.
 *
 * For paths already inside `@humhub/resources` we therefore skip publishing and
 * return the source location itself; the files are reachable in place during
 * compression. External sources (npm/vendor) still publish normally into
 * `@humhub/resources/build/<hash>`, so the bundled CSS can reference them via
 * tree-relative URLs that survive the runtime publish to the assets mount.
 */
class BuildAssetManager extends \yii\web\AssetManager
{
    public function publish($path, $options = [])
    {
        $resolved = Yii::getAlias($path);
        $resourcesPath = Yii::getAlias('@humhub/resources');

        if ($resolved === $resourcesPath || str_starts_with($resolved, $resourcesPath . DIRECTORY_SEPARATOR)) {
            // The URL is only used by Yii's build-time machinery for CSS URL
            // adjustment, which operates on filesystem paths. The bundle's
            // runtime URL is set later by the live AssetManager when it
            // publishes `@humhub/resources` to the assets mount.
            return [$resolved, ''];
        }

        return parent::publish($path, $options);
    }
}
