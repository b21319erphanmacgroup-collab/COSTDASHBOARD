<?php
extract($_REQUEST);
$file_name = trim(ICONV("UTF-8","EUC-KR",$file_name));
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$file_name.xls\"");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
//header("Pragma: no-cache");
ini_set('memory_limit','-1');
//print_r($tbl_body);
//print_r(str_replace("\'", "'", str_replace('\"','"',trim($tbl_body))));

//$tbl_body = json_decode(rtrim(str_replace("\'", "'", str_replace('\"','"',trim($tbl_body))), "\0"));
$tbl_body = stripslashes(html_entity_decode($tbl_body));
$tbl_body = json_decode($tbl_body);
//print_r(str_replace('\\"','"',trim($tbl_info)));
$tbl_info = stripslashes(html_entity_decode($tbl_info));
$tbl_info = json_decode($tbl_info);
//print_r($tbl_info);
//print_r($tbl_body);
//$tbl_body =  array_map(__FUNCTION__, $tbl_body);
for($j=0; $j<count($tbl_info); $j++){
	$tbl_info[$j] = get_object_vars($tbl_info[$j]);
}

?>
<!DOCTYPE html>
	<head>
		<meta charset="utf-8" />
		<style type="text/css">
			td { mso-number-format: '@'; }	/*글자표시*/
			/*td.type_text { mso-number-format: '@'; }*/	/*글자표시*/
			td.number_Decimals_1 { mso-number-format:"0\.0"; }	/*소수자리1*/
			td.number_Decimals_2 { mso-number-format:"0\.00"; }	/*소수자리2*/
			td.number_Decimals_3 { mso-number-format:"0\.000"; }	/*소수자리3*/
			td.number_Decimals_4 { mso-number-format:"0\.0000"; }	/*소수자리4*/
			td.int { mso-number-format:"\#\,\#\#0"; }	/*원화표시*/
		</style>
	</head>
	<body>
		<?=str_replace('\\"',"",trim($search_info))?>
		<table border='1px'>
			<thead id="tbl_head"><?=str_replace('\\"',"",trim($tbl_head))?></thead>
			<tbody id="tbl_body">
<?php
	$tr_text = "";
	for($i=0; $i<count($tbl_body); $i++){
		$tr_text .= "<tr>";

		$tbl_body[$i] = get_object_vars($tbl_body[$i]);
		for($j=0; $j<count($tbl_info); $j++){
			//print_r($tbl_info[$i]);
			$tr_text .= "<td style='".$tbl_info[$j]['style']."' class='".$tbl_info[$j]['render']."'>";
			//echo $tbl_info[$j]['field'];
			$tr_text .= $tbl_body[$i][$tbl_info[$j]['field']];
			$tr_text .= "</td>";
		}

		$tr_text .= "</tr>";
	}
	echo $tr_text;
?>
			</tbody>
			<tfoot id="tbl_foot"><?=str_replace('\\"',"",trim($tbl_foot))?></tfoot>
		</table>
	</body>
</html>