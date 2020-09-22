<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

$i = 0;
?>

<?php if ($numItems > 0): ?>
    <li class="divider visible-sm visible-xs"></li>

    <?php foreach ($items as $item): ?>
        <?php if ($item['label'] == '---'): ?>
            <li class="divider visible-sm visible-xs"></li>
        <?php else: ?>
            <li class="visible-sm visible-xs">
                <a <?= isset($item['id']) ? 'id="' . $item['id'] . '"' : '' ?>
                    href="<?= $item['url']; ?>" <?= isset($item['pjax']) && $item['pjax'] === false ? 'data-pjax-prevent' : '' ?>>
                    <small>
                        <?= $item['icon'] . ' ' . $item['label']; ?>
                    </small>
                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
