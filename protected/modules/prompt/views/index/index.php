<?php

use app\humhub\modules\prompt\assets\Assets;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\promptForm */
/* @var $summary string */

// Register our module assets, this could also be done within the controller
\app\humhub\modules\prompt\assets\Assets::register($this);

$this->title = 'Quick Prompt';
$this->params['breadcrumbs'][] = $this->title;

?>

<div id="root"></div>
