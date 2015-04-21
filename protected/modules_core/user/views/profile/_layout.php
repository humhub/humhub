<div class="container profile-layout-container">

  <div class="row">
    <div class="">
      <?php
        $this->widget('application.modules_core.user.widgets.ProfileHeaderWidget', array(
          'user'     => $this->getUser(),
          'comotion' => true
        ));
      ?>
    </div>
  </div>

  <div class="row">
    <div class="">
      <?php
        echo $content;
      ?>
    </div>
  </div>

</div>
