<?php

namespace humhub\tests\codeception\a11y;

use AcceptanceTester;
use Codeception\Scenario;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use humhub\helpers\ThemeHelper;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Yii;

/**
 * Checks a fixed set of pages with axe-core (npm dev dependency) against WCAG 2.2 A/AA, each in the light and the
 * dark colour mode, with and without high contrast. Writes `_output/a11y/report.json` and `summary.md` (also to the
 * job summary of GitHub Actions), new violations apart from the known ones of `known.php`.
 *
 * Skipped unless `HUMHUB_A11Y` is `report` (never fails) or `fail` (fails on new violations).
 * `HUMHUB_A11Y_PAGES` adds pages (comma-separated paths, e.g. of a module), `HUMHUB_A11Y_THEME` names another theme.
 *
 * @see docs/develop/intro-testing.md
 */
class A11yCest
{
    // Colour mode (`data-bs-theme`) and high contrast (`data-hh-contrast`) of each pass over a page
    private const MODES = [
        'light' => ['light', false],
        'dark' => ['dark', false],
        'light-hc' => ['light', true],
        'dark-hc' => ['dark', true],
    ];

    private const TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];

    private const DESKTOP = [1280, 900];
    private const MOBILE = [375, 812];

    // The final colours, not those of a transition; nothing else is hidden or changed
    private const FREEZE_CSS = '*, *::before, *::after { transition: none !important; animation: none !important; }';

    private const RUN_JS = <<<'JS'
        const done = arguments[arguments.length - 1];
        axe.run(document, { runOnly: { type: 'tag', values: arguments[0] }, resultTypes: ['violations'] })
            .then((result) => done(JSON.stringify({
                version: axe.version,
                violations: result.violations.map((violation) => ({
                    rule: violation.id,
                    impact: violation.impact,
                    help: violation.help,
                    helpUrl: violation.helpUrl.replace(/\?.*$/, ''),
                    nodes: violation.nodes.map((node) => ({
                        target: node.target.join(' '),
                        summary: (node.failureSummary || '').split('\n').slice(1).join(' ').trim(),
                    })),
                })),
            })))
            .catch((error) => done(JSON.stringify({ error: String(error) })));
        JS;

    private string $mode = '';

    /**
     * @var string[] paths of further pages
     */
    private array $pages = [];

    /**
     * @var array[] the violations found, by rule and element
     */
    private array $found = [];

    private string $version = '';

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        $this->mode = (string)getenv('HUMHUB_A11Y');
        if (!in_array($this->mode, ['report', 'fail'], true)) {
            $scenario->skip('Set HUMHUB_A11Y=report or HUMHUB_A11Y=fail to run the accessibility checks');
        }

        if (!is_file(self::axeFile())) {
            throw new RuntimeException('axe-core is missing, run `npm install`');
        }

        $pages = trim((string)getenv('HUMHUB_A11Y_PAGES'));
        foreach ($pages === '' ? [] : explode(',', $pages) as $page) {
            $page = trim($page);
            if (!preg_match('#^/[\w\-./?=&%]*$#', $page)) {
                throw new RuntimeException('Invalid path in HUMHUB_A11Y_PAGES: ' . $page);
            }
            $this->pages[] = $page;
        }

        // The test server may have cached the settings from before the fixtures (e.g. the site name of the installer)
        Yii::$app->cache->flush();

        // Another theme than the core one, e.g. of a module (enabled before); after the fixtures, which reset the
        // settings
        $themeName = (string)getenv('HUMHUB_A11Y_THEME');
        if ($themeName !== '') {
            $theme = ThemeHelper::getThemeByName($themeName);
            if ($theme === null) {
                throw new RuntimeException('Theme "' . $themeName . '" not found, is its module enabled?');
            }
            $theme->activate();
        }
    }

    public function checkPages(AcceptanceTester $I): void
    {
        $I->executeInSelenium(function (RemoteWebDriver $webDriver) {
            $webDriver->manage()->timeouts()->setScriptTimeout(120);
        });
        $this->setViewport($I, self::DESKTOP);

        $I->amOnRoute(['/user/auth/login']);
        $this->check($I, 'login');

        $I->amAdmin();

        $I->amOnDashboard();
        $this->check($I, 'dashboard');

        $I->amOnSpace(1);
        $this->check($I, 'space-stream');

        $I->amOnSpace(1, '/space/space/about');
        $this->check($I, 'space-about');

        $I->amOnProfile();
        $this->check($I, 'profile');

        foreach ([
            'people' => '/user/people',
            'spaces' => '/space/spaces',
            'notifications' => '/notification/overview',
            'account-profile' => '/user/account/edit',
            'account-settings' => '/user/account/edit-settings',
            'admin-users' => '/admin/user/list',
            'admin-appearance' => '/admin/appearance/theme',
            'admin-modules' => '/admin/module/list',
        ] as $name => $route) {
            $I->amOnRoute([$route]);
            $this->check($I, $name);
        }

        // Opened menus of the top bar; a menu the theme does not have is left out
        foreach (['notifications-menu' => '#icon-notifications', 'space-menu' => '#space-menu',
            'account-menu' => '#account-dropdown-link'] as $name => $selector) {
            $I->amOnDashboard();
            $this->waitUntilLoaded($I);
            if ($I->executeJS('return document.querySelector(' . json_encode($selector) . ') !== null;')) {
                $I->jsClick($selector);
                $I->wait(1);
                $this->check($I, $name);
            }
        }

        foreach ($this->pages as $page) {
            $I->amOnPage($page);
            $this->check($I, $page);
        }

        $this->setViewport($I, self::MOBILE);

        $I->amOnDashboard();
        $this->check($I, 'mobile-dashboard');

        $I->amOnSpace(1);
        $this->check($I, 'mobile-space-stream');

        $this->report();
    }

    /**
     * Runs axe on the current page in each mode.
     */
    private function check(AcceptanceTester $I, string $page): void
    {
        $this->waitUntilLoaded($I);
        $I->executeJS('if (!window.axe) { ' . file_get_contents(self::axeFile()) . ' }'
            . ' if (!document.getElementById("a11y-test-style")) { const style = document.createElement("style");'
            . ' style.id = "a11y-test-style"; style.textContent = ' . json_encode(self::FREEZE_CSS) . ';'
            . ' document.head.appendChild(style); }');

        foreach (self::MODES as $mode => [$colorMode, $highContrast]) {
            $I->executeJS('document.documentElement.setAttribute("data-bs-theme", ' . json_encode($colorMode) . ');'
                . ($highContrast
                    ? 'document.documentElement.setAttribute("data-hh-contrast", "more");'
                    : 'document.documentElement.removeAttribute("data-hh-contrast");'));
            $I->wait(0.3);

            $result = json_decode((string)$I->executeAsyncJS(self::RUN_JS, [self::TAGS]), true);
            if (!is_array($result) || isset($result['error'])) {
                throw new RuntimeException('axe failed on ' . $page . ': ' . ($result['error'] ?? 'no result'));
            }
            $this->version = $result['version'];

            foreach ($result['violations'] as $violation) {
                foreach ($violation['nodes'] as $node) {
                    $key = $violation['rule'] . '|' . $node['target'];
                    $this->found[$key] ??= [
                        'rule' => $violation['rule'],
                        'impact' => $violation['impact'],
                        'help' => $violation['help'],
                        'helpUrl' => $violation['helpUrl'],
                        'target' => $node['target'],
                        'summary' => $node['summary'],
                        'where' => [],
                    ];
                    $this->found[$key]['where'][$page][] = $mode;
                }
            }
        }
    }

    /**
     * Writes the report and the summary; fails with `HUMHUB_A11Y=fail` on violations that are not known.
     */
    private function report(): void
    {
        $known = require __DIR__ . '/known.php';
        $new = $old = [];
        foreach ($this->found as $violation) {
            $reason = null;
            $modes = array_unique(array_merge(...array_values($violation['where'])));
            foreach ($known as $entry) {
                if ($entry['rule'] === $violation['rule'] && str_contains($violation['target'], $entry['target'])
                    && !array_diff($modes, $entry['modes'] ?? array_keys(self::MODES))) {
                    $reason = $entry['reason'];
                    break;
                }
            }
            if ($reason === null) {
                $new[] = $violation;
            } else {
                $old[] = $violation + ['reason' => $reason];
            }
        }

        $title = 'Accessibility (axe-core ' . $this->version . ', WCAG 2.2 A/AA)';
        $summary = '## ' . $title . ': ' . count($new) . ' new, ' . count($old) . " known\n\n";
        if ($new) {
            $summary .= $this->table($new, false) . "\n";
        }
        if ($old) {
            $summary .= "<details><summary>Known (" . count($old) . ")</summary>\n\n" . $this->table($old, true)
                . "\n</details>\n";
        }
        $summary .= "\nPages, modes and the list of known violations: docs/develop/intro-testing.md\n";

        $directory = codecept_output_dir() . 'a11y';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($directory . '/report.json', json_encode(
            ['new' => $new, 'known' => $old],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
        file_put_contents($directory . '/summary.md', $summary);
        $stepSummary = (string)getenv('GITHUB_STEP_SUMMARY');
        if ($stepSummary !== '') {
            file_put_contents($stepSummary, $summary, FILE_APPEND);
        }

        if ($this->mode === 'fail' && $new) {
            Assert::fail(count($new) . ' new accessibility violations, see ' . $directory . '/summary.md');
        }
    }

    private function table(array $violations, bool $withReason): string
    {
        $rows = ['| Rule | Impact | Element | Where |' . ($withReason ? ' Known as |' : ''),
            '|---|---|---|---|' . ($withReason ? '---|' : '')];
        foreach ($violations as $violation) {
            $where = [];
            foreach ($violation['where'] as $page => $modes) {
                $modes = array_unique($modes);
                $where[] = $page . (count($modes) === count(self::MODES) ? '' : ' (' . implode(', ', $modes) . ')');
            }
            $target = mb_strimwidth($violation['target'], 0, 100, '…');
            $rows[] = '| [' . $violation['rule'] . '](' . $violation['helpUrl'] . ') | ' . $violation['impact']
                . ' | `' . str_replace(['|', '`'], ['\|', "'"], $target) . '` | ' . implode(', ', $where) . ' |'
                . ($withReason ? ' ' . str_replace('|', '\|', $violation['reason']) . ' |' : '');
        }

        return implode("\n", $rows) . "\n";
    }

    private static function axeFile(): string
    {
        return dirname(Yii::getAlias('@humhub'), 2) . '/node_modules/axe-core/axe.min.js';
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
}
