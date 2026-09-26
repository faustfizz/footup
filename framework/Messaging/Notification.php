<?php

declare(strict_types=1);

namespace Footup\Messaging;

use Footup\Orm\DB;
use Footup\Orm\Model;

/*
|--------------------------------------------------------------------------
| Notification — send via mail, db, or custom channels (minimaliste)
|
| Usage:
|   class OrderShipped extends Notification {
|       public function via() { return ['mail', 'database']; }
|       public function toMail() { return Mail::to(...); }
|       public function toDatabase() { return ['message' => 'Order shipped!']; }
|   }
|
| Send: $user->notify(new OrderShipped($order));
|       Notification::route('mail', 'admin@app.com')->send(new OrderShipped());
|--------------------------------------------------------------------------
*/

abstract class Notification
{
    abstract public function via(): array;

    public function toMail()
    {
        return null;
    }

    public function toDatabase(): array
    {
        return [];
    }

    public function toSlack(): string
    {
        return '';
    }

    /**
     * Send notification through specified channels
     */
    final public function send(Model $notifiable = null): bool
    {
        $channels = $this->via();
        $success = true;

        foreach ($channels as $channel) {
            if ($channel === 'mail' && method_exists($this, 'toMail')) {
                $mail = $this->toMail();

                if ($mail) {
                    $mail->send();
                }
            } elseif ($channel === 'database' && method_exists($this, 'toDatabase')) {
                $data = $this->toDatabase();

                if (!empty($data) && $notifiable) {
                    DB::table('notifications')->insert([
                        'notifiable_id' => $notifiable->id,
                        'notifiable_type' => get_class($notifiable),
                        'type' => get_class($this),
                        'data' => json_encode($data),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } elseif ($channel === 'slack' && method_exists($this, 'toSlack')) {
                // Simple webhook call
                $message = $this->toSlack();

                if ($message) {
                    $webhook = env('SLACK_WEBHOOK');

                    if ($webhook) {
                        file_get_contents($webhook, false, stream_context_create([
                            'http' => [
                                'method' => 'POST',
                                'header' => 'Content-Type: application/json',
                                'content' => json_encode(['text' => $message]),
                            ],
                        ]));
                    }
                }
            }
        }

        return $success;
    }
}
