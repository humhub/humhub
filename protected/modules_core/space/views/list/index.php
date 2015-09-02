<?php foreach ($memberships as $membership): ?>
    <?php $newItems = $membership->countNewItems(); ?>
    <li>
        <a href="<?php echo $membership->space->getUrl(); ?>">
            <div class="media">
                <!-- Show user image -->
                <img class="media-object img-rounded pull-left" alt="24x24" data-src="holder.js/24x24"
                     style="width: 24px; height: 24px;"
                     src="<?php echo $membership->space->getProfileImage()->getUrl(); ?>">
                <div class="media-body">
                    <strong><?php echo CHtml::encode($membership->space->name); ?></strong>
                    <?php if ($newItems != 0): ?>
                        <div class="badge badge-space pull-right" style="display:none"><?php echo $newItems; ?></div>
                    <?php endif; ?>
                    <br>
                    <p><?php echo CHtml::encode(Helpers::truncateText($membership->space->description, 60)); ?></p>
                </div>
            </div>
        </a>
    </li>
<?php endforeach; ?>
<script>
    jQuery('.badge-space').fadeIn('slow');
</script>



