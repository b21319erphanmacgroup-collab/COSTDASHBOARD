<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	/***************************************
	* 수주 및 매출, 수금현황
	* ------------------------------------
	* 2014-01-14 : 파일정리: JYJ
	****************************************/ 

	//include "../../sys/inc/dbcon.inc";
	include "../../sys/inc/dbcon_hm.inc";
	include "../../sys/inc/function_intranet.php";
	include "../util/HanamcPageControl.php";
	include "../../../SmartyConfig.php";
	include "../../sys/inc/function_project_v2.php";
	extract($_GET);
	require_once($SmartyClassPath);

	class BusinessResult extends Smarty {
		
	
		function BusinessResult()
		{

			global $SmartyClass_TemplateDir;
			global $SmartyClass_CompileDir;
			global $SmartyClass_ConfigDir;
			global $SmartyClass_CacheDir;
			global $ProjectCode,$bridgeno,$n_num,$Item_no,$id,$mode;

			
			$this->Smarty();
			$this->template_dir	=$SmartyClass_TemplateDir;
			$this->compile_dir	=$SmartyClass_CompileDir;
			$this->config_dir	=$SmartyClass_ConfigDir;	

		}

		//============================================================================
		// 수주 및 매출, 수금현황 (총괄) 표시
		//============================================================================		
		function BusinessResultList()
		{
			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$PRINT;

			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘

			$today = $date1."-".$date2."-".$date3;

			if($sel_year == "" || $sel_year == null) { $sel_year = $date1; }
			if($sel_month == "" || $sel_month == null) 
			{
				$sel_month = $date2;
			} 
			else 
			{
				if($sel_month <= 9) { $sel_month = "0".$sel_month; }
			}

			$uyear = date("Y")+1;  /////최대 보이는 년도 
			$UNIT = 100000000; //단위:억만원

			if($tab_index == "") { $tab_index = "2"; }
			if($sub_index == "") { $sub_index = "1"; }
			$ana_option=$sub_index;
			$tab_Titel2 = array('총괄','신규','계약금액변경','물가변동');
			$tab_value2 = array('1','2','3','4');

			if($_SESSION['auth_ceo'])//임원
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);
			

			if($_SESSION['auth_depart'])//부서장
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);
			

			$uyear = date("Y")+1; 
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;
			
			
			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");

			$this->assign('today',$today);
			$this->assign('uyear',$uyear);
			$this->assign('tab_Titel2',$tab_Titel2);
			$this->assign('tab_value2',$tab_value2);
			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);

	
//-----------------------------------------------------------------------------------------------------------------------------------------------------//
//-----1. 수주/매출/수금 현황--------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------------------//

			$Contractpayment=0;
			$CollectionPayment=0;

			$Contractpayment2=0;
			$CollectionPayment2=0;


			$start_serch=$sel_year."-01-01";
			
			//월을 선택하는것 (안보이게처리) 무조건 조회하는 달로 조회
			//해가 다르면 모든년 검색
			if($date1 <> $sel_year) //같은년도 아니면 모두
			{
				$end_serch=$sel_year."-12-31";
			}else
			{
				$end_serch=$sel_year."-".$sel_month."-31";
			}
			$start_serch2=$sel_year."-".$sel_month."-01";
	
			$end_serch2=$sel_year."-12-31";
			//수주/매출 목표
			$Contractpayment=0;
			$CollectionPayment=0;
			$sql="select sum(Contractpayment) Contractpayment,sum(CollectionPayment) CollectionPayment from project_targetmoney_tbl where Month like '$sel_year%'"; 

			$clist = mysql_query($sql,$db);
			$left_num = mysql_num_rows($clist);
			if ($left_num >0)
			{
				while($result = mysql_fetch_array($clist))
				{
					$Contractpayment=$result[Contractpayment]/$UNIT;
					$CollectionPayment=$result[CollectionPayment]/$UNIT;
				}
			}

			//수주/매출 추진중목표
			$Contractpayment2=0;
			$CollectionPayment2=0;
			$sql="select sum(Contractpayment) Contractpayment2,sum(CollectionPayment) CollectionPayment2 from project_targetmoney_tbl where (Month > '$start_serch2' and Month < '$end_serch2')"; 
			
			$clist = mysql_query($sql,$db);
			$left_num = mysql_num_rows($clist);
			if ($left_num >0)
			{
				while($result = mysql_fetch_array($clist))
				{
					$Contractpayment2=$result[Contractpayment2]/$UNIT;
					$CollectionPayment2=$result[CollectionPayment2]/$UNIT;
				}
			}


			//수주 실적
			
			//$sql="select * from project_tbl where (ContractDate >= '$start_serch' and ContractDate <= '$end_serch') and ContractPayment > '0' "; 
			$sql="select * from project_tbl where ContractDate like '$sel_year%'";
			
			$clist = mysql_query($sql,$db);
			while($result = mysql_fetch_array($clist)){
				//$g_ContractPayment = $result[OrgContractPayment];
				//부가세별도인것 처리
				$g_ContractPayment = $result[Payment];
				$g_ContractRatio = $result[ContractRatio];
				$g_ActualityRatio = $result[ActualityRatio];
				if($g_ActualityRatio <= 0) $g_ActualityRatio = 100;
				if($g_ActualityRatio > 0 and $g_ActualityRatio < 100) {
					$intotal = $intotal + ($g_ContractPayment * $g_ActualityRatio / 100);
				} else {
					$intotal = $intotal + $g_ContractPayment;
				}
			}
		//		$intotal=$intotal/$UNIT;


			//총괄수주실적은 더해야 한다
			$azSQL = "select * from change_list_tbl where ChangeItem like '%계약금액%' and ChangeDate like '$sel_year%'";
			
			$azRecord = mysql_query($azSQL,$db);
			while($result_record = mysql_fetch_array($azRecord)) {
			$g_ChangeBefore = str_replace(",","",$result_record[ChangeBefore]);
			$g_ChangeAfter = str_replace(",","",$result_record[ChangeAfter]);

			$g_ChangePrice = ($g_ChangeAfter-$g_ChangeBefore);
			$change_SUM = $change_SUM + $g_ChangePrice;
			}

			$change_SUM = $change_SUM/1.1;
			$intotal=$intotal+$change_SUM;
			$intotal=$intotal/$UNIT;

			$intotal=sprintf("%.1f",round($intotal,2));

			if($intotal <> 0 && $Contractpayment <> 0)
			{		
				$intotal_per=sprintf("%.1f",($intotal/$Contractpayment)*100);
			}
			else 
			{
				$intotal_per="0";
			}
			
			//매출 실적
			$sql1="select sum(CollectionPayment) total from collectionpayment_tbl where (DemandDate >= '$start_serch' and DemandDate <= '$end_serch') and CollectionPayment > '0' "; 
			
			$clist1 = mysql_query($sql1,$db);
			while($result1 = mysql_fetch_array($clist1))
			{
				$outtotal=$result1[total]/$UNIT;
			}	

			//부가세별도
			$outtotal=$outtotal/1.1;
			$outtotal=sprintf("%.1f",round($outtotal,2));

			$outtotal=sprintf("%.1f",round($outtotal,2));

			if($outtotal <> 0 && $CollectionPayment <> 0)
			{		
				$outtotal_per=sprintf("%.1f",($outtotal/$CollectionPayment)*100);
			}
			else 
			{
				$outtotal_per="0";
			}

			//수금 실적
			$sql="select sum(CollectionPayment) total from collectionpayment_tbl where CollectionDate like '$sel_year%'"; 
			
			$clist = mysql_query($sql,$db);
			while($result = mysql_fetch_array($clist)){
				$CollectionPaymentsum = $result[total];
			}
			$CollectionPaymentsum=$CollectionPaymentsum/$UNIT;
			$CollectionPaymentsum=$CollectionPaymentsum/1.1;
			$CollectionPaymentsum=sprintf("%.1f",round($CollectionPaymentsum,2));

			if($CollectionPaymentsum <> 0 && $CollectionPayment <> 0)
			{		
				$CollectionPaymentsum_per=sprintf("%.1f",($CollectionPaymentsum/$CollectionPayment)*100);
			}
			else 
			{
				$CollectionPaymentsum_per="0";
			}

			$this->assign('Contractpayment',$Contractpayment);
			$this->assign('intotal',$intotal);
			$this->assign('intotal_per',$intotal_per);
			$this->assign('CollectionPayment',$CollectionPayment);
			$this->assign('outtotal',$outtotal);
			$this->assign('outtotal_per',$outtotal_per);
			$this->assign('CollectionPaymentsum',$CollectionPaymentsum);
			$this->assign('CollectionPaymentsum_per',$CollectionPaymentsum_per);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);


