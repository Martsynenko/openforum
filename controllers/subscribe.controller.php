<?php

class SubscribeController extends Controller
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelSubscribe();
    }

    public function index(){

        /*Формирую данные для select */
        $this->data['categories'] = $this->model->getCategories();

        if($_POST){

            $cat_id = $_POST['category'];

            $name = trim($_POST['name']);
            $name = strip_tags($name);
            $name = htmlspecialchars($name, ENT_QUOTES);
            $name = stripcslashes($name);

            $email = trim($_POST['email']);
            $email = strip_tags($email);
            $email = htmlspecialchars($email, ENT_QUOTES);
            $email = stripcslashes($email);

            $this->model->insertSubscribe($name, $email, $cat_id);

            Router::redirect('/');
        }

    }

    public function delete(){

        $params = App::getRouter()->getParams();

        $email = $params[0];

        $this->model->deleteUserSubscribeByEmail($email);

        Session::setFlash('unsubscribe', '<span class="bold">Мы отписали Вас от рассылки! Теперь Вы не будете получать письма с интересными темами. Вы всегда можете оформить новую подписку на любую из категорий!</span>');

    }

    public function admin_index(){

        /* Получаю всех подписчиков */

        $this->data['users'] = $this->model->getAllUsersSubscribe();

    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $user_id = $params[0];

        $this->model->deleteUserSubscribe($user_id);

        Router::redirect('/admin/subscribe/index');

    }

    public function admin_send(){

        /* Получаю все категории */
        $this->data['categories'] = $this->model->getCategories();

        if($_POST){

            $cat_id = $_POST['category'];

            $category = $this->model->getCategoryByID($cat_id);

            $message = trim($_POST['text']);
            $message = htmlspecialchars($message, ENT_QUOTES);
            $message = stripcslashes($message);

            $data = $this->model->getUsersForMail($cat_id);

            if(empty($data)){
                Session::setFlash('nousers', '<span class="bold">По данной категории нет подписчиков!</span>');
            } else {

                $mail = new PHPMailer(false);
                $mail->isSMTP();
                $mail->Host = Config::get('smtp_host');
                $mail->SMTPAuth = Config::get('smtp_auth');
                $mail->Port = Config::get('smtp_port');
                $mail->SMTPSecure = Config::get('smtp_secure');
                $mail->CharSet = Config::get('smtp_charset');
                $mail->Username = Config::get('smtp_username');
                $mail->Password = Config::get('smtp_password');
                $mail->setFrom(Config::get('smtp_addreply'), 'openForum.com. ua');
                for($i=0;$i<count($data);$i++){
                    $email = $data[$i]['email'];
                    $name = $data[$i]['firstname'];
                    $mail->addBCC("$email", "$name");
                }

                $mail->Subject = htmlspecialchars('Новая тема на форуме!!');
                $text = file_get_contents(MAIL_PATH."/mail_subscribe.html");
                $text = str_replace(['%message%'], [htmlspecialchars_decode("$message")], $text);
                $mail->Body = "$text";
                $mail->isHTML(true);
                $mail->send();
                $mail->ClearAddresses();
                $mail->ClearAttachments();

                Session::setFlash('send', "<span class='bold'>Сообщение успешно отправлено всем пользователям по категории $category!</span>.");

            }

        }

    }

}