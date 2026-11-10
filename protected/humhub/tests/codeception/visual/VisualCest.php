<?php

namespace humhub\tests\codeception\visual;

use Codeception\Scenario;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverKeys;
use AcceptanceTester;
use humhub\helpers\ThemeHelper;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use RuntimeException;
use Yii;

/**
 * Takes the screenshots of the visual comparison of the CSS: the same pages are taken with the
 * stylesheets of two branches, `.github/scripts/visual-diff.py` compares them.
 *
 * Skipped unless `HUMHUB_VISUAL` names the folder (under `_output/visual/`) to write to. `HUMHUB_VISUAL_THEME` names
 * another theme to take them with, `HUMHUB_VISUAL_COLOR_MODE` Bootstrap's colour mode (`dark`, `[data-bs-theme]`).
 *
 * @see docs/develop/intro-testing.md
 */
class VisualCest
{
    private const DESKTOP = [1280, 900];
    private const MOBILE = [375, 812];

    // Pages that are long anyway are cut, so a changed height does not hide all other differences
    private const MAX_HEIGHT = 4000;

    // A post with all the Markdown the stylesheets style, and emojis
    private const MARKDOWN = "## Heading\n\nA paragraph with **bold**, *italic*, `inline code` and a [link](https://www.humhub.com).\n\n"
        . "- First item\n- Second item\n    - Nested item\n\n1. One\n2. Two\n\n> A quotation\n\n"
        . "```\nfunction example() {\n    return 42;\n}\n```\n\n| Name | Value |\n|---|---|\n| Alpha | 1 |\n| Beta | 2 |\n\n---\n\n"
        . "Emojis: 😀 👍🏽 ❤️ 👩‍💻 🇩🇪 1️⃣ 🫨\n\nThe end.";

    // Checkboxes, radios and switches in each state, put on top of the first panel body; the checked checkbox of the
    // first row is focused
    private const CHOICE_CONTROLS_JS = <<<'JS'
        const states = ['', 'checked', 'disabled', 'checked disabled'];
        const row = (type, extra) => '<div class="d-flex gap-3 mb-2">' + states.map(state =>
            '<div class="form-check' + (extra || '') + '"><input class="form-check-input" type="' + type + '" '
            + state + ' name="visual-' + type + (extra || '') + state.replace(' ', '') + '"'
            + (extra ? ' role="switch"' : '') + '><label class="form-check-label">' + (state || 'off') + '</label></div>'
        ).join('') + '<div class="form-check' + (extra || '') + '"><input class="form-check-input visual-focus" type="'
            + type + '" checked' + (extra ? ' role="switch"' : '') + '><label class="form-check-label">focused</label></div></div>';
        const box = document.createElement('div');
        box.className = 'p-3';
        box.innerHTML = row('checkbox') + row('radio') + row('checkbox', ' form-switch')
            + '<div class="checkbox"><label><input type="checkbox" checked> legacy</label></div>'
            + '<div class="content-form-body"><div class="contentForm_options"><div class="mb-3"><div class="checkbox">'
            + '<label><input type="checkbox" class="visual-focus" checked> post option</label></div></div></div></div>';
        (document.querySelector('#layout-content .panel-body, #layout-content .card-body')
            || document.querySelector('#layout-content')).prepend(box);
        box.querySelector('.visual-focus').focus();
        JS;

    // Things that differ between two runs whatever the CSS
    private const STABILIZE_CSS = '*, *::before, *::after { transition: none !important; animation: none !important;'
        . ' caret-color: transparent !important; } time, .time, #nprogress { visibility: hidden !important; }';

    private string $folder = '';

    private string $colorMode = '';

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        $this->folder = (string)getenv('HUMHUB_VISUAL');
        if ($this->folder === '' || !preg_match('/^[\w-]+$/', $this->folder)) {
            $scenario->skip('Set HUMHUB_VISUAL=<folder> to take the screenshots of the visual comparison');
        }

        // The Markdown post on top of the stream of the first space
        $user = User::findOne(['username' => 'Admin']);
        Yii::$app->user->switchIdentity($user);
        $post = new Post(Space::findOne(['id' => 1]), Content::VISIBILITY_PUBLIC, ['message' => self::MARKDOWN]);
        $post->save();
        // A fixed date instead of "just now", which changes the width of the entry's header
        $post->content->updateAttributes(['created_at' => '2020-01-15 10:00:00', 'updated_at' => '2020-01-15 10:00:00']);
        // Two comments and a reply below it
        $comments = [];
        foreach (['A first comment', 'A second comment'] as $message) {
            $comments[] = $comment = new Comment(['message' => $message, 'content_id' => $post->content->id]);
            $comment->save();
        }
        $reply = new Comment(['message' => 'A reply', 'content_id' => $post->content->id, 'parent_comment_id' => $comments[0]->id]);
        $reply->save();
        foreach ([...$comments, $reply] as $comment) {
            $comment->updateAttributes(['created_at' => '2020-01-15 11:00:00', 'updated_at' => '2020-01-15 11:00:00']);
        }
        Yii::$app->user->logout();

