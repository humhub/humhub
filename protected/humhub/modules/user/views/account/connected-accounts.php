<?php

use \yii\bootstrap\Html;
use yii\helpers\Url;
?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
    <div class="help-block">
        <?php echo Yii::t('UserModule.base', 'Here you can connect to external service provider for using external services like a single sign on authentication.'); ?>
    </div>
    <table class="table table-hover">
        <?php foreach ($authClients as $client) : ?>
            <tr>
                <td width='10'>
                    <?php $viewOptions = $client->getViewOptions(); ?>
                    <?php $iconClass = (isset($viewOptions['cssIcon'])) ? $viewOptions['cssIcon'] : ''; ?>

                    <div class='<?= $iconClass; ?> pull-left' style='font-size:200%'></div>
                </td>

                <td style='vertical-align: middle;'>
                    <strong><?php echo $client->getTitle(); ?></strong>
                </td>

                <td class="text-right">
                    <?php if ($client->getId() == $currentAuthProviderId): ?>
                        <?php echo Html::a(Yii::t('UserModule.base', 'Currently in use'), '#', ['class' => 'btn btn-default btn-sm', 'data-method' => 'POST', 'disabled' => 'disabled']); ?>
                    <?php elseif (in_array($client->getId(), $activeAuthClientIds)) : ?>
                        <?php echo Html::a(Yii::t('UserModule.base', 'Disconnect account'), ['connected-accounts', 'disconnect' => $client->getId()], ['class' => 'btn btn-danger btn-sm', 'data-method' => 'POST']); ?>
                    <?php else: ?>
                        <?php echo Html::a(Yii::t('UserModule.base', 'Connect account'), Url::to(['/user/auth/external', 'authclient' => $client->getId()]), ['class' => 'btn btn-success  btn-sm']); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php $this->endContent(); ?>
