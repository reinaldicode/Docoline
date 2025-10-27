<?php

include ("modul/jpgraph.php");
include ("modul/jpgraph_line.php");
include ("modul/jpgraph_bar.php");

$dataJum = array();
$dataTh = array();

$link = mysqli_connect("localhost","root","123456789","doc");
//mysql_select_db("doc");

$query = "SELECT status,count(no_drf) as jum from docu group by status";
$hasil = mysqli_query($link, $query);
while ($data = mysqli_fetch_array($hasil))
{
	array_unshift($dataJum, $data['jum']);
    array_unshift($dataTh, $data['status']);
}

$graph = new Graph(300,250,"auto");    
$graph->SetScale("textlin");

// menampilkan plot batang dari data jumlah penduduk
$bplot = new BarPlot($dataJum);
$graph->Add($bplot);

// menampilkan plot garis dari data jumlah penduduk
$lineplot=new LinePlot($dataJum);
$graph->Add($lineplot);


$graph->img->SetMargin(40,20,20,40);
$graph->title->Set("Grafik Jumlah Dokumen berdasarkan status");
$graph->xaxis->title->Set("Status");
$graph->yaxis->title->Set("Jumlah");
$graph->xaxis->SetTickLabels($dataTh);

$graph->title->SetFont(FF_FONT1,FS_BOLD);

$lineplot->SetColor("blue");
$bplot->SetFillColor("red");

$graph->SetShadow();
$graph->Stroke();
?>
