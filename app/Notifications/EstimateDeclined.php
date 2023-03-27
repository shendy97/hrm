<?php

namespace App\Notifications;

use App\Models\Estimate;
use Illuminate\Notifications\Messages\MailMessage;

class EstimateDeclined extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimate;

    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
        $this->company = $this->estimate->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('estimates.show', $this->estimate->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.estimateDeclined.text');

        return parent::build()
            ->subject(__('email.estimateDeclined.subject') . ' - ' . config('app.name') . __('!'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimateDeclined.action'),
                'notifiableName' => $notifiable->name
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */

    // phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'id' => $this->estimate->id,
            'estimate_number' => $this->estimate->estimate_number
        ];
    }

}
