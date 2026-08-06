<?php

namespace App\Mail;

use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionApprovalModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PrRejectedMail extends Mailable
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
        $this->url = url('/purchase-requesition/history');
    }

    public function build()
    {
        return $this->subject("Ditolak: Purchase Requisition - {$this->pr->no_doc}")
            ->view('emails.pr_rejected');
    }
}
