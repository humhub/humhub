<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\libs\MimeHelper;

$this->registerJsVar('file_delete_url', Url::to(['/file/file/delete']));
?>
<div class="progress" id="fileUploaderProgressbar_<?php echo $uploaderId; ?>" style="display:none">
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0"
         aria-valuemax="100" style="width: 0%">
    </div>
</div>

<div id="fileUploaderList_<?php echo $uploaderId; ?>" >
    <ul style="list-style: none; margin: 0;" class="contentForm-upload-list" id="fileUploaderListUl_<?php echo $uploaderId; ?>"></ul>
</div>


<script>
<?php foreach ($files as $file): ?>
        addToUploadList("<?php echo $uploaderId; ?>", "<?php echo $file->guid; ?>", "<?php echo Html::encode($file->file_name); ?>", "<?php echo MimeHelper::getMimeIconClassByExtension($file->getExtension()); ?>");
<?php endforeach; ?>
</script>