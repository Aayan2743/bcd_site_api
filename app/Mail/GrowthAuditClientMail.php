<?php
namespace App\Mail;

use App\Models\GrowthAuditRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GrowthAuditClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public GrowthAuditRequest $audit;

    public function __construct(GrowthAuditRequest $audit)
    {
        $this->audit = $audit;
    }

    public function build()
    {
        return $this
            ->subject('We Received Your Free Growth Consultation Request')
            ->view('emails.growth-audit-client');
    }
}
