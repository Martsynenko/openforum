<?php

class RankController extends Controller
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelRank();
    }

    public function admin_index(){

        /* Получаю все специальности */

        $this->data['rank'] = $this->model->getAllRank();

    }

    public function admin_add(){

        /* Получаю все категории */

        $this->data['category'] = $this->model->getAllCategory();

        if($_POST){

            $cat_id = $_POST['category'];

            $title = trim($_POST['title']);
            $title = strip_tags($title);
            $title = htmlspecialchars($title, ENT_QUOTES);
            $title = stripcslashes($title);

            if($this->model->checkIssetRank($title)){
                $this->model->insertRank($title, $cat_id);
                Session::setFlash('add', '<span class="bold">Специальность успешно добавлена!</span>');
            } else {
                Session::setFlash('wrong_rank','<span class="bold">Данная специальность уже добавлена!</span>');
            }

        }
    }

    public function admin_edit(){

        $params = App::getRouter()->getParams();

        $rank_id = $params[0];

        $this->data['rank_id'] = $rank_id;

        if($_POST){

            $cat_id = $_POST['category'];

            $title = trim($_POST['title']);
            $title = strip_tags($title);
            $title = htmlspecialchars($title, ENT_QUOTES);
            $title = stripcslashes($title);

            $this->model->updateRank($rank_id, $title, $cat_id);

            Session::setFlash('update', '<span class="bold">Специальноть успешно обновлена!</span>');
        }

        $this->data['category'] = $this->model->getAllCategory();
        $this->data['cat_id'] = $this->model->getCategoryByRankID($rank_id);
        $this->data['rank'] = $this->model->getRankByID($rank_id);

    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $rank_id = $params[0];

        $bool = $this->model->checkForeignKeyRank($rank_id);
        if($bool){
            $this->model->deleteRank($rank_id);
        } else {
            Session::set('prohibition_rank', '<span class="bold">Вы не можете удалить данную специальность, так как некоторые пользователи ссылаются на нее!</span>');
        }

        Router::redirect('/admin/rank/index');

    }
}