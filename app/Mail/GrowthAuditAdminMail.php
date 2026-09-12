<?php
namespace App\Mail;

use App\Models\GrowthAuditRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GrowthAuditAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public GrowthAuditRequest $audit;

    public function __construct(GrowthAuditRequest $audit)
    {
        $this->audit = $audit;
    }

    public function build()
    {
        return $this
            ->subject('New Growth Audit Consultation Request')
            ->view('emails.growth-audit-admin');
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Growth Audit Admin Mail',
        );
    }

    /**
     * Get the message content definition.
     */

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
