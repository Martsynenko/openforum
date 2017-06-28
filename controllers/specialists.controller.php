<?php

class SpecialistsController extends Controller {

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelSpecialists();
    }

    public function index(){

        $params = App::getRouter()->getParams();

        if(!empty($params[0])){
            $page = $params[0];
        } else {
            $page = 1;
        }

        if($_POST){
            $rank = $_POST['rank'];
            if($rank == 0){
                $user_rank = $_POST['user_rank'];
                $data = $this->model->checkIssetRank($user_rank);
                if(!empty($data)){
                    $rank = $data[0]['id'];
                }
            }
            $_SESSION['sort'] = [$_POST['category'], $rank];
        }

        $cat = null;
        $rank = null;
        if(isset($_SESSION['sort'])&&!empty($params[1])&&$params[1] == 'sort') {
            $cat = $_SESSION['sort'][0];
            $rank = $_SESSION['sort'][1];
            $this->data['topics'] = $this->model->getSpecialistsSort($page, $cat, $rank);
        } else {
            $this->data['topics'] = $this->model->getSpecialists($page);
        }

        if(empty($this->data['topics'])){
            Session::setFlash('nodata', '<span class="bold">К сожалению нет специалистов по данному запросу.</span> Попробуйте выбрать другую категорию или специальность.');
        }

        /* Формирую pagination*/
        $count_topics = null;
        $page_previous = $page-1;
        $page_next = $page+1;
        if(isset($_SESSION['sort'])&&!empty($params[1])&&$params[1] == 'sort') {
            $link_previous = "/specialists/index/$page_previous/sort";
            $link_next = "/specialists/index/$page_next/sort";
            $count_topics = $this->model->countTopicsSort($cat, $rank);
        } else {
            $link_previous = "/specialists/index/$page_previous";
            $link_next = "/specialists/index/$page_next";
            $count_topics = $this->model->countTopics();
        }

        $count_pages = $count_topics/20;
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

        /*Формирую данные для select */
        $this->data['categories'] = $this->model->getCategories();
    }

    public function view(){

        $id = Session::get('id');

        $params = App::getRouter()->getParams();

        $specailist_id = $params[0];

        if(isset($params[1])){
            $action = $params[1];
            if($action == 'add'){

                $this->model->add($id, $specailist_id);

                Session::set('count_specialists', Session::get('count_specialists')+1);
                Session::setFlash('add_spec', '<span class="bold">Пользователь добавлен в Ваш список.</span> Для просмотра всех Ваших специалистов перейдите в раздел <a href="/user/specialists/index/">Мои специалисты</a>.');

            }
        }

        /* Проверяю добавлен ли пользователь */
        if(isset($_SESSION['auth_user'])){
            $bool = $this->model->checkIssetSpecialist($id, $specailist_id);
            $this->data['btn_add'] = $bool;
        }

        /* Формирую данные пользователя */
        $this->data['specialist_id'] = $specailist_id;
        $this->data['specialist'] = $this->model->getSpecialistInfo($specailist_id);
        $this->data['popular_topics'] = $this->model->getPopularTopics($specailist_id);
        if(!$this->data['popular_topics']){
            $this->data['last_topics'] = $this->model->getLastTopics($specailist_id);
        }

        /* Формирую возраст */
        $data_age = $this->data['specialist']['year'];
        $year = date('Y');
        $age = $year-$data_age;
        $this->data['age'] = $age;

        /* Формирую количество тем и ответов */
        $this->data['user_topics'] = $this->model->getCountUserTopics($specailist_id);
        $this->data['user_answers'] = $this->model->getCountUserAnswers($specailist_id);

        /* Формирую похожих специалистов */
        $rank_id = $this->model->getUserRank($specailist_id);
        $this->data['similar'] = $this->model->getSimilarUsers($rank_id, $specailist_id);

        if(empty($this->data['similar'])){
            Session::setFlash('nodata', '<span class="bold">К сожалению нет похожих специалистов. Попробуйте выбрать другого специалиста в данной категории. Можете воспользоваться сортировкой для задания критериев поиска.</span>');
        }
    }

    public function user_index(){

        $id = Session::get('id');

        $this->data['specialists'] = $this->model->getListSpecialist($id);
//        $this->data['answers'] = $this->model->getListCountAnswers();

        if(!$this->data['specialists']){
            Session::setFlash('nodata', '<span class="bold">Вы еще не добавляли себе в список специалистов.</span> Для добавления новых специалистов перейдите в раздел <a href="/specialists/">Специалисты</a>.');
        }
    }

    public function user_delete(){

        $id = Session::get('id');

        $params = App::getRouter()->getParams();

        $specialist_id = $params[0];

        $this->model->deleteSpecialist($id, $specialist_id);

        Session::set('count_specialists', Session::get('count_specialists')-1);

        Router::redirect('/user/specialists/index/');

    }
}