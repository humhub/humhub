<?php
$count_max = count($notifications);
$count_current = 1;
$class = "";
?>


<?php if ($count_max == 0) { ?>
    <li class="placeholder">There are no notifications yet.</li>
<?php } else { ?>
    <ul class="media-list">
        <?php
        foreach ($notifications as $notification) {
            ?>
            <?php echo $notification->getOut(); ?>

            <?php
            $count_current++;
        }
        ?>
    </ul>
<?php } ?>

<script type="text/javascript">
    $('span.time').timeago();
</script>