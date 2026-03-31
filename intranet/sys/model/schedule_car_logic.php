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

    // apiLibrary
    include_once $_SERVER['DOCUMENT_ROOT'] . '/apiLibrary/common.lib.php';
    $crypto = new Crypto();

	$start_m=sprintf('%02d',$start_m);
	$date3=sprintf('%02d',$date3);

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
            global $crypto;
			global $db;
			global $mode, $memberID, $today;

			$carList = array(); 
				
				//윤여훈,정헤윤,이준희 (당일출장전용)
//				if($memberID=="B16308" || $memberID=="B17305" || $memberID=="B18302" || $memberID=="T03225"){
//					$sql = "select * from systemconfig_tbl where SysKey='bizcarno' order by orderno";
//				}else{
//					$sql = "select * from systemconfig_tbl where SysKey='bizcarno' and Code <>'6640' order by orderno";
//				}
//				$re = mysql_query($sql,$db);
//				while($re_row = mysql_fetch_array($re))
//				{
//				    array_push($carList,$re_row);
//				}

                $arrayData = array(
                    'ActionMode' => 'baron_schedule_car',
                    'requestType' => 'select_systemconfig',
                    // 'data_json' => array('Code' => '6640')
                );

                // 윤여훈,정헤윤,이준희 (당일출장전용)
                if ($memberID=="B16308" || $memberID=="B17305" || $memberID=="B18302" || $memberID=="T03225") {
                } else {
                    $arrayData['data_json'] = array('Code' => '6640');
                }

                // apiLibrary 호출
                $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
                $carList = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

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
            global $crypto;
			global $db;
			global $mode, $memberID, $today;
			global $membername, $contents, $carno;
			global $sdate, $edate, $endtime;

//			$sql="insert into schedule_car_tbl (membername,contents,carno,sdate,edate,endtime,updatedate,updateuser,insertdate) values";
//			$sql=$sql."('$membername','$contents','$carno','$sdate','$edate','$endtime',now(),'$memberID',now())";
//			mysql_query($sql,$db);

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'insert_schedule_car',
                'data_json' => array(
                    'membername' => $membername,
                    'contents' => $contents,
                    'carno' => $carno,
                    'sdate' => $sdate, 
                    'edate' => $edate,
                    'endtime' => $endtime,
                    'memberID' => $memberID
                )
            );
            
            // apiLibrary 호출
            $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            
			// echo $sql;
			$this->smarty->display("intranet/moveclose_page.tpl");
		}


		//============================================================================
		// 배차신청 읽기/수정 logic
		//============================================================================	
		function UpdateReadPage()
		{
            global $crypto;
			global $db;
			global $mode,$no,$currentPage;
			global $memberID,$techNo,$now_day;

			$carList = array(); 

//			//윤여훈,정헤윤,이준희 (당일출장전용)
//			if($memberID=="B16308" || $memberID=="B17305" || $memberID=="B18302" || $memberID=="T03225" ){
//				$sql = "select * from systemconfig_tbl where SysKey='bizcarno' order by orderno";
//			}else{
//				$sql = "select * from systemconfig_tbl where SysKey='bizcarno' and Code <>'6640' order by orderno";
//			}
//			$re = mysql_query($sql,$db);
//			while($re_row = mysql_fetch_array($re))
//			{
//				array_push($carList,$re_row);
//			}

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_systemconfig',
                // 'data_json' => array('Code' => '6640')
            );

            // 윤여훈,정헤윤,이준희 (당일출장전용)
            if ($memberID=="B16308" || $memberID=="B17305" || $memberID=="B18302" || $memberID=="T03225") {
            } else {
                $arrayData['data_json'] = array('Code' => '6640');
            }

            // apiLibrary 호출
            $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            $carList = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

			$this->smarty->assign('carList',$carList);

