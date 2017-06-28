<?php
class MessagesController extends Controller {

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelMessages();
    }

    public function user_outbox(){

        $id = Session::get('id');

        /* Получаю количество сообщений outbox*/
        $this->data['count_outbox'] = $this->model->getCountOutbox($id);

        /* Получаю количество сообщений inbox*/
        $this->data['messages_inbox'] = $this->model->getMessagesInbox($id);
        if(empty($this->data['messages_inbox'])){
            $this->data['count_inbox'] = 0;
        } else {
            $this->data['count_inbox'] = count($this->data['messages_inbox']);
        }

        $count_messages = $this->data['count_outbox'] + $this->data['count_inbox'];
        Session::set('count_messages', $count_messages);

        $this->data['messages'] = $this->model->getMessages($id);

        if(empty($this->data['messages'])){
            Session::setFlash('nodata', '<span class="bold">На данный момент у Вас нет сообщений</span>. Для того чтоб разослать вопрос специалистам перейдите в раздел <a href="/user/messages/new/">Разослать вопрос</a>.');
        }
    }

    public function user_inbox(){

        $id = Session::get('id');

        /* Получаю сообщения*/

        $this->data['messages'] = $this->model->getMessagesInbox($id);
        $this->data['isset_answer'] = $this->model->checkIssetAnswer($id);
        if(!$this->data['messages']){
            Session::setFlash('nodata', '<span class="bold">На данный момент Вы не получали сообщений от пользователей</span>.');
        }

        /* Получаю количество сообщений outbox*/
        $this->data['count_outbox'] = $this->model->getCountOutbox($id);

        /* Получаю количество сообщений inbox*/
        if(empty($this->data['messages'])){
            $this->data['count_inbox'] = 0;
        } else {
            $this->data['count_inbox'] = count($this->data['messages']);
        }

        $count_messages = $this->data['count_outbox'] + $this->data['count_inbox'];
        Session::set('count_messages', $count_messages);

    }

    public function user_new(){

        $id = Session::get('id');

        /* Проверяю рассылка конкретному пользователю или из списка */

        $params = App::getRouter()->getParams();

        if(isset($params[0])){
            $user_id = $params[0];
            $data = $this->model->getUserName($user_id);
            $this->data['user_id'] = $data[0]['id'];
            $this->data['firstname'] = $data[0]['firstname'];
            $this->data['lastname'] = $data[0]['lastname'];
        }

        if($_POST){

            $cat_id = $_POST['category'];
            $rank_id = $_POST['rank'];
            if($rank_id == 0){
                $user_rank = $_POST['user_rank'];
                $data = $this->model->checkIssetRank($user_rank);
                if(!empty($data)){
                    $rank_id = $data[0]['id'];
                }
            }
            $for = $_POST['for'];
            $date = date('Y:m:d H:i:s');

            $text = trim($_POST['text']);
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = stripcslashes($text);

            if($for === 'all'){
                $users = $this->model->getUsersForMessage($cat_id, $rank_id, $id);
                if(!$users){
                    Session::setFlash('nousers', '<span class="bold">К сожалению нет специалистов по данной теме.</span> Попробуйте выбрать другую категорию или специальность и отправьте ещё раз.');
                } else {
                    $this->model->insert($id, $cat_id, $rank_id, $text, $date, $users);
                    Session::setFlash('send', "<span class='bold'>Ваш вопрос будет отправлен всем указаным специалистам после проверки нашими модераторами</span>. Вам прийдет email уведомление с подтверждением. Для просмотра Ваших сообщений перейдите в раздел <a href='/user/messages/'>Мои сообщения</a>.");
                }
            } elseif($for === 'my'){
                if(!$this->model->checkIssetUser($id)){
                    Session::setFlash('nousers', '<span class="bold">В вашем списке нет специалистов.</span> Можете выбрать категорию Всем специалистам или отправить вопрос конкретному специалисту.');
                } else {
                    $users = $this->model->getUsersForMessageMy($cat_id, $rank_id, $id);
                    if(!$users){
                        Session::setFlash('nousers', '<span class="bold">К сожалению нет специалистов по данной теме.</span> Попробуйте выбрать другую категорию или специальность и отправьте ещё раз.');
                    } else {
                        $this->model->insert($id, $cat_id, $rank_id, $text, $date, $users);
                        Session::setFlash('send', "<span class='bold'>Ваш вопрос будет отправлен Вашим специалистам после проверки нашими модераторами</span>. Вам прийдет email уведомление с подтверждением. Для просмотра Ваших сообщений перейдите в раздел <a href='/user/messages/'>Мои сообщения</a>.");
                    }
                }
            } else {
                $users = $_POST['for'];
                $data = $this->model->getUserName($users);
                $firstname = $data[0]['firstname'];
                $lastname = $data[0]['lastname'];
                $this->model->insert($id, $cat_id, $rank_id, $text, $date, $users);
                Session::setFlash('send', "<span class='bold'>Ваш вопрос будет отправлен пользователю $firstname $lastname после проверки нашими модераторами</span>. Вам прийдет email уведомление с подтверждением. Для просмотра Ваших сообщений перейдите в раздел <a href='/user/messages/'>Мои сообщения</a>.");

            }

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
            $mail->addAddress(Config::get('smtp_username'), "Admin");
            $mail->Subject = htmlspecialchars('Модерация сообщения');
            $text = file_get_contents(MAIL_PATH."/mail_moderation_message.html");
            $text = str_replace(['%name%'], ['Александр'], $text);
            $mail->Body = "$text";
            $mail->isHTML(true);
            $mail->send();
            $mail->ClearAddresses();
            $mail->ClearAttachments();

        }

        /*Формирую данные для select */
        $this->data['categories'] = $this->model->getCategories();
        $this->data['ranks'] = $this->model->getRanks(1);
    }

    public function user_outview(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        /* Формирую данные для темы*/

        $this->data['message'] = $this->model->getUserMessageOut($message_id);

        $user_id = $this->data['message'][0]['user_id'];
        $this->data['position'] = $this->model->getUserPosition($user_id);

        $this->data['message_id'] = $message_id;
        $this->data['user_id'] = $this->data['message'][0]['user_id'];
        $this->data['firstname'] = $this->data['message'][0]['firstname'];
        $this->data['lastname'] = $this->data['message'][0]['lastname'];
        $this->data['avatar'] = $this->data['message'][0]['avatar'];
        $this->data['answers'] = $this->data['message'][0]['answers'];
        $this->data['rank'] = $this->data['message'][0]['rank'];
        $this->data['text'] = $this->data['message'][0]['text'];
        $users = $this->data['message'][0]['users'];
        $array = explode(', ', $users);
        $count = count($array);
        $this->data['gets'] = $count;

        $this->data['data_answers'] = $this->model->getAnswers($message_id);

        if(empty($this->data['answers'])){
            Session::setFlash('nodata', '<span class="bold">К сожалению нет ответов на данный вопрос.</span> Возможно прошло слишком мало времени. Попробуйте задать другой вопрос или воспользуйтесь поиском на сайте.');
        }
    }

    public function user_inview(){

        $id = Session::get('id');

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        /* Формирую данные для темы*/

        $this->data['message'] = $this->model->getUserMessageIn($message_id);

        $user_id = $this->data['message'][0]['user_id'];
        $this->data['position'] = $this->model->getUserPosition($user_id);

        $this->data['message_id'] = $message_id;
        $this->data['user_id'] = $this->data['message'][0]['user_id'];
        $this->data['firstname'] = $this->data['message'][0]['firstname'];
        $this->data['lastname'] = $this->data['message'][0]['lastname'];
        $this->data['avatar'] = $this->data['message'][0]['avatar'];
        $this->data['city'] = $this->data['message'][0]['city'];
        $this->data['rank'] = $this->data['message'][0]['rank'];
        $this->data['text'] = $this->data['message'][0]['text'];
        $date_data = $this->data['message'][0]['date'];
        $date_array = explode(' ', $date_data);
        $this->data['date'] = $date_array[0];
        $this->data['time'] = substr($date_array[1], 0, -3);

        if($_POST){

            $answer = trim($_POST['answer']);
            $answer = htmlspecialchars($answer, ENT_QUOTES);
            $answer = stripcslashes($answer);

            $date = date('Y:m:d H:i:s');

            $this->model->setAnswer($id, $message_id, $date, $answer);

        }

        $this->data['answer'] = $this->model->getUserAnswer($id, $message_id);

    }

    public function user_outdelete(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $this->model->deleteMessageOut($message_id);

        Session::set('count_messages', Session::get('count_messages')-1);

        Router::redirect('/user/messages/outbox/');

    }

    public function user_indelete(){

        $id = Session::get('id');

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $this->model->deleteMessageIn($message_id, $id);

        Session::set('count_messages', Session::get('count_messages')-1);

        Router::redirect('/user/messages/inbox/');

    }

    public function admin_index(){

        /* Получаю все сообщения */
        $this->data['messages'] = $this->model->getAllMessages();

    }

    public function admin_edit()
    {

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $this->data['message_id'] = $message_id;

        if ($_POST) {

            $text = trim($_POST['text']);
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = stripcslashes($text);

            $this->model->updateMessage($message_id, $text);

            Session::setFlash('update', '<span class="bold">Вопрос успешно редактирован</span>.');

        }

        $this->data['message'] = $this->model->getMessageInfo($message_id);
    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $message_id = $params[0];

        $this->model->deleteMessageOut($message_id);

        Router::redirect('/admin/messages/index');

    }
}