<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\models\forms\AdminDeleteContentForm;

/**
 * Admin Delete Modal for Wall Entries
 *
 * This widget will be shown when admin deletes someone's content
 *
 */
class AdminDeleteModal extends \yii\base\Widget
{
    /**
     * @var AdminDeleteContentForm
     */
    public $model = null;

    /**
     * Executes the widget.
     */
    public function run()
    {
        return $this->render('adminDeleteModal', [
            'model' => $this->model,
        ]);
    }
}
