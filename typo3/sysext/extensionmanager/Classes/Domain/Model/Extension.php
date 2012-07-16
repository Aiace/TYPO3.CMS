<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * Main extension model
 *
 * @author Susanne Moog <typo3@susannemoog.de>
 * @package Extension Manager
 * @subpackage Model
 */
class Tx_Extensionmanager_Domain_Model_Extension extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * Contains default categories.
	 *
	 * @var  array
	 */
	protected static $defaultCategories = array(
		0 => 'be',
		1 => 'module',
		2 => 'fe',
		3 => 'plugin',
		4 => 'misc',
		5 => 'services',
		6 => 'templates',
		8 => 'doc',
		9 => 'example'
	);

	/**
	 * Contains default states.
	 *
	 * @var  array
	 */
	protected static $defaultStates = array(
		0 => 'alpha',
		1 => 'beta',
		2 => 'stable',
		3 => 'experimental',
		4 => 'test',
		5 => 'obsolete',
		6 => 'excludeFromUpdates',
		999 => 'n/a'
	);


	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var string
	 */
	protected $extensionKey = '';

	/**
	 * @var string
	 */
	protected $version = '';

	/**
	 * @var int
	 */
	protected $integerVersion = 0;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var int
	 */
	protected $state = 0;

	/**
	 * @var int
	 */
	protected $category = 0;

	/**
	 * @var DateTime
	 */
	protected $lastUpdated;

	/**
	 * @var string
	 */
	protected $updateComment = '';

	/**
	 * @var string
	 */
	protected $authorName = '';

	/**
	 * @var string
	 */
	protected $authorEmail = '';

	/**
	 * @var boolean
	 */
	protected $currentVersion = FALSE;

	/**
	 * @var string
	 */
	protected $md5hash = '';

	/**
	 * @var string
	 */
	protected $serializedDependencies = '';

	/**
	 * @var SplObjectStorage<Tx_Extensionmanager_Utility_Dependency>
	 */
	protected $dependencies = NULL;


	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param string $authorEmail
	 * @return void
	 */
	public function setAuthorEmail($authorEmail) {
		$this->authorEmail = $authorEmail;
	}

	/**
	 * @return string
	 */
	public function getAuthorEmail() {
		return $this->authorEmail;
	}

	/**
	 * @param string $authorName
	 * @return void
	 */
	public function setAuthorName($authorName) {
		$this->authorName = $authorName;
	}

	/**
	 * @return string
	 */
	public function getAuthorName() {
		return $this->authorName;
	}

	/**
	 * @param int $category
	 * @return void
	 */
	public function setCategory($category) {
		$this->category = $category;
	}

	/**
	 * @return int
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * Get Category String
	 *
	 * @return string
	 */
	public function getCategoryString() {
		$categoryString = '';
		if (isset(self::$defaultCategories[$this->getCategory()])) {
			$categoryString = self::$defaultCategories[$this->getCategory()];
		}
		return $categoryString;
	}

	/**
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $extensionKey
	 * @return void
	 */
	public function setExtensionKey($extensionKey) {
		$this->extensionKey = $extensionKey;
	}

	/**
	 * @return string
	 */
	public function getExtensionKey() {
		return $this->extensionKey;
	}

	/**
	 * @param DateTime $lastUpdated
	 * @return void
	 */
	public function setLastUpdated(DateTime $lastUpdated) {
		$this->lastUpdated = $lastUpdated;
	}

	/**
	 * @return DateTime
	 */
	public function getLastUpdated() {
		return $this->lastUpdated;
	}

	/**
	 * @param int $state
	 * @return void
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Get State string
	 *
	 * @return string
	 */
	public function getStateString() {
		$stateString = '';
		if (isset(self::$defaultStates[$this->getState()])) {
			$stateString = self::$defaultStates[$this->getState()];
		}
		return $stateString;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $updateComment
	 * @return void
	 */
	public function setUpdateComment($updateComment) {
		$this->updateComment = $updateComment;
	}

	/**
	 * @return string
	 */
	public function getUpdateComment() {
		return $this->updateComment;
	}

	/**
	 * @param string $version
	 * @return void
	 */
	public function setVersion($version) {
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @param boolean $currentVersion
	 * @return void
	 */
	public function setCurrentVersion($currentVersion) {
		$this->currentVersion = $currentVersion;
	}

	/**
	 * @return boolean
	 */
	public function getCurrentVersion() {
		return $this->currentVersion;
	}

	/**
	 * @param string $md5hash
	 * @return void
	 */
	public function setMd5hash($md5hash) {
		$this->md5hash = $md5hash;
	}

	/**
	 * @return string
	 */
	public function getMd5hash() {
		return $this->md5hash;
	}

	public static function returnInstallPaths() {
		return array(
			'System' => PATH_typo3 . 'sysext/',
			'Global' => PATH_typo3 . 'ext/',
			'Local' => PATH_typo3conf . 'ext/'
		);
	}

	/**
	 * @param string $dependencies
	 * @return void
	 */
	public function setSerializedDependencies($dependencies) {
		$this->serializedDependencies = $dependencies;
	}

	/**
	 * @return string
	 */
	public function getSerializedDependencies() {
		return $this->serializedDependencies;
	}

	/**
	 * @param SplObjectStorage $dependencies
	 * @return void
	 */
	public function setDependencies($dependencies) {
		$this->dependencies = $dependencies;
	}

	/**
	 * @return SplObjectStorage
	 */
	public function getDependencies() {
		if (!is_object($this->dependencies)) {
			/** @var $dependencyUtility Tx_Extensionmanager_Utility_Dependency */
			$dependencyUtility = $this->objectManager->get('Tx_Extensionmanager_Utility_Dependency');
			$this->setDependencies($dependencyUtility->convertDependenciesToObjects($this->getSerializedDependencies()));
		}
		return $this->dependencies;
	}

	/**
	 * @param Tx_Extensionmanager_Domain_Model_Dependency $dependency
	 * @return void
	 */
	public function addDependency(Tx_Extensionmanager_Domain_Model_Dependency $dependency) {
		$this->dependencies->attach($dependency);
	}

	/**
	 * @param int $integerVersion
	 * @return void
	 */
	public function setIntegerVersion($integerVersion) {
		$this->integerVersion = $integerVersion;
	}

	/**
	 * @return int
	 */
	public function getIntegerVersion() {
		return $this->integerVersion;
	}
}

?>