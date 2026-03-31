<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<br>
<table cellspacing=1 cellspacing=1 border=0 width=800>
<tr>
	<td height="40" class="t_center" style="font-size:20px;"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	수 주 현 황 [신규](<?=$sel_year;?>년)</td></tr>
<tr>
	<td class="t_left" style="font-size:12px;"> 현재</td>
	<td class="t_right" style="font-size:12px;">(단위:백만원,VAT별도)</td>
</tr>
</table>
<div style="float:left;width:100%;">
	<table class="t_center cal_text1 tbl_border_01" width="100%" border="0" cellspacing="0" cellpadding="0">
		<colgroup>
			<col width="5%"/>
			<col width="25%"/>
			<col width="20%"/>
			<col width="10%"/>
			<col width="10%"/>
			<col width="10%"/>
			<col width="10%"/>
			<col width="10%"/>
		</colgroup>
		<tr>
			<td rowspan="2" class="gray_bg tbl_bottom_border tbl_right_border" height="60">No</td>
			<td rowspan="2" class="gray_bg tbl_bottom_border tbl_right_border" height="30">용 역 명</td>
			<td rowspan="2" class="gray_bg tbl_bottom_border tbl_right_border" height="30">발 주 처</td>
			<td colspan="2" colspan="2" class="gray_bg tbl_bottom_border tbl_right_border" height="30">수주금액</td>
			<td colspan="2" colspan="2" class="gray_bg tbl_bottom_border tbl_right_border" height="30">계약기간</td>
			<td rowspan="2" class="gray_bg tbl_bottom_border ">비 고</td>
		</tr>
		<tr>
			<td class="tbl_bottom_border tbl_right_border" height="30">계약금액</td>
			<td class="tbl_bottom_border tbl_right_border" height="30">지분금액</td>
			<td class="tbl_bottom_border tbl_right_border" height="30">착수일</td>
			<td class="tbl_bottom_border tbl_right_border" height="30">준공예정일</td>
		</tr>

<?
$n_color=0;
$i = 1;

$tot1 = 0; //계약금액 총계
$tot2 = 0; //순수주금액 총계

