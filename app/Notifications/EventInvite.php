<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Messages\MailMessage;

class EventInvite extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->company = $this->event->company;
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
     * @param mixed $notifiable
     * @return MailMessage
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
        $vCalendar = new \Eluceo\iCal\Component\Calendar('www.example.com');
        $vEvent = new \Eluceo\iCal\Component\Event();
        $vEvent
            ->setDtStart(new \DateTime($this->event->start_date_time))
            ->setDtEnd(new \DateTime($this->event->end_date_time))
            ->setNoTime(true)
            ->setSummary(ucfirst($this->event->event_name));
        $vCalendar->addComponent($vEvent);
        $vFile = $vCalendar->render();

        $url = route('events.show', $this->event->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newEvent.text') . '<br>' . __('modules.events.eventName') . ': ' . $this->event->event_name . '<br>' . __('modules.events.startOn') . ': ' . $this->event->start_date_time->format($this->company->date_format . ' - ' . $this->company->time_format) . '<br>' . __('modules.events.endOn') . ': ' . $this->event->end_date_time->format($this->company->date_format . ' - ' . $this->company->time_format);


        $eventInvite = parent::build();
        $eventInvite->subject(__('email.newEvent.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newEvent.action'),
                'notifiableName' => $notifiable->name
            ]);
            
        $eventInvite->attachData($vFile, 'cal.ics', [
                'mime' => 'text/calendar',
            ]);

        return $eventInvite;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
//phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'id' => $this->event->id,
            'start_date_time' => $this->event->start_date_time->format('Y-m-d H:i:s'),
            'event_name' => $this->event->event_name
        ];
    }

}
