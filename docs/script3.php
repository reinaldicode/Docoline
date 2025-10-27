<?php // content="text/plain; charset=utf-8"
require_once ('modul/jpgraph.php');
require_once ('modul/jpgraph_bar.php');

$datay=array(62,105,85,50);


$dataJum = array();
$datasec = array();

$link = mysqli_connect("localhost","root","123456789","doc");
//mysql_select_db("doc");

$query = "SELECT section , count( no_drf ) AS jumlah FROM docu GROUP BY section";
$hasil = mysqli_query($link, $query);
while ($data = mysqli_fetch_array($hasil))
{
	array_unshift($dataJum, $data['jumlah']);
    array_unshift($datasec, $data['section']);
}


// Create the graph. These two calls are always required
$graph = new Graph(2500,250,'auto');
$graph->SetScale("textlin");

//$theme_class="DefaultTheme";
//$graph->SetTheme(new $theme_class());

// set major and minor tick positions manually
//$graph->yaxis->SetTickPositions(array(0,5,10,20,30), array(3,7,15,25));
//$graph->SetBox(false);

//$graph->ygrid->SetColor('gray');
$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels($datasec);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

// Create the bar plots
$b1plot = new BarPlot($dataJum);

// ...and add it to the graPH
$graph->Add($b1plot);


$b1plot->SetColor("white");
$b1plot->SetFillGradient("#4B0082","white",GRAD_LEFT_REFLECTION);
$b1plot->SetWidth(45);
$graph->title->Set("Jumlah Dokumen per Section");

// Display the graph
$graph->Stroke();
?>