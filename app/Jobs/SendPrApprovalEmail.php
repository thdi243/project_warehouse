<?php

namespace App\Jobs;

use App\Mail\PrApprovalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPrApprovalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pr;
    protected $approval;
    protected $email;

    public function __construct($pr, $approval, $email)
    {
        $this->pr = $pr;
        $this->approval = $approval;
        $this->email = $email;

        $this->afterCommit();
    }

    public function handle()
    {
        Mail::to($this->email)
            ->send(new PrApprovalMail($this->pr, $this->approval));
    }
}
