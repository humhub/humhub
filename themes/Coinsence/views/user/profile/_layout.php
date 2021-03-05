<?php

use humhub\assets\Select2BootstrapAsset;
use humhub\modules\space\widgets\Image as SpaceImage;

$user = $this->context->contentContainer;

// test begin
use humhub\modules\user\widgets\ProfileHeader;
use humhub\modules\xcoin\widgets\ProjectPortfolio;
use humhub\modules\xcoin\widgets\MarketPlacePortfolio;
use humhub\modules\user\widgets\ProfileMenu;
use humhub\modules\xcoin\assets\Assets;
use humhub\modules\xcoin\models\Challenge;
use humhub\widgets\FooterMenu;
use yii\bootstrap\Progress;
use \humhub\modules\xcoin\models\Funding;
use \yii\helpers\Html;
use \yii\helpers\Url;
use humhub\modules\xcoin\models\Product;
use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\xcoin\widgets\MyRecentActivities;
use humhub\modules\post\widgets\Form;
use humhub\modules\stream\widgets\StreamViewer;
Assets::register($this);
use humhub\modules\xcoin\widgets\UserExperience;

Select2BootstrapAsset::register($this);
/** @var $selectedChallenge Challenge | null */
/** @var $fundings Funding[] */
/** @var $assetsList array */
/** @var $challengesList array */
/** @var $countriesList array */
/** @var $challengesCarousel array */
use humhub\modules\user\widgets\ProfileHeaderCounterSet;
use humhub\libs\Iso3166Codes;
use humhub\modules\user\widgets\Image;
use humhub\modules\xcoin\widgets\UserProfileOfferNeed;

Assets::register($this);
Select2BootstrapAsset::register($this);

/** @var $selectedMarketplace Marketplace | null */
/** @var $products Product[] */
/** @var $assetsList array */
/** @var $marketplacesList array */
/** @var $countriesList array */
/** @var $marketplacesCarousel array */
/** @var $model ProductFilter */

?>


<div class="container profile-layout-container userProfileContainer">
    <div class="row">
        <div class="col-md-12">
            <?= ProfileHeader::widget([
                'user' => $user
            ]);?>
        </div>
    </div>

    <div class="row">

        <div class="col-lg-<?= ($this->hasSidebar()) ? '9' : '12' ?> profileLeftContainer">
           <?= $content ?>
        </div>
        <?php if ($this->hasSidebar()): ?>
        <div class="col-lg-3 layout-sidebar-container">
            <?=$this->getSidebar()?>
            <?=FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]);?>
        </div>
        <?php endif;?>
    </div>

    <!-- <div class="row profile-content">
        <div class="col-md-2 layout-nav-container">
            <?/*= ProfileMenu::widget(['user' => $user]); */?>
        </div>
        <div class="col-md-<?/*=($this->hasSidebar()) ? '7' : '10'*/?> layout-content-container">
            <?/*=$content;*/?>
            <?/*php if (!$this->hasSidebar()): */?>
            <?/*= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); */?>
            <?/*php endif;*/?>
        </div>
    </div> -->
</div>
   