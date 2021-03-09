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
 *   - APP_QBANK_HOSTURL
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
     * QBank host URL
     *
     * @var string
     */
    protected string $hostUrl;

    /**
     * Base URL for QBank GUI
     *
     * @var string
     */
    protected string $guiBaseUrl;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $configuration = $extensionConfiguration->get('qbank');

        $this->clientId = getenv('APP_QBANK_CLIENTID') ?? $configuration['clientId'];
        $this->username = getenv('APP_QBANK_USERNAME') ?? $configuration['username'];
        $this->password = getenv('APP_QBANK_PASSWORD') ?? $configuration['password'];
        $this->hostUrl = getenv('APP_QBANK_HOSTURL') ?? $configuration['hostUrl'];
        $this->guiBaseUrl = getenv('APP_QBANK_GUIBASEURL') ?? $configuration['guiBaseUrl'];
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
    public function getHostUrl(): string
    {
        return $this->hostUrl;
    }

    /**
     * @param string $hostUrl
     */
    public function setHostUrl(string $hostUrl)
    {
        $this->hostUrl = $hostUrl;
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
}
