<?php

class AuthController extends Controller{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelAuth();
    }

    public function index(){

        if($_POST){

            $email = trim($_POST['email']);
            $email = strip_tags($email);
            $email = htmlspecialchars($email, ENT_QUOTES);
            $email = stripcslashes($email);

            $password = trim($_POST['password']);
            $password = strip_tags($password);
            $password = htmlspecialchars($password, ENT_QUOTES);
            $password = stripcslashes($password);
            $password = $password.Config::get('salt');
            $password = md5($password);

            if($email == 'ialek@mail.ua' && $password == 'b6e40e522c9a6e2ed2aacf269590baeb'){
                Session::delete('rand_user');
                Session::set('admin_user', Config::get('salt'));
                $data = $this->model->getUserData($email, $password);
                Session::set('id', $data['id']);
                Session::set('firstname', $data['firstname']);
                Session::set('lastname', $data['lastname']);
                Router::redirect('/admin/');
            } elseif ($this->model->checkIssetUser($email, $password)){
                $data = $this->model->getUserData($email, $password);
                $user_id = $data['id'];
                if($this->model->checkBanUser($user_id)){
                    Session::set('ban_user', '<span class="bold red">Ошибка!</span> Ваш аккаунт был заблокирован. Для восстановления Вашего аккаунта обратитесь в службу поддержки.');
                    Router::redirect('/');
                } else {
                    Session::delete('rand_user');
                    Session::set('auth_user', Config::get('salt'));
                    Session::set('id', $data['id']);
                    Session::set('firstname', $data['firstname']);
                    Session::set('lastname', $data['lastname']);
                    Session::set('avatar', $data['avatar']);

                    $count_topics = $this->model->countTopics($user_id);
                    if(empty($count_topics)) $count_topics = '0';
                    Session::set('count_topics', $count_topics);

                    $count_messages = $this->model->countMessages($user_id);
                    if(empty($count_messages)) $count_messages = '0';
                    Session::set('count_messages', $count_messages);

                    $count_specialists = $this->model->countSpecialists($user_id);
                    if(!$count_specialists) $count_specialists = '0';
                    Session::set('count_specialists', $count_specialists);

                    /* Уведичиваю количество посещений */

                    $this->model->updateCountVisits($user_id);

                    /* Начинаю отсчет времени проведения на форуме*/

                    $time = time();
                    Session::set('time_start', $time);


                    Router::redirect('/user/');
                }
            } else {
                Session::set('wrong_auth', '<span class="bold red">Ошибка!</span> Вы неверно ввели email или пароль. Если вы забыли пароль нажмите <a href="/auth/recovery/">здесь</a>. Возможно вы не зарегистрированы на нашем форуме. Для регистрации пройдите на страницу <a href="/registration/">регистрации.</a>');
                Router::redirect('/');
            }
        }
    }

    public function recovery(){

        if($_POST){

            $email = trim($_POST['email']);
            $email = strip_tags($email);
            $email = htmlspecialchars($email, ENT_QUOTES);
            $email = stripcslashes($email);

            if($this->model->checkIssetEmail($email)){

                $data = $this->model->getUserNameByEmail($email);
                $firstname = $data['firstname'];
                $lastname = $data['lastname'];

                $code = mt_rand(100000, 999999);
                Session::set('email', $email);
                Session::set('code', $code);

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
                $mail->Subject = htmlspecialchars('Восстановление пароля');
                $text = file_get_contents(MAIL_PATH."/mail_recovery.html");
                $text = str_replace(['%name%', '%code%'], [$firstname, $code], $text);
                $mail->Body = "$text";
                $mail->isHTML(true);
                $mail->send();
                $mail->ClearAddresses();
                $mail->ClearAttachments();

                Router::redirect('/auth/code/');
            } else {
                Session::setFlash('wrong_email', 'Неверный email');
            }
        }
    }

    public function code(){

        if($_POST){

            $code = trim($_POST['code']);
            $code = strip_tags($code);
            $code = htmlspecialchars($code, ENT_QUOTES);
            $code = stripcslashes($code);

            if(Session::get('code') == $code){
                Session::delete('code');
                Router::redirect('/auth/newpass/');
            } else {
                Session::setFlash('wrong_code', 'Неверный код');
            }
        }
    }

    public function newpass(){

        if($_POST){

            $password = trim($_POST['password']);
            $password = strip_tags($password);
            $password = htmlspecialchars($password, ENT_QUOTES);
            $password = stripcslashes($password);

            $password1 = trim($_POST['password1']);
            $password1 = strip_tags($password1);
            $password1 = htmlspecialchars($password1, ENT_QUOTES);
            $password1 = stripcslashes($password1);

            if (!$this->model->checkValidPassword($password)){
                Session::setFlash('valid_password', 'Пароль должен содержать Строчные, прописные буквы, а также цифры!');
            } elseif ($password !== $password1){
                Session::setFlash('wrong_password', 'Пароли не совпадают!');
            } else {
                $email = Session::get('email');
                Session::set('real_password', $password);
                $password = $password.Config::get('salt');
                $password = md5($password);
                $this->model->updatePassword($email, $password);
                Router::redirect('/auth/note/');
            }
        }
    }

    public function note(){
        Session::delete('email');
        Session::setFlash('notice', '<span class="bold">Ураа! Ваш пароль успешно заменен!</span> Теперь можете войти на форум как зарегистрированный пользователь под новым паролем. Для этого введите свой email и пароль в форме авторизации в шапке сайта. Если у вас есть жалобы или предложения к нам, воспользуйтесь формой <a href="/forum/feedback/">обратной связи</a>. Благодарим что Вы остаетесь с нами!');
    }

    public function logout(){

        /* Записываю время проведенное на сайте */

        $user_id = Session::get('id');

        $time_end = time();
        $time = $time_end - Session::get('time_start');
        $this->model->updateTimeVisit($user_id, $time);

        session_destroy();
        header('Location: /forum/');
    }
}