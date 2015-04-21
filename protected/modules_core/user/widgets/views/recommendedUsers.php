<!-- mode 1: global user recommendations -->
<div class="comotion-content">
<h2>Connections</h2>
<div class="panel panel-default">
  <div id="recommendedUsers" class="panel-body"
    data-base-url="<?php echo Yii::app()->baseUrl; ?>"
    data-in-userid="<?php echo $user->guid ?>">
  </div>
</div>
<script type="text/jsx">
  var container = $("#recommendedUsers");
  $(document).ready(function() {
    React.render(
      <RecommendationList base_url={container.attr('data-base-url')}
        data={[]}
        in_userid={container.attr('data-in-userid')} />,
      container[0]
    );
  });
</script>
</div>