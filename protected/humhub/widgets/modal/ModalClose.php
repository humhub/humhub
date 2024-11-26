<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\modal;

use yii\base\Widget;

/**
 * This Widget can be used to finish a modal process.
 * If the frontend requires a modal response, this widget will close the global modal
 * and show a status message.
 *
 * Usage examples:
 *
 * ```
 * <?php
 * return ModalClose::widget(['saved' => true, 'reload' => true]);
 * ?>
 * ```
 */
class ModalClose extends Widget
{
    public $success;
    public $info;
    public $error;
    public $warn;
    public $saved;
    public $script;
    public $reload = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('modalClose', [
            'success' => $this->success,
            'info' => $this->info,
            'error' => $this->error,
            'warn' => $this->warn,
            'saved' => $this->saved,
            'script' => $this->script,
            'reload' => $this->reload,
        ]);
    }
}
