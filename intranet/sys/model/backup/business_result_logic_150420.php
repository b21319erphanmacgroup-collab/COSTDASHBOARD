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
include "../../sys/inc/function_project_v2.php";
extract ( $_GET );
extract ( $_POST );
require_once ($SmartyClassPath);
class BusinessResult extends Smarty {
	function BusinessResult() {
		global $SmartyClass_TemplateDir;
		global $SmartyClass_CompileDir;
		global $SmartyClass_ConfigDir;
		global $SmartyClass_CacheDir;
		global $ProjectCode, $bridgeno, $n_num, $Item_no, $id, $mode;
		
		$this->Smarty ();
		$this->template_dir = $SmartyClass_TemplateDir;
		$this->compile_dir = $SmartyClass_CompileDir;
		$this->config_dir = $SmartyClass_ConfigDir;
	}
	
	// ============================================================================
	// 수주 및 매출, 수금현황 (총괄) 표시
	// ============================================================================
	function BusinessResultList() {
		global $db, $memberID;
		global $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $PRINT;
		
		$date1 = date ( "Y" ); // / 오늘
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
		
		$today = $date1 . "-" . $date2 . "-" . $date3;
		
		if ($sel_year == "" || $sel_year == null) {
			$sel_year = $date1;
		}
		if ($sel_month == "" || $sel_month == null) {
			$sel_month = $date2;
		} else {
			if ($sel_month <= 9) {
				$sel_month = "0" . $sel_month;
			}
		}
		
		$uyear = date ( "Y" ) + 1; // ///최대 보이는 년도
		$UNIT = 100000000; // 단위:억만원
		
		if ($tab_index == "") {
			$tab_index = "2";
		}
		if ($sub_index == "") {
			$sub_index = "1";
		}
		$ana_option = $sub_index;
		$tab_Titel2 = array (
				'총괄',
				'신규',
				'계약금액변경',
				'물가변동' 
		);
		$tab_value2 = array (
				'1',
				'2',
				'3',
				'4' 
		);
		
		if ($_SESSION ['auth_ceo']) // 임원
			$this->assign ( 'auth_ceo', true );
		else
			$this->assign ( 'auth_ceo', false );
		
		if ($_SESSION ['auth_depart']) // 부서장
			$this->assign ( 'auth_depart', true );
		else
			$this->assign ( 'auth_depart', false );
		
		$uyear = date ( "Y" ) + 1;
		$last_day = date ( "t", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
		$last_day = $last_day + 1;
		
		if ($sel_year == "")
			$sel_year = date ( "Y" );
		if ($sel_month == "")
			$sel_month = date ( "m" );
		if ($sel_day == "")
			$sel_day = date ( "d" );
		
		$this->assign ( 'today', $today );
		$this->assign ( 'uyear', $uyear );
		$this->assign ( 'tab_Titel2', $tab_Titel2 );
		$this->assign ( 'tab_value2', $tab_value2 );
		$this->assign ( 'tab_index', $tab_index );
		$this->assign ( 'sub_index', $sub_index );
		$this->assign ( 'last_day', $last_day );
		$this->assign ( 'sel_year', $sel_year );
		$this->assign ( 'sel_month', $sel_month );
		$this->assign ( 'sel_day', $sel_day );
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----1. 수주/매출/수금 현황--------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		$Contractpayment = 0;
		$CollectionPayment = 0;
		
		$Contractpayment2 = 0;
		$CollectionPayment2 = 0;
		
		$start_serch = $sel_year . "-01-01";
		
		// 월을 선택하는것 (안보이게처리) 무조건 조회하는 달로 조회
		// 해가 다르면 모든년 검색
		if ($date1 != $sel_year) // 같은년도 아니면 모두
{
			$end_serch = $sel_year . "-12-31";
		} else {
			$end_serch = $sel_year . "-" . $sel_month . "-31";
		}
		$start_serch2 = $sel_year . "-" . $sel_month . "-01";
		
		$end_serch2 = $sel_year . "-12-31";
		// 수주/매출 목표
		$Contractpayment = 0;
		$CollectionPayment = 0;
		$sql = "select sum(Contractpayment) Contractpayment,sum(CollectionPayment) CollectionPayment from project_targetmoney_tbl where Month like '$sel_year%'";
		
		$clist = mysql_query ( $sql, $db );
		$left_num = mysql_num_rows ( $clist );
		if ($left_num > 0) {
			while ( $result = mysql_fetch_array ( $clist ) ) {
				$Contractpayment = $result [Contractpayment] / $UNIT;
				$CollectionPayment = $result [CollectionPayment] / $UNIT;
			}
		}
		
		// 수주/매출 추진중목표
		$Contractpayment2 = 0;
		$CollectionPayment2 = 0;
		$sql = "select sum(Contractpayment) Contractpayment2,sum(CollectionPayment) CollectionPayment2 from project_targetmoney_tbl where (Month > '$start_serch2' and Month < '$end_serch2')";
		
		$clist = mysql_query ( $sql, $db );
		$left_num = mysql_num_rows ( $clist );
		if ($left_num > 0) {
			while ( $result = mysql_fetch_array ( $clist ) ) {
				$Contractpayment2 = $result [Contractpayment2] / $UNIT;
				$CollectionPayment2 = $result [CollectionPayment2] / $UNIT;
			}
		}
		
		// 수주 실적
		
		// $sql="select * from project_tbl where (ContractDate >= '$start_serch' and ContractDate <= '$end_serch') and ContractPayment > '0' ";
		$sql = "select * from project_tbl where ContractDate like '$sel_year%'";
		
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


			
			//해당년도에 계약금액변경이 있으면 신규에는 변경전 금액을 표시한다
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

			}else
			{
					if ($g_ActualityRatio > 0 and $g_ActualityRatio < 100) {
						$intotal = $intotal + ($g_ContractPayment * $g_ActualityRatio / 100);
					} else {
						$intotal = $intotal + $g_ContractPayment;
					}

			}

		}
		// $intotal=$intotal/$UNIT;
		
		// 총괄수주실적은 더해야 한다
		$azSQL = "select * from change_list_tbl where ChangeItem like '%계약금액%' and ChangeDate like '$sel_year%'";
		
		$azRecord = mysql_query ( $azSQL, $db );
		while ( $result_record = mysql_fetch_array ( $azRecord ) ) {
			$g_ChangeBefore = str_replace ( ",", "", $result_record [ChangeBefore] );
			$g_ChangeAfter = str_replace ( ",", "", $result_record [ChangeAfter] );
			
			$g_ChangePrice = ($g_ChangeAfter - $g_ChangeBefore);
			$change_SUM = $change_SUM + $g_ChangePrice;
		}
		
		$change_SUM = $change_SUM / 1.1;
		$intotal = $intotal + $change_SUM;
		$intotal = $intotal / $UNIT;
		
		$intotal = sprintf ( "%.1f", round ( $intotal, 2 ) );
		
		if ($intotal != 0 && $Contractpayment != 0) {
			$intotal_per = sprintf ( "%.1f", ($intotal / $Contractpayment) * 100 );
		} else {
			$intotal_per = "0";
		}
		
		// 매출 실적
		//$sql1 = "select sum(CollectionPayment) total from collectionpayment_tbl where (DemandDate >= '$start_serch' and DemandDate <= '$end_serch') and CollectionPayment > '0' ";
		$sql1 = "select sum(CollectionPayment) total from collectionpayment_tbl where (DemandDate >= '$start_serch' and DemandDate <= '$end_serch') ";
		
		$clist1 = mysql_query ( $sql1, $db );
		while ( $result1 = mysql_fetch_array ( $clist1 ) ) {
			$outtotal = $result1 [total] / $UNIT;
		}
		
		// 부가세별도
		$outtotal = $outtotal / 1.1;
		$outtotal = sprintf ( "%.1f", round ( $outtotal, 2 ) );
		
		$outtotal = sprintf ( "%.1f", round ( $outtotal, 2 ) );
		
		if ($outtotal != 0 && $CollectionPayment != 0) {
			$outtotal_per = sprintf ( "%.1f", ($outtotal / $CollectionPayment) * 100 );
		} else {
			$outtotal_per = "0";
		}
		
		// 수금 실적
		$sql = "select sum(CollectionPayment) total from collectionpayment_tbl where CollectionDate like '$sel_year%'";
		
		$clist = mysql_query ( $sql, $db );
		while ( $result = mysql_fetch_array ( $clist ) ) {
			$CollectionPaymentsum = $result [total];
		}
		$CollectionPaymentsum = $CollectionPaymentsum / $UNIT;
		$CollectionPaymentsum = $CollectionPaymentsum / 1.1;
		$CollectionPaymentsum = sprintf ( "%.1f", round ( $CollectionPaymentsum, 2 ) );
		
		if ($CollectionPaymentsum != 0 && $CollectionPayment != 0) {
			$CollectionPaymentsum_per = sprintf ( "%.1f", ($CollectionPaymentsum / $CollectionPayment) * 100 );
		} else {
			$CollectionPaymentsum_per = "0";
		}
		
		$this->assign ( 'Contractpayment', $Contractpayment );
		$this->assign ( 'intotal', $intotal );
		$this->assign ( 'intotal_per', $intotal_per );
		$this->assign ( 'CollectionPayment', $CollectionPayment );
		$this->assign ( 'outtotal', $outtotal );
		$this->assign ( 'outtotal_per', $outtotal_per );
		$this->assign ( 'CollectionPaymentsum', $CollectionPaymentsum );
		$this->assign ( 'CollectionPaymentsum_per', $CollectionPaymentsum_per );
		$this->assign ( 'sel_month', $sel_month );
		$this->assign ( 'sel_day', $sel_day );
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----2. 월별 수주 / 매출 / 수금 현황-----------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		// -----1. 월별 수주---------------------------------------------------------------------------------------------------------------------------------//
		$query_data1 = array ();
		$thismonth = date ( "m" );
		if ($sel_year == "" || $sel_year == null) {
			$sel_year = $date1;
		}
		if ($sel_month == "" || $sel_month == null) {
			$sel_month = $date2;
		} else {
			// if($sel_month <= 9) { $sel_month = "0".$sel_month; }
		}
		
		$maxin = "0";
		$i = "1";
		for($month = 1; $month <= 12; $month ++) {
			$contractSUM = 0;
			$change_SUM = 0;
			if ($month <= 9) {
				$month = "0" . $month;
			}
			$serchdate = $sel_year . "-" . $month;
			$thisdate = $date1 . "-" . $date2;
			
			$azsql = "select * from project_tbl where ContractDate like '$serchdate%'";
			
			$azRecord = mysql_query ( $azsql, $db );
			while ( $result_record = mysql_fetch_array ( $azRecord ) ) {
				$g_ContractPayment = $result_record [OrgContractPayment];
				$g_ContractRatio = $result_record [ContractRatio];
				$g_ActualityRatio = $result_record [ActualityRatio];
				$tmp_ProjectCode = $result_record [ProjectCode];
				
				
				
				//해당년도에 계약금액변경이 있으면 신규에는 변경전 금액을 표시한다
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
						$contractSUM = $contractSUM + $g_ContractPayment;

				}else
				{
						if ($g_ActualityRatio > 0 and $g_ActualityRatio < 100) {
							// $contractSUM = $contractSUM + ($g_ContractPayment * $g_ContractRatio / 100);
							$contractSUM = $contractSUM + ($g_ContractPayment * $g_ActualityRatio / 100);
						} else {
							$contractSUM = $contractSUM + $g_ContractPayment;
						}

				}

				
				
				
				
			}
			
			$azSQL = "select * from change_list_tbl where ChangeItem like '%계약금액%' and ChangeDate like '$serchdate%'";
			//echo $azSQL."<br>";
			
			$azRecord = mysql_query ( $azSQL, $db );
			while ( $result_record = mysql_fetch_array ( $azRecord ) ) {
				$g_ChangeBefore = str_replace ( ",", "", $result_record [ChangeBefore] );
				$g_ChangeAfter = str_replace ( ",", "", $result_record [ChangeAfter] );
				
				$g_ChangePrice = ($g_ChangeAfter - $g_ChangeBefore);
				$change_SUM = $change_SUM + $g_ChangePrice;
			}
			
			$contract_change_sum = $contractSUM + $change_SUM;
			
			if ($contract_change_sum == "") {
				$contract_change_sum2 [$i] = "0";
			} else {
				$contract_change_sum = $contract_change_sum / 1.1;
				$contract_change_sum = $contract_change_sum / $UNIT;
				$contract_change_sum2 [$i] = sprintf ( "%.1f", round ( $contract_change_sum, 2 ) );
			}
			
			if ($maxin < $contract_change_sum2 [$i]) {
				$maxin = $contract_change_sum2 [$i];
			}
			
			$CCSUMtotal = $CCSUMtotal + $contract_change_sum2 [$i];
			$val1 = $val1 . ceil ( $contract_change_sum2 [$i] ) . ",";
			
			$CCSUMtotal_sum = sprintf ( "%.1f", round ( $CCSUMtotal, 2 ) );
			
			// -----2. 월별 매출---------------------------------------------------------------------------------------------------------------------------------//
			
			if ($thisdate >= $serchdate) {
				$sql = "select sum(CollectionPayment) total from collectionpayment_tbl where DemandDate like '$serchdate%'";
			} else {
				$sql = "select sum(CollectionPayment) total from project_targetmoney_tbl where Month like'$serchdate%'";
			}
			
