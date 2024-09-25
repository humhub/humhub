<?php

namespace humhub\widgets;

/**
 * This Widget can be used to finish a modal process.
 * If the frontend requires a modal response, this widget will close the global modal
 * and show an status message.
 *
 * @deprecated since 1.18.0 use [[humhub\widgets\modal\ModalClose]] instead
 * @author buddha
 */
class ModalClose extends modal\ModalClose
{
}
