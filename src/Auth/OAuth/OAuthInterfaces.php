<?php

namespace Ferrox\Auth\OAuth;

enum OAuthProviderType: string {
    case GOOGLE = 'google';
    case GITHUB = 'github';
    case APPLE = 'apple';
    case MICROSOFT = 'microsoft';
}

class FerroxIdentity {
    public function __construct(
        public readonly OAuthProviderType $provider,
        public readonly string $providerUserId,
        public readonly ?string $email,
        public readonly bool $isEmailVerified,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $avatarUrl = null,
        public readonly array $rawData = []
    ) {}
}

interface OAuthProviderInterface {
    public function getAuthorizationUrl(string $state, string $redirectUri): string;
    public function exchangeCode(string $code, string $redirectUri): string;
    public function fetchUserProfile(string $accessToken): FerroxIdentity;
}
