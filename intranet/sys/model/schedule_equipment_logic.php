<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
	
	/***************************************
	* 배차현황
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/ 

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../inc/function_intranet.php";
	include "../util/HanamcPageControl.php";

	if($start_y<>"" and $start_m<>"")
	{
		$pday=$start_y."-".$start_m."-".$date3;
	}

	if($pday=="")
	{
		$today=date("Y-m-d");
	}
	else
	{
		$today=$pday;
	}


	extract($_GET);
	class ScheduleEquipmentLogic {
		var $smarty;
		function ScheduleEquipmentLogic($smarty)
		{
			$this->smarty=$smarty;
		}


		//============================================================================
		// 배차신청 작성 logic
		//============================================================================	
		function InsertPage()
		{
			global $db;
			global $mode, $memberID, $today;

			$equipmentList = array(); 

			$sql = "select * from systemconfig_tbl where SysKey='bizdevice' and Code<>'10' and Code<>'11' order by Code";
				$re = mysql_query($sql,$db);
				while($re_row = mysql_fetch_array($re))
				{
				array_push($equipmentList,$re_row);
				}

				$this->smarty->assign('equipmentList',$equipmentList);				

				$this->smarty->assign('sdate',$today);
				$this->smarty->assign('edate',$today);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('mode',$mode);

				$this->smarty->assign("page_action","schedule_equipment_controller.php");

				$this->smarty->display("intranet/common_contents/work_equipment/equipment_input_mvc.tpl");
		}


		//============================================================================
		// 배차신청 저장 logic (Insert)
		//============================================================================	
		function InsertAction()
		{

			global $db;
			global $mode, $memberID, $today;
			global $membername, $contents, $devicename;
			global $sdate, $edate, $endtime;

			$sql="insert into schedule_device_tbl (membername,contents,devicename,sdate,edate,endtime,updatedate,updateuser) values";
			$sql=$sql."('$membername','$contents','$devicename','$sdate','$edate','$endtime','$today','$memberID')";
			//echo $sql."<br>";
			mysql_query($sql,$db);	
			$this->smarty->display("intranet/moveclose_page.tpl");
		}


		//============================================================================
		// 배차신청 읽기/수정 logic
		//============================================================================	
		function UpdateReadPage()
		{
			global $db;
			global $mode,$no,$currentPage;
			global $memberID,$techNo,$now_day;

			$equipmentList = array(); 

			$sql = "select * from systemconfig_tbl where SysKey='bizdevice' and Code<>'10' and Code<>'11' order by Code";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
			array_push($equipmentList,$re_row);
			}

			$this->smarty->assign('equipmentList',$equipmentList);				


			$sql1 = "select * from schedule_device_tbl where no='$no'";
			$re1 = mysql_query($sql1,$db);
			while($re_row1 = mysql_fetch_array($re1))
			{
				$membername = $re_row1[membername];
				$endtime = $re_row1[endtime];
				$devicename = $re_row1[devicename];
				$contents = $re_row1[contents];
				$sdate = $re_row1[sdate];
				$edate = $re_row1[edate];
				$updatedate = $re_row1[updatedate];
				$updateuser = $re_row1[updateuser];
			}

			$this->smarty->assign('no',$no	);
			$this->smarty->assign('memberID',$memberID	);
			$this->smarty->assign('mode',$mode	);
			$this->smarty->assign('membername',$membername);
			$this->smarty->assign('endtime',$endtime);
			$this->smarty->assign('devicename',$devicename);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('sdate',$sdate);
			$this->smarty->assign('edate',$edate);
			$this->smarty->assign('updatedate',$updatedate);
			$this->smarty->assign('updateuser',$updateuser);				
		
			$this->smarty->assign("page_action","schedule_equipment_controller.php");

			$this->smarty->display("intranet/common_contents/work_equipment/equipment_input_mvc.tpl");
		}


		//============================================================================
		// 배차신청 저장 logic (Update)
		//============================================================================	
		function UpdateAction()
		{
			global $db;
			global $mode, $memberID, $no, $today;
			global $today, $membername, $contents;
			global $devicename, $sdate, $edate, $endtime;
				
			$sql  = "update schedule_device_tbl set membername='$membername', contents='$contents', devicename='$devicename', sdate='$sdate', edate='$edate', endtime='$endtime', updatedate='$today', updateuser='$memberID' where no='$no'";
			mysql_query($sql,$db);

			$this->smarty->display("intranet/moveclose_page.tpl");
		}


		//============================================================================
		// 배차신청 삭제 logic (Delete)
		//============================================================================	
		function DeleteAction()
		{
	
			global $db;
			global $no,$mode,$memberID;	
	
			$sql = "delete from schedule_device_tbl where no=$no";
			mysql_query($sql,$db);

			$this->smarty->display("intranet/moveclose_page.tpl");
		}
		

		//============================================================================
		// 배차신청 List logic
		//============================================================================	
		function View()
		{
			global $db;
			global $today,$memberID,$start_y,$start_m;
			global $date1,$date2,$date3,$last_page,$EquipmentCode,$EquipmentName,$select_index;
;

			if($_SESSION['Manager_auth'])//총무
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);

			$sql="select * from member_tbl where MemberNo='$memberID'";
				$re = mysql_query($sql,$db);
				$Certificate = mysql_result($re,0,"Certificate");

				if($Certificate) 
				{
					if(strpos($Certificate,"총무") !== false) { $Auth=true; }
				}	

			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘
			$uyear = date("Y")+3;  /////최대 보이는 년도-1

			if($start_y == "") { $start_y = $date1; }
			if($start_m == "") { $start_m = $date2; }

			$date = $year."-".$month;
			$ilastday= month_lastday($year,$month);
 			$ilastday=date( "t", mktime( 0, 0, 0, $month, 1, $year ) ); 

			if($select_index==""){$select_index="프로젝터";}

			$equipmentList = array();
			
			$Equipment_Row="0";

			$sql="select * from systemconfig_tbl where SysKey='bizdevice' and Code<>'10' and Code<>'11' order by Code"; 
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				array_push($equipmentList,$re_row);

				$EquipmentCode[$Equipment_Row] = $re_row[Code];
				$EquipmentName[$Equipment_Row] = $re_row[Name];
				$tab_value[$Equipment_Row] = $Equipment_Row;

				$Equipment_Row++;
			}

			$this->smarty->assign('today',$today);
			$this->smarty->assign('date1',$date1);
			$this->smarty->assign('date2',$date2);
			$this->smarty->assign('date3',$date3);
			$this->smarty->assign('Equipment_Row',$Equipment_Row);
			$this->smarty->assign('equipment_use',$equipment_use);
			$this->smarty->assign('select_index',$select_index);
			$this->smarty->assign('EquipmentName',$EquipmentName);
			$this->smarty->assign('EquipmentCode',$EquipmentCode);
			$this->smarty->assign('tab_value',$tab_value);
			

			$this->smarty->assign('start_y',$start_y);
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('ilastday',$ilastday);
			$this->smarty->assign('equipmentList',$equipmentList);


			/////////////////오늘의 배차현황/////////////////////////////////////////////////////////////////////////////////////////
			for ($k=0;$k<$Equipment_Row;$k++)
			{
				$sql3 = "select * from schedule_device_tbl where  sdate <= '$today' and edate >= '$today' and devicename='$EquipmentName[$k]'";
					$re3 = mysql_query($sql3,$db);
					$use_row = mysql_num_rows($re3);
					
					if($use_row>"0")
					{
						$equipment_use[$k]="yes";
					}
					else if($use_row=="0")
					{
						$equipment_use[$k]="no";
					}
			}
						$this->smarty->assign('equipment_use',$equipment_use);
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
			$iiday=$date3-2;
			if ($iiday==0){
				$iiday=1;
				$ilastday=$ilastday-1;
			}

			$DateList = array();
			$DateInc;
			$Today=split("-",$today);	

			$endday=date("Y-m-d",strtotime ("+28 days {$today}"));
			$EndDay=split("-",$endday);	
			
			for($Count=-2;$Count<29;$Count++)
			{
				$DateInc="+".$Count." days";
				$NewDate=Date('Y-m-d', strtotime("$DateInc {$today}"));
					array_push($DateList,$NewDate);
			}
					$dateitem=split("-",$currentDate);	


			$query_data2 = array();
			$Sday = sprintf("%04d-%02d-%02d",$Today[0],$Today[1],$iiday);	
			$Eday = sprintf("%04d-%02d-%02d",$EndDay[0],$EndDay[1],$EndDay[2]);	
	
			$sql2 = "select * from schedule_device_tbl where  (('$Sday' <= sdate and sdate <= '$Eday' ) or ('$Sday' <= edate and edate <= '$Eday' )) and devicename='$select_index' order by no asc";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2)) 
			{
				array_push($query_data2,$re_row2);				
			}


			$query_data3 = array();
			$currentDate ="";
			$holy_sc[0]="";

			$dateitem=split("-",$DateList[0]);	

			$currentDate=$DateList[0];

			$EquipmentReg = array();

			for($Count=0;$Count<count($DateList);$Count++)
			{
				 $DateListsep = explode("-",$DateList[$Count]); 

				$tday[$Count] = week_day($DateListsep[0],$DateListsep[1],$DateListsep[2]);
				$holy_sc[$Count] = holy($DateListsep[0]."-".$DateListsep[1]."-".$DateListsep[2]);

				$MemberCount=0;

				for($Count2=0;$Count2<count($query_data2);$Count2++)
				{
					$no=$query_data2[$Count2][no];
					$membername=$query_data2[$Count2][membername];
					$endtime=$query_data2[$Count2][endtime];
					$devicename=$query_data2[$Count2][devicename];
					$contents=$query_data2[$Count2][contents];
					$sdate=$query_data2[$Count2][sdate];
					$edate=$query_data2[$Count2][edate];
			
					$start_day=$query_data2[$Count2][sdate];
					$end_day=$query_data2[$Count2][edate];
					$interval=  $this->date_diff($end_day,$start_day);

					for($Count3=0;$Count3<=$interval;$Count3++)
					{
						$NextDay[$Count2]=date("Y-m-d",strtotime ("+{$Count3} days {$start_day}"));

							if($DateList[$Count] == $NextDay[$Count2])
							{

								$EquipmentReg[$MemberCount][$Count][0]=$membername;
								$EquipmentReg[$MemberCount][$Count][1]=$contents;
								$EquipmentReg[$MemberCount][$Count][2]=$endtime;
								$EquipmentReg[$MemberCount][$Count][3]=$no;

							}
							
					
					}
					$MemberCount++;
				}
			}

			$this->smarty->assign('Auth',$Auth);
			$this->smarty->assign('EquipmentReg',$EquipmentReg);
			$this->smarty->assign('dateitem',$dateitem);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('endtime',$endtime);
			$this->smarty->assign('regno',$regno);
			$this->smarty->assign('NextDay',$NextDay);
			$this->smarty->assign('DateList',$DateList);
			$this->smarty->assign('tday',$tday);
			$this->smarty->assign('holy_sc',$holy_sc);
			$this->smarty->assign('querydata2_count',count($query_data2));
			$this->smarty->assign('DateList1',$DateList1);

			$currentDay= substr ( $currentDate , 8 , 2 );
			$oneitem[0]=$currentDay;
			$tday = week_day($start_y,$start_m,$currentDay);
			$w=date('w',strtotime($currentDate));
			$oneitem[1]="(".$tday.")";

			array_push($query_data3,$oneitem);				


			$this->smarty->assign('spanCount',4);
			$this->smarty->assign('start_y',$start_y);
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('query_data1',$query_data1);
			$this->smarty->assign('query_data3',$query_data3);
	
			$this->smarty->assign("page_action","schedule_equipment_controller.php");
			$this->smarty->display("intranet/common_contents/work_equipment/equipment_contents_mvc.tpl");
		}



		function date_diff($date1, $date2){ 
		 $_date1 = explode("-",$date1); 
		 $_date2 = explode("-",$date2);

		 $tm1 = mktime(0,0,0,$_date1[1],$_date1[2],$_date1[0]); 
		 $tm2 = mktime(0,0,0,$_date2[1],$_date2[2],$_date2[0]);

		 return ($tm1 - $tm2) / 86400;
		}

		 

	}
?>