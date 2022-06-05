<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\mail;

use Symfony\Component\Mime\Crypto\SMimeSigner;

/**
 * Message
 *
 * @since 1.2
 * @author Luke
 */
class Message extends \yii\symfonymailer\Message
{
    /*
    public function setSmimeSigner($signingCertificatePath, $signingPrivateKeyPath, $signingOptions = PKCS7_DETACHED, $extraCerts = null)
	{
		$signer = SMimeSigner::newInstance();

		$signer->setSignCertificate($signingCertificatePath, $signingPrivateKeyPath, $signingOptions, $extraCerts);

		$this->getSwiftMessage()->attachSigner($signer);

        return $this;
	}
    */
}
