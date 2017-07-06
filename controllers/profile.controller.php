<?php

class ProfileController extends Controller{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelProfile();
    }

    public function user_avatar(){

        $user_id = Session::get('id');

        $uploaddir = "images/user_images";

        $address = $_SERVER['HTTP_REFERER'];

        // Для обрезки изобрадений использую библиотеку WideImage

        if($_POST){

            $max_size =  1258291; //1.2Мбайт
            if($_FILES['avatar']['size']>$max_size){
                Session::set('max_size', 'Размер не больше 1.2М');
                Router::redirect('/profile/');
            } elseif($_FILES['avatar']['error'] == 0) {
                $filename = $_FILES['avatar']['name'];
                move_uploaded_file($_FILES['avatar']['tmp_name'], "$uploaddir/$filename");

                $path = "$uploaddir/$filename";
                if($_FILES['avatar']['size']>614400){
                    WideImage::load("$path")->resize(400, 400)->crop('center', 'center', 220, 220)->saveToFile("$path");
                } else {
                    WideImage::load("$path")->resize(250, 250)->saveToFile("$path");
                }

                $path = "/$uploaddir/$filename";

                /* Получаю путь к файлу для удаления старой фотографии */

                $prev_avatar = $this->model->getPreviousAvatar($user_id);
                $prev_avatar = ltrim($prev_avatar, '/');

                if("/$prev_avatar" != Config::get('avatar')){
                    unlink("$prev_avatar");
                }

                $this->model->updateUserAvatar($user_id, $path);

                Session::set('avatar', $path);

                Router::redirect("$address");
            } else {
                Session::set('max_size', 'Размер не больше 1.2М');
                Router::redirect("$address");
            }
        }
    }

    public function user_deleteavatar(){

        $user_id = Session::get('id');

        $default_avatar = Config::get('avatar');

        $address = $_SERVER['HTTP_REFERER'];

        /* Получаю путь к файлу для удаления старой фотографии */

        $prev_avatar = $this->model->getPreviousAvatar($user_id);
        $prev_avatar = ltrim($prev_avatar, '/');

        if("/$prev_avatar" != Config::get('avatar')){
            unlink("$prev_avatar");
        }

        $this->model->deleteUserAvatar($user_id, $default_avatar);

        Session::set('avatar', Config::get('avatar'));

        Router::redirect("$address");
    }

    public function user_edit(){

        $user_id = Session::get('id');

        if($_POST){

            $city = trim($_POST['city']);
            $city = strip_tags($city);
            $city = htmlspecialchars($city, ENT_QUOTES);
            $city = stripcslashes($city);

            $this->model->updateUserCity($user_id, $city);

            Session::setFlash('update', '<span class="bold">Информация успешно обновлена!</span>');
        }

        $this->data['user'] = $this->model->getUserInfo($user_id);


    }

    public function user_email(){

        $user_id = Session::get('id');

        $this->data['user'] = $this->model->getUserInfo($user_id);

        $firstname = $this->data['user']['firstname'];
        $lastname = $this->data['user']['lastname'];

        if($_POST) {

            $email = $_POST['email'];

            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('email', 'Проверьте корректность email!');
            } elseif ($this->model->checkIssetEmail($email)) {
                Session::setFlash('email', 'Данный email уже зарегистрирован!');
            } else {
                $code = mt_rand(100000, 999999);
                Session::set('code', $code);
                Session::set('email', $email);

                $email = trim($_POST['email']);
                $email = strip_tags($email);
                $email = htmlspecialchars($email, ENT_QUOTES);
                $email = stripcslashes($email);

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
                $mail->Subject = htmlspecialchars('Изменение почтового адреса');
                $text = file_get_contents(MAIL_PATH."/mail_edit_email.html");
                $text = str_replace(['%name%', '%code%'], [$firstname, $code], $text);
                $mail->Body = "$text";
                $mail->isHTML(true);
                $mail->send();
                $mail->ClearAddresses();
                $mail->ClearAttachments();

                Router::redirect("/user/profile/code/$user_id");

            }
        }

    }

    public function user_code(){

        $user_id = Session::get('id');

        $this->data['user'] = $this->model->getUserInfo($user_id);
        $firstname = $this->data['user']['firstname'];

        if($_POST){
            if(Session::get('code') == $_POST['code']){

                $email = Session::get('email');

                $this->model->updateUserEmail($user_id, $email);

                Router::redirect("/user/profile/note/$user_id");
            } else {
                Session::setFlash('wrong_code', 'Неверный код');
            }
        }
    }

    public function user_note(){

        $user_id = Session::get('id');

        $this->data['user'] = $this->model->getUserInfo($user_id);
        $firstname = $this->data['user']['firstname'];

        Session::setFlash('notice', "<span class='bold'>$firstname, Ваши данные успешно отредактированы.</span> Если у Вас есть жалобы или предложения к нам, Вы всегда можете обратиться в <a href='/forum/feedback/'>службу поддержки</a> openForum. ");

    }

    public function user_password(){

        $user_id = Session::get('id');

        $this->data['user'] = $this->model->getUserInfo($user_id);

        if($_POST){

            $password = trim($_POST['password']);
            $password = strip_tags($password);
            $password = htmlspecialchars($password, ENT_QUOTES);
            $password = stripcslashes($password);
            $password = $password.Config::get('salt');
            $password = md5($password);

            if($this->model->checkUserPassword($user_id, $password)){

                $password1 = trim($_POST['password1']);
                $password1 = strip_tags($password1);
                $password1 = htmlspecialchars($password1, ENT_QUOTES);
                $password1 = stripcslashes($password1);

                $password2 = trim($_POST['password2']);
                $password2 = strip_tags($password2);
                $password2 = htmlspecialchars($password2, ENT_QUOTES);
                $password2 = stripcslashes($password2);

                if (!$this->model->checkValidPassword($password1)){
                    Session::setFlash('password', 'Пароль должен содержать Строчные, прописные буквы, а также цифры!');
                } elseif($password1 != $password2){
                    Session::setFlash('password', 'Пароли не совпадают!');
                } else {
                    $password1 = $password1.Config::get('salt');
                    $password1 = md5($password1);

                    $this->model->updateUserPassword($user_id, $password1);

                    Router::redirect("/user/profile/note/$user_id");
                }

            } else {
                Session::setFlash('wrong_pass', 'Неверный пароль');
            }

        }

    }

    public function user_rank(){

        $user_id = Session::get('id');

        $this->data['user'] = $this->model->getUserInfo($user_id);

        $this->data['categories'] = $this->model->getAllCategory();

        if($_POST){

            $category = $_POST['category'];

            $rank = $_POST['rank'];
            if($rank == 0){
                $user_rank = $_POST['user_rank'];
                $firstletter = mb_substr($user_rank, 0, 1);
                $firstletter = mb_strtoupper($firstletter);
                $user_rank = $firstletter.mb_substr($user_rank, 1);
                $data = $this->model->checkIssetRank($user_rank);
                if(empty($data)){
                    $this->model->insertUserRank($category, $user_rank);
                    $rank = $this->model->getUserRank($user_rank);
                } else {
                    $rank = $data[0]['id'];
                }
            }

            $this->model->updateUserRank($user_id, $category, $rank);

            Router::redirect('/user/profile/note');

        }

    }

    public function user_delete(){

        $user_id = Session::get('id');

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

            if(!$this->model->checkValidUserData($user_id, $email, $password)){
                Session::setFlash('wrong_data', 'Email или пароль не совпадают!');
            } else {

                /* Сначала удаляю ID пользователя с списка специалистов */

                $this->model->deleteIDFromUsers($user_id);

                /* Удаляю пользователя */

                $this->model->deleteUser($user_id);

                session_destroy();

                Router::redirect('/');

            }

        }

    }
}