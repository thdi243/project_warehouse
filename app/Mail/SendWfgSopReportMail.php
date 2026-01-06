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

    public $sop;
    public $filePath;
    public $tanggal;
    public $principal;
    public $recipientName;

    public function __construct($sop, $recipientName, $filePath, $tanggal, $principal)
    {
        $this->sop = $sop;
        $this->recipientName = $recipientName;
        $this->filePath = $filePath;
        $this->tanggal = $tanggal;
        $this->principal = $principal;
    }

    public function build()
    {
        return $this->subject("SO WFG Report {$this->tanggal} - {$this->principal}")
            ->view('emails.sop_report')
            ->attach($this->filePath, [
                'as' => basename($this->filePath),
                'mime' => 'application/pdf',
            ]);
    }
}
