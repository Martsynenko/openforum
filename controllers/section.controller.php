<?php

class SectionController extends Controller
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->model = new modelSection();
    }

    public function admin_index(){

        /* Получаю все категории */

        $this->data['category'] = $this->model->getAllCategory();
    }

    public function admin_add(){

        if($_POST){

            $title = trim($_POST['title']);
            $title = strip_tags($title);
            $title = htmlspecialchars($title, ENT_QUOTES);
            $title = stripcslashes($title);

            if(!$this->model->checkIssetCategory($title)){
                $this->model->insertCategory($title);
                Session::setFlash('add', '<span class="bold">Категория успешно добавлена!</span>');
            } else {
                Session::setFlash('isset', '<span class="bold">Данная категория уже добавлена!</span>');
            }
        }
    }

    public function admin_edit(){

        $params = App::getRouter()->getParams();

        $cat_id = $params[0];

        $this->data['cat_id'] = $cat_id;

        if($_POST){

            $title = trim($_POST['title']);
            $title = strip_tags($title);
            $title = htmlspecialchars($title, ENT_QUOTES);
            $title = stripcslashes($title);

            $this->model->updateCategory($cat_id, $title);

            Session::setFlash('update', '<span class="bold">Категория успешно обновлена!</span>');
        }

        $this->data['category'] = $this->model->getCategoryByID($cat_id);

    }

    public function admin_delete(){

        $params = App::getRouter()->getParams();

        $cat_id = $params[0];

        $bool = $this->model->checkForeignKeyCategory($cat_id);
        if($bool){
            $this->model->deleteCategory($cat_id);
        } else {
            Session::set('prohibition_category', '<span class="bold">Вы не можете удалить данную категорию, так как некоторые пользователи ссылаются на нее!</span>');
        }

        Router::redirect('/admin/section/index');
    }
}