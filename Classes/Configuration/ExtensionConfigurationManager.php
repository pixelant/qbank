<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Configuration;


use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;

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
 *   - APP_QBANK_GUIBASEURL
 */
class ExtensionConfigurationManager implements SingletonInterface
{
    /**
     * QBank client ID
     *
     * @var string
     */
    protected string $clientId;

    /**
     * QBank username
     *
     * @var string
     */
    protected string $username;

    /**
     * QBank password
     *
     * @var string
     */
    protected string $password;

    /**
     * QBank host domain
     *
     * @var string
     */
    protected string $host;

    /**
     * Local target folder, for example "1:user_upload/qbank"
     *
     * @var string
     */
    protected string $downloadFolder;

    /**
     * Base URL for QBank GUI
     *
     * @var string
     */
    protected string $guiBaseUrl;

    /**
     * @var int
     */
    protected int $sessionSource;

    /**
     * ExtensionConfigurationManager constructor.
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $configuration = $extensionConfiguration->get('qbank');

        $this->clientId = getenv('APP_QBANK_CLIENTID') ?: (string)$configuration['clientId'];
        $this->username = getenv('APP_QBANK_USERNAME') ?: (string)$configuration['username'];
        $this->password = getenv('APP_QBANK_PASSWORD') ?: (string)$configuration['password'];
        $this->host = getenv('APP_QBANK_HOST') ?: (string)$configuration['host'];
        $this->guiBaseUrl = getenv('APP_QBANK_GUIBASEURL') ?: (string)$configuration['guiBaseUrl'];
        $this->downloadFolder = getenv('APP_QBANK_DOWNLOADFOLDER') ?: (string)$configuration['downloadFolder'];
        $this->sessionSource = (int)(getenv('APP_QBANK_SESSIONSOURCE') ?: $configuration['sessionSource']);
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
    public function setClientId(string $clientId)
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
    public function setUsername(string $username)
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
    public function setPassword(string $password)
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
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getGuiBaseUrl(): string
    {
        return $this->guiBaseUrl;
    }

    /**
     * @param string $guiBaseUrl
     */
    public function setGuiBaseUrl(string $guiBaseUrl)
    {
        $this->guiBaseUrl = $guiBaseUrl;
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
    public function setDownloadFolder(string $downloadFolder)
    {
        $this->downloadFolder = $downloadFolder;
    }

    /**
     * @return int
     */
    public function getSessionSource()
    {
        return $this->sessionSource;
    }

    /**
     * @param int $sessionSource
     */
    public function setSessionSource(int $sessionSource)
    {
        $this->sessionSource = $sessionSource;
    }
}
