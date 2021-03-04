<?php namespace QBNK\GuzzleOAuth2\GrantType;

use QBNK\GuzzleOAuth2\TokenData;
use QBNK\GuzzleOAuth2\Signer\ClientCredentials\SignerInterface;

interface GrantTypeInterface
{
    /**
     * Get the token data returned by the OAuth2 server.
     *
     * @return TokenData
     */
    public function getTokenData(SignerInterface $clientCredentialsSigner);
}
