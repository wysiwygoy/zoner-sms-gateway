<?php

namespace NotificationChannels\ZonerSmsGateway;

use Illuminate\Notifications\Notification;
use NotificationChannels\ZonerSmsGateway\Exceptions\CouldNotSendNotification;

class ZonerSmsGatewayChannel
{
    /**
     * @var ZonerSmsGateway
     */
    protected $gateway;

    public function __construct(ZonerSmsGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return tracking number
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toZonerSmsGateway($notifiable);

        if (is_string($message)) {
            $message = new ZonerSmsGatewayMessage($message);
        }

        // Use the receiver from message, if defined:
        $receiver = $message->receiver;

        // Otherwise use the receiver given by notifiable:
        if (empty($receiver)) {
            $receiver = $notifiable->routeNotificationFor(ZonerSmsGatewayChannel::class);
        }

        // As the last resort, try to get the phone_number attribute from notifiable:
        if (empty($receiver) && isset($notifiable->phone_number)) {
            $receiver = $notifiable->phone_number;
        }

        if (empty($receiver)) {
            throw CouldNotSendNotification::receiverNotProvided();
        }

        return $this->gateway->sendMessage($receiver, trim($message->content), $message->sender);
    }
}
