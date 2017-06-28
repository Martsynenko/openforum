<?php

class ForumController extends Controller {

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelForum();
    }

    public function index(){

        /* Записываю посещение */

        $date = date('Y:m:d');
        $ip = $_SERVER["REMOTE_ADDR"];

        if($this->model->checkVisit($ip, $date)){
            $this->model->insertVisit($ip, $date);
        }

        /* Удаляю просроченых пользователей */
        $time = time();
        $this->model->deleteExpiredUsers($time);

        /* Создаю нового случайного пользователя*/
        if(isset($_SESSION['auth_user'])){
            unset($_SESSION['rand_user']);
        } elseif(!isset($_SESSION['rand_user'])){
            $names = ['Adriegelv', 'Grafyn', 'Nilv', 'Darkredeemer', 'Dawnflame', 'Shalira', 'Faektilar', 'Lanaya', 'Nikora', 'Kelenis', 'Bladeweaver', 'Buzadwyn', 'Thordilas', 'Zulkigrel', 'Anarador'];
            $key_name = array_rand($names);
            $name = $names[$key_name];
            $time_start = time();
            $time_end = $time_start + 86400;
            $this->model->insertRandUser($name, $time_start, $time_end);
            $data = $this->model->getDataUser($time_start);
            Session::set('id', $data['id']);
            Session::set('firstname', $name);
            Session::set('lastname', '');
            Session::set('rand_user', $name);
            Session::set('count_topics', '0');
        } else {
            $time = time();
            $id = Session::get('id');
            if($this->model->checkExpiredUser($id, $time)){
                unset($_SESSION['rand_user']);
                Router::redirect('/');
            }
        }

        /*Формирую данные для select */
        $this->data['categories'] = $this->model->getCategories();

        $params = App::getRouter()->getParams();

        if(!empty($params[0])){
            $page = $params[0];
        } else {
            $page = 1;
        }

        /*Проверка, вывод по умолчанию или сортировка */
        if(isset($_POST['send_sort'])){
            $rank = $_POST['rank'];
            if($rank == 0){
                $user_rank = $_POST['user_rank'];
                $data = $this->model->checkIssetRank($user_rank);
                if(!empty($data)){
                    $rank = $data[0]['id'];
                }
            }
            $_SESSION['sort'] = [$_POST['category'], $rank, $_POST['sort']];
        } elseif (isset($_POST['send_search'])){
            $_SESSION['search'] = $_POST['words'];
        }

        $cat = null;
        $rank = null;
        $sort = null;
        $words = null;
        if(isset($_SESSION['sort'])&&!empty($params[1])&&$params[1] == 'sort') {
            $cat = $_SESSION['sort'][0];
            $rank = $_SESSION['sort'][1];
            $sort = $_SESSION['sort'][2];
            $this->data['topics'] = $this->model->getTopicsSort($page, $cat, $rank, $sort);
            $array_data = $this->model->getIDTopicsSort($cat, $rank, $sort);
            $array_id = [];
            for($i=0;$i<count($array_data);$i++){
                $array_id[] = $array_data[$i]['id'];
            }
            Session::set('topics_id', $array_id);
        } elseif(isset($_SESSION['search'])&&!empty($params[1])&&$params[1] == 'search') {
            $words = $_SESSION['search'];
            $array_id = $this->model->getIDTopicsSearch($words);
            if(!$array_id){
                Session::setFlash('nodata', '<span class="bold">К сожалению нет тем по данному запросу.</span> Вы можете быть первым! Для этого перейдите в раздел <a href="/topics/newtopic/">Создать тему</a>. Или выбирите другую категорию или специальность. Также можете воспользоваться поиском для поиска тем.');
            } else {
                $str_id = implode(', ', $array_id);
                $this->data['topics'] = $this->model->getTopicsSearch($str_id, $words, $page);
                Session::set('topics_id', $array_id);
            }
        } else {
            $this->data['topics'] = $this->model->getTopics($page);
            $array_data = $this->model->getIDTopics();
            $array_id = [];
            for($i=0;$i<count($array_data);$i++){
                $array_id[] = $array_data[$i]['id'];
            }
            Session::set('topics_id', $array_id);
        }

        if(empty($this->data['topics'])){
            Session::setFlash('nodata', '<span class="bold">К сожалению нет тем по данному запросу.</span> Вы можете быть первым! Для этого перейдите в раздел <a href="/topics/newtopic/">Создать тему</a>. Или выбирите другую категорию или специальность. Также можете воспользоваться поиском для поиска тем.');
        }

        /* Получаю данные просмотров */

        $this->data['views'] = $this->model->getCountViews();

        /* Формирую pagination*/
        $count_topics = null;
        $page_previous = $page-1;
        $page_next = $page+1;
        if(isset($_SESSION['sort'])&&!empty($params[1])&&$params[1] == 'sort') {
            $link_previous = "/forum/index/$page_previous/sort";
            $link_next = "/forum/index/$page_next/sort";
            $count_topics = $this->model->countTopicsSort($cat, $rank);
        } elseif(isset($_SESSION['search'])&&!empty($params[1])&&$params[1] == 'search') {
            $link_previous = "/forum/index/$page_previous/search";
            $link_next = "/forum/index/$page_next/search";
            $count_topics = $this->model->countTopicsSearch($words);
        } else {
            $link_previous = "/forum/index/$page_previous";
            $link_next = "/forum/index/$page_next";
            $count_topics = $this->model->countTopics();
        }

        $count_pages = $count_topics/10;
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


        /*Формирую title для заголовка блок-форума */
        if(isset($_SESSION['sort'])&&!empty($params[1])&&$params[1] == 'sort') {
            $this->data['title_category'] = $this->model->getTitleCategory($cat);
            $this->data['title_rank'] = $this->model->getTitleRank($rank);
            if($sort == '0') $this->data['title_sort'] = 'Последние темы';
            elseif($sort == '1') $this->data['title_sort'] = 'Популярные темы';
        }

        /* Формирую данные для autocomplete поиска */
        $auto_category = $this->model->getCategories();
        $auto_str = '';
        for($i=0;$i<count($auto_category);$i++){
            $auto_str .= '"'.$auto_category[$i]['category'].'",';
        }
        $auto_rank = $this->model->getAllRanks();
        for($i=0;$i<count($auto_rank);$i++){
            $auto_str .= '"'.$auto_rank[$i]['rank'].'",';
        }
        $auto_topic = $this->model->getAllTopics();
        for($i=0;$i<count($auto_topic);$i++){
            $auto_str .= '"'.$auto_topic[$i]['subject'].'",';
        }
        $str = substr($auto_str, 0, -1);
        $this->data['auto_str'] = $str;
    }

    public function view(){

        $params = App::getRouter()->getParams();

        if(!empty($params[0])){
            $page = $params[0];
        } else {
            $page = 1;
        }

        $topic_id = $params[1];

        /* Количество просмотров */

        $visitor_ip = $_SERVER['REMOTE_ADDR'];
        $date = date('Y-m-d');
        $this->model->setTopicViews($topic_id, $visitor_ip, $date);

        $this->data['views'] = $this->model->getCountViewsByID($topic_id);

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

        $this->data['user_id'] = $this->data['topics'][0]['user_id'];
        $this->data['firstname'] = $this->data['topics'][0]['firstname'];
        $this->data['lastname'] = $this->data['topics'][0]['lastname'];
        $this->data['avatar'] = $this->data['topics'][0]['avatar'];
        $this->data['city'] = $this->data['topics'][0]['city'];
        $this->data['email'] = $this->data['topics'][0]['email'];
        if(empty($this->data['avatar'])) $this->data['avatar'] = Config::get('avatar');
        $date_data = $this->data['topics'][0]['date'];
        $date_array = explode(' ', $date_data);
        $this->data['date'] = $date_array[0];
        $this->data['time'] = substr($date_array[1], 0, -3);
        $this->data['category'] = $this->data['topics'][0]['category'];
        $this->data['rank'] = $this->data['topics'][0]['rank'];
        $this->data['subject'] = $this->data['topics'][0]['subject'];
        $this->data['text'] = $this->data['topics'][0]['text'];

        /* Формирую ответы */

        $this->data['answers'] = $this->model->getAnswers($page, $topic_id);

        if(empty($this->data['answers'])){
            Session::setFlash('nodata', '<span class="bold">К сожалению нет ответов на данную тему.</span> Вы можете быть первым. Для этого введите ваш ответ в форму.');
        }

        /* Формирую данные вложеных ответов */

        $this->data['comments'] = $this->model->getComments($topic_id);

        /* Формирую pagination*/
        $page_previous = $page-1;
        $page_next = $page+1;
        $link_previous = "/forum/view/$page_previous/$topic_id";
        $link_next = "/forum/view/$page_next/$topic_id";
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
        $this->data['link_action'] = "/forum/view/$page/$topic_id";

        /* Формирую кнопки prev и next*/
        $topics_id = Session::get('topics_id');
        $current_key = array_keys($topics_id, $topic_id);
        $current_key = $current_key[0];
        end($topics_id);
        $last_key = key($topics_id);
        if($current_key == 0) $this->data['btn_prev'] = '';
        else {
            $btn_prev = $topics_id[$current_key-1];
            $this->data['btn_prev'] = "/forum/view/1/$btn_prev";
        }
        if($current_key == $last_key) $this->data['btn_next'] = '';
        else {
            $btn_next = $topics_id[$current_key+1];
            $this->data['btn_next'] = "/forum/view/1/$btn_next";
        }

    }

    public function feedback(){

        if($_POST){
            if(!$this->model->checkValidName($_POST['name'])){
                Session::setFlash('firstname','Имя может содержать только буквы!');
            } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                Session::setFlash('email', 'Проверьте корректность email!');
            } else {

                $firstname = trim($_POST['name']);
                $firstname = strip_tags($firstname);
                $firstname = htmlspecialchars($firstname, ENT_QUOTES);
                $firstname = stripcslashes($firstname);

                $email = trim($_POST['email']);
                $email = strip_tags($email);
                $email = htmlspecialchars($email, ENT_QUOTES);
                $email = stripcslashes($email);

                $subject = trim($_POST['subject']);
                $subject = strip_tags($subject);
                $subject = htmlspecialchars($subject, ENT_QUOTES);
                $subject = stripcslashes($subject);

                $text = trim($_POST['text']);
                $text = strip_tags($text);
                $text = htmlspecialchars($text, ENT_QUOTES);
                $text = stripcslashes($text);

                $date = date('Y:m:d H:i:s');

                $this->model->insertFeedback($firstname, $email, $subject, $text, $date);
                Session::setFlash('notice', "<b>$firstname</b>, Ваше сообщение успешно отправлено администрации сайта openForum. В ближайшее время мы его обработаем и ответим на Ваши вопросы.");
            }
        }
    }

    public function about(){

    }

    public function admin_index(){

        /* Получаю все темы */
        $this->data['topics'] = $this->model->getDataAllTopics();
    }

    public function admin_statistic(){

        $this->data['date'] = date('d.m.Y');

        $this->data['users'] = $this->model->getCountUsers();

        $date = date('Y:m:d');

        $this->data['users_today'] = $this->model->getCountUsersToday($date);

        $this->data['topics'] = $this->model->getCountTopics();

        $this->data['topics_today'] = $this->model->getCountTopicsToday($date);

        $this->data['messages'] = $this->model->getCountMessages();

        $this->data['messages_today'] = $this->model->getCountMessagesToday($date);

        $this->data['visits'] = $this->model->getCountVisits();

        $this->data['visits_today'] = $this->model->getCountVisitsToday($date);

        $this->data['category'] = $this->model->getCountCategory();

        $this->data['rank'] = $this->model->getCountRank();

    }


}