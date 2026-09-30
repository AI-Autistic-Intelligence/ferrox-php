<?php
namespace Ferrox\Mailer\Adapters;

use Ferrox\Mailer\MailerInterface;

/**
 * A dummy Mailer adapter that writes emails to standard output/logs.
 * Perfect for local development or CI/CD pipelines without sending real emails.
 */
class LogMailerAdapter implements MailerInterface
{
    public function send(string $to, string $subject, string $htmlBody, array $cc = []): bool
    {
        $log = "\n[MAILER] 📧 Email Sent!\n";
        $log .= "To: {$to}\n";
        $log .= "Subject: {$subject}\n";
        $log .= "Body Length: " . strlen($htmlBody) . " bytes\n";
        
        error_log($log);
        return true;
    }
}
