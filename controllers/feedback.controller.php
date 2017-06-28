<?php

class FeedbackController extends Controller
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelFeedback();
    }

    public function admin_index(){

        /* Получаю все сообщения */

        $this->data['messages'] = $this->model->getAllFeedbackMessages();
    }

    public function admin_answer(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $this->data['message_id'] = $message_id;

        $data = $this->model->getMessageInfo($message_id);
        $name = $data[0]['name'];
        $email = $data[0]['email'];

        if($_POST){

            $subject = trim($_POST['subject']);
            $subject = strip_tags($subject);
            $subject = htmlspecialchars($subject, ENT_QUOTES);
            $subject = stripcslashes($subject);

            $message = trim($_POST['text']);
            $message = htmlspecialchars($message, ENT_QUOTES);
            $message = stripcslashes($message);

            $mail = new PHPMailer(false);
            $mail->isSMTP();
            $mail->Host = Config::get('smtp_host');
            $mail->SMTPAuth = Config::get('smtp_auth');
            $mail->Port = Config::get('smtp_port');
            $mail->SMTPSecure = Config::get('smtp_secure');
            $mail->CharSet = Config::get('smtp_charset');
            $mail->Username = Config::get('smtp_username');
            $mail->Password = Config::get('smtp_password');
            $mail->setFrom(Config::get('smtp_addreply'), 'openForum.ua');
            $mail->addAddress("$email", "$name");
            $mail->Subject = htmlspecialchars("$subject");
            $text = file_get_contents(MAIL_PATH."/mail_feedback_answer.html");
            $text = str_replace(['%name%', '%message%'], [$name, htmlspecialchars_decode("$message")], $text);
            $mail->Body = "$text";
            $mail->isHTML(true);
            $mail->send();
            $mail->ClearAddresses();
            $mail->ClearAttachments();

            Session::setFlash('send', "<span class='bold'>Сообщение успешно отправлено пользователю $name</span>.");

        }
    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $this->model->deleteFeedbackMessage($message_id);

        Router::redirect('/admin/feedback/index');

    }
}