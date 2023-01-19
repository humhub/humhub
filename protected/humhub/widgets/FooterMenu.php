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
    const LOCATION_ACCOUNT_MENU = 'account_menu';
    const LOCATION_LOGIN = 'login';
    const LOCATION_SIDEBAR = 'sidebar';
    const LOCATION_FULL_PAGE = 'full';
    const LOCATION_EMAIL = 'mail';

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
     * @inheritDoc
     */
    public function run()
    {
        // Make sure Footer on login for powered by
        if (empty($this->entries) && $this->location === static::LOCATION_LOGIN) {
            return $this->render($this->template, $this->getViewParams());
        }

        return parent::run();
    }

}
