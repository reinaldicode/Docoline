<?php
// PDO connect *********
function connect() {
    return new PDO('mysql:host=localhost;dbname=doc', 'root', 'SSItop123!', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
}

$pdo = connect();
$keyword = '%'.$_POST['keyword'].'%';
$sql = "SELECT * FROM document WHERE no_doc LIKE (:keyword) ORDER BY no_drf ASC LIMIT 0, 10";
$query = $pdo->prepare($sql);
$query->bindParam(':keyword', $keyword, PDO::PARAM_STR);
$query->execute();
$list = $query->fetchAll();
foreach ($list as $rs) {
	// put in bold the written text
	$no_doc = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs['no_doc']);
	// add new option
    echo '<li class="dropdown" onclick="set_item(\''.str_replace("'", "\'", $rs['no_doc']).'\')">'.$no_doc.'</li>';
}


function connect2() {
    return new PDO('mysql:host=localhost;dbname=doc', 'root', 'SSItop123!', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
}

$pdo2 = connect2();
$keyword2 = '%'.$_POST['keyword'].'%';
$sql2 = "SELECT * FROM document WHERE title LIKE (:keyword) ORDER BY title ASC LIMIT 0, 10";
$query2 = $pdo2->prepare($sql2);
$query2->bindParam(':keyword', $keyword, PDO::PARAM_STR);
$query2->execute();
$list2 = $query2->fetchAll();
foreach ($list2 as $rs2) {
	// put in bold the written text
	$title = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs['title']);
	// add new option
    echo '<li class="dropdown" onclick="set_item(\''.str_replace("'", "\'", $rs['title']).'\')">'.$title.'</li>';
}
?>