<?php
namespace TYPO3\CMS\Core\Mail;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Mailer Adapter interface
 *
 * @author Ingo Renner <ingo@typo3.org>
 */
interface MailerAdapterInterface {

	/**
	 * Mail sending function
	 *
	 * @param string $to Mail recipient.
	 * @param string $subject Mail subject.
	 * @param string $messageBody Mail body.
	 * @param array $additionalHeaders Additional mail headers.
	 * @param array $additionalParameters Additional mailer parameters.
	 * @param bool $fakeSending Whether to fake sending or not, used in Unit Tests.
	 * @return bool TRUE if the mail was successfully sent, FALSE otherwise.
	 */
	public function mail($to, $subject, $messageBody, $additionalHeaders = NULL, $additionalParameters = NULL, $fakeSending = FALSE);

}
