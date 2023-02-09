<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->url = config('app.url') . '/api/verify/' . $token;
        $this->subject = "Подтверждение регистрации пользователя";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_USERNAME'), config('app.name'))
                    ->markdown('mail.verify')
                    ->subject($this->subject)
                    ->view('emails.orders.new')
                    ->with(['url' => $this->url]);
    }
}
