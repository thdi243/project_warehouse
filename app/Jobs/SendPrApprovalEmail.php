<?php

namespace App\Jobs;

use App\Mail\PrApprovalMail;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionApprovalModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPrApprovalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $prId;
    protected int $approvalId;
    protected string $email;
    protected ?string $recipientName;

    public function __construct(int $prId, int $approvalId, string $email, ?string $recipientName = null)
    {
        $this->prId = $prId;
        $this->approvalId = $approvalId;
        $this->email = $email;
        $this->recipientName = $recipientName;
    }

    public function handle(): void
    {
        $pr = WspPurchaseRequesitionModel::find($this->prId);
        $approval = WspPurchaseRequesitionApprovalModel::find($this->approvalId);

        if (!$pr || !$approval) {
            return;
        }

        Mail::to($this->email)
            ->send(new PrApprovalMail($pr, $approval, $this->recipientName));
    }
}
