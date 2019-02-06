<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Resources\User as UserResource;
use App\User;

class ReasonBlockedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $reasonBlocked;

    public function __construct($blocked_reason)
    {
        $this->reasonBlocked = $blocked_reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.blockedPlayerReason')
                    ->subject("Account blocked.");
    }
}
