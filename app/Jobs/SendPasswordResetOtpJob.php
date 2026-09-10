<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPasswordResetOtpJob extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public int $otp;

    public function __construct(string $email, int $otp)
    {
        $this->email = $email;
        $this->otp   = $otp;
    }

    // public function handle(): void
    // {
    //     Mail::raw(
    //         "Your OTP for password reset is: {$this->otp}\nThis OTP will expire in 10 minutes.",
    //         function ($mail) {
    //             $mail->to($this->email)
    //                 ->subject('Password Reset OTP');
    //         }
    //     );
    // }

    public function build()
    {
        return $this->subject('Password Reset OTP')
            ->text('emails.password_reset_otp');
    }
}
