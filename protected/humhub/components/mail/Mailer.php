<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\mail;

use Symfony\Component\Mime\Crypto\SMimeSigner;
use Yii;

/**
 * Mailer implements a mailer based on SymfonyMailer.
 *
 * @see \yii\symfonymailer\Mailer
 * @since 1.2
 * @author Luke
 */
class Mailer extends \yii\symfonymailer\Mailer
{
    /**
     * @inheritdoc
     */
    public $messageClass = 'humhub\components\mail\Message';

    /**
     * @var array of surpressed recipient e-mail addresses
     * @since 1.3
     */
    public $surpressedRecipients = ['david.roberts@example.com', 'sara.schuster@example.com'];

    /**
     * @var string|null Path for the sigining certificate. If provided emails will be digitally signed before sending.
     */
    public $signingCertificatePath = null;

    /**
     * @var string|null Path for the sigining certificate private key. If provided emails will be digitally signed before sending.
     */
    public $signingPrivateKeyPath = null;

    /**
     * @var string|null A passphrase of the private key (if any)
     */
    public $signingPrivateKeyPassphrase = null;

    /**
     * @var string|null Path for extra sigining certificates (i.e. intermidiate certificates).
     */
    public $signingExtraCertsPath = null;

    /**
     * @var int Bitwise operator options for openssl_pkcs7_sign()
     */
    public $signingOptions = PKCS7_DETACHED;

    /**
     * @var SMimeSigner|null
     */
    private $signer = null;

    /**
     * @inheritDoc
     */
    public function compose($view = null, array $params = [])
    {
        $message = parent::compose($view, $params);

        // Set HumHub default from values
        if (empty($message->getFrom())) {
            $message->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')]);
            if ($replyTo = Yii::$app->settings->get('mailer.systemEmailReplyTo')) {
                $message->setReplyTo($replyTo);
            }
        }

        if ($this->signingCertificatePath !== null && $this->signingPrivateKeyPath !== null) {
            if ($this->signer === null) {
                $this->signer = new SMimeSigner(
                    $this->signingCertificatePath,
                    $this->signingPrivateKeyPath,
                    $this->signingPrivateKeyPassphrase,
                    $this->signingExtraCertsPath,
                    $this->signingOptions
                );
            }
            $this->withSigner($this->signer);
        }

        return $message;
    }


    /**
     * @inheritdoc
     * @param Message $message
     */
    public function sendMessage($message): bool
    {

        // Remove example e-mails
        $address = $message->getTo();

        if (is_array($address)) {
            foreach (array_keys($address) as $email) {
                if ($this->isRecipientSurpressed($email)) {
                    unset($address[$email]);
                }
            }
            if (count($address) == 0) {
                return true;
            }
            $message->setTo($address);
        } elseif ($this->isRecipientSurpressed($address)) {
            return true;
        }

        return parent::sendMessage($message);
    }

    private function isRecipientSurpressed($email): bool
    {
        $email = strtolower($email);

        foreach ($this->surpressedRecipients as $surpressed) {
            if (strpos($email, $surpressed) !== false) {
                return true;
            }
        }

        return false;
    }
}
