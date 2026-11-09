<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * Tabs of the "Profile" page of the account settings: the categories of the profile fields and the tags
 *
 * Sort orders: the categories 100, 200, …, the tags after them. Modules add tabs on `EVENT_INIT`; module pages
 * render inside `@user/views/account/_userProfileLayout.php`.
 */
class AccountProfileMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $categories = Yii::$app->user->getIdentity()->profile->getFormCategories();
        $current = (int)Yii::$app->request->get('category', array_key_first($categories) ?? 0);

        $sortOrder = 100;
        foreach ($categories as $id => $category) {
            $this->addEntry(new MenuLink([
                'label' => $category['title'],
                'url' => ['/user/account/edit', 'category' => $id],
                'sortOrder' => $sortOrder,
                'isActive' => $current === $id && ControllerHelper::isActivePath('user', 'account', 'edit'),
            ]));
            $sortOrder += 100;
        }

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Tags'),
            'url' => ['/user/account/edit-tags'],
            'sortOrder' => $sortOrder,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit-tags'),
        ]));

        parent::init();
    }
}
