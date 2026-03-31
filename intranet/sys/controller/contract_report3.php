<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<br>
<table cellspacing=1 cellspacing=1 border=0 width=750>
<tr>	
	<td height="40" class="t_center" style="font-size:20px;">
		<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		수 주 현 황[물가변동](<?=$sel_year;?>년)
	</td>
</tr>
<tr>
	<td class="t_left" style="font-size:12px;"><?=date("Y-m-d");?> 현재</td>
	<td class="t_right" style="font-size:12px;">(단위:백만원,VAT별도)</td>
</tr>
</table>
<div style="float:left;width:100%;">
	<table class="t_center cal_text1 tbl_border_01" width="100%" border="0" cellspacing="0" cellpadding="0">
		<colgroup>
			<col width="5%"/>
			<col width="24%"/>
			<col width="20%"/>
			<col width="12%"/>
			<col width="12%"/>
			<col width="12%"/>
			<col width="15%"/>
		</colgroup>
<tr>
	<td class="gray_bg tbl_bottom_border tbl_right_border" height="30">No</td>
	<td class="gray_bg tbl_bottom_border tbl_right_border" height="30">용 역 명</td>
	<td class="gray_bg tbl_bottom_border tbl_right_border" height="30">발 주 처</td>
	<!--
	<td width=80 class=tb_header2>변경항목</td>
	-->
	<td class="gray_bg tbl_bottom_border tbl_right_border" height="30">변경일</td>
	<td class="gray_bg tbl_bottom_border tbl_right_border" height="30">변경전</td>
	<td class="gray_bg tbl_bottom_border tbl_right_border" height="30">변경후</td>
	<td class="gray_bg tbl_bottom_border ">비 고(증감)</td>
</tr>

<?
$n_color=0;
$i = 1;

$tot1 = 0; //물가변동 총계

