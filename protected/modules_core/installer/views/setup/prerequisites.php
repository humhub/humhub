<div class="panel panel-default">
    <div class="panel-body">
        <h3> HumHub Installation</h3>
        <hr>

            <div class="well">

                <ul>

                    <?php foreach ($checks as $check): ?>
                        <li>
                            <strong><?php echo $check['title']; ?>:</strong>

                            <?php if ($check['state'] == 'OK') : ?>
                                <span style="color:green">Ok!</span>
                            <?php elseif ($check['state'] == 'WARNING') : ?>
                                <span style="color:orange">Warning!</span>
                            <?php else : ?>
                                <span style="color:red">Error!</span>
                            <?php endif; ?>

                            <?php if (isset($check['hint'])): ?>
                                <span>(Hint: <?php echo $check['hint']; ?>)</span>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>


                </ul>
            </div>

            <?php if (!$hasError): ?>
                <div class="alert alert-success">
                    Everything ok, we can start over!
                </div>
            <?php endif; ?>

            <hr>

            <?php echo HHtml::link('Check again', array('//installer/setup/prerequisites'), array('class' => 'btn btn-primary')); ?>

            <?php if (!$hasError): ?>
                <?php echo HHtml::link('Go to database configuration <i class="fa fa-circle-arrow-right"></i>', array('//installer/setup/database'), array('class' => 'btn btn-success')); ?>
            <?php endif; ?>









    </div>
</div>