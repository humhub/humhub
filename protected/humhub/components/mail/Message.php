<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\mail;

/**
 * Message
 *
 * @since 1.2
 * @author Luke
 */
class Message extends \yii\swiftmailer\Message
{
    public function setSmimeSigner($signingCertificatePath, $signingPrivateKeyPath, $signingOptions = PKCS7_DETACHED, $extraCerts = null)
	{
		$signer = \Swift_Signers_SMimeSigner::newInstance();

		$signer->setSignCertificate($signingCertificatePath, $signingPrivateKeyPath, $signingOptions, $extraCerts);

		$this->getSwiftMessage()->attachSigner($signer);

        return $this;
	}
}
