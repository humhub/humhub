<?php

echo \humhub\widgets\Modal::widget([
    'id' => 'globalModalConfirm',
    'size' => 'extra-small',
    'centerText' => true,
    'animation' => 'pulse',
    'footer' => '<button data-modal-cancel data-modal-close class="btn btn-primary"></button><button data-modal-confirm data-modal-close class="btn btn-primary"></button>'
]);

?>