<?php

class RegistrationController extends Controller {

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelRegistration();
    }

    public function index(){
        /*Формирую данные для полей регистрации*/
        $this->data['categories'] = $this->model->getCategories();
        $this->data['month'] = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

        if($_POST){
            if(!$this->model->checkValidName($_POST['firstname'])){
                Session::setFlash('firstname','Имя может содержать только буквы!');
            } elseif (!$this->model->checkValidName($_POST['lastname'])){
                Session::setFlash('lastname', 'Фамилия может содержать только буквы!');
            } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                Session::setFlash('email', 'Проверьте корректность email!');
            } elseif ($this->model->checkIssetEmail($_POST['email'])) {
                Session::setFlash('email', 'Данный email уже зарегистрирован!');
            } elseif (!$this->model->checkValidPassword($_POST['password'])){
                Session::setFlash('password', 'Пароль должен содержать Строчные, прописные буквы, а также цифры!');
            } else {
                $code = mt_rand(100000, 999999);
                Session::set('code', $code);


                $firstname = trim($_POST['firstname']);
                $firstname = strip_tags($firstname);
                $firstname = htmlspecialchars($firstname, ENT_QUOTES);
                $firstname = stripcslashes($firstname);

                $lastname = trim($_POST['lastname']);
                $lastname = strip_tags($lastname);
                $lastname = htmlspecialchars($lastname, ENT_QUOTES);
                $lastname = stripcslashes($lastname);

                $city = trim($_POST['city']);
                $city = strip_tags($city);
                $city = htmlspecialchars($city, ENT_QUOTES);
                $city = stripcslashes($city);

                $email = trim($_POST['email']);
                $email = strip_tags($email);
                $email = htmlspecialchars($email, ENT_QUOTES);
                $email = stripcslashes($email);

                $password = trim($_POST['password']);
                $password = strip_tags($password);
                $password = htmlspecialchars($password, ENT_QUOTES);
                $password = stripcslashes($password);
//                Session::set('real_password', $password);
                $password = $password.Config::get('salt');
                $password = md5($password);

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

                $day = $_POST['day'];
                $month = $_POST['month'];
                $year = $_POST['year'];
                $birthdate = "$year-$month-$day";

                Session::set('name', $firstname);
                Session::set('lastname', $lastname);
                Session::set('city', $city);
                Session::set('email', $email);
                Session::set('password', $password);
                Session::set('category', $category);
                Session::set('rank', $rank);
                Session::set('birthdate', $birthdate);
                Session::set('count_topics', '0');

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
                $mail->Subject = htmlspecialchars('Подтверждение почтового адреса');
                $text = file_get_contents(MAIL_PATH."/mail_registration.html");
                $text = str_replace(['%name%', '%code%'], [$firstname, $code], $text);
                $mail->Body = "$text";
                $mail->isHTML(true);
                $mail->send();
                $mail->ClearAddresses();
                $mail->ClearAttachments();

                Router::redirect('/registration/confirm/');
            }
        }
    }

    public function confirm(){
        $this->data['name'] = Session::get('name');
        if($_POST){
            if(Session::get('code') == $_POST['code']){

                $firstname = Session::get('name');
                $lastname = Session::get('lastname');
                $city = Session::get('city');
                $email = Session::get('email');
                $password = Session::get('password');
                $category = Session::get('category');
                $rank = Session::get('rank');
                $birthdate = Session::get('birthdate');
                $date_reg = date('Y:m:d');
                $avatar = '/images/admin_images/no-avatar.jpg';
                $this->model->insert($firstname, $lastname, $city, $email, $password, $category, $rank, $birthdate, $avatar, $date_reg);

                Router::redirect('/registration/note/');
            } else {
                Session::setFlash('wrong_code', 'Неверный код');
            }
        }
    }

    public function note(){
        Session::delete('name');
        Session::delete('lastname');
        Session::delete('city');
        Session::delete('email');
        Session::delete('password');
        Session::delete('category');
        Session::delete('rank');
        Session::delete('birthdate');
        Session::delete('code');
        Session::setFlash('notice', '<span class="bold">Ураа! Вы успешно зарегистрированы на форуме!</span> Теперь можете войти на форум как зарегистрированный пользователь. Для этого введите свой email и пароль в форме авторизации в шапке сайта. Если у вас есть жалобы или предложения к нам, воспользуйтесь формой <a href="/forum/feedback/">обратной связи</a>. Благодарим за регистрацию!');
    }
}