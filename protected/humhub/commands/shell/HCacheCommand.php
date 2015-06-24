<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * HCacheCommand provides console access to caching functions.
 *
 * @package humhub.commands.shell
 * @since 0.5
 */
class HCacheCommand extends CConsoleCommand {

    public function actionIndex() {
        print $this->getHelp();
    }

    /**
     * Flushes all application cache data
     */
    public function actionFlush() {
        Yii::app()->cache->flush();
        ModuleManager::flushCache();

        if (Yii::app()->cache instanceof CApcCache) {
            print "Warning: Could not flush APC Cache! - Restart Webserver!\n";
        }

        print "All application caches flushed!\n";
    }

    /**
     * Disables application cache
     */
    public function actionDisable() {
        HSetting::Set('type', 'CDummyCache', 'cache');
        print "Application Cache disabled!\n";
    }

    public function getHelp() {
        return <<<EOD
USAGE
  yiic cache [action]

DESCRIPTION
  This command provides access to the caching backend.

EXAMPLES
 * yiic cache flush
   Flushes all application caches.

 * yiic cache disable
   Disables application cache.

EOD;
    }

}
