<?php

namespace Buzzex\Notifications;

use Buzzex\Models\ExchangeOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderFulfilledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var $order
     */
    protected $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ExchangeOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->generateMessage(),
            'sender' => 'System'
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
           'message' => $this->generateMessage(),
           'sender' => 'System'
        ]);
    }

    /**
     * Generate a message for notification.
     * @return string|null
     */
    private function generateMessage()
    {
        $fullfilled_percentage = $this->order->getFulfilledPercentage();
        
        if ($this->order->isCancelled() || $fullfilled_percentage == 0) {
            return '';
        }
        
        if ($this->order->isFulfilled()) {
            return "Your ".strtolower($this->order->type)." order for ".str_replace('_', '/', $this->order->pairStat->pair_text)." has been filled.";
        }

        return "Your ".strtolower($this->order->type)." order for ".str_replace('_', '/', $this->order->pairStat->pair_text)." has been filled: ".currency($fullfilled_percentage, 2)."%";
    }
}
