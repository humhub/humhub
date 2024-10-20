<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\modules\ui\menu\widgets\Menu;

/**
 * FooterMenu displays a footer navigation for pages e.g. Imprint
 *
 * @since 1.2.6
 * @author Luke
 */
class FooterMenu extends Menu
{
    public const LOCATION_ACCOUNT_MENU = 'account_menu';
    public const LOCATION_LOGIN = 'login';
    public const LOCATION_SIDEBAR = 'sidebar';
    public const LOCATION_FULL_PAGE = 'full';
    public const LOCATION_EMAIL = 'mail';

    /**
     * @var string location of footer menu (e.g. login, mail, sidebar)
     */
    public $location = 'full';

    /**
     * @inheritdoc
     */
    public $template = 'footerNavigation';

    /**
     * @inheritdoc
     */
    public $id = 'footer-menu-nav';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->location === static::LOCATION_LOGIN) {
            $this->template = 'footerNavigation_login';
        } elseif ($this->location === static::LOCATION_SIDEBAR) {
            $this->template = 'footerNavigation_sidebar';
        } elseif ($this->location === static::LOCATION_EMAIL) {
            $this->template = 'footerNavigation_email';
        } elseif ($this->location === static::LOCATION_ACCOUNT_MENU) {
            $this->template = 'footerNavigation_account_menu';
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getViewParams()
    {
        $params = parent::getViewParams();
        $params['location'] = $this->location;
        return $params;
    }

    /**
     * Make sure "Powered by" is displayed even if no entries in the menu
     * @inheritDoc
     */
    public function run()
    {
        $this->trigger(static::EVENT_RUN);
        return $this->render($this->template, $this->getViewParams());
    }
}
