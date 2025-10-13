<?php
require_once("connection.php");

$show_upload_no_photo = '0';
$show_data_total = '1';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Foto Wisuda</title>
	
	<style type="text/css" media="print">
		@page{size:A5 potrait;}.pagebreak{page-break-before:always;}
	</style>
						
	<style>
		body{-webkit-print-color-adjust:exact !important;}
		p{text-align:justify;}
		.font_size_16{font-size:16;}
		.font_size_8pt{font-size:8pt;}
		.bold{font-weight:bold}
		.italic{font-style:italic;}
		.v_top{vertical-align:top;}
		.margin-ul-tb-10 tr td ul{margin:13px;}
		.watermark{position:fixed; top:25%; left:15%;display:block;z-index:-1}
		.linespace{line-height: 1.6;}
	</style>
</head>
<body style="padding:0px; margin:0px;">
	
	<?php
	$i=1;
	$nomor = 1;
	
	// Tentukan array urutan prodi
	$array_urutan = array("Hukum","Manajemen","Akuntansi");
	
	$html_show = "";
	$html_show .=  "<script>console.log('# Data ditampilkan termasuk yg tidak daftar wisuda #');</script>";
	$html_show .=  "<script>console.log('====================================================');</script>";
		$html_show .= '<table style="margin-bottom:10px;" class="font_size_8pt" border=0px>';
		foreach($array_urutan as $prodiforshow){	

			$tableprodi = "tbl_wisudawan";
			if($tableprodi!==""){
				
				//$html_show .= '<div>'.$prodiforshow.'</div>';
				
				$query = "select * from ".$tableprodi." where prodi = '".$prodiforshow."' order by nama asc";
				$execute = mysqli_query($koneksi,$query);
				$total_mhs_alumni = mysqli_num_rows($execute);
				$total_halaman_alumni = ceil($total_mhs_alumni/4);
				$totalmhs = 0;
				$prodi_urutan = 1;
				$page_show = 1;
				
				while($row = mysqli_fetch_array($execute)){

						$URUTAN = strtoupper(strtolower($row['urutan']));
						$NIRM = strtoupper(strtolower($row['nirm']));
						$NAMA = ucwords(strtolower($row['nama']));
						$NAMA_LOG = ucwords(strtolower(str_replace("'"," ",$row['nama'])));
						$ORTULAKI = ucwords(strtolower($row['ortu_laki']));
						$ORTUPEREMPUAN = ucwords(strtolower($row['ortu_perempuan']));
						$TMPTTL = ucwords(strtolower($row['tmp_tgl_lahir']));
						$IPK = str_replace(",",".",$row['ipk']);
						$IPK = number_format($IPK,2);
						$JUDUL = str_replace("?","",$row['judul']);
						$KETERANGAN = strtoupper(strtolower($row['keterangan']));
						$PRODI = strtoupper(strtolower($row['prodi']));
						
						if(is_file("photo/2025/".$NIRM.".jpg")){
							$GAMBAR = "".$NIRM.".jpg";
							
						}else{
							if($show_upload_no_photo=='1'){
								$html_show .= "<script>console.log('".$nomor."');</script>";
								$html_show .= "<script>console.log('".$NAMA." ".$PRODI."');</script>";
							}

							if($URUTAN !== ""){
								$html_show .= "<script>console.log('".$NIRM." ".$NAMA_LOG." [tdk ada foto]');</script>";
								$GAMBAR = "tidak ada";
							}else{
								$html_show .= "<script>console.log('".$NIRM." ".$NAMA_LOG." [tdk ada foto] [tdk wisuda]');</script>";
								$GAMBAR = "tidak ada";
							}
						}

						if($GAMBAR == "tidak ada"){
							$html_show .= '<tr style="color:red">
											<td class="v_top">'.$nomor.'</td>
											<td class="v_top">'.$NIRM.'</td>
											<td class="v_top">'.$NAMA.'</td>
											<td class="v_top">'.$GAMBAR.'</td>
										</tr>';
							
						}else{
							$html_show .= '<tr>
											<td class="v_top">'.$nomor.'</td>
											<td class="v_top">'.$NIRM.'</td>
											<td class="v_top">'.$NAMA.'</td>
											<td class="v_top">'.$GAMBAR.'</td>
										</tr>';
						}	
						
						$totalmhs++;
						$nomor++;
					
					$prodi_urutan++;
					
				}
				
				if($show_data_total=='1'){
					$html_show .=  "<script>console.log('".$prodiforshow." = ".$totalmhs." Orang');</script>";
					$html_show .=  "<script>console.log('-------------------------------------------------------');</script>";
				}
			}
			
		}
	
		$html_show .= '</table>';
	echo $html_show;
	?>
        
</body>
</html>