//-----------------------------------------------------------------------------------------------------------------------------------------------------//
//-----2. 월별 수주 / 매출 / 수금 현황-----------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------------------//

//-----1. 월별 수주---------------------------------------------------------------------------------------------------------------------------------//
 			$query_data1 = array(); 
			$thismonth= date("m");
			if($sel_year == "" || $sel_year == null) { $sel_year = $date1; }
			if($sel_month == "" || $sel_month == null) 
			{
				$sel_month = $date2;
			}
			else 
			{
				//if($sel_month <= 9) { $sel_month = "0".$sel_month; }
			}

			$maxin="0";
			$i="1";
			for($month=1; $month<=12; $month++) {
				$contractSUM=0;
				$change_SUM=0;
				if($month <= 9) { $month = "0".$month; }	
				$serchdate=$sel_year."-".$month;
				$thisdate=$date1."-".$date2;
												 
				$azsql = "select * from project_tbl where ContractDate like '$serchdate%'";
		
					$azRecord = mysql_query($azsql,$db);
					while($result_record = mysql_fetch_array($azRecord)) 
					{
						$g_ContractPayment = $result_record[OrgContractPayment];
						$g_ContractRatio = $result_record[ContractRatio];
						$g_ActualityRatio = $result_record[ActualityRatio];
						if($g_ActualityRatio > 0 and $g_ActualityRatio < 100) 
						{
							//$contractSUM = $contractSUM + ($g_ContractPayment * $g_ContractRatio / 100);
							$contractSUM = $contractSUM + ($g_ContractPayment * $g_ActualityRatio / 100);
						}
						else
						{
							$contractSUM = $contractSUM + $g_ContractPayment;
						}
					}



				$azSQL = "select * from change_list_tbl where ChangeItem like '%계약금액%' and ChangeDate like '$serchdate%'";
	
					$azRecord = mysql_query($azSQL,$db);
					while($result_record = mysql_fetch_array($azRecord))
					{
						$g_ChangeBefore = str_replace(",","",$result_record[ChangeBefore]);
						$g_ChangeAfter = str_replace(",","",$result_record[ChangeAfter]);

						$g_ChangePrice = ($g_ChangeAfter-$g_ChangeBefore);
						$change_SUM = $change_SUM + $g_ChangePrice;
					}


				
				$contract_change_sum=$contractSUM+$change_SUM;


				if ($contract_change_sum =="")
				{
					$contract_change_sum2[$i] = "0";
				}
				else
				{
					$contract_change_sum=$contract_change_sum/1.1;
					$contract_change_sum=$contract_change_sum/$UNIT	;
					$contract_change_sum2[$i] = sprintf("%.1f",round($contract_change_sum,2));
				}

				if($maxin < $contract_change_sum2[$i])
				{
					$maxin=$contract_change_sum2[$i];
				}

				$CCSUMtotal=$CCSUMtotal+$contract_change_sum2[$i];
				$val1=$val1.ceil($contract_change_sum2[$i]).",";

				$CCSUMtotal_sum = sprintf("%.1f",round($CCSUMtotal,2));


//-----2. 월별 매출---------------------------------------------------------------------------------------------------------------------------------//

				if( $thisdate >= $serchdate)
				{
					$sql="select sum(CollectionPayment) total from collectionpayment_tbl where DemandDate like '$serchdate%'"; 
				}
				else
				{	
					$sql="select sum(CollectionPayment) total from project_targetmoney_tbl where Month like'$serchdate%'";
				}

				$clist = mysql_query($sql,$db);
				while($result = mysql_fetch_array($clist))
				{
					//$mintotal=$result[total];
					//부가세별도
					if( $thisdate >= $serchdate)
					{
						$mintotal=$result[total]/1.1;
					}
					else
					{
						$mintotal=$result[total];
					}
				}

				
				if ($mintotal =="")
				{
					$mintotal_sum[$i] = "0";
				}
				else
				{
					$mintotal=$mintotal/$UNIT	;
					$mintotal_sum[$i] = sprintf("%.1f",round($mintotal,2));
				}

				$totalin=$totalin+$mintotal_sum[$i];
				if($maxin < $mintotal_sum[$i])
				{
					$maxin=$mintotal_sum[$i];
				}
								
				$val2=$val2.ceil($mintotal_sum[$i]).",";
							
				$totalin_sum = sprintf("%.1f",round($totalin,2));

//-----3. 월별 수금---------------------------------------------------------------------------------------------------------------------------------//
				
				$sql="select sum(CollectionPayment) total from collectionpayment_tbl where CollectionDate like '$serchdate%'"; 
				
				$clist = mysql_query($sql,$db);
				while($result = mysql_fetch_array($clist))
				{
					//부가세별도
					$mouttotal=$result[total]/1.1;
				}
				if ($mouttotal =="")
				{
					$mouttotal_sum[$i] = "0";
				}
				else
				{
					$mouttotal=$mouttotal/$UNIT;
					$mouttotal_sum[$i] = sprintf("%.1f",round($mouttotal,2));
				}

				if($maxin < $mouttotal_sum[$i])
				{
					$maxin=$mouttotal_sum[$i];
				}
				
				$totalout=$totalout+$mouttotal_sum[$i];
				$val3=$val3.ceil($mouttotal_sum[$i]).",";

				$totalout_sum = sprintf("%.1f",round($totalout,2));

				$something1 = array('category' => urlencode($i.월),'column-1' => urlencode(round($contract_change_sum2[$i])),'column-2' => urlencode(round($mintotal_sum[$i])),'column-3' => urlencode(round($mouttotal_sum[$i])));
				array_push($query_data1,$something1);
	$i++;}

			$this->assign('jsondata1',$jsondata1);
			$this->assign('thismonth',$thismonth);
			$this->assign('contract_change_sum2',$contract_change_sum2);
			$this->assign('CCSUMtotal_sum',$CCSUMtotal_sum);
			$this->assign('mintotal_sum',$mintotal_sum);
			$this->assign('totalin_sum',$totalin_sum);
			$this->assign('mouttotal_sum',$mouttotal_sum);
			$this->assign('totalout_sum',$totalout_sum);

			$jsondata1= urldecode(json_encode($query_data1));

			$this->assign('jsondata1',$jsondata1);

			//=월별/수주/매출/수금 그래프 자바스크립트 include==================================================//
			include "../../../Smarty/templates/intranet/business_graph.tpl";
			//=====================================================================================//

