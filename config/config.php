<?php

Config::set('site_name', 'openForum');

Config::set('routes', ['default' => '', 'admin' => 'admin_', 'user' => 'user_']);

Config::set('default_route', 'default');
Config::set('default_controller', 'forum');
Config::set('default_controller_user', 'messages');
Config::set('default_action_user', 'outbox');
Config::set('default_action', 'index');

Config::set('db_host', 'localhost');
Config::set('db_user', 'root');
Config::set('db_password', '');
Config::set('db_name', 'db_openforum');

//Config::set('db_host', 'localhost');
//Config::set('db_user', 'openforu_Aleksandr');
//Config::set('db_password', 'martsynenko1989');
//Config::set('db_name', 'openforu_db_openforum');

Config::set('salt', 'enuri87879fioset989399');

Config::set('avatar', '/images/admin_images/no-avatar.jpg');

Config::set('smtp_host', 'smtp.gmail.com');
Config::set('smtp_auth', true);
Config::set('smtp_port', '465');
Config::set('smtp_username', 'noreply.openforum@gmail.com');
Config::set('smtp_password', 'martsynenko1989');
Config::set('smtp_addreply', 'noreply.openforum@gmail.com');
Config::set('smtp_secure', 'ssl');
Config::set('smtp_mail_name', 'Admin');
Config::set('smtp_charset', 'UTF-8');