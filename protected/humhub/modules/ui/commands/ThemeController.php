<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\commands;

use humhub\modules\ui\view\helpers\ThemeHelper;
use Yii;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

/**
 * Theme Tools
 *
 * @since 1.3.3
 */
class ThemeController extends \yii\console\Controller
{

    /**
     * {@inheritdoc}
     */
    public $defaultAction = 'info';

    /**
     * Shows all available and active themes
     *
     * @return int
     * @throws \Exception
     */
    public function actionInfo()
    {
        $name = $this->ansiFormat(Yii::$app->view->theme->name, Console::FG_GREEN);

        $this->stdout("\nActive theme: {$name} \n", Console::BOLD);
        $this->stdout("\nInstalled themes:\n", Console::BOLD);

        $themes = [];
        foreach (ThemeHelper::getThemes() as $theme) {
            $parents = array_map(function ($t) {
                return $t->name;
            }, $theme->getParents());
            $themes[] = [$theme->name, implode(' > ', $parents), $theme->getBasePath()];
        }

        echo Table::widget([
            'headers' => ['Name:', 'Derived from:', 'Path:'],
            'rows' => $themes,
        ]);

        $this->stdout("\n");
        return ExitCode::OK;
    }

    /**
     * Switches the current theme
     *
     * @param string $name the theme name
     * @return int the exit code
     */
    public function actionSwitch($name)
    {
        $theme = ThemeHelper::getThemeByName($name);
        if ($theme === null) {
            $this->stderr("\nCould not find theme:\n", Console::BOLD);
            $this->stderr($name . "\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $theme->activate();

        $this->stdout("\nSuccessfully switched to theme: \n", Console::BOLD);
        $this->stdout(Yii::$app->view->theme->name. "\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

}
