<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Invoice;
use App\Models\Payment;

class InvoicePaymentReceived extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $payment;

    private $invoiceSetting;

    private $emailSetting;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->company = $this->payment->company;
        $this->invoiceSetting = $this->company->invoiceSetting;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'invoice-createupdate-notification')->first();

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

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
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

        $invoice = Invoice::findOrFail($this->payment->invoice_id);

        if ($invoice->order_id != null) {
            $number = __('app.order') . '#' . $invoice->order_id;
            $message = __('email.invoices.paymentReceivedForOrder');
            $url = route('orders.show', $invoice->order_id);
            $actionBtn = __('email.orders.action');

        }
        else {
            $number = $invoice->invoice_number;
            $message = __('email.invoices.paymentReceivedForInvoice');
            $url = route('invoices.show', $invoice->id);
            $actionBtn = __('email.invoices.action');
        }

        $url = getDomainSpecificUrl($url, $this->company);

        $content = $message . ':- ' . '<br>' . $number;

        return parent::build()
            ->subject(__('email.invoices.paymentReceived') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => $actionBtn,
                'notifiableName' => $notifiable->name
            ]);
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

        $invoice = Invoice::find($this->payment->invoice_id);

        if ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ];
        }

        return '';
    }

}
