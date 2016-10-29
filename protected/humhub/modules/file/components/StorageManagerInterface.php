<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humhub\modules\file\components;

/**
 * StorageManagerInterface
 * 
 * @version 1.2
 * @author Luke
 */
interface StorageManagerInterface
{

    public function get($variant = null);

    public function set(\yii\web\UploadedFile $file, $variant = null);

    public function setContent($content, $variant = null);

    public function delete($variant = null);

    public function setFile(\humhub\modules\file\models\File $file);
}
