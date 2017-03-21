<?php 

/**
 * This is a javascript widget view containing the javascript, that adds the sort by drag an drop logic to the defined elements.
 * 
 * @uses $containerClassName;
 * @uses $sortableItemClassName;
 * @uses $url;
 * @uses $additionalAjaxParams;
 * @author Sebastian Stumpf
 */
?>

<script>
$(function() {
    $( ".<?= $containerClassName; ?>" ).sortable({
        stop: function(event, ui) {
            var data = {};
            var sortable = ui.item.closest('.<?= $containerClassName; ?>');
            var items = sortable.find('.<?= $sortableItemClassName; ?>');
            <?php foreach($additionalAjaxParams as $name => $value) {
            if($name != null && $name != '' && $value != null && $value != '') { ?>
            data.<?= $name ?> = '<?= $value ?>';    
            <?php } } ?>
            data.items = [];
            items.each(function(new_index) {
                data.items.push({
                    id:jQuery(this).data('id'),
                    index:new_index
                });
            });
            jQuery.ajax({
                type:"POST",
                url: "<?= $url?>",
                data: data,
                dataType: "json",
                error: function(resp) {
                    $( ".<?= $containerClassName; ?>" ).sortable('cancel');
                }
            });
        }
    });
    $( ".<?= $containerClassName; ?>" ).disableSelection();
});
</script>