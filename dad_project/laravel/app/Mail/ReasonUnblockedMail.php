<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Resources\User as UserResource;
use App\User;

class ReasonUnblockedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $reasonUnblocked;

    public function __construct($unblocked_reason)
    {
        $this->reasonUnblocked = $unblocked_reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.unblockedPlayerReason')
            ->subject("Account Unblocked.");
    }
}
