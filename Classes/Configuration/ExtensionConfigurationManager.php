<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Configuration;

use InvalidArgumentException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration manager for QBank. Fetches configuration from environment variables with fallback to extension
 * configuration.
 *
 * Environment variables:
 *
 *   - APP_QBANK_CLIENTID
 *   - APP_QBANK_USERNAME
 *   - APP_QBANK_PASSWORD
 *   - APP_QBANK_HOST
 *   - APP_QBANK_DOWNLOADFOLDER
 *   - APP_QBANK_SESSIONSOURCE
 *   - APP_QBANK_DEPLOYMENTSITES
 */
class ExtensionConfigurationManager implements SingletonInterface
{
    /**
     * QBank client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * QBank username.
     *
     * @var string
     */
    protected $username;

    /**
     * QBank password.
     *
     * @var string
     */
    protected $password;

    /**
     * QBank host domain.
     *
     * @var string
     */
    protected $host;

    /**
     * Local target folder, for example "1:user_upload/qbank".
     *
     * @var string
     */
    protected $downloadFolder;

    /**
     * @var int
     */
    protected $sessionSource;

    /**
     * @var array
     */
    protected $deploymentSites;

    /**
     * @var int
     */
    protected $autoUpdate;

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * ExtensionConfigurationManager constructor.
     * @param ExtensionConfiguration $extensionConfiguration
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $configuration = $this->extensionConfiguration->get('qbank');

        $this->clientId = getenv('APP_QBANK_CLIENTID') ?: (string)$configuration['clientId'];
        $this->username = getenv('APP_QBANK_USERNAME') ?: (string)$configuration['username'];
        $this->password = getenv('APP_QBANK_PASSWORD') ?: (string)$configuration['password'];
        $this->host = getenv('APP_QBANK_HOST') ?: (string)$configuration['host'];
        $this->downloadFolder = getenv('APP_QBANK_DOWNLOADFOLDER') ?: (string)$configuration['downloadFolder'];
        $this->autoUpdate = (int)$configuration['autoUpdate'] ?? 0;
        $this->sessionSource = (int)(getenv('APP_QBANK_SESSIONSOURCE') ?: $configuration['sessionSource']);
        $this->deploymentSites = GeneralUtility::intExplode(
            ',',
            getenv('APP_QBANK_DEPLOYMENTSITES') ?: (string)$configuration['deploymentSites'],
            true
        );
    }

    /**
     * Add page-specific configuration.
     *
     * @param int $pageId
     * @param int $languageId
     */
    public function configureForPage(int $pageId, int $languageId = 0): void
    {
        try {
            $this->configureForSite(
                GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId),
                $languageId
            );
            // phpcs:ignore
        } catch (SiteNotFoundException $exception) {
            // If there is no site for this page, we can't fetch site configuration.
        }
    }

    /**
     * Add site-specific configuration.
     *
     * @param Site $site
     * @param int $languageId
     */
    public function configureForSite(Site $site, int $languageId = 0): void
    {
        $language = null;

        try {
            $language = $site->getLanguageById($languageId);
            // phpcs:ignore
        } catch (InvalidArgumentException $exception) {
            // If we can't get the language, we can't configure for it.
        }

        if ($language !== null) {
            $languageConfiguration = $language->toArray();

            if ($languageConfiguration['qbank_deploymentSites']) {
                $this->deploymentSites = GeneralUtility::intExplode(
                    ',',
                    $languageConfiguration['qbank_deploymentSites'],
                    true
                );

                return;
            }
        }

        if ($site->getConfiguration()['qbank_deploymentSites']) {
            $this->deploymentSites = GeneralUtility::intExplode(
                ',',
                $site->getConfiguration()['qbank_deploymentSites'],
                true
            );
        }
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getDownloadFolder(): string
    {
        return $this->downloadFolder;
    }

    /**
     * @param string $downloadFolder
     */
    public function setDownloadFolder(string $downloadFolder): void
    {
        $this->downloadFolder = $downloadFolder;
    }

    /**
     * @return int
     */
    public function getSessionSource(): int
    {
        return $this->sessionSource;
    }

    /**
     * @param int $sessionSource
     */
    public function setSessionSource(int $sessionSource): void
    {
        $this->sessionSource = $sessionSource;
    }

    /**
     * @return array
     */
    public function getDeploymentSites(): array
    {
        return $this->deploymentSites;
    }

    /**
     * @param array $deploymentSites
     */
    public function setDeploymentSites(array $deploymentSites): void
    {
        $this->deploymentSites = $deploymentSites;
    }

    /**
     * Get the value of autoUpdate.
     *
     * @return int
     */
    public function getAutoUpdate()
    {
        return $this->autoUpdate;
    }

    /**
     * Set the value of autoUpdate.
     *
     * @param int $autoUpdate
     *
     * @return void
     */
    public function setAutoUpdate(int $autoUpdate): void
    {
        $this->autoUpdate = $autoUpdate;
    }
}
