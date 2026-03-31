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
	//include "../util/OracleClass.php";


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
	class ScheduleMeetingroomLogic {
		var $smarty;
		//var $oracle;

		function ScheduleMeetingroomLogic($smarty)
		{
			$this->smarty=$smarty;
			//$this->oracle=new OracleClass($smarty);
		}


		//============================================================================
		// 배차신청 작성 logic
		//============================================================================
		function InsertPage()
		{
			global $db;
			global $mode, $memberID, $today,$devicecode;

			$roomList = array();
			$memberID = strtoupper($memberID);	//2022-12-01 정명준. 신혜영 책임이 사번을 소문자로 입력함. 어찌했는지는 모르것고, 소문자면 대문자로 치환하도록 수정.

			$sql2 ="select a.*,b.Name as GroupName,b.Description as GroupDescription from
			(
				select * from
				(
					select * from member_tbl where MemberNo='$memberID'
				)a1 left JOIN
				(
					select * from systemconfig_tbl where SysKey='PositionCode'
				)a2 on a1.RankCode = a2.code

			) a left JOIN
			(
				select * from systemconfig_tbl where SysKey='GroupCode'
			)b on a.GroupCode = b.code";

			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			if(mysql_num_rows($re2) > 0)
			{
					$GroupName=mysql_result($re2,0,"GroupName");
					$korName=mysql_result($re2,0,"korName");
					$Name=mysql_result($re2,0,"Name");

					$membername=$korName." ".$Name." [".$GroupName."]";
			}else{
				$sql3 = "select kor_name, rank_name, dept_name from total_member_tbl where Member_id='$memberID' limit 1 ";
				$re3 = mysql_query($sql3,$db);
				$membername= mysql_result($re3,0,"kor_name")." ".mysql_result($re3,0,"rank_name")." [".mysql_result($re3,0,"dept_name")."]";
			}
			$this->smarty->assign('membername',$membername);

			if($stime == "") { $stime = "9"; }
			$this->smarty->assign('stime',$stime);

			if($etime == "") { $etime = "18"; }
			$this->smarty->assign('etime',$etime);

			$hours = array();
			for($i=6; $i<24; $i += 0.5 ){
				array_push( $hours, array($i, fmod($i, 1)) );
			}
			for($i=0; $i<6; $i += 0.5 ){
				array_push( $hours, array($i, fmod($i, 1)) );
			}
			//print_r($hours);
			$this->smarty->assign('hours',$hours);
 
			$sql = "select Code, Name, Description, para1 from systemconfig_tbl where SysKey='meetingroom' order by orderno";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)){
				array_push($roomList,$re_row);
			}

			$this->smarty->assign('roomList',$roomList);

			$this->smarty->assign('sdate',$today);
			$this->smarty->assign('edate',$today);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('devicecode',$devicecode);
			$this->smarty->assign("page_action","schedule_meetingroom_controller.php");

			$this->smarty->display("intranet/common_contents/work_meetingroom/meetingroom_input_mvc.tpl");
		}


		//============================================================================
		// 배차신청 저장 logic (Insert)
		//============================================================================
		function InsertAction()
		{

			global $db;
			global $mode, $memberID, $today;
			global $membername, $contents, $devicecode;
			global $sdate, $edate, $stime, $etime;

			//updatedate 에 넣을 작성일자 생성
			$nowdatetime = date("Y-m-d H:i:s");
			$memberID = strtoupper($memberID);	//2022-12-01 정명준. 신혜영 책임이 사번을 소문자로 입력함. 어찌했는지는 모르것고, 소문자면 대문자로 치환하도록 수정.

			$nowdate = date("Y-m-d H").":00:00";										// 작성일자 (년월일시)
			$oneHourDate = date("Y-m-d H",strtotime("+1 hours")).":00:00";	// 작성일자 (년월일시 + 1시간)
			$rentsdate = $sdate." ".sprintf("%02d",$stime).":00:00";					// 차량 대여 일자 (년월일시)

			//문자 발송 상태 초기값 N
			$sms_send = "N";

			//자량 대여 일자가 작성일자에서 1시간 내이면 문자 바로 발송
			if($nowdate <= $rentsdate && $oneHourDate >= $rentsdate || $nowdate >= $rentsdate){
				$sms_send = "Y";
			}

			$sql="insert into schedule_meetingroom_tbl (membername,memberno,contents,devicecode,devicename,sdate,edate,stime,etime,updatedate,updateuser,sms_send) values";
			$sql=$sql."('$membername','$memberID','$contents','$devicecode','$devicename','$sdate','$sdate','$stime','$etime','$today','$memberID','$sms_send')";
			$dbResult = mysql_query($sql,$db);

			if( $sdate != $edate ){
				$insert_date = $sdate;
				while($insert_date != $edate){
					$insert_date = date("Y-m-d", strtotime($insert_date."+1day"));
					$sql="insert into schedule_meetingroom_tbl (membername,memberno,contents,devicecode,devicename,sdate,edate,stime,etime,updatedate,updateuser,sms_send) values";
					$sql=$sql."('$membername','$memberID','$contents','$devicecode','$devicename','$insert_date','$insert_date','$stime','$etime','$today','$memberID','$sms_send')";
					$dbResult = mysql_query($sql,$db);
				}
			}

			/*
			if($dbResult == true && $sms_send == "Y"){
				$sql2 = "SELECT Mobile FROM member_tbl WHERE MemberNo = '$memberID'";
				$re2 = mysql_query($sql2,$db);
				$renum2 = mysql_num_rows($re2);

				$RecieveNumber = mysql_result($re2,0,'Mobile');

				if($RecieveNumber != '') {
					$Message="[회의실 예약 안내]\n";
					$Message.=$stime."시에 회의실이 예약되어 있습니다.\n 회의실 예약시간을 준수해 주시기 바라며, 다음 이용자를 위해 깨끗한 이용 및 정리정돈을 부탁드립니다. 감사합니다.";

					///////////오라클 DB 연결////////////
					$SendNumber='0264888076';

					$Message=ICONV("UTF-8","EUC-KR//IGNORE",$Message);

					if(strlen($Message) > 85){	//90까지는 되기는함.
						$type = 'LMS';
					}else{
						$type = 'SMS';
					}

					$prosql ="BEGIN usp_sms_send_iu( '$RecieveNumber', '$SendNumber', '$Message', '$type' ); END;";
					$this->oracle->ProcedureExcuteQuery($prosql);
				}
				else{
					echo "<script>alert('사용자 핸드폰 번호가 확인이 되지 않아 문자 발송에 실패하였습니다.');history.go(-1);</script>";
					exit;
				}
			}

			if ($dbResult) {
				//$this->sendSMS("insert");
			}
			*/


			$MoveURL="schedule_meetingroom_controller.php?ActionMode=view&memberID=$memberID&devicecode=$devicecode";
			$this->smarty->assign('target', "opener");
			$this->smarty->assign('MoveURL', $MoveURL);
			$this->smarty->display("intranet/move_page.tpl");

		}


		//============================================================================
		// 배차신청 읽기/수정 logic
		//============================================================================
		function UpdateReadPage()
		{
			global $db;
			global $mode,$no,$currentPage;
			global $memberID,$techNo,$now_day,$devicecode;

			$roomList = array();
			$memberID = strtoupper($memberID);	//2022-12-01 정명준. 신혜영 책임이 사번을 소문자로 입력함. 어찌했는지는 모르것고, 소문자면 대문자로 치환하도록 수정.

			$sql = "select * from systemconfig_tbl where SysKey='meetingroom' order by orderno";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)){
				array_push($roomList,$re_row);
			}

			$this->smarty->assign('roomList',$roomList);

			$hours = array();
			for($i=6; $i<24; $i += 0.5 ){
				array_push( $hours, array($i, fmod($i, 1)) );
			}
			for($i=0; $i<6; $i += 0.5 ){
				array_push( $hours, array($i, fmod($i, 1)) );
			}
			$this->smarty->assign('hours',$hours);


			$sql1 = "select * from schedule_meetingroom_tbl where no='$no'";
			$re1 = mysql_query($sql1,$db);
			while($re_row1 = mysql_fetch_array($re1))
			{
				$memberno = $re_row1[memberno];
				$membername = $re_row1[membername];
				$stime = $re_row1[stime];
				$etime = $re_row1[etime];
				$devicename = $re_row1[devicename];
				$devicecode = $re_row1[devicecode];
				$contents = $re_row1[contents];
				$sdate = $re_row1[sdate];
				$edate = $re_row1[edate];
				$updatedate = $re_row1[updatedate];
				$updateuser = $re_row1[updateuser];
			}

			$this->smarty->assign('no',$no	);
			$this->smarty->assign('memberno',$memberno	);
			$this->smarty->assign('memberID',$memberID	);
			$this->smarty->assign('mode',$mode	);
			$this->smarty->assign('membername',$membername);
			$this->smarty->assign('stime',$stime);
			$this->smarty->assign('etime',$etime);
			$this->smarty->assign('devicename',$devicename);
			$this->smarty->assign('devicecode',$devicecode);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('sdate',$sdate);
			$this->smarty->assign('edate',$edate);
			$this->smarty->assign('updatedate',$updatedate);
			$this->smarty->assign('updateuser',$updateuser);
			$this->smarty->assign('devicecode',$devicecode);

			$this->smarty->assign("page_action","schedule_meetingroom_controller.php");

			$this->smarty->display("intranet/common_contents/work_meetingroom/meetingroom_input_mvc.tpl");
		}


		//============================================================================
		// 배차신청 저장 logic (Update)
		//============================================================================
		function UpdateAction()
		{
			global $db;
			global $mode, $memberID, $no, $today;
			global $today, $membername, $contents;
			global $devicename,$devicecode, $sdate, $edate, $stime, $etime;

			//updatedate 에 넣을 작성일자 생성
			$nowdatetime = date("Y-m-d H:i:s");
			$memberID = strtoupper($memberID);	//2022-12-01 정명준. 신혜영 책임이 사번을 소문자로 입력함. 어찌했는지는 모르것고, 소문자면 대문자로 치환하도록 수정.

			$nowdate = date("Y-m-d H").":00:00";										// 작성일자 (년월일시)
			$oneHourDate = date("Y-m-d H",strtotime("+1 hours")).":00:00";	// 작성일자 (년월일시 + 1시간)
			$rentsdate = $sdate." ".sprintf("%02d",$stime).":00:00";					// 차량 대여 일자 (년월일시)

			//문자 발송 상태 초기값 N
			$sms_send = "N";

			//자량 대여 일자가 작성일자에서 1시간 내이면 문자 바로 발송
			if($nowdate <= $rentsdate && $oneHourDate >= $rentsdate || $nowdate >= $rentsdate){
				$sms_send = "Y";
			}

			$sql  = "update schedule_meetingroom_tbl set membername='$membername', contents='$contents',devicecode='$devicecode', devicename='$devicename', sdate='$sdate', edate='$sdate', stime='$stime', etime='$etime', updatedate='$today', updateuser='$memberID',sms_send='$sms_send' where no='$no' and is_del = '1'";
			//echo $sql;
			$dbResult = mysql_query($sql,$db);
			/*

			if($dbResult == true && $sms_send == "Y"){
				$sql2 = "SELECT Mobile FROM member_tbl WHERE MemberNo = '$memberID'";
				$re2 = mysql_query($sql2,$db);
				$renum2 = mysql_num_rows($re2);

				$RecieveNumber = mysql_result($re2,0,'Mobile');

				if($RecieveNumber != '') {
					$Message="[회의실 예약 안내]\n";
					$Message.=$stime."시에 회의실이 예약되어 있습니다.\n 회의실 예약시간을 준수해 주시기 바라며, 다음 이용자를 위해 깨끗한 이용 및 정리정돈을 부탁드립니다. 감사합니다.";

					///////////오라클 DB 연결////////////
					$SendNumber='0264888076';

					$Message=ICONV("UTF-8","EUC-KR//IGNORE",$Message);

					if(strlen($Message) > 85){	//90까지는 되기는함.
						$type = 'LMS';
					}else{
						$type = 'SMS';
					}

					$prosql ="BEGIN usp_sms_send_iu( '$RecieveNumber', '$SendNumber', '$Message', '$type' ); END;";
					$this->oracle->ProcedureExcuteQuery($prosql);
				}
				else{
					echo "<script>alert('사용자 핸드폰 번호가 확인이 되지 않아 문자 발송에 실패하였습니다.');history.go(-1);</script>";
					exit;
				}
			}


			if ($dbResult) {
				//$this->sendSMS("update");
			}
			*/

			$MoveURL="schedule_meetingroom_controller.php?ActionMode=view&memberID=$memberID&devicecode=$devicecode";
			$this->smarty->assign('target', "opener");
			$this->smarty->assign('MoveURL', $MoveURL);
			$this->smarty->display("intranet/move_page.tpl");

		}


		//============================================================================
		// 배차신청 삭제 logic (Delete)
		//============================================================================
		function DeleteAction()
		{

			global $db;
			global $no,$mode,$memberID;

			//$sql = "delete from schedule_meetingroom_tbl where no=$no";
			$sql = "update schedule_meetingroom_tbl set is_del='0' where no='$no'";
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
			global $date1,$date2,$date3,$last_page,$RoomCode,$RoomName,$tab_index,$devicecode;
			
			if($_SESSION['Manager_auth'])//총무
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);

			$sql="select * from member_tbl where MemberNo='$memberID'";
			$re = mysql_query($sql,$db);
			if( mysql_num_rows($re) > 0 ){
				$Certificate = mysql_result($re,0,"Certificate");

				if($Certificate)
				{
					if(strpos($Certificate,"총무") !== false) { $Auth=true; }
				}
				if( mysql_result($re,0,"GroupCode") == '11' ){
					//$this->smarty->assign('Auth',true );
				}
			}

			if(!$start_y && !$start_m ){
				$date1 = date("Y");  /// 오늘
				$date2 = date("m");  /// 오늘
				$date3 = date("d");  /// 오늘
			}else {
				$date1 = $start_y;  /// 오늘
				$date2 = $start_m;  /// 오늘
				$date3 = date("d");  /// 오늘
			}
			$date_now = date("Y-m-d");  /// 오늘
			$uyear = date("Y")+3;  /////최대 보이는 년도-1

			if($start_y == "") { $start_y = $date1; }
			if($start_m == "") { $start_m = $date2; }

			$date = $year."-".$month;
			$ilastday= month_lastday($year,$month);
 			$ilastday=date( "t", mktime( 0, 0, 0, $month, 1, $year ) );

			//grooupCode 추가
			$sql3 = "select groupCode from member_tbl where MemberNo = '$memberID' ";
			$re3 = mysql_query($sql3,$db);
			
			$re3_data = mysql_fetch_assoc($re3);
			$groupCode = $re3_data['groupCode'];
			$this->smarty->assign('groupCode',$groupCode);
			
			if($devicecode=="")
				$groupCode == '3' ? $devicecode ="207" :  $devicecode ="103" ; 
			
			$roomList = array();

			$Room_Row="0";

			$sql="select * from systemconfig_tbl where SysKey='meetingroom' order by orderno";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($roomList,$re_row);

				$RoomCode[$Room_Row] = $re_row[Code];
				$RoomTemp = split(']',$re_row[Name]);
				$RoomPosition[$Room_Row] = $RoomTemp[0]."]";
				$Roomlayer[$Room_Row] = $RoomTemp[1];
				$RoomName[$Room_Row] = $re_row[Name];
				$RoomSubName[$Room_Row] = str_replace("]", "]<br>", $re_row[Name]);
				$RoomOption[$Room_Row] = $re_row[Note];
				$tab_value[$Room_Row] = $Room_Row;

				$Room_Row++;
			}
			//print_r($roomList);

			$this->smarty->assign('today',$today);
			$this->smarty->assign('date1',$date1);
			$this->smarty->assign('date2',$date2);
			$this->smarty->assign('date3',$date3);
			$this->smarty->assign('date_now',$date_now);
			$this->smarty->assign('Room_Row',$Room_Row);
			$this->smarty->assign('room_use',$room_use);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('RoomPosition',$RoomPosition);
			$this->smarty->assign('Roomlayer',$Roomlayer);
			$this->smarty->assign('RoomName',$RoomName);
			$this->smarty->assign('RoomSubName',$RoomSubName);
			$this->smarty->assign('RoomCode',$RoomCode);
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('devicecode',$devicecode);
			$this->smarty->assign('RoomOption',$RoomOption);


			$this->smarty->assign('start_y',$start_y);
			$this->smarty->assign('start_m',$start_m);
			$this->smarty->assign('ilastday',$ilastday);
			$this->smarty->assign('roomList',$roomList);

			$hour = date("H");
			if( 30 <= date("i") ){
				$hour = $hour + 0.5;
			}
			/////////////////오늘의 배차현황/////////////////////////////////////////////////////////////////////////////////////////
			for ($k=0;$k<$Room_Row;$k++)
			{
				$sql3 = "select * from schedule_meetingroom_tbl where  sdate <= '$today' and edate >= '$today' and devicecode='$RoomCode[$k]' and ( stime <= $hour and $hour < etime ) and is_del like 1";
				$re3 = mysql_query($sql3,$db);
					$use_row = mysql_num_rows($re3);

					if($use_row>"0")
					{
						$room_use[mysql_result($re3,0,"devicecode")]="yes";
					}
			}
						$this->smarty->assign('room_use',$room_use);


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

			$sql2 = "select * from schedule_meetingroom_tbl where  (('$Sday' <= sdate and sdate <= '$Eday' ) or ('$Sday' <= edate and edate <= '$Eday' )) and devicecode='$devicecode' and is_del like 1 order by sdate asc, stime asc";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				$status = $re_row2[status];
				if($status == "0")
				{
					$re_row2[status_name]="[예약중]";
				}else if($status == "1")
				{
					$re_row2[status_name]="[대여중]";
				}else if($status == "2")
				{
					$re_row2[status_name]="[반납완료]";
				}


				array_push($query_data2,$re_row2);
			}


			$query_data3 = array();
			$currentDate ="";
			$holy_sc[0]="";

			$dateitem=split("-",$DateList[0]);

			$currentDate=$DateList[0];

			$RoomReg = array();

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
					$stime=$query_data2[$Count2][stime];
					$etime=$query_data2[$Count2][etime];
					$devicename=$query_data2[$Count2][devicename];
					$contents=$query_data2[$Count2][contents];
					$sdate=$query_data2[$Count2][sdate];
					$edate=$query_data2[$Count2][edate];

					$start_day=$query_data2[$Count2][sdate];
					$end_day=$query_data2[$Count2][edate];
					$interval=  $this->date_diff($end_day,$start_day);

					$status_name=$query_data2[$Count2][status_name];

					for($Count3=0;$Count3<=$interval;$Count3++)
					{
						$NextDay[$Count2]=date("Y-m-d",strtotime ("+{$Count3} days {$start_day}"));

						if($DateList[$Count] == $NextDay[$Count2])
						{
							// 기술개발센터 회의실 사용일때 부서명 제거해서 보여주기
							if($devicecode > 200 ){
								// $membername = str_replace(" [기술개발센터]","",$membername); 
								$strName = explode("[",$membername);
								$membername = $strName[0]; 
							}
							$RoomReg[$MemberCount][$Count][0]=$membername;
							$RoomReg[$MemberCount][$Count][1]=$contents;
							$RoomReg[$MemberCount][$Count][2]=sprintf("%d",$stime);
							$RoomReg[$MemberCount][$Count][3]=sprintf("%d",$etime);
							$RoomReg[$MemberCount][$Count][4]=$status_name;
							$RoomReg[$MemberCount][$Count][5]=$no;
							$RoomReg[$MemberCount][$Count][6]=sprintf("%02d",fmod($stime, 1)*60);
							$RoomReg[$MemberCount][$Count][7]=sprintf("%02d",fmod($etime, 1)*60);
						}
					}
					$MemberCount++;
				}
			}
			//print_r($RoomReg);
			$this->smarty->assign('Auth',$Auth);
			$this->smarty->assign('RoomReg',$RoomReg);
			$this->smarty->assign('dateitem',$dateitem);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('stime',$stime);
			$this->smarty->assign('etime',$etime);
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

			$this->smarty->assign("page_action","schedule_meetingroom_controller.php");
			$this->smarty->display("intranet/common_contents/work_meetingroom/meetingroom_contents_mvc.tpl");
		}
		//============================================================================
		// 시간체크
		//============================================================================
		function CheckTime(){
			global $db;
			extract($_REQUEST);
			//print_r($_REQUEST);

			$s_h = floor($stime);
			if($s_h < 10){
				$s_h = '0'.$s_h;
			}
			$s_m = '00';
			if( fmod($stime, 1) != 0 ){
				$s_m = '30';
			}

			$e_h = floor($etime);
			if($e_h < 10){
				$e_h = '0'.$e_h;
			}
			$e_m = '00';
			if( fmod($etime, 1) != 0 ){
				$e_m = '30';
			}

			if($etime < 10){
				$etime = '0'.$etime;
			}

			if($edate == null or $edate == ''){
				$edate = $sdate;
			}

			$sql1 = "
				select
					*
				from
					schedule_meetingroom_tbl
				where
					devicecode = '$devicecode'
					and concat( edate, IF(etime < 10, ' 0', ' '), TRUNCATE(etime, 0), ':', if( etime%1 = 0, '00', '30'),':00') > '$sdate $s_h:$s_m:00'
					and concat( sdate, IF(stime < 10, ' 0', ' '), TRUNCATE(stime, 0), ':', if( stime%1 = 0, '00', '30'),':00') < '$edate $e_h:$e_m:00'
					and is_del like 1
			";
			if (!empty($no)) {
				$sql1 .= "and no != $no";
			}

			$re1 = mysql_query($sql1,$db);
			if(mysql_num_rows($re1) > 0){
				echo "used";
			}
		}


		function date_diff($date1, $date2){
			$_date1 = explode("-",$date1);
			$_date2 = explode("-",$date2);

			$tm1 = mktime(0,0,0,$_date1[1],$_date1[2],$_date1[0]);
			$tm2 = mktime(0,0,0,$_date2[1],$_date2[2],$_date2[0]);

			return ($tm1 - $tm2) / 86400;
		}

		//회의실 예약시 문자발송
		function sendSMS($cmd) {
			global $db;
			extract($_REQUEST);
			header("Content-Type: text/html; charset=utf-8");

			$_6FloorVideoMeetingRoomCode = 1006;
			if ($devicecode != $_6FloorVideoMeetingRoomCode) {
				return;
			}

			$sql  = " Select ";
			$sql .= "     x.*, ";
			$sql .= "     ConCat(x.korName, ' ', x.POSITION_NAME, '[', x.GROUP_NAME, ']') USER_NAME ";
			$sql .= " From ";
			$sql .= "     ( ";
			$sql .= "         Select ";
			$sql .= "             a.*, ";
			$sql .= "             (Select x.Name From systemconfig_tbl x Where SysKey = 'PositionCode' And x.Code = a.RankCode) POSITION_NAME, ";
			$sql .= "             (Select x.Name From systemconfig_tbl x Where SysKey = 'GroupCode' And x.Code = a.GroupCode) GROUP_NAME ";
			$sql .= "         From ";
			$sql .= "             member_tbl a ";
			$sql .= "         Where a.MemberNo = '$memberID' ";
			$sql .= "     ) x ";
			$resource = mysql_query($sql, $db);

			if (!$resource) {
				echo "mysql_query(member resource) error";
				return;
			}

			$userInfo = array();
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				array_push($userInfo, $row);
			}

			$CRLF = "\r\n";
			$title = "";
			if ($cmd == "insert") {
				$title = "화상회의 회의실이 등록 되었습니다.";
			} else if ($cmd == "update") {
				$title = "화상회의 회의실이 수정 되었습니다.";
			}

			$message = $title . $CRLF;
			$message .= "신청자 : " . $userInfo[0]["USER_NAME"] . $CRLF;
			$message .= "일시 : ($sdate $stime~$etime)";
			$message = $this->HangleEncodeUTF8_EUCKR($message);

			if (mb_strlen($message) > 1998) {
				echo '내용이 너무 길어 보낼 수 없습니다.';
				return;
			}

			if (mb_strlen($message) > 90) {
				$type = 'LMS';
			} else {
				$type = 'SMS';
			}

			$receiver =  array(
				'이선영' => '01049247411',
				'김지은' => '01043753001'
			);
			$sendNumber = "0264888000";
			foreach ($receiver as $receiverName => $receiverNumber) {
				$prosql ="BEGIN usp_sms_send_iu('".$receiverNumber."', '$sendNumber', '$message', '$type' ); END;";
				//echo $prosql;
				$this->oracle->ProcedureExcuteQuery($prosql);
			}
		}

		function HangleEncodeUTF8_EUCKR($item)
		{
			$result=trim(ICONV("UTF-8","EUC-KR",$item));
			return $result;
		}
	}
?>