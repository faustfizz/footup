<?php

declare(strict_types=1);

namespace Footup\Messaging;

use Footup\Monitoring\Log;
use Footup\Queuing\Queue;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/*
|--------------------------------------------------------------------------
| Mailer — Email sending with templates, attachments, queueing
|
| Configuration in .env:
|   MAIL_DRIVER=smtp
|   MAIL_HOST=smtp.mailtrap.io
|   MAIL_PORT=465
|   MAIL_FROM=noreply@example.com
|   MAIL_FROM_NAME=MyApp
|   MAIL_USERNAME=your_username
|   MAIL_PASSWORD=your_password
|   MAIL_ENCRYPTION=tls
|
| Usage:
|   Mailer::create('user@example.com')
|       ->subject('Welcome!')
|       ->body('Hello!')
|       ->send();
|
|   Mailer::create('user@example.com')
|       ->subject('Welcome!')
|       ->view('emails.welcome', ['name' => 'John'])
|       ->cc('admin@example.com')
|       ->attach('/path/to/file.pdf')
|       ->send();
|
|   Mailer::create('user@example.com')->queue();
|--------------------------------------------------------------------------
*/

class Mailer
{
    private array $to = [];
    private array $cc = [];
    private array $bcc = [];
    private string $subject = '';
    private string $body = '';
    private string $view = '';
    private array $data = [];
    private array $attachments = [];
    private string $driver = 'smtp';
    private array $config = [];
    private bool $isHtml = true;

    public function __construct()
    {
        $this->driver = env('MAIL_DRIVER', 'smtp');
        $this->config = [
            'host' => env('MAIL_HOST', 'localhost'),
            'port' => (int)env('MAIL_PORT', 587),
            'from' => env('MAIL_FROM', 'noreply@example.com'),
            'from_name' => env('MAIL_FROM_NAME', 'Application'),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        ];
    }

    /**
     * Static factory method
     */
    public static function create(string|array $email): self
    {
        $instance = new self();
        $instance->to = is_array($email) ? $email : [$email];
        return $instance;
    }

    /**
     * Set recipient(s)
     */
    public function to(string|array $email): self
    {
        $this->to = is_array($email) ? $email : [$email];
        return $this;
    }

    /**
     * Add recipient
     */
    public function addTo(string $email): self
    {
        $this->to[] = $email;
        return $this;
    }

    /**
     * Set CC recipients
     */
    public function cc(string|array $email): self
    {
        $this->cc = is_array($email) ? $email : [$email];
        return $this;
    }

    /**
     * Set BCC recipients
     */
    public function bcc(string|array $email): self
    {
        $this->bcc = is_array($email) ? $email : [$email];
        return $this;
    }

    /**
     * Set email subject
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set email body (HTML by default)
     */
    public function body(string $body, bool $html = true): self
    {
        $this->body = $body;
        $this->isHtml = $html;
        $this->view = '';
        return $this;
    }

    /**
     * Set email body as plain text
     */
    public function text(string $text): self
    {
        return $this->body($text, false);
    }

    /**
     * Set email from view/template
     */
    public function view(string $view, array $data = []): self
    {
        $this->view = $view;
        $this->data = $data;
        $this->body = '';
        return $this;
    }