//			$sql1 = "select * from schedule_car_tbl where no='$no'";
//			$re1 = mysql_query($sql1,$db);
//			while($re_row1 = mysql_fetch_array($re1))
//			{
//				$membername = $re_row1[membername];
//				$endtime = $re_row1[endtime];
//				$car_code = $re_row1[carno];
//				$contents = $re_row1[contents];
//				$sdate = $re_row1[sdate];
//				$edate = $re_row1[edate];
//				$updatedate = $re_row1[updatedate];
//				$updateuser = $re_row1[updateuser];
//			}

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_schedule_car',
                'data_json' => array('no' => $no)
            );

            // apiLibrary 호출
            $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

            $membername = $hanmacResult->data['membername'];
            $endtime = $hanmacResult->data['endtime'];
            $car_code = $hanmacResult->data['carno'];
            $contents = $hanmacResult->data['contents'];
            $sdate = $hanmacResult->data['sdate'];
            $edate = $hanmacResult->data['edate'];
            $updatedate = $hanmacResult->data['updatedate'];
            $updateuser = $hanmacResult->data['updateuser'];

			$updateName=MemberNo2Name($updateuser);

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
			$this->smarty->assign('updateName',$updateName);
		
			$this->smarty->assign("page_action","schedule_car_controller.php");

			$this->smarty->display("intranet/common_contents/work_car/schedulecar_input_mvc.tpl");
		}


		//============================================================================
		// 배차신청 저장 logic (Update)
		//============================================================================	
		function UpdateAction()
		{
            global $crypto;
			global $db;
			global $mode, $memberID, $no, $today;
			global $today, $membername, $contents;
			global $carno, $sdate, $edate, $endtime;
				
//			$sql  = "update schedule_car_tbl set membername='$membername', contents='$contents', carno='$carno', sdate='$sdate', edate='$edate', endtime='$endtime', updatedate=now(), updateuser='$memberID' where no='$no'";
//			mysql_query($sql,$db);

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_schedule_car',
                'data_json' => array(
                    'membername' => $membername,
                    'contents' => $contents,
                    'carno' => $carno,
                    'sdate' => $sdate,
                    'edate' => $edate,
                    'endtime' => $endtime,
                    'updateuser' => $memberID,
                    'no' => $no
                )
            );

            // apiLibrary 호출
            $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);

			$this->smarty->display("intranet/moveclose_page.tpl");
		}


		//============================================================================
		// 배차신청 삭제 logic (Delete)
		//============================================================================	
		function DeleteAction()
		{
            global $crypto;
			global $db;
			global $no,$mode,$memberID;	
	
//			$sql = "delete from schedule_car_tbl where no=$no";
//			mysql_query($sql,$db);

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_schedule_car',
                'data_json' => array(
                    'no' => $no
                )
            );

            // apiLibrary 호출
            $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);

			$this->smarty->display("intranet/moveclose_page.tpl");
		}
		

		//============================================================================
		// 배차신청 List logic
		//============================================================================	
		function View()
		{
            global $crypto;
			global $db;
			global $today,$memberID,$start_y,$start_m;
			global $date1,$date2,$date3,$last_page,$CarCode,$CarName,$tab_index;

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
//			$Car_Row="0";
//			$sql="select * from systemconfig_tbl where SysKey='bizcarno' order by orderno";
//			$re = mysql_query($sql,$db);
//			while($re_row = mysql_fetch_array($re))
//			{
//				array_push($carList,$re_row);
//
//				$CarCode[$Car_Row] = $re_row[Code];
//				$CarName[$Car_Row] = $re_row[Name];
//				$tab_value[$Car_Row] = $Car_Row;
//
//				$Car_Row++;
//			}

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_systemconfig'
            );

            // apiLibrary 호출
            $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            $carList = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);
            
            $Car_Row = count($carList);
            for ($idx = 0; $idx < count($carList); $idx++) {
				$CarCode[$idx] = $carList[$idx]['Code'];
				$CarName[$idx] = $carList[$idx]['Name'];
				$tab_value[$idx] = $idx;
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
//				$sql3 = "select * from schedule_car_tbl where  sdate <= '$today' and edate >= '$today' and carno='$CarCode[$k]'";
//                $re3 = mysql_query($sql3,$db);
//                $use_row = mysql_num_rows($re3);

                $arrayData = array(
                    'ActionMode' => 'baron_schedule_car',
                    'requestType' => 'select_schedule_car_today',
                    'data_json' => array(
                        'today' => $today,
                        'carno' => $CarCode[$k]
                    )
                );

                // apiLibrary 호출
                $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
                $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

                $use_row = count($hanmacResult->data);
                
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
//			$sql2 = "select * from schedule_car_tbl where  (('$Sday' <= sdate and sdate <= '$Eday' ) or ('$Sday' <= edate and edate <= '$Eday' )) and carno='$CarCode[$tab_index]' order by no asc";
//			//echo $sql2."<br>";
//			$re2 = mysql_query($sql2,$db);
//            while($re_row2 = mysql_fetch_array($re2)){
//                array_push($query_data2,$re_row2);
//            }

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_schedule_car_between_date',
                'data_json' => array(
                    'sdate' => $Sday,
                    'edate' => $Eday,
                    'carno' => $CarCode[$tab_index]
                )
            );

            // apiLibrary 호출
            $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            $query_data2 = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

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
//echo $CarReg[$MemberCount][$Count][3]."<br>";;
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
			global $today,$memberID,$go_to,$start_y,$start_m;
			global $date1,$date2,$date3,$last_page,$CarCode,$CarName,$tab_index;
			
			//echo "start_m".$start_m."<Br>";
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
			if($start_m == "" or $start_m == "00") { $start_m = $date2; }
			//echo "start_m".$start_m."<Br>";
			$date = $year."-".$month;

		$ilastday= month_lastday($start_y,$start_m);
		
		if($tab_index==""){$tab_index="0";}

			$carList = array();
