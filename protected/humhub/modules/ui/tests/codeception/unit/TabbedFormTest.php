<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\form\widgets\FormTabs;
use tests\codeception\_support\HumHubDbTestCase;
use tests\codeception\models\TestTabbedFormModel;

class TabbedFormTest extends HumHubDbTestCase
{
    public function testTabbedForm()
    {
        // Create a model with not filled required fields
        $tabbedForm = new TestTabbedFormModel();
        $tabbedForm->email = 'email@test.local';
        $this->assertFalse($tabbedForm->validate());

        // Create a form with tabs
        $form = ActiveForm::begin(['action' => '/test']);
        $tabs = new FormTabs([
            'form' => $tabbedForm,
            'viewPath' => '@ui/tests/codeception/views',
            'params' => ['form' => $form, 'tabbedForm' => $tabbedForm],
        ]);
        $this->assertTrue($tabs->beforeRun());
        $result = $tabs->run();
        $tabs->afterRun($result);
        ActiveForm::end();

        // Check the second tab is active with error in the empty field Country ID
        $this->assertArrayNotHasKey('active', $tabs->items[0]);
        $this->assertArrayHasKey('active', $tabs->items[1]);
        $this->assertTrue($tabs->items[1]['active']); // <- Second tab is active

        $tabbedForm->countryId = 10;
        $this->assertTrue($tabbedForm->validate());
    }
}
