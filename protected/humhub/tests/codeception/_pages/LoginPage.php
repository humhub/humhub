<?php

namespace tests\codeception\_pages;

use tests\codeception\_support\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class LoginPage extends BasePage
{
    public $route = 'user/auth/login';

    public $registerRoute = 'user/auth/register';

    /**
     * GET params passed to {@see openBy()}, replayed by {@see login()} when it
     * needs to navigate back to a fresh Step 1 — keeps `?maintenanceAdmin=1`
     * (and any future variants) sticky across the helper's internal navigations.
     */
    private array $openParams = [];

    /**
     * @inheritdoc
     */
    public static function openBy($I, $params = [])
    {
        /** @var static $page */
        $page = parent::openBy($I, $params);
        $page->openParams = $params;

        return $page;
    }

    /**
     * Two-step sign-in: Step 1 (username/email + Continue) followed by Step 2
     * (password). When $password is empty the helper stops on Step 1 so callers
     * can assert against the Step 1 validation error.
     *
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        // Always start from Step 1 — callers may chain login() after a previous
        // assertion that left the browser on Step 2 (e.g. an empty-password
        // validation case), where Login[username] no longer exists. Replay the
        // openBy() params so that `?maintenanceAdmin=1` (and similar sticky
        // flags) stay in effect across the helper's internal navigations.
        $this->actor->amOnPage($this->getUrl($this->openParams));
        $this->actor->fillField('Login[username]', $username);
        $this->actor->click('#continue-button');

        if ($password === '') {
            // Stay on Step 1; either the username was also empty (validation
            // error visible) or the caller is asserting Step-1 behaviour.
            return;
        }

        // "Please sign in" is the heading on both steps, so we can't wait on it
        // to detect the transition — wait on Step 2's password input instead.
        if (method_exists($this->actor, 'waitForElement')) {
            $this->actor->waitForElement('#login_password');
        }
        $this->actor->fillField('Login[password]', $password);
        $this->actor->click('#login-button');
    }

    public function selfInvite($email)
    {
        $this->actor->amOnRoute('/' . $this->registerRoute);
        $this->actor->fillField('Invite[email]', $email);
        $this->actor->submitForm('#invite-form', ['Invite' => [
            'email' => $email,
        ]]);
    }

    /**
     * Open the login page and advance to Step 2 (password) for the given user.
     * Useful for tests that need to interact with elements only present on the
     * password screen (e.g. the "Forgot your password?" link).
     */
    public function openPasswordStep($username): void
    {
        $this->actor->amOnPage($this->getUrl($this->openParams));
        $this->actor->fillField('Login[username]', $username);
        $this->actor->click('#continue-button');
        if (method_exists($this->actor, 'waitForElement')) {
            $this->actor->waitForElement('#login_password');
        }
    }
}
