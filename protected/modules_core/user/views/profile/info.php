<div class="panel panel-default">
    <div class="panel-heading">Info</div>

    <div class="panel-body">

        <?php foreach ($user->profile->getProfileFieldCategories() as $category): ?>
            <div>
                <h2><?php echo $category->title; ?></h2>

                <?php foreach ($user->profile->getProfileFields($category) as $field) : ?>

                    <strong><?php echo $field->title ; ?>:</strong>
                    <?php echo $field->getUserValue($user, false); ?>
                    <br />
                    
                <?php endforeach; ?>
                <hr />
            </div>
        <?php endforeach; ?>


    </div>
</div>