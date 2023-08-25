<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $details)
    {
        //
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = 0; // Set to 2 for debugging
            $mail->isSMTP();
            $mail->Host       = config('smvrtlegal.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = config('support1@smvrtlegal.com');
            $mail->Password   = config('!9&*$R?n=rfN');
            $mail->SMTPSecure = config('tls');
            $mail->Port       = config('465');

            // Recipients
            $mail->setFrom(config('support1@smvrtlegal.com'), config('support1'));
            $mail->addAddress($this->details['recipient']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $this->details['subject'];
            $mail->Body    = $this->details['body'];

            $mail->send();
        } catch (Exception $e) {
            // Handle exception here
            // For example, log the error message
            Log::error($e->getMessage());
        }
    }
}
