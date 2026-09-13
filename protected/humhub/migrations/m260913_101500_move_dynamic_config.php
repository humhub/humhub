<?php

use humhub\services\ConfigDirectoryService;
use yii\db\Migration;

/**
 * Moves the dynamic configuration into the configuration directory in the installation root.
 *
 * It is the one configuration file HumHub writes itself, so leaving it to the administrator would
 * mean the installer and every later migration keep writing into the directory this release calls
 * legacy. Everything else in there is hand-written and stays where it is; Administration ->
 * Information -> Prerequisites reports what is left.
 *
 * Nothing is ever overwritten: a target that already exists, a directory that cannot be written to
 * or a rename that fails leaves the file where it is, and the installation keeps running off it.
 *
 * @see ConfigDirectoryService
 */
class m260913_101500_move_dynamic_config extends Migration
{
    public function safeUp()
    {
        // The test suite runs against its own dynamic configuration and must not move files around.
        if (YII_ENV_TEST) {
            return;
        }

        $configDirectory = ConfigDirectoryService::instance();

        $source = $configDirectory->getLegacyPath() . '/' . ConfigDirectoryService::DYNAMIC_CONFIG_FILE;
        $target = $configDirectory->getPath() . '/' . ConfigDirectoryService::DYNAMIC_CONFIG_FILE;

        if (!is_file($source)) {
            return;
        }

        if (file_exists($target)) {
            $this->report('Left ' . $source . ' in place: ' . $target . ' already exists. Merge the two by hand.');

            return;
        }

        if (!is_dir($configDirectory->getPath())) {
            $this->report('Could not move ' . $source . ': the directory ' . $configDirectory->getPath() . ' does not exist.');

            return;
        }

        // Renaming unlinks the source, which needs write permission on the directory holding it.
        if (!is_writable($configDirectory->getPath()) || !is_writable($configDirectory->getLegacyPath())) {
            $this->report('Could not move ' . $source . ' to ' . $target . ': not writable by the PHP process.');

            return;
        }

        if (!@rename($source, $target)) {
            $this->report('Could not move ' . $source . ' to ' . $target . '.');

            return;
        }

        foreach ([$source, $target] as $file) {
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($file, true);
            }
        }

        echo "    > moved the dynamic configuration to " . $target . "\n";
    }

    public function safeDown()
    {
        echo "m260913_101500_move_dynamic_config cannot be reverted.\n";

        return false;
    }

    /**
     * The administrator has to act on these, so they go to the console the update runs in as well
     * as to the log.
     */
    private function report(string $message): void
    {
        echo '    > ' . $message . "\n";
        Yii::error($message, 'migration');
    }
}