			$clist = mysql_query ( $sql, $db );
			while ( $result = mysql_fetch_array ( $clist ) ) {
				// $mintotal=$result[total];
				// 부가세별도
				if ($thisdate >= $serchdate) {
					$mintotal = $result [total] / 1.1;
				} else {
					$mintotal = $result [total];
				}
			}
			
			if ($mintotal == "") {
				$mintotal_sum [$i] = "0";
			} else {
				$mintotal = $mintotal / $UNIT;
				$mintotal_sum [$i] = sprintf ( "%.1f", round ( $mintotal, 2 ) );
			}
			
			$totalin = $totalin + $mintotal_sum [$i];
			if ($maxin < $mintotal_sum [$i]) {
				$maxin = $mintotal_sum [$i];
			}
			
			$val2 = $val2 . ceil ( $mintotal_sum [$i] ) . ",";
			
			$totalin_sum = sprintf ( "%.1f", round ( $totalin, 2 ) );
			
			// -----3. 월별 수금---------------------------------------------------------------------------------------------------------------------------------//
			
			$sql = "select sum(CollectionPayment) total from collectionpayment_tbl where CollectionDate like '$serchdate%'";
			
			$clist = mysql_query ( $sql, $db );
			while ( $result = mysql_fetch_array ( $clist ) ) {
				// 부가세별도
				$mouttotal = $result [total] / 1.1;
			}
			if ($mouttotal == "") {
				$mouttotal_sum [$i] = "0";
			} else {
				$mouttotal = $mouttotal / $UNIT;
				$mouttotal_sum [$i] = sprintf ( "%.1f", round ( $mouttotal, 2 ) );
			}
			
			if ($maxin < $mouttotal_sum [$i]) {
				$maxin = $mouttotal_sum [$i];
			}
			
			$totalout = $totalout + $mouttotal_sum [$i];
			$val3 = $val3 . ceil ( $mouttotal_sum [$i] ) . ",";
			
			$totalout_sum = sprintf ( "%.1f", round ( $totalout, 2 ) );

