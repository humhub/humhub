<?php

use humhub\libs\Html;
use yii\helpers\Url;
use humhub\libs\MimeHelper;
use humhub\modules\file\libs\FileHelper;

/* @var $uploaderId string */
/* @var $file \humhub\modules\file\models\File */

$this->registerJsVar('file_delete_url', Url::to(['/file/file/delete']));
?>
<div class="progress" id="fileUploaderProgressbar_<?= Html::encode($uploaderId) ?>" style="display:none">
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0"
         aria-valuemax="100" style="width: 0%">
    </div>
</div>

<div id="fileUploaderList_<?= Html::encode($uploaderId) ?>" >
    <ul style="list-style: none; margin: 0;" class="contentForm-upload-list" id="fileUploaderListUl_<?= Html::encode($uploaderId) ?>"></ul>
</div>


<script <?= Html::nonce() ?>>
<?php foreach ($files as $file): ?>
        addToUploadList("<?= Html::encode($uploaderId)?>", "<?= Html::encode($file->guid) ?>", "<?= Html::encode($file->file_name) ?>", "<?= MimeHelper::getMimeIconClassByExtension(FileHelper::getExtension($file->file_name)) ?>");
<?php endforeach; ?>
</script>
