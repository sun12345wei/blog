<?php
// 定义常量
define('ROOT', dirname(__FILE__) . '/../');

// 实现类的自动加载
function autoLoad($class)
{
    $path = str_replace('\\','/', $class);

    require(ROOT . $path . '.php');
}

spl_autoload_register('autoLoad');

// 添加路由 ：解析 URL 上的路径：   控制器/方法
// 获取 URL 上的路径

if( isset($_SERVER['PATH_INFO']) )
{
    $pathInfo = $_SERVER['PATH_INFO'];
    // 根据 / 转成数组
    $pathInfo = explode('/', $pathInfo);

    // 得到控制器名和方法名
    $controller = ucfirst($pathInfo[1]) . 'Controller';
    $action = $pathInfo[2];
}
else
{
    // 默认控制器和方法
    $controller = 'IndexController';
    $action = 'index';
}


// 为控制器添加命名空间
$fullController = 'controllers\\'.$controller;

$_C = new $fullController;
$_C->$action();

// 加载视图
function view($viewFileName, $data = [])
{
    extract($data);

    $path = str_replace('.', '/', $viewFileName) . '.html';

    // 加载视图
    require(ROOT . 'views/' . $path);
}
