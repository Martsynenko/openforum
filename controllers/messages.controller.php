<?php
class MessagesController extends Controller {

    public function index(){
        $this->data['content'] = 'Здесь будет главная страница сайта форума';
    }
}