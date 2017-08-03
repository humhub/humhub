<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\libs\MimeHelper;
use humhub\modules\file\libs\FileHelper;

$this->registerJsVar('file_delete_url', Url::to(['/file/file/delete']));
?>
<div class="progress" id="fileUploaderProgressbar_<?= $uploaderId; ?>" style="display:none">
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0"
         aria-valuemax="100" style="width: 0%">
    </div>
</div>

<div id="fileUploaderList_<?= $uploaderId; ?>" >
    <ul style="list-style: none; margin: 0;" class="contentForm-upload-list" id="fileUploaderListUl_<?= $uploaderId; ?>"></ul>
</div>


<script>
<?php foreach ($files as $file) : ?>
        addToUploadList("<?= $uploaderId; ?>", "<?= $file->guid; ?>", "<?= Html::encode($file->file_name); ?>", "<?= MimeHelper::getMimeIconClassByExtension(FileHelper::getExtension($file->file_name)); ?>");
<?php endforeach; ?>
</script>
