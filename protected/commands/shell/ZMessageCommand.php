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
Yii::import('application.vendors.yii.cli.commands.shell.*');

/**
 * ZMessageCommand extends Yiis MessageCommand with better module support.
 *
 * - Also add module messages into module/messages dir.
 *
 * @package humhub.commands
 * @since 0.5
 */
class ZMessageCommand extends MessageCommand {

    /**
     * Execute the action.
     * @param array command line parameters specific for this command
     */
    public function run($args) {

        if (!isset($args[0]))
            $this->usageError('the configuration file is not specified.');
        if (!is_file($args[0]))
            $this->usageError("the configuration file {$args[0]} does not exist.");

        $config = require_once($args[0]);
        $translator = 'Yii::t';
        extract($config);

        if (!isset($sourcePath, $messagePath, $languages))
            $this->usageError('The configuration file must specify "sourcePath", "messagePath" and "languages".');
        if (!is_dir($sourcePath))
            $this->usageError("The source path $sourcePath is not a valid directory.");
        if (!is_dir($messagePath))
            $this->usageError("The message path $messagePath is not a valid directory.");
        if (empty($languages))
            $this->usageError("Languages cannot be empty.");

        if (!isset($overwrite))
            $overwrite = false;

        if (!isset($removeOld))
            $removeOld = false;

        if (!isset($sort))
            $sort = false;

        $options = array();
        if (isset($fileTypes))
            $options['fileTypes'] = $fileTypes;
        if (isset($exclude))
            $options['exclude'] = $exclude;
        $files = CFileHelper::findFiles(realpath($sourcePath), $options);

        $messages = array();
        foreach ($files as $file)
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $translator));


        foreach ($messages as $category => $msgs) {

            $dir = "";
            if (($pos = strpos($category, '.')) !== false) {
                $parts = explode(".", $category);
                $className = $parts[0];

                if (!$this->isModuleClassDefined($className)) {
                    print "Skip not enabled module: " . $className . "\n";
                    continue;
                }

                // Get base Path of Module
                $class = new ReflectionClass($className);
                $dir = dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR;

                $category = $parts[1];
            } else {
                $dir = $messagePath . DIRECTORY_SEPARATOR;
            }

            // Skip when there is no message dir
            if (!is_dir($dir)) {
                continue;
            }

            foreach ($languages as $language) {
                $messageDir = $dir . DIRECTORY_SEPARATOR . $language;
                if (!is_dir($messageDir))
                    @mkdir($messageDir);

                $msgs = array_values(array_unique($msgs));
                $this->generateMessageFile($msgs, $messageDir . DIRECTORY_SEPARATOR . $category . '.php', $overwrite, $removeOld, $sort);
            }
        }
    }

    protected function extractMessages($fileName, $translator) {
        echo "Extracting messages from $fileName...\n";
        $subject = file_get_contents($fileName);
        $messages = array();
        if (!is_array($translator))
            $translator = array($translator);

        foreach ($translator as $currentTranslator) {
            $n = preg_match_all('/\b' . $currentTranslator . '\s*\(\s*(\'[\w.]*?(?<!\.)\'|"[\w.]*?(?<!\.)")\s*,\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")\s*[,\)]/s', $subject, $matches, PREG_SET_ORDER);

            for ($i = 0; $i < $n; ++$i) {
                $category = substr($matches[$i][1], 1, -1);
                $message = $matches[$i][2];
                $messages[$category][] = eval("return $message;");  // use eval to eliminate quote escape
            }
        }
        return $messages;
    }

    /**
     * Checks if given module is registered to the yii application.
     * This is needed to prevent class autoload errrors when module/class is not enabled.
     *
     * @param type $className
     * @return boolean
     */
    public function isModuleClassDefined($className) {
        foreach (Yii::app()->modules as $mod) {
            if (strpos($mod['class'], $className) !== false) {
                return true;
            }
        }
        return false;
    }

}
