<?php

namespace App\Mail;

use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionApprovalModel;
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

    public function __construct(
        WspPurchaseRequesitionModel $pr,
        WspPurchaseRequesitionApprovalModel $approval
    ) {
        $this->pr = $pr;
        $this->approval = $approval;
        $this->url = url('/purchase-requesition/approval');
    }

    public function build()
    {
        return $this->subject("Approval Purchase Requesition - {$this->pr->no_doc}")
            ->view('emails.pr_approval');
    }
}
