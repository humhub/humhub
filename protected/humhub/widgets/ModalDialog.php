<?php
namespace humhub\widgets;

/**
 * Used for rendering a modal header
 *
 * @author buddha
 */
class ModalDialog extends Modal
{
    
    public $dialogContent;

    private $dialogClass;

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

        $bodyClass = 'modal-body';
        $bodyClass .= ($this->centerText) ? ' text-center' : '';

        $this->initialLoader = ($this->initialLoader ==! null) ? $this->initialLoader : ($this->body === null);
       
        $modalData = '';
        $modalData .= !$this->closable || !$this->backdrop ? 'data-backdrop="static"' : '';
        $modalData .= !$this->closable || !$this->keyboard ? 'data-keyboard="false"' : '';
        $modalData .= $this->show ? 'data-show="true"' : '';
        
        return $this->render('modalDialog', [
            'header' => $this->header,
            'options' => $this->getOptions(),
            'dialogContent' => $this->dialogContent,
            'body' => $this->body,
            'bodyClass' => $bodyClass,
            'footer' => $this->footer,
            'initialLoader' => $this->initialLoader,
            'showClose' => $showClose
        ]);
    }

    public function getAttributes()
    {
        $dialogClass = 'modal-dialog';
        $dialogClass .= ($this->size != null) ? ' modal-dialog-'.$this->size : '';
        $dialogClass .= ($this->animation != null) ? ' animated '.$this->animation : '';

        return [
            'class' => $dialogClass
        ];
    }
    public function getData()
    {
        return [
            'backdrop' => (!$this->closable || $this->backdrop === false) ? "static" : $this->backdrop,
            'keyboard' => (!$this->closable || !$this->keyboard) ? "false" : 'true',
        ];
    }

}
