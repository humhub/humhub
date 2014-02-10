<div class="well sidebar-nav">
    <ul class="nav nav-list">
        <li class="nav-header">Your spaces</li>
        <li class="<?php if ($this->getController()->id == 'dashboard'): ?>active<?php endif; ?>"><a href="<?php echo $this->createUrl('//dashboard/index') ?>">All Spaces</a></li>

        <?php foreach ($usersSpaces as $space): ?>
        
            <li class="<?php
            if ($space->guid == $currentSpaceGuid) {
                echo "active";
            }
            ?>"><a href="<?php echo $this->createUrl('//space/space', array('sguid' => $space->guid)); ?>"><?php print Helpers::trimText($space->name, 35); ?></a></li>
            <?php endforeach; ?>
    </ul>
    <br>
    <a href="<?php echo $this->createUrl('//space/create') ?>" class="btn"><i class="icon-plus"></i> New Space</a>
</div>
