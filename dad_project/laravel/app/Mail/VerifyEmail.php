<?php

namespace App\Mail;

use App\PasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $confirmEmailToken;

    public function __construct(PasswordReset $confirmEmailToken)
    {
        $this->confirmEmailToken = $confirmEmailToken;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.confirmRegistration')
                    ->subject('Welcome to ' .config('app.name').'!');
    }
}
