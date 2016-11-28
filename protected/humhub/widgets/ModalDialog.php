<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humhub\widgets;

/**
 * Used for rendering a modal header
 *
 * @author buddha
 */
class ModalDialog extends Modal
{
    
    public $dialogContent;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if(!$this->body && !$this->footer) {
            ob_start();
            ob_implicit_flush(false);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if(!$this->body && !$this->footer) {
            $this->dialogContent = ob_get_clean();
        }
        
        //The x close button is rendered by default either if forced by showClose or a headertext is given
        $showClose = ($this->showClose != null) ? $this->showClose : ($this->header != null);
        
        $dialogClass = 'modal-dialog';
        $dialogClass .= ($this->size != null) ? ' modal-dialog-'.$this->size : '';
        $dialogClass .= ($this->animation != null) ? ' animated '.$this->animation : '';
        
        $bodyClass = 'modal-body';
        $bodyClass .= ($this->centerText) ? ' text-center' : '';

        $this->initialLoader = ($this->initialLoader ==! null) ? $this->initialLoader : ($this->body === null);
       
        $modalData = '';
        $modalData .= !$this->backdrop ? 'data-backdrop="static"' : '';
        $modalData .= !$this->keyboard ? 'data-keyboard="false"' : '';
        $modalData .= $this->show ? 'data-show="true"' : '';
        
        return $this->render('modalDialog', [
            'header' => $this->header,
            'dialogContent' => $this->dialogContent,
            'body' => $this->body,
            'bodyClass' => $bodyClass,
            'footer' => $this->footer,
            'dialogClass' => $dialogClass,
            'initialLoader' => $this->initialLoader,
            'showClose' => $showClose
        ]);
    }

}
