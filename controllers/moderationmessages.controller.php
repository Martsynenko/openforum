<?php

class ModerationMessagesController extends Controller
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelModerationMessages();
    }

    public function admin_index(){

        /* Получаю все темы для модерации */

        $this->data['messages'] = $this->model->getAllMessages();

        $this->data['users'] = $this->model->getAllUsersName();

        if(empty($this->data['messages'])){
            Session::setFlash('nodata', '<span class="bold">На данный момент нет вопросов для модерации!</span>');
        }
    }

    public function admin_public(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        /* Получаю ID пользователя для получение email */
        $user_id = $this->model->getUserID($message_id);

        /* Получаю email, имя и фамилию пользователя */

        $data = $this->model->getUserInfo($user_id);

        $email = $data[0]['email'];
        $firstname = $data[0]['firstname'];
        $lastname = $data[0]['lastname'];

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
        $mail->addAddress("$email", "$firstname $lastname");
        $mail->Subject = htmlspecialchars('Рассылка Вашего вопроса');
        $text = file_get_contents(MAIL_PATH."/mail_confirm_message.html");
        $text = str_replace(['%name%'], [$firstname], $text);
        $mail->Body = "$text";
        $mail->isHTML(true);
        $mail->send();
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        /* Получаю данные для публикации */

        $data = $this->model->getMessageInfo($message_id);

        $user_id = $data['user_id'];
        $cat_id = $data['cat_id'];
        $rank_id = $data['rank_id'];
        $text = $data['text'];
        $date = $data['date'];
        $users = $data['users'];

        $this->model->insertMessage($user_id, $cat_id, $rank_id, $text, $date, $users);

        $this->model->deleteMessageModeration($message_id);

        Router::redirect('/admin/moderationmessages/index/');
    }

    public function admin_edit(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $this->data['message_id'] = $message_id;

        if($_POST){

            $text = trim($_POST['text']);
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = stripcslashes($text);

            $this->model->updateMessage($message_id, $text);

            Session::setFlash('update', '<span class="bold">Вопрос успешно редактирован</span>.');

        }

        $this->data['message'] = $this->model->getMessageText($message_id);

    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $user_id = $this->model->getUserID($message_id);

        $data = $this->model->getUserInfo($user_id);
        $email = $data[0]['email'];
        $firstname = $data[0]['firstname'];
        $lastname = $data[0]['lastname'];

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
        $mail->addAddress("$email", "$firstname $lastname");
        $mail->Subject = htmlspecialchars('Рассылка вопроса');
        $text = file_get_contents(MAIL_PATH."/mail_ban_message.html");
        $text = str_replace(['%name%'], [$firstname], $text);
        $mail->Body = "$text";
        $mail->isHTML(true);
        $mail->send();
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        $this->model->deleteMessageModeration($message_id);

        Router::redirect('/admin/moderationmessages/index');
    }
}
