<?php

namespace Footup\Messaging;

/**
 * Mailable — Base class for mailable notifications
 */
abstract class Mailable
{
  protected Mailer $mailer;
  protected string $subject = '';
  protected string $view = '';
  protected string $body = '';
  protected array $data = [];
  protected array $to = [];

  public function __construct()
  {
    $this->mailer = new Mailer();
  }

  /**
   * Get mailer instance (lazy initialization if needed)
   */
  protected function getMailer(): Mailer
  {
    return $this->mailer ??= new Mailer();
  }

  /** @return void */
  abstract public function build();

  /**
   * Send the mailable
   */
  public function send(): bool
  {
    $this->build();

    if (empty($this->to)) {
      throw new \Exception("Recipient (to) not set");
    }

    if (!empty($this->view)) {
      return $this->getMailer()->to($this->to)
        ->subject($this->subject)
        ->view($this->view, $this->data)
        ->send();
    }

    if (!empty($this->body)) {
      return $this->getMailer()->to($this->to)
        ->subject($this->subject)
        ->body($this->body)
        ->send();
    }

    throw new \Exception("View or body must be set");
  }

  public function subject(string $subject): self
  {
    $this->subject = $subject;
    return $this;
  }

  public function view(string $view, array $data = []): self
  {
    $this->view = $view;
    $this->data = $data;
    return $this;
  }

  public function body(string $body): self
  {
    $this->body = $body;
    return $this;
  }

  public function __call(string $name, $arguments)
  {
    if (method_exists($this->mailer, $name) && !str_starts_with($name, 'send')) {
      return $this->mailer->$name(...$arguments);
    }

    throw new \Exception('Undefined method ' . $name . ' called on Mailable');
  }
}
