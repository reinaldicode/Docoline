<?php 
include "koneksi.php";

			//$id_doc=$_Post['drf'];
			// $id_dis=$_Post['id_dis'];
			// $pic=$_Post['pic'];
			// $drf=$_Post['drf'];
			// $nama=$_Post['nama'];
			// $no_doc=$_Post['no_doc'];
			// $qty=$_Post['qty'];

				$tglsekarang = date('d-m-Y');
			$sql = "UPDATE distribusi SET retrieve='$qty' ,retrieve_from='$pic', retrieve_date='$tglsekarang' WHERE id_dis=$id_dis and no_drf=$drf" ; 
						// echo $sql;
						mysqli_query($link, $sql); 			// Mengeksekusi syntak UPDDate nya ..

						$sql_ins="SELECT no_drf FROM docu WHERE no_doc = '$no_doc' AND no_rev = (SELECT max( no_rev ) FROM docu WHERE no_doc = '$no_doc')";
						$res_ins=mysqli_query($link, $sql_ins);
						while($info = mysqli_fetch_array($res_ins))
						{
							$new_drf=$info[no_drf];
						}
						
					echo $new_drf;

					$insert="insert into distribusi(id_dis,no_drf,pic,give,date_give,location,receiver,retrieve,retrieve_from,retrieve_date)
							 values('','$new_drf','','','','','','','','')";
					$result=mysqli_query($link, $insert);
					

					
						
						

						




			
			




										
				?>
						<script language='javascript'>
							alert('Document Was Distributed');
							document.location='detail_dist.php?drf=<?php echo $drf;?>&no_doc=<?php echo $no_doc;?>&title=<?php echo $title;?>';
						</script>
						
						
				<?php 
			
 
  
//include 'footer.php';


?>
