# Module Migration — Version 1.18

## Version 1.18.1

### New
- `AltchaCaptchaInput::$showOnFocusElement` and `YiiCaptchaInput::$showOnFocusElement` allowing hiding the Captcha input until a form field is focused
- HTML classes in views using the `user/views/layouts/main.php` layout to set the container width ([see PR #8054](https://github.com/humhub/humhub/pull/8054)): `.container-registration`, `.container-login` and `.container-password`

### Changed
- `humhub\widgets\bootstrap\Button`, `humhub\widgets\bootstrap\Link`, `humhub\widgets\bootstrap\Badge`, `humhub\modules\ui\menu\widgets\DropdownMenu` labels are now HTML encoded by default. Set `encodeLabel` to `false` if already encoded.

## Version 1.18

Updated minimum required PHP version to 8.2.

### New
- `\humhub\components\captcha\CaptchaInterface`
- `\humhub\components\captcha\AltchaCaptcha`
- `\humhub\components\captcha\AltchaCaptchaInput`
- `\humhub\components\captcha\AltchaCaptchaValidator`
- `\humhub\components\captcha\AltchaCaptchaAction`
- `\humhub\components\captcha\AltchaCaptchaAsset`
- `\humhub\components\captcha\YiiCaptcha`
- `\humhub\components\captcha\YiiCaptchaInput`
- `\humhub\components\captcha\YiiCaptchaValidator`
- `Yii::$app->captcha` component
- `\humhub\assets\DriverJsAsset` (driver.js)
- `\humhub\modules\tour\Module::tourConfigFiles` (allows customizing the introduction tour)
- `\humhub\modules\tour\Module::driverJsOptions`
- `\humhub\widgets\mails\MailHeaderImage` widget for displaying a header image in emails
- `humhub\helpers\MailStyleHelper` to get Sass variable values in email templates
- `\humhub\components\Theme::CORE_THEME_NAME`
- `\humhub\modules\user\models\forms\EVENT_AFTER_SET_FORM`
- `\humhub\components\Migration::safeAlterColumn()`

### Deprecated
- `\humhub\components\Application::isInstalled()` use `\humhub\components\Application::hasState()` instead
- `\humhub\components\Application::isDatabaseInstalled()` use `\humhub\components\Application::hasState()` instead
- `\humhub\components\Application::setInstalled()` use `\humhub\components\Application::setState()` instead
- `humhub\modules\ui\mail\DefaultMailStyle` use `humhub\helpers\MailStyleHelper` instead

### Changed

- The following Mailer settings keys have been renamed to work with `.env`:

| Old Key                          | New Key                        |
|----------------------------------|--------------------------------|
| `mailer.transportType`           | `mailerTransportType`          |
| `mailer.dsn`                     | `mailerDsn`                    |
| `mailer.hostname`                | `mailerHostname`               |
| `mailer.username`                | `mailerUsername`               |
| `mailer.password`                | `mailerPassword`               |
| `mailer.useSmtps`                | `mailerUseSmtps`               |
| `mailer.port`                    | `mailerPort`                   |
| `mailer.encryption`              | `mailerEncryption`             |
| `mailer.allowSelfSignedCerts`    | `mailerAllowSelfSignedCerts`   |
| `mailer.systemEmailAddress`      | `mailerSystemEmailAddress`     |
| `mailer.systemEmailName`         | `mailerSystemEmailName`        |
| `mailer.systemEmailReplyTo`      | `mailerSystemEmailReplyTo`     |
| `proxy.*`                        | `proxy*`     |

- `tour` module:
  - Library [bootstrap-tour](https://github.com/sorich87/bootstrap-tour/) replaced Wwith [driver.js](https://driverjs.com/)
  - Widget view files rewritten

### Removed deprecations
- Widget class `\humhub\widgets\DataSaved`, the related code `Yii::$app->getSession()->setFlash('data-saved', Yii::t('base', 'Saved'));` must be replaced with `$this->view->saved();` on controllers

### Module tests for Codeception v5
- Update the file `tests/codeception.yml`: `log: codeception/_output` => `output: codeception/_output`
- Update files `tests/codeception/*.suite.yml`: `class_name: *Tester` => `actor: *Tester`
- `$I->waitFor*('Text', null)` => `$I->waitFor*('Text', 10)`, the second param can be only integer for the methods:
  - `waitForText()`
  - `waitForElement()`
  - `waitForElementVisible()`
  - `waitForElementNotVisible()`
  - `waitForElementClickable()`
- Functional tests: `$I->amOnPage(['/some/page/url', 'id' => 1])` => `$I->amOnRoute('/some/page/url', ['id' => 1])`
