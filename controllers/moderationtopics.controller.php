<?php

class ModerationTopicsController extends Controller
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelModerationTopics();
    }

    public function admin_index(){

        /* Получаю все темы для модерации */

        $this->data['topics'] = $this->model->getAllTopics();

        if(empty($this->data['topics'])){
            Session::setFlash('nodata', '<span class="bold">На данный момент нет тем для модерации!</span>');
        }
    }

    public function admin_public(){

        $params = App::getRouter()->getParams();

        $topic_id = $params[0];

        /* Получаю ID пользователя для получение email */
        $user_id = $this->model->getUserID($topic_id);

        /* Получаю email, имя и фамилию пользователя */

        $data = $this->model->getUserInfo($user_id);

        if(!empty($data[0]['email'])){

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
            $mail->Subject = htmlspecialchars('Публикация Вашей темы');
            $text = file_get_contents(MAIL_PATH."/mail_confirm_topic.html");
            $text = str_replace(['%name%'], [$firstname], $text);
            $mail->Body = "$text";
            $mail->isHTML(true);
            $mail->send();
            $mail->ClearAddresses();
            $mail->ClearAttachments();

        }

        /* Получаю данные для публикации */

        $data = $this->model->getTopicInfo($topic_id);

        $cat_id = $data['cat_id'];
        $rank_id = $data['rank_id'];
        $title = $data['title'];
        $date = $data['date'];
        $text = $data['text'];
        $user_id = $data['user_id'];

        $this->model->insertTopic($cat_id, $rank_id, $title, $text, $date, $user_id);
        $topic_id_public = $this->model->getTopicID($user_id, $title, $text);

        $users = $this->model->getUsersForMail($cat_id);
        if(!empty($users)){
            $category = $this->model->getCategoryByID($cat_id);

            $mail = new PHPMailer(false);
            $mail->isSMTP();
            $mail->Host = Config::get('smtp_host');
            $mail->SMTPAuth = Config::get('smtp_auth');
            $mail->Port = Config::get('smtp_port');
            $mail->SMTPSecure = Config::get('smtp_secure');
            $mail->CharSet = Config::get('smtp_charset');
            $mail->Username = Config::get('smtp_username');
            $mail->Password = Config::get('smtp_password');
            $mail->setFrom(Config::get('smtp_addreply'), 'openForum');
            for($i=0;$i<count($users);$i++){
                $email = $users[$i]['email'];
                $name = $users[$i]['firstname'];
                $mail->addAddress("$email", "$name");
                $link = "http://openforum.com.ua/forum/view/1/$topic_id_public";
                $link_unsubscribe = "http://openforum.com.ua/subscribe/delete/".md5($email);
                $mail->Subject = htmlspecialchars('Новая тема на форуме!!');
                $text = file_get_contents(MAIL_PATH."/mail_subscribe_static.html");
                $text = str_replace(['%name%', '%category%', '%link%','%title%', '%date%', '%link_unsubscribe%'], ["$name", "$category", "$link", htmlspecialchars_decode("$title"), $date, $link_unsubscribe], $text);
                $mail->Body = "$text";
                $mail->isHTML(true);
                $mail->send();
                $mail->clearAddresses();
                $mail->clearAllRecipients();
                $mail->clearAttachments();
            }
        }

        $this->model->deleteTopicModeration($topic_id);

        Router::redirect('/admin/moderationtopics/index/');
    }

    public function admin_edit(){

        $params = App::getRouter()->getParams();

        $topic_id = $params[0];

        $this->data['topic_id'] = $topic_id;

        if($_POST){

            $title = trim($_POST['title']);
            $title = htmlspecialchars($title, ENT_QUOTES);
            $title = stripcslashes($title);

            $text = trim($_POST['text']);
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = stripcslashes($text);

            $this->model->updateTopic($topic_id, $title, $text);

            Session::setFlash('update', '<span class="bold">Тема успешно редактирована</span>.');

        }

        $this->data['topic'] = $this->model->getTopicInfo($topic_id);

    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $topic_id = $params[0];

        $user_id = $this->model->getUserID($topic_id);

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
        $mail->Subject = htmlspecialchars('Публикация Вашей темы');
        $text = file_get_contents(MAIL_PATH."/mail_ban_topic.html");
        $text = str_replace(['%name%'], [$firstname], $text);
        $mail->Body = "$text";
        $mail->isHTML(true);
        $mail->send();
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        $this->model->deleteTopicModeration($topic_id);

        Router::redirect('/admin/moderationtopics/index');
    }
}
