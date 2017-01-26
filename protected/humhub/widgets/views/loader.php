<div <?= $id ? 'id="'.$id.'"' : ''?> class="loader humhub-ui-loader <?= $cssClass; ?>" <?php if(isset($show) && !$show) : ?> style="display:none;" <?php endif; ?>>
    <div class="sk-spinner sk-spinner-three-bounce">
        <div class="sk-bounce1"></div>
        <div class="sk-bounce2"></div>
        <div class="sk-bounce3"></div>
    </div>
</div>