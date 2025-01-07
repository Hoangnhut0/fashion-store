<?php
    if(isset($_GET['action']) && $_GET['query']){
        $tmp = $_GET['action'];
        $query = $_GET['query'];
    }else{
        $tmp = '';
        $query = '';
    }
    if($tmp == 'category' && $query == 'list'){
        include('modules/category/list.php');
    }elseif($tmp == 'category' && $query == 'add'){
        include('modules/category/add.php');
    }elseif($tmp == 'category' && $query == 'edit'){
        include('modules/category/edit.php');
    }elseif($tmp == 'brand' && $query == 'list'){
        include('modules/brand/list.php');
    }elseif($tmp == 'brand' && $query == 'add'){
        include('modules/brand/add.php');
    }elseif($tmp == 'brand' && $query == 'edit'){
        include('modules/brand/edit.php');
    }else{
        include('index.php');
    }
?>