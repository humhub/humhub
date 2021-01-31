<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\roadrunner;

use Laminas\Diactoros\Stream;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Cookie;
use Psr\Http\Message\ResponseInterface;

/**
 * @inheritDoc
 */
class Response extends \humhub\components\Response
{
    /**
     * @var static
     */
    public static $sentResponse;

    private $_sendFileHandle = null;
    private $_sendOptions = [];

    /**
     * Do not really sent the response
     */
    public function send()
    {
        static::$sentResponse = $this;
    }


    public function sendStreamAsFile($handle, $attachmentName, $options = [])
    {
        $this->_sendFileHandle = $handle;
        $this->_sendOptions['mimeType'] = isset($options['mimeType']) ? $options['mimeType'] : 'application/octet-stream';
        $this->_sendOptions['inline'] = !empty($options['inline']);
    }


    /**
     * Populates this response with a PSR7 Response Interface
     *
     * @param ResponseInterface $response
     * @return self
     */
    public function withPsr7Response(ResponseInterface $response)
    {
        $this->setStatusCode($response->getStatusCode());
        $this->content = (string)$response->getBody();
        foreach ($response->getHeaders() as $name => $value) {
            $this->headers->add($name, $value);
        }

        return $this;
    }

    /**
     * Returns a PSR7 response
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        $this->trigger(self::EVENT_BEFORE_SEND);
        $this->prepare();
        $this->trigger(self::EVENT_AFTER_PREPARE);
        $stream = $this->getPsr7Content();

        // If a session is defined transform it into a `yii\web\Cookie` instance then close the session.
        if (($session = Yii::$app->getSession()) !== null) {
            $this->cookies->add(
                new Cookie(
                    [
                        'name' => $session->getName(),
                        'value' => $session->id,
                        'path' => ini_get('session.cookie_path')
                    ]
                )
            );
            $session->close();
        }

        if ($this->_sendFileHandle !== null) {
            $stream = $this->_sendFileHandle;
        }

        $response = new \Laminas\Diactoros\Response(
            $stream,
            $this->getStatusCode()
        );

        // Manually set headers to ensure array headers are added.
        foreach ($this->getPsr7Headers() as $header => $value) {
            if (\is_array($header)) {
                foreach ($header as $v) {
                    $response = $response->withAddedHeader($header, $v);
                }
            } else {
                $response = $response->withHeader($header, $value);
            }
        }


        if (isset($this->_sendOptions['mimeType'])) {
            $response = $response->withHeader('Content-Type', $this->_sendOptions['mimeType']);
        }
        if (isset($this->_sendOptions['attachmentName'])) {
            $disposition = empty($this->_sendOptions['inline']) ? 'attachment' : 'inline';
            $response = $response->withHeader('Content-Disposition', $this->getDispositionHeaderValue(
                $disposition, $this->_sendOptions['attachmentName']
            ));
        }

        $this->trigger(self::EVENT_AFTER_SEND);
        $this->isSent = true;

        return $response;
    }

    /**
     * Returns all headers to be sent to the client
     *
     * @return array
     */
    private function getPsr7Headers(): array
    {
        $headers = [];
        foreach ($this->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            // set replace for first occurrence of header but false afterwards to allow multiple
            $replace = true;
            foreach ($values as $value) {
                if ($replace) {
                    $headers[$name] = $value;
                }
                $replace = false;
            }
        }

        return \array_merge($headers, $this->getPsr7Cookies());
    }

    /**
     * Convers the PSR-7 header cookies to raw headers
     *
     * @return array
     */
    private function getPsr7Cookies(): array
    {
        $cookies = [];
        $request = Yii::$app->getRequest();
        if ($request->enableCookieValidation) {
            if ($request->cookieValidationKey == '') {
                throw new InvalidConfigException(get_class($request) . '::cookieValidationKey must be configured with a secret key.');
            }
            $validationKey = $request->cookieValidationKey;
        }

        foreach ($this->getCookies() as $cookie) {
            $value = $cookie->value;
            if ($cookie->name !== 'PHPSESSID' && $cookie->expire != 1 && isset($validationKey)) {
                $value = Yii::$app->getSecurity()->hashData(serialize([$cookie->name, $value]), $validationKey);
            }

            $data = "$cookie->name=" . \urlencode($value);

            if ($cookie->expire) {
                $data .= "; Expires={$cookie->expire}";
            }

            if (!empty($cookie->path)) {
                $data .= "; Path={$cookie->path}";
            }

            if (!empty($cookie->domain)) {
                $data .= "; Domain={$cookie->domain}";
            }

            if ($cookie->secure) {
                $data .= "; Secure";
            }

            if ($cookie->httpOnly) {
                $data .= "; HttpOnly";
            }

            $cookies['Set-Cookie'][] = $data;
        }

        return $cookies;
    }

    /**
     * Returns the PSR7 Stream
     *
     * @return stream
     */
    private function getPsr7Content()
    {
        if ($this->stream === null) {
            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $this->content ?? '');
            rewind($stream);
            $this->stream = $stream;
        }

        return $this->stream;
    }
}
