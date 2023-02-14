<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class PHPMailerController extends Controller
{
    public string $email;
    public User $user;

    public function __construct($email, $user)
    {
        $this->user = $user;
        $this->email = $email;
    }

    public function sentMail(): JsonResponse
    {
        require base_path("vendor/autoload.php");
        $mail = new PHPMailer(true);

        try {
            $user = $this->user;
            $link = 'http://localhost/';

            $mail->isSMTP();
            $mail->CharSet = "UTF-8";
            $mail->SMTPAuth = true;

            $mail->Host = env('MAIL_HOST');
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), 'Igor Fetisov');
            $mail->addAddress($this->email);
            $mail->isHTML();

            $mail->Subject = 'Сброс пароля';
            $mail->Body = view('email')->with(['user' => $user, 'link' => $link]);

            if (!$mail->send()) {
                return response()->json(['failed' => 'Email not sent.']);
            } else {
                return response()->json(['success' => 'Email has been sent.']);
            }
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
