<?php

namespace user;

use Codeception\Lib\Friend;
use humhub\modules\user\models\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \AcceptanceTester
{
    use _generated\AcceptanceTesterActions;

    /**
     * Define custom actions here
     */

    public function impersonateUser($userName)
    {
        $this->clickAccountDropDown();
        $this->click('Administration');
        $this->expectTo('see the users list');

        $user = User::findOne(['username' => $userName]);
        $this->waitForText('User administration');
        $this->jsClick('tr[data-key=' . $user->id . '] div.dropdown button');
        $this->waitForText('Impersonate');
        $this->click('Impersonate', '.dropdown-menu.show');
        $this->acceptPopup();
    }

    public function stopImpersonation()
    {
        $this->clickAccountDropDown();
        $this->click('Stop impersonation');
    }

    public function createProfileField(string $title, string $type, $options = [])
    {
        $options = array_merge([
            'categoryTitle' => 'Test fields',
            'internalName' => preg_replace('/[^a-z0-9]+/i', '_', strtolower($title)),
            'required' => false,
            'visible' => false,
            'showAtRegistration' => false,
            'editable' => false,
            'searchable' => false,
            'checkboxlist-options' => '',
        ], $options);

        $this->waitForText('Manage profile attributes');
        $this->waitForText($options['categoryTitle']);
        $this->click($options['categoryTitle']);
        $this->waitForText('Add new field', 10, '.tab-pane.active');
        $this->click('Add new field', '.tab-pane.active');
        $this->waitForText('Create new profile field');
        $this->fillField('#profilefield-internal_name', $options['internalName']);
        $this->fillField('#profilefield-title', $title);
        $this->selectOption('#profilefield-field_type_class', $type);

        $this->scrollToBottom();
        $this->wait(1);

        if ($options['required']) {
            $this->checkOption('#profilefield-required');
        }

        if ($options['visible']) {
            $this->checkOption('#profilefield-visible');
        }

        if ($options['showAtRegistration']) {
            $this->checkOption('#profilefield-show_at_registration');
        }

        if ($options['editable']) {
            $this->checkOption('#profilefield-editable');
        }

        if ($options['searchable']) {
            $this->checkOption('#profilefield-searchable');
        }

        if ($options['checkboxlist-options']) {
            $this->fillField('#checkboxlist-options', $options['checkboxlist-options']);
        }

        $this->scrollToBottom();
        $this->wait(1);
        $this->click('Save');
        $this->seeSuccess();
    }
}
