<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Payroll;

class PayslipGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $payroll;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Payroll $payroll)
    {
        $this->user = $user;
        $this->payroll = $payroll;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Payslip Generated - ' . $this->payroll->month,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.payslip_generated',
            with: [
                'user' => $this->user,
                'payroll' => $this->payroll,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
