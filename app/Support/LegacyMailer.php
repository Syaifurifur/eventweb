<?php

namespace App\Support;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

final class LegacyMailer
{
    private string $from = '';
    private string $fromName = '';
    private string $to = '';
    private string $subject = '';
    private string $message = '';
    private string $altMessage = '';
    private string $mailType = 'text';
    private string $debug = '';

    public function __construct(private readonly array $config = [])
    {
        if (! empty($this->config['mailType'])) {
            $this->mailType = (string) $this->config['mailType'];
        }
    }

    public function setFrom(string $email, string $name = ''): self { $this->from = $email; $this->fromName = $name; return $this; }
    public function setTo(string $email): self { $this->to = $email; return $this; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }
    public function setAltMessage(string $message): self { $this->altMessage = $message; return $this; }
    public function setMailType(string $type): self { $this->mailType = $type; return $this; }

    public function send(bool $autoClear = true): bool
    {
        try {
            $host = trim((string) ($this->config['SMTPHost'] ?? ''));
            if ($host === '') {
                $host = trim((string) config('mail.mailers.smtp.host', '127.0.0.1'));
            }

            $port = (int) ($this->config['SMTPPort'] ?? 0);
            if ($port <= 0) {
                $port = (int) config('mail.mailers.smtp.port', 587);
            }

            $crypto = strtolower(trim((string) ($this->config['SMTPCrypto'] ?? '')));
            if ($crypto === '') {
                $crypto = strtolower(trim((string) config('mail.mailers.smtp.scheme', 'tls')));
            }

            $username = trim((string) ($this->config['SMTPUser'] ?? ''));
            if ($username === '') {
                $username = trim((string) config('mail.mailers.smtp.username', ''));
            }

            $password = (string) ($this->config['SMTPPass'] ?? '');
            if ($password === '') {
                $password = (string) config('mail.mailers.smtp.password', '');
            }

            $fromAddress = trim($this->from);
            if ($fromAddress === '' || $fromAddress === 'noreply@eventhub.local') {
                $configuredFrom = trim((string) config('mail.from.address', ''));
                if ($configuredFrom !== '' && $configuredFrom !== 'hello@example.com') {
                    $fromAddress = $configuredFrom;
                } elseif ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $fromAddress = $username;
                }
            }
            $fromName = trim($this->fromName) !== '' ? trim($this->fromName) : (string) config('mail.from.name', 'EventHub');

            $transport = new EsmtpTransport($host, $port, $crypto === 'ssl' || $crypto === 'smtps');
            if ($username !== '') {
                $transport->setUsername($username);
            }
            if ($password !== '') {
                $transport->setPassword($password);
            }

            $isHtml = ($this->mailType === 'html') || str_contains($this->message, '<html') || str_contains($this->message, '<table');

            $email = (new Email())
                ->from($fromName !== '' ? sprintf('%s <%s>', $fromName, $fromAddress) : $fromAddress)
                ->to($this->to)
                ->subject($this->subject);

            if ($isHtml) {
                $email->html($this->message);
                if ($this->altMessage !== '') {
                    $email->text($this->altMessage);
                }
            } else {
                $email->text($this->message);
            }

            (new Mailer($transport))->send($email);
            return true;
        } catch (\Throwable $error) {
            $this->debug = $error->getMessage();
            return false;
        }
    }

    public function printDebugger(array $include = []): string
    {
        return $this->debug;
    }
}
