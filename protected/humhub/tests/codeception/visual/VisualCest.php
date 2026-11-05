<?php

namespace humhub\tests\codeception\visual;

use Codeception\Scenario;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use AcceptanceTester;
use humhub\helpers\ThemeHelper;
use RuntimeException;

/**
 * Takes the screenshots of the visual comparison of the CSS: the same pages are taken with the
 * stylesheets of two branches, `.github/scripts/visual-diff.py` compares them.
 *
 * Skipped unless `HUMHUB_VISUAL` names the folder (under `_output/visual/`) to write to. `HUMHUB_VISUAL_THEME` names
 * another theme to take them with.
 *
 * @see docs/develop/intro-testing.md
 */
class VisualCest
{
    private const DESKTOP = [1280, 900];
    private const MOBILE = [375, 812];

    // Pages that are long anyway are cut, so a changed height does not hide all other differences
    private const MAX_HEIGHT = 4000;

    // Things that differ between two runs whatever the CSS
    private const STABILIZE_CSS = '*, *::before, *::after { transition: none !important; animation: none !important;'
        . ' caret-color: transparent !important; } time, .time, #nprogress { visibility: hidden !important; }';

    private string $folder = '';

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        $this->folder = (string)getenv('HUMHUB_VISUAL');
        if ($this->folder === '' || !preg_match('/^[\w-]+$/', $this->folder)) {
            $scenario->skip('Set HUMHUB_VISUAL=<folder> to take the screenshots of the visual comparison');
        }

        // Another theme than the core one, e.g. of a module (enabled before); after the fixtures, which reset the
        // settings
        $themeName = (string)getenv('HUMHUB_VISUAL_THEME');
        if ($themeName !== '') {
            $theme = ThemeHelper::getThemeByName($themeName);
            if ($theme === null) {
                throw new RuntimeException('Theme "' . $themeName . '" not found, is its module enabled?');
            }
            $theme->activate();
        }
    }

    public function takeScreenshots(AcceptanceTester $I): void
    {
        $this->setViewport($I, self::DESKTOP);

        $I->amOnRoute(['/user/auth/login']);
        $this->screenshot($I, 'login');

        $I->amAdmin();

        $I->amOnDashboard();
        $this->screenshot($I, 'dashboard');

        $I->amOnSpace(1);
        $this->screenshot($I, 'space-stream');

        $I->amOnSpace(1, '/space/space/about');
        $this->screenshot($I, 'space-about');

        $I->amOnProfile();
        $this->screenshot($I, 'profile');

        $I->amOnRoute(['/user/people']);
        $this->screenshot($I, 'people');

        $I->amOnRoute(['/space/spaces']);
        $this->screenshot($I, 'spaces');

        $I->amOnRoute(['/notification/overview']);
        $this->screenshot($I, 'notifications');

        $I->amOnRoute(['/user/account/edit']);
        $this->screenshot($I, 'account-profile');

        $I->amOnRoute(['/admin/user/list']);
        $this->screenshot($I, 'admin-users');

        $I->amOnRoute(['/admin/setting/design']);
        $this->screenshot($I, 'admin-appearance');

        $I->amOnRoute(['/admin/module/list']);
        $this->screenshot($I, 'admin-modules');

        // Opened menus of the top bar
        $I->amOnDashboard();
        $this->menuScreenshot($I, '#icon-notifications', 'notifications-dropdown');

        $I->amOnDashboard();
        $this->menuScreenshot($I, '#space-menu', 'space-menu');

        $I->amOnDashboard();
        $this->menuScreenshot($I, '#account-dropdown-link', 'account-menu');

        $this->setViewport($I, self::MOBILE);

        $I->amOnDashboard();
        $this->screenshot($I, 'mobile-dashboard');

        $I->amOnSpace(1);
        $this->screenshot($I, 'mobile-space-stream');

        $I->amOnDashboard();
        $this->menuScreenshot($I, '#icon-notifications', 'mobile-notifications-dropdown');

        $I->amOnDashboard();
        $this->menuScreenshot($I, '#space-menu', 'mobile-space-menu');
    }

    /**
     * Takes the viewport with an opened menu, which a resize would close. A menu the theme does not have is left out.
     */
    private function menuScreenshot(AcceptanceTester $I, string $selector, string $name): void
    {
        $this->waitUntilLoaded($I);
        if (!$I->executeJS('return document.querySelector(' . json_encode($selector) . ') !== null;')) {
            return;
        }
        $I->jsClick($selector);
        $I->wait(1);
        $this->screenshot($I, $name, false);
    }

    private function setViewport(AcceptanceTester $I, array $viewport): void
    {
        $I->resizeWindow($viewport[0], $viewport[1]);
        // The window is larger than its viewport by the browser's own bars
        $bars = (int)$I->executeJS('return window.outerHeight - window.innerHeight;');
        $I->resizeWindow($viewport[0], $viewport[1] + $bars);
    }

    private function waitUntilLoaded(AcceptanceTester $I): void
    {
        $I->waitForJS('return document.readyState === "complete" && (!window.jQuery || jQuery.active === 0)'
            . ' && !document.querySelector(\'#nprogress, [aria-busy="true"]\');', 30);
    }

    /**
     * @param bool $fullPage whether to take the whole page (up to [[MAX_HEIGHT]]) or the viewport only
     */
    private function screenshot(AcceptanceTester $I, string $name, bool $fullPage = true): void
    {
        $this->waitUntilLoaded($I);
        $I->executeJS('if (!document.getElementById("visual-test-style")) {'
            . ' const style = document.createElement("style"); style.id = "visual-test-style";'
            . ' style.textContent = ' . json_encode(self::STABILIZE_CSS) . '; document.head.appendChild(style); }');
        // Lazy content (stream entries, images, web fonts) settles
        $I->wait(1);
        $this->waitUntilLoaded($I);

        $directory = codecept_output_dir() . 'visual/' . $this->folder;
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $file = $directory . '/' . $name . '.png';

        // A full page by Chrome's DevTools: a larger window would first have to lay out the page anew
        $I->executeInSelenium(function (RemoteWebDriver $webDriver) use ($file, $fullPage) {
            $parameters = ['format' => 'png'];
            if ($fullPage) {
                [$width, $height] = $webDriver->executeScript('return [document.documentElement.clientWidth,'
                    . ' Math.max(document.documentElement.scrollHeight, document.body.scrollHeight)];');
                $parameters['captureBeyondViewport'] = true;
                $parameters['clip'] = ['x' => 0, 'y' => 0, 'width' => $width,
                    'height' => min($height, self::MAX_HEIGHT), 'scale' => 1];
            }
            $result = (new ChromeDevToolsDriver($webDriver))->execute('Page.captureScreenshot', $parameters);
            file_put_contents($file, base64_decode($result['data']));
        });
    }
}
