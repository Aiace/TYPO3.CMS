<?php
namespace TYPO3\CMS\Extbase\Validation\Validator;

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
 * Contract for a validator
 *
 * @api
 */
interface ValidatorInterface {

	/**
	 * Constructs the validator and sets validation options
	 *
	 * @param array $options The validation options
	 * @api
	 */
	//public function __construct(array $options = array());

	/**
	 * Checks if the given value is valid according to the validator, and returns
	 * the Error Messages object which occurred.
	 *
	 * @param mixed $value The value that should be validated
	 * @return \TYPO3\CMS\Extbase\Error\Result
	 * @api
	 */
	public function validate($value);

	/**
	 * Returns the options of this validator which can be specified in the constructor
	 *
	 * @return array
	 */
	public function getOptions();
}
