<!-- mode 1: global user recommendations user match strength -->
<style>
.user-profile-connections{ padding-left: 15px;}

</style>

<div class="user-profile-connections">
<h2 class="">Connections</h2>
<div class="">
  <div id="recommendedUsers" class=""
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
</div>