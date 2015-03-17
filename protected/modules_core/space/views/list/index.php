<?php $count = 0; ?>

<?php foreach (SpaceMembership::GetUserSpaces() as $space): ?>
    <li>
        <a href="<?php echo $space->getUrl(); ?>">
            <div class="media">
                <!-- Show user image -->
                <img class="media-object img-rounded pull-left" alt="24x24" data-src="holder.js/24x24"
                     style="width: 24px; height: 24px;"
                     src="<?php echo $space->getProfileImage()->getUrl(); ?>">
                <!-- Show space image, if you are outside from a space -->
                <div class="media-body">
                    <!-- Show content -->
                    <strong><?php echo CHtml::encode($space->name); ?></strong>

                    <div id="space-badge-<?php echo $count; ?>" class="badge badge-space pull-right" style="display:none;">0</div>
                    <br>
                    <p><?php echo CHtml::encode(Helpers::truncateText($space->description, 60)); ?></p>
                </div>
            </div>
        </a>
    </li>
    <?php $count++; ?>
<?php endforeach; ?>


