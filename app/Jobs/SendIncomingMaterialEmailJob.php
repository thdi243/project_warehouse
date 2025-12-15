<?php

namespace App\Jobs;

use App\Mail\IncomingMaterialMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendIncomingMaterialEmailJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 120;
    public $emailGroup;

    /**
     * Create a new job instance.
     */
    public function __construct($emailGroup)
    {
        $this->emailGroup = $emailGroup;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->emailGroup as $materialDoc => $items) {

            $allEmails = [];
            foreach ($items as $item) {
                $allEmails = array_merge($allEmails, $item['emails']);
            }

            $allEmails = array_unique($allEmails);

            $emailData = [
                'material_doc' => $materialDoc,
                'list' => $items
            ];

            Mail::to($allEmails)->send(new IncomingMaterialMail($emailData));
        }
    }
}
