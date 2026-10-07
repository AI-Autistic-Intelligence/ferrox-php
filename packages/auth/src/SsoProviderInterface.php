<?php
namespace Ferrox\Auth;

/**
 * Interface for connecting various SSO Providers (Google, Azure AD, Okta, etc.)
 */
interface SsoProviderInterface
{
    /**
     * Returns the normalized User Profile by exchanging the OAuth/SAML code.
     */
    public function exchangeCodeForProfile(string $code): array;
}
