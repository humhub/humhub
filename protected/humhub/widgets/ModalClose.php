<?php

namespace humhub\widgets;

/**
 * This Widget can be used to finish a modal process.
 * If the frontend requires a modal response, this widget will close the global modal
 * and show an status message.
 * 
 *
 * @author buddha
 */
class ModalClose extends \yii\base\Widget
{
    public $success;
    public $info;
    public $error;
    public $warn;
    public $saved;
    
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
        ]);
    }
}