// '계약금액(vat포함) X 실지분율' 로 계산 (계약지분율로 계산않음)
$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
$recordBlock3 = mysql_query($azSQL,$db);
while($record3 = mysql_fetch_array($recordBlock3)) {
	$group_code = $record3[Code];
	$group_name = $record3[Name];

	$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
	//echo $azSQL."<br>";
	$recordBlock4 = mysql_query($azSQL,$db);
	while($record4 = mysql_fetch_array($recordBlock4)) {
		$g_code = $record4[Code];
		$g_name = $record4[Name];
		$code_sum1 = 0 ;
		$code_sum2 = 0 ;

		if($g_code <= $LAST_GROUP_CODE_NO) {  //"정통" "경영" "영업" "관리" "교휴"
			$g_Prn = "<tr class=t_left><td colspan=8  class=tbl_bottom_border height=30 style=padding-left:0px;>[$g_name - $record4[Note]]</td></tr>";
			for($k=1; $k<=4; $k++) {
				//include "../../line_color_h.php";
				$sum1 = 0; //분기별 계약금액 소계
				$sum2 = 0; //분기별 순수주금액 소계

				$StDate = $sel_year."-01-01";
				$EdDate = $sel_year."-04-01";
				if($k == 2) {
					$StDate = $sel_year."-04-01";
					$EdDate = $sel_year."-07-01";
				} elseif($k == 3) {
					$StDate = $sel_year."-07-01";
					$EdDate = $sel_year."-10-01";
				} elseif($k == 4) {
					$StDate = $sel_year."-10-01";
					$EdDate = ($sel_year+1)."-01-01";
				}

			    //$azSQL = "SELECT * FROM project_tbl WHERE ProjectCode like '%$g_name%' and ContractDate >= '$StDate' and  ContractDate < '$EdDate'";
				$azSQL = "SELECT * FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and DivCodeName = '$g_name' and ContractDate >= '$StDate' and ContractDate < '$EdDate'";

				//echo $azSQL."<br>";
				$recordBlock5 = mysql_query($azSQL,$db);
				if(mysql_num_rows($recordBlock5) > 0) {
					$m=1;
					while($record5 = mysql_fetch_array($recordBlock5)) {
						//$g_ContractPayment = $record5[OrgContractPayment]; //최초 계약 금액 적용
						//부가세별도인것으로 처리 (Payment) 필드가 부가세별도금액임
						$g_ContractPayment = $record5[Payment]; //최초 계약 금액 적용
						$g_ContractRatio = $record5[ContractRatio];
						$g_ActualityRatio = $record5[ActualityRatio];

						$g_ProjectNickname = $record5[ProjectNickname];
						$g_OrderNickname = $record5[OrderNickname];
						$g_ContractStart = $record5[ContractStart];
						$g_ContractEnd = $record5[ContractEnd];
						$g_DivRate = $record5[DivRate];               //부서별 실지분율

						if($g_ActualityRatio <= 0 and $g_ActualityRatio > 100) $g_ActualityRatio = 100;

						if($g_ContractRatio > 0 and $g_ContractRatio < 100) {
							//$ContractValue = ($g_ContractPayment * $g_ContractRatio / 100);
							$ContractValue = $g_ContractPayment;
						} else {
							$ContractValue = $g_ContractPayment;
						}
						if($g_ActualityRatio > 0 and $g_ActualityRatio < 100) { //당사지분율 적용
							$ActualityValue1 = ($g_ContractPayment * $g_ActualityRatio / 100);
							//$ActualityValue1 = ($g_ContractPayment * $g_ActualityRatio / 100) * ($g_DivRate / 100);
						} else {
							$ActualityValue1 = $g_ContractPayment;
						}
						if($g_DivRate > 0 and $g_DivRate < 100) { //부서별 지분율 적용
							//$ActualityValue = ($g_ContractPayment * $g_ActualityRatio / 100);
							$ActualityValue = ($ActualityValue1 * $g_DivRate / 100);
						} else {
							$ActualityValue = $ActualityValue1;
						}

						//VAT별도처리
						//$ContractValue=$ContractValue/1.1;
						//$ActualityValue=$ActualityValue/1.1;

						$sum1 = $sum1 + $ContractValue ;
						$sum2 = $sum2 + $ActualityValue ;
						$code_sum1 = $code_sum1 + $ContractValue ;
						$code_sum2 = $code_sum2 + $ActualityValue ;
						$tot1 = $tot1 + $ContractValue ;
						$tot2 = $tot2 + $ActualityValue ;

						$g_ContractValue = number_format($ContractValue / 1000000);
						$g_ActualityValue = number_format($ActualityValue / 1000000);

						$g_Prn = $g_Prn."<tr>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center bgcolor=#FFFFFF height=30>$m</td>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border bgcolor=#FFFFFF height=30>&nbsp;$g_ProjectNickname</td>"; 
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border bgcolor=#FFFFFF height=30>&nbsp;$g_OrderNickname</td>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right bgcolor=#FFFFFF height=30>$g_ContractValue</td>"; 
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right bgcolor=#FFFFFF height=30>$g_ActualityValue</td>"; 
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center bgcolor=#FFFFFF height=30>&nbsp;$g_ContractStart</td>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center bgcolor=#FFFFFF height=30>&nbsp;$g_ContractEnd</td>";
							$g_Prn = $g_Prn."<td class=tbl_bottom_border bgcolor=#FFFFFF height=30>&nbsp;</td>";
						$g_Prn = $g_Prn."</tr>";
						$m++;
						$i++;
						$n_color++;
					} //while($record5 = mysql_fetch_array($recordBlock5))
					$sum_1 = number_format($sum1 / 1000000);
					$sum_2 = number_format($sum2 / 1000000);
					$tit = $k."분기 계";
					$g_Prn = $g_Prn."<tr>";
						$g_Prn = $g_Prn."<td colspan=3 class=tbl_right_border_bottom_border_t_center height=30>$tit</td>";
						$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>$sum_1</td>"; //분기별소계 출력
						$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>$sum_2</td>"; 
						$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
						$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
						$g_Prn = $g_Prn."<td class=tbl_bottom_border height=30>&nbsp;</td>";
					$g_Prn = $g_Prn."</tr>";
				} // 
			} //for($k=1; $k<=4; $k++)
			$code_sum_1 = number_format($code_sum1 / 1000000);
			$code_sum_2 = number_format($code_sum2 / 1000000);
			$g_Prn = $g_Prn."<tr>";
				$g_Prn = $g_Prn."<td colspan=3 class=tbl_right_border_bottom_border_t_center height=30>소&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;계</td>";
				$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>$code_sum_1</td>"; //코드별소계 출력
				$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>$code_sum_2</td>"; 
				$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
				$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
				$g_Prn = $g_Prn."<td class=tbl_bottom_border height=30>&nbsp;</td>";
			$g_Prn = $g_Prn."</tr>";
			if($code_sum1 > 0) { echo $g_Prn; }

		} //if($g_code < 30)
	} //while($record4 = mysql_fetch_array($recordBlock4))
} //while($record3 = mysql_fetch_array($recordBlock3))

$tot_1 = number_format($tot1 / 1000000);
$tot_2 = number_format($tot2 / 1000000);
echo "<tr>";
	echo "<td colspan=3 class=tbl_right_border_bottom_border_t_center height=30><b>총&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;계</b></td>";
	echo "<td class=tbl_right_border_bottom_border_t_right height=30><b>$tot_1</b></td>"; //코드별소계 출력
	echo "<td class=tbl_right_border_bottom_border_t_right height=30><b>$tot_2</b></td>"; 
	echo "<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
	echo "<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
	echo "<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
echo "</tr>";
?>

</table>
</div>