<?php
                if(isset($_GET['manage'])){
                    $tmp = $_GET['manage'];
                }else{
                    $tmp = '';
                }
                if($tmp == 'products'){
                   include('main/products.php');
                }elseif($tmp =='single-product'){
                    include('main/single-product.php');
                }elseif($tmp =='about'){
                    include('main/about.php');
                }elseif($tmp =='contact'){
                    include('main/contact.php');
                }else{
                    include('main/index.php');
                }
?>