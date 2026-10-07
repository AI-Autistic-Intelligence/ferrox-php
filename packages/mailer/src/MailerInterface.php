<?php
namespace Ferrox\Mailer;

/**
 * Universal Mailer Interface for sending transactional emails and alerts.
 */
interface MailerInterface
{
    /**
     * Send an email.
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $htmlBody HTML content of the email
     * @param array $cc Optional CC addresses
     */
    public function send(string $to, string $subject, string $htmlBody, array $cc = []): bool;
}
