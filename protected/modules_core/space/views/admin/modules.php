<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.base', '<strong>Space</strong> modules'); ?>
    </div>
    <div class="panel-body">
        <?php echo Yii::t('SpaceModule.base', 'Enhance this space with modules.'); ?><br/><br/>

        <div class="row">
            <div class="col-md-4">
                <div class="alert alert-default text-center">
                    <img class="img-rounded" data-src="holder.js/48x48" alt="48x48" style="width: 48px; height: 48px;"
                         src="http://localhost/notes-icon.png">
                    <br/>

                    <div class="info">
                        <strong>Notes</strong><br/>
                        Edit with other user at notes <br/>
                    </div>
                    <a href="" class="btn btn-primary btn-sm">Disable</a> <a href="" class="btn btn-default btn-sm">Configure</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-default text-center" style="position: relative;">

                    <ul class="nav nav-pills preferences text-left">
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="font-size: 14px;"><i
                                    class="fa fa-info-circle"></i></a>
                            <ul class="dropdown-menu pull-right">
                                <li><a href="#" class="panel-collapse"
                                       style="font-size: 13px !important;">Description</a></li>
                                <li><a href="#" class="panel-collapse" style="font-size: 13px !important;">Module
                                        website</a></li>

                            </ul>
                        </li>
                    </ul>

                    <img class="img-rounded" data-src="holder.js/48x48" alt="48x48" style="width: 48px; height: 48px;"
                         src="https://dt8kf6553cww8.cloudfront.net/static/images/brand/glyph@2x-vflJ1vxbq.png">
                    <br/>

                    <div class="info">
                        <strong>Dropbox</strong><br/>
                        Edit with other user at notes <br/>
                    </div>
                    <a href="" class="btn btn-info">Enable</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-default text-center">
                    <img class="img-rounded" data-src="holder.js/48x48" alt="48x48" style="width: 48px; height: 48px;"
                         src="http://screenshots.de.sftcdn.net/blog/de/2012/04/Google_Drive_Logo_lrg-580x461.jpg">
                    <br/>

                    <div class="info">
                        <strong>Google Drive</strong><br/>
                        Edit with other user at notes <br/>
                    </div>
                    <a href="" class="btn btn-info">Enable</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-default text-center">
                    <img class="img-rounded" data-src="holder.js/48x48" alt="48x48" style="width: 48px; height: 48px;"
                         src="https://lh3.ggpht.com/si0cgkp2rkVX5JhhBYrtZ4cy2I1hZcrx8aiz-v8MjvPykfhT7-YAM2B8MNi0OCF9AQ=w300">
                    <br/>

                    <div class="info">
                        <strong>Evernote</strong><br/>
                        Edit with other user at notes <br/>
                    </div>
                    <a href="" class="btn btn-info">Enable</a>
                </div>
            </div>
        </div>


        <br/><br/>

        <div class="alert alert-default">

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/48x48" alt="48x48"
                     style="width: 48px; height: 48px;" src="http://localhost/notes-icon.png">

                <div class="media-body">
                    <h4 class="media-heading">Notes 2.0.2<a class="btn btn-primary pull-right"
                                                            onclick="return moduleDisableWarning()"
                                                            href="/humhub/index.php?r=space/admin/disableModule&amp;moduleId=notes&amp;sguid=8f775bd0-f4cd-4aee-b983-537f80ab4075">Enable</a>
                    </h4>

                    <p>Integrates etherpads to your space.</p>
                </div>
            </div>

        </div>

        <div class="alert alert-default">

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="https://dt8kf6553cww8.cloudfront.net/static/images/brand/glyph@2x-vflJ1vxbq.png">

                <div class="media-body">
                    <h4 class="media-heading">Dropbox<a class="btn btn-primary pull-right"
                                                        onclick="return moduleDisableWarning()"
                                                        href="/humhub/index.php?r=space/admin/disableModule&amp;moduleId=notes&amp;sguid=8f775bd0-f4cd-4aee-b983-537f80ab4075">Enable</a>
                    </h4>

                    <p>Integrates etherpads to your space.</p>
                </div>
            </div>

        </div>
        <div class="alert alert-default">

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="http://screenshots.de.sftcdn.net/blog/de/2012/04/Google_Drive_Logo_lrg-580x461.jpg">

                <div class="media-body">
                    <h4 class="media-heading">Google Drive<a class="btn btn-primary pull-right"
                                                             onclick="return moduleDisableWarning()"
                                                             href="/humhub/index.php?r=space/admin/disableModule&amp;moduleId=notes&amp;sguid=8f775bd0-f4cd-4aee-b983-537f80ab4075">Enable</a>
                    </h4>

                    <p>Integrates etherpads to your space.</p>
                </div>
            </div>

        </div>
        <div class="alert alert-default">

            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="https://lh3.ggpht.com/si0cgkp2rkVX5JhhBYrtZ4cy2I1hZcrx8aiz-v8MjvPykfhT7-YAM2B8MNi0OCF9AQ=w300">

                <div class="media-body">
                    <h4 class="media-heading">Evernote<a class="btn btn-primary pull-right"
                                                         onclick="return moduleDisableWarning()"
                                                         href="/humhub/index.php?r=space/admin/disableModule&amp;moduleId=notes&amp;sguid=8f775bd0-f4cd-4aee-b983-537f80ab4075">Enable</a>
                    </h4>

                    <p>Integrates etherpads to your space. <br/>
                        <a href="">Description</a> | <a href="">Author website</a>
                    </p>
                </div>
            </div>

        </div>

        <?php foreach ($this->getSpace()->getAvailableModules() as $moduleId => $moduleInfo): ?>
            <div class="media">
                <img class="media-object img-rounded pull-left" data-src="holder.js/64x64" alt="64x64"
                     style="width: 64px; height: 64px;"
                     src="<?php echo Yii::app()->baseUrl; ?>/uploads/profile_image/default_module.jpg">

                <div class="media-body">
                    <h4 class="media-heading"><?php echo $moduleInfo['title']; ?>
                        <?php if ($this->getSpace()->isModuleEnabled($moduleId)) : ?>
                            <small><span
                                    class="label label-success"><?php echo Yii::t('SpaceModule.base', 'Activated'); ?></span>
                            </small>
                        <?php endif; ?>
                    </h4>

                    <p><?php echo $moduleInfo['description']; ?></p>
                    <?php if ($this->getSpace()->isModuleEnabled($moduleId)) : ?>
                        <?php echo CHtml::link(Yii::t('base', 'Disable'), array('//space/admin/disableModule', 'moduleId' => $moduleId, 'sguid' => $this->getSpace()->guid), array('class' => 'btn btn-sm btn-primary', 'onClick' => 'return moduleDisableWarning()')); ?>

                        <?php if (isset($moduleInfo['configRoute'])) : ?>
                            <?php
                            echo CHtml::link(
                                Yii::t('SpaceModule.base', 'Configure'), $this->createUrl($moduleInfo['configRoute'], array('sguid' => $this->getSpace()->guid)), array('class' => 'btn btn-default')
                            );
                            ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo CHtml::link(Yii::t('base', 'Enable'), array('//space/admin/enableModule', 'moduleId' => $moduleId, 'sguid' => $this->getSpace()->guid), array('class' => 'btn btn-sm btn-primary')); ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Start: Module update message for the future -->
            <!--            <br>
                        <div class="alert alert-warning">
                            New Update for this module is available! <a href="#">See details</a>
                        </div>-->
            <!-- End: Module update message for the future -->
            <hr>
        <?php endforeach; ?>

    </div>
</div>