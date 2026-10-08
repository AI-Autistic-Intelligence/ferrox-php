<?php

namespace Ferrox\Auth\OAuth;

class GoogleOAuthProvider implements OAuthProviderInterface {
    
    private string $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    private string $tokenUrl = 'https://oauth2.googleapis.com/token';
    private string $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public function __construct(
        private string $clientId,
        private string $clientSecret
    ) {}

    public function getAuthorizationUrl(string $state, string $redirectUri): string {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        return $this->authUrl . '?' . http_build_query($params);
    }

    public function exchangeCode(string $code, string $redirectUri): string {
        $ch = curl_init($this->tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new \RuntimeException("Failed to exchange token with Google.");
        }
        
        $data = json_decode($response, true);
        return $data['access_token'];
    }

    public function fetchUserProfile(string $accessToken): FerroxIdentity {
        $ch = curl_init($this->userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new \RuntimeException("Failed to fetch user profile from Google.");
        }
        
        $data = json_decode($response, true);
        
        return new FerroxIdentity(
            provider: OAuthProviderType::GOOGLE,
            providerUserId: $data['sub'] ?? '',
            email: $data['email'] ?? null,
            isEmailVerified: $data['email_verified'] ?? false,
            firstName: $data['given_name'] ?? null,
            lastName: $data['family_name'] ?? null,
            avatarUrl: $data['picture'] ?? null,
            rawData: $data
        );
    }
}