$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
$recordBlock3 = mysql_query($azSQL,$db);
while($record3 = mysql_fetch_array($recordBlock3)) {
	$group_code = $record3[Code];
	$group_name = $record3[Name];

	$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
	$recordBlock4 = mysql_query($azSQL,$db);
	while($record4 = mysql_fetch_array($recordBlock4)) {
		$g_code = $record4[Code];
		$g_name = $record4[Name];
		$code_sum1 = 0 ;

		if($g_code <= $LAST_GROUP_CODE_NO) {  //"정통" "경영" "영업" "관리" "교휴"
			$g_Prn = "<tr class=t_left><td colspan=8  class=tbl_bottom_border height=30 style=padding-left:0px;>[$g_name - $record4[Note]]</td></tr>";
			for($k=1; $k<=4; $k++) {
				//include "../../line_color_h.php";
				$sum1 = 0; //분기별 물가변동 소계

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

				$azSQL = "SELECT * FROM change_list_tbl WHERE ProjectCode like '%$g_name%' and ChangeItem like '%$DETAIL_OP2%' and ChangeDate >= '$StDate' and  ChangeDate < '$EdDate' order by ProjectCode asc, ChangeDate asc";

			    //$azSQL = "SELECT * FROM project_tbl WHERE ProjectCode like '%$g_name%' and ContractDate >= '$StDate' and  ContractDate < '$EdDate'";
				$recordBlock5 = mysql_query($azSQL,$db);
				if(mysql_num_rows($recordBlock5) > 0) {
					$m=1;
					while($record5 = mysql_fetch_array($recordBlock5)) {
						$g_ProjectCode = $record5[ProjectCode];

					    $azSQL = "SELECT * FROM project_tbl WHERE ProjectCode = '$g_ProjectCode'";
						$recordBlock6 = mysql_query($azSQL,$db);
						$g_ProjectNickname = mysql_result($recordBlock6,0,"ProjectNickname");
						$g_OrderNickname = mysql_result($recordBlock6,0,"OrderNickname");

						$g_ChangeDate = $record5[ChangeDate];
						$g_ChangeItem = $record5[ChangeItem];

						$g_ChangeBefore = str_replace(",","",$record5[ChangeBefore]);
						$g_ChangeAfter = str_replace(",","",$record5[ChangeAfter]);

						//VAT별도처리
						$g_ChangeBefore=$g_ChangeBefore/1.1;
						$g_ChangeAfter=$g_ChangeAfter/1.1;

						$g_ChangePrice = ($g_ChangeAfter-$g_ChangeBefore);
						$g_Note = $record5[Note];

						$sum1 = $sum1 + $g_ChangePrice;
						$code_sum1 = $code_sum1 + $g_ChangePrice;
						$tot1 = $tot1 + $g_ChangePrice;

						$g_Prn = $g_Prn."<tr>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center bgcolor=#FFFFFF height=30>$m</td>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border bgcolor=#FFFFFF height=30>&nbsp;$g_ProjectNickname</td>"; 
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border bgcolor=#FFFFFF height=30>&nbsp;$g_OrderNickname</td>";
							//$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border bgcolor=#FFFFFF height=30>&nbsp;$g_ChangeItem</td>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center bgcolor=#FFFFFF height=30>&nbsp;$g_ChangeDate</td>";
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right bgcolor=#FFFFFF height=30>".number_format($g_ChangeBefore / 1000000)."&nbsp;</td>"; 
							$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right bgcolor=#FFFFFF height=30>".number_format($g_ChangeAfter / 1000000)."&nbsp;</td>";
							$g_Prn = $g_Prn."<td class=tbl_bottom_border_t_right bgcolor=#FFFFFF height=30>".number_format($g_ChangePrice / 1000000)."&nbsp;</td>";
						$g_Prn = $g_Prn."</tr>";
						$m++;
						$i++;
						$n_color++;
					} //while($record5 = mysql_fetch_array($recordBlock5))
					$sum_1 = number_format($sum1 / 1000000);
					$tit = $k."분기 계";
					$g_Prn = $g_Prn."<tr>";
						$g_Prn = $g_Prn."<td colspan=3 class=tbl_right_border_bottom_border_t_center height=30>$tit</td>";
						$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
						$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
						$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
						//$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
						$g_Prn = $g_Prn."<td class=tbl_bottom_border_t_right height=30>$sum_1&nbsp;</td>"; //분기별소계 출력
					$g_Prn = $g_Prn."</tr>";
				} // 
			} //for($k=1; $k<=4; $k++)
			$code_sum_1 = number_format($code_sum1 / 1000000);
			$g_Prn = $g_Prn."<tr>";
				$g_Prn = $g_Prn."<td colspan=3 class=tbl_right_border_bottom_border_t_center height=30>소&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;계</td>";
				$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_center height=30>&nbsp;</td>";
				$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
				$g_Prn = $g_Prn."<td class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
				//$g_Prn = $g_Prn."<td class=$color>&nbsp;</td>";
				$g_Prn = $g_Prn."<td class=tbl_bottom_border_t_right height=30>$code_sum_1&nbsp;</td>"; //코드별소계 출력
			$g_Prn = $g_Prn."</tr>";
			if($code_sum1 <> 0) { echo $g_Prn; }

		} //if($g_code < 30)
	} //while($record4 = mysql_fetch_array($recordBlock4))
} //while($record3 = mysql_fetch_array($recordBlock3))

$tot_1 = number_format($tot1 / 1000000);
echo "<tr>";
	echo "<td colspan=3 class=tbl_right_border_bottom_border_t_center height=30><b>총&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;계</b></td>";
	echo "<td  class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
	echo "<td  class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
	echo "<td  class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
	//echo "<td  class=tbl_right_border_bottom_border_t_right height=30>&nbsp;</td>";
	echo "<td  class=tbl_bottom_border_t_right height=30><b>$tot_1&nbsp;</b></td>"; //코드별소계 출력
echo "</tr>";
?>

</table>
</div>
