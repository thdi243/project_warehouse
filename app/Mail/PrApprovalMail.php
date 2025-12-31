<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pr;
    public $approval;
    public $url;

    public function __construct($pr, $approval)
    {
        $this->pr = $pr;
        $this->approval = $approval;

        $this->url = url("/app/approval-pr/{$pr->id}");
        // $this->url = url("/login");
    }

    public function build()
    {
        return $this->subject('Approval Purchase Requesition')
            ->view('emails.pr_approval');
    }
}
