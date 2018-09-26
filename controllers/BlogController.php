<?php
namespace controllers;

use PDO;

Class BlogController
{
    // 日志列表
    public function index()
    {
        // 取日志的数据
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog001', 'root', '');
        $pdo->exec('SET NAMES utf8');

        // 设置的 $where
        $where = ' 1 ';

        // 放预处理对应的值
        $value = [];

        // 如果有keword 并值不为空时
        if(isset($_GET['keyword']) && $_GET['keyword'])
        {
            $where .= " AND (title LIKE ? OR content LIKE ?)";
            $value[] = '%'.$_GET['keyword'].'%';
            $value[] = '%'.$_GET['keyword'].'%';
        }

        if(isset($_GET['start_date']) && $_GET['start_date'])
        {
            $where .= "AND created_at >= ?";
            $value[] = $_GET['start_date'];
        }

        if(isset($_GET['end_date']) && $_GET['end_date'])
        {
            $where .= "AND created_at <= ?";
            $value[] = $_GET['end_date'];
        }

        if(isset($_GET['is_show']) && ($_GET['is_show']== 1 || $_GET['is_show']==='0'))
        {
            $where .= "AND is_show = ?";
            $value[] = $_GET['is_show'];
        }

        /************* 排序 *****************/
        // 默认排序
        $odby = 'created_at';
        $odway = 'desc';

        if(isset($_GET['odby']) && $_GET['odby'] == 'display' )
        {
            $odby = 'display';
        }

        if(isset($_GET['odway']) && $_GET['odway'] == 'asc' )
        {
            $odway = 'asc';
        }

        /************* 翻页 *****************/
        $prepare = 15;  // 每页15
        // // 接收当前页码（大于等于1的整数）, max
        $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1 ;
        // // 计算开始的下标
        $offset = ($page-1)*$prepare;

        // // 制作按钮
        // // 取出总的记录数
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE $where");
        $stmt->execute($value);
        $count = $stmt->fetch(PDO::FETCH_COLUMN);

        // // 计算总的页数
        $pageCount = ceil( $count / $prepare );

        $btns = '';
        for($i=1;$i<=$pageCount;$i++)
        {
            // 先获取之前的参数
            $params = getUrlParams(['page']);

            $class = $page==$i ? 'active' : '';
            $btns .= "<a class='$class' href='?{$params}page=$i'> $i </a>";
            
        }
    
        /*************** 执行 SQL ***************/
        // 预处理  SQL
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE $where ORDER BY $odby $odway LIMIT $offset,$prepare");
        // 执行 SQL
        $stmt->execute($value);

        // 取数据
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 加载视图
        view('blogs.index', [
            'data' => $data,
            'btns' => $btns,
        ]);
    }

    public function content_to_html()
    {
        // 取日志的数据
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog001', 'root', '');
        $pdo->exec('SET NAMES utf8');

        $stmt = $pdo->query('SELECT * FROM blogs');
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();

        // 生成静态页
        foreach($blogs as $v)
        {
            view('blogs.content', [
                'blog' => $v,
            ]);

            $str = ob_get_contents();
            file_put_contents(ROOT.'public/contents/'.$v['id'].'.html', $str);
            ob_clean();
        }
    }
}