<?php

namespace App\Jobs;

use App\Mail\SendWfgSopReportMail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWfgSopReportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $approverEmails;
    protected $managerEmail;
    protected $sop;
    protected $absolutePath;
    protected $tanggal;
    protected $principal;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        array $approverEmails,
        ?string $managerEmail,
        $sop,
        string $absolutePath,
        string $tanggal,
        string $principal
    ) {
        $this->approverEmails = $approverEmails;
        $this->managerEmail  = $managerEmail;
        $this->sop           = $sop;
        $this->absolutePath  = $absolutePath;
        $this->tanggal       = $tanggal;
        $this->principal     = $principal;
    }

    public function handle()
    {
        // === Kirim ke approver ===
        foreach ($this->approverEmails as $email) {
            Mail::to($email)->send(
                new SendWfgSopReportMail(
                    $this->sop,
                    $this->absolutePath,
                    $this->tanggal,
                    $this->principal
                )
            );
        }

        // === Kirim ke dept_head ===
        if ($this->managerEmail) {
            Mail::to($this->managerEmail)->send(
                new SendWfgSopReportMail(
                    $this->sop,
                    $this->absolutePath,
                    $this->tanggal,
                    $this->principal
                )
            );
        }

        if (file_exists($this->absolutePath)) {
            unlink($this->absolutePath);
        }
    }
}
