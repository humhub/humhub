<?php

use humhub\widgets\Icon;

?>
<div class="installer">
    <div class="prerequisites-list">
        <ul>
            <?php foreach ($checks as $check): ?>
                <li>

                    <?php if ($check['state'] == 'OK') : ?>
                        <?= Icon::get('circle-check')->class('check-ok animated bounceIn') ?>
                    <?php elseif ($check['state'] == 'WARNING') : ?>
                        <?= Icon::get('alert-triangle')->class('check-warning animated swing') ?>
                    <?php else : ?>
                        <?= Icon::get('circle-minus')->class('check-error animated wobble') ?>
                    <?php endif; ?>

                    <strong><?= $check['title']; ?></strong>

                    <?php if (isset($check['hint'])): ?>
                        <span>(Hint: <?= $check['hint']; ?>)</span>
                    <?php endif; ?>

                </li>
            <?php endforeach; ?>

        </ul>
    </div>
</div>