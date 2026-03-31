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
	class ScheduleCarLogic {
		var $smarty;
		function ScheduleCarLogic($smarty)
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

			$carList = array(); 

			$sql = "select * from systemconfig_tbl where SysKey='bizcarno' order by orderno";
				$re = mysql_query($sql,$db);
				while($re_row = mysql_fetch_array($re))
				{
				array_push($carList,$re_row);
				}

				$this->smarty->assign('carList',$carList);				

		
				$url="patent_controller.php";
				$this->smarty->assign('sdate',$today);
				$this->smarty->assign('edate',$today);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('mode',$mode);

				$this->smarty->assign("page_action","schedule_car_controller.php");

				$this->smarty->display("intranet/common_contents/work_car/schedulecar_input_mvc.tpl");
		}


		//============================================================================
		// 배차신청 저장 logic (Insert)
		//============================================================================	
		function InsertAction()
		{

			global $db;
			global $mode, $memberID, $today;
			global $membername, $contents, $carno;
			global $sdate, $edate, $endtime;

			$sql="insert into schedule_car_tbl (membername,contents,carno,sdate,edate,endtime,updatedate,updateuser) values";
			$sql=$sql."('$membername','$contents','$carno','$sdate','$edate','$endtime','$today','$memberID')";
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

			$carList = array(); 

			$sql = "select * from systemconfig_tbl where SysKey='bizcarno' order by code";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
			array_push($carList,$re_row);
			}

			$this->smarty->assign('carList',$carList);				


			$sql1 = "select * from schedule_car_tbl where no='$no'";
			$re1 = mysql_query($sql1,$db);
			while($re_row1 = mysql_fetch_array($re1))
			{
				$membername = $re_row1[membername];
				$endtime = $re_row1[endtime];
				$car_code = $re_row1[carno];
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
			$this->smarty->assign('car_code',$car_code);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('sdate',$sdate);
			$this->smarty->assign('edate',$edate);
			$this->smarty->assign('updatedate',$updatedate);
			$this->smarty->assign('updateuser',$updateuser);				
		
			$this->smarty->assign("page_action","schedule_car_controller.php");

			$this->smarty->display("intranet/common_contents/work_car/schedulecar_input_mvc.tpl");
		}


		//============================================================================
		// 배차신청 저장 logic (Update)
		//============================================================================	
		function UpdateAction()
		{
			global $db;
			global $mode, $memberID, $no, $today;
			global $today, $membername, $contents;
			global $carno, $sdate, $edate, $endtime;
				
			$sql  = "update schedule_car_tbl set membername='$membername', contents='$contents', carno='$carno', sdate='$sdate', edate='$edate', endtime='$endtime', updatedate='$today', updateuser='$memberID' where no='$no'";
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
	
			$sql = "delete from schedule_car_tbl where no=$no";
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
			global $date1,$date2,$date3,$last_page,$CarCode,$CarName,$tab_index;
;

			

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$this->smarty->assign('Auth',true);
			}else{
				$this->smarty->assign('Auth',false);
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

			if($tab_index==""){$tab_index="0";}

			$carList = array();
			
			$Car_Row="0";

			$sql="select * from systemconfig_tbl where SysKey='bizcarno' order by orderno"; 
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				array_push($carList,$re_row);

				$CarCode[$Car_Row] = $re_row[Code];
				$CarName[$Car_Row] = $re_row[Name];
				$tab_value[$Car_Row] = $Car_Row;

				$Car_Row++;
			}

			$this->smarty->assign('today',$today);
			$this->smarty->assign('date1',$date1);
			$this->smarty->assign('date2',$date2);
			$this->smarty->assign('date3',$date3);
			$this->smarty->assign('Car_Row',$Car_Row);
			$this->smarty->assign('car_use',$car_use);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('CarName',$CarName);
			$this->smarty->assign('CarCode',$CarCode);
			$this->smarty->assign('tab_value',$tab_value);
			

			$this->smarty->assign('start_y',$start_y);
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('ilastday',$ilastday);
			$this->smarty->assign('carList',$carList);


			/////////////////오늘의 배차현황/////////////////////////////////////////////////////////////////////////////////////////
			for ($k=0;$k<$Car_Row;$k++)
			{
				$sql3 = "select * from schedule_car_tbl where  sdate <= '$today' and edate >= '$today' and carno='$CarCode[$k]'";
					$re3 = mysql_query($sql3,$db);
					$use_row = mysql_num_rows($re3);
					
					if($use_row>"0")
					{
						$car_use[$k]="yes";
					}
					else if($use_row=="0")
					{
						$car_use[$k]="no";
					}
			}
						$this->smarty->assign('car_use',$car_use);
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
	
			$sql2 = "select * from schedule_car_tbl where  (('$Sday' <= sdate and sdate <= '$Eday' ) or ('$Sday' <= edate and edate <= '$Eday' )) and carno='$CarCode[$tab_index]' order by no asc";
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

			$CarReg = array();

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
					$carno=$query_data2[$Count2][carno];
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

								$CarReg[$MemberCount][$Count][0]=$membername;
								$CarReg[$MemberCount][$Count][1]=$contents;
								$CarReg[$MemberCount][$Count][2]=$endtime;
								$CarReg[$MemberCount][$Count][3]=$no;
							}
							
					
					}
					$MemberCount++;
				}
			}


			$this->smarty->assign('CarReg',$CarReg);
			$this->smarty->assign('dateitem',$dateitem);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('endtime',$endtime);
			$this->smarty->assign('regno',$regno);
			$this->smarty->assign('NextDay',$NextDay);
			$this->smarty->assign('DateList',$DateList);
			$this->smarty->assign('tday',$tday);
			$this->smarty->assign('start_m',$start_m);
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
	
			$this->smarty->assign("page_action","schedule_car_controller.php");
			$this->smarty->display("intranet/common_contents/work_car/schedulecar_contents_mvc.tpl");
		}



		function date_diff($date1, $date2){ 
		 $_date1 = explode("-",$date1); 
		 $_date2 = explode("-",$date2);

		 $tm1 = mktime(0,0,0,$_date1[1],$_date1[2],$_date1[0]); 
		 $tm2 = mktime(0,0,0,$_date2[1],$_date2[2],$_date2[0]);

		 return ($tm1 - $tm2) / 86400;
		}


		//============================================================================
		// 배차신청 이전형식 List logic
		//============================================================================	
		function View2()
		{
			global $db;
			global $today,$memberID,$start_y,$start_m;
			global $date1,$date2,$date3,$last_page,$CarCode,$CarName,$tab_index;
;

			

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$this->smarty->assign('Auth',true);
			}else{
				$this->smarty->assign('Auth',false);
			}


			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘

			if($date3=="1")
			{
				$date3="3";
			}
			$uyear = date("Y")+3;  /////최대 보이는 년도-1
			if($start_y == "") { $start_y = $date1; }
			if($start_m == "") { $start_m = $date2; }

			$date = $year."-".$month;

		$ilastday= month_lastday($start_y,$start_m);
		
		if($tab_index==""){$tab_index="0";}

			$carList = array();
			
			$Car_Row="0";

			$sql="select * from systemconfig_tbl where SysKey='bizcarno' order by orderno"; 
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				array_push($carList,$re_row);

				$CarCode[$Car_Row] = $re_row[Code];
				$CarName[$Car_Row] = $re_row[Name];
				$tab_value[$Car_Row] = $Car_Row;

				$Car_Row++;
			}
			$car_col=$Car_Row*3+1;

			$twidth=$Car_Row*300;

			$this->smarty->assign('today',$today);
			$this->smarty->assign('date1',$date1);
			$this->smarty->assign('date2',$date2);
			$this->smarty->assign('date3',$date3);
			$this->smarty->assign('Car_Row',$Car_Row);
			$this->smarty->assign('car_use',$car_use);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('CarName',$CarName);
			$this->smarty->assign('CarCode',$CarCode);
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('twidth',$twidth);		
			$this->smarty->assign('car_col',$car_col);		

			$this->smarty->assign('start_y',$start_y);
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('carList',$carList);

			$iiday=$date3-2;
			$iiday2=$date3-2;
			if ($iiday==0){
				$iiday=1;
				//$ilastday=$ilastday-1;
			}
			else if ($iiday==-1){
				$iiday=1;
				//$ilastday=$ilastday-1;
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

					$dateitem=split("-",$DateList[0]);	

					$currentDate=$DateList[0];


			for ($iiday;$iiday<=$ilastday;$iiday++)
			{
				$day1[$ilday] = sprintf("%04d-%02d-%02d",$start_y,$start_m,$iiday);	

				//$n_color++;
				//include "../../line_color_h.php";	
			for ($car_count="0";$car_count<$Car_Row;$car_count++)
				{
			
				$n=0;
	
				$query01 = "select * from schedule_car_tbl where sdate <= '$day1[$ilday]' and edate >= '$day1[$ilday]' and carno='$CarCode[$car_count]' order by no asc";
				
				$result01 = mysql_query($query01,$db);
				$result01_row[$iiday][$car_count]=mysql_num_rows($result01); 
				if(mysql_num_rows($result01) > 0) 
				{  /// 사용종료일
					while($result_row_01 = mysql_fetch_array($result01)) 
					{
						$usename1[$iiday][$car_count][$n] = $result_row_01[membername];
						//$contents1[$iiday][$car_count][$n] = $result_row_01[contents];
						$contents1[$iiday][$car_count][$n] = str_cutting($result_row_01[contents],24);
						$endtime1[$iiday][$car_count][$n] = $result_row_01[endtime];
						$updateuser1[$iiday][$car_count][$n] =$result_row_01[updateuser] ;
						$carno1[$iiday][$car_count][$n] = $result_row_01[carno];
						$regno1[$iiday][$car_count][$n] = $result_row_01[no];		
					$n++;}
					}

				$DateListsep = explode("-",$day1[$ilday]); 
				$tday[$iiday] = week_day($DateListsep[0],$DateListsep[1],$DateListsep[2]);
				$holy_sc[$iiday] = holy($DateListsep[0]."-".$DateListsep[1]."-".$DateListsep[2]);

			}//for ($car_count="0";$car_count<$Car_Row;$car_count++)
		}//for ($iiday;$iiday<=$ilastday;$iiday++)
		





			if($start_m =="12")
			{
				$start_y=$start_y+1;
				$start_m="01";
			}else
			{
				$start_m2=$start_m+1;
				$start_m2=sprintf('%02d',$start_m2);
			}

			for ($iday=1;$iday<=$date3-3;$iday++)
			{
				$day2[$iday] = sprintf("%04d-%02d-%02d",$start_y,$start_m2,$iday);	

				//$n_color++;
				//include "../../line_color_h.php";	

			for ($car_count2="0";$car_count2<$Car_Row;$car_count2++)
				{
				$j=0;
				$query02 = "select * from schedule_car_tbl where sdate <= '$day2[$iday]' and edate >= '$day2[$iday]' and carno='$CarCode[$car_count2]' order by carno asc";
				$result02 = mysql_query($query02,$db);
				$result02_row[$iday][$car_count2]=mysql_num_rows($result02); 
				if(mysql_num_rows($result02) > 0) 
				{  /// 사용종료일
					while($result_row_02 = mysql_fetch_array($result02)) 
					{
						$usename2[$iday][$car_count2][$j] = $result_row_02[membername];
						$contents2[$iday][$car_count2][$j] = $result_row_02[contents];
						$endtime2[$iday][$car_count2][$j] = $result_row_02[endtime];
						$updateuser[$iday][$car_count2][$j] =$result_row_02[updateuser] ;
						$carno2[$iday][$car_count2][$j] = $result_row_02[carno];
						$regno2[$iday][$car_count2][$j]	 = $result_row_02 [no];		
					$j++;}
				}

				$DateListsep = explode("-",$day2[$iday]); 
				$tday[$iday] = week_day($DateListsep[0],$DateListsep[1],$DateListsep[2]);
				$holy_sc[$iday] = holy($DateListsep[0]."-".$DateListsep[1]."-".$DateListsep[2]);
				}
			}



			$this->smarty->assign('iday',$iday);
			$this->smarty->assign('dateitem',$dateitem);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('endtime',$endtime);
			$this->smarty->assign('regno',$regno);
			$this->smarty->assign('NextDay',$NextDay);
			$this->smarty->assign('DateList',$DateList);
			$this->smarty->assign('tday',$tday);
			$this->smarty->assign('holy_sc',$holy_sc);
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('start_m2',$start_m2);
			$this->smarty->assign('querydata2_count',count($query_data2));
			$this->smarty->assign('DateList1',$DateList1);
			$this->smarty->assign('iiday',$iiday);
			$this->smarty->assign('ilastday',$ilastday);
			$this->smarty->assign('date3',$date3);

			$currentDay= substr ( $currentDate , 8 , 2 );
			$oneitem[0]=$currentDay;
			$tday = week_day($start_y,$start_m,$currentDay);
			$w=date('w',strtotime($currentDate));
			$oneitem[1]="(".$tday.")";


			$this->smarty->assign('spanCount',4);
			$this->smarty->assign('start_y',$start_y);
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('query_data1',$query_data1);
			$this->smarty->assign('query_data3',$query_data3);

			$this->smarty->assign('result01_row',$result01_row);
			$this->smarty->assign('usename1',$usename1);
			$this->smarty->assign('contents1',$contents1);
			$this->smarty->assign('endtime1',$endtime1);
			$this->smarty->assign('updateuser1',$updateuser1);
			$this->smarty->assign('carno1',$carno1);
			$this->smarty->assign('regno1',$regno1);
	
			$this->smarty->assign('result02_row',$result02_row);
			$this->smarty->assign('usename2',$usename2);
			$this->smarty->assign('contents2',$contents2);
			$this->smarty->assign('endtime2',$endtime2);
			$this->smarty->assign('updateuser2',$updateuser2);
			$this->smarty->assign('carno2',$carno2);
			$this->smarty->assign('regno2',$regno2);

			$this->smarty->assign("page_action","schedule_car_controller.php");
			$this->smarty->display("intranet/common_contents/work_car/schedulecar_contents_mvc2.tpl");
		}
	}
?>