    /**
     * Attach file
     */
    public function attach(string $path, string $name = ''): self
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: $path");
        }

        $this->attachments[] = [
            'path' => $path,
            'name' => $name ?: basename($path),
        ];

        return $this;
    }

    /**
     * Send email
     */
    public function send(): bool
    {
        if (empty($this->to) || empty($this->subject) || (empty($this->body) && empty($this->view))) {
            Log::error('Mailer: Missing required fields (to, subject, body/view)');
            return false;
        }

        try {
            if ($this->driver === 'file') {
                return $this->sendToFile();
            }

            return $this->sendViaSMTP();
        } catch (\Exception $e) {
            Log::error("Mail error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send via SMTP
     */
    private function sendViaSMTP(): bool
    {
        // Check if PHPMailer is available
        if (!class_exists(PHPMailer::class)) {
            return $this->sendViaPhpMail();
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            if ($this->config['host'] !== 'localhost') {
                $mail->isSMTP();
                $mail->Host = $this->config['host'];
                $mail->Port = $this->config['port'];
                $mail->SMTPSecure = $this->config['encryption'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->config['username'];
                $mail->Password = $this->config['password'];
            }

            // Set sender
            $mail->setFrom($this->config['from'], $this->config['from_name']);

            // Add recipients
            foreach ($this->to as $recipient) {
                $mail->addAddress(trim($recipient));
            }

            // Add CC
            foreach ($this->cc as $recipient) {
                $mail->addCC(trim($recipient));
            }

            // Add BCC
            foreach ($this->bcc as $recipient) {
                $mail->addBCC(trim($recipient));
            }

            // Set subject and body
            $mail->Subject = $this->subject;
            $mail->isHTML($this->isHtml);
            $mail->Body = $this->getBody();
            $mail->AltBody = strip_tags($this->getBody());

            // Add attachments
            foreach ($this->attachments as $attachment) {
                $mail->addAttachment($attachment['path'], $attachment['name']);
            }

            return $mail->send();
        } catch (PHPMailerException $e) {
            Log::error("PHPMailer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send via PHP mail() function
     */
    private function sendViaPhpMail(): bool
    {
        $headers = "From: " . $this->config['from_name'] . " <" . $this->config['from'] . ">\r\n";
        $headers .= "Reply-To: " . $this->config['from'] . "\r\n";

        if (!empty($this->cc)) {
            $headers .= "Cc: " . implode(', ', $this->cc) . "\r\n";
        }

        if (!empty($this->bcc)) {
            $headers .= "Bcc: " . implode(', ', $this->bcc) . "\r\n";
        }

        if ($this->isHtml) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }

        $to = implode(', ', $this->to);
        $body = $this->getBody();

        return mail($to, $this->subject, $body, $headers);
    }

    /**
     * Save email to file (for development)
     */
    private function sendToFile(): bool
    {
        $timestamp = date('Y-m-d_H-i-s');
        $toStr = implode(', ', $this->to);
        $filename = "storage/emails/{$timestamp}_" . md5($toStr) . ".txt";

        $content = "To: " . $toStr . "\n";
        if (!empty($this->cc)) {
            $content .= "Cc: " . implode(', ', $this->cc) . "\n";
        }
        if (!empty($this->bcc)) {
            $content .= "Bcc: " . implode(', ', $this->bcc) . "\n";
        }
        $content .= "Subject: " . $this->subject . "\n";
        $content .= "---\n";
        $content .= $this->getBody() . "\n";

        @mkdir(dirname($filename), 0755, true);
        return (bool)file_put_contents($filename, $content);
    }

    /**
     * Get email body (from view or plain text)
     */
    private function getBody(): string
    {
        if ($this->view) {
            return $this->renderView();
        }

        return $this->body;
    }

    /**
     * Render view template
     */
    private function renderView(): string
    {
        $viewPath = "resources/views/" . str_replace('.', '/', $this->view) . ".php";

        if (!file_exists($viewPath)) {
            throw new \Exception("Email view not found: {$this->view}");
        }

        extract($this->data);
        ob_start();
        include $viewPath;
        return ob_get_clean() ?: '';
    }

    /**
     * Queue email for later sending via the framework's Queue system
     * Usage: Mailer::create('user@example.com')->queue()
     * Processing: php bin/console queue:work
     */
    public function queue(): bool
    {
        try {
            $jobData = [
                'to' => $this->to,
                'cc' => $this->cc,
                'bcc' => $this->bcc,
                'subject' => $this->subject,
                'body' => !empty($this->body) ? $this->body : null,
                'view' => !empty($this->view) ? $this->view : null,
                'data' => !empty($this->data) ? $this->data : null,
                'attachments' => !empty($this->attachments) ? $this->attachments : null,
            ];

            Queue::push('App\\Jobs\\SendEmailJob', $jobData);
            Log::info("Email queued for " . implode(', ', $this->to));
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to queue email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get configuration
     */
    public static function config(): array
    {
        return (new self())->config;
    }

    /**
     * Forward method calls to Mailer
     */
    public static function __callStatic(string $method, array $arguments)
    {
      if (method_exists(self::class, $method)) {
        return (new self())->$method(...$arguments);
      }
    }
}
