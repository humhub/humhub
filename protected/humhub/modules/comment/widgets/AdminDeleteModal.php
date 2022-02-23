<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\forms\AdminDeleteCommentForm;

/**
 * Admin Delete Modal for Comments
 *
 * This widget will be shown when admin deletes someone's comment
 *
 */
class AdminDeleteModal extends \yii\base\Widget
{
    /**
     * @var AdminDeleteCommentForm
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
