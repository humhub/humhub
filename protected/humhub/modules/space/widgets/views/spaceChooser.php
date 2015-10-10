<?php
/* @var $this \humhub\components\WebView */
/* @var $currentSpace \humhub\modules\space\models\Space */

use yii\helpers\Url;
use yii\helpers\Html;

$this->registerJsFile("@web/resources/space/spacechooser.js");
$this->registerJsVar('scSpaceListUrl', Url::to(['/space/list', 'ajax' => 1]));
?>

<li class="dropdown">
    <a href="#" id="space-menu" class="dropdown-toggle" data-toggle="dropdown">
        <!-- start: Show space image and name if chosen -->
        <?php if ($currentSpace) { ?>
            <img
                src="<?php echo $currentSpace->getProfileImage()->getUrl(); ?>"
                width="32" height="32" alt="32x32" data-src="holder.js/24x24"
                style="width: 32px; height: 32px; margin-right: 3px; margin-top: 3px;" class="img-rounded"/>
            <?php } ?>

        <?php
        if (!$currentSpace) {
            echo '<i class="fa fa-dot-circle-o"></i><br>' . Yii::t('SpaceModule.widgets_views_spaceChooser', 'My spaces');
        }
        ?>
        <!-- end: Show space image and name if chosen -->
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu" id="space-menu-dropdown">
        <li>
            <form action="" class="dropdown-controls"><input type="text" id="space-menu-search"
                                                             class="form-control"
                                                             autocomplete="off"
                                                             placeholder="<?php echo Yii::t('SpaceModule.widgets_views_spaceChooser', 'Search'); ?>">

                <div class="search-reset" id="space-search-reset"><i
                        class="fa fa-times-circle"></i></div>
            </form>
        </li>

        <li class="divider"></li>
        <li>
            <ul class="media-list notLoaded" id="space-menu-spaces">
                <li id="loader_spaces">
                    <?php echo \humhub\widgets\LoaderWidget::widget(); ?>
                </li>
            </ul>
        </li>
        <?php if ($canCreateSpace): ?>
            <li>
                <div class="dropdown-footer">
                    <?php
                    echo Html::a(Yii::t('SpaceModule.widgets_views_spaceChooser', 'Create new space'), Url::to(['/space/create/create']), array('class' => 'btn btn-info col-md-12', 'data-target' => '#globalModal'));
                    ?>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</li>

<script type="text/javascript">

    // set niceScroll to SpaceChooser menu
    $("#space-menu-spaces").niceScroll({
        cursorwidth: "7",
        cursorborder: "",
        cursorcolor: "#555",
        cursoropacitymax: "0.2",
        railpadding: {top: 0, right: 3, left: 0, bottom: 0}
    });

</script>
