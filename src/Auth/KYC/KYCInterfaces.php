<?php

namespace Ferrox\Auth\KYC;

enum KYCStatus: string {
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DECLINED = 'declined';
    case NEEDS_REVIEW = 'needs_review';
    case ERROR = 'error';
}

class KYCApplicant {
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $countryIso3
    ) {}
}

class KYCVerificationResult {
    public function __construct(
        public readonly string $applicantId,
        public readonly KYCStatus $status,
        public readonly string $provider,
        public readonly array $rejectionReasons,
        public readonly array $rawData
    ) {}
}

interface KYCProviderInterface {
    public function createApplicant(KYCApplicant $applicant): string;
    public function generateVerificationLink(string $providerApplicantId, string $redirectUrl): string;
    public function checkStatus(string $providerApplicantId): KYCVerificationResult;
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool;
}
