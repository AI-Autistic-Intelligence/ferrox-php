<?php
namespace Ferrox\Mailer;

use Ferrox\Mailer\Adapters\LogMailerAdapter;
// use Ferrox\Mailer\Adapters\SmtpMailerAdapter;
// use Ferrox\Mailer\Adapters\AwsSesMailerAdapter;

class MailerFactory
{
    public static function create(): MailerInterface
    {
        $driver = getenv('FERROX_MAILER_DRIVER') ?: 'log';

        return match (strtolower($driver)) {
            // 'smtp' => new SmtpMailerAdapter(...),
            // 'ses'  => new AwsSesMailerAdapter(...),
            default => new LogMailerAdapter(),
        };
    }
}
