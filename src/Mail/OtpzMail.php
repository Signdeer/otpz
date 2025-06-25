<?php

namespace BenBjurstrom\Otpz\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class OtpzMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Model $otp,
        protected string $code
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sign in to ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $user = $this->otp->user ?? null;
        $email = $user?->email ?? 'unknown@example.com';

        $formattedCode = substr_replace($this->code, '-', 5, 0);

        $template = config('otpz.template', 'otpz::mail.otpz');

        return new Content(
            markdown: $template,
            with: [
                'email' => $email,
                'code' => $formattedCode,
            ],
        );
    }

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
