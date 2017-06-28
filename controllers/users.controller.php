<?php

class UsersController extends Controller
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelUsers();
    }

    public function admin_index(){

        /* Получаю всех пользователей */

        $this->data['users'] = $this->model->getAllUsers();

        /* Получаю заблокированных пользователей */

        $data = $this->model->getUsersBlock();
        if(!empty($data)){
            $array = [];
            for($i=0;$i<count($data);$i++){
                $array[] = $data[$i]['user_id'];
            }
            $this->data['users_block'] = $array;
        }

    }

    public function admin_statistic(){

        /*Получаю данные пользователя */

        $params = App::getRouter()->getParams();

        $user_id = $params[0];

        $this->data['user'] = $this->model->getUserInfo($user_id);

        $this->data['count_topics'] = $this->model->getCountUserTopics($user_id);

        $this->data['count_messages'] = $this->model->getCountUserMessages($user_id);

        $answers_topics = $this->model->getCountAnswersTopics($user_id);
        $answers_messages = $this->model->getCountAnswersMessages($user_id);
        $this->data['count_answers'] = $answers_topics + $answers_messages;

        $this->data['count_visits'] = $this->model->getCountVisists($user_id);

        $time = $this->model->getTimeVisits($user_id);
        if($time<86400){
            $time = date("H:i:s", mktime(0, 0, $time));
            $time_array = explode(':', $time);
            $this->data['hour'] = $time_array[0];
            $this->data['minute'] = $time_array[1];
            $this->data['second'] = $time_array[2];
        } else {
            $days = $time/86400;
            $days = (integer)$days;
            $time = $time - ($days*86400);
            $time = date("H:i:s", mktime(0, 0, $time));
            $time_array = explode(':', $time);
            $this->data['hour'] = $time_array[0];
            $this->data['minute'] = $time_array[1];
            $this->data['second'] = $time_array[2];
            $this->data['day'] = $days;
        }
    }

    public function admin_block(){

        $params = App::getRouter()->getParams();

        $user_id = $params[0];

        /* Добавляю пользователя в таблицу заблокированых пользователей */

        $this->model->insertBlockUser($user_id);

        /* Получаю данные пользователя и оптравляю письмо */

        $data = $this->model->getUserInfo($user_id);
        $email = $data['email'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];

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
        $mail->Subject = htmlspecialchars('Ваш аккаунт заблокирован');
        $text = file_get_contents(MAIL_PATH."/mail_block.html");
        $text = str_replace(['%name%'], [$firstname], $text);
        $mail->Body = "$text";
        $mail->isHTML(true);
        $mail->send();
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        Router::redirect('/admin/users/index/');

    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $user_id = $params[0];

        $this->model->deleteUser($user_id);

        Router::redirect('/admin/users/index');

    }

    public function admin_unblock(){

        $params = App::getRouter()->getParams();

        $user_id = $params[0];

        $data = $this->model->getUserInfo($user_id);
        $email = $data['email'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];

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
        $mail->Subject = htmlspecialchars('Ваш аккаунт разблокирован');
        $text = file_get_contents(MAIL_PATH."/mail_unblock.html");
        $text = str_replace(['%name%'], [$firstname], $text);
        $mail->Body = "$text";
        $mail->isHTML(true);
        $mail->send();
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        $this->model->deleteUserBlock($user_id);

        Router::redirect('/admin/users/index/');
    }
}