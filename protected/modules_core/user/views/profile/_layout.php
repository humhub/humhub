<div class="container profile-layout-container">

  <div class="row">
      <?php
        $this->widget('application.modules_core.user.widgets.ProfileHeaderWidget', array(
          'user'     => $this->getUser(),
          'comotion' => true
        ));
      ?>
  </div>

  <div class="row">
      <?php
        echo $content;
      ?>
  </div>

</div>
