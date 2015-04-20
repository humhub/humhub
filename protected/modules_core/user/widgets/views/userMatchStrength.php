<!-- mode 1: global user recommendations -->
<div class="panel panel-default">
  <div id="recommendedUsers" class="panel-body"
    data-base-url="<?php echo Yii::app()->baseUrl; ?>"
    data-in-userid="<?php echo $user->guid ?>"
    data-out-userid="<?php echo $out_user->guid ?>">
  </div>
</div>
<script type="text/jsx">
  var container = $("#recommendedUsers");
  $(document).ready(function() {
    React.render(
      <RecommendationList base_url={container.attr('data-base-url')}
        data={STUB_DATA}
        in_userid={container.attr('data-in-userid')} />,
      container[0]
    );
  });
</script>
