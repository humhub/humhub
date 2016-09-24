<?php
/**
 * Left Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5
 */
?>
<?php foreach ($this->context->getItemGroups() as $group) : ?>

    <?php $items = $this->context->getItems($group['id']); ?>
    <?php if (count($items) == 0) continue; ?>

    <div class="btn-group dropdown-navigation">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="true">
            <?php if ($group['label'] != "") {
                echo $group['label'];
            } ?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu pull-right">

            <?php foreach ($items as $item) : ?>
                <li>
                    <?php echo \yii\helpers\Html::a($item['icon'] . " <span>" . $item['label'] . "</span>", $item['url'], $item['htmlOptions']); ?>
                </li>
            <?php endforeach; ?>

        </ul>
    </div>
<?php endforeach; ?>
