<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
/**
 * *************************************
 * 수주 및 매출, 수금현황
 * ------------------------------------
 * 2014-01-14 : 파일정리: JYJ
 * **************************************
 */

// include "../../sys/inc/dbcon.inc";
include "../../sys/inc/dbcon_hm.inc";
include "../../sys/inc/function_intranet.php";
include "../util/HanamcPageControl.php";
include "../../../SmartyConfig.php";
include "../../sys/inc/function_project_v2_work.php";



$sel_year="2017-02";
//$UNIT = 100000000; // 단위:억만원
$UNIT = 100000000; // 단위:억만원

// 수주 실적
		
		// $sql="select * from project_tbl where (ContractDate >= '$start_serch' and ContractDate <= '$end_serch') and ContractPayment > '0' ";
		$sql = "select * from project_tbl where ContractDate like '$sel_year%'";
		echo $sql."<Br>"; 
		$clist = mysql_query ( $sql, $db );
		while ( $result = mysql_fetch_array ( $clist ) ) {
			// $g_ContractPayment = $result[OrgContractPayment];
			// 부가세별도인것 처리
			$g_ContractPayment = $result [Payment];
			$g_ContractRatio = $result [ContractRatio];
			$g_ActualityRatio = $result [ActualityRatio];
			$tmp_ProjectCode  = $result[ProjectCode ];   

			if ($g_ActualityRatio <= 0)
				$g_ActualityRatio = 100;


			$sql="select sum(DivRate) as sumDivRate,ProjectCode from group_div_tbl where ProjectCode='$tmp_ProjectCode'";
			$re = mysql_query($sql);
			if(mysql_num_rows($re) > 0)
			{	
				while($re_row = mysql_fetch_array($re)) {

					$sumDivRate=$re_row[sumDivRate];
					$ProjectCode=$re_row[ProjectCode];
					if($sumDivRate < 100)
					{
						//echo $ProjectCode."=====".$sumDivRate."<br>";
						$g_ActualityRatio = $sumDivRate;
					}

				}
			}

			//echo $tmp_ProjectCode."--".$g_ActualityRatio."<br>";
			
			//해당년도에 계약금액변경이 있으면 신규에는 변경전 금액을 표시한다
			//$sql = "select * from Change_List_tbl where ProjectCode='$tmp_ProjectCode' and ChangeItem ='계약금액' and ChangeDate like '$sel_year%'";
			$sql = "select * from Change_List_tbl where ProjectCode='$tmp_ProjectCode' and ChangeItem ='계약금액' and ChangeDate like '$sel_year%'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql);
			if(mysql_num_rows($re) > 0)
			{
				while($re_row = mysql_fetch_array($re)) {

					$g_ContractPayment=$re_row[ChangeBefore];
					$g_ContractPayment=str_replace(",","",$g_ContractPayment);
					$g_ContractPayment=round($g_ContractPayment/1.1);
					//echo $g_ContractPayment."<br>";
				}
					$intotal = $intotal + $g_ContractPayment;

					echo $ProjectCode."===".$g_ContractPayment."==".$intotal."<br>";

			}else
			{
					if ($g_ActualityRatio > 0 and $g_ActualityRatio < 100) {
						$intotal = $intotal + ($g_ContractPayment * $g_ActualityRatio / 100);

						echo $ProjectCode."===".($g_ContractPayment * $g_ActualityRatio / 100)."==".$intotal."<br>";
					} else {
						$intotal = $intotal + $g_ContractPayment;
						echo $ProjectCode."===".$g_ContractPayment."==".$intotal."<br>";

					}

			}

			

		}
		// $intotal=$intotal/$UNIT;
		
		// 총괄수주실적은 더해야 한다
		$azSQL = "select * from change_list_tbl where ChangeItem like '%계약금액%' and ChangeDate like '$sel_year%'";
		echo $azSQL."<br>"; 
		$azRecord = mysql_query ( $azSQL, $db );
		while ( $result_record = mysql_fetch_array ( $azRecord ) ) {
			$g_ChangeBefore = str_replace ( ",", "", $result_record [ChangeBefore] );
			$g_ChangeAfter = str_replace ( ",", "", $result_record [ChangeAfter] );
			
			$g_ChangePrice = ($g_ChangeAfter - $g_ChangeBefore);
			$change_SUM = $change_SUM + $g_ChangePrice;
		}
		
		$change_SUM = $change_SUM / 1.1;
		$intotal = $intotal + $change_SUM;

		
		echo $change_SUM."==".$intotal."<br>";

		$intotal = $intotal / $UNIT;
		
		$intotal = sprintf ( "%.1f", round ( $intotal, 2 ) );


		