				$something1 = array('category' => urlencode($i.월),'column-1' => urlencode(round($contract_change_sum2[$i])),'column-2' => urlencode(round($mintotal_sum[$i])),'column-3' => urlencode(round($mouttotal_sum[$i])));
			array_push ( $query_data1, $something1 );
			$i ++;
		}
		
		$this->assign ( 'jsondata1', $jsondata1 );
		$this->assign ( 'thismonth', $thismonth );
		$this->assign ( 'contract_change_sum2', $contract_change_sum2 );
		$this->assign ( 'CCSUMtotal_sum', $CCSUMtotal_sum );
		$this->assign ( 'mintotal_sum', $mintotal_sum );
		$this->assign ( 'totalin_sum', $totalin_sum );
		$this->assign ( 'mouttotal_sum', $mouttotal_sum );
		$this->assign ( 'totalout_sum', $totalout_sum );
		
		$jsondata1 = urldecode ( json_encode ( $query_data1 ) );
		
		$this->assign ( 'jsondata1', $jsondata1 );
		
		// =월별/수주/매출/수금 그래프 자바스크립트 include==================================================//
		include "../../../Smarty/templates/intranet/business_graph.tpl";
		// =====================================================================================//
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----3. 수주집계표 (총괄)------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		$UNIT = "1000000"; // 단위:백만원
		$MAX_CNT = "12";
		$MAX_SUM = "13";
		$SALES_FIELD = "Order1";
		// $LAST_GROUP_CODE_NO = 27;
		$LAST_GROUP_CODE_NO = "212";
		
		if ($ana_option == 1) {
			$detail_title = "수주집계표 (총괄)";
		} else if ($ana_option == 2) {
			$detail_title = "수주집계표 (신규)";
		} else if ($ana_option == 3) {
			$detail_title = "수주집계표 (계약금액변경)";
		} else if ($ana_option == 4) {
			$detail_title = "수주집계표 (물가변동)";
		}
		
		// -----1. 설계관련부서(교통,국도,지방,구조,지반,교통,수자,항만,도시,환경,환에,진단)-------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft1_tot [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			$g = "0";
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by orderno";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				$g_code = $record2 [Code];
				$g_name [$g] = $record2 [Name];
				
				if ($g_code >= 100 && $g_code <= 111) // 설계(고속,국도,)
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$design_name [$g] = $g_name [$g];
						
						switch ($ana_option) {
							case 1 : // 총괄
								$sum [$month] = Contract_SUM_M ( $g_name [$g], $sel_year, $month ) + Change_SUM_M ( $g_name [$g], $sel_year, $month );
								break;
							case 2 : // 신규
								$sum [$month] = Contract_SUM_M ( $g_name [$g], $sel_year, $month );
								break;
							case 3 : // 계약금액변경
								$sum [$month] = Change_SUM_M ( $g_name [$g], $sel_year, $month );
								break;
							case 4 : // 물가변동
								$sum [$month] = ES_SUM_M ( $g_name [$g], $sel_year, $month );
								break;
						}
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$g]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD ); // 목표
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft1_tot [$month] = $soft1_tot [$month] + $sum [$month];
						$contract_sum [$g] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					$achievement_rate [$g] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$g ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft1_tot [$MAX_SUM + 1] > 0) {
			$soft1_tot [$MAX_SUM + 2] = round ( ($soft1_tot [$MAX_SUM] / $soft1_tot [$MAX_SUM + 1]) * 100 );
		}
		$sum_quartersum = 0;
		
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft1_tot_sum [$month] = number_format ( $soft1_tot [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft1_tot [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum = number_format ( $soft1_tot [$MAX_SUM + 2] );
		
		$design_name_num = count ( $design_name ) + 1;
		
		$this->assign ( 'detail_title', $detail_title );
		$this->assign ( 'design_name', $design_name );
		$this->assign ( 'design_name_num', $design_name_num );
		$this->assign ( 'contract_sum', $contract_sum );
		$this->assign ( 'achievement_rate', $achievement_rate );
		$this->assign ( 'soft1_tot_sum', $soft1_tot_sum );
		$this->assign ( 'achievement_rate_sum', $achievement_rate_sum );
		
		// -----2. 감리관련부서(감리, 전기)------------------------------------------------------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft2_tot [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			if ($inspection_name == "") {
				$f = "0";
			}
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				
				$g_code = $record2 [Code];
				$g_name [$f] = $record2 [Name];
				
				if ($g_code >= 120 && $g_code <= 130) // 감리 + 시공의 전기포함
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$inspection_name [$f] = $g_name [$f];
						
						switch ($ana_option) {
							case 1 : // 총괄
								$sum [$month] = Contract_SUM_M ( $g_name [$f], $sel_year, $month ) + Change_SUM_M ( $g_name [$f], $sel_year, $month );
								break;
							case 2 : // 신규
								$sum [$month] = Contract_SUM_M ( $g_name [$f], $sel_year, $month );
								break;
							case 3 : // 계약금액변경
								$sum [$month] = Change_SUM_M ( $g_name [$f], $sel_year, $month );
								break;
							case 4 : // 물가변동
								$sum [$month] = ES_SUM_M ( $g_name [$f], $sel_year, $month );
								break;
						}
						
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$f]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD ); // 목표
						
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft2_tot [$month] = $soft2_tot [$month] + $sum [$month];
						$contract_sum2 [$f] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					
					$achievement_rate2 [$f] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$f ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft2_tot [$MAX_SUM + 1] > 0) {
			$soft2_tot [$MAX_SUM + 2] = ($soft2_tot [$MAX_SUM] / $soft2_tot [$MAX_SUM + 1]) * 100;
		}
		
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft2_tot_sum [$month] = number_format ( $soft2_tot [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft2_tot [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum2 = number_format ( $soft2_tot [$MAX_SUM + 2] );
		
		$inspection_name_num = count ( $inspection_name ) + 1;
		
		$this->assign ( 'inspection_name', $inspection_name );
		$this->assign ( 'inspection_name_num', $inspection_name_num );
		$this->assign ( 'contract_sum2', $contract_sum2 );
		$this->assign ( 'achievement_rate2', $achievement_rate2 );
		$this->assign ( 'soft2_tot_sum', $soft2_tot_sum );
		$this->assign ( 'achievement_rate_sum2', $achievement_rate_sum2 );
		
		// -----3. R&D관련부서(연구, 거더, 파일, 제안)------------------------------------------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft6_tot [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			if ($rd_name == "") {
				$e = "0";
			}
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				
				$g_code = $record2 [Code];
				$g_name [$e] = $record2 [Name];
				
				if ($g_code >= 140 && $g_code <= 150) // R&D
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$rd_name [$e] = $g_name [$e];
						
						switch ($ana_option) {
							case 1 : // 총괄
								$sum [$month] = Contract_SUM_M ( $g_name [$e], $sel_year, $month ) + Change_SUM_M ( $g_name [$e], $sel_year, $month );
								break;
							case 2 : // 신규
								$sum [$month] = Contract_SUM_M ( $g_name [$e], $sel_year, $month );
								break;
							case 3 : // 계약금액변경
								$sum [$month] = Change_SUM_M ( $g_name [$e], $sel_year, $month );
								break;
							case 4 : // 물가변동
								$sum [$month] = ES_SUM_M ( $g_name [$e], $sel_year, $month );
								break;
						}
						
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$e]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD ); // 목표
						
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft6_tot [$month] = $soft6_tot [$month] + $sum [$month];
						$contract_sum3 [$e] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					
					$achievement_rate3 [$e] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$e ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft6_tot [$MAX_SUM + 1] > 0) {
			$soft6_tot [$MAX_SUM + 2] = ($soft6_tot [$MAX_SUM] / $soft6_tot [$MAX_SUM + 1]) * 100;
		}
		
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft6_tot_sum [$month] = number_format ( $soft6_tot [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft6_tot [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum3 = number_format ( $soft6_tot [$MAX_SUM + 2] );
		
		$rd_name_num = count ( $rd_name ) + 1;
		
		$this->assign ( 'rd_name', $rd_name );
		$this->assign ( 'rd_name_num', $rd_name_num );
		$this->assign ( 'contract_sum3', $contract_sum3 );
		$this->assign ( 'achievement_rate3', $achievement_rate3 );
		$this->assign ( 'soft6_tot_sum', $soft6_tot_sum );
		$this->assign ( 'achievement_rate_sum3', $achievement_rate_sum3 );
		
		// -----4. 총계----------------------------------------------------------------------------------------------------------------------------------//
		
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft_tot [$month] = $soft1_tot [$month] + $soft2_tot [$month] + $soft3_tot [$month] + $soft4_tot [$month] + $soft5_tot [$month] + $soft6_tot [$month] + $soft7_tot [$month] + $soft8_tot [$month] + $soft9_tot [$month];
			$soft_tot_sum [$month] = number_format ( $soft_tot [$month] / $UNIT );
			
			$sum_quartersum = $sum_quartersum + number_format ( $soft_tot [$month] / $UNIT, 0, '.', '' );
		}
		if ($soft_tot [$MAX_SUM + 1] > 0) {
			$soft_tot [$MAX_SUM + 2] = round ( ($soft_tot [$MAX_SUM] / $soft_tot [$MAX_SUM + 1]) * 100 );
		}
		$soft_tot_sum2 = number_format ( $soft_tot [$MAX_SUM + 2] );
		
		// -----2011-12-22 유승렬이사 수주는 했는데 미계약 해서 프로젝트 코드 안딴 경우도 수주금액을 표시해달라고 해서 처리함--------------------//
		$a = 1;
		$sum_quartersum = 0;
		
		for($month = 1; $month <= $MAX_SUM; $month ++) {
			if ($month <= 9) {
				$month = "0" . $month;
			}
			$serchdate = $sel_year . "-" . $month;
			
			$sql = "select sum(Contractpayment) total from project_no_contract_tbl where Month like '$serchdate%'";
			
			$clist = mysql_query ( $sql, $db );
			while ( $result = mysql_fetch_array ( $clist ) ) {
				$mtotal = $result [total];
			}
			if ($mtotal == "") {
				
				$sum_total [$a] = "0";
			} else {
				$sum_total [$a] = number_format ( $mtotal / $UNIT );
			}
			$sum_quartersum2 += ($mtotal / $UNIT);
			
			$a ++;
		}
		
		$this->assign ( 'soft_tot_sum', $soft_tot_sum );
		$this->assign ( 'soft_tot_sum2', $soft_tot_sum2 );
		$this->assign ( 'sum_total', $sum_total );
		$this->assign ( 'sum_quartersum2', $sum_quartersum2 );
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----3. 메츨집계표-----------------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		$UNIT = 1000000; // 단위:백만원
		$MAX_CNT = 12;
		$MAX_SUM = 13;
		$SALES_FIELD2 = "Sales";
		// $LAST_GROUP_CODE_NO = 27;
		$LAST_GROUP_CODE_NO = 212;
		
		// -----1. 설계관련부서(교통,국도,지방,구조,지반,교통,수자,항만,도시,환경,환에,진단)-------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft1_tot2 [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			$gg = "0";
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by orderno";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				$g_code = $record2 [Code];
				$g_name [$gg] = $record2 [Name];
				
				if ($g_code >= 100 && $g_code <= 111) // 설계(고속,국도,)
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$design_name2 [$gg] = $g_name [$gg];
						
						$sum [$month] = Demand_SUM_M ( $g_name [$gg], $sel_year, $month );
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$gg]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD2 ); // 목표
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft1_tot2 [$month] = $soft1_tot2 [$month] + $sum [$month];
						$bill_sum [$gg] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					$achievement_rate4 [$gg] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$gg ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft1_tot2 [$MAX_SUM + 1] > 0) {
			$soft1_tot2 [$MAX_SUM + 2] = round ( ($soft1_tot2 [$MAX_SUM] / $soft1_tot2 [$MAX_SUM + 1]) * 100 );
		}
		$sum_quartersum = 0;
		
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft1_tot2_sum2 [$month] = number_format ( $soft1_tot2 [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft1_tot2 [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum4 = number_format ( $soft1_tot2 [$MAX_SUM + 2] );
		
		$design_name_num2 = count ( $design_name2 ) + 1;
		
		$this->assign ( 'design_name2', $design_name2 );
		$this->assign ( 'design_name_num2', $design_name_num2 );
		$this->assign ( 'bill_sum', $bill_sum );
		$this->assign ( 'achievement_rate4', $achievement_rate4 );
		$this->assign ( 'soft1_tot2_sum2', $soft1_tot2_sum2 );
		$this->assign ( 'achievement_rate_sum4', $achievement_rate_sum4 );
		
		// -----2. 감리관련부서(감리, 전기)------------------------------------------------------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft2_tot2 [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			if ($inspection_name2 == "") {
				$ff = "0";
			}
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				
				$g_code = $record2 [Code];
				$g_name [$ff] = $record2 [Name];
				
				if ($g_code >= 120 && $g_code <= 130) // 감리 + 시공의 전기포함
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$inspection_name2 [$ff] = $g_name [$ff];
						
						$sum [$month] = Demand_SUM_M ( $g_name [$ff], $sel_year, $month );
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ff]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD2 ); // 목표
						
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft2_tot2 [$month] = $soft2_tot2 [$month] + $sum [$month];
						$bill_sum2 [$ff] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					
					$achievement_rate5 [$ff] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$ff ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft2_tot2 [$MAX_SUM + 1] > 0) {
			$soft2_tot2 [$MAX_SUM + 2] = ($soft2_tot2 [$MAX_SUM] / $soft2_tot2 [$MAX_SUM + 1]) * 100;
		}
		
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft2_tot2_sum [$month] = number_format ( $soft2_tot2 [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft2_tot2 [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum5 = number_format ( $soft2_tot2 [$MAX_SUM + 2] );
		
		$inspection_name_num2 = count ( $inspection_name2 ) + 1;
		
		$this->assign ( 'inspection_name2', $inspection_name2 );
		$this->assign ( 'inspection_name_num2', $inspection_name_num2 );
		$this->assign ( 'bill_sum2', $bill_sum2 );
		$this->assign ( 'achievement_rate5', $achievement_rate5 );
		$this->assign ( 'soft2_tot2_sum', $soft2_tot2_sum );
		$this->assign ( 'achievement_rate_sum5', $achievement_rate_sum5 );
		
		// -----3. R&D관련부서(연구, 거더, 파일, 제안)------------------------------------------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft6_tot2 [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			if ($rd_name2 == "") {
				$ee = "0";
			}
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				
				$g_code = $record2 [Code];
				$g_name [$ee] = $record2 [Name];
				
				if ($g_code >= 140 && $g_code <= 150) // R&D
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$rd_name2 [$ee] = $g_name [$ee];
						
						$sum [$month] = Demand_SUM_M ( $g_name [$ee], $sel_year, $month );
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ee]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD2 ); // 목표
						
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft6_tot2 [$month] = $soft6_tot2 [$month] + $sum [$month];
						$bill_sum3 [$ee] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					
					$achievement_rate6 [$ee] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$ee ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft6_tot2 [$MAX_SUM + 1] > 0) {
			$soft6_tot2 [$MAX_SUM + 2] = ($soft6_tot2 [$MAX_SUM] / $soft6_tot2 [$MAX_SUM + 1]) * 100;
		}
		
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft6_tot2_sum [$month] = number_format ( $soft6_tot2 [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft6_tot2 [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum6 = number_format ( $soft6_tot2 [$MAX_SUM + 2] );
		
		$rd_name_num2 = count ( $rd_name2 ) + 1;
		
		$this->assign ( 'rd_name2', $rd_name2 );
		$this->assign ( 'rd_name_num2', $rd_name_num2 );
		$this->assign ( 'bill_sum3', $bill_sum3 );
		$this->assign ( 'achievement_rate6', $achievement_rate6 );
		$this->assign ( 'soft6_tot2_sum', $soft6_tot2_sum );
		$this->assign ( 'achievement_rate_sum6', $achievement_rate_sum6 );
		
		// -----4. 총계----------------------------------------------------------------------------------------------------------------------------------//
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft_tot2 [$month] = $soft1_tot2 [$month] + $soft2_tot2 [$month] + $soft3_tot2 [$month] + $soft4_tot2 [$month] + $soft5_tot2 [$month] + $soft6_tot2 [$month] + $soft7_tot2 [$month] + $soft8_tot2 [$month] + $soft9_tot2 [$month];
			$soft_tot_sum3 [$month] = number_format ( $soft_tot2 [$month] / $UNIT );
			
			$sum_quartersum = $sum_quartersum + number_format ( $soft_tot2 [$month] / $UNIT, 0, '.', '' );
		}
		if ($soft_tot2 [$MAX_SUM + 1] > 0) {
			$soft_tot2 [$MAX_SUM + 2] = round ( ($soft_tot2 [$MAX_SUM] / $soft_tot2 [$MAX_SUM + 1]) * 100 );
		}
		$soft_tot_sum4 = number_format ( $soft_tot2 [$MAX_SUM + 2] );
		
		$this->assign ( 'soft_tot_sum3', $soft_tot_sum3 );
		$this->assign ( 'soft_tot_sum4', $soft_tot_sum4 );
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----4. 수금집계표---------------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		$UNIT = 1000000; // 단위:백만원
		$MAX_CNT = 12;
		$MAX_SUM = 13;
		// $LAST_GROUP_CODE_NO = 27;
		$LAST_GROUP_CODE_NO = 212;
		
		// -----1. 설계관련부서(교통,국도,지방,구조,지반,교통,수자,항만,도시,환경,환에,진단)-------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft1_tot3 [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			$gg = "0";
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by orderno";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				$g_code = $record2 [Code];
				$g_name [$gg] = $record2 [Name];
				
				if ($g_code >= 100 && $g_code <= 111) // 설계(고속,국도,)
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$design_name3 [$gg] = $g_name [$gg];
						
						$sum [$month] = Collection_SUM_M ( $g_name [$gg], $sel_year, $month );
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$gg]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD2 ); // 목표
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft1_tot3 [$month] = $soft1_tot3 [$month] + $sum [$month];
						$collection_sum [$gg] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					$achievement_rate7 [$gg] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$gg ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft1_tot3 [$MAX_SUM + 1] > 0) {
			$soft1_tot3 [$MAX_SUM + 2] = round ( ($soft1_tot3 [$MAX_SUM] / $soft1_tot3 [$MAX_SUM + 1]) * 100 );
		}
		$sum_quartersum = 0;
		
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft1_tot3_sum2 [$month] = number_format ( $soft1_tot3 [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft1_tot3 [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum7 = number_format ( $soft1_tot3 [$MAX_SUM + 2] );
		
		$design_name_num3 = count ( $design_name3 ) + 1;
		
		$this->assign ( 'design_name3', $design_name3 );
		$this->assign ( 'design_name_num3', $design_name_num3 );
		$this->assign ( 'collection_sum', $collection_sum );
		$this->assign ( 'achievement_rate7', $achievement_rate7 );
		$this->assign ( 'soft1_tot3_sum2', $soft1_tot3_sum2 );
		$this->assign ( 'achievement_rate_sum7', $achievement_rate_sum7 );
		
		// -----2. 감리관련부서(감리, 전기)------------------------------------------------------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft2_tot3 [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			if ($inspection_name3 == "") {
				$ff = "0";
			}
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				
				$g_code = $record2 [Code];
				$g_name [$ff] = $record2 [Name];
				
				if ($g_code >= 120 && $g_code <= 130) // 감리 + 시공의 전기포함
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$inspection_name3 [$ff] = $g_name [$ff];
						
						$sum [$month] = Collection_SUM_M ( $g_name [$ff], $sel_year, $month );
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ff]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD2 ); // 목표
						
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft2_tot3 [$month] = $soft2_tot3 [$month] + $sum [$month];
						$collection_sum2 [$ff] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					
					$achievement_rate8 [$ff] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$ff ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft2_tot3 [$MAX_SUM + 1] > 0) {
			$soft2_tot3 [$MAX_SUM + 2] = ($soft2_tot3 [$MAX_SUM] / $soft2_tot3 [$MAX_SUM + 1]) * 100;
		}
		
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft2_tot3_sum [$month] = number_format ( $soft2_tot3 [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft2_tot3 [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum8 = number_format ( $soft2_tot3 [$MAX_SUM + 2] );
		
		$inspection_name_num3 = count ( $inspection_name3 ) + 1;
		
		$this->assign ( 'inspection_name3', $inspection_name3 );
		$this->assign ( 'inspection_name_num3', $inspection_name_num3 );
		$this->assign ( 'collection_sum2', $collection_sum2 );
		$this->assign ( 'achievement_rate8', $achievement_rate8 );
		$this->assign ( 'soft2_tot3_sum', $soft2_tot3_sum );
		$this->assign ( 'achievement_rate_sum8', $achievement_rate_sum8 );
		
		// -----3. R&D관련부서(연구, 거더, 파일, 제안)------------------------------------------------------------------------------------------------------//
		
		for($month = 1; $month <= $MAX_SUM + 2; $month ++) // $month = 13 합계
{
			$soft6_tot3 [$month] = 0;
		}
		
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			if ($rd_name3 == "") {
				$ee = "0";
			}
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				
				$g_code = $record2 [Code];
				$g_name [$ee] = $record2 [Name];
				
				if ($g_code >= 140 && $g_code <= 150) // R&D
{
					
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$rd_name3 [$ee] = $g_name [$ee];
						
						$sum [$month] = Collection_SUM_M ( $g_name [$ee], $sel_year, $month );
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];
					}
					
					$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ee]'";
					$rec_target = mysql_query ( $azSQL, $db );
					$sum [$MAX_SUM + 1] = 0;
					$sum [$MAX_SUM + 2] = 0;
					if (mysql_num_rows ( $rec_target ) > 0) {
						$sum [$MAX_SUM + 1] = mysql_result ( $rec_target, 0, $SALES_FIELD2 ); // 목표
						
						if ($sum [$MAX_SUM + 1] > 0) {
							$sum [$MAX_SUM + 2] = round ( ($sum [$MAX_SUM] / $sum [$MAX_SUM + 1] * 100.0) ); // 달성률
						} else {
							$sum [$MAX_SUM + 2] = 0; // 달성률
						}
					}
					$sum_quarter = 0;
					
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft6_tot3 [$month] = $soft6_tot3 [$month] + $sum [$month];
						$collection_sum3 [$ee] [$month] = number_format ( $sum [$month] / $UNIT );
						$sum_quarter = $sum_quarter + number_format ( $sum [$month] / $UNIT, 0, '.', '' );
					}
					
					$achievement_rate9 [$ee] = number_format ( $sum [$MAX_SUM + 2] );
				}
				$ee ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
		
		if ($soft6_tot3 [$MAX_SUM + 1] > 0) {
			$soft6_tot3 [$MAX_SUM + 2] = ($soft6_tot3 [$MAX_SUM] / $soft6_tot3 [$MAX_SUM + 1]) * 100;
		}
		
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft6_tot3_sum [$month] = number_format ( $soft6_tot3 [$month] / $UNIT );
			$sum_quartersum = $sum_quartersum + number_format ( $soft6_tot3 [$month] / $UNIT, 0, '.', '' );
		}
		$achievement_rate_sum9 = number_format ( $soft6_tot3 [$MAX_SUM + 2] );
		
		$rd_name_num3 = count ( $rd_name3 ) + 1;
		
		$this->assign ( 'rd_name3', $rd_name3 );
		$this->assign ( 'rd_name_num3', $rd_name_num3 );
		$this->assign ( 'collection_sum3', $collection_sum3 );
		$this->assign ( 'achievement_rate9', $achievement_rate9 );
		$this->assign ( 'soft6_tot3_sum', $soft6_tot3_sum );
		$this->assign ( 'achievement_rate_sum9', $achievement_rate_sum9 );
		
		// -----4. 총계----------------------------------------------------------------------------------------------------------------------------------//
		$sum_quartersum = 0;
		for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
			$soft_tot3 [$month] = $soft1_tot3 [$month] + $soft2_tot3 [$month] + $soft3_tot3 [$month] + $soft4_tot3 [$month] + $soft5_tot3 [$month] + $soft6_tot3 [$month] + $soft7_tot3 [$month] + $soft8_tot3 [$month] + $soft9_tot3 [$month];
			$soft_tot_sum5 [$month] = number_format ( $soft_tot3 [$month] / $UNIT );
			
			$sum_quartersum = $sum_quartersum + number_format ( $soft_tot3 [$month] / $UNIT, 0, '.', '' );
		}
		if ($soft_tot3 [$MAX_SUM + 1] > 0) {
			$soft_tot3 [$MAX_SUM + 2] = round ( ($soft_tot3 [$MAX_SUM] / $soft_tot3 [$MAX_SUM + 1]) * 100 );
		}
		$soft_tot_sum6 = number_format ( $soft_tot3 [$MAX_SUM + 2] );
		
		$this->assign ( 'soft_tot_sum5', $soft_tot_sum5 );
		$this->assign ( 'soft_tot_sum6', $soft_tot_sum6 );
		$this->assign ( 'PRINT', $PRINT );
		$this->assign ( 'memberID', $memberID );
		
		$this->display ( "intranet/common_contents/work_business/business_result_mvc.tpl" );
		
		// 프린트 페이지 자동 미리보기 시간 설정===============================
		if ($PRINT == "YES") {
			$this->assign ( 'mode', "print" );
			$this->display ( "intranet/js_page.tpl" );
		}
		// 프린트 페이지 자동 미리보기 시간 설정 끝=============================
	}
	
	
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	// -----수주 현황 상세 ==----------------------------------------------------------------------------------------------------------------------------//
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	function ContractReportLogic() {
		global $db;
		global $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $memberID;
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----1. 기본정보 LOGIC------------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		$date1 = date ( "Y" ); // / 오늘
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
		
		$LAST_GROUP_CODE_NO = 212;
		
		$today = $date1 . "-" . $date2 . "-" . $date3;
		
		if ($sel_year == "" || $sel_year == null) {
			$sel_year = $date1;
		}
		if ($sel_month == "" || $sel_month == null) {
			$sel_month = $date2;
		} else {
			if ($sel_month <= 9) {
				$sel_month = "0" . $sel_month;
			}
		}
		
		$uyear = date ( "Y" ) + 1; // ///최대 보이는 년도
		$UNIT = 100000000; // 단위:억만원
		
		if ($tab_index == "") {
			$tab_index = "2";
		}
		if ($sub_index == "") {
			$sub_index = "1";
		}
		$ana_option = $sub_index;
		$tab_Titel2 = array (
				'신규',
				'계약금액변경',
				'물가변동' 
		);
		$tab_value2 = array (
				'1',
				'2',
				'3' 
		);
		
		if ($sub_index == "") {
			$contract_title = "1";
		}
		
		if ($sub_index == "1") {
			$contract_title = "수 주 현 황 [신규]";
		} else if ($sub_index == "2") {
			$contract_title = "수 주 현 황[계약변경]";
			$DETAIL_OP = "계약금액";
		} else if ($sub_index == "3") {
			$contract_title = "수 주 현 황[물가변동]";
			$DETAIL_OP = "물가변동";
		}
		
		if ($_SESSION ['auth_ceo']) // 임원
			$this->assign ( 'auth_ceo', true );
		else
			$this->assign ( 'auth_ceo', false );
		
		if ($_SESSION ['auth_depart']) // 부서장
			$this->assign ( 'auth_depart', true );
		else
			$this->assign ( 'auth_depart', false );
		
		$uyear = date ( "Y" ) + 1;
		$last_day = date ( "t", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
		$last_day = $last_day + 1;
		
		if ($sel_year == "")
			$sel_year = date ( "Y" );
		if ($sel_month == "")
			$sel_month = date ( "m" );
		if ($sel_day == "")
			$sel_day = date ( "d" );
			
			// ////////쿼리 시작//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$k = "0";
		$query_data = array ();
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName in('01','02','03','05') order by code";
		
		$recordBlock4 = mysql_query ( $azSQL, $db );
		$g_code_num = mysql_num_rows ( $recordBlock4 );
		while ( $record4 = mysql_fetch_array ( $recordBlock4 ) ) {
			$g_code [$k] = $record4 [Code];
			$g_name [$k] = $record4 [Name];
			$g_Note [$k] = $record4 [Note];
			array_push ( $query_data, $re_row2 );
			
			for($i = 0; $i <= 3; $i ++) {
				
				$StDate = $sel_year . "-01-01";
				$EdDate = $sel_year . "-04-01";
				if ($i == 1) {
					$StDate = $sel_year . "-04-01";
					$EdDate = $sel_year . "-07-01";
				} else if ($i == 2) {
					$StDate = $sel_year . "-07-01";
					$EdDate = $sel_year . "-10-01";
				} else if ($i == 3) {
					$StDate = $sel_year . "-10-01";
					$EdDate = ($sel_year + 1) . "-01-01";
				}
				if ($sub_index == "1") {
					$j = "0";
					
					$azSQL = "SELECT * FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and DivCodeName = '$g_name[$k]' and ContractDate >= '$StDate' and ContractDate < '$EdDate'";
					
					$recordBlock5 = mysql_query ( $azSQL, $db );
					$result_num_row [$k] [$i] = mysql_num_rows ( $recordBlock5 );
					
					if (mysql_num_rows ( $recordBlock5 ) > 0) {
						while ( $record5 = mysql_fetch_array ( $recordBlock5 ) ) {
							// $g_ContractPayment = $record5[OrgContractPayment]; //최초 계약 금액 적용
							// 부가세별도인것으로 처리 (Payment) 필드가 부가세별도금액임
							$g_ContractPayment [$k] [$i] [$j] = $record5 [Payment]; // 최초 계약 금액 적용
							$g_ContractRatio [$k] [$i] [$j] = $record5 [ContractRatio];
							$g_ActualityRatio [$k] [$i] [$j] = $record5 [ActualityRatio];
							
							$g_ProjectNickname [$k] [$i] [$j] = $record5 [ProjectNickname];
							
							$g_OrderNickname [$k] [$i] [$j] = $record5 [OrderNickname];
							
							$g_ContractStart [$k] [$i] [$j] = $record5 [ContractStart];
							$g_ContractEnd [$k] [$i] [$j] = $record5 [ContractEnd];
							$g_DivRate [$k] [$i] [$j] = $record5 [DivRate]; // 부서별 실지분율
							
							if ($g_ActualityRatio [$k] [$i] [$j] <= 0 and $g_ActualityRatio [$k] [$i] [$j] > 100)
								$g_ActualityRatio [$k] [$i] [$j] = 100;
							
							if ($g_ContractRatio [$k] [$i] [$j] > 0 and $g_ContractRatio [$k] [$i] [$j] < 100) {
								// $ContractValue[$k][$i][$j] = ($g_ContractPayment[$k][$i][$j] * $g_ContractRatio[$k][$i][$j] / 100);
								$ContractValue [$k] [$i] [$j] = $g_ContractPayment [$k] [$i] [$j];
							} else {
								$ContractValue [$k] [$i] [$j] = $g_ContractPayment [$k] [$i] [$j];
							}
							if ($g_ActualityRatio [$k] [$i] [$j] > 0 and $g_ActualityRatio [$k] [$i] [$j] < 100) { // 당사지분율 적용
								$ActualityValue1 [$k] [$i] [$j] = ($g_ContractPayment [$k] [$i] [$j] * $g_ActualityRatio [$k] [$i] [$j] / 100);
								// $ActualityValue1 = ($g_ContractPayment * $g_ActualityRatio[$k][$i][$j] / 100) * ($g_DivRate / 100);
							} else {
								$ActualityValue1 [$k] [$i] [$j] = $g_ContractPayment [$k] [$i] [$j];
							}
							if ($g_DivRate [$k] [$i] [$j] > 0 and $g_DivRate [$k] [$i] [$j] < 100) { // 부서별 지분율 적용
							                                                                  // $ActualityValue[$k][$i][$j] = ($g_ContractPayment[$k][$i][$j] * $g_ActualityRatio[$k][$i][$j] / 100);
								$ActualityValue [$k] [$i] [$j] = ($ActualityValue1 [$k] [$i] [$j] * $g_DivRate [$k] [$i] [$j] / 100);
							} else {
								$ActualityValue [$k] [$i] [$j] = $ActualityValue1 [$k] [$i] [$j];
							}
							
							$sum1 [$k] [$i] = $sum1 [$k] [$i] + $ContractValue [$k] [$i] [$j];
							$sum2 [$k] [$i] = $sum2 [$k] [$i] + $ActualityValue [$k] [$i] [$j];
							$code_sum1 [$k] = $code_sum1 [$k] + $ContractValue [$k] [$i] [$j];
							$code_sum2 [$k] = $code_sum2 [$k] + $ActualityValue [$k] [$i] [$j];
							$tot1 = $tot1 + $ContractValue [$k] [$i] [$j];
							$tot2 = $tot2 + $ActualityValue [$k] [$i] [$j];
							
							$g_ContractValue [$k] [$i] [$j] = number_format ( $ContractValue [$k] [$i] [$j] / 1000000 );
							$g_ActualityValue [$k] [$i] [$j] = number_format ( $ActualityValue [$k] [$i] [$j] / 1000000 );
							
							$sum_1 [$k] [$i] = number_format ( $sum1 [$k] [$i] / 1000000 );
							$sum_2 [$k] [$i] = number_format ( $sum2 [$k] [$i] / 1000000 );
							
							$code_sum_1 [$k] = number_format ( $code_sum1 [$k] / 1000000 );
							$code_sum_2 [$k] = number_format ( $code_sum2 [$k] / 1000000 );
							
							$tot_1 = number_format ( $tot1 / 1000000 );
							$tot_2 = number_format ( $tot2 / 1000000 );
							$j ++;
						}
					}
				} else if ($sub_index == "2" or $sub_index == "3") {
					$j = "0";
					
					$azSQL = "SELECT * FROM (
								SELECT * FROM change_list_tbl WHERE 
									ProjectCode like '%$g_name[$k]%' 
									and 
									ChangeItem like '%$DETAIL_OP%' 
									and 
									ChangeDate >= '$StDate' 
									and  
									ChangeDate < '$EdDate' 
									order by ProjectCode asc, ChangeDate asc
								) a inner join
								(SELECT * FROM project_tbl)b
								on a.ProjectCode=b.ProjectCode";
					
				
					$recordBlock5 = mysql_query ( $azSQL, $db );
					$result_num_row [$k] [$i] = mysql_num_rows ( $recordBlock5 );
					
					if (mysql_num_rows ( $recordBlock5 ) > 0) {
						while ( $record5 = mysql_fetch_array ( $recordBlock5 ) ) {
							$g_ProjectCode [$k] [$i] [$j] = $record5 [ProjectCode];
							$g_ProjectNickname [$k] [$i] [$j] = $record5 [ProjectNickname];
							$g_OrderNickname [$k] [$i] [$j] = $record5 [OrderNickname];
							$g_ChangeDate [$k] [$i] [$j] = $record5 [ChangeDate];
							$g_ChangeItem [$k] [$i] [$j] = $record5 [ChangeItem];
							
							$g_ChangeBefore [$k] [$i] [$j] = str_replace ( ",", "", $record5 [ChangeBefore] );
							$g_ChangeAfter [$k] [$i] [$j] = str_replace ( ",", "", $record5 [ChangeAfter] );
							
							// VAT별도처리
							$g_ChangeBefore [$k] [$i] [$j] = $g_ChangeBefore [$k] [$i] [$j] / 1.1;
							$g_ChangeAfter [$k] [$i] [$j] = $g_ChangeAfter [$k] [$i] [$j] / 1.1;
							
							$send_AfterMoney = $g_ChangeAfter [$k] [$i] [$j];

							/*최동석 차장요청 20150420 start -----------------------------------*/
							$azsql99=          		" SELECT														";
							$azsql99= $azsql99." *, count(*) cnt											";
							$azsql99= $azsql99." FROM	group_div_tbl									";
							$azsql99= $azsql99." WHERE 														";
							$azsql99= $azsql99." ProjectCode='".$record5 [ProjectCode]."' 	";
							//echo $azsql99."<br>" ;
							/* ----------------------------------------------- */
							$result99 = mysql_query($azsql99, $db);
							$re_num99 = mysql_num_rows($result99);
							/* ----------------------------------------------- */
							$re_cnt	[$k] [$i] [$j]= mysql_result($result99,0,"cnt");
							
							$strJoin2 = "";
							$strJoinYn="N";
							if($re_cnt	[$k] [$i] [$j]>1){
								$returnArray = projectDivInfo( $record5 [ProjectCode], $send_AfterMoney ,'')	;
								$returnArrayLength = count($returnArray);
							   for($jj=0;$jj<$returnArrayLength;$jj++){
							   	$strJoin2 =	$strJoin2.$returnArray[$jj].",";
							   }
							   $strJoinYn="Y";
							}else{
								$strJoin2 = "";
								$strJoinYn="N";
							}
							$strJoin2_length = mb_strlen(trim($strJoin2));

							if($strJoin2_length>0){
								$aaa = substr($strJoin2, $strJoin2_length-1, 1);
								if($aaa==","){
									$strJoin2 = substr($strJoin2, 0,$strJoin2_length-1);
								}
							}
							$g_strJoin2 [$k] [$i] [$j] = $strJoin2;
							$g_strJoinYn [$k] [$i] [$j] = $strJoinYn;
							/*최동석 차장요청 20150420 start -----------------------------------*/

							$g_ChangePrice [$k] [$i] [$j] = ($g_ChangeAfter [$k] [$i] [$j] - $g_ChangeBefore [$k] [$i] [$j]);
							
							$g_Note2 [$k] [$i] [$j] = $record5 [Note];
							
							$sum1 [$k] [$i] = $sum1 [$k] [$i] + $g_ChangePrice [$k] [$i] [$j];
							$code_sum1 [$k] = $code_sum1 [$k] + $g_ChangePrice [$k] [$i] [$j];
							$tot1 = $tot1 + $g_ChangePrice [$k] [$i] [$j];
							
							$j ++;
						}
					}
				}
			}
			$k ++;
		}
		
		$this->assign ( 'memberID', $memberID );
		$this->assign ( 'today', $today );
		$this->assign ( 'uyear', $uyear );
		$this->assign ( 'tab_Titel2', $tab_Titel2 );
		$this->assign ( 'tab_value2', $tab_value2 );
		$this->assign ( 'tab_index', $tab_index );
		$this->assign ( 'sub_index', $sub_index );
		$this->assign ( 'last_day', $last_day );
		$this->assign ( 'sel_year', $sel_year );
		$this->assign ( 'sel_month', $sel_month );
		$this->assign ( 'sel_day', $sel_day );
		$this->assign ( 'contract_title', $contract_title );
		
		$this->assign ( 'g_name', $g_name );
		$this->assign ( 'g_Note', $g_Note );
		$this->assign ( 'g_code_row', $g_code_row );
		$this->assign ( 'query_data', $query_data );
		$this->assign ( 'g_ProjectNickname', $g_ProjectNickname );
		$this->assign ( 'g_ContractPayment', $g_ContractPayment );
		$this->assign ( 'g_ContractRatio', $g_ContractRatio );
		$this->assign ( 'g_ActualityRatio', $g_ActualityRatio );
		$this->assign ( 'g_OrderNickname', $g_OrderNickname );
		$this->assign ( 'g_ContractStart', $g_ContractStart );
		$this->assign ( 'g_ContractEnd', $g_ContractEnd );
		$this->assign ( 'g_DivRate', $g_DivRate );
		$this->assign ( 'g_Note2', $g_Note2 );
		
		$this->assign ( 'ContractValue', $ContractValue );
		$this->assign ( 'ActualityValue', $ActualityValue );
		$this->assign ( 'sum1', $sum1 );
		$this->assign ( 'sum2', $sum2 );
		$this->assign ( 'code_sum1', $code_sum1 );
		$this->assign ( 'code_sum2', $code_sum2 );
		$this->assign ( 'tot1', $tot1 );
		$this->assign ( 'tot2', $tot2 );
		
		$this->assign ( 'g_ProjectCode', $g_ProjectCode );
		$this->assign ( 'g_ChangeDate', $g_ChangeDate );
		$this->assign ( 'g_ChangeItem', $g_ChangeItem );
		$this->assign ( 'g_ChangeBefore', $g_ChangeBefore );
		
		$this->assign ( 'g_ChangeAfter', $g_ChangeAfter );

$this->assign ( 'g_strJoinYn', $g_strJoinYn );
$this->assign ( 'g_strJoin', $g_strJoin2 );	

		
		$this->assign ( 'g_ChangePrice', $g_ChangePrice );
		
		$this->assign ( 'result_num_row', $result_num_row );
		
		if ($sub_index == "1") {
			$this->display ( "intranet/common_contents/work_business/business_contract_mvc.tpl" );
		} else if ($sub_index == "2" or $sub_index == "3") {
			$this->display ( "intranet/common_contents/work_business/business_contract_mvc2.tpl" );
		}
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	// -----매출 현황 상세 ==----------------------------------------------------------------------------------------------------------------------------//
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	function SalesReportLogic() {
		global $db;
		global $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $memberID;
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----1. 기본정보 LOGIC------------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		$date1 = date ( "Y" ); // / 오늘
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
		
		$LAST_GROUP_CODE_NO = 212;
		
		$today = $date1 . "-" . $date2 . "-" . $date3;
		
		$uyear = date ( "Y" ) + 1; // ///최대 보이는 년도
		$UNIT = 100000000; // 단위:억만원
		
		if ($tab_index == "") {
			$tab_index = "2";
		}
		if ($sub_index == "") {
			$sub_index = "1";
		}
		$ana_option = $sub_index;
		$tab_Titel2 = array (
				'신규',
				'계약금액변경',
				'물가변동' 
		);
		$tab_value2 = array (
				'1',
				'2',
				'3' 
		);
		
		if ($sub_index == "") {
			$contract_title = "1";
		}
		
		if ($sub_index == "1") {
			$contract_title = "수 주 현 황 [신규]";
		} else if ($sub_index == "2") {
			$contract_title = "수 주 현 황[계약변경]";
			$DETAIL_OP = "계약금액";
		} else if ($sub_index == "3") {
			$contract_title = "수 주 현 황[물가변동]";
			$DETAIL_OP = "물가변동";
		}
		
		if ($_SESSION ['auth_ceo']) // 임원
			$this->assign ( 'auth_ceo', true );
		else
			$this->assign ( 'auth_ceo', false );
		
		if ($_SESSION ['auth_depart']) // 부서장
			$this->assign ( 'auth_depart', true );
		else
			$this->assign ( 'auth_depart', false );
		
		$uyear = date ( "Y" ) + 1;
		$last_day = date ( "t", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
		$last_day = $last_day + 1;
		
		if ($sel_year == "")
			$sel_year = date ( "Y" );
		if ($sel_month == "")
			$sel_month = date ( "m" );
		if ($sel_day == "")
			$sel_day = date ( "d" );
			
			// ////////쿼리 시작//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$k = "0";
		$query_data = array ();
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName in('01','02','03','05') order by code";
		
		$recordBlock4 = mysql_query ( $azSQL, $db );
		$g_code_num = mysql_num_rows ( $recordBlock4 );
		while ( $record4 = mysql_fetch_array ( $recordBlock4 ) ) {
			$g_code [$k] = $record4 [Code];
			$g_name [$k] = $record4 [Name];
			$g_Note [$k] = $record4 [Note];
			array_push ( $query_data, $re_row2 );
			
			for($i = 0; $i <= 3; $i ++) {
				
				$StDate = $sel_year . "-01-01";
				$EdDate = $sel_year . "-04-01";
				if ($i == 1) {
					$StDate = $sel_year . "-04-01";
					$EdDate = $sel_year . "-07-01";
				} else if ($i == 2) {
					$StDate = $sel_year . "-07-01";
					$EdDate = $sel_year . "-10-01";
				} else if ($i == 3) {
					$StDate = $sel_year . "-10-01";
					$EdDate = ($sel_year + 1) . "-01-01";
				}
				
				$j = "0";
				
				$azSQL = "SELECT * FROM group_div_tbl A, collectionpayment_tbl B WHERE A.ProjectCode = B.projectCode and DivCodeName = '$g_name[$k]' and DemandDate >= '$StDate' and DemandDate < '$EdDate'";
				
				$recordBlock5 = mysql_query ( $azSQL, $db );
				$result_num_row [$k] [$i] = mysql_num_rows ( $recordBlock5 );
				
				if (mysql_num_rows ( $recordBlock5 ) > 0) {
					while ( $record5 = mysql_fetch_array ( $recordBlock5 ) ) {
						
						$g_CollectionPayment [$k] [$i] [$j] = $record5 [CollectionPayment];
						
						// /2011-10-13추가////
						$g_DivRate [$k] [$i] [$j] = $record5 [DivRate]; // 부서별 실지분율
						
						if ($g_DivRate [$k] [$i] [$j] > 0 and $g_DivRate [$k] [$i] [$j] < 100) { // 부서별 지분율 적용
							$g_CollectionPayment [$k] [$i] [$j] = ($g_CollectionPayment [$k] [$i] [$j] * $g_DivRate [$k] [$i] [$j] / 100);
						} else {
							$g_CollectionPayment [$k] [$i] [$j] = $g_CollectionPayment [$k] [$i] [$j];
						}
						
						// VAT별도처리
						$g_CollectionPayment [$k] [$i] [$j] = $g_CollectionPayment [$k] [$i] [$j] / 1.1;
						
						// /2011-10-13추가////
						
						$g_ProjectCode = $record5 [ProjectCode];
						
						$azSQL = "SELECT * FROM project_tbl WHERE ProjectCode = '$g_ProjectCode'";
						
						$recordBlock6 = mysql_query ( $azSQL, $db );
						$g_ProjectNickname [$k] [$i] [$j] = @mysql_result ( $recordBlock6, 0, "ProjectNickname" );
						$g_OrderNickname [$k] [$i] [$j] = @mysql_result ( $recordBlock6, 0, "OrderNickname" );

						if($g_ProjectNickname [$k] [$i] [$j] =="")
						{
							$g_ProjectNickname [$k] [$i] [$j] =$g_ProjectCode;
						}

						
						$g_ContractStep [$k] [$i] [$j] = $record5 [ContractStep];
						$g_Kind [$k] [$i] [$j] = $record5 [Kind];
						
						$g_DemandDate [$k] [$i] [$j] = $record5 [DemandDate];
						$g_CollectionDate [$k] [$i] [$j] = $record5 [CollectionDate];
						$g_Note2 [$k] [$i] [$j] = $record5 [Note];
						
						$sum1 [$k] [$i] = $sum1 [$k] [$i] + $g_CollectionPayment [$k] [$i] [$j];
						
						$code_sum1 [$k] = $code_sum1 [$k] + $g_CollectionPayment [$k] [$i] [$j];
						$tot1 = $tot1 + $g_CollectionPayment [$k] [$i] [$j];
						
						$j ++;
					}
				}
			}
			$k ++;
		}
		
		$this->assign ( 'memberID', $memberID );
		$this->assign ( 'today', $today );
		$this->assign ( 'uyear', $uyear );
		$this->assign ( 'tab_Titel2', $tab_Titel2 );
		$this->assign ( 'tab_value2', $tab_value2 );
		$this->assign ( 'tab_index', $tab_index );
		$this->assign ( 'sub_index', $sub_index );
		$this->assign ( 'last_day', $last_day );
		$this->assign ( 'sel_year', $sel_year );
		$this->assign ( 'sel_month', $sel_month );
		$this->assign ( 'sel_day', $sel_day );
		
		$this->assign ( 'g_code', $g_code );
		$this->assign ( 'g_name', $g_name );
		$this->assign ( 'g_Note', $g_Note );
		
		$this->assign ( 'g_CollectionPayment', $g_CollectionPayment );
		$this->assign ( 'g_DivRate', $g_DivRate );
		$this->assign ( 'g_ProjectNickname', $g_ProjectNickname );
		$this->assign ( 'g_OrderNickname', $g_OrderNickname );
		$this->assign ( 'g_ContractStep', $g_ContractStep );
		$this->assign ( 'g_Kind', $g_Kind );
		$this->assign ( 'g_DemandDate', $g_DemandDate );
		$this->assign ( 'g_CollectionDate', $g_CollectionDate );
		$this->assign ( 'g_Note2', $g_Note2 );
		$this->assign ( 'sum1', $sum1 );
		$this->assign ( 'code_sum1', $code_sum1 );
		$this->assign ( 'tot1', $tot1 );
		
		$this->assign ( 'query_data', $query_data );
		$this->assign ( 'result_num_row', $result_num_row );
		
		$this->display ( "intranet/common_contents/work_business/business_sales_mvc.tpl" );
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	// -----수금 현황 상세 ==----------------------------------------------------------------------------------------------------------------------------//
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	function CollectionReportLogic() {
		global $db;
		global $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $memberID;
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----1. 기본정보 LOGIC------------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		
		$date1 = date ( "Y" ); // / 오늘
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
		
		$LAST_GROUP_CODE_NO = 212;
		
		$today = $date1 . "-" . $date2 . "-" . $date3;
		
		$uyear = date ( "Y" ) + 1; // ///최대 보이는 년도
		$UNIT = 100000000; // 단위:억만원
		
		if ($tab_index == "") {
			$tab_index = "2";
		}
		if ($sub_index == "") {
			$sub_index = "1";
		}
		$ana_option = $sub_index;
		$tab_Titel2 = array (
				'신규',
				'계약금액변경',
				'물가변동' 
		);
		$tab_value2 = array (
				'1',
				'2',
				'3' 
		);
		
		if ($sub_index == "") {
			$contract_title = "1";
		}
		
		if ($sub_index == "1") {
			$contract_title = "수 주 현 황 [신규]";
		} else if ($sub_index == "2") {
			$contract_title = "수 주 현 황[계약변경]";
			$DETAIL_OP = "계약금액";
		} else if ($sub_index == "3") {
			$contract_title = "수 주 현 황[물가변동]";
			$DETAIL_OP = "물가변동";
		}
		
		if ($_SESSION ['auth_ceo']) // 임원
			$this->assign ( 'auth_ceo', true );
		else
			$this->assign ( 'auth_ceo', false );
		
		if ($_SESSION ['auth_depart']) // 부서장
			$this->assign ( 'auth_depart', true );
		else
			$this->assign ( 'auth_depart', false );
		
		$uyear = date ( "Y" ) + 1;
		$last_day = date ( "t", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
		$last_day = $last_day + 1;
		
		if ($sel_year == "")
			$sel_year = date ( "Y" );
		if ($sel_month == "")
			$sel_month = date ( "m" );
		if ($sel_day == "")
			$sel_day = date ( "d" );
			
			// ////////쿼리 시작//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$k = "0";
		$query_data = array ();
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName in('01','02','03','05') order by code";
		
		$recordBlock4 = mysql_query ( $azSQL, $db );
		$g_code_num = mysql_num_rows ( $recordBlock4 );
		while ( $record4 = mysql_fetch_array ( $recordBlock4 ) ) {
			$g_code [$k] = $record4 [Code];
			$g_name [$k] = $record4 [Name];
			$g_Note [$k] = $record4 [Note];
			array_push ( $query_data, $re_row2 );
			
			for($i = 0; $i <= 3; $i ++) {
				
				$StDate = $sel_year . "-01-01";
				$EdDate = $sel_year . "-04-01";
				if ($i == 1) {
					$StDate = $sel_year . "-04-01";
					$EdDate = $sel_year . "-07-01";
				} else if ($i == 2) {
					$StDate = $sel_year . "-07-01";
					$EdDate = $sel_year . "-10-01";
				} else if ($i == 3) {
					$StDate = $sel_year . "-10-01";
					$EdDate = ($sel_year + 1) . "-01-01";
				}
				
				$j = "0";
				
				$azSQL = "SELECT * FROM group_div_tbl A, collectionpayment_tbl B WHERE A.ProjectCode = B.projectCode and DivCodeName = '$g_name[$k]' and CollectionDate >= '$StDate' and CollectionDate < '$EdDate'";
				
				$recordBlock5 = mysql_query ( $azSQL, $db );
				$result_num_row [$k] [$i] = mysql_num_rows ( $recordBlock5 );
				
				if (mysql_num_rows ( $recordBlock5 ) > 0) {
					while ( $record5 = mysql_fetch_array ( $recordBlock5 ) ) {
						
						$g_CollectionPayment [$k] [$i] [$j] = $record5 [CollectionPayment];
						
						// /2011-10-13추가////
						$g_DivRate [$k] [$i] [$j] = $record5 [DivRate]; // 부서별 실지분율
						
						if ($g_DivRate [$k] [$i] [$j] > 0 and $g_DivRate [$k] [$i] [$j] < 100) { // 부서별 지분율 적용
							$g_CollectionPayment [$k] [$i] [$j] = ($g_CollectionPayment [$k] [$i] [$j] * $g_DivRate [$k] [$i] [$j] / 100);
						} else {
							$g_CollectionPayment [$k] [$i] [$j] = $g_CollectionPayment [$k] [$i] [$j];
						}
						
						// VAT별도처리
						$g_CollectionPayment [$k] [$i] [$j] = $g_CollectionPayment [$k] [$i] [$j] / 1.1;
						// /2011-10-13추가////
						
						$g_ProjectCode = $record5 [ProjectCode];
						
						$azSQL = "SELECT * FROM project_tbl WHERE ProjectCode = '$g_ProjectCode'";
						
						$recordBlock6 = mysql_query ( $azSQL, $db );
						
						$g_ProjectNickname [$k] [$i] [$j] = @mysql_result ( $recordBlock6, 0, "ProjectNickname" );
						$g_OrderNickname [$k] [$i] [$j] = @mysql_result ( $recordBlock6, 0, "OrderNickname" );
						
						if($g_ProjectNickname [$k] [$i] [$j]=="")
						{
							$g_ProjectNickname [$k] [$i] [$j]=$g_ProjectCode;
						}
						$g_ContractStep [$k] [$i] [$j] = $record5 [ContractStep];
						$g_Kind [$k] [$i] [$j] = $record5 [Kind];
						
						$g_DemandDate [$k] [$i] [$j] = $record5 [DemandDate];
						$g_CollectionDate [$k] [$i] [$j] = $record5 [CollectionDate];
						$g_Note2 [$k] [$i] [$j] = $record5 [Note];
						
						$sum1 [$k] [$i] = $sum1 [$k] [$i] + $g_CollectionPayment [$k] [$i] [$j];
						$code_sum1 [$k] = $code_sum1 [$k] + $g_CollectionPayment [$k] [$i] [$j];
						$tot1 = $tot1 + $g_CollectionPayment [$k] [$i] [$j];
						
						$j ++;
					}
				}
			}
			$k ++;
		}
		
		$this->assign ( 'memberID', $memberID );
		$this->assign ( 'today', $today );
		$this->assign ( 'uyear', $uyear );
		$this->assign ( 'tab_Titel2', $tab_Titel2 );
		$this->assign ( 'tab_value2', $tab_value2 );
		$this->assign ( 'tab_index', $tab_index );
		$this->assign ( 'sub_index', $sub_index );
		$this->assign ( 'last_day', $last_day );
		$this->assign ( 'sel_year', $sel_year );
		$this->assign ( 'sel_month', $sel_month );
		$this->assign ( 'sel_day', $sel_day );
		
		$this->assign ( 'g_code', $g_code );
		$this->assign ( 'g_name', $g_name );
		$this->assign ( 'g_Note', $g_Note );
		
		$this->assign ( 'g_CollectionPayment', $g_CollectionPayment );
		$this->assign ( 'g_DivRate', $g_DivRate );
		$this->assign ( 'g_ProjectNickname', $g_ProjectNickname );
		$this->assign ( 'g_OrderNickname', $g_OrderNickname );
		$this->assign ( 'g_ContractStep', $g_ContractStep );
		$this->assign ( 'g_Kind', $g_Kind );
		$this->assign ( 'g_DemandDate', $g_DemandDate );
		$this->assign ( 'g_CollectionDate', $g_CollectionDate );
		$this->assign ( 'g_Note2', $g_Note2 );
		$this->assign ( 'sum1', $sum1 );
		$this->assign ( 'code_sum1', $code_sum1 );
		$this->assign ( 'tot1', $tot1 );
		
		$this->assign ( 'query_data', $query_data );
		$this->assign ( 'result_num_row', $result_num_row );
		
		$this->display ( "intranet/common_contents/work_business/business_collection_mvc.tpl" );
	}
	
	// ============================================================================
	// 미계약 수주현황 보기창
	// ============================================================================
	function NoncontractViewLogic() {
		global $db;
		global $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $PRINT;
		
		$date1 = date ( "Y" ); // / 오늘
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
		
		$today = $date1 . "-" . $date2 . "-" . $date3;
		
		$uyear = date ( "Y" ) + 1; // ///최대 보이는 년도
		$UNIT = 100000000; // 단위:억만원
		
		if ($tab_index == "") {
			$tab_index = "2";
		}
		if ($sub_index == "") {
			$sub_index = "1";
		}
		$ana_option = $sub_index;
		$tab_Titel2 = array (
				'총괄',
				'신규',
				'계약금액변경',
				'물가변동' 
		);
		$tab_value2 = array (
				'1',
				'2',
				'3',
				'4' 
		);
		
		if ($_SESSION ['auth_ceo']) // 임원
			$this->assign ( 'auth_ceo', true );
		else
			$this->assign ( 'auth_ceo', false );
		
		if ($_SESSION ['auth_depart']) // 부서장
			$this->assign ( 'auth_depart', true );
		else
			$this->assign ( 'auth_depart', false );
		
		$uyear = date ( "Y" ) + 1;
		$last_day = date ( "t", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
		$last_day = $last_day + 1;
		
		if ($sel_year == "")
			$sel_year = date ( "Y" );
		if ($sel_month == "")
			$sel_month = date ( "m" );
		if ($sel_day == "")
			$sel_day = date ( "d" );
		
		$serchdate = $sel_year . "-" . $sel_month;
		
		$NonContract_List = array ();
		
		$sql = "select * from project_no_contract_tbl where Month like '$serchdate%'";
		$clist = mysql_query ( $sql, $db );
		$i = 1;
		$total = 0;
		while ( $row_clist = mysql_fetch_array ( $clist ) ) {
			array_push ( $NonContract_List, $row_clist );
			
			$Contractpayment = $row_clist [Contractpayment];
			$Contractpayment_sum += $Contractpayment;
		}
		
		$this->assign ( 'today', $today );
		$this->assign ( 'uyear', $uyear );
		$this->assign ( 'tab_Titel2', $tab_Titel2 );
		$this->assign ( 'tab_value2', $tab_value2 );
		$this->assign ( 'tab_index', $tab_index );
		$this->assign ( 'sub_index', $sub_index );
		$this->assign ( 'last_day', $last_day );
		$this->assign ( 'sel_year', $sel_year );
		$this->assign ( 'sel_month', $sel_month );
		$this->assign ( 'sel_day', $sel_day );
		$this->assign ( 'NonContract_List', $NonContract_List );
		$this->assign ( 'Contractpayment_sum', $Contractpayment_sum );
		$this->display ( "intranet/common_contents/work_business/business_Noncontract_mvc.tpl" );
	}
	
	// ============================================================================
	// 미계약 수주현황 편집창
	// ============================================================================
	function NoncontractInputLogic() {
		global $db;
		global $mode, $no, $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $PRINT;
		
		$date1 = date ( "Y" ); // / 오늘
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
		
		$today = $date1 . "-" . $date2 . "-" . $date3;
		
		if ($sel_year == "" || $sel_year == null) {
			$sel_year = $date1;
		}
		if ($sel_month == "" || $sel_month == null) {
			$sel_month = $date2;
		} else {
			if ($sel_month <= 9) {
				$sel_month = "0" . $sel_month;
			}
		}
		
		$uyear = date ( "Y" ) + 1; // ///최대 보이는 년도
		$UNIT = 100000000; // 단위:억만원
		
		if ($tab_index == "") {
			$tab_index = "2";
		}
		if ($sub_index == "") {
			$sub_index = "1";
		}
		$ana_option = $sub_index;
		$tab_Titel2 = array (
				'총괄',
				'신규',
				'계약금액변경',
				'물가변동' 
		);
		$tab_value2 = array (
				'1',
				'2',
				'3',
				'4' 
		);
		
		if ($_SESSION ['auth_ceo']) // 임원
			$this->assign ( 'auth_ceo', true );
		else
			$this->assign ( 'auth_ceo', false );
		
		if ($_SESSION ['auth_depart']) // 부서장
			$this->assign ( 'auth_depart', true );
		else
			$this->assign ( 'auth_depart', false );
		
		$uyear = date ( "Y" ) + 1;
		$last_day = date ( "t", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
		$last_day = $last_day + 1;
		
		if ($sel_year == "")
			$sel_year = date ( "Y" );
		if ($sel_month == "")
			$sel_month = date ( "m" );
		if ($sel_day == "")
			$sel_day = date ( "d" );
		
		$serchdate = $sel_year . "-" . $sel_month;
		
		$sql = "select * from project_no_contract_tbl where no = '$no'";
		$clist = mysql_query ( $sql, $db );
		while ( $row_clist = mysql_fetch_array ( $clist ) ) {
			$ProjectName = $row_clist [ProjectName];
			$Month = $row_clist [Month];
			$Contractpayment = $row_clist [Contractpayment];
			$Note = $row_clist [Note];
		}
		
		$this->assign ( 'mode', $mode );
		$this->assign ( 'today', $today );
		$this->assign ( 'uyear', $uyear );
		$this->assign ( 'tab_Titel2', $tab_Titel2 );
		$this->assign ( 'tab_value2', $tab_value2 );
		$this->assign ( 'tab_index', $tab_index );
		$this->assign ( 'sub_index', $sub_index );
		$this->assign ( 'last_day', $last_day );
		$this->assign ( 'sel_year', $sel_year );
		$this->assign ( 'sel_month', $sel_month );
		$this->assign ( 'sel_day', $sel_day );
		$this->assign ( 'no', $no );
		$this->assign ( 'ProjectName', $ProjectName );
		$this->assign ( 'Month', $Month );
		$this->assign ( 'Contractpayment', $Contractpayment );
		$this->assign ( 'Note', $Note );
		$this->display ( "intranet/common_contents/work_business/business_Noncontract_input_mvc.tpl" );
	}
	
	// ============================================================================
	// 미계약 수주현황 편집 (입력 / 수정 / 삭제)
	// ============================================================================
	function NoncontractActionLogic() {
		global $db;
		global $ProjectName, $Month, $Contractpayment, $Note, $no;
		global $mode, $no, $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $PRINT;
		
		$Month = $sel_year . "-" . $sel_month . "-01";
		
		if ($mode == "mod") // 수정시
{
			$sql = "update project_no_contract_tbl set ProjectName='$ProjectName', Month='$Month', Contractpayment='$Contractpayment',Note='$Note' where no='$no'";
		} else if ($mode == "del") // 삭제시
{
			$sql = "delete from project_no_contract_tbl where no='$no'";
		} else // 추가시
{
			$sql = "insert into project_no_contract_tbl (ProjectName, Month, Contractpayment,Note) values";
			$sql = $sql . "('$ProjectName','$Month','$Contractpayment','$Note')";
		}
		
		mysql_query ( $sql, $db );
		
		$this->assign ( 'target', "opener" );
		$this->assign ( 'MoveURL', "businessresult_controller.php?ActionMode=Noncontract_view" );
		$this->display ( "intranet/move_page.tpl" );
	}
	
	// ============================================================================
	// 수주/매출 목표 입력
	// ============================================================================
	function ConsaleGoalInputLogic() {
		global $db;
		global $mode, $no, $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $PRINT;
		
		if ($ana_year == "")
			$ana_year = date ( "Y" );
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
		
		$today = $ana_year . "-" . $date2 . "-" . $date3;
		
		$azSQL = "select * from sale_target_tbl where TargetYear='$ana_year'";
		$rec_target = mysql_query ( $azSQL, $db );
		$target_count = mysql_num_rows ( $rec_target );
		
		if ($target_count > "0") {
			$mode = "mod";
		} else {
			$mode = "add";
		}
		
		if ($mode == "add") {
			$Group_List = array ();
			
			$azSQL = "select *,a.Name as Name1 from 
				(
					select * from systemconfig_tbl where SysKey = 'ProjectGroup' and Code in('01','02','03','05')
				)a inner join
				(
					select * from systemconfig_tbl where SysKey = 'ProjectCode'
				)b on a.Code = b.CodeORName order by b.orderno";
			
			$rec_target = mysql_query ( $azSQL, $db );
			while ( $record3 = mysql_fetch_array ( $rec_target ) ) {
				array_push ( $Group_List, $record3 );
			}
			
			$this->assign ( 'Group_List', $Group_List );
			$this->assign ( 'mode', $mode );
		}
		if ($mode == "mod") {
			$ConsaleGoal_List = array ();
			
			$azSQL = "select * from 
				(
					select * from systemconfig_tbl where SysKey = 'projectcode' and Code<'150' and Code<>'131'
				)a inner join
				(
					select * from sale_target_tbl  where TargetYear='$ana_year'
				)b on a.Name = b.ProjectPart order by orderno";
			
			$rec_target = mysql_query ( $azSQL, $db );
			while ( $record4 = mysql_fetch_array ( $rec_target ) ) {
				array_push ( $ConsaleGoal_List, $record4 );
			}
			$this->assign ( 'mode', $mode );
			$this->assign ( 'ConsaleGoal_List', $ConsaleGoal_List );
		}
		
		$this->assign ( 'ana_year', $ana_year );
		$this->display ( "intranet/common_contents/work_business/business_consalegoal_mvc.tpl" );
	}
	
	// ============================================================================
	// 수주/매출 목표 저장
	// ============================================================================
	function ConsaleGoalUpdateLogic() {
		global $db;
		global $mode, $ana_year;
		global $Group_num, $ConsaleGoal_num, $ProjectPart, $Order1, $Sales;
		
		$indel = "delete from sale_target_tbl where TargetYear='$ana_year'";
		
		// echo $indel."<br>";
		mysql_query ( $indel, $db );
		
		$delsql = "delete from project_targetmoney_tbl where Month like '$ana_year%'";
		
		// echo $delsql."<br>";
		mysql_query ( $delsql, $db );
		
		/*
		 * if($mode=="add")
		 * {
		 * $tCnt=$Group_num;
		 * }
		 * else if($mode=="mod")
		 * {
		 * $tCnt=$ConsaleGoal_num;
		 * }
		 */
		
		// for($m=1;$m <= $tCnt; $m++)
		for($m = 0; $m < count ( $ProjectPart ); $m ++) {
			$aa = str_replace ( ',', '', $Order1 [$m] );
			if ($aa == "")
				$aa = 0;
			$aa = $aa * 1000000;
			
			$aasum = $aasum + $aa;
			
			$bb = str_replace ( ',', '', $Sales [$m] );
			if ($bb == "")
				$bb = 0;
			$bb = $bb * 1000000;
			
			$bbsum = $bbsum + $bb;
			
			$insql = "insert into sale_target_tbl (TargetYear, ProjectPart, Order1, Sales) values('$ana_year', '$ProjectPart[$m]', $aa, $bb)";
			// echo $insql."<br>";
			mysql_query ( $insql, $db );
		}
		
		$ana_year2 = $ana_year . "-01-01";
		$insql2 = "insert into project_targetmoney_tbl (Month, Contractpayment, CollectionPayment) values('$ana_year2', '$aasum', $bbsum)";
		
		// echo $insql2."<br>";
		mysql_query ( $insql2, $db );
		
		$this->assign ( 'target', "self" );
		$this->assign ( 'MoveURL', "businessresult_controller.php?ActionMode=Consalegoal_input" );
		$this->display ( "intranet/move_page.tpl" );
	}
	
	
	
	
	
	
	
	
	
	
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	// -----수주 현황 상세 ==----------------------------------------------------------------------------------------------------------------------------//
	// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	function ContractReportLogic2() {
		global $db;
		global $sel_year, $sel_month, $sel_day, $GroupCode, $sub_index, $memberID;
	
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
		// -----1. 기본정보 LOGIC------------------------------------------------------------------------------------------------------------------------------//
		// -----------------------------------------------------------------------------------------------------------------------------------------------------//
	
		$date1 = date ( "Y" ); // / 오늘
		$date2 = date ( "m" ); // / 오늘
		$date3 = date ( "d" ); // / 오늘
	
		$LAST_GROUP_CODE_NO = 212;
	
		$today = $date1 . "-" . $date2 . "-" . $date3;
	
		if ($sel_year == "" || $sel_year == null) {
			$sel_year = $date1;
		}
		if ($sel_month == "" || $sel_month == null) {
			$sel_month = $date2;
		} else {
			if ($sel_month <= 9) {
				$sel_month = "0" . $sel_month;
			}
		}
	
		$uyear = date ( "Y" ) + 1; // ///최대 보이는 년도
		$UNIT = 100000000; // 단위:억만원
	
		if ($tab_index == "") {
			$tab_index = "2";
		}
		if ($sub_index == "") {
			$sub_index = "1";
		}
		$ana_option = $sub_index;
		$tab_Titel2 = array (
				'신규',
				'계약금액변경',
				'물가변동'
		);
		$tab_value2 = array (
				'1',
				'2',
				'3'
		);
	
		if ($sub_index == "") {
			$contract_title = "1";
		}
	
		if ($sub_index == "1") {
			$contract_title = "수 주 현 황 [신규]";
		} else if ($sub_index == "2") {
			$contract_title = "수 주 현 황[계약변경]";
			$DETAIL_OP = "계약금액";
		} else if ($sub_index == "3") {
			$contract_title = "수 주 현 황[물가변동]";
			$DETAIL_OP = "물가변동";
		}
	
		if ($_SESSION ['auth_ceo']) // 임원
			$this->assign ( 'auth_ceo', true );
		else
			$this->assign ( 'auth_ceo', false );
	
		if ($_SESSION ['auth_depart']) // 부서장
			$this->assign ( 'auth_depart', true );
		else
			$this->assign ( 'auth_depart', false );
	
		$uyear = date ( "Y" ) + 1;
		$last_day = date ( "t", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
		$last_day = $last_day + 1;
	
		if ($sel_year == "")
			$sel_year = date ( "Y" );
		if ($sel_month == "")
			$sel_month = date ( "m" );
		if ($sel_day == "")
			$sel_day = date ( "d" );
			
		// ////////쿼리 시작//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
		$k = "0";
		$query_data = array ();
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName in('01','02','03','05') order by code";
	
		$recordBlock4 = mysql_query ( $azSQL, $db );
		$g_code_num = mysql_num_rows ( $recordBlock4 );
			while ( $record4 = mysql_fetch_array ( $recordBlock4 ) ) {
				$g_code [$k] = $record4 [Code];
				$g_name [$k] = $record4 [Name];
				$g_Note [$k] = $record4 [Note];
				array_push ( $query_data, $re_row2 );
					
				for($i = 0; $i <= 3; $i ++) {//분기별 설정 1~4분기
		
					$StDate = $sel_year . "-01-01";
					$EdDate = $sel_year . "-04-01";
					if ($i == 1) {
						$StDate = $sel_year . "-04-01";
						$EdDate = $sel_year . "-07-01";
					} else if ($i == 2) {
						$StDate = $sel_year . "-07-01";
						$EdDate = $sel_year . "-10-01";
					} else if ($i == 3) {
						$StDate = $sel_year . "-10-01";
						$EdDate = ($sel_year + 1) . "-01-01";
					}
					if ($sub_index == "1") {
						$j = "0";
							
						//$azSQL = "SELECT * FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and DivCodeName = '$g_name[$k]' and ContractDate >= '$StDate' and ContractDate < '$EdDate'";

						$azSQL = "SELECT  a.*,b.*,b.projectCode as tmp_ProjectCode  FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and DivCodeName = '$g_name[$k]' and ContractDate >= '$StDate' and ContractDate < '$EdDate'";
						
						//echo $azSQL."<br>";


						$recordBlock5 = mysql_query ( $azSQL, $db );
						$result_num_row [$k] [$i] = mysql_num_rows ( $recordBlock5 );
							
						if (mysql_num_rows ( $recordBlock5 ) > 0) {
							while ( $record5 = mysql_fetch_array ( $recordBlock5 ) ) {
								// $g_ContractPayment = $record5[OrgContractPayment]; //최초 계약 금액 적용
								// 부가세별도인것으로 처리 (Payment) 필드가 부가세별도금액임
								$g_ContractPayment [$k] [$i] [$j] = $record5 [Payment]; // 최초 계약 금액 적용
								$g_ContractRatio [$k] [$i] [$j] = $record5 [ContractRatio];
								$g_ActualityRatio [$k] [$i] [$j] = $record5 [ActualityRatio];
									
								$g_ProjectNickname [$k] [$i] [$j] = $record5 [ProjectNickname];
									
								$g_OrderNickname [$k] [$i] [$j] = $record5 [OrderNickname];
									
								$g_ContractStart [$k] [$i] [$j] = $record5 [ContractStart];
								$g_ContractEnd [$k] [$i] [$j] = $record5 [ContractEnd];
								$g_DivRate [$k] [$i] [$j] = $record5 [DivRate]; // 부서별 실지분율
								$tmp_ProjectCode  = $record5[tmp_ProjectCode ]; 
									
								if ($g_ActualityRatio [$k] [$i] [$j] <= 0 and $g_ActualityRatio [$k] [$i] [$j] > 100)
									$g_ActualityRatio [$k] [$i] [$j] = 100;
									
								if ($g_ContractRatio [$k] [$i] [$j] > 0 and $g_ContractRatio [$k] [$i] [$j] < 100) {
									// $ContractValue[$k][$i][$j] = ($g_ContractPayment[$k][$i][$j] * $g_ContractRatio[$k][$i][$j] / 100);
									$ContractValue [$k] [$i] [$j] = $g_ContractPayment [$k] [$i] [$j];
								} else {
									$ContractValue [$k] [$i] [$j] = $g_ContractPayment [$k] [$i] [$j];
								}
								if ($g_ActualityRatio [$k] [$i] [$j] > 0 and $g_ActualityRatio [$k] [$i] [$j] < 100) { // 당사지분율 적용
									$ActualityValue1 [$k] [$i] [$j] = ($g_ContractPayment [$k] [$i] [$j] * $g_ActualityRatio [$k] [$i] [$j] / 100);
									// $ActualityValue1 = ($g_ContractPayment * $g_ActualityRatio[$k][$i][$j] / 100) * ($g_DivRate / 100);
								} else {
									$ActualityValue1 [$k] [$i] [$j] = $g_ContractPayment [$k] [$i] [$j];
								}
								if ($g_DivRate [$k] [$i] [$j] > 0 and $g_DivRate [$k] [$i] [$j] < 100) { // 부서별 지분율 적용
									// $ActualityValue[$k][$i][$j] = ($g_ContractPayment[$k][$i][$j] * $g_ActualityRatio[$k][$i][$j] / 100);
									$ActualityValue [$k] [$i] [$j] = ($ActualityValue1 [$k] [$i] [$j] * $g_DivRate [$k] [$i] [$j] / 100);
								} else {
									$ActualityValue [$k] [$i] [$j] = $ActualityValue1 [$k] [$i] [$j];
								}
									

								//해당년도에 계약금액변경이 있으면 신규에는 변경전 금액을 표시한다
								$sql = "select * from Change_List_tbl where ProjectCode='$tmp_ProjectCode' and ChangeItem ='계약금액' and ChangeDate like '$sel_year%'";
								//echo $sql."<br>"; 
								$re = mysql_query($sql,$db);
								while($re_row = mysql_fetch_array($re)) {

									$ActualityValue [$k] [$i] [$j]=$re_row[ChangeBefore];
									$ActualityValue [$k] [$i] [$j]=str_replace(",","",$ActualityValue [$k] [$i] [$j]);
									$ActualityValue [$k] [$i] [$j]=round($ActualityValue [$k] [$i] [$j]/1.1);
								}


								$sum1 [$k] [$i] = $sum1 [$k] [$i] + $ContractValue [$k] [$i] [$j];
								$sum2 [$k] [$i] = $sum2 [$k] [$i] + $ActualityValue [$k] [$i] [$j];
								$code_sum1 [$k] = $code_sum1 [$k] + $ContractValue [$k] [$i] [$j];
								$code_sum2 [$k] = $code_sum2 [$k] + $ActualityValue [$k] [$i] [$j];
								$tot1 = $tot1 + $ContractValue [$k] [$i] [$j];
								$tot2 = $tot2 + $ActualityValue [$k] [$i] [$j];
									
								$g_ContractValue [$k] [$i] [$j] = number_format ( $ContractValue [$k] [$i] [$j] / 1000000 );
								$g_ActualityValue [$k] [$i] [$j] = number_format ( $ActualityValue [$k] [$i] [$j] / 1000000 );
									
								$sum_1 [$k] [$i] = number_format ( $sum1 [$k] [$i] / 1000000 );
								$sum_2 [$k] [$i] = number_format ( $sum2 [$k] [$i] / 1000000 );
									
								$code_sum_1 [$k] = number_format ( $code_sum1 [$k] / 1000000 );
								$code_sum_2 [$k] = number_format ( $code_sum2 [$k] / 1000000 );
									
								$tot_1 = number_format ( $tot1 / 1000000 );
								$tot_2 = number_format ( $tot2 / 1000000 );
								$j ++;
							}
						}
					} else if ($sub_index == "2" or $sub_index == "3") {
						$j = "0";
						
						$azSQL = "  SELECT                                                             
												  GDT.ProjectCode			as gdt_ProjectCode                                         
												, GDT.GroupCode				as gdt_GroupCode                                           
												, GDT.DivRate          		as gdt_DivRate                                                 
												, GDT.DivCodeName			as gdt_DivCodeName                                      
												, GDT.Note              		as gdt_Note                                                    
												, GDT.no                 		as gdt_no                                                        
												, GDT.UpdateDate   			as gdt_UpdateDate                                          
												, GDT.UpdateUser          	as gdt_UpdateUser 
												
												, PT.ProjectCode           	as pt_ProjectCode                                            
												, PT.ProjectName          	as pt_ProjectName         
												, PT.ProjectNickname     	as pt_ProjectNickname  
												, PT.OrderNickname     		as pt_OrderNickname  
												, PT.Payment                 	as pt_Payment              
												, PT.mAINgroup             	as pt_mAINgroup           
												, PT.ContractDate          	as pt_ContractDate        
												, PT.ContractStart         	as pt_ContractStart        
												, PT.ContractEnd           	as pt_ContractEnd 

												, CLT.ProjectCode     		as clt_ProjectCode     	     
												, CLT.ChangeDate     		as clt_ChangeDate     		
												, CLT.ChangeItem     		as clt_ChangeItem     		 
												, CLT.ChangeBefore  		as clt_ChangeBefore  		     
												, CLT.ChangeAfter     		as clt_ChangeAfter     	
												, CLT.Note                		as clt_Note                		
												, CLT.No                   		as clt_No                   		     
												, CLT.UpdateDate     		as clt_UpdateDate     		
												, CLT.UpdateUser      		as clt_UpdateUser      	
											 FROM                                                              
											group_div_tbl GDT                                            
												LEFT OUTER JOIN project_tbl PT ON              
													GDT.ProjectCode = PT.ProjectCode           
												LEFT OUTER JOIN change_list_tbl CLT ON      
													GDT.ProjectCode = CLT.ProjectCode         
											WHERE                                                             
												CLT.ChangeItem like '%$DETAIL_OP%'                     
												AND                                                             
												CLT.ChangeDate >= '$StDate'               
												AND                                                             
												CLT.ChangeDate < '$EdDate'    
												AND
												GDT.DivCodeName like '%$g_name[$k]%'
												
												";			

				//ECHO $azSQL ."<bR>";
					/*		
						$azSQL = "  SELECT * FROM (
											SELECT * FROM change_list_tbl WHERE
											ProjectCode like '%$g_name[$k]%'
											and
											ChangeItem like '%$DETAIL_OP%'
											and
											ChangeDate >= '$StDate'
											and
											ChangeDate < '$EdDate'
											order by ProjectCode asc, ChangeDate asc
											) a inner join
											(SELECT * FROM project_tbl)b
											on a.ProjectCode=b.ProjectCode                       ";
					
					*/		
						$recordBlock5 = mysql_query ( $azSQL, $db );
						$result_num_row [$k] [$i] = mysql_num_rows ( $recordBlock5 );
							
							if (mysql_num_rows ( $recordBlock5 ) > 0) {
								while ( $record5 = mysql_fetch_array ( $recordBlock5 ) ) {
								$g_ProjectCode [$k] [$i] [$j] = $record5 [gdt_ProjectCode];
								$g_ProjectNickname [$k] [$i] [$j] = $record5 [pt_ProjectNickname];
								$g_OrderNickname [$k] [$i] [$j] = $record5 [pt_OrderNickname];
								$g_ChangeDate [$k] [$i] [$j] = $record5 [clt_ChangeDate];
								$g_ChangeItem [$k] [$i] [$j] = $record5 [clt_ChangeItem];
									
								$g_ChangeBefore [$k] [$i] [$j] = str_replace ( ",", "", $record5 [clt_ChangeBefore] );
								$g_ChangeAfter [$k] [$i] [$j] = str_replace ( ",", "", $record5 [clt_ChangeAfter] );
									
								// VAT별도처리
								$g_ChangeBefore [$k] [$i] [$j] = $g_ChangeBefore [$k] [$i] [$j] / 1.1;
								$g_ChangeAfter [$k] [$i] [$j] = $g_ChangeAfter [$k] [$i] [$j] / 1.1;
									
								$send_AfterMoney = $g_ChangeAfter [$k] [$i] [$j];
				

								
								/**************************************************/
								/*최동석 차장요청 20150420 start -----------------------------------*/
									$azsql99=          		" SELECT														";
									$azsql99= $azsql99." *, count(*) cnt											";
											$azsql99= $azsql99." FROM	group_div_tbl									";
											$azsql99= $azsql99." WHERE 														";
											$azsql99= $azsql99." ProjectCode='".$record5 [gdt_ProjectCode]."' 	";
									//echo $azsql99."<br>" ;
									/* ----------------------------------------------- */
									$result99 = mysql_query($azsql99, $db);
									$re_num99 = mysql_num_rows($result99);
									/* ----------------------------------------------- */
									$re_cnt	[$k] [$i] [$j]= mysql_result($result99,0,"cnt");
										
								
									$strJoin2 = "";
									$strJoinYn="N";
									$gdt_name ="";
									$int_DivRate=0;
									$Rate=0;
									$g_ChangeBefore2=array();
					
									if($re_cnt[$k][$i][$j]>1){
											
										$clt_Note = $record5 [clt_Note];
										//echo $clt_Note;									
										if(strpos($clt_Note, "<>") == true){

											$Note_arr     = explode("<>",$clt_Note);
											$Note_arr_cnt = count ($Note_arr);
											/*-------------------------------------*/
											$g_Change = ($g_ChangeAfter [$k] [$i] [$j] - $g_ChangeBefore [$k] [$i] [$j]);	
											$g_ChangePrice2 [$k] [$i] [$j]= 	$g_Change;
					 						
											for($ii=0;$ii<$Note_arr_cnt;$ii++){
												$Note     = explode(":",$Note_arr[$ii]);
																							
												if($Note[0]==$g_name[$k]){
													/*--------------------------------------------------------------------------------------------------------*/
													$returnArray2 = projectDivInfo( $record5 [gdt_ProjectCode], $g_ChangeBefore [$k] [$i] [$j] ,$g_name[$k])	;
													$gdt_name2 = $returnArray2['gdt_name'];//부서 구분값(한글)
													$int_DivRate2 = $returnArray2['int_DivRate'];//비율적용한 금액
													$Rate2 = $returnArray2['Rate'];
													/*----------------------------------------------------*/
													$ggg= abs($g_ChangeBefore [$k] [$i] [$j]) *$Rate2;
													$strJoin22 = $ggg;
													/*--------------------------------------------------------------------------------------------------------*/
														
													$Note[1] = str_replace(",","",$Note[1]);// 숫자입력값중 콤마(,) 제거
												
													$Rate = (int)$Note[1]*0.1;
													$strJoinYn="Y";
																										
													if($g_Change<0){//음수
														$g_ChangePrice [$k] [$i] [$j] = abs($Note[1]);
															
														$g_ChangePrice [$k] [$i] [$j] = -$g_ChangePrice [$k] [$i] [$j];
															
													}else{//양수
														$g_ChangePrice [$k] [$i] [$j] = abs($Note[1]);
															
													}
												
												$returnArray = projectDivInfo( $record5 [gdt_ProjectCode], $send_AfterMoney ,$g_name[$k])	;
												$gdt_name = $returnArray['gdt_name'];
												$int_DivRate = $returnArray['int_DivRate'];
												$Rate = $returnArray['Rate'];
												//echo $gdt_name."=".$int_DivRate."=".$Rate;
													
												$strJoin2 = $int_DivRate;												
												
												}else{}
											}//for End
														
											
										}else{							
											/*--------------------------------------------------------------------------------------------------------*/
											$returnArray2 = projectDivInfo( $record5 [gdt_ProjectCode], $g_ChangeBefore [$k] [$i] [$j] ,$g_name[$k])	;
											$gdt_name2 = $returnArray2['gdt_name'];
											$int_DivRate2 = $returnArray2['int_DivRate'];
											$Rate2 = $returnArray2['Rate'];
											/*----------------------------------------------------*/
											$ggg= abs($g_ChangeBefore [$k] [$i] [$j]) *$Rate2;
											$strJoin22 = $ggg;
											/*--------------------------------------------------------------------------------------------------------*/

											$returnArray = projectDivInfo( $record5 [gdt_ProjectCode], $send_AfterMoney ,$g_name[$k])	;
											$gdt_name = $returnArray['gdt_name'];
											$int_DivRate = $returnArray['int_DivRate'];
											$Rate = $returnArray['Rate'];
											//echo $gdt_name."=".$int_DivRate."=".$Rate;
											
											$strJoin2 = $int_DivRate;
											$strJoinYn="Y";
											
											//$g_ChangePrice [$k] [$i] [$j] = ($g_ChangeAfter [$k] [$i] [$j] - $g_ChangeBefore [$k] [$i] [$j]);
											$g_Change = ($g_ChangeAfter [$k] [$i] [$j] - $g_ChangeBefore [$k] [$i] [$j]);
											
											$g_ChangePrice2 [$k] [$i] [$j] = ($g_ChangeAfter [$k] [$i] [$j] - $g_ChangeBefore [$k] [$i] [$j]);
											
											if($g_Change<0){//음수
												$g_ChangePrice [$k] [$i] [$j] = abs($g_Change) *$Rate;
											
												$g_ChangePrice [$k] [$i] [$j] = -$g_ChangePrice [$k] [$i] [$j];
											
											}else{//양수
												$g_ChangePrice [$k] [$i] [$j] = abs($g_Change) *$Rate;
											
											}											
											
										}										
								
									}else{
										
										$gdt_name = "";
										$int_DivRate = 0;
										$Rate = 0;

										$strJoin2 = "";
										$strJoinYn="N";

									$g_ChangePrice [$k] [$i] [$j] = ($g_ChangeAfter [$k] [$i] [$j] - $g_ChangeBefore [$k] [$i] [$j]);

									}
									
									
									$g_strJoin22 [$k] [$i] [$j]   = $strJoin22;
									
									
									$g_strJoin2 [$k] [$i] [$j]   = $strJoin2;
									$g_strJoinYn [$k] [$i] [$j] = $strJoinYn;
								/*최동석 차장요청 20150420 end -----------------------------------*/
								/**************************************************/


								
										
									$g_Note2 [$k] [$i] [$j] = $record5 [Note];
										
									$sum1 [$k] [$i] = $sum1 [$k] [$i] + $g_ChangePrice [$k] [$i] [$j];
									$code_sum1 [$k] = $code_sum1 [$k] + $g_ChangePrice [$k] [$i] [$j];
									$tot1 = $tot1 + $g_ChangePrice [$k] [$i] [$j];
										
									$j ++;
								}
							}//if recordBlock5
						}//else if : sub_index ==2,3
					}//for
					$k ++;
				}//while
	
				$this->assign ( 'memberID', $memberID );
				$this->assign ( 'today', $today );
				$this->assign ( 'uyear', $uyear );
				$this->assign ( 'tab_Titel2', $tab_Titel2 );
				$this->assign ( 'tab_value2', $tab_value2 );
				$this->assign ( 'tab_index', $tab_index );
				$this->assign ( 'sub_index', $sub_index );
				$this->assign ( 'last_day', $last_day );
				$this->assign ( 'sel_year', $sel_year );
				$this->assign ( 'sel_month', $sel_month );
				$this->assign ( 'sel_day', $sel_day );
				$this->assign ( 'contract_title', $contract_title );

				$this->assign ( 'g_name', $g_name );
				$this->assign ( 'g_Note', $g_Note );
				$this->assign ( 'g_code_row', $g_code_row );
				$this->assign ( 'query_data', $query_data );
				$this->assign ( 'g_ProjectNickname', $g_ProjectNickname );
				$this->assign ( 'g_ContractPayment', $g_ContractPayment );
				$this->assign ( 'g_ContractRatio', $g_ContractRatio );
				$this->assign ( 'g_ActualityRatio', $g_ActualityRatio );
				$this->assign ( 'g_OrderNickname', $g_OrderNickname );
				$this->assign ( 'g_ContractStart', $g_ContractStart );
				$this->assign ( 'g_ContractEnd', $g_ContractEnd );
				$this->assign ( 'g_DivRate', $g_DivRate );
				$this->assign ( 'g_Note2', $g_Note2 );

				$this->assign ( 'ContractValue', $ContractValue );
				$this->assign ( 'ActualityValue', $ActualityValue );
				$this->assign ( 'sum1', $sum1 );
				$this->assign ( 'sum2', $sum2 );
				$this->assign ( 'code_sum1', $code_sum1 );
				$this->assign ( 'code_sum2', $code_sum2 );
				$this->assign ( 'tot1', $tot1 );
				$this->assign ( 'tot2', $tot2 );

				$this->assign ( 'g_ProjectCode', $g_ProjectCode );
				$this->assign ( 'g_ChangeDate', $g_ChangeDate );
				$this->assign ( 'g_ChangeItem', $g_ChangeItem );
				
				$this->assign ( 'g_ChangeBefore', $g_ChangeBefore );
				$this->assign ( 'g_ChangeBefore2', $g_strJoin22 );
				
				
				

				$this->assign ( 'g_ChangeAfter', $g_ChangeAfter );

				$this->assign ( 'g_strJoinYn', $g_strJoinYn );
				$this->assign ( 'g_strJoin', $g_strJoin2 );


				$this->assign ( 'g_ChangePrice', $g_ChangePrice );
				$this->assign ( 'g_ChangePrice2', $g_ChangePrice2 );

				$this->assign ( 'result_num_row', $result_num_row );

		if ($sub_index == "1") {
			$this->display ( "intranet/common_contents/work_business/business_contract_mvc11.tpl" );
		} else if ($sub_index == "2" or $sub_index == "3") {
			$this->display ( "intranet/common_contents/work_business/business_contract_mvc22.tpl" );
		}
	}
	
	
	
}
?>