//-----------------------------------------------------------------------------------------------------------------------------------------------------//
//-----3. 수주집계표 (총괄)------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------------------//

			$UNIT = "1000000"; //단위:백만원
			$MAX_CNT = "12";
			$MAX_SUM = "13";
			$SALES_FIELD = "Order1";
			//$LAST_GROUP_CODE_NO = 27;
			$LAST_GROUP_CODE_NO = "212";

				if($ana_option ==1)
				{
					$detail_title = "수주집계표 (총괄)";
				}
				else if($ana_option ==2)
				{
					$detail_title = "수주집계표 (신규)";
				}
				else if($ana_option ==3)
				{
					$detail_title = "수주집계표 (계약금액변경)";
				}
				else if($ana_option ==4)
				{
					$detail_title = "수주집계표 (물가변동)";
				}

//-----1. 설계관련부서(교통,국도,지방,구조,지반,교통,수자,항만,도시,환경,환에,진단)-------------------------------------------------------------------//

			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft1_tot[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					$g="0";
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by orderno";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{
							$g_code = $record2[Code];
							$g_name[$g] = $record2[Name];

							if($g_code >= 100 && $g_code <= 111) //설계(고속,국도,)
							{  

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++)
								{
									$design_name[$g] =$g_name[$g];

									switch ($ana_option) 
									{
										case 1:  //총괄
											$sum[$month]=Contract_SUM_M($g_name[$g], $sel_year, $month) + Change_SUM_M($g_name[$g], $sel_year, $month);
											break;
										case 2:  //신규
											$sum[$month]=Contract_SUM_M($g_name[$g], $sel_year, $month);
											break;
										case 3:  //계약금액변경
											$sum[$month]=Change_SUM_M($g_name[$g], $sel_year, $month);
											break;
										case 4:  //물가변동
											$sum[$month]=ES_SUM_M($g_name[$g], $sel_year, $month);
											break;
									}
									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$g]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
									{
										$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD);  //목표
										if($sum[$MAX_SUM+1] > 0) 
										{
											$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
										}
										else
										{
											$sum[$MAX_SUM+2] = 0; //달성률
										}

									} $sum_quarter=0;
		
									for($month=1; $month<=$MAX_SUM+1; $month++) 
									{
										$soft1_tot[$month] = $soft1_tot[$month] + $sum[$month];
										$contract_sum[$g][$month]=number_format($sum[$month] / $UNIT);
										$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
									}
									$achievement_rate[$g] = number_format($sum[$MAX_SUM+2]);
							}
						$g++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft1_tot[$MAX_SUM+1] > 0) 
				{
					$soft1_tot[$MAX_SUM+2] = round(($soft1_tot[$MAX_SUM] / $soft1_tot[$MAX_SUM+1]) * 100);
				}
					$sum_quartersum=0;

				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft1_tot_sum[$month] = number_format($soft1_tot[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft1_tot[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum =	number_format($soft1_tot[$MAX_SUM+2]);

					$design_name_num=count($design_name)+1;

			$this->assign('detail_title',$detail_title);
			$this->assign('design_name',$design_name);
			$this->assign('design_name_num',$design_name_num);
			$this->assign('contract_sum',$contract_sum);
			$this->assign('achievement_rate',$achievement_rate);
			$this->assign('soft1_tot_sum',$soft1_tot_sum);	
			$this->assign('achievement_rate_sum',$achievement_rate_sum);	
	
//-----2. 감리관련부서(감리, 전기)------------------------------------------------------------------------------------------------------------------//

			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft2_tot[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					if($inspection_name=="")
					{
						$f="0";
					}
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{

							$g_code = $record2[Code];
							$g_name[$f] = $record2[Name];

							if($g_code >= 120 && $g_code <= 130)  //감리 + 시공의 전기포함
							{ 

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++) 
								{
									$inspection_name[$f] =$g_name[$f];

									switch ($ana_option) 
									{
										case 1:  //총괄
											$sum[$month]=Contract_SUM_M($g_name[$f], $sel_year, $month) + Change_SUM_M($g_name[$f], $sel_year, $month);
											break;
										case 2:  //신규
											$sum[$month]=Contract_SUM_M($g_name[$f], $sel_year, $month);
											break;
										case 3:  //계약금액변경
											$sum[$month]=Change_SUM_M($g_name[$f], $sel_year, $month);
											break;
										case 4:  //물가변동
											$sum[$month]=ES_SUM_M($g_name[$f], $sel_year, $month);
											break;
									}

									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$f]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
										{
											$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD); //목표

											if($sum[$MAX_SUM+1] > 0) 
											{
												$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
											}
											else
											{
												$sum[$MAX_SUM+2] = 0; //달성률
											}

										} $sum_quarter=0;
										
										for($month=1; $month<=$MAX_SUM+1; $month++)
										{
											$soft2_tot[$month] = $soft2_tot[$month] + $sum[$month];
											$contract_sum2[$f][$month]=number_format($sum[$month] / $UNIT);
											$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
										}
	
									$achievement_rate2[$f] = number_format($sum[$MAX_SUM+2]);
							}
						$f++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft2_tot[$MAX_SUM+1] > 0)
				{
					$soft2_tot[$MAX_SUM+2] = ($soft2_tot[$MAX_SUM] / $soft2_tot[$MAX_SUM+1]) * 100;
				}

				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft2_tot_sum[$month] = number_format($soft2_tot[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft2_tot[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum2 =	number_format($soft2_tot[$MAX_SUM+2]);

					$inspection_name_num=count($inspection_name)+1;

				$this->assign('inspection_name',$inspection_name);
				$this->assign('inspection_name_num',$inspection_name_num);
				$this->assign('contract_sum2',$contract_sum2);
				$this->assign('achievement_rate2',$achievement_rate2);
				$this->assign('soft2_tot_sum',$soft2_tot_sum);	
				$this->assign('achievement_rate_sum2',$achievement_rate_sum2);	

//-----3. R&D관련부서(연구, 거더, 파일, 제안)------------------------------------------------------------------------------------------------------//

			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft6_tot[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					if($rd_name=="")
					{
						$e="0";
					}
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{

							$g_code = $record2[Code];
							$g_name[$e] = $record2[Name];

							if($g_code >= 140 && $g_code <= 150)   // R&D
							{ 

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++) 
								{
									$rd_name[$e] =$g_name[$e];

									switch ($ana_option) 
									{
										case 1:  //총괄
											$sum[$month]=Contract_SUM_M($g_name[$e], $sel_year, $month) + Change_SUM_M($g_name[$e], $sel_year, $month);
											break;
										case 2:  //신규
											$sum[$month]=Contract_SUM_M($g_name[$e], $sel_year, $month);
											break;
										case 3:  //계약금액변경
											$sum[$month]=Change_SUM_M($g_name[$e], $sel_year, $month);
											break;
										case 4:  //물가변동
											$sum[$month]=ES_SUM_M($g_name[$e], $sel_year, $month);
											break;
									}

									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$e]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
										{
											$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD); //목표

											if($sum[$MAX_SUM+1] > 0) 
											{
												$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
											}
											else
											{
												$sum[$MAX_SUM+2] = 0; //달성률
											}

										} $sum_quarter=0;
										
										for($month=1; $month<=$MAX_SUM+1; $month++)
										{
											$soft6_tot[$month] = $soft6_tot[$month] + $sum[$month];
											$contract_sum3[$e][$month]=number_format($sum[$month] / $UNIT);
											$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
										}
	
									$achievement_rate3[$e] = number_format($sum[$MAX_SUM+2]);
							}
						$e++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft6_tot[$MAX_SUM+1] > 0)
				{
					$soft6_tot[$MAX_SUM+2] = ($soft6_tot[$MAX_SUM] / $soft6_tot[$MAX_SUM+1]) * 100;
				}

				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft6_tot_sum[$month] = number_format($soft6_tot[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft6_tot[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum3 =	number_format($soft6_tot[$MAX_SUM+2]);

					$rd_name_num=count($rd_name)+1;

				$this->assign('rd_name',$rd_name);
				$this->assign('rd_name_num',$rd_name_num);
				$this->assign('contract_sum3',$contract_sum3);
				$this->assign('achievement_rate3',$achievement_rate3);
				$this->assign('soft6_tot_sum',$soft6_tot_sum);	
				$this->assign('achievement_rate_sum3',$achievement_rate_sum3);	

//-----4. 총계----------------------------------------------------------------------------------------------------------------------------------//	

				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++) 
				{
					$soft_tot[$month] = $soft1_tot[$month] + $soft2_tot[$month] + $soft3_tot[$month] + $soft4_tot[$month] + $soft5_tot[$month]+ $soft6_tot[$month]+ $soft7_tot[$month]+ $soft8_tot[$month]+$soft9_tot[$month];
					$soft_tot_sum[$month]=number_format($soft_tot[$month] / $UNIT);

					$sum_quartersum=$sum_quartersum + number_format($soft_tot[$month]/$UNIT, 0,'.','');
					
				}
				if($soft_tot[$MAX_SUM+1] > 0) 
				{
					$soft_tot[$MAX_SUM+2] = round(($soft_tot[$MAX_SUM] / $soft_tot[$MAX_SUM+1]) * 100);
				}
				$soft_tot_sum2=number_format($soft_tot[$MAX_SUM+2]);
				

//-----2011-12-22 유승렬이사 수주는 했는데 미계약 해서 프로젝트 코드 안딴 경우도 수주금액을 표시해달라고 해서 처리함--------------------//	
				$a=1;
				$sum_quartersum=0;

				for($month=1; $month<=$MAX_SUM; $month++) 
				{
					if($month <= 9) { $month = "0".$month; }	
					$serchdate=$sel_year."-".$month;
							
					$sql="select sum(Contractpayment) total from project_no_contract_tbl where Month like '$serchdate%'"; 

						$clist = mysql_query($sql,$db);
						while($result = mysql_fetch_array($clist))
						{
							$mtotal=$result[total];
						}
						if ($mtotal =="")
						{

							$sum_total[$a] = "0";
						}else
						{
							$sum_total[$a] = number_format($mtotal/$UNIT);
						}
							$sum_quartersum2+=($mtotal/$UNIT);
						
				$a++;}

				$this->assign('soft_tot_sum',$soft_tot_sum);
				$this->assign('soft_tot_sum2',$soft_tot_sum2);
				$this->assign('sum_total',$sum_total);
				$this->assign('sum_quartersum2',$sum_quartersum2);


//-----------------------------------------------------------------------------------------------------------------------------------------------------//
//-----3. 메츨집계표-----------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------------------//

				$UNIT = 1000000; //단위:백만원
				$MAX_CNT = 12;
				$MAX_SUM = 13;
				$SALES_FIELD2 = "Sales";
				//$LAST_GROUP_CODE_NO = 27;
				$LAST_GROUP_CODE_NO = 212;

//-----1. 설계관련부서(교통,국도,지방,구조,지반,교통,수자,항만,도시,환경,환에,진단)-------------------------------------------------------------------//

			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft1_tot2[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					$gg="0";
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by orderno";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{
							$g_code = $record2[Code];
							$g_name[$gg] = $record2[Name];

							if($g_code >= 100 && $g_code <= 111) //설계(고속,국도,)
							{  

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++)
								{
									$design_name2[$gg] =$g_name[$gg];

									$sum[$month]=Demand_SUM_M($g_name[$gg], $sel_year, $month);
									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$gg]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
									{
										$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD2);  //목표
										if($sum[$MAX_SUM+1] > 0) 
										{
											$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
										}
										else
										{
											$sum[$MAX_SUM+2] = 0; //달성률
										}

									} $sum_quarter=0;
		
									for($month=1; $month<=$MAX_SUM+1; $month++) 
									{
										$soft1_tot2[$month] = $soft1_tot2[$month] + $sum[$month];
										$bill_sum[$gg][$month]=number_format($sum[$month] / $UNIT);
										$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
									}
									$achievement_rate4[$gg] = number_format($sum[$MAX_SUM+2]);
							}
						$gg++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft1_tot2[$MAX_SUM+1] > 0) 
				{
					$soft1_tot2[$MAX_SUM+2] = round(($soft1_tot2[$MAX_SUM] / $soft1_tot2[$MAX_SUM+1]) * 100);
				}
					$sum_quartersum=0;

				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft1_tot2_sum2[$month] = number_format($soft1_tot2[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft1_tot2[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum4 =	number_format($soft1_tot2[$MAX_SUM+2]);

					$design_name_num2=count($design_name2)+1;

			$this->assign('design_name2',$design_name2);
			$this->assign('design_name_num2',$design_name_num2);
			$this->assign('bill_sum',$bill_sum);
			$this->assign('achievement_rate4',$achievement_rate4);
			$this->assign('soft1_tot2_sum2',$soft1_tot2_sum2);	
			$this->assign('achievement_rate_sum4',$achievement_rate_sum4);	
	


//-----2. 감리관련부서(감리, 전기)------------------------------------------------------------------------------------------------------------------//


			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft2_tot2[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					if($inspection_name2=="")
					{
						$ff="0";
					}
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{

							$g_code = $record2[Code];
							$g_name[$ff] = $record2[Name];

							if($g_code >= 120 && $g_code <= 130)  //감리 + 시공의 전기포함
							{ 

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++) 
								{
									$inspection_name2[$ff] =$g_name[$ff];

									$sum[$month]=Demand_SUM_M($g_name[$ff], $sel_year, $month);
									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ff]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
										{
											$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD2); //목표

											if($sum[$MAX_SUM+1] > 0) 
											{
												$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
											}
											else
											{
												$sum[$MAX_SUM+2] = 0; //달성률
											}

										} $sum_quarter=0;
										
										for($month=1; $month<=$MAX_SUM+1; $month++)
										{
											$soft2_tot2[$month] = $soft2_tot2[$month] + $sum[$month];
											$bill_sum2[$ff][$month]=number_format($sum[$month] / $UNIT);
											$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
										}
	
									$achievement_rate5[$ff] = number_format($sum[$MAX_SUM+2]);
							}
						$ff++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft2_tot2[$MAX_SUM+1] > 0)
				{
					$soft2_tot2[$MAX_SUM+2] = ($soft2_tot2[$MAX_SUM] / $soft2_tot2[$MAX_SUM+1]) * 100;
				}

				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft2_tot2_sum[$month] = number_format($soft2_tot2[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft2_tot2[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum5 =	number_format($soft2_tot2[$MAX_SUM+2]);

					$inspection_name_num2=count($inspection_name2)+1;

				$this->assign('inspection_name2',$inspection_name2);
				$this->assign('inspection_name_num2',$inspection_name_num2);
				$this->assign('bill_sum2',$bill_sum2);
				$this->assign('achievement_rate5',$achievement_rate5);
				$this->assign('soft2_tot2_sum',$soft2_tot2_sum);	
				$this->assign('achievement_rate_sum5',$achievement_rate_sum5);	


//-----3. R&D관련부서(연구, 거더, 파일, 제안)------------------------------------------------------------------------------------------------------//


			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft6_tot2[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					if($rd_name2=="")
					{
						$ee="0";
					}
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{

							$g_code = $record2[Code];
							$g_name[$ee] = $record2[Name];

							if($g_code >= 140 && $g_code <= 150)   // R&D
							{ 

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++) 
								{
									$rd_name2[$ee] =$g_name[$ee];

									$sum[$month]=Demand_SUM_M($g_name[$ee], $sel_year, $month);
									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ee]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
										{
											$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD2); //목표

											if($sum[$MAX_SUM+1] > 0) 
											{
												$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
											}
											else
											{
												$sum[$MAX_SUM+2] = 0; //달성률
											}

										} $sum_quarter=0;
										
										for($month=1; $month<=$MAX_SUM+1; $month++)
										{
											$soft6_tot2[$month] = $soft6_tot2[$month] + $sum[$month];
											$bill_sum3[$ee][$month]=number_format($sum[$month] / $UNIT);
											$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
										}
	
									$achievement_rate6[$ee] = number_format($sum[$MAX_SUM+2]);
							}
						$ee++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft6_tot2[$MAX_SUM+1] > 0)
				{
					$soft6_tot2[$MAX_SUM+2] = ($soft6_tot2[$MAX_SUM] / $soft6_tot2[$MAX_SUM+1]) * 100;
				}

				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft6_tot2_sum[$month] = number_format($soft6_tot2[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft6_tot2[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum6 =	number_format($soft6_tot2[$MAX_SUM+2]);

					$rd_name_num2=count($rd_name2)+1;

				$this->assign('rd_name2',$rd_name2);
				$this->assign('rd_name_num2',$rd_name_num2);
				$this->assign('bill_sum3',$bill_sum3);
				$this->assign('achievement_rate6',$achievement_rate6);
				$this->assign('soft6_tot2_sum',$soft6_tot2_sum);	
				$this->assign('achievement_rate_sum6',$achievement_rate_sum6);	


//-----4. 총계----------------------------------------------------------------------------------------------------------------------------------//	
				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++) 
				{
					$soft_tot2[$month] = $soft1_tot2[$month] + $soft2_tot2[$month] + $soft3_tot2[$month] + $soft4_tot2[$month] + $soft5_tot2[$month]+ $soft6_tot2[$month]+ $soft7_tot2[$month]+ $soft8_tot2[$month]+$soft9_tot2[$month];
					$soft_tot_sum3[$month]=number_format($soft_tot2[$month] / $UNIT);

					$sum_quartersum=$sum_quartersum + number_format($soft_tot2[$month]/$UNIT, 0,'.','');
					
				}
				if($soft_tot2[$MAX_SUM+1] > 0) 
				{
					$soft_tot2[$MAX_SUM+2] = round(($soft_tot2[$MAX_SUM] / $soft_tot2[$MAX_SUM+1]) * 100);
				}
				$soft_tot_sum4=number_format($soft_tot2[$MAX_SUM+2]);
				
				$this->assign('soft_tot_sum3',$soft_tot_sum3);
				$this->assign('soft_tot_sum4',$soft_tot_sum4);

//-----------------------------------------------------------------------------------------------------------------------------------------------------//
//-----4. 수금집계표---------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------------------//

			$UNIT = 1000000; //단위:백만원
			$MAX_CNT = 12;
			$MAX_SUM = 13; 
			//$LAST_GROUP_CODE_NO = 27;
			$LAST_GROUP_CODE_NO = 212;

//-----1. 설계관련부서(교통,국도,지방,구조,지반,교통,수자,항만,도시,환경,환에,진단)-------------------------------------------------------------------//

			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft1_tot3[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					$gg="0";
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by orderno";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{
							$g_code = $record2[Code];
							$g_name[$gg] = $record2[Name];

							if($g_code >= 100 && $g_code <= 111) //설계(고속,국도,)
							{  

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++)
								{
									$design_name3[$gg] =$g_name[$gg];

									$sum[$month]=Collection_SUM_M($g_name[$gg], $sel_year, $month);
									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$gg]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
									{
										$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD2);  //목표
										if($sum[$MAX_SUM+1] > 0) 
										{
											$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
										}
										else
										{
											$sum[$MAX_SUM+2] = 0; //달성률
										}

									} $sum_quarter=0;
		
									for($month=1; $month<=$MAX_SUM+1; $month++) 
									{
										$soft1_tot3[$month] = $soft1_tot3[$month] + $sum[$month];
										$collection_sum[$gg][$month]=number_format($sum[$month] / $UNIT);
										$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
									}
									$achievement_rate7[$gg] = number_format($sum[$MAX_SUM+2]);
							}
						$gg++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft1_tot3[$MAX_SUM+1] > 0) 
				{
					$soft1_tot3[$MAX_SUM+2] = round(($soft1_tot3[$MAX_SUM] / $soft1_tot3[$MAX_SUM+1]) * 100);
				}
					$sum_quartersum=0;

				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft1_tot3_sum2[$month] = number_format($soft1_tot3[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft1_tot3[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum7 =	number_format($soft1_tot3[$MAX_SUM+2]);

					$design_name_num3=count($design_name3)+1;

			$this->assign('design_name3',$design_name3);
			$this->assign('design_name_num3',$design_name_num3);
			$this->assign('collection_sum',$collection_sum);
			$this->assign('achievement_rate7',$achievement_rate7);
			$this->assign('soft1_tot3_sum2',$soft1_tot3_sum2);	
			$this->assign('achievement_rate_sum7',$achievement_rate_sum7);	
	


//-----2. 감리관련부서(감리, 전기)------------------------------------------------------------------------------------------------------------------//


			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft2_tot3[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					if($inspection_name3=="")
					{
						$ff="0";
					}
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{

							$g_code = $record2[Code];
							$g_name[$ff] = $record2[Name];

							if($g_code >= 120 && $g_code <= 130)  //감리 + 시공의 전기포함
							{ 

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++) 
								{
									$inspection_name3[$ff] =$g_name[$ff];

									$sum[$month]=Collection_SUM_M($g_name[$ff], $sel_year, $month);
									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ff]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
										{
											$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD2); //목표

											if($sum[$MAX_SUM+1] > 0) 
											{
												$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
											}
											else
											{
												$sum[$MAX_SUM+2] = 0; //달성률
											}

										} $sum_quarter=0;
										
										for($month=1; $month<=$MAX_SUM+1; $month++)
										{
											$soft2_tot3[$month] = $soft2_tot3[$month] + $sum[$month];
											$collection_sum2[$ff][$month]=number_format($sum[$month] / $UNIT);
											$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
										}
	
									$achievement_rate8[$ff] = number_format($sum[$MAX_SUM+2]);
							}
						$ff++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft2_tot3[$MAX_SUM+1] > 0)
				{
					$soft2_tot3[$MAX_SUM+2] = ($soft2_tot3[$MAX_SUM] / $soft2_tot3[$MAX_SUM+1]) * 100;
				}

				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft2_tot3_sum[$month] = number_format($soft2_tot3[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft2_tot3[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum8 =	number_format($soft2_tot3[$MAX_SUM+2]);

					$inspection_name_num3=count($inspection_name3)+1;

				$this->assign('inspection_name3',$inspection_name3);
				$this->assign('inspection_name_num3',$inspection_name_num3);
				$this->assign('collection_sum2',$collection_sum2);
				$this->assign('achievement_rate8',$achievement_rate8);
				$this->assign('soft2_tot3_sum',$soft2_tot3_sum);	
				$this->assign('achievement_rate_sum8',$achievement_rate_sum8);	


//-----3. R&D관련부서(연구, 거더, 파일, 제안)------------------------------------------------------------------------------------------------------//


			for($month=1; $month<=$MAX_SUM+2; $month++) //$month = 13 합계
			{  
				$soft6_tot3[$month] = 0;
			}

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
				$recordBlock1 = mysql_query($azSQL,$db);
				while($record1 = mysql_fetch_array($recordBlock1)) 
				{
					$group_code = $record1[Code];
					$group_name = $record1[Name];

					$i = $group_code;
					$n_color = $group_code;
					
					if($rd_name3=="")
					{
						$ee="0";
					}
					$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' order by code";

						$recordBlock2 = mysql_query($azSQL,$db);
						while($record2 = mysql_fetch_array($recordBlock2)) 
						{

							$g_code = $record2[Code];
							$g_name[$ee] = $record2[Name];

							if($g_code >= 140 && $g_code <= 150)   // R&D
							{ 

								$sum[$MAX_SUM] = 0;
								for($month=1; $month<=$MAX_CNT; $month++) 
								{
									$rd_name3[$ee] =$g_name[$ee];

									$sum[$month]=Collection_SUM_M($g_name[$ee], $sel_year, $month);
									$sum[$MAX_SUM] = $sum[$MAX_SUM] + $sum[$month];
								}

								$azSQL = "select * from sale_target_tbl where TargetYear = '$sel_year' and ProjectPart = '$g_name[$ee]'";
									$rec_target = mysql_query($azSQL,$db);
									$sum[$MAX_SUM+1] = 0;
									$sum[$MAX_SUM+2] = 0;
									if(mysql_num_rows($rec_target) > 0) 
										{
											$sum[$MAX_SUM+1] = mysql_result($rec_target,0,$SALES_FIELD2); //목표

											if($sum[$MAX_SUM+1] > 0) 
											{
												$sum[$MAX_SUM+2] = round(($sum[$MAX_SUM] / $sum[$MAX_SUM+1] * 100.0)); //달성률
											}
											else
											{
												$sum[$MAX_SUM+2] = 0; //달성률
											}

										} $sum_quarter=0;
										
										for($month=1; $month<=$MAX_SUM+1; $month++)
										{
											$soft6_tot3[$month] = $soft6_tot3[$month] + $sum[$month];
											$collection_sum3[$ee][$month]=number_format($sum[$month] / $UNIT);
											$sum_quarter=$sum_quarter + number_format($sum[$month]/$UNIT, 0,'.','');
										}
	
									$achievement_rate9[$ee] = number_format($sum[$MAX_SUM+2]);
							}
						$ee++;} //while($record2 = mysql_fetch_array($recordBlock2))
				} //while($record1 = mysql_fetch_array($recordBlock1))

				if($soft6_tot3[$MAX_SUM+1] > 0)
				{
					$soft6_tot3[$MAX_SUM+2] = ($soft6_tot3[$MAX_SUM] / $soft6_tot3[$MAX_SUM+1]) * 100;
				}

				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++)
				{
					$soft6_tot3_sum[$month] = number_format($soft6_tot3[$month] / $UNIT);
					$sum_quartersum=$sum_quartersum + number_format($soft6_tot3[$month]/$UNIT, 0,'.','');
				}
					$achievement_rate_sum9 =	number_format($soft6_tot3[$MAX_SUM+2]);

					$rd_name_num3=count($rd_name3)+1;

				$this->assign('rd_name3',$rd_name3);
				$this->assign('rd_name_num3',$rd_name_num3);
				$this->assign('collection_sum3',$collection_sum3);
				$this->assign('achievement_rate9',$achievement_rate9);
				$this->assign('soft6_tot3_sum',$soft6_tot3_sum);	
				$this->assign('achievement_rate_sum9',$achievement_rate_sum9);	


//-----4. 총계----------------------------------------------------------------------------------------------------------------------------------//	
				$sum_quartersum=0;
				for($month=1; $month<=$MAX_SUM+1; $month++) 
				{
					$soft_tot3[$month] = $soft1_tot3[$month] + $soft2_tot3[$month] + $soft3_tot3[$month] + $soft4_tot3[$month] + $soft5_tot3[$month]+ $soft6_tot3[$month]+ $soft7_tot3[$month]+ $soft8_tot3[$month]+$soft9_tot3[$month];
					$soft_tot_sum5[$month]=number_format($soft_tot3[$month] / $UNIT);

					$sum_quartersum=$sum_quartersum + number_format($soft_tot3[$month]/$UNIT, 0,'.','');
					
				}
				if($soft_tot3[$MAX_SUM+1] > 0) 
				{
					$soft_tot3[$MAX_SUM+2] = round(($soft_tot3[$MAX_SUM] / $soft_tot3[$MAX_SUM+1]) * 100);
				}
				$soft_tot_sum6=number_format($soft_tot3[$MAX_SUM+2]);
				
				$this->assign('soft_tot_sum5',$soft_tot_sum5);
				$this->assign('soft_tot_sum6',$soft_tot_sum6);
				$this->assign('PRINT',$PRINT);	

				$this->display("intranet/common_contents/work_business/business_result_mvc.tpl");

				//프린트 페이지 자동 미리보기 시간 설정===============================
				if($PRINT=="YES")
				{
					$this->assign('mode',"print");
					$this->display("intranet/js_page.tpl");
				}
				//프린트 페이지 자동 미리보기 시간 설정 끝=============================
			}

//-----------------------------------------------------------------------------------------------------------------------------------------------------//
//-----수주 현황 상세 ==----------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------------------//

		function ContractReportLogic()
		{
			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$sub_index;

//-----------------------------------------------------------------------------------------------------------------------------------------------------//
//-----1. 기본정보 LOGIC------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------------------//

			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘

			$DETAIL_OP1 = "계약금액";
			$DETAIL_OP2 = "물가변동";

			$LAST_GROUP_CODE_NO = 212;

			$today = $date1."-".$date2."-".$date3;

			if($sel_year == "" || $sel_year == null) { $sel_year = $date1; }
			if($sel_month == "" || $sel_month == null) 
			{
				$sel_month = $date2;
			} 
			else 
			{
				if($sel_month <= 9) { $sel_month = "0".$sel_month; }
			}

			$uyear = date("Y")+1;  /////최대 보이는 년도 
			$UNIT = 100000000; //단위:억만원

			if($tab_index == "") { $tab_index = "2"; }
			if($sub_index == "") { $sub_index = "1"; }
			$ana_option=$sub_index;
			$tab_Titel2 = array('신규','계약금액변경','물가변동');
			$tab_value2 = array('1','2','3');

			if($sub_index == "1") { $contract_title = "수 주 현 황 [신규]"; }
			else if($sub_index == "2") { $contract_title = "수 주 현 황[계약변경]"; }
			else if($sub_index == "3") { $contract_title = "수 주 현 황[물가변동]"; }


			if($_SESSION['auth_ceo'])//임원
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);
			

			if($_SESSION['auth_depart'])//부서장
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);
			

			$uyear = date("Y")+1; 
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;
			
			//$sel_year=2014;
			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");


			$this->assign('contract_title',$contract_title);
			$this->assign('uyear',$uyear);
			$this->assign('tab_Titel2',$tab_Titel2);
			$this->assign('tab_value2',$tab_value2);
			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);
		

			$k="0";

			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName in('01','02','03','05') order by code";
				echo $azSQL."<br>";
				$recordBlock4 = mysql_query($azSQL,$db);
				$g_code_num = mysql_num_rows($recordBlock4);
				while($record4 = mysql_fetch_array($recordBlock4)) 
				{
					$g_code= $record4[Code];
					$g_name = $record4[Name];
					$g_Note = $record4[Note];

echo $g_code_num."============<br>";

						for($i=1;$i <=4; $i++)
						{
	
							$StDate = $sel_year."-01-01";
							$EdDate = $sel_year."-04-01";
							if($i == 2) 
							{
								$StDate = $sel_year."-04-01";
								$EdDate = $sel_year."-07-01";
							}
							else if($i == 3) 
							{
								$StDate = $sel_year."-07-01";
								$EdDate = $sel_year."-10-01";
							}
							else if($i == 4) 
							{
								$StDate = $sel_year."-10-01";
								$EdDate = ($sel_year+1)."-01-01";
							}

						$azSQL = "SELECT * FROM group_div_tbl A, project_tbl B WHERE A.ProjectCode = B.projectCode and DivCodeName = '$g_name' and ContractDate >= '$StDate' and ContractDate < '$EdDate'";
						ECHO $azSQL."<BR>";
							$recordBlock5 = mysql_query($azSQL,$db);
							$result_num_row=mysql_num_rows($recordBlock5);

							if(mysql_num_rows($recordBlock5) > 0) 
							{
								while($record5 = mysql_fetch_array($recordBlock5)) 
								{
									//$g_ContractPayment = $record5[OrgContractPayment]; //최초 계약 금액 적용
									//부가세별도인것으로 처리 (Payment) 필드가 부가세별도금액임
									$g_ContractPayment[$k][$i] = $record5[Payment]; //최초 계약 금액 적용
									$g_ContractRatio[$k][$i] = $record5[ContractRatio];
									$g_ActualityRatio[$k][$i] = $record5[ActualityRatio];

									$g_ProjectNickname[$k][$i] = $record5[ProjectNickname];
				echo $g_ProjectNickname[$k][$i]."<br>";
									$g_OrderNickname[$k][$i] = $record5[OrderNickname];
									$g_ContractStart[$k][$i] = $record5[ContractStart];
									$g_ContractEnd[$k][$i] = $record5[ContractEnd];
									$g_DivRate[$k][$i] = $record5[DivRate];               //부서별 실지분율
								}
							}
				echo $i."<br>";		}
				
echo $k."<br>";
				$k++;}
				
	


							$arrSum = array();

							$query = "select SUM(Payment) tmpSum, DATE_FORMAT(ContractDate, '%m') tmpDate  from project_tbl where DATE_FORMAT(ContractDate, '%Y') = '$sel_year' GROUP BY DATE_FORMAT(ContractDate, '%Y-%m')";

								$result = mysql_query($query);
								while($dataRow = mysql_fetch_array($result)) 
								{
									switch($dataRow[tmpDate]) 
									{
										case '01':
										case '02':
										case '03':
										 $arrSum['1분기'] += $dataRow[tmpSum];
										 break;

									   case '04':
										case '05':
										case '06':
										 $arrSum['2분기'] += $dataRow[tmpSum];
										 break;

									   case '07':
										case '08':
										case '09':
										 $arrSum['3분기'] += $dataRow[tmpSum];
										 break;

									   case '10':
										case '11':
										case '12':
										 $arrSum['4분기'] += $dataRow[tmpSum];
										 break;
									}
								}

							 // 결과값 배열 출력
							 echo "<pre>";
							 print_r($arrSum); 
							 echo "</pre>";
							  
							  // 사용법
							 echo $arrSum['1분기'];
				$g_code_row=$g_code_num+1;

			$this->assign('today',$today);
			$this->assign('uyear',$uyear);
			$this->assign('tab_Titel2',$tab_Titel2);
			$this->assign('tab_value2',$tab_value2);
			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);

			$this->assign('g_code_row',$g_code_row);

			$this->assign('result_num_row',$result_num_row);
			$this->assign('group_code_num',$group_code_num);
			$this->assign('g_code_num',$g_code_num);
			$this->assign('g_code',$g_code);
			$this->assign('g_name',$g_name);
			$this->assign('g_Note',$g_Note);
			$this->assign('g_ProjectNickname',$g_ProjectNickname);
			$this->assign('tit',$tit);
			$this->display("intranet/common_contents/work_business/business_contract_mvc.tpl");

		}

		//============================================================================
		// 미계약 수주현황 보기창
		//============================================================================		
		function NoncontractViewLogic()
		{
			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$PRINT;

			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘

			$today = $date1."-".$date2."-".$date3;

			if($sel_year == "" || $sel_year == null) { $sel_year = $date1; }
			if($sel_month == "" || $sel_month == null) 
			{
				$sel_month = $date2;
			} 
			else 
			{
				if($sel_month <= 9) { $sel_month = "0".$sel_month; }
			}

			$uyear = date("Y")+1;  /////최대 보이는 년도 
			$UNIT = 100000000; //단위:억만원


			if($tab_index == "") { $tab_index = "2"; }
			if($sub_index == "") { $sub_index = "1"; }
			$ana_option=$sub_index;
			$tab_Titel2 = array('총괄','신규','계약금액변경','물가변동');
			$tab_value2 = array('1','2','3','4');

			if($_SESSION['auth_ceo'])//임원
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);
			

			if($_SESSION['auth_depart'])//부서장
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);
			

			$uyear = date("Y")+1; 
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;
			
			
			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");

		$serchdate=$sel_year."-".$sel_month;

		$NonContract_List = array(); 	

		$sql="select * from project_no_contract_tbl where Month like '$serchdate%'"; 
			$clist = mysql_query($sql,$db);
			$i=1;
			$total=0;
			while($row_clist = mysql_fetch_array($clist)) 
			{
				array_push($NonContract_List,$row_clist);

				$Contractpayment=$row_clist[Contractpayment];
				$Contractpayment_sum+=$Contractpayment;
			}

			$this->assign('today',$today);
			$this->assign('uyear',$uyear);
			$this->assign('tab_Titel2',$tab_Titel2);
			$this->assign('tab_value2',$tab_value2);
			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);
			$this->assign('NonContract_List',$NonContract_List);
			$this->assign('Contractpayment_sum',$Contractpayment_sum);
			$this->display("intranet/common_contents/work_business/business_Noncontract_mvc.tpl");
		}

		//============================================================================
		// 미계약 수주현황 편집창
		//============================================================================		
		function NoncontractInputLogic()
		{
			global $db;
			global $mode,$no,$sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$PRINT;

			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘

			$today = $date1."-".$date2."-".$date3;

			if($sel_year == "" || $sel_year == null) { $sel_year = $date1; }
			if($sel_month == "" || $sel_month == null) 
			{
				$sel_month = $date2;
			} 
			else 
			{
				if($sel_month <= 9) { $sel_month = "0".$sel_month; }
			}

			$uyear = date("Y")+1;  /////최대 보이는 년도 
			$UNIT = 100000000; //단위:억만원


			if($tab_index == "") { $tab_index = "2"; }
			if($sub_index == "") { $sub_index = "1"; }
			$ana_option=$sub_index;
			$tab_Titel2 = array('총괄','신규','계약금액변경','물가변동');
			$tab_value2 = array('1','2','3','4');

			if($_SESSION['auth_ceo'])//임원
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);
			

			if($_SESSION['auth_depart'])//부서장
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);
			

			$uyear = date("Y")+1; 
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;
			
			
			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");

		$serchdate=$sel_year."-".$sel_month;

		$sql="select * from project_no_contract_tbl where no = '$no'"; 
			$clist = mysql_query($sql,$db);
			while($row_clist = mysql_fetch_array($clist)) 
			{
				$ProjectName=$row_clist[ProjectName];
				$Month=$row_clist[Month];
				$Contractpayment=$row_clist[Contractpayment];
				$Note=$row_clist[Note];
			}



			$this->assign('mode',$mode);
			$this->assign('today',$today);
			$this->assign('uyear',$uyear);
			$this->assign('tab_Titel2',$tab_Titel2);
			$this->assign('tab_value2',$tab_value2);
			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);
			$this->assign('no',$no);
			$this->assign('ProjectName',$ProjectName);
			$this->assign('Month',$Month);
			$this->assign('Contractpayment',$Contractpayment);
			$this->assign('Note',$Note);
			$this->display("intranet/common_contents/work_business/business_Noncontract_input_mvc.tpl");
		}

		//============================================================================
		// 미계약 수주현황 편집 (입력 / 수정 / 삭제)
		//============================================================================		
		function NoncontractActionLogic()
		{
			global $db;
			global $ProjectName,$Month,$Contractpayment,$Note,$no;
			global $mode,$no,$sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$PRINT;


			$Month=$sel_year."-".$sel_month."-01";

			if($mode == "mod")//수정시 
			{ 
				$sql="update project_no_contract_tbl set ProjectName='$ProjectName', Month='$Month', Contractpayment='$Contractpayment',Note='$Note' where no='$no'";
			}
			else if ($mode == "del")//삭제시
			{ 
				$sql="delete from project_no_contract_tbl where no='$no'";
			}
			else//추가시
			{
				$sql = "insert into project_no_contract_tbl (ProjectName, Month, Contractpayment,Note) values";
				$sql = $sql ."('$ProjectName','$Month','$Contractpayment','$Note')";
			} 
			
				mysql_query($sql,$db);

			$this->assign('target',"opener");
			$this->assign('MoveURL',"businessresult_controller.php?ActionMode=Noncontract_view");
			$this->display("intranet/move_page.tpl");
		}

		//============================================================================
		// 수주/매출 목표 입력
		//============================================================================		
		function ConsaleGoalInputLogic()
		{
			global $db;
			global $mode,$no,$sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$PRINT;

			if($ana_year == "") $ana_year = date("Y");
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘

			$today = $ana_year."-".$date2."-".$date3;			

			$azSQL = "select * from sale_target_tbl where TargetYear='$ana_year'";
				$rec_target = mysql_query($azSQL,$db);
				$target_count = mysql_num_rows($rec_target);

			if($target_count>"0"){$mode="mod";}
			else{$mode="add";}

			if($mode == "add")
			{
				$Group_List = array(); 	

				$azSQL = "select *,a.Name as Name1 from 
				(
					select * from systemconfig_tbl where SysKey = 'ProjectGroup' and Code in('01','02','03','05')
				)a inner join
				(
					select * from systemconfig_tbl where SysKey = 'ProjectCode'
				)b on a.Code = b.CodeORName order by b.orderno";

					$rec_target = mysql_query($azSQL,$db);
					while($record3 = mysql_fetch_array($rec_target)) 
					{
						array_push($Group_List,$record3);
					}

				$this->assign('Group_List',$Group_List);
				$this->assign('mode',$mode);

			}
			if($mode == "mod")
			{
				$ConsaleGoal_List = array(); 	

				$azSQL = "select * from 
				(
					select * from systemconfig_tbl where SysKey = 'projectcode' and Code<'150' and Code<>'131'
				)a inner join
				(
					select * from sale_target_tbl  where TargetYear='$ana_year'
				)b on a.Name = b.ProjectPart order by orderno";

					$rec_target = mysql_query($azSQL,$db);
					while($record4 = mysql_fetch_array($rec_target)) 
					{
						array_push($ConsaleGoal_List,$record4);
					}
				$this->assign('mode',$mode);
				$this->assign('ConsaleGoal_List',$ConsaleGoal_List);
			}

				$this->assign('ana_year',$ana_year);
			$this->display("intranet/common_contents/work_business/business_consalegoal_mvc.tpl");
		}

		//============================================================================
		// 수주/매출 목표 저장
		//============================================================================		
		function ConsaleGoalUpdateLogic()
		{
			global $db;
			global $mode,$ana_year;
			global $Group_num,$ConsaleGoal_num,$ProjectPart,$Order1,$Sales;

			$indel="delete from sale_target_tbl where TargetYear='$ana_year'";

			mysql_query($indel,$db);

			$delsql="delete from project_targetmoney_tbl where Month like '$ana_year%'";
			
			mysql_query($delsql,$db);


			if($mode=="add")
			{
				$tCnt=$Group_num;
			}
			else if($mode=="mod")
			{
				$tCnt=$ConsaleGoal_num;
			}

			for($m=1;$m <= $tCnt; $m++)
			{
				$aa=str_replace(',','',$Order1[$m]);
				if($aa == "") $aa=0;
				$aa=$aa*1000000;

				$aasum=$aasum+$aa;

				$bb=str_replace(',','',$Sales[$m]);
				if($bb == "") $bb=0;
				$bb=$bb*1000000;

				$bbsum=$bbsum+$bb;

				$insql="insert into sale_target_tbl (TargetYear, ProjectPart, Order1, Sales) values('$ana_year', '$ProjectPart[$m]', $aa, $bb)";
				
				mysql_query($insql,$db);
	
			}

				$ana_year2=$ana_year."-01-01";
				$insql2="insert into project_targetmoney_tbl (Month, Contractpayment, CollectionPayment) values('$ana_year2', '$aasum', $bbsum)";
				
				mysql_query($insql2,$db);

			$this->assign('target',"self");
			$this->assign('MoveURL',"businessresult_controller.php?ActionMode=Consalegoal_input");
			$this->display("intranet/move_page.tpl");
		}
	}
?>
