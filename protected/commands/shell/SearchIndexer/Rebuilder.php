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
 * Rebuilder command purges all search index files and rebuilds the whole index.
 *
 * @package humhub.commands.shell.SearchIndexer
 * @since 0.5
 */
class Rebuilder extends HConsoleCommand {

    public function run($args) {

        $this->printHeader('Rebuild Search Index\n');

        print "Deleting old index files: ";
        HSearch::getInstance()->flushIndex();
        print " done!\n";

        print "Rebuilding index: ";
        HSearch::getInstance()->rebuild();
        print " done!\n";

        print "\n";
    }

}
