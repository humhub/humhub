<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\roadrunner;

use humhub\components\Event;
use Psr\Http\Message\ResponseInterface;

/**
 * @inheritDoc
 * @package humhub\components\roadrunner
 */
class Application extends \humhub\components\Application
{

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        Event::offAll();

        $config['components']['response']['class'] = Response::class;
        $config['components']['request']['class'] = Request::class;

        $config['components']['urlManager']['showScriptName'] = false;
        $config['components']['urlManager']['enablePrettyUrl'] = true;

        return parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (($session = $this->getSession()) !== null) {
            $session->close();
            $session->setUseCookies(null);

            if (isset($_COOKIE['PHPSESSID'])) {
                $session->setId($_COOKIE['PHPSESSID']);
            }
        }

        parent::init();
    }


    /**
     * @return ResponseInterface
     */
    public function run()
    {
        parent::run();

        // We dont really sent the response
        if (Response::$sentResponse !== null) {
            #Event::offAll();
            #$this->db->close();
            return Response::$sentResponse->getPsr7Response();
        }
    }
}
