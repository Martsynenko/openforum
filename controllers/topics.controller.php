<?php
class TopicsController extends Controller {

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelTopic();
    }

    public function newtopic(){

        if($_POST){

            $cat_id = $_POST['category'];
            $rank_id = $_POST['rank'];
            if($rank_id == 0){
                $user_rank = $_POST['user_rank'];
                $firstletter = mb_substr($user_rank, 0, 1);
                $firstletter = mb_strtoupper($firstletter);
                $user_rank = $firstletter.mb_substr($user_rank, 1);
                $data = $this->model->checkIssetRank($user_rank);
                if(empty($data)){
                    $this->model->insertUserRank($cat_id, $user_rank);
                    $rank_id = $this->model->getUserRank($user_rank);
                } else {
                    $rank_id = $data[0]['id'];
                }
            }
            $date = date("Y:m:d H:i:s");
            $user_id = Session::get('id');

            $subject = trim($_POST['subject']);
            $subject = strip_tags($subject);
            $subject = htmlspecialchars($subject, ENT_QUOTES);
            $subject = stripcslashes($subject);

            $text = trim($_POST['text']);
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = stripcslashes($text);

            $this->model->insertTopic($cat_id, $rank_id, $subject, $text, $date, $user_id);

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
            $mail->Subject = htmlspecialchars('Модерация темы');
            $text = file_get_contents(MAIL_PATH."/mail_moderation_topic.html");
            $text = str_replace(['%name%'], ['Александр'], $text);
            $mail->Body = "$text";
            $mail->isHTML(true);
            $mail->send();
            $mail->ClearAddresses();
            $mail->ClearAttachments();

            $count_topics = $this->model->CountTopics($user_id);
            if(empty($count_topics)) $count_topics = '0';
            Session::set('count_topics', $count_topics);
            Session::setFlash('notice_insert', '<span class="bold">Ваша тема будет опубликована в течении часа после проверки нашими модераторами!</span> Для просмотра посещений вашей темы, а также ответы перейдите в раздел <a href="/topics/my/">Мои темы</a>. Также можете воспользоваться поиском на главной странице.');

        }

        /* Получаю данные для select*/
        $this->data['categories'] = $this->model->getCategories();
    }

    public function my(){

        $this->data['prev_uri'] = $_SERVER['HTTP_REFERER'];

        $params = App::getRouter()->getParams();

        if(!empty($params[0])){
            $page = $params[0];
        } else {
            $page = 1;
        }

        $id = Session::get('id');

        $count_topics = $this->model->countTopics($id);
        if(empty($count_topics)) $count_topics = '0';
        Session::set('count_topics', $count_topics);
        $this->data['count_user_topics'] = $count_topics;

        $this->data['topics'] = $this->model->getUserTopics($id, $page);

        $this->data['views'] = $this->model->getCountViews();

        if(empty($this->data['topics'])){
            Session::setFlash('nodata', '<span class="bold">Вы можете опубликовать новую тему прямо сейчас. </span>Для этого перейдите в раздел <a href="/topics/newtopic/">Создать тему</a>. Или найдите похожую тему на <a href="/">форуме</a>. Также можете воспользоваться поиском для поиска похожих тем.');
        }

        /* Формирую pagination*/
        $page_previous = $page-1;
        $page_next = $page+1;
        $link_previous = "/topics/my/$page_previous";
        $link_next = "/topics/my/$page_next";
        $count_topics = $this->model->countTopics($id);

        $count_pages = $count_topics/5;
        if(is_float($count_pages)) {
            $count_pages = $count_pages + 1;
            $count_pages = (integer)$count_pages;
        }
        if($page>=3){
            $page_start = $page-2;
        } else $page_start = 1;

        $this->data['count_topics'] = $count_topics;
        $this->data['page'] = $page;
        $this->data['link_previous'] = $link_previous;
        $this->data['link_next'] = $link_next;
        $this->data['count_pages'] = $count_pages;
        $this->data['page_start'] = $page_start;


    }

    public function user_index(){

    }

    public function view(){

        $params = App::getRouter()->getParams();

        if(!empty($params[0])){
            $page = $params[0];
        } else {
            $page = 1;
        }

        $topic_id = $params[1];

        /* Проверяю на отправление сообщения с формы */

        if($_POST&&isset($_POST['comment'])) {

            $answer = trim($_POST['answer']);
            $answer = htmlspecialchars($answer, ENT_QUOTES);
            $answer = stripcslashes($answer);

            $date = date('Y:m:d H:i:s');

            $id = Session::get('id');

            $this->model->setAnswer($id, $topic_id, $date, $answer);
            $answer_id = $this->model->getAnswerID($id, $date, $answer);

            $comment_id = $_POST['comment'];

            /* Получаю данные для записи в базу */

            $data = $this->model->getAnswerInfoByID($comment_id);

            $firstname = $data[0]['firstname'];
            $lastname = $data[0]['lastname'];
            $date_answer = $data[0]['date'];
            $text = $data[0]['text'];

            $this->model->insertAnswerComment($topic_id, $answer_id, $firstname, $lastname, $date_answer, $text);

        } elseif($_POST){

            $answer = trim($_POST['answer']);
            $answer = htmlspecialchars($answer, ENT_QUOTES);
            $answer = stripcslashes($answer);

            $date = date('Y:m:d H:i:s');

            $id = Session::get('id');

            $this->model->setAnswer($id, $topic_id, $date, $answer);

        }

        /* Формирую данные для темы*/

        $this->data['topics'] = $this->model->getUserTopic($topic_id);

        $this->data['topic_id'] = $topic_id;
        $this->data['user_id'] = $this->data['topics'][0]['user_id'];
        $avatar = $this->data['topics'][0]['avatar'];
        if(empty($avatar)) $avatar = Config::get('avatar');
        $this->data['avatar'] = $avatar;
        $this->data['city'] = $this->data['topics'][0]['city'];
        $this->data['firstname'] = $this->data['topics'][0]['firstname'];
        $this->data['lastname'] = $this->data['topics'][0]['lastname'];
        $user_rank = $this->data['topics'][0]['rank_id'];
        $this->data['user_rank'] = $this->model->getTitleUserRank($user_rank);
        $this->data['category'] = $this->data['topics'][0]['category'];
        $this->data['rank'] = $this->data['topics'][0]['rank'];
        $this->data['subject'] = $this->data['topics'][0]['subject'];
        $this->data['text'] = $this->data['topics'][0]['text'];
        $date_data = $this->data['topics'][0]['date'];
        $date_array = explode(' ', $date_data);
        $this->data['date'] = $date_array[0];
        $this->data['time'] = substr($date_array[1], 0, -3);
        $this->data['views'] = $this->model->getCountViewsByID($topic_id);

        $this->data['answers'] = $this->model->getAnswers($page, $topic_id);

        if(empty($this->data['answers'])){
            Session::setFlash('nodata', '<span class="bold">К сожалению нет ответов на данную тему.</span> Возможно прошло слишком мало времени. Вы можете воспользоваться раcсылкой сообщений напрямую специалистам, которые специализируються в данной специальности.');
        }

        /* Формирую данные вложеных ответов */

        $this->data['comments'] = $this->model->getComments($topic_id);

        /* Формирую pagination*/
        $page_previous = $page-1;
        $page_next = $page+1;
        $link_previous = "/topics/view/$page_previous/$topic_id";
        $link_next = "/topics/view/$page_next/$topic_id";
        $count_topics = $this->model->countAnswers($topic_id);

        $count_pages = $count_topics/5;
        if(is_float($count_pages)) {
            $count_pages = $count_pages + 1;
            $count_pages = (integer)$count_pages;
        }
        if($page>=3){
            $page_start = $page-2;
        } else $page_start = 1;

        $this->data['topic_id'] = $topic_id;
        $this->data['count_topics'] = $count_topics;
        $this->data['page'] = $page;
        $this->data['link_previous'] = $link_previous;
        $this->data['link_next'] = $link_next;
        $this->data['count_pages'] = $count_pages;
        $this->data['page_start'] = $page_start;
        $this->data['link_action'] = "/topics/view/$page/$topic_id";


    }

    public function delete(){

        $params = App::getRouter()->getParams();

        $topic_id = $params[0];

        $this->model->deleteTopic($topic_id);

        $user_id = Session::get('id');

        $count_topics = $this->model->CountTopics($user_id);
        if(empty($count_topics)) $count_topics = '0';
        Session::set('count_topics', $count_topics);

        Router::redirect('/topics/my/1/');

    }

    public function admin_edit(){

        $params = App::getRouter()->getParams();

        $topic_id = $params[0];

        $this->data['topic_id'] = $topic_id;

        if($_POST){

            $subject = trim($_POST['title']);
            $subject = htmlspecialchars($subject, ENT_QUOTES);
            $subject = stripcslashes($subject);

            $text = trim($_POST['text']);
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = stripcslashes($text);

            $this->model->updateTopic($topic_id, $subject, $text);

            Session::setFlash('update', '<span class="bold">Тема успешно редактирована</span>.');

        }

        $this->data['topic'] = $this->model->getTopicInfo($topic_id);

    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $topic_id = $params[0];

        $this->model->deleteTopic($topic_id);

        Router::redirect('/admin/forum/index');

    }
}