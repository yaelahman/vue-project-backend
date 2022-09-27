<?php

namespace App\Mail;

header_remove("Access-Control-Allow-Origin");
// header("Access-Control-Allow-Origin", "*");

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ChangeMail extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $details;
    public $subject;
    public $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
        $this->subject = $details['subject'];
        $this->token = $details['token'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->view('mails.change', $this->details);
        // ->introLines("OKE")
        // ->line(Lang::get(' '))
        // ->action(
        //     Lang::get('Verify Email Address'),
        //     $this->link
        // )
        // ->with([
        //     'name' => 'Divisi HR'
        // ])
        // ->line(Lang::get('Thank you for using our application!'))
        // ->line('if you do not create an account, please ignore this email.');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