//			$Car_Row="0";
//			$sql="select * from systemconfig_tbl where SysKey='bizcarno' order by orderno";
//			$re = mysql_query($sql,$db);
//			while($re_row = mysql_fetch_array($re))
//			{
//				array_push($carList,$re_row);
//
//				$CarCode[$Car_Row] = $re_row[Code];
//				$CarName[$Car_Row] = $re_row[Name];
//				$tab_value[$Car_Row] = $Car_Row;
//
//				$Car_Row++;
//			}
//			$car_col=$Car_Row*3+1;
//			$twidth=$Car_Row*300;

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_systemconfig'
            );

            // apiLibrary 호출
            global $crypto;
            $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            $carList = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);
            $Car_Row = count($carList);
            for ($idx = 0; $idx < count($carList); $idx++) {
                $CarCode[$idx] = $carList[$idx]['Code'];
                $CarName[$idx] = $carList[$idx]['Name'];
                $tab_value[$idx] = $idx;
            }
			$car_col = $Car_Row * 3 + 1;
			$twidth = $Car_Row * 300;

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
			//echo "start_m".$start_m."<Br>";
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('carList',$carList);

            $iiday2 = $iiday = $date3-2;
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
			
			for($Count=-2;$Count<29;$Count++){
				$DateInc="+".$Count." days";

				$NewDate=Date('Y-m-d', strtotime("$DateInc {$today}"));
                array_push($DateList,$NewDate);
			}
            $dateitem=split("-",$currentDate);
            $dateitem=split("-",$DateList[0]);
            $currentDate=$DateList[0];

            $data_json1 = array();
			for ($iiday; $iiday <= $ilastday; $iiday++) {
				$day1[$ilday] = sprintf("%04d-%02d-%02d", $start_y, $start_m, $iiday);

				//$n_color++;
				//include "../../line_color_h.php";
                for ($car_count="0"; $car_count < $Car_Row; $car_count++) {
                    $n=0;
                    /* 기존 쿼리
    				$query01 = "select * from schedule_car_tbl where sdate <= '$day1[$ilday]' and edate >= '$day1[$ilday]' and carno='$CarCode[$car_count]' order by no asc";
    				//echo $query01."<br>";
    				$result01 = mysql_query($query01,$db);
    				$result01_row[$iiday][$car_count]=mysql_num_rows($result01);
    				if(mysql_num_rows($result01) > 0)
    				{  /// 사용종료일
    					while($result_row_01 = mysql_fetch_array($result01))
    					{
    						$usename1[$iiday][$car_count][$n] = $result_row_01[membername];
    						$contents1[$iiday][$car_count][$n] = $result_row_01[contents];
    						$contents1[$iiday][$car_count][$n] = utf8_strcut($contents1[$iiday][$car_count][$n],15,'..');

    						$endtime1[$iiday][$car_count][$n] = $result_row_01[endtime];
    						$updateuser1[$iiday][$car_count][$n] =$result_row_01[updateuser] ;
    						$carno1[$iiday][$car_count][$n] = $result_row_01[carno];
    						$regno1[$iiday][$car_count][$n] = $result_row_01[no];
    					    $n++;
                        }
                    }
                    */

                    /* 4초 이상 소요 주석처리
                    $arrayData = array(
                        'ActionMode' => 'baron_schedule_car',
                        'requestType' => 'select_schedule_car_today',
                        'data_json' => array(
                            'today' => $day1[$ilday],
                            'carno' => $CarCode[$car_count],
                            'orderby' => 'order by no asc'
                        )
                    );

                    // apiLibrary 호출
                    $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
                    $result01 = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

                    $result01_row[$iiday][$car_count] = count($result01);
                    if (count($result01) > 0) {
                        for ($idx = 0; $idx < count($result01); $idx++) {
                            $usename1[$iiday][$car_count][$n] = $result01[$idx]['membername'];
                            $contents1[$iiday][$car_count][$n] = $result01[$idx]['contents'];
                            $contents1[$iiday][$car_count][$n] = utf8_strcut($contents1[$iiday][$car_count][$idx],15,'..');

                            $endtime1[$iiday][$car_count][$n] = $result01[$idx]['endtime'];
                            $updateuser1[$iiday][$car_count][$n] =$result01[$idx]['updateuser'] ;
                            $carno1[$iiday][$car_count][$n] = $result01[$idx]['carno'];
                            $regno1[$iiday][$car_count][$n] = $result01[$idx]['no'];

                            $n++;
                        }
                    }

                    $DateListsep = explode("-",$day1[$ilday]);
                    $tday[$iiday] = week_day($DateListsep[0],$DateListsep[1],$DateListsep[2]);
                    $holy_sc[$iiday] = holy($DateListsep[0]."-".$DateListsep[1]."-".$DateListsep[2]);
                    */

                    // 한번만 요청하기 위해 데이터 수집
                    array_push($data_json1, array(
                        'today' => $day1[$ilday],
                        'carno' => $CarCode[$car_count],
                        'orderby' => 'order by no asc'
                    ));
                } // for ($car_count="0";$car_count<$Car_Row;$car_count++)
            } // for ($iiday;$iiday<=$ilastday;$iiday++)

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_schedule_car_today_foreach',
                'data_json' => $data_json1
            );

            $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            $result01 = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

            // 한번에 응답받은 데이터로 변수 초기화
            for ($iiday2; $iiday2 <= $ilastday; $iiday2++) {
                $day1[$ilday2] = sprintf("%04d-%02d-%02d", $start_y, $start_m, $iiday2);
                for ($car_count="0"; $car_count < $Car_Row; $car_count++) {
                    $n=0;
                    // $iiday2 는 일자 숫자값 (1~31)
                    // $car_count 는 $carList 의 index 값 (차 번호 대신 index 사용하는 중)
                    $car_data = $result01[$iiday2][$car_count];
                    $result01_row[$iiday2][$car_count] = count($car_data);
                    if (count($car_data) > 0) {
                        for ($idx = 0; $idx < count($car_data); $idx++) {
                            $usename1[$iiday2][$car_count][$n] = $car_data[$idx]['membername'];
                            $contents1[$iiday2][$car_count][$n] = $car_data[$idx]['contents'];
                            $contents1[$iiday2][$car_count][$n] = utf8_strcut($contents1[$iiday2][$car_count][$idx],15,'..');

                            $endtime1[$iiday2][$car_count][$n] = $car_data[$idx]['endtime'];
                            $updateuser1[$iiday2][$car_count][$n] =$car_data[$idx]['updateuser'] ;
                            $carno1[$iiday2][$car_count][$n] = $car_data[$idx]['carno'];
                            $regno1[$iiday2][$car_count][$n] = $car_data[$idx]['no'];

                            $n++;
                        }
                    }

                    $DateListsep = explode("-",$day1[$ilday2]);
                    $tday[$iiday2] = week_day($DateListsep[0],$DateListsep[1],$DateListsep[2]);
                    $holy_sc[$iiday2] = holy($DateListsep[0]."-".$DateListsep[1]."-".$DateListsep[2]);
                }
            }

			if ($start_m == "12") {
				$start_Y=$start_y+1;
				$start_m2="01";
				$start_m2=sprintf('%02d',$start_m2);
			} else {
				$start_m2=$start_m+1;
				$start_m2=sprintf('%02d',$start_m2);
			}

            $data_json2 = array();
            $start_m = sprintf('%02d', $start_m);
			for ($iday = 1; $iday <= $date3 - 3; $iday++) {
				if($start_m == "12") {
					$day2[$iday] = sprintf("%04d-%02d-%02d",$start_Y,$start_m2,$iday);	
				} else {
					$day2[$iday] = sprintf("%04d-%02d-%02d",$start_y,$start_m2,$iday);	
				}
				//$n_color++;
				//include "../../line_color_h.php";
                for ($car_count2 = "0"; $car_count2 < $Car_Row; $car_count2++) {
                    $j = 0;
                    /* 기존 쿼리
                    $query02 = "select * from schedule_car_tbl where sdate <= '$day2[$iday]' and edate >= '$day2[$iday]' and carno='$CarCode[$car_count2]' order by carno asc";
                    //echo $query01."<br>";
                    $result02 = mysql_query($query02,$db);
                    $result02_row[$iday][$car_count2]=mysql_num_rows($result02);
                    if(mysql_num_rows($result02) > 0) {  /// 사용종료일
                        while($result_row_02 = mysql_fetch_array($result02)) {
                            $usename2[$iday][$car_count2][$j] = $result_row_02[membername];
                            $contents2[$iday][$car_count2][$j] = $result_row_02[contents];
                            $contents2[$iday][$car_count2][$j] = utf8_strcut($contents2[$iday][$car_count2][$j],15,'..');

                            $endtime2[$iday][$car_count2][$j] = $result_row_02[endtime];
                            $updateuser[$iday][$car_count2][$j] =$result_row_02[updateuser] ;
                            $carno2[$iday][$car_count2][$j] = $result_row_02[carno];
                            $regno2[$iday][$car_count2][$j]	 = $result_row_02 [no];
                            $j++;
                        }
                    }
                    */

                    /* 2초 이상 소요 주석처리
                    $arrayData = array(
                        'ActionMode' => 'baron_schedule_car',
                        'requestType' => 'select_schedule_car_today',
                        'data_json' => array(
                            'today' => $day2[$iday],
                            'carno' => $CarCode[$car_count2],
                            'orderby' => 'order by no asc'
                        )
                    );

                    // apiLibrary 호출
                    $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
                    $result02 = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

                    $result02_row[$iday][$car_count2] = count($result02);
                    if (count($result02) > 0) {
                        for ($idx = 0; $idx < count($result02); $idx++) {
                            $usename2[$iday][$car_count2][$j] = $result02[$idx]['membername'];
                            $contents2[$iday][$car_count2][$j] = $result02[$idx]['contents'];
                            $contents2[$iday][$car_count2][$j] = utf8_strcut($contents2[$iday][$car_count2][$j],15,'..');

                            $endtime2[$iday][$car_count2][$j] = $result02[$idx]['endtime'];
                            $updateuser[$iday][$car_count2][$j] =$result02[$idx]['updateuser'] ;
                            $carno2[$iday][$car_count2][$j] = $result02[$idx]['carno'];
                            $regno2[$iday][$car_count2][$j]	 = $result02[$idx]['no'];

                            $j++;
                        }
                    }

                    $DateListsep = explode("-",$day2[$iday]);
                    $tday[$iday] = week_day($DateListsep[0],$DateListsep[1],$DateListsep[2]);
                    $holy_sc[$iday] = holy($DateListsep[0]."-".$DateListsep[1]."-".$DateListsep[2]);
                    */

                    // 한번만 요청하기 위해 데이터 수집
                    array_push($data_json2, array(
                        'today' => $day2[$iday],
                        'carno' => $CarCode[$car_count2],
                        'orderby' => 'order by no asc'
                    ));
				}
			}

            $arrayData = array(
                'ActionMode' => 'baron_schedule_car',
                'requestType' => 'select_schedule_car_today_foreach',
                'data_json' => $data_json2
            );

            $hanmacResult = $crypto->sendEncryptedRequest('http://erp.hanmaceng.co.kr/apiLibrary/', $arrayData);
            $result02 = $hanmacResult->data = json_decode(json_encode($hanmacResult->data), true);

            // 한번에 응답받은 데이터로 변수 초기화
            for ($iday = 1; $iday <= $date3 - 3; $iday++) {
                if($start_m == "12") {
                    $day2[$iday] = sprintf("%04d-%02d-%02d",$start_Y,$start_m2,$iday);
                } else {
                    $day2[$iday] = sprintf("%04d-%02d-%02d",$start_y,$start_m2,$iday);
                }

                for ($car_count2 = "0"; $car_count2 < $Car_Row; $car_count2++) {
                    $j = 0;
                    // $iday 는 일자 숫자값 (1~31)
                    // $car_count2 는 $carList 의 index 값 (차 번호 대신 index 사용하는 중)
                    $car_data = $result02[$iday][$car_count2];
                    $result02_row[$iday][$car_count2] = count($car_data);
                    if (count($car_data) > 0) {
                        for ($idx = 0; $idx < count($car_data); $idx++) {
                            $usename2[$iday][$car_count2][$j] = $car_data[$idx]['membername'];
                            $contents2[$iday][$car_count2][$j] = $car_data[$idx]['contents'];
                            $contents2[$iday][$car_count2][$j] = utf8_strcut($contents2[$iday][$car_count2][$j],15,'..');

                            $endtime2[$iday][$car_count2][$j] = $car_data[$idx]['endtime'];
                            $updateuser[$iday][$car_count2][$j] =$car_data[$idx]['updateuser'] ;
                            $carno2[$iday][$car_count2][$j] = $car_data[$idx]['carno'];
                            $regno2[$iday][$car_count2][$j]	 = $car_data[$idx]['no'];

                            $j++;
                        }
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