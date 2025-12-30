<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendWfgSopReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $manager;
    public $filePath;
    public $tanggal;
    public $principal;

    public function __construct($manager, $filePath, $tanggal, $principal)
    {
        $this->manager = $manager;
        $this->filePath = $filePath;
        $this->tanggal = $tanggal;
        $this->principal = $principal;
    }

    public function build()
    {
        return $this->subject("SO WFG Report {$this->tanggal} - {$this->principal}")
            ->view('emails.sop_report')
            ->with([
                'manager' => $this->manager,
                'tanggal' => $this->tanggal,
                'principal' => $this->principal,
            ])
            ->attach($this->filePath, [
                'as' => basename($this->filePath),
                'mime' => 'application/pdf',
            ]);
    }
}
