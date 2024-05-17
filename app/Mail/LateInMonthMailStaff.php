<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LateInMonthMailStaff extends Mailable
{
    use Queueable, SerializesModels;
    private $object;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.lateInMonthMailStaff', ['obj' => $this->object])
            ->subject('Report check in-out late or forgot');
    }
}
