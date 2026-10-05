<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * Sent synchronously (deliberately NOT ShouldQueue): the digest runs from
 * the scheduler on installs that have no queue worker, and a queued mail
 * would silently rot in the database queue table.
 */
class PaymentDigestMail extends Mailable
{
    public $digest;

    public function __construct(array $digest)
    {
        $this->digest = $digest;
    }

    public function build()
    {
        return $this->subject(
            'Payment gateway failures digest (' . $this->digest['total'] . ')'
        )->view('emails.payment-digest');
    }
}