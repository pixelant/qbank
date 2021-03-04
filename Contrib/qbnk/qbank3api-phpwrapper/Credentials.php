<?php

namespace QBNK\QBank\API;

/**
 * Represents the API callers credentials used to authenticate against QBank.
 */
class Credentials
{
    /** @var string */
    protected $clientId;

    /** @var string */
    protected $username;

    /**
     * @param string $clientId
     * @param string $username
     * @param string $password
     */
    public function __construct($clientId, $username, $password)
    {
        $this->clientId = $clientId;
        $this->username = $username;
        $this->password($password);
    }

    /**
     * Gets or sets the internal value.
     *
     * @internal Hack to hide value from dumping and possibly exposing by mistake.
     *
     * @param string|null $newPassword
     *
     * @return string|null
     */
    protected function password($newPassword = null)
    {
        static $password;
        if (null !== $newPassword) {
            $password = $newPassword;
        }

        return $password;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password();
    }
}
