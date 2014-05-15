<div class="panel panel-default profile">

    <div class="panel-body">
        <?php $seperator = ""; ?>

        <div class="controls">
            <!-- start: User following -->
            <?php
            if (Yii::app()->user->id != $this->getUser()->id) {
                if ($this->getUser()->isFollowedBy(Yii::app()->user->id)) {
                    print CHtml::link("Unfollow", $this->createUrl('profile/unfollow', array('guid' => $this->getUser()->guid)), array('class' => 'btn btn-primary'));
                } else {
                    print CHtml::link("Follow", $this->createUrl('profile/follow', array('guid' => $this->getUser()->guid)), array('class' => 'btn btn-success'));
                }
            }
            ?>
            <!-- end: User following -->

            <!-- start: Edit profile -->
            <?php if (Yii::app()->user->id == $this->getUser()->id) { ?>
                <!-- Edit user account (if this is your profile) -->
                <a href="<?php echo $this->createUrl('//user/account/edit', array('guid' => $this->getUser()->guid)); ?>"
                   id="edit_profile" class="btn btn-primary">Edit account</a>
            <?php } ?>
            <!-- end: Edit profile -->

        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="media">

                    <a class="pull-left" href="#">
                        <img class="media-object img-rounded" data-src="holder.js/140x140" alt="140x140"
                             style="width: 140px; height: 140px;"
                             src="<?php echo $this->getUser()->getProfileImage()->getUrl(); ?>">
                    </a>


                    <div class="media-body">
                        <h3 class="media-heading"><?php echo $this->getUser()->displayName; ?>

                            <small>
                                <!-- start: Social user bookmarks -->

                                <!-- show skype icon, if stated -->
                                <?php if ($this->getUser()->profile->im_skype != "") : ?>
                                    <a href="skype:<?php echo $this->getUser()->profile->im_skype; ?>?chat&topic=hello"
                                       target="_self"
                                       alt="skype link"><i
                                            class="fa fa-skype"></i></a>
                                <?php endif; ?>

                                <!-- show facebook icon, if stated -->
                                <?php if ($this->getUser()->profile->url_facebook != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_facebook; ?>" target="_blank"
                                       alt="facebook link"><i
                                            class="fa fa-facebook-sign"></i></a>
                                <?php endif; ?>

                                <!-- show linkedin icon, if stated -->
                                <?php if ($this->getUser()->profile->url_linkedin != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_linkedin; ?>" target="_blank"
                                       alt="linkedin link"><i
                                            class="fa fa-linkedin-sign"></i></a>
                                <?php endif; ?>

                                <!-- show xing icon, if stated -->
                                <?php if ($this->getUser()->profile->url_xing != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_xing; ?>" target="_blank"
                                       alt="xing link"><i
                                            class="fa fa-xing"></i></a>
                                <?php endif; ?>

                                <!-- show youtube icon, if stated -->
                                <?php if ($this->getUser()->profile->url_youtube != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_youtube; ?>" target="_blank"
                                       alt="youtube link"><i
                                            class="fa fa-youtube"></i></a>
                                <?php endif; ?>

                                <!-- show vimeo icon, if stated -->
                                <?php if ($this->getUser()->profile->url_vimeo != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_vimeo; ?>" target="_blank"
                                       alt="vimeo link"><i
                                            class="fa fa-youtube-play"></i></a>
                                <?php endif; ?>

                                <!-- show flickr icon, if stated -->
                                <?php if ($this->getUser()->profile->url_flickr != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_flickr; ?>" target="_blank"
                                       alt="flickr link"><i
                                            class="fa fa-flickr"></i></a>
                                <?php endif; ?>

                                <!-- show myspace icon, if stated -->
                                <?php if ($this->getUser()->profile->url_myspace != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_myspace; ?>" target="_blank"
                                       alt="myspace link"><i
                                            class="fa fa-music"></i></a>
                                <?php endif; ?>

                                <!-- show googleplus icon, if stated -->
                                <?php if ($this->getUser()->profile->url_googleplus != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_googleplus; ?>" target="_blank"
                                       alt="googleplus link"><i
                                            class="fa fa-google-plus"></i></a>
                                <?php endif; ?>

                                <!-- show twitter icon, if stated -->
                                <?php if ($this->getUser()->profile->url_twitter != "") : ?>
                                    <a href="<?php echo $this->getUser()->profile->url_twitter; ?>" target="_blank"
                                       alt="twitter link"><i
                                            class="fa fa-twitter"></i></a>
                                <?php endif; ?>

                                <!-- end: Social user bookmarks -->
                            </small>
                        </h3>

                        <h5 class="media-heading"><?php echo $this->getUser()->title; ?>
                            (<?php echo $this->getUser()->group->name; ?>)</h5>


                        <?php if ($this->getUser()->profile->phone_work != "") : ?>
                            <?php echo $seperator; ?>
                            <i class="fa fa-phone"></i> <?php echo $this->getUser()->profile->phone_work; ?>
                            <?php $seperator = ", "; ?>
                        <?php endif; ?>

                        <?php if ($this->getUser()->profile->city != "") : ?>
                            <?php echo $seperator; ?>
                            <i class="fa fa-map-marker"></i> <?php echo $this->getUser()->profile->city; ?>
                            <?php $seperator = ", "; ?>
                        <?php endif; ?>



                        <br>

                        <!-- start: tags for user skills -->
                        <div class="tags">
                            <?php if ($this->getUser()->tags) : ?>
                                <?php foreach ($this->getUser()->getTags() as $tag) { ?>
                                    <?php echo HHtml::link($tag, $this->createUrl('//directory/directory/members', array('keyword' => 'tags:' . $tag, 'areas' => array('User'))), array('class' => 'btn btn-info btn-xs tag')); ?>
                                <?php } ?>
                            <?php endif; ?>
                        </div>
                        <!-- end: tags for user skills -->


                    </div>

                </div>

            </div>
            <div class="col-md-5">

                <!-- start: User statistics -->
                <div class="statistics pull-right">

                    <div class="pull-left entry">
                        <span class="count"><?php echo count($this->getUser()->followerUser); ?></span></a>
                        <br>
                        <span class="title"><?php echo Yii::t('UserModule.profile', 'Followers'); ?></span>
                    </div>

                    <div class="pull-left entry">
                        <span class="count"><?php echo count($this->getUser()->followsUser); ?></span>
                        <br>
                        <span class="title"><?php echo Yii::t('UserModule.profile', 'Following'); ?></span>
                    </div>

                    <div class="pull-left entry">
                        <span class="count"><?php echo count($this->getUser()->workspaces); ?></span><br>
                        <span class="title"><?php echo Yii::t('base', 'Spaces'); ?></span>
                    </div>
                </div>
                <!-- end: User statistics -->

            </div>
        </div>

    </div>

</div>