        $this->colorMode = (string)getenv('HUMHUB_VISUAL_COLOR_MODE');
        if ($this->colorMode !== '' && !preg_match('/^[a-z]+$/', $this->colorMode)) {
            throw new RuntimeException('Invalid HUMHUB_VISUAL_COLOR_MODE');
        }

        // The test server may have cached the settings from before the fixtures (e.g. the site name of the installer)
        Yii::$app->cache->flush();

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

        // The post form's editor: focused, with the mention list, the emoji picker and the emoji autocomplete
        $I->click('#contentForm_message .ProseMirror');
        $I->wait(1);
        $this->screenshot($I, 'editor-focused', false);
        $I->pressKey('#contentForm_message .ProseMirror', '@', 'a');
        $I->waitForElementVisible('.atwho-view', 10);
        $this->screenshot($I, 'editor-mention', false);
        $I->pressKey('#contentForm_message .ProseMirror', WebDriverKeys::ESCAPE);
        $I->click('#contentForm_message [title="Insert Emoji"]');
        $I->waitForElementVisible('.humhub-emoji-picker emoji-picker', 10);
        // The picker data is loaded on the first opening
        $I->wait(2);
        $this->screenshot($I, 'editor-emoji', false);
        $I->pressKey('.humhub-emoji-picker emoji-picker', WebDriverKeys::ESCAPE);
        $I->waitForElementNotVisible('.humhub-emoji-picker', 10);
        $I->pressKey('#contentForm_message .ProseMirror', ' ', ':', 's', 'm', 'i');
        $I->waitForElementVisible('.humhub-emoji-autocomplete', 10);
        $this->screenshot($I, 'editor-emoji-autocomplete', false);
        $I->pressKey('#contentForm_message .ProseMirror', WebDriverKeys::ESCAPE);

        // The comment form of the first entry
        $I->amOnSpace(1);
        $this->waitUntilLoaded($I);
        $I->click('[data-action-click="comment.toggleComment"]');
        $I->wait(1);
        $this->screenshot($I, 'comment-form');

        // The Markdown post, unfolded
        $I->amOnSpace(1);
        $this->waitUntilLoaded($I);
        $I->click('Read more');
        $I->wait(1);
        $this->screenshot($I, 'markdown-post');

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

        // Every state of the choice controls, placed into the page
        $I->amOnRoute(['/user/account/edit']);
        $this->waitUntilLoaded($I);
        $I->executeJS(self::CHOICE_CONTROLS_JS);
        $this->screenshot($I, 'choice-controls', false);
        // The focus on the switch, then on the option of the post form
        $I->executeJS('document.querySelectorAll(".visual-focus")[2].focus();');
        $this->screenshot($I, 'choice-controls-switch-focus', false);
        $I->executeJS('document.querySelectorAll(".visual-focus")[3].focus();');
        $this->screenshot($I, 'choice-controls-option-focus', false);

        $I->amOnRoute(['/user/account/edit']);
        $this->waitUntilLoaded($I);

        // The date picker of the birthday, on a fixed month (no "today")
        $I->executeJS('$("#profile-birthday").datepicker("setDate", new Date(2020, 0, 15));');
        $I->click('#profile-birthday');
        $I->waitForElementVisible('#ui-datepicker-div', 10);
        $I->moveMouseOver('#ui-datepicker-div tbody tr:nth-child(2) td:nth-child(3) a');
        $this->screenshot($I, 'datepicker', false);

        $I->amOnRoute(['/admin/user/list']);
        $this->screenshot($I, 'admin-users');

        $I->amOnRoute(['/admin/appearance/theme']);
        $this->screenshot($I, 'admin-appearance');

        $I->amOnRoute(['/admin/module/list']);
        $this->screenshot($I, 'admin-modules');

        $I->amOnRoute(['/admin/user-profile']);
        $this->screenshot($I, 'admin-profile-fields');

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

        $I->amOnProfile();
        $this->screenshot($I, 'mobile-profile');

        // The menu of the account settings as a strip of entries, one hovered
        $I->amOnRoute(['/user/account/edit']);
        $this->waitUntilLoaded($I);
        $I->moveMouseOver('.section-menu .nav-link:not(.active)');
        $this->screenshot($I, 'mobile-account');

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
        if ($this->colorMode !== '') {
            $I->executeJS('document.documentElement.setAttribute("data-bs-theme", ' . json_encode($this->colorMode) . ');');
        }
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
