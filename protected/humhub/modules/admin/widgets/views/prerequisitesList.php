<div class="installer">
    <div class="prerequisites-list">
        <ul>
            <?php foreach ($checks as $check): ?>
                <li>

                    <?php if ($check['state'] == 'OK') : ?>
                        <i class="fa fa-check-circle check-ok animated bounceIn"></i>
                    <?php elseif ($check['state'] == 'WARNING') : ?>
                        <i class="fa fa-exclamation-triangle check-warning animated swing"></i>
                    <?php else : ?>
                        <i class="fa fa-minus-circle check-error animated wobble"></i>
                    <?php endif; ?>

                    <strong><?php echo $check['title']; ?></strong>

                    <?php if (isset($check['hint'])): ?>
                        <span>(Hint: <?php echo $check['hint']; ?>)</span>
                    <?php endif; ?>

                </li>
            <?php endforeach; ?>

        </ul>
    </div>
</div>