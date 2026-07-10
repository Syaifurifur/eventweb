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
    private string $debug = '';

    public function __construct(private readonly array $config = [])
    {
    }

    public function setFrom(string $email, string $name = ''): self { $this->from = $email; $this->fromName = $name; return $this; }
    public function setTo(string $email): self { $this->to = $email; return $this; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }

    public function send(): bool
    {
        try {
            $host = (string) ($this->config['SMTPHost'] ?? 'localhost');
            $port = (int) ($this->config['SMTPPort'] ?? 587);
            $crypto = strtolower((string) ($this->config['SMTPCrypto'] ?? 'tls'));
            $transport = new EsmtpTransport($host, $port, $crypto === 'ssl');
            if (($this->config['SMTPUser'] ?? '') !== '') $transport->setUsername((string) $this->config['SMTPUser']);
            if (($this->config['SMTPPass'] ?? '') !== '') $transport->setPassword((string) $this->config['SMTPPass']);

            $email = (new Email())
                ->from($this->fromName !== '' ? sprintf('%s <%s>', $this->fromName, $this->from) : $this->from)
                ->to($this->to)
                ->subject($this->subject)
                ->text($this->message);
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
