<?php
namespace TYPO3\CMS\Core\Log;

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
 * Global LogManager that keeps track of global logging information.
 *
 * Inspired by java.util.logging
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @author Steffen Müller <typo3@t3node.com>
 * @author Steffen Gebert <steffen.gebert@typo3.org>
 */
class LogManager implements \TYPO3\CMS\Core\SingletonInterface, LogManagerInterface {

	/**
	 * @var string
	 */
	const CONFIGURATION_TYPE_WRITER = 'writer';

	/**
	 * @var string
	 */
	const CONFIGURATION_TYPE_PROCESSOR = 'processor';

	/**
	 * Loggers to retrieve them for repeated use.
	 *
	 * @var array
	 */
	protected $loggers = array();

	/**
	 * Default / global / root logger.
	 *
	 * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected $rootLogger = NULL;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->rootLogger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Logger::class, '');
		$this->loggers[''] = $this->rootLogger;
	}

	/**
	 * For use in unit test context only. Resets the internal logger registry.
	 *
	 * @return void
	 */
	public function reset() {
		$this->loggers = array();
	}

	/**
	 * Gets a logger instance for the given name.
	 *
	 * \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger('main.sub.subsub');
	 *
	 * $name can also be submitted as a underscore-separated string, which will
	 * be converted to dots. This is useful to call this method with __CLASS__
	 * as parameter.
	 *
	 * @param string $name Logger name, empty to get the global "root" logger.
	 * @return \TYPO3\CMS\Core\Log\Logger Logger with name $name
	 */
	public function getLogger($name = '') {
		/** @var $logger \TYPO3\CMS\Core\Log\Logger */
		$logger = NULL;
		// Transform namespaces and underscore class names to the dot-name style
		$separators = array('_', '\\');
		$name = str_replace($separators, '.', $name);
		if (isset($this->loggers[$name])) {
			$logger = $this->loggers[$name];
		} else {
			// Lazy instantiation
			/** @var $logger \TYPO3\CMS\Core\Log\Logger */
			$logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Logger::class, $name);
			$this->loggers[$name] = $logger;
			$this->setWritersForLogger($logger);
			$this->setProcessorsForLogger($logger);
		}
		return $logger;
	}

	/**
	 * For use in unit test context only.
	 *
	 * @param string $name
	 * @return void
	 */
	public function registerLogger($name) {
		$this->loggers[$name] = NULL;
	}

	/**
	 * For use in unit test context only.
	 *
	 * @return array
	 */
	public function getLoggerNames() {
		return array_keys($this->loggers);
	}

	/**
	 * Appends the writers to the given logger as configured.
	 *
	 * @param \TYPO3\CMS\Core\Log\Logger $logger Logger to configure
	 * @return void
	 */
	protected function setWritersForLogger(Logger $logger) {
		$configuration = $this->getConfigurationForLogger(self::CONFIGURATION_TYPE_WRITER, $logger->getName());
		foreach ($configuration as $severityLevel => $writer) {
			foreach ($writer as $logWriterClassName => $logWriterOptions) {
				/** @var $logWriter \TYPO3\CMS\Core\Log\Writer\WriterInterface */
				$logWriter = NULL;
				try {
					$logWriter = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($logWriterClassName, $logWriterOptions);
					$logger->addWriter($severityLevel, $logWriter);
				} catch (\Psr\Log\InvalidArgumentException $e) {
					$logger->warning('Instantiation of LogWriter "' . $logWriterClassName . '" failed for logger ' . $logger->getName() . ' (' . $e->getMessage() . ')');
				} catch (\TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException $e) {
					$logger->warning('Instantiation of LogWriter "' . $logWriterClassName . '" failed for logger ' . $logger->getName() . ' (' . $e->getMessage() . ')');
				}
			}
		}
	}

	/**
	 * Appends the processors to the given logger as configured.
	 *
	 * @param \TYPO3\CMS\Core\Log\Logger $logger Logger to configure
	 * @return void
	 */
	protected function setProcessorsForLogger(Logger $logger) {
		$configuration = $this->getConfigurationForLogger(self::CONFIGURATION_TYPE_PROCESSOR, $logger->getName());
		foreach ($configuration as $severityLevel => $processor) {
			foreach ($processor as $logProcessorClassName => $logProcessorOptions) {
				/** @var $logProcessor \TYPO3\CMS\Core\Log\Processor\ProcessorInterface */
				$logProcessor = NULL;
				try {
					$logProcessor = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($logProcessorClassName, $logProcessorOptions);
					$logger->addProcessor($severityLevel, $logProcessor);
				} catch (\Psr\Log\InvalidArgumentException $e) {
					$logger->warning('Instantiation of LogProcessor "' . $logProcessorClassName . '" failed for logger ' . $logger->getName() . ' (' . $e->getMessage() . ')');
				} catch (\TYPO3\CMS\Core\Log\Exception\InvalidLogProcessorConfigurationException $e) {
					$logger->warning('Instantiation of LogProcessor "' . $logProcessorClassName . '" failed for logger ' . $logger->getName() . ' (' . $e->getMessage() . ')');
				}
			}
		}
	}

	/**
	 * Returns the configuration from $TYPO3_CONF_VARS['LOG'] as
	 * hierarchical array for different components of the class hierarchy.
	 *
	 * @param string $configurationType Type of config to return (writer, processor)
	 * @param string $loggerName Logger name
	 * @throws \Psr\Log\InvalidArgumentException
	 * @return array
	 */
	protected function getConfigurationForLogger($configurationType, $loggerName) {
		// Split up the logger name (dot-separated) into its parts
		$explodedName = explode('.', $loggerName);
		// Search in the $TYPO3_CONF_VARS['LOG'] array
		// for these keys, for example "writerConfiguration"
		$configurationKey = $configurationType . 'Configuration';
		$configuration = $GLOBALS['TYPO3_CONF_VARS']['LOG'];
		$result = $configuration[$configurationKey] ?: array();
		// Walk from general to special (t3lib, t3lib.db, t3lib.db.foo)
		// and search for the most specific configuration
		foreach ($explodedName as $partOfClassName) {
			if (!empty($configuration[$partOfClassName][$configurationKey])) {
				$result = $configuration[$partOfClassName][$configurationKey];
			}
			$configuration = $configuration[$partOfClassName];
		}
		// Validate the config
		foreach ($result as $level => $unused) {
			try {
				LogLevel::validateLevel($level);
			} catch (\Psr\Log\InvalidArgumentException $e) {
				throw new \Psr\Log\InvalidArgumentException('The given severity level "' . htmlspecialchars($level) . '" for ' . $configurationKey . ' of logger "' . $loggerName . '" is not valid.', 1326406447);
			}
		}
		return $result;
	}

}
