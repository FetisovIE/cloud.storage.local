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

            $mail->isSMTP();
            $mail->CharSet = "UTF-8";
            $mail->SMTPAuth = true;

            $mail->Host = 'smtp.gmail.com';
            $mail->Username = 'ig.fetisoff@gmail.com';
            $mail->Password = 'root';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('ig.fetisoff@gmail.com', 'Igor Fetisov');
            $mail->addAddress($this->email);
            $mail->isHTML();

            $mail->Subject = 'Сброс пароля';
            $mail->Body = view('email')->with(['link'=>'asdasdasd']);

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