/*$azsql = "SELECT a.*,b.*,b.projectCode as tmp_ProjectCode  FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and A.DivCodeName IN('지방',
'지반',
'국도',
'수자',
'환경',
'항만',
'진단',
'도시',
'고속',
'구조',
'교통',
'AS',
'자기',
'기술',
'환에',
'감리',
'전기',
'IT',
'거더',
'연구',
'파일',
'영업',
'제안',
'교휴',
'고문',
'관리'
) and B.ContractDate like '2017%'"; 
*/

$azsql = "SELECT a.*,b.*,b.projectCode as tmp_ProjectCode  FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and A.ProjectCode IN('H17-도시-02',
'H17-AS-02',
'H17-도시-02',
'H17-AS-08',
'H17-수자-01',
'H17-고속-01',
'H17-진단-01',
'H17-진단-02'
) ";



$mYear="2017";
/*$azsql = "SELECT a.*,b.*,b.projectCode as tmp_ProjectCode  FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and A.ProjectCode IN('H17-수자-01'
) ";
*/
	echo $azsql."<br>";

	$azRecord = mysql_query($azsql);
	while($result_record = mysql_fetch_array($azRecord)) {

		//$g_ContractPayment = $result_record[OrgContractPayment];
		//부가세별도인것으로 처리 (Payment) 필드가 부가세별도금액임
		$g_ContractPayment = $result_record[Payment];
		$g_ContractRatio = $result_record[ContractRatio];   //계약 지분율
		$g_ActualityRatio = $result_record[ActualityRatio]; //실지분율
		$g_DivRate = $result_record[DivRate];               //부서별 실지분율
		$tmp_ProjectCode  = $result_record[tmp_ProjectCode ];   

		if($g_ActualityRatio <= 0 and $g_ActualityRatio > 100) $g_ActualityRatio = 100;
	
		if($g_DivRate <= 0 and $g_DivRate > 100) $g_DivRate = 100;
		
		if($g_ActualityRatio > 0 and $g_ActualityRatio < 100) { //당사지분율 적용
			$ActualityValue1 = ($g_ContractPayment * $g_ActualityRatio / 100);
		
		} else {
			$ActualityValue1 = $g_ContractPayment;
		}
		if($g_DivRate > 0 and $g_DivRate < 100) { //부서별 지분율 적용
			$ActualityValue = ($ActualityValue1 * $g_DivRate / 100);
		} else {
			$ActualityValue = $ActualityValue1;
		}


		
		
		//해당년도에 계약금액변경이 있으면 신규에는 변경전 금액을 표시한다
		$sql = "select * from Change_List_tbl where ProjectCode='$tmp_ProjectCode' and ChangeItem ='계약금액' and ChangeDate like '$mYear%'";
		//echo $sql."<br>"; 
		$re = mysql_query($sql);
		while($re_row = mysql_fetch_array($re)) {
			
			$ActualityValue=$re_row[ChangeBefore];
			$ActualityValue=str_replace(",","",$ActualityValue);
			$ActualityValue=round($ActualityValue/1.1);
			
		}

		

		$contractSUM += $ActualityValue;

		echo $tmp_ProjectCode."====".$ActualityValue."==".$contractSUM."<br>";

	}
//	return $contractSUM/1.1;
	//echo $result_record[ProjectCode]."/".$g_ContractPayment."/".$g_ContractRatio."/".$g_ActualityRatio."/".$g_DivRate."/".$contractSUM."<br>";
	echo $contractSUM;


?>
