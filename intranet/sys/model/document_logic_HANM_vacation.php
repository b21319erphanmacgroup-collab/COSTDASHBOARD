<?php
if(!($ActionMode=="CheckVacation" or $ActionMode=="CheckOverWork" or $ActionMode=="CancelOverWork" or $ActionMode=="CheckEditVacation" or $open_type == 'package')){
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
}

	/***************************************
	* 전자결재문서 작성
	****************************************/

	include "../inc/dbcon.inc";
	include "../inc/function_mysql.php";
//	include "../inc/dbconForMystation.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/function_intranet_v2.php";//181030 : 한맥ERP 프로젝트코드 작업 관련하여 추가 : by moon
	include "../../../person_mng/inc/vacationfunction.php";
	include "../../../person_mng/inc/vacationfunction_v3.php";
	include "../inc/function_add.php";

	if(strpos($FormNum, "HMF-5-") !== false || strpos($FormNum, "BRF-5-") !== false || $FormNum=="HMF-2-4" || $FormNum=="BRF-2-4" || $FormNum=="HMF-10-1" || $FormNum=="HMF-10-2"){
		include "../util/OracleClass.php";
	}



	extract($_POST);
	class DocumentLogic {
		var $smarty;
		var $oracle;
		function DocumentLogic($smarty)
		{
			global $memberID,$FormNum;
			if(strpos($FormNum, "HMF-5-") !== false || strpos($FormNum, "BRF-5-") !== false || $FormNum=="HMF-2-4" || $FormNum=="BRF-2-4"|| $FormNum=="HMF-10-1" || $FormNum=="HMF-10-2"){
				$this->oracle=new OracleClass($smarty);
			}

			$this->smarty=$smarty;
		}

		//============================================================================
		// 전자결재 문서보기
		//============================================================================
		function InsertPage()
		{
			include "../inc/approval_function.php";
			include "../util/CommonCodeList.php";
			extract($_REQUEST);

			global $db,$memberID,$ActionMode,$DocTitle,$DocSN;
			global $FormNum,$End_index,$Receive_index,$Now_Step,$menu_cmd;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5,$CompanyKind,$doc_status;
			global $TempValue,$TempValue2,$TempValue3;
			global $Detail_1;
			global $report_type, $dateto, $dept, $seq, $satis,$targetKind;	//전표관련 변수
			global $Site;//전자도서관 변수
			global $PJT_CODE,$DGREE,$WBS_CODE,$ORA_DEPTCODE,$ORA_CUSTCODE,$ORDER_CONTENTS, $mobile; //외주품의서 작성 변수; //외주품의서 작성 변수

			if(FN_DevConfirm($memberID)){
// 				$qq = "H18-영업-01";
// 				$NewProjectCode	= FN_projectToColumn($qq,'NewProjectCode');		//프로젝트코드 NewProjectCode
// 				echo $NewProjectCode;

				//echo $memberID;

			}

			if($memberID=="T03225"){
			/*
				$testcode="H17-AS-03";
				$Now_ProjectCode	= FN_projectToColumn($testcode,'oldProjectCode');		//프로젝트코드 oldProjectCode
				echo $Now_ProjectCode."<br>";
				echo strlen($Now_ProjectCode)."<br>";
				if(strlen($Now_ProjectCode) !="6")
				{
					$Now_ProjectCode="ZZZZZZ";
				}
				echo $Now_ProjectCode."<br>";
				*/
			}




			$DocSN=TempSerialNo2($memberID);
			if($report_type != ""){
				$FormNum = "HMF-5-".(int)$report_type;
			}

			$RG_Date = date('Y-m-d'); //기안일

				$sql =      " select a.korName as Name,a.GroupCode,b.Name as GroupName,a.Name as Position,a.RankCode as RankCode,a.ExtNo,a.Mobile,a.eMail, a.EntryDate		 ";
				$sql .= " from                                                                   ";
				$sql .= "	(                                                                    ";
				$sql .= "		select * from                                                    ";
				$sql .= "		(                                                                ";
				$sql .= "			select * from member_tbl where MemberNo = '$memberID'        ";
				$sql .= "		)a1 left JOIN                                                    ";
				$sql .= "		(                                                                ";
				$sql .= "			select * from systemconfig_tbl where SysKey='PositionCode'   ";
				$sql .= "		)a2 on a1.RankCode = a2.code                                     ";
				$sql .= "	                                                                     ";
				$sql .= "	) a left JOIN                                                        ";
				$sql .= "	(                                                                    ";
				$sql .= "		select * from systemconfig_tbl where SysKey='GroupCode'          ";
				$sql .= "	)b on a.GroupCode = b.code											 ";

				//echo $sql."<br>";
				$result=mysql_query($sql,$db);
				$row=mysql_fetch_array($result);
				$Name=$row[Name];
				$RG_Code=$row[GroupCode];
				$RG_Code = sprintf("%02d", $RG_Code);

				$GroupName=$row[GroupName];
				$MemberInfo=$row[Position];
				$ExtNo=$row[ExtNo];
				$eMail=$row[eMail];
				$EntryDate=$row[EntryDate];

				//문서별 기본표시내용 -------------------------------------------------------
				if($FormNum=="HMF-4-8" || $FormNum=="BRF-4-8" ){  //휴가계

					if(strpos($row[RankCode],"C") !== false) //임원(전무,상무,이사)
					{
						$now_vacation= "-";
					}else
					{
						$now_vacation=  $this->NowVacation($memberID);
					}

					if($Detail1 == "") { $Detail1=date('Y-m-d'); } //휴가시작일
					if($Detail2 == "") { $Detail2=date('Y-m-d'); } //휴가종료일

				}else if($FormNum=="HMF-4-7" || $FormNum=="BRF-4-7"){//근태사유서

					if($Detail_1[0] <> ""){$Dt1[0] = $Detail_1[0];}
					if($Detail_1[1] <> ""){$Dt1[1] = $Detail_1[1];}

					if($Dt1[0] == "") { $Dt1[0]=date('Y-m-d'); } //휴가시작일
					if($Dt1[1] == "") { $Dt1[1]=date('Y-m-d'); } //휴가종료일

					$this->smarty->assign('Dt1',$Dt1);

					//if(strpos($row[RankCode],"C") !== false) //임원(전무,상무,이사)
					//신규 직급 추가로 인한 버그 발생우려 :  선임 (E1C) : 20180209
					if(substr($row[RankCode], 0, 1)=="C") //임원(전무,상무,이사)
					{
						$Dt5[1]= "";
					}else
					{
						$Dt5[1]=$this->NowVacation($memberID);
					}
					$this->smarty->assign('Dt5',$Dt5);

					if($DocTitle=="경유" || $DocTitle=="교육" || $DocTitle=="업무")
					{
						$this->smarty->assign('display','');
					}
					else
					{
						$this->smarty->assign('display','none');
					}

				}else if($FormNum=="HMF-9-8" || $FormNum=="BRF-9-8"){//업무주차확인증

					if($Detail1 == "") { $Detail1=date('Y-m-d'); } //시작일
					if($Detail2 == "") { $Detail2=date('Y-m-d'); } //종료일

				}else if($FormNum=="HMF-9-1" || $FormNum=="BRF-9-1"){	//연장근무확인서

					if($Detail1 == "") { $Detail1=date("Y-m-d", mktime(0,0,0,date("m")  , date("d")-1, date("Y"))); } //연장근무일어제날짜로 나오게
					if($Detail2 == "") { $Detail2="19:00"; } //연장근무 시작시간
					if($Detail3 == "") { $Detail3="21:00"; } //연장근무 종료시간
					if($Dt5[0] == "") { $Dt5[0]="연장근무 미시작";} //연장근무 종료시간

					$this->smarty->assign('Dt5',$Dt5);

				}else if($FormNum=="HMF-2-4" || $FormNum=="BRF-2-4"){	//출장,배차신청서

					$Dt4[0] = date('Y-m-d');
					$Dt4[1]= date('Y-m-d');

					$Dt4[2]="당일";
					$Dt4[3]="개인차량";
					$Dt4[4]="09";
					$Dt4[5]="18";

					$SelectCar_data = array();
					//회사차량 선택-------------------------------------------------------
					$res_device = "select * from systemconfig_tbl where SysKey='bizcarno' order by orderno";
					// 선우현 DR 엑센트 6210 차량 출장배차 신청서에서 보이지 않게 처리 요청
					//$res_device ="select * from systemconfig_tbl where SysKey='bizcarno' and Code<>'6210' order by orderno";
						//echo $res_device."<br>";
						$re_device = mysql_query($res_device,$db);
						while($rec_device = mysql_fetch_array($re_device))
						{
							array_push($SelectCar_data,$rec_device);
						}

					$this->smarty->assign('Dt4',$Dt4);
					$this->smarty->assign('SelectCar_data',$SelectCar_data);

				}else if($FormNum=="HMF-9-2-s" || $FormNum=="HMF-4-5-s" || $FormNum=="BRF-9-2-s" || $FormNum=="BRF-4-5-s"){	//연장근무신청서[개인],휴일근로신청서[개인]

					if($Detail1 == "") { $Detail1=date("Y-m-d"); }
					if($Detail5 == "") { $Detail5="1"; }
					if($AttchFile == "") { $AttchFile="1"; }

					$this->smarty->assign('AttchFile',$AttchFile);

					$dailysql="select * from dallyproject_tbl where EntryTime like '$Detail1%' and MemberNo='$memberID'";
					//echo $dailysql."<br>";
					$dailyre = mysql_query($dailysql,$db);
					while($dailyre_row = mysql_fetch_array($dailyre)) {
						$tmpEntryPCode=$dailyre_row[EntryPCode];
						$tmpEntryPName=ProjectCode2Name($tmpEntryPCode);

					}
					$this->smarty->assign('ProjectCode',$tmpEntryPCode);
					$this->smarty->assign('ProjectName',$tmpEntryPName);

				}else if($FormNum=="HMF-9-2" || $FormNum=="BRF-9-2" ){	//연장근무신청서[팀장]
					if($Detail1 <> ""){
						$TempValue=$Detail1;
					}

					if($TempValue == ""){
						$Dt1[0]=date("Y-m-d");
					}else{
						$Dt1[0]=$TempValue;
					}

					if(holycheck($Dt1[0])=="weekday"){
							$Dt1[1]=1;
					}else{
							$Dt1[1]=2;
					}

					$MemberInfo2=$MemberInfo;
					$this->smarty->assign('MemberInfo2',$MemberInfo2);

					$MemberInfo=MemberNoToRankCode($memberID)."/n";

					$this->smarty->assign('Dt1',$Dt1);

					$FormLiist="FormNum='HMF-9-2-s' or FormNum='BRF-9-2-s'";

					$query_data = array();

					if($Dt1[1]=="1")
					{
						$sql = "select * from SanctionDoc_tbl where (".$FormLiist.") and Detail1='$Dt1[0]' and Detail3='$memberID' and RT_SanctionState like '%".$SANCTION_CODE."%' and (Detail5='$Dt1[1]' or Detail5='')";
					}else
					{
						$sql = "select * from SanctionDoc_tbl where (".$FormLiist.") and Detail1='$Dt1[0]' and Detail3='$memberID' and RT_SanctionState like '%".$SANCTION_CODE."%' and Detail5='$Dt1[1]'";
					}


					if($memberID=="M06505"){

						//echo $sql."<br>";
					}

					//echo $sql."<br>";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {
						//================================================================================
						// $re_row[MemberName] = MemberNo2Name($re_row[MemberNo])." ".$re_row[MemberInfo];
						// //$re_row[ProjectName] = ProjectCode2Name($re_row[ProjectCode])." [".$re_row[ProjectCode]."]";
						// $ProjectCodexx=change_XX($re_row[ProjectCode]);
						// $re_row[ProjectName] = ProjectCode2Name($ProjectCodexx)." [".$re_row[ProjectCode]."]";
						// $tmpDetail2=split("/n",$re_row[Detail2]);
						// $re_row[Dt4_1]=$tmpDetail2[0];
						// $re_row[Dt4_2]=$tmpDetail2[1];
						// array_push($query_data,$re_row);
						// $MemberInfo=$MemberInfo.MemberNoToRankCode($re_row[MemberNo])."/n";
						//================================================================================

						//================================================================================
						$re_row[MemberName] = MemberNo2Name($re_row[MemberNo])." ".$re_row[MemberInfo];
						//-----------------------------------------------------------------------------------------------------
						$re_NewProjectCode	= FN_projectToColumn($re_row[ProjectCode],'NewProjectCode');		//프로젝트코드 NewProjectCode
						$re_projectViewCode	= FN_projectToColumn($re_row[ProjectCode],'projectViewCode');		//프로젝트코드 ViewCode
						$re_ProjectNickname	= FN_projectToColumn($re_row[ProjectCode],'ProjectNickname');		//프로젝트코드 ProjectNickname
						//-----------------------------------------------------------------------------------------------------
						$re_row[ProjectName] = $re_ProjectNickname." [".$re_projectViewCode."]";
						$tmpDetail2=split("/n",$re_row[Detail2]);
						$re_row[Dt4_1]=$tmpDetail2[0];
						$re_row[Dt4_2]=$tmpDetail2[1];
						array_push($query_data,$re_row);
						$MemberInfo=$MemberInfo.MemberNoToRankCode($re_row[MemberNo])."/n";
						//================================================================================

					}
					$this->smarty->assign('query_data',$query_data);

				}else if($FormNum=="HMF-4-5" || $FormNum=="BRF-4-5"){	//휴일근무신청서[팀장]

					if($Detail1 == "") {
						$Detail1=date("Y-m-d");
					}

					$query_data = array();
					$sql = "select * from SanctionDoc_tbl where (FormNum='HMF-4-5-s' or FormNum='BRF-4-5-s') and RG_Code='$RG_Code' and Detail1='$Detail1' and Detail3='$memberID' and RT_SanctionState like '%".$SANCTION_CODE."%'";


					//echo $sql."<br>";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {
						$re_row[MemberName] = MemberNo2Name($re_row[MemberNo])." ".$re_row[MemberInfo];
						$re_row[ProjectName] = ProjectCode2Name($re_row[ProjectCode])." [".$re_row[ProjectCode]."]";
						array_push($query_data,$re_row);
					}
					$this->smarty->assign('query_data',$query_data);

				}else if($FormNum=="HMF-2-3" || $FormNum=="BRF-2-3"){	//비품사용신청서

					$query_data = array();
					$sql="select * from systemconfig_tbl where SysKey='bizdevice' and Name not like '%회의실%' order by code";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {
							array_push($query_data,$re_row);
					}
					$this->smarty->assign('query_data',$query_data);

					if($Detail2 == "") { $Detail2=date('Y-m-d'); }
					if($Detail3 == "") { $Detail3= date("Y-m-d", strtotime(date("Y-m-d")." +1 days")); }
					if($Detail4 == "") { $Detail4=1; }

				}else if($FormNum=="HMF-9-3" || $FormNum=="BRF-9-3"){	//회의실 사용신청서

					$Dt1[0] = $Dt1[0] == ""?date('Y-m-d'):$Dt1[0];
					$Dt1[1] = $Dt1[1] == ""?"09":$Dt1[1];
					$Dt1[2] = $Dt1[2] == ""?date('Y-m-d'):$Dt1[2];
					$Dt1[3] = $Dt1[3] == ""?"18":$Dt1[3];
					$this->smarty->assign('Dt1',$Dt1);

					$RG_Date = $RG_Date == ""?date('Y-m-d'):$RG_Date;
					$Detail2 = $Detail2 == ""?"6층회의실":$Detail2;

				}else if($FormNum=="HMF-9-4" || $FormNum=="BRF-9-4"){	//명함신청서
					/*
					if($Detail1 == "") { $Detail1=$GroupName; } //부서명
					if($Detail2 == "") { $Detail2=$MemberInfo."/".MemberNo2Name($memberID); } //직급/이름
					*/
					if($Dt1[0] == "") { $Dt1[0]=$GroupName; } //부서명
					if($Dt2[0] == "") { $Dt2[0]=$MemberInfo."/".MemberNo2Name($memberID); } //직급/이름
					if($Dt5[1] == "") { $Dt5[1]="200"; } //신청부수
					$this->smarty->assign('Dt1',$Dt1);
					$this->smarty->assign('Dt2',$Dt2);
					$this->smarty->assign('Dt5',$Dt5);

				}else if($FormNum=="HMF-9-6" || $FormNum=="BRF-9-6"){	//재직증명발급신청서
					if($Detail1 == "") { $Detail1="1"; }
					if($Detail2 == "") { $Detail2=date('Y-m-d'); }
					if($Detail3 == "") { $Detail3=$GroupName; } //부서명
					if($Detail4 == "") { $Detail4=$MemberInfo."/".MemberNo2Name($memberID); } //직급/이름

				}else if($FormNum=="HMF-9-7" || $FormNum=="BRF-9-7"){	//원청징수발급신청서

				}else if($FormNum=="HMF-4-9" || $FormNum=="BRF-4-9" ){	//연차휴가 변경계획서

					$nowdate=date('Y-m-d');
					$Detail2=date('Y-m-d');

					if( substr( $row[RankCode], 0, 1 ) == 'C' ){ //임원(전무,상무,이사)
						$Detail4= "";
					}else{
						$Detail4=$this->NowVacation($memberID);
					}

					$state_data = array();
					$state_sql="select * from userstate_tbl where MemberNo = '$memberID' and start_time >= '$nowdate' and ( state ='1' or state = '30' or state = '31' ) order by start_time";
					//echo $state_sql."<br>";
					$re_state = mysql_query($state_sql,$db);
					while($re_state_row = mysql_fetch_array($re_state))
					{
						array_push($state_data,$re_state_row);
					}

					$this->smarty->assign('state_data',$state_data);
				}elseif( $FormNum=="BRF-4-10" || $FormNum=="HMF-4-10" ){
					extract($_REQUEST);
					$today = date('Y-m-d');
					$this->smarty->assign('today',$today);
					if( substr( $row[RankCode], 0, 1 ) == 'C' ){ //임원(전무,상무,이사)
						$Detail4= "";
					}else{
						$Detail4=$this->NowVacation($memberID);
						$Detail4 = '기존 : '.$Detail4.', 변경 : '.$Detail4;
					}

					//echo date('Y').substr( $EntryDate, 4, 6 );

					if( $EntryDate < '2017-06-01' ){
						$end_date = date('Y').'-12-31';
					}elseif( date('Y').substr( $EntryDate, 4, 6 ) < $today ){
						$end_date = date("Y-m-d", strtotime((date('Y')+1).substr( $EntryDate, 4, 6 )." -1 day"));
					}else{
						$end_date = date("Y-m-d", strtotime(date('Y').substr( $EntryDate, 4, 6 )." -1 day"));
					}

					$this->smarty->assign('end_date',$end_date);

					$query_data = array();
					$sql = "select * from userstate_tbl where MemberNo = '$memberID' and state in ( 1, 18, 30, 31 ) and start_time >= '$today' order by start_time, state";
					//echo $sql."<br>";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {
						$split_note = split("/n",$re_row['note']);
						$re_row['vacation_date'] = $re_row['start_time'];
						if( $re_row['state'] == '1' ){
							$re_row['start_time'] = '09';
							$re_row['end_time'] = '18';
						}elseif( $re_row['state'] == '18' ){
							$re_row['start_time'] = $split_note[1];
							$re_row['end_time'] = $split_note[2];
						}elseif( $re_row['state'] == '30' ){
							$re_row['start_time'] = '09';
							$re_row['end_time'] = '14';
						}elseif( $re_row['state'] == '31' ){
							$re_row['start_time'] = '14';
							$re_row['end_time'] = '18';
						}
						array_push($query_data,$re_row);
					}
					$this->smarty->assign('vacation_list',json_encode($query_data));

					$query_data = array();
					$sql = "select * from holyday_tbl where date >= '".date('Y-m-d')."'";
					//echo $sql."<br>";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {
						array_push($query_data,$re_row['date']);
					}
					$this->smarty->assign('holy_list',json_encode($query_data));
					$this->smarty->assign('EndYear',date('Y'));

					if($vacation != ''){	//노무수령거부 통지
						//$this->smarty->assign('Detail1','연차사용일 변경');
						$this->smarty->assign('vacation','vacation');
					}

				}elseif(strpos($FormNum, "HMF-5-") !== false){

					$doc_zero = '';
					$seq = (int)$seq;
					$doc_count = 3-strlen($seq);
					for($f=0; $f<$doc_count; $f++){
						$doc_zero = '0'.$doc_zero;
					}
					$Doc_Code = '11-'.$dateto.'-'.$dept.'-'.$doc_zero.$seq;

					//문서종류 검색
					$sql = "select DocSn, RT_SanctionState, Detail1 from sanctiondoc_tbl where Detail2 like '$Doc_Code'";
					//echo $sql."<br>";
					$re = @mysql_query($sql,$db);
					if(mysql_num_rows($re) > 0){
						//결재중인 문서가 있을시 페이지 이동
						/*
						if((strpos(@mysql_result($re,0,"DocSn"), $PROCESS_TEMPORARY) !== false) or (strpos(@mysql_result($re,0,"RT_SanctionState"), $PROCESS_RETURN) !== false)){
							$report_type = @mysql_result($re,0,"Detail1");
						}else{
						*/
						$this->smarty->assign('ConservationYear',$ConservationYear);
							$this->smarty->assign('target',"self");
							$this->smarty->assign('MoveURL',"document_controller.php?ActionMode=update_page&FormNum=$FormNum&dbkey=".@mysql_result($re,0,"DocSn")."&doc_status=VIEWER&memberID=$memberID&targetKind=1&tab_index=HMF-5-1&printYN=N&tab_index=HMF-4-1&currentPage=1&satis=satis&targetKind=0");
							$this->smarty->display("intranet/move_page.tpl");
							return true;
						//}
					}elseif($report_type == "%" or $report_type == ""){
						$report_type = '001';
					}

					$FormNum = "HMF-5-".(int)$report_type;

					//부결된 문서가 있을때 부결사유 출력
					$sql = "select RT_SanctionState from sanctiondoc_tbl where Detail1 like '$report_type' AND Detail2 like '$Doc_Code' and RT_SanctionState LIKE '%$PROCESS_RETURN%'";
					//echo $sql."<br>";
					$re = @mysql_query($sql,$db);
					if(mysql_num_rows($re) > 0){
						//echo $sql ;
						$search_RT_SanctionState	= @mysql_result($re,0,"RT_SanctionState");
						$ReturnState = split(":",$search_RT_SanctionState);
						$ReturnMsg=$ReturnState[3]." ".$ReturnState[4];
						$this->smarty->assign('ReturnMsg',$ReturnMsg);
					}

					//증빙자료 존재 유무 확인
					$AddLocation = "./../../../account_file/evidence/".substr($dateto, 0, 4)."/".substr($dateto, 4, 2)."/".substr($dateto, 6, 2)."/".$Doc_Code;
					if(file_exists($AddLocation.".pdf")){
						$Addfile = $Doc_Code.".pdf";
					}

					//첨부파일 존재 유무 확인
					$AddLocation = "./../../../account_file/attachfile/".substr($dateto, 0, 4)."/".substr($dateto, 4, 2)."/".substr($dateto, 6, 2)."/".$Doc_Code;
					if(file_exists($AddLocation)){
						$handle  = opendir($AddLocation);
						$files = array();

						// 디렉터리에 포함된 파일을 저장한다.
						while (false !== ($filename = readdir($handle))) {
							if($filename == "." || $filename == ".."){
								continue;
							}

							// 파일인 경우만 목록에 추가한다.
							if(is_file($AddLocation . "/" . $filename)){
								$files[] = $filename;
							}
						}

						// 핸들 해제
						closedir($handle);
						$this->smarty->assign('attachfile',$files[0]);
					}

					$this->smarty->assign('Doc_Code',$Doc_Code);
					$this->smarty->assign('report_type',$report_type);
					$this->smarty->assign('dateto',$dateto);
					$this->smarty->assign('dept',$dept);
					$this->smarty->assign('seq',$seq);
					$this->smarty->assign('satis',$satis);
					$this->smarty->assign('Addfile',$Addfile);

					/*
					$cfile="../log/".date("Y-m")."_HMF-5.txt";
					$exist = file_exists("$cfile");
					if($exist) {
						$fd=fopen($cfile,'r');
						$con=fread($fd,filesize($cfile));
						fclose($fd);
					}
					$fp=fopen($cfile,'w');
					$aa=date("Y-m-d H:i");
					$cond=$con.$aa." ".$Doc_Code." ".$report_type." ".$dept." ".$seq." ".$satis." ".$memberID."\n";
					fwrite($fp,$cond);
					fclose($fp);
					*/

				}else if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" ){ //발신공문

					if($Detail5 == "") {
						$Detail5="1";
					}

					$Dt2[2] = date("Y-m-d");
					$Dt3[2] = "02)2141-7".$ExtNo;
					$Dt3[4] = $eMail;
					$this->smarty->assign('Dt2',$Dt2);
					$this->smarty->assign('Dt3',$Dt3);
				}else if($FormNum=="HMF-6-2" || $FormNum=="BRF-6-2" ){ //수신공문

					if($Detail5 == "") {
						$Detail5="1";
					}

				}else if($FormNum=="HMF-7-1" || $FormNum=="BRF-7-1" ){ //업무연락

					$this->smarty->assign('ExtNo',$ExtNo);

				}else if($FormNum=="HMF-8-1" || $FormNum=="BRF-8-1" ){ //자료신청서

					$ProjectCode=$_REQUEST['ProjectCode'];

					$ProjectName = ProjectViewCode2Name($_REQUEST['ProjectCode']);

					$Detail1= date("Y-m-d");
					$PAGE=$_REQUEST['PAGE'];
					$NO=$_REQUEST['NO'];

					$this->smarty->assign('ProjectCode',$ProjectCode);
					$this->smarty->assign('viewProjectCode',$viewProjectCode);
					$this->smarty->assign('ProjectName',$ProjectName);
					$this->smarty->assign('PAGE',$PAGE);
					$this->smarty->assign('NO',$NO);
					$this->smarty->assign('referer',$referer);
					$this->smarty->assign('ViewFilePath',$ViewFilePath);

				}
				else if($FormNum=="HMF-8-2" || $FormNum=="BRF-8-2" ){ //자료신청서

					if($_REQUEST['Program']=='Y')
					{
						$_REQUEST['ProjectCode']=iconv('euc-kr','utf-8',$_REQUEST['ProjectCode']);

						$viewCodesql="SELECT
										ProjectViewCode
									FROM
										project_tbl
									WHERE
										oldProjectCode='".$_REQUEST['ProjectCode']."'
									OR oldProjectCode2='".$_REQUEST['ProjectCode']."'";
						$viewCodere=mysql_query($viewCodesql);
						$_REQUEST['viewProjectCode']=mysql_result($viewCodere,0,'ProjectViewCode');

						$_REQUEST['PMDeptName']=iconv('euc-kr','utf-8',$_REQUEST['PMDeptName']);

						$UserDeptsql="SELECT GroupCode FROM member_tbl WHERE MemberNo='".$_REQUEST['memberID']."'";
						$UserDeptre=mysql_query($UserDeptsql,$db);
						$_REQUEST['UserDeptCode']=mysql_result($UserDeptre,0,'GroupCode');
					}

					$PMsql="SELECT
								Code
							FROM
								systemconfig_tbl
							WHERE SysKey='GroupCode'
							AND (CodeORName='".$_REQUEST['PMDeptCode']."'
								OR
								Name='".$_REQUEST['PMDeptName']."')
							";

					$PMre=mysql_query($PMsql,$db);
					$PMDeptCode=mysql_result($PMre,0,'Code');
					$userDeptCode=sprintf('%02d',$_REQUEST['UserDeptCode']);

					$ProjectCode=$_REQUEST['ProjectCode'];

					$ProjectName = ProjectViewCode2Name($_REQUEST['viewProjectCode']);
					$viewProjectCode=$_REQUEST['viewProjectCode'];
					$DocTitle=$ProjectName."의 성과물 파일";


					$Detail1= date("Y-m-d");
					$PAGE=$_REQUEST['PAGE'];
					$NO=$_REQUEST['NO'];

					$this->smarty->assign('ProjectCode',$ProjectCode);
					$this->smarty->assign('viewProjectCode',$viewProjectCode);
					$this->smarty->assign('ProjectName',$ProjectName);
					$this->smarty->assign('PAGE',$PAGE);
					$this->smarty->assign('NO',$NO);
					$this->smarty->assign('PMDeptCode',$PMDeptCode);
					$this->smarty->assign('PMDeptName',$_REQUEST['PMDeptName']);
					$this->smarty->assign('userDeptCode',$userDeptCode);
				}
				elseif($FormNum=="HMF-10-1")
				{
					$procedure01="BEGIN usp_pm_cont_08_print(:entries,'11', '$PJT_CODE', '$DGREE','$WBS_CODE'); END;";

					$datarow=$this->oracle->LoadProcedure($procedure01,"list_data01","","0");

					$CommonCode = new CommonCodeList ( $this->smarty );
					$CommonCode->MakeOption ( "전문공종", 'select_item08', 'Project' );

					for($i=0; $i<count($datarow); $i++){

						$fulldata = array();
						$fulldata = $datarow[$i];

						if($fulldata[item03]==0)
						{
							$fulldata[per]=0;
						}
						else
						{
							$fulldata[per]=round($fulldata[item12]/$fulldata[item03]*100,1);
						}

						$item01_cnt=mb_strlen($fulldata[item01]);
						$fulldata[item01]=mb_substr($fulldata[item01],0,$item01_cnt-1)."_".$WBS_CODE." )";

						$fulldata[item04]=FN_date($fulldata[item04],"-");
						$fulldata[item05]=FN_date($fulldata[item05],"-");
						$fulldata[item09]=FN_date($fulldata[item09],"-");
						$fulldata[item10]=FN_date($fulldata[item10],"-");

					}

					$Detail1=$PJT_CODE.'_'.$DGREE.'_'.$WBS_CODE.'_'.$ORA_DEPTCODE.'_'.$ORA_CUSTCODE;
					$FolderName=$PJT_CODE.'-'.$DGREE.'-'.$WBS_CODE;

					//$fileCheck=file_exists('../../../intranet_file/documents/HMF-10-1/'.$FolderName.'/'.$FileName);
					$DirPath='./../../../intranet_file/documents/HMF-10-1/'.$FolderName;

					if(is_dir($DirPath))
					{
						$scanFolder=scandir($DirPath.'/');

						$FileCount=count($scanFolder);

						if(is_dir($DirPath))
						{
							if($dh=opendir($DirPath))
							{
								while(($file=readdir($dh))!= false)
								{
									if($file!='.' && $file!='..')
									{
										$AttchFile=$file;
									}
								}
							}
						}
					}

				}
				elseif($FormNum=="HMF-10-2")
				{
					$CommonCode = new CommonCodeList ( $this->smarty );
					$CommonCode->MakeOption ( "전문공종", 'select_item08', 'Project' );

					$procedure01="BEGIN usp_pm_cont_09_print(:entries,'11', '$PJT_CODE', '$DGREE','$WBS_CODE'); END;";
					$datarow=$this->oracle->LoadProcedure($procedure01,"list_data01","","0");

					for($i=0; $i<count($datarow); $i++){
						$fulldata = array();
						$fulldata = $datarow[$i];

						$item01_cnt=mb_strlen($fulldata[item01]);
						$fulldata[item01]=mb_substr($fulldata[item01],0,$item01_cnt-1)."_".$WBS_CODE." )";

						$fulldata[item04]=FN_date($fulldata[item04],'-');
						$fulldata[item05]=FN_date($fulldata[item05],'-');

					}

					$procedure02 = "BEGIN Usp_Pm_Cont_0901(:entries, '11', '$PJT_CODE', '$ORA_DEPTCODE', '$WBS_CODE'); END;";
					$datarow2 = $this->oracle->LoadProcedure ( $procedure02, "list_data","","0");

					$fulldata2 = array();

					for($i=0; $i<count($datarow2); $i++){
						$datarow2[$i][WORK_S_DATE]=FN_date($datarow2[$i][WORK_S_DATE],'-');
						$datarow2[$i][WORK_E_DATE]=FN_date($datarow2[$i][WORK_E_DATE],'-');
						$datarow2[$i][CONTRACT_DATE]=FN_date($datarow2[$i][CONTRACT_DATE],'-');

						array_push($fulldata2,$datarow2[$i]);

					}

					$this->smarty->assign("ORDER_CONTENTS",$ORDER_CONTENTS);

					$Detail1=$PJT_CODE.'_'.$DGREE.'_'.$WBS_CODE.'_'.$ORA_DEPTCODE.'_'.$ORA_CUSTCODE;

					$FolderName=$PJT_CODE.'-'.$DGREE.'-'.$WBS_CODE;

					$DirPath='./../../../intranet_file/documents/HMF-10-2/'.$FolderName;

					if(is_dir($DirPath))
					{
						if($dh=opendir($DirPath))
						{
							while(($file=readdir($dh))!= false)
							{
								if($file!='.' && $file!='..')
								{
									$AttchFile=$file;
								}
							}
						}
					}
				}


				//결재선관련-------------------------------------------------------

				$Receive_index = -1;
				$End_index = 0;


				$sql_doc="select * from systemconfig_tbl where SysKey='bizform' and Code='$FormNum' and Note <> 'hidden' order by code";
				//echo $sql_doc;
				$re_doc = mysql_query($sql_doc,$db);
				$doc_name = mysql_result($re_doc,0,"Name");
				$doc_description = mysql_result($re_doc,0,"Description");

				//**수신부서,보전연한,1차결재자,2차결재자,1차결재자 action,2차결재자 action(02;1;관리자:임원:RECEIVE:FINISH)
				$doc_CodeORName = mysql_result($re_doc,0,"CodeORName");


				//**결재선정보************************************
				$DB_Sanction = split(";",$doc_CodeORName);
				//echo $DB_Sanction[2];

				//**결재자정보************************************ (//$RT_Sanction ="J14101-관리자:J09102-임원:RECEIVE";)

				$RT_Sanction = SanctionArange_Step1($memberID, $DB_Sanction[2]);
				$TmpArr = split(":",$RT_Sanction);
				$TmpArrCount=count($TmpArr);
				$Sanction_data = array();
				if($PG_Code == "") { $PG_Code = $DB_Sanction[0]; } //수신부서: 부서코드가 없는경우 DB의 수신부서로 처리


				if($ConservationYear == "") { $ConservationYear = $DB_Sanction[1]; } //보존년한 업으면 1년


				$sql="select * from systemconfig_tbl where SysKey = 'GroupCode' and Code='$PG_Code'";
				$re = mysql_query($sql,$db);
				$PGName = @mysql_result($re, 0, "Name");


				//**결재자순서라벨************************************ (// 담당:관리자:임원:부서장:접수대기:관리자:부서장:결재종료)
				$Sanction_Label = split(":",$DB_Sanction[2]);
				for($i=0; $i<count($Sanction_Label); $i++) {
					if($Sanction_Label[$i] == $PROCESS_FINISH) {
						$End_index = $i; break;
					}
					else if($Sanction_Label[$i] == $PROCESS_RECEIVE) {
						$Receive_index = $i;
					}
				}

				if(strpos($FormNum, "HMF-5-") !== false){	//전표
					$sql_doc="SELECT * FROM sanctiondoc_tbl where FormNum like '$FormNum' and memberNo like '$memberID' order by RG_Date desc, DocSN desc limit 1";
					$re_doc = mysql_query($sql_doc,$db);
				}


				if($Receive_index < 0) { $Receive_index = $End_index+1; }
				$Now_Step = Now_Step($DocSN);
				if(!$Now_Step) { $Now_Step = 0;}



				//**결재인 표시************************************

				if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" || $FormNum=="HMF-6-2" || $FormNum=="BRF-6-2" ) //발신공문,수신공문
				{
						for($i=0; $i<count($Sanction_Label); $i++) {
							if($Sanction_Label[$i] == $PROCESS_FINISH) {
								$ItemData=array("Label" =>'',"mLabel"=>$PROCESS_FINISH,"mName"=>'',"mCode"=>'',"mStatus"=>'');
							}
							else if($Sanction_Label[$i] == $PROCESS_RECEIVE)
							{
								$ItemData=array("Label" =>'수신부서',"mLabel"=>$PROCESS_RECEIVE,"mName"=>$PGName,"mCode"=>$PG_Code,"mStatus"=>'');
							}
							else
							{
								//발신공문 검토부서 부서장 유승렬 기본설정
								if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1")
								{
									if($i==4 && $TmpArr[$i]=="")
									{
										$TmpArr[$i]="M10102-담당";
									}
								}
								$m_Status=$this->ApprovalCheck2($TmpArr[$i],$i);
								$m_tmpName=$this->ApprovalName($TmpArr[$i]);
								$m_Name_arr = split("-",$m_tmpName);
								$m_Name=$m_Name_arr[0];
								if($m_Name_arr[1]=="대결")
								{
									$m_Code=$TmpArr[$i]."-".$m_Name_arr[1];
								}else
								{
									$m_Code=$TmpArr[$i];
								}
								$ItemData=array("Label" =>$Sanction_Label[$i],"mLabel"=>$Sanction_Label[$i],"mName"=>$m_Name,"mCode"=>$m_Code,"mStatus"=>$m_Status);
							}

							//echo $i."--".$Sanction_Label[$i]."<br>";
							array_push($Sanction_data,$ItemData);
						}
				}
				else
				{
						for($i=0; $i<count($Sanction_Label); $i++) {
							if($Sanction_Label[$i] == $PROCESS_FINISH) {
								$ItemData=array("Label" =>'',"mLabel"=>$PROCESS_FINISH,"mName"=>'',"mCode"=>'',"mStatus"=>'');
							}
							else if($Sanction_Label[$i] == $PROCESS_RECEIVE)
							{
								$ItemData=array("Label" =>'수신부서',"mLabel"=>$PROCESS_RECEIVE,"mName"=>$PGName,"mCode"=>$PG_Code,"mStatus"=>'');
							}
							else
							{
								if($FormNum=="HMF-8-1" || $FormNum=="HMF-8-2")
								{
									$m_Status=$this->ApprovalCheckFile($TmpArr[$i],$i);
								}else
								{
									$m_Status=$this->ApprovalCheck($TmpArr[$i],$i);
								}

								$m_tmpName=$this->ApprovalName($TmpArr[$i]);
								$m_Name_arr = split("-",$m_tmpName);
								$m_Name=$m_Name_arr[0];
								if($m_Name_arr[1]=="대결")
								{
									$m_Code=$TmpArr[$i]."-".$m_Name_arr[1];
								}else
								{
									$m_Code=$TmpArr[$i];
								}
								$ItemData=array("Label" =>$Sanction_Label[$i],"mLabel"=>$Sanction_Label[$i],"mName"=>$m_Name,"mCode"=>$m_Code,"mStatus"=>$m_Status);
							}

							//echo $Sanction_Label[$i]."<br>";
							array_push($Sanction_data,$ItemData);
						}
				}

				$this->smarty->assign('ActionMode2',$ActionMode);
				$this->smarty->assign('CompanyKind',$CompanyKind);
				$this->smarty->assign('PGName',$PGName);
				$this->smarty->assign('PG_Code',$PG_Code);
				$this->smarty->assign('ConservationYear',$ConservationYear);
				$this->smarty->assign('TmpArrCount',$TmpArrCount);
				$this->smarty->assign('fulldata',$fulldata);
				$this->smarty->assign('fulldata2',$fulldata2);
				$this->smarty->assign('Sanction_data',$Sanction_data);

				$this->smarty->assign('backgroundcolor','#f5f5f6;');
				$this->smarty->assign('readonly','');
				$this->smarty->assign('Edit',true);

				$this->smarty->assign('DocTitle',$DocTitle);
				$this->smarty->assign('AttchFile',$AttchFile);

				$this->smarty->assign('doc_status',$DOC_STATUS_CREATE);
				$this->smarty->assign('DOC_STATUS_CREATE',$DOC_STATUS_CREATE);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('Name',$Name);
				$this->smarty->assign('RG_Code',$RG_Code);
				$this->smarty->assign('RG_Date',$RG_Date);
				$this->smarty->assign('GroupName',$GroupName);
				$this->smarty->assign('MemberInfo',$MemberInfo);
				$this->smarty->assign('Detail1',$Detail1);
				$this->smarty->assign('Detail2',$Detail2);
				$this->smarty->assign('Detail3',$Detail3);
				$this->smarty->assign('Detail4',$Detail4);
				$this->smarty->assign('Detail5',$Detail5);
				$this->smarty->assign('now_vacation',$now_vacation);
				$this->smarty->assign('FormNum',$FormNum);
				$this->smarty->assign('doc_name',$doc_name);
				$this->smarty->assign('targetKind',$targetKind);
				$this->smarty->assign('mobile',$mobile);

				$this->smarty->assign('PROCESS_APPROVE',$PROCESS_APPROVE);
				$this->smarty->assign('PROCESS_TEMPORARY',$PROCESS_TEMPORARY);
				$this->smarty->assign('PROCESS_ACCEPT',$PROCESS_ACCEPT);
				$this->smarty->assign('PROCESS_REJECTION',$PROCESS_REJECTION);
				$this->smarty->assign('PROCESS_RETURN',$PROCESS_RETURN);
				$this->smarty->assign('PROCESS_BACK',$PROCESS_BACK);
				$this->smarty->assign('PROCESS_FINISH',$PROCESS_FINISH);
				$this->smarty->assign('PROCESS_DECISION',$PROCESS_DECISION);
				$this->smarty->assign('PROCESS_RECEIVE',$PROCESS_RECEIVE);
				$this->smarty->assign('PROCESS_DELETE',$PROCESS_DELETE);

				$this->smarty->assign('DOC_STATUS_CREATE',$DOC_STATUS_CREATE);
				$this->smarty->assign('DOC_STATUS_EDIT',$DOC_STATUS_EDIT);
				$this->smarty->assign('DOC_STATUS_VIEW',$DOC_STATUS_VIEW);
				$this->smarty->assign('DOC_STATUS_APPROVE',$DOC_STATUS_APPROVE);
				$this->smarty->assign('DOC_STATUS_ACCEPT',$DOC_STATUS_ACCEPT);

				$this->smarty->assign('PROCESS_CODE',$PROCESS_CODE);
				$this->smarty->assign('TEMPORARY_CODE',$TEMPORARY_CODE);
				$this->smarty->assign('SANCTION_CODE',$SANCTION_CODE);
				$this->smarty->assign('SANCTION_CODE2',$SANCTION_CODE2);
				$this->smarty->assign('STEP_NO',$STEP_NO);

				$this->smarty->assign("page_action","document_controller.php");
				if($Site=="File_mng") //전자도서관 자료신청
				{
					$this->smarty->display("intranet/common_contents/work_approval/document_input_file_mvc.tpl");
				}else{
					$this->smarty->display("intranet/common_contents/work_approval/document_input_mvc_jmj.tpl");
				}

		}


		//============================================================================
		// 전자결재 문서작성
		//============================================================================
		function InsertAction()
		{
			include "../inc/approval_function.php";
			extract($_REQUEST);
			global $db,$memberID;

			global $FormNum,$TmpArrCount;

			global $NewSN,$FormNum,$ProjectCode,$DocTitle,$AttchFile,$MemberNo,$Project_Name,$Project_Code,$GroupName,$WriterName;
			global $RT_Sanction_,$RT_SanctionState, $MemberInfo;
			global $RG_Code,$Security,$ConservationYear,$Account;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5,$Detail_5tmp;

			global $Position,$RG_Date,$RG_Code,$PG_Code,$PG_Date;
			global $mLabel,$mName,$mCode,$menu_cmd,$DocSN;
			global $Detail_1,$Detail_2,$Detail_3,$Detail_4,$Detail_5;

			global $Detail_4_0,$Detail_4_1,$Detail_4_2,$Detail_4_3,$Detail_4_4;

			global $userfile,$userfile_name,$userfile_size,$filename,$Addfile;
			global $DeviceChk,$doc_status;
			global $vacation_num,$AttchFile_1,$attachfile;
			global $Detail6,$Detail_6,$confirm_members;
			global $viewProjectCode;
			global $ORDER_CONTENTS, $mobile; //외주품의서 작성 변수

			if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" || $FormNum=="HMF-6-2" || $FormNum=="BRF-6-2"){ //발신공문.수신공문
				$dbinsert="No";
				$dbinsert="yes";
			}else
			{
				$dbinsert="yes";
			}

			if($memberID=="T03225")
			{
					//$dbinsert="No";
			}
			if(FN_DevConfirm($memberID)){
				// 				$qq = "H18-영업-01";
				// 				$NewProjectCode	= FN_projectToColumn($qq,'NewProjectCode');		//프로젝트코드 NewProjectCode
				// 				echo $NewProjectCode;
				$dbinsert="No";
			}

			$o_group= MemberNo2GroupCode($memberID);
			if($o_groupo<"10")
			{
				$o_group = substr($o_group, 1, 1);
			}

			//********양식별 추가내용 저장(전처리) --------------------------------------------------------------------
				switch ($FormNum) {

					//휴가계
						case "HMF-4-8":case "BRF-4-8":

							$Detail4="";
							for($i=0; $i<=7; $i++) {
								$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
							}

							$Detail5="";
							if(strpos($DocTitle,"반차") !== false || strpos($DocTitle,"연차") !== false)
							{
								$Detail5="1";
							}
							else if(strpos($DocTitle,"경조") !== false)
							{
								$Detail5="7";
							}
							else if(strpos($DocTitle,"보건") !== false || strpos($DocTitle,"출산") !== false || strpos($DocTitle,"특별") !== false)
							{
								$Detail5="8";
							}

							break;

					//근태사유서
						case "HMF-4-7":case "BRF-4-7":

							$Detail1="";

							if(strpos($DocTitle,"업무") !== false || strpos($DocTitle,"경유") !== false || strpos($DocTitle,"기타") !== false || strpos($DocTitle,"반차") !== false)
							{
								$Detail_1[1]=$Detail_1[0];
							}

							for($i=0; $i<=1; $i++) {
								$Detail1= $Detail1 . str_replace("'","",$Detail_1[$i])."/n";
							}

							$Detail2="";
							$Detail3="";
							$Detail4="";
							$Detail5="";

							if( strpos($DocTitle,"오전반차") !== false ){
								$Detail_5[0]="30";
							}else if( strpos($DocTitle,"오후반차") !== false ){
								$Detail_5[0]="31";
							}else if(strpos($DocTitle,"반차") !== false || strpos($DocTitle,"연차") !== false){
								$Detail_5[0]="1";
							}
							else if(strpos($DocTitle,"경조") !== false)
							{
								$Detail_5[0]="7";
							}
							else if(strpos($DocTitle,"보건") !== false || strpos($DocTitle,"출산") !== false || strpos($DocTitle,"특별") !== false)
							{
								$Detail_5[0]="8";
							}
							else if(strpos($DocTitle,"경유") !== false)
							{
								$Detail_5[0]="2";
							}
							else if(strpos($DocTitle,"훈련") !== false)
							{
								$Detail_5[0]="5";
							}
							else if(strpos($DocTitle,"교육") !== false)
							{
								$Detail_5[0]="6";
							}
							else if(strpos($DocTitle,"기타") !== false)
							{
								$Detail_5[0]="17";
							}
							else if(strpos($DocTitle,"시차") !== false)
							{
								$Detail_5[0]="18";
								$Detail1= $Detail1.$Detail_6[0]."/n".$Detail_6[1]."/n";
							}


							for($i=0; $i<=1; $i++) {
								$Detail5= $Detail5 . str_replace("'","",$Detail_5[$i])."/n";
							}

							for($i=0; $i<=6; $i++) {
								$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";
								$Detail3= $Detail3 . str_replace("'","",$Detail_3[$i])."/n";
								if(strpos($DocTitle,"업무") !== false || strpos($DocTitle,"경유") !== false)
								{
									$TempDetail4=$Detail_4_0[$i]."=".$Detail_4_1[$i]."=".$Detail_4_2[$i]."=".$Detail_4_3[$i]."=".$Detail_4_4[$i];
									$Detail4= $Detail4 . str_replace("'","",$TempDetail4)."/n";
								}else
								{
									$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
								}

							}

							break;

					//업무주차확인증
						case "HMF-9-8":case "BRF-9-8":

							$Detail5="";
							$Detail5= $Detail5 . str_replace("'","",$GroupName)."/n".str_replace("'","",$WriterName)."/n";

							break;

					//연장근무확인서
						case "HMF-9-1":case "BRF-9-1":
							$Detail4="";
							$Detail5="";
							for($i=0; $i<=2; $i++) {
								$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
								$Detail5= $Detail5 . str_replace("'","",$Detail_5[$i])."/n";
							}

							$DocTitle=$DocTitle."(".$Detail1.")";
							break;

					//출장배차신청서
						case "HMF-2-4":case "BRF-2-4":

							$mName="";
							$Detail2="";
							$Detail3="";
							$Detail4="";
							$Detail5="";
							for($i=0; $i<=4; $i++) {

							$mName[$i] = MemberNo2Name($Detail_2[$i]);
							$myname = MemberNo2Name($memberID);

							$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";

							if($i == "0")
							{
								$mName2 = $myname.",".$mName[$i];
								$Detail_22 = $memberID.",".$Detail_2[$i];
							}
							else if($i <> "0" and $mName[$i] <> "" and $Detail_2[$i]<>"")
							{
								$mName2 = $mName2.",".$mName[$i];
								$Detail_22 = $Detail_22.",".$Detail_2[$i];
							}

							}

							for($i=0; $i<=3; $i++) {

								$Detail3= $Detail3 . str_replace("'","",$Detail_3[$i])."/n";
							}

							for($i=0; $i<=5; $i++) {

								$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
							}

							if($Detail_4[3]=="회사차량")
							{
								$Detail_5[3]=$Detail_5tmp[3];
							}

							for($i=0; $i<=5; $i++)
							{
								//echo $i."-".$Detail_5[$i]."<br>";
								$Detail5= $Detail5 . str_replace("'","",$Detail_5[$i])."/n";
							}

							break;

					//연장근무신청서(개인),휴일근로신청서(개인)
					case "HMF-9-2-s": case "HMF-4-5-s":	case "BRF-9-2-s": case "BRF-4-5-s":

							$Detail2="";
							for($i=0; $i<=1; $i++) {
								$Detail2= $Detail2 . $Detail_2[$i]."/n";
							}

							$TmpState = split("-",$mCode[0]);
							$Detail3=$TmpState[0];
							if($Detail5=="1" || $Detail5=="")
							{
								$DocTitle=$Detail1."(연장근무)";
							}
							else
							{
								$DocTitle=$Detail1."(휴일근무)";
							}

							$Detail4=MemberNo2Name($memberID);
							break;

					//연장근무신청서(팀장),휴일근로신청서(팀장)
					case "HMF-9-2": case "HMF-4-5": case "BRF-9-2": case "BRF-4-5":


							if($Detail_1[1]=="1" || $Detail_1[1]=="" )
							{
								$DocTitle=$GroupName."(".$Detail_1[0]." 연장근무)";
							}
							else
							{
								$DocTitle=$GroupName."(".$Detail_1[0]." 휴일근무)";
							}

							if($Project_Name <> "")
							{
								//================================================================================
								// $Detail_3[0]=$Project_Name." [".$Project_Code."]";
								// $ProjectCode=$Project_Code;
								//================================================================================

								//================================================================================
								$re_NewProjectCode	= FN_projectToColumn($Project_Code,'NewProjectCode');		//프로젝트코드 NewProjectCode
								$re_projectViewCode	= FN_projectToColumn($Project_Code,'projectViewCode');		//프로젝트코드 ViewCode
								$re_ProjectNickname	= FN_projectToColumn($Project_Code,'ProjectNickname');		//프로젝트코드 ProjectNickname
								//-----------------------------------------------------------------------------------------------------
								$Detail_3[0]=$re_ProjectNickname." [".$re_projectViewCode."]";
								$ProjectCode=$Project_Code;
								//================================================================================

							}

							if($Detail1 == "")
							{
								$Detail1="";
								for($i=0; $i<=1; $i++) {
									$Detail1= $Detail1 . $Detail_1[$i]."/n";
								}
							}else
							{
								$Detail_1[0]=$Detail1;
								$Detail_1[1]="1";

							}

							//팀원들이 올린 결재를 모두 처리중 업데이트
							$nowdate=date("Y-m-d");
							$RT_value="2:RECEIVE:".$memberID.":".date("Y-m-d");

							if($FormNum =="HMF-9-2" || $FormNum =="BRF-9-2")
								$Tmp_FormNum="('HMF-9-2-s','BRF-9-2-s')";
							else if($FormNum =="HMF-4-5" || $FormNum =="BRF-4-5")
								$Tmp_FormNum="('HMF-4-5-s','BRF-4-5-s')";

							$DocSN_tmp=NewSerialNo2($memberID);
							$MemberNo_tmp=$memberID."-팀장";

							for($i=0; $i<=25; $i++) {
								if($Detail_2[$i] <> "" && $Detail_3[$i] <> "" ){
									$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";

									$Detail3= $Detail3 . str_replace("'","",$Detail_3[$i])."/n";

									//$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
									//$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
									$Detail4_1= $Detail4_1 . str_replace("'","",$Detail_4_1[$i])."/n";
									$Detail4_2= $Detail4_2 . str_replace("'","",$Detail_4_2[$i])."/n";
									$Detail5= $Detail5 . str_replace("'","",$Detail_5[$i])."/n";
									$AttchFile= $AttchFile . str_replace("'","",$AttchFile_1[$i])."/n";

									//부서원들 결재완료.
									$sql = "update SanctionDoc_tbl set RT_SanctionState ='$RT_value',PG_Date='$nowdate' where FormNum in $Tmp_FormNum and Detail1='$Detail_1[0]' and MemberNo='$Detail_5[$i]' and DocSN not like 'TEMP%'  and (PG_Date='0000-00-00' or IsNull(PG_Date))";
									if($dbinsert =="yes"){
										record_log('document', 'InsertAction_1_'.$memberID, $sql);
										mysql_query($sql,$db);
									}else{
										echo "[sub--- ".$sql."<br>";
									}

									//부서원들 결재완료 코드추가
									$sql2="select * from SanctionDoc_tbl where FormNum in $Tmp_FormNum and Detail1='$Detail_1[0]' and Detail3='$memberID' and MemberNo = '$Detail_5[$i]' and DocSN not like 'TEMP%'";

									//echo $sql2."<Br>";
									$re2 = mysql_query($sql2,$db);
									while($re_row2 = mysql_fetch_array($re2)){

										$DocSN_tmp=$re_row2[DocSN];

										$sql3="insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN_tmp', '$MemberNo_tmp', '1:결의부서내', now(), now(), 'ACCEPT','')";
										if($dbinsert =="yes"){
											record_log('document', 'InsertAction_2_'.$memberID, $sql3);
											mysql_query($sql3,$db);
										}else{
											echo "[sub3--- ".$sql3."<br>";
										}
									}
								}
							}

							$Detail4=$Detail4_1."<*>".$Detail4_2;

							break;

					//비품사용신청서
					case "HMF-2-3": case "BRF-2-3":
							$Detail5="";
							for($i=0; $i<=5; $i++) {
								$Detail5= $Detail5 . $Detail_5[$i]."/n";
							}

							if($menu_cmd==$PROCESS_APPROVE)
							{
								// 비품사용일정표 입력
								$updatedate = date("Y-m-d");
								$KorName=MemberNo2Name($memberID);
								for($i=0; $i<sizeof($DeviceChk); $i++) {

									$sql = "insert into schedule_device_tbl (membername,endtime,devicename,contents,sdate,edate,updatedate) values('$KorName','09~18','$DeviceChk[$i]','$Detail1','$Detail2','$Detail3','$updatedate')";


									if($dbinsert =="yes"){
										record_log('document', 'InsertAction_3_'.$memberID, $sql);
										$result=mysql_query($sql,$db);
									}else{
										echo "[HMF-2-3--- ".$sql."<br>";
									}
								}
							}

							break;

					//회의실사용신청서
					case "HMF-9-3": case "BRF-9-3":

							$Detail1="";
							for($i=0; $i<=3; $i++) {
								$Detail1= $Detail1 . $Detail_1[$i]."/n";
							}

							if($menu_cmd==$PROCESS_APPROVE)
							{
								// 회의실사용일정표 입력
								$updatedate = date("Y-m-d");
								$KorName=MemberNo2Name($memberID);
								$Dt1=split("/n",$Detail1);

								$sql = "insert into schedule_device_tbl (membername,endtime,devicename,contents,sdate,edate,updatedate) values('$KorName','$Dt1[1]~$Dt1[3]','$Detail2','$Detail3','$Dt1[0]','$Dt1[2]','$updatedate')";

								if($dbinsert =="yes"){
									record_log('document', 'InsertAction_4_'.$memberID, $sql);
									$result=mysql_query($sql,$db);
								}else{
									echo "[HMF-9-3--- ".$sql."<br>";
								}
							}
							break;

					//명함신청서
					case "HMF-9-4":case "BRF-9-4":

							$Detail1="";
							for($i=0; $i<=1; $i++) {
								$Detail1= $Detail1 . $Detail_1[$i]."/n";
							}

							$Detail2="";
							for($i=0; $i<=1; $i++) {
								$Detail2= $Detail2 . $Detail_2[$i]."/n";
							}

							$Detail3="";
							for($i=0; $i<=1; $i++) {
								$Detail3= $Detail3 . $Detail_3[$i]."/n";
							}

							$Detail4="";
							for($i=0; $i<=3; $i++) {
								$Detail4= $Detail4 . $Detail_4[$i]."/n";
							}

							$Detail5="";
							for($i=0; $i<=1; $i++) {
								$Detail5= $Detail5 . $Detail_5[$i]."/n";
							}
							break;

					//연차휴가 변경계획서
					case "HMF-4-9":case "BRF-4-9":

							$DocTitle="연차휴가 변경계획서 [".$Detail1."]->[".$Detail2."]";

							break;

					//연차휴가 변경계획서2
					case "HMF-4-10":case "BRF-4-10":
						$DocTitle="연차휴가 변경계획서[전체] ";
						/*
							for($i=0; $i<$vacation_num; $i++) {
								$Detail1= $Detail1 . $Detail_1[$i]."/n";
							}

							for($i=0; $i<$vacation_num; $i++) {
								$Detail2= $Detail2 . $Detail_2[$i]."/n";
							}
						*/

							break;

					//발신공문
					case "HMF-6-1":case "BRF-6-1":

						global $PG_Y,$PG_M,$PG_D;
						$Detail_2[2]=$PG_Y."-".$PG_M."-".$PG_D;

						$Detail2 = "";
						$Detail3 = "";
						$ProjectCode = "";

						$Detail1 = str_replace("\\\\","￦",$Detail1);

						for($i=0; $i<=3; $i++) {
							$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";
						}
						for($i=0; $i<=5; $i++) {
							$Detail3= $Detail3 . str_replace("'","",$Detail_3[$i])."/n";
						}
						for($i=0; $i<=6; $i++) {
							$ProjectCode= $ProjectCode.$mCoop[$i]."/";
						}
					break;

					//수신공문
					case "HMF-6-2":case "BRF-6-2":

						//global $PGCode,$PGCodeName;

						//$Detail2=$PGCodeName."/n".$PGCode;
						//$Detail3=$PGCode;
						$Detail4 = "";
						for($i=0; $i<=3; $i++) {
							$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
						}

						if($ProjectCode == ""){
							//발신번호 생성 (해당년도4+해당월7-xxxx 4자리 자동증가 년도 바뀌면 0001부터)
							$ThisYear=date("Y");
							$sql="select lpad(substr(Max(ProjectCode),8,5)+1,5,0) as docnumber from sanctiondoc_tbl where FormNum in('HMF-6-2','BRF-6-2') and ProjectCode like '".date("Y")."%'";
							//echo $sql."<br>";
							$re = @mysql_query($sql,$db);
							if(mysql_num_rows($re) > 0)
							{
								$docnumber=mysql_result($re,0,"docnumber");
								if($docnumber=="")	{
									$docnumber="00001";
									//$docnumber="2440";
								}
							}
							$ProjectCode=date('Ym')."-".$docnumber;
						}else{
							$sql="select RG_Date from sanctiondoc_tbl where FormNum in('HMF-6-2','BRF-6-2') and ProjectCode like '$ProjectCode'";
							//echo $sql."<br>";
							$re = @mysql_query($sql,$db);
							if(mysql_num_rows($re) > 0)
							{
								$RG_Date=mysql_result($re,0,"RG_Date");
							}
						}

					break;

					//업무연락
					case "HMF-7-1":case "BRF-7-1":

						global $PGCode,$PGCodeName,$ExtNo;

							$Detail2=$PGCodeName;
							$Detail3=$PGCode;


							$MemberInfo=$MemberInfo."/n".$ExtNo;

					break;

					case "HMF-8-1":case "BRF-8-1":



					break;

					case "HMF-8-2":case "BRF-8-2":
						global  $viewProjectCode;

						$Detail2=$viewProjectCode;

						$MaxSql="SELECT MAX(CAST(NO AS UNSIGNED)) AS NO FROM tn_file_revise_tbl WHERE PAGE='Complete'";
						$MaxRe=mysql_query($MaxSql,$db);

						$reviseMaxNo=mysql_result($MaxRe,0,"NO");

						if($reviseMaxNo=="")
						{
							$reviseMaxNo=1;
						}
						else
						{
							$reviseMaxNo=$reviseMaxNo+1;
						}

						$revise_sql="INSERT INTO tn_file_revise_tbl
						(
						PAGE,
						NO,
						PROJECTCODE,
						VIEW_FILENAME,
						DOWNUSER
						)
						VALUES
						(
						'Complete',
						'$reviseMaxNo',
						'$_REQUEST[Project_Code]',
						'$_REQUEST[Project_Name]',
						'$_REQUEST[memberID]'
						)";

						record_log('document', 'InsertAction_14_'.$_REQUEST[memberID], $revise_sql);
						mysql_query($revise_sql,$db);

						$Detail4=$Detail4.$reviseMaxNo;

					break;

					case "HMF-10-1": case "HMF-10-2":
						$Detail1_arr=explode('_',$Detail1);

						$pjt_sql="select ProjectCode from project_tbl where oldProjectCode='$Detail1_arr[0]'";
						$pjt_re=mysql_query($pjt_sql,$db);
						$re_num=mysql_num_rows($pjt_re);

						if($re_num>0)
						{
							$ProjectCode=mysql_result($pjt_re,0,"ProjectCode");
						}
						else
						{
							$ProjectCode=$Detail1_arr[0];
						}

						break;

				}


				if($dbinsert <>"yes")
				{
					echo "Detail1".$Detail1."<br>";
					echo "Detail2".$Detail2."<br>";
					echo "Detail3".$Detail3."<br>";
					echo "Detail4".$Detail4."<br>";
					echo "Detail5".$Detail5."<br>";
				}


				$Security="LOW";
				$RG_Date = date('Y-m-d');
				if(!$DocTitle) { $DocTitle = "제목없음";}

			//******결재자정보(RT_SanctionState)********************************************************************************/

				$RT_Sanction_ = "";
				for($i=0; $i<=8; $i++) {
					if($mLabel[$i] == $PROCESS_RECEIVE) {
						$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
						break;
					} else {
						if($mCode[$i] <> "") {
							if($i == 0) {
								$RT_Sanction_ = $mCode[$i];
							} else {
								$RT_Sanction_ = $RT_Sanction_.":".$mCode[$i];
							}
						} else {
							if($i == 0) {
								$RT_Sanction_ = "";
							} else {
								$RT_Sanction_ = $RT_Sanction_.":";

							}
						}
					}
				}

				$path ="./../../../intranet_file/documents/".$FormNum."/";
				$path_is ="./../../../intranet_file/documents/".$FormNum;


			if(strpos($FormNum, "HMF-5-") !== false || strpos($FormNum, "BRF-5-") !== false ){

				$filename = $Addfile;
				$AttchFile=$attachfile;
			}else if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" || $FormNum=="HMF-6-2" || $FormNum=="BRF-6-2"){ //발신공문.수신공문


						$sql="select Addfile from sanctiondoc_tbl where DocSN='$DocSN'";
						//echo $sql."<Br>";
						$re = mysql_query($sql,$db);
						while($re_row = mysql_fetch_array($re))
						{
							$Addfile=$re_row[Addfile];
						}
						$multyfile_exist_name=split("/n",$Addfile);
						$multyfile_exist_cnt=count($multyfile_exist_name);


						//******결재자정보재지정(RT_SanctionState)********************************************************************************/

							$RT_Sanction_ = "";
							for($i=0; $i<=7; $i++) {
								if($mLabel[$i] == $PROCESS_RECEIVE) {
									$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
								}else if($mLabel[$i] == $PROCESS_FINISH) {
									$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_FINISH;
								}
								else {
									if($mCode[$i] <> "") {
										if($i == 0) {
											$RT_Sanction_ = $mCode[$i];
										} else {
											$RT_Sanction_ = $RT_Sanction_.":".$mCode[$i];
										}
									} else {
										if($i == 0) {
											$RT_Sanction_ = "";
										} else {
											$RT_Sanction_ = $RT_Sanction_.":";

										}
									}
								}
							}
						//******결재자정보재지정(RT_SanctionState)********************************************************************************/




						//----------첨부파일 여러개 올리기------------------------------
						global $multyfile,$multyfile_name,$multyfile_size;
						for($i=0; $i<count($multyfile); $i++) {
							if ($multyfile_name[$i]) //첨부파일있으면
							{
								if (is_dir ($path_is))
								{
								}
								else
								{
									mkdir($path_is, 0777);
								}

								$prefile=time();
								$multyfile[$i]=stripslashes($multyfile[$i]);
								$_FILES['multyfile']['name'][$i] = iconv("UTF-8", "EUC-KR",$_FILES['multyfile']['name'][$i]);
								$vupload = $path."[".$prefile."]".$_FILES['multyfile']['name'][$i];
								$vupload = str_replace(" ","",$vupload);
								$vupload = str_replace("#","",$vupload);
								//$vupload = str_replace("'","",$vupload);
								$_FILES['multyfile']['tmp_name'][$i] = iconv("UTF-8", "EUC-KR",$_FILES['multyfile']['tmp_name'][$i]);

								if($multyfile_exist_name[$i]<>"")
								{
									$multyfile_Arr2=split("/",$multyfile_exist_name[$i]);
									if($multyfile_Arr2[2] <> "")
									{
										$del_path = $path.$multyfile_Arr2[2];
										$Resultfile_org = file_exists("$del_path");
										if($Resultfile_org)	{
											$re=unlink("$del_path");
										}
									}

								}
								move_uploaded_file($_FILES['multyfile']['tmp_name'][$i], $vupload);

								$filename_m="./".$FormNum."/"."[".$prefile."]".$multyfile_name[$i];
								$filename_m = str_replace(" ","",$filename_m);
								$filename_m = str_replace("#","",$filename_m);
								//$filename_m = str_replace("'","",$filename);

								$filename= $filename.$filename_m."/n";

							}
							else  //첨부파일없고
							{
								if($multyfile_exist_name[$i] <>"") //기존파일잇으면
								{
									$filename= $filename.$multyfile_exist_name[$i]."/n";
								}
							}
						}

			}elseif($FormNum=="HMF-8-2" || $FormNum=="BRF-8-2"){
				$RT_Sanction_ = "";
				for($i=0; $i<=7; $i++) {
					if($mLabel[$i] == $PROCESS_RECEIVE) {
						$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
					}else if($mLabel[$i] == $PROCESS_FINISH) {
						$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_FINISH;
					}
					else {
						if($mCode[$i] <> "") {
							if($i == 0) {
								$RT_Sanction_ = $mCode[$i];
							} else {
								$RT_Sanction_ = $RT_Sanction_.":".$mCode[$i];
							}
						} else {
							if($i == 0) {
								$RT_Sanction_ = "";
							} else {
								$RT_Sanction_ = $RT_Sanction_.":";

							}
						}
					}
				}

			}elseif($FormNum=="HMF-10-1" || $FormNum=="HMF-10-2"){
				$RT_Sanction_ = "";
				for($i=0; $i<=5; $i++) {
					if($mLabel[$i] == $PROCESS_RECEIVE) {
						$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
					}else if($mLabel[$i] == $PROCESS_FINISH) {
						$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_FINISH;
					}
					else {
						if($mCode[$i] <> "") {
							if($i == 0) {
								$RT_Sanction_ = $mCode[$i];
							} else {
								$RT_Sanction_ = $RT_Sanction_.":".$mCode[$i];
							}
						} else {
							if($i == 0) {
								$RT_Sanction_ = "";
							} else {
								$RT_Sanction_ = $RT_Sanction_.":";

							}
						}
					}
				}

			}else{
					if ($userfile)
					{ //첨부파일 있으면서 수정이면
							if (is_dir ($path_is)){
								////
							}else{
								mkdir($path_is, 0777);
							}

							$prefile=time();
							if($userfile_name <>"" && $userfile_size <>0)
							{
								$userfile=stripslashes($userfile);
								$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
								$vupload = $path.$prefile.$_FILES['userfile']['name'];
								$vupload = str_replace(" ","",$vupload);
								//$vupload = str_replace("'","",$vupload);

								$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
								move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
								$userfile_size = number_format($userfile_size);
								$filename="./".$FormNum."/".$prefile.$userfile_name;
								$filename = str_replace(" ","",$filename);
								//$filename = str_replace("'","",$filename);
							}
					}
			}

			// ******Case별 내용저장 -------------------------------------------------------------------------

			switch ($menu_cmd) {
				case $PROCESS_TEMPORARY: //신규파일 임시저장

					$tmpDocSN=TempSerialNo2($memberID);
					$RT_SanctionState=TempState($memberID); //현재 결재자

					$azSQL = "insert into SanctionDoc_tbl (DocSN, FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, RT_SanctionState, PG_Code, Security, ConservationYear, Account, FinishMemberNo, Detail1, Detail2, Detail3, Detail4, Detail5,MemberInfo, confirm_members) values('$tmpDocSN', '$FormNum', '$ProjectCode', '$DocTitle', '$filename', '$AttchFile', '$memberID', '$RG_Date', '$RG_Code',  '$RT_Sanction_', '$RT_SanctionState', '$PG_Code', '$Security', '$ConservationYear', '$Account', '', '$Detail1', '$Detail2', '$Detail3', '$Detail4', '$Detail5','$MemberInfo', '$confirm_members')";


					if($dbinsert =="yes"){
						record_log('document', 'InsertAction_5_'.$memberID, $azSQL);
						$result=mysql_query($azSQL,$db);
					}else{
						echo "[1--- ".$azSQL."<br>";
					}


					break;

				case $DOC_STATUS_EDIT:  //임시저장 파일 편집후 다시 저장시

					$azSQL = "update SanctionDoc_tbl set ProjectCode='$ProjectCode', DocTitle='$DocTitle', Addfile='$filename', AttchFile='$AttchFile',  RG_Date='$RG_Date', RT_Sanction='$RT_Sanction_', RT_SanctionState='$RT_SanctionState', PG_Code='$PG_Code', Security='$Security', ConservationYear='$ConservationYear', Account='$Account', FinishMemberNo='', Detail1='$Detail1', Detail2='$Detail2', Detail3='$Detail3', Detail4='$Detail4', Detail5='$Detail5' ,MemberInfo='$MemberInfo', confirm_members='$confirm_members' where DocSN='$DocSN'";


					if($dbinsert =="yes"){
						record_log('document', 'InsertAction_6_'.$memberID, $azSQL);
						$result=mysql_query($azSQL,$db);
					}else{
						echo "[2--- ".$azSQL."<br>";
					}


					break;

				case $PROCESS_APPROVE: //결재상신

					$NewSN=NewSerialNo2($memberID);

					if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" || $FormNum=="HMF-6-2" || $FormNum=="BRF-6-2" || $FormNum=="HMF-8-1" || $FormNum=="HMF-8-2"){ //발신공문.수신공문
							$RT_SanctionState = NextSanctionState22($RT_Sanction_,"");
					}else
					{
						$RT_SanctionState = NextSanctionState($RT_Sanction_,"");
					}

					$rescount = mysql_query("select * from SanctionDoc_tbl where DocSN='$DocSN'",$db);
					$rescountval = mysql_num_rows($rescount);

					//항만부 -> 기술개발TF팀 부장 결재 위해
					if($RG_Code=="34"){$RG_Code="03";}

					if($rescountval > 0) {
						if(substr($DocSN,0,5) == $PROCESS_TEMPORARY) { //임시저장인경우 신규코드부여후 결재상신
							$azSQL = "update SanctionDoc_tbl set DocSN='$NewSN', ProjectCode='$ProjectCode', DocTitle='$DocTitle', Addfile='$filename', AttchFile='$AttchFile' ,  RG_Date='$RG_Date', RT_Sanction='$RT_Sanction_', RT_SanctionState='$RT_SanctionState', PG_Code='$PG_Code', Security='$Security', ConservationYear='$ConservationYear', Account='$Account', FinishMemberNo='', Detail1='$Detail1', Detail2='$Detail2', Detail3='$Detail3', Detail4='$Detail4', Detail5='$Detail5' ,MemberInfo='$MemberInfo', confirm_members='$confirm_members'  where DocSN='$DocSN'";
						} else {                                       //재기안 인경우 기존 문서번호 사용 결재상신
							$azSQL = "update SanctionDoc_tbl set DocSN='$NewSN', ProjectCode='$ProjectCode', DocTitle='$DocTitle', Addfile='$filename', AttchFile='$AttchFile',  RG_Date='$RG_Date', RT_Sanction='$RT_Sanction_', RT_SanctionState='$RT_SanctionState', PG_Code='$PG_Code', Security='$Security', ConservationYear='$ConservationYear', Account='$Account', FinishMemberNo='', Detail1='$Detail1', Detail2='$Detail2', Detail3='$Detail3', Detail4='$Detail4', Detail5='$Detail5' ,MemberInfo='$MemberInfo', confirm_members='$confirm_members'  where DocSN='$DocSN'";
						}
					} else {                                           //신규파일 작성후 바로 결재상신


							if(strpos($FormNum, "HMF-5-") !== false || strpos($FormNum, "BRF-5-") !== false){
								$temp_code = split('-', $Detail2);
								$dateto = $temp_code[1];
								$dept = $temp_code[2];
								$seq = (int)$temp_code[3];

								$prosql ="BEGIN Usp_slipreport_0001('$dateto', '$dept', '$seq', '4' ); END;";
								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($prosql);
								}else{
									echo "신청부서 결재중 oracle : ".$prosql."<br>";
								}

								if($Detail4 != ""){
									$Detail4 .= " - ".MemberNo2Name($memberID);
								}
							}

						$azSQL = "insert into SanctionDoc_tbl (DocSN, FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, RT_SanctionState, PG_Code, PG_Date, Security, ConservationYear, Account, FinishMemberNo, Detail1, Detail2, Detail3, Detail4, Detail5, MemberInfo, confirm_members) values('$NewSN', '$FormNum', '$ProjectCode', '$DocTitle', '$filename', '$AttchFile', '$memberID', '$RG_Date', '$RG_Code',  '$RT_Sanction_', '$RT_SanctionState', '$PG_Code', '', '$Security', '$ConservationYear', '$Account', '', '$Detail1', '$Detail2', '$Detail3', '$Detail4', '$Detail5', '$MemberInfo', '$confirm_members')";

						/*
						if(strpos($FormNum, "HMF-5-") !== false || strpos($FormNum, "BRF-5-") !== false){
							$cfile="../log/".date("Y-m")."_HMF-5.txt";
							$exist = file_exists("$cfile");
							if($exist) {
								$fd=fopen($cfile,'r');
								$con=fread($fd,filesize($cfile));
								fclose($fd);
							}
							$fp=fopen($cfile,'w');
							$aa=date("Y-m-d H:i");
							$cond=$con.$aa." ".$azSQL."\n";
							fwrite($fp,$cond);
							fclose($fp);
						}
						*/
					}



					//문서별 결재상신시 처리

					if($FormNum=="HMF-7-1" and (strpos($RT_SanctionState,"RECEIVE") !== false)) //업무연락 && 신청부서결재자 없으면 바로 수신부서로 결재처리
					{

								$tmp=explode(",",$Detail3);
								$tmpcount=count($tmp)-1;
								for($i=0; $i<$tmpcount; $i++)
								{

									$PG_Code=$tmp[$i];

									if($i==0)
									{
										$NewSN_tmp=$NewSN;
									}else
									{
										$NewSN_tmp=$NewSN."-".$i;
									}
										$insql = "insert into SanctionDoc_tbl (  ";
										$insql = $insql." DocSN, FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, RT_SanctionState, PG_Code,  Security, ConservationYear, Account ";
										$insql = $insql." , Detail1, Detail2, Detail3, Detail4, Detail5, MemberInfo  ";
										$insql = $insql." ) values( ";
										$insql = $insql." '$NewSN_tmp', '$FormNum', '$ProjectCode', '$DocTitle', '$filename', '$AttchFile', '$memberID', '$RG_Date', '$RG_Code',  '$RT_Sanction_', '$RT_SanctionState', '$PG_Code',  '$Security', '$ConservationYear', '$Account' ";
										$insql = $insql." , '$Detail1', '$Detail2', '$Detail3', '$Detail4', '$Detail5', '$MemberInfo' ";
										$insql = $insql." )";

										if($dbinsert =="yes"){
											record_log('document', 'InsertAction_7_'.$memberID, $insql);
											$result=mysql_query($insql,$db);
										}else{
											echo "[3sub--- ".$insql."<br>";
										}
								}


					}

					if($dbinsert =="yes"){
						record_log('document', 'InsertAction_8_'.$memberID, $azSQL);
						$result=mysql_query($azSQL,$db);
					}else{
						echo "[3--- ".$azSQL."<br>";
					}



					//****************************************************************************************
					//상신시 다음 결재선이 본인 인경우 접수를 결재로 처리 다음으로 결재진행

					if(strpos($RT_SanctionState,$memberID) !== false) { //"-담당"

							$TmpState = split(":",$RT_SanctionState);
							$SanctionOrder = $TmpState[0].":".$TmpState[1];
							$MemberNum = $TmpState[2]; //$n_num."-담당";
							$ReceiveDate = $TmpState[3];
							$SanctionState = $PROCESS_ACCEPT;
							$SanctionDate = date('Y-m-d');

							//결재자 상세정보 기록
							$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$NewSN', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','')";


							if($dbinsert =="yes"){
								record_log('document', 'InsertAction_9_'.$memberID, $azSQL);
								$result=mysql_query($azSQL,$db);
							}else{
								echo "[4---* ".$azSQL."<br>";
							}


							$tmpFinishMemberNo = $MemberNum.",".$SanctionDate.":";

							if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" || $FormNum=="HMF-6-2" || $FormNum=="BRF-6-2"|| $FormNum=="HMF-8-1" || $FormNum=="HMF-8-2"){ //발신공문.수신공문
								$NEW_SanctionState=NextSanctionState22($RT_Sanction_,$RT_SanctionState);
							}else{
								$NEW_SanctionState=NextSanctionState($RT_Sanction_,$RT_SanctionState);
							}


							//가결 정보 기록
							if($FormNum=="HLF-1-19")
							{

									$tmp=explode(",",$Detail3);
									$tmpcount=count($tmp)-1;
									for($i=0; $i<$tmpcount; $i++)
									{

										if($i==0)
										{
											if(strpos($NEW_SanctionState,$PROCESS_RECEIVE) !== false) //부서로 넘어가면
											{
												$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo',PG_Code='$tmp[$i]' where DocSN='$NewSN'";
											}else
											{
												$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo' where DocSN='$NewSN'";
											}

										}
										if($i>0)
										{
												if(strpos($NEW_SanctionState,$PROCESS_RECEIVE) !== false)
												{
													$DocSN_tmp=$NewSN."-".$i;
													$insql = "insert into SanctionDoc_tbl (  ";
													$insql = $insql." DocSN,RT_SanctionState,FinishMemberNo,PG_Code, FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, Security, ConservationYear, Account, ";
													$insql = $insql." Detail1, Detail2, Detail3, Detail4, Detail5, MemberInfo  ";
													$insql = $insql." ) select '$DocSN_tmp','$NEW_SanctionState','','$tmp[$i]',";
													$insql = $insql." FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, Security, ConservationYear, Account,Detail1, Detail2, Detail3, Detail4, Detail5, MemberInfo";
													$insql = $insql." from SanctionDoc_tbl where DocSN='$NewSN'";


													$insql2 = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN_tmp', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','')";

													if($dbinsert =="yes"){
														record_log('document', 'InsertAction_10_'.$memberID, $insql);
														$result=mysql_query($insql,$db);
														record_log('document', 'InsertAction_11_'.$memberID, $insql2);
														$result=mysql_query($insql2,$db);
													}else{
														echo "[5sub--- ".$insql."<br>";
														echo "[5-1sub--- ".$insql2."<br>";
													}
												}
										}
									}



							}else
							{
								$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo' where DocSN='$NewSN'";
							}

							if($dbinsert =="yes"){
								record_log('document', 'InsertAction_12_'.$memberID, $azSQL);
								$result=mysql_query($azSQL,$db);
							}else{
								echo "[5---* ".$azSQL."<br>";
							}


					}
					else
					{
						$NEW_SanctionState=$RT_SanctionState;
					}



					//****************************************************************************************
					//*알림기능-----------------------------------------------------------------------
					/*
						$SendName=MemberNo2Name($memberID);
						if(strpos($NEW_SanctionState,$PROCESS_RECEIVE) !== false) {

							//처리부서 담당자 체크
							$sql="select distinct(NoticeMember) from approval_tbl where FormName='$FormNum'";
							$re = mysql_query($sql,$db);
							$re_row = mysql_num_rows($re);//총 개수 저장
							if($re_row > 0)
							{
								$NoticeMember=mysql_result($re,0,"NoticeMember");
							}

							$SendIP = MemberNo2Ip($NoticeMember);

						}
						else
						{
							$SendIP=MemberNo2BossIP($NEW_SanctionState,'2');
						}

						if($SendIP <> "")
						{
							$send_string="CMD:ESIGNSEND=".$SendName."=".$SendIP;

							$this->smarty->assign('mode',"msg");
							$this->smarty->assign('send_string',$send_string);
							$this->smarty->display("intranet/js_page.tpl");
						}
					*/


				break;
			}

			// 양식별 추가내용 저장(후처리) --------------------------------------------------------------------
			switch ($FormNum) {


			//출장배차신청서
			case "HMF-2-4":case "BRF-2-4":

				$Dt3=split("/n",$Detail3);
				$Dt4=split("/n",$Detail4);
				$Dt5=split("/n",$Detail5);

				if ($menu_cmd==$PROCESS_APPROVE)
				{
					if ($Dt4[3] == "회사차량")
					{
						if ($Dt5[3] <> "nocar")
						{
							$updatedate = date("Y-m-d");

							$query00 = "insert into schedule_car_tbl (membername,contents,carno,sdate,edate,endtime,insertdate,updatedate,updateuser,DocSN) values('$Dt5[2]','$Detail1','$Dt5[3]','$Dt4[0]','$Dt4[1]','09~18',now(),now(),'$memberID','$NewSN')";


							if($dbinsert =="yes"){
								record_log('document', 'InsertAction_13_'.$memberID, $query00);
								$result=mysql_query($query00,$db);
							}else{
								echo "[6--- ".$query00."<br>";
							}
						}
					}
				}
				break;
			}

			if($dbinsert =="yes")
			{
				if( $mobile == 'y' ){
					$this->smarty->assign('target',"opener");
					$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=4&mobile=$mobile");
				}elseif(strpos($FormNum, "HMF-5-") !== false || strpos($FormNum, "BRF-5-") !== false){
					$this->smarty->assign('target','account_no');
				}elseif($FormNum=="HMF-8-1" || $FormNum=="HMF-8-2")
				{
					$this->smarty->assign('target','file_mng');
				}elseif($FormNum=="HMF-10-1")
				{
					$Detail1_arr=explode("_",$Detail1);

					$PJT_CODE=$Detail1_arr[0];
					$DGREE=$Detail1_arr[1];
					$WBS_CODE=$Detail1_arr[2];
					$ORA_DEPTCODE=$Detail1_arr[3];

					$procedure02="BEGIN Usp_pm_Cont_0801_APPROVAL('11','$PJT_CODE','$DGREE','$WBS_CODE','N','A','HMF-10-1','$memberID');END;";

					//여기다여기

					if($dbinsert=="yes"){
						$this->oracle->ProcedureExcuteQuery($procedure02);

						$this->smarty->assign('target','no');
						$this->smarty->display("intranet/move_page.tpl");
					}
					else{
						echo "확정 oracle : ".$procedure02."<br>";
					}
				}elseif($FormNum=="HMF-10-2")
				{
					$Detail1_arr=explode("_",$Detail1);

					$PJT_CODE=$Detail1_arr[0];
					$DGREE=$Detail1_arr[1];
					$WBS_CODE=$Detail1_arr[2];
					$ORA_DEPTCODE=$Detail1_arr[3];

					$procedure01="BEGIN USP_PM_CONT_INTRA_APPROVAL('11','$PJT_CODE','$DGREE','$WBS_CODE','A','$memberID');END;";
					//여기다여기

					if($dbinsert =="yes"){
						$this->oracle->ProcedureExcuteQuery($procedure01);

						$this->smarty->assign('target','no');
						$this->smarty->display("intranet/move_page.tpl");
					}
					else{
						echo "확정 oracle : ".$procedure01."<br>";
					}
				}else
				{
					$this->smarty->assign('target',"opener");
					$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=4&mobile=$mobile");
				}

				$this->smarty->display("intranet/move_page.tpl");
			}
		}

		//============================================================================
		// 전자결재 수정
		//============================================================================
		function UpdateReadPage()
		{
			include "../inc/approval_function.php";
			include "../util/CommonCodeList.php";

			extract($_REQUEST);
			global $db,$memberID,$ActionMode;
			global $FormNum,$End_index,$Receive_index,$Now_Step,$DocSN;
			global $RG_Code,$PG_Code,$menu_cmd;
			global $dbkey,$doc_status,$DocSN;
			global $Comment,$CompanyKind;

			global $printYN, $satis;
			global $sdate,$edate,$group_code,$send_group,$sub_group_code,$open_check,$selt,$targetKind,$open_type,$currentPage;
			global $Detail6,$Detail_6, $mobile;

			//결재서류 내용표시-----------
			$DocSN = $dbkey;
			$sql="select * from SanctionDoc_tbl where DocSN='$DocSN'";
			//echo $sql."<br>";
			$re = mysql_query($sql,$db);

			$FormNum = mysql_result($re,0,"FormNum");
			$ProjectCode = mysql_result($re,0,"ProjectCode"); //프로젝트코드
			$ProjectName =ProjectCode2Name($ProjectCode);  //약징
			$AttchFile = mysql_result($re,0,"AttchFile");     //첨부문서
			$MemberNo = mysql_result($re,0,"MemberNo");       //기안자 사번
			$Name=MemberNo2Name($MemberNo);
			$RG_Code = mysql_result($re,0,"RG_Code");         //기안자 부서
			$GroupName = Code2Name($RG_Code, 'GroupCode', 0);
			$RG_Date = mysql_result($re,0,"RG_Date");         //기안일
			$PG_Code = mysql_result($re,0,"PG_Code");         //처리(접수)부서
			$PG_Date = mysql_result($re,0,"PG_Date");         //접수일

			$ConservationYear = mysql_result($re,0,"ConservationYear"); //보존년한

			$Account = mysql_result($re,0,"Account");

			//Member_tbl의 부서결재정보 + 경영지원부 서식별 결재선정보
			$RT_Sanction = mysql_result($re,0,"RT_Sanction");

			if($doc_status == $DOC_STATUS_ACCEPT) {
				//처리부서 결재선 설정 및 입력
				$res_06 = mysql_query("SELECT * FROM SystemConfig_Tbl WHERE SysKey='bizform' and Code='$FormNum'",$db);
				$OrderArr = split(";",mysql_result($res_06,0,"CodeORName")); //"부서코드:처리구분:보존년한"
				$RT_Sanction = $RT_Sanction . SanctionArange_Step2($memberID, $OrderArr[2]);
			}
			$RT_SanctionState = mysql_result($re,0,"RT_SanctionState");  //현재 결재자

			$Security = mysql_result($re,0,"Security"); // 보안등급 : 낮은등급으로 설정
			$Account = mysql_result($re,0,"Account");   // 계정과목
			$DocTitle = mysql_result($re,0,"DocTitle"); // 기안 제목


			//기결재자 사번 : 결재자 조회시만 사용, 실Data는 상세에있음
			$FinishMemberNo = mysql_result($re,0,"FinishMemberNo");

			//아래 Detail은 각양식별 페이지에서 보여줌
			$Detail1 = mysql_result($re,0,"Detail1");
			$Detail2 = mysql_result($re,0,"Detail2");
			$Detail3 = mysql_result($re,0,"Detail3");
			$Detail4 = mysql_result($re,0,"Detail4");
			$Detail5 = mysql_result($re,0,"Detail5");
			$Addfile = mysql_result($re,0,"Addfile");

			$MemberInfo=mysql_result($re,0,"MemberInfo");  //직급추가(DB화)


			if($FormNum == "HMF-6-1" || $FormNum == "BRF-6-1" ){
						$Coop=split("/",$ProjectCode);
						$this->smarty->assign('Coop',$Coop);
			}

			$RegDate = array();
			$R_Date = FindSandDate($DocSN); //접수일
			if($R_Date == "0000-00-00") {
				$ItemData=array("Year" =>'&nbsp;',"Month"=>'&nbsp;',"Day"=>'&nbsp;');
			} else {
				$ItemData=array("Year" =>substr($R_Date,2,2),"Month"=>substr($R_Date,5,2),"Day"=>substr($R_Date,8,2));
			}
			array_push($RegDate,$ItemData);

			//참조문서 읽음처리 -------------------------------------------------------
			$confirm_members = mysql_result($re,0,"confirm_members");         //참조
			if($confirm_members<>""){
				$ConferMember2=$memberID."/0";
				if(strpos($confirm_members,$ConferMember2) !== false){
					$ConferMember3=$memberID."/1/".date("Y-m-d");
					$sql="update SanctionDoc_tbl set confirm_members=REPLACE(confirm_members,'$ConferMember2','$ConferMember3') where DocSN='$DocSN'";
					//echo $sql."<br>";
					mysql_query($sql,$db);
					$confirm_members = str_replace($ConferMember2 , $ConferMember3, $confirm_members);
				}

				$confirm_temp = explode( ':', $confirm_members );
				$confirm_members_name = '';
				foreach($confirm_temp as $key => $value){
					if( $value != ''){
						$value_temp = explode( '/', $value );
						// HM00373/0:HM01015/1/2021-09-09:
						$confirm_members_name .= '['.MemberNo2Name($value_temp[0]).' '.MemberNo2Rank($value_temp[0]);
						if( $value_temp[1] == '1' ){
							$confirm_members_name .= '<font color=blue>('.str_replace('-' , '/', substr($value_temp[2], -5, 5)).')</font>';
						}
						$confirm_members_name .= '] ';
					}
				}
			}

			//결재선관련-------------------------------------------------------

			$Receive_index = -1;
			$End_index = 0;


			$sql_doc="select * from systemconfig_tbl where SysKey='bizform' and Code='$FormNum' and Note <> 'hidden' order by code";


			$re_doc = mysql_query($sql_doc,$db);
			$doc_name = mysql_result($re_doc,0,"Name");
			$doc_description = mysql_result($re_doc,0,"Description");
			$doc_CodeORName = mysql_result($re_doc,0,"CodeORName");

			//$doc_CodeORName="02;1;관리자:임원:RECEIVE:FINISH;";   //수신부서,보전연한,1차결재자,2차결재자,1차결재자 action,2차결재자 action
			$DB_Sanction = split(";",$doc_CodeORName);  // 결재선정보

			//결재자정보(DB값)
			$TmpArr = split(":",$RT_Sanction);
			$TmpArrCount=count($TmpArr);
//echo $RT_Sanction;
			$Sanction_data = array();


			if($PG_Code == "") { $PG_Code = $DB_Sanction[0]; } //수신부서: 부서코드가 없는경우 DB의 수신부서로 처리

			$sql="select * from systemconfig_tbl where SysKey = 'GroupCode' and Code='$PG_Code'";
			$re = mysql_query($sql,$db);

			$PGName = @mysql_result($re, 0, "Name");
			$Sanction_Label = split(":",$DB_Sanction[2]);   // 담당:관리자:임원:부서장:접수대기:관리자:부서장:결재종료


			for($i=0; $i<count($Sanction_Label); $i++) {
				if($Sanction_Label[$i] == $PROCESS_FINISH) {
					$End_index = $i; break;
				}
				else if($Sanction_Label[$i] == $PROCESS_RECEIVE) {
					$Receive_index = $i;
				}
			}


			if($Receive_index < 0) { $Receive_index = $End_index+1; }
			$Now_Step = Now_Step($DocSN);
			if(!$Now_Step) { $Now_Step = 0;}



			//결재선
			$Receive=false;

			for($i=0; $i<count($Sanction_Label); $i++) {
				if($Sanction_Label[$i] == $PROCESS_FINISH) {
					$ItemData=array("Label" =>'',"mLabel"=>$PROCESS_FINISH,"mName"=>'',"mCode"=>'',"mStatus"=>'',"mSignStatus"=>'');
				}
				else if($Sanction_Label[$i] == $PROCESS_RECEIVE) {
					$ItemData=array("Label" =>'수신부서',"mLabel"=>$PROCESS_RECEIVE,"mName"=>$PGName,"mCode"=>$PG_Code,"mStatus"=>'',"mSignStatus"=>'');
					$Receive=true;

				}else {
						if($Receive)  //경영지원
						{
							$m_SignStatus=FindSanctionState2($DocSN,$Sanction_Label[$i],$SANCTION_CODE2);
						}
						else  //부서
						{
							$m_SignStatus=FindSanctionState2($DocSN,$Sanction_Label[$i],$SANCTION_CODE);
						}

						$m_tmpName=$this->ApprovalName($TmpArr[$i]);

						$m_Name_arr = split("-",$m_tmpName);
						$m_Name=$m_Name_arr[0];

					if($doc_status <> "VIEWER"){



						if($FormNum=="HMF-6-1")
						{
							$Step=$i+1;
							if($Now_Step<=$Step )
							{
								$m_Status=$this->ApprovalCheck2($TmpArr[$i],$i);
							}
						}else
						{
							$m_Status=$this->ApprovalCheck($TmpArr[$i],$i);
						}


								if($m_Name_arr[1]=="대결"){
									$m_Code=$TmpArr[$i]."-".$m_Name_arr[1];
								}else{
									$m_Code=$TmpArr[$i];
								}
						/*---- 중간결재권자 자신의 결재 임원보여줌 ------------*/

								if($Now_Step <= $i && !$Receive && $doc_status <> "EDIT" && !(strpos($FormNum, "HMF-5-") !== true)){
										$sql="select * from sanctionmember_tbl where MemberNo='$memberID' and SanctionStep='1'";

										$re = mysql_query($sql,$db);
										if(mysql_num_rows($re) > 0) {
												$SanctionMember = mysql_result($re,0,"SanctionMember");
												$KeyName = mysql_result($re,0,"KeyName");
										}

										if($SanctionMember <> ""){
											$m_Status=$this->ApprovalCheck($SanctionMember,$i);
											$m_tmpName=$this->ApprovalName($SanctionMember);
											$m_Name_arr = split("-",$m_tmpName);
											$m_Name=$m_Name_arr[0];
											if($m_Name_arr[1]=="대결")
											{
												//$m_Code=$SanctionMember."-".$m_Name_arr[1];
												$m_Code=$SanctionMember."-부서장";
											}else
											{
												$m_Code=$SanctionMember."-부서장";
											}

										}
								}
						/*---------------------------------------------------*/
					}else{
						//echo "m_SignStatus".$m_SignStatus."<br>";
						if($m_SignStatus==""){
							$StateArray=split(":",$RT_SanctionState);
							if($i==$StateArray[0]-1){
								if($FormNum =="HMF-9-2-s" || $FormNum =="BRF-9-2-s" || $FormNum =="HMF-4-5-s" || $FormNum =="BRF-4-5-s"){
									$m_SignStatus=FindSanctionState_tmp($DocSN);

								}else{
									$m_SignStatus=ProcessingState($DocSN, $StateArray[1], $StateArray[2])."<br><br>결재중";
								}
							}
						}
					}

					echo '<div style="display:none;">';
					echo $i."<br>";
					echo $TmpArr[$i]."<br>";
					echo $m_tmpName."<br>";
					echo $doc_status."<br>";
					echo $m_SignStatus."<br>";
					echo '</div>';


						if((strpos($FormNum, "HMF-5-") !== false) and $manager and ($i == 4) and $TmpState[0] == 4){
							$m_Status=$this->ApprovalCheck($memberID,$i);
							$m_tmpName=$this->ApprovalName($memberID);


							$m_Name_arr = split("-",$m_tmpName);
							$m_Name=$m_Name_arr[0];
							$m_Rank=$m_Name_arr[1];
							$m_Code=$TmpArr[$i];
						}else if((strpos($FormNum, "BRF-5-") !== false) and $manager and ($i == 4) and $TmpState[0] == 4){
							$m_Status=$this->ApprovalCheck($memberID,$i);
							$m_tmpName=$this->ApprovalName($memberID);


							$m_Name_arr = split("-",$m_tmpName);
							$m_Name=$m_Name_arr[0];
							$m_Rank=$m_Name_arr[1];
							$m_Code=$TmpArr[$i];
						}




					$ItemData=array("Label" =>$Sanction_Label[$i],"mLabel"=>$Sanction_Label[$i],"mName"=>$m_Name,"mCode"=>$m_Code,"mStatus"=>$m_Status,"mSignStatus"=>$m_SignStatus);


				}
					if($DocSN=="2019-06072-T03225")
					{

						echo '<div style="display:none1;">';
						echo $i."<br>";
						echo "RT_Sanction".$RT_Sanction."<br>";
						echo "m_Name".$m_Name."<br>";
						echo "m_Code".$m_Code."<br>";
						echo "m_Status".$m_Status."<br>";
						//echo "m_SignStatus".$m_SignStatus."<br>";
						echo '</div>';

					}
					array_push($Sanction_data,$ItemData);



			}

			//echo $DocTitle."<bR>";
			$this->smarty->assign('ActionMode2',$ActionMode);
			$this->smarty->assign('Name',$Name);
			$this->smarty->assign('PGName',$PGName);
			$this->smarty->assign('PG_Code',$PG_Code);
			$this->smarty->assign('TmpArrCount',$TmpArrCount);
			$this->smarty->assign('Sanction_data',$Sanction_data);

			//결재선관련-끝------------------------------------------------------

			//문서별 처리
			if($FormNum=="HMF-4-7" || $FormNum=="BRF-4-7" )  //근태사유서
			{

				if($DocTitle2 <> "")
				{
					$DocTitle=$DocTitle2;
				}

				if($DocTitle=="경유" || $DocTitle=="교육" || $DocTitle=="업무")
				{
					$this->smarty->assign('display','');
				}
				else
				{
					$this->smarty->assign('display','none');
				}

				$Dt1=split("/n",$Detail1);  //일시(시행일)
				$Dt2=split("/n",$Detail2);
				$Dt3=split("/n",$Detail3);
				$Dt4_tmp=split("/n",$Detail4);
				$Detail5 = str_replace('readonly' , '', $Detail5);

				if($doc_status=="EDIT")
				{
					$sqlm="select * from member_tbl where MemberNo = '$memberID'";
					$rem= mysql_query($sqlm,$db);
					$RankCode = @mysql_result($rem, '', "RankCode");

					if(substr($RankCode, 0, 1)=="C") //임원(전무,상무,이사)
					{
						$Dt5[1]= "";
					}else
					{
						$Dt5[1]=$this->NowVacation($memberID);
					}

				}else
				{
					$Dt5=split("/n",$Detail5);
				}
				$this->smarty->assign('Dt3',$Dt3);


				$DetailData = array();
				for($i=0; $i<count($Dt2)-1; $i++) {

						$Dt4_tmp2[$i]=split("=",$Dt4_tmp[$i]);
						if($Dt4_tmp2[$i][0] <> "")
						{
							$Dt4[$i]="시작시간: ".$Dt4_tmp2[$i][1]." /사업명: ".$Dt4_tmp2[$i][0]." /업무내용: ".$Dt4_tmp2[$i][2];
						}

						if($Dt2[$i] <> "")
						{
							$mRank[$i] = MemberNo2Rank($Dt2[$i]);
							$mName[$i] = MemberNo2Name($Dt2[$i]);

							//$ItemData2=array("ID"=>$Dt2[$i],"Rank" =>$mRank[$i],"Name"=>$mName[$i],"Content"=>$Dt3[$i],"Note"=>$Dt4[$i]);

						}else{
							//$ItemData2=array("ID"=>'',"Rank" =>'',"Name"=>'',"Content"=>'',"Note"=>'');
							$mRank[$i] = "";
							$mName[$i] = "";
						}

						//array_push($DetailData,$ItemData2);

				}
				$this->smarty->assign('Dt1',$Dt1);
				$this->smarty->assign('Dt2',$Dt2);
				//$this->smarty->assign('Dt3',$Dt3);
				$this->smarty->assign('Dt4',$Dt4);
				$this->smarty->assign('Dt4_tmp',$Dt4_tmp);
				$this->smarty->assign('Dt4_tmp2',$Dt4_tmp2);
				$this->smarty->assign('Dt5',$Dt5);
				$this->smarty->assign('mName',$mName);
				$this->smarty->assign('mRank',$mRank);
				$this->smarty->assign('DetailData',$DetailData);



			}


			if($FormNum=="HMF-4-8" || $FormNum=="BRF-4-8" )  //휴가계
			{

				$now_vacation=  $this->NowVacation($memberID);
				$Name = MemberNo2Name($MemberNo);

				if($Detail1 == "") { $Detail1=date('Y-m-d'); } //일시(시행일)
				if($Detail2 == "") { $Detail2=date('Y-m-d'); } //일시(시행일)

				$Dt4=split("/n",$Detail4);


				$this->smarty->assign('Name',$Name);
				$this->smarty->assign('Detail1',$Detail1);
				$this->smarty->assign('Detail2',$Detail2);
				$this->smarty->assign('Dt4',$Dt4);

			}

			if($FormNum=="HMF-9-8" || $FormNum=="BRF-9-8" )  //업무주차확인증
			{

				$Name = MemberNo2Name($MemberNo);
				$this->smarty->assign('Name',$Name);
			}

			if($FormNum=="HMF-9-1" || $FormNum=="BRF-9-1")	//연장근무확인서
			{
				$Dt4=split("/n",$Detail4); //야근시업무
				$Dt5=split("/n",$Detail5); //사유

				$this->smarty->assign('Dt4',$Dt4);
				$this->smarty->assign('Dt5',$Dt5);




			}

			if($FormNum=="HMF-2-4" ||$FormNum=="BRF-2-4"  )	//출장배차신청서
			{

					$SelectCar_data = array();
					//회사차량 선택-------------------------------------------------------
					//$sql = "select * from systemconfig_tbl where SysKey='bizcarno' order by orderno";
					// 선우현 DR 엑센트 6210 차량 출장배차 신청서에서 보이지 않게 처리 요청
					$res_device ="select * from systemconfig_tbl where SysKey='bizcarno' and Code<>'6210' order by orderno";
						//echo $res_device."<br>";
						$re_device = mysql_query($res_device,$db);
						while($rec_device = mysql_fetch_array($re_device))
						{
							array_push($SelectCar_data,$rec_device);
						}

					$this->smarty->assign('Dt4',$Dt4);
					$this->smarty->assign('SelectCar_data',$SelectCar_data);

				$DetailData = array();
				$Dt2=split("/n",$Detail2);
				for($i=0; $i<count($Dt2)-1; $i++) {
						if($Dt2[$i] <> "")
						{

							//$mGroup= MemberNo2GroupName($Dt2[$i]);
							//$mRank = MemberNo2Rank($Dt2[$i]);
							$mGroup= MemberNo2GroupNameDate($Dt2[$i],$RG_Date);
							$mRank = MemberNo2RankDate($Dt2[$i],$RG_Date);
							$mName = MemberNo2Name($Dt2[$i]);

							$ItemData2=array("ID"=>$Dt2[$i],"Group" =>$mGroup,"Rank" =>$mRank,"Name"=>$mName);

						}else{
							$ItemData2=array("ID"=>'',"Group" =>'',"Rank" =>'',"Name"=>'');
						}
						array_push($DetailData,$ItemData2);

				}
				$this->smarty->assign('DetailData',$DetailData);

				$Dt3=split("/n",$Detail3);
				$this->smarty->assign('Dt3',$Dt3);

				$Dt4=split("/n",$Detail4);
				$this->smarty->assign('Dt4',$Dt4);

				$Dt5=split("/n",$Detail5);
				$this->smarty->assign('Dt5',$Dt5);

				if($Dt4[3]=="회사차량" && $Dt5[3]<> "" )
				{
					$carsql ="select * from systemconfig_tbl where SysKey='bizcarno' and Code='$Dt5[3]'";
					$re_car = mysql_query($carsql,$db);
					if(mysql_num_rows($re_car) > 0)
					{
						$carname= mysql_result($re_car,0,"Name");
						$this->smarty->assign('carname',$carname);
					}

				}


				$this->smarty->assign('Name',$Name);


			}


			if($FormNum=="HMF-9-2-s" || $FormNum=="HMF-4-5-s" || $FormNum=="BRF-9-2-s" || $FormNum=="BRF-4-5-s" )	//연장근무신청서[개인],휴일근로신청서[개인]
			{

				$Dt2=split("/n",$Detail2);
				$this->smarty->assign('Dt2',$Dt2);
				$ProjectCodexx=change_XX($ProjectCode);
				$ProjectName =ProjectCode2Name($ProjectCodexx);
			}

			if($FormNum=="HMF-9-2" || $FormNum=="HMF-4-5" || $FormNum=="BRF-9-2" || $FormNum=="BRF-4-5")	//연장근무신청서[팀장],휴일근로신청서[팀장]
			{

				$Dt1=explode("/n",$Detail1);
				$Dt2=explode("/n",$Detail2);
				$Dt3=explode("/n",$Detail3);
				$Dt4=explode("/n",$Detail4);
				$Dt4_tmp=explode("<*>",$Detail4);
				$Dt4_1=explode("/n",$Dt4_tmp[0]);
				$Dt4_2=explode("/n",$Dt4_tmp[1]);

				$this->smarty->assign('Dt4_1',$Dt4_1);
				$this->smarty->assign('Dt4_2',$Dt4_2);

				$Dt5=split("/n",$Detail5);
				$this->smarty->assign('Dt1',$Dt1);

				$AttchFile_arr=split("/n",$AttchFile);

				$query_data = array();
				for($i=0; $i<count($Dt2)-1; $i++) {

					if($Dt2[$i] <> "")
					{
						//$ItemData2=array("MemberName"=>$Dt2[$i],"ProjectName" =>$Dt3[$i],"Detail2"=>$Dt4[$i],"MemberNo"=>$Dt5[$i]);
						$ItemData2=array("MemberName"=>$Dt2[$i],"ProjectName" =>$Dt3[$i],"Detail2"=>$Dt4[$i],"MemberNo"=>$Dt5[$i],"AttchFile"=>$AttchFile_arr[$i],"Dt4_1"=>$Dt4_1[$i],"Dt4_2"=>$Dt4_2[$i]);
						array_push($query_data,$ItemData2);
					}


				}
				$this->smarty->assign('query_data',$query_data);
				/*
				$Signer_1=FindSanctionState2($DocSN,"임원",$SANCTION_CODE);
				$Signer_2=FindSanctionState_tmp($DocSN);

				$this->smarty->assign('Signer_1',$Signer_1);
				$this->smarty->assign('Signer_2',$Signer_2);
				*/

			}

			if($FormNum=="HMF-2-3" || $FormNum=="BRF-2-3" )	//비품사용신청서
			{
					$Dt5=split("/n",$Detail5);
					$this->smarty->assign('Dt5',$Dt5);

					$DocT=split("/",$DocTitle);
					$this->smarty->assign('DocT',$DocT);


					$query_data = array();
					$sql="select * from systemconfig_tbl where SysKey='bizdevice' and Name not like '%회의실%' order by code";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {

							$Name=$re_row[Name];
							for($i=0;$i<count($DocT)-1;$i++)
							{
								if($DocT[$i]==$re_row[Name])
								{	$re_row[Chk]="checked";
									break;
								}
							}
							array_push($query_data,$re_row);
					}
					$this->smarty->assign('query_data',$query_data);

			}

			if($FormNum=="HMF-9-3" || $FormNum=="BRF-9-3" )	//회의실사용신청서
			{
					$Dt1=split("/n",$Detail1);
					$this->smarty->assign('Dt1',$Dt1);
			}


			if($FormNum=="HMF-9-4" || $FormNum=="BRF-9-4" )	//명함신청서
			{
					$Dt1=split("/n",$Detail1);
					$Dt2=split("/n",$Detail2);
					$Dt3=split("/n",$Detail3); //Tel/fax
					$Dt4=split("/n",$Detail4); //Mobile/email_1);
					$Dt5=split("/n",$Detail5);

					$this->smarty->assign('Dt1',$Dt1);
					$this->smarty->assign('Dt2',$Dt2);
					$this->smarty->assign('Dt3',$Dt3);
					$this->smarty->assign('Dt4',$Dt4);
					$this->smarty->assign('Dt5',$Dt5);
			}

			if($FormNum=="HMF-4-10" || $FormNum=="BRF-4-10" )	//
			{
				if( $DocSN < '2022-27402-B21321' ){
					$Dt1=split("/n",$Detail1);
					$Dt2=split("/n",$Detail2);

					$vacation_list = array();
					$Detail1 = '';
					$Detail2 = '';
					for($i=0; $i<count($Dt1)-1; $i++) {
						if($Dt2[$i] <> ""){
							$Detail1 .= 'i_'.$Dt1[$i].'_1_09_18';
							$Detail2 .= 'i_'.$Dt2[$i].'_1_09_18';
						}
					}

				}

					if($doc_status == 'EDIT' or $doc_status == 'CREATE'){
						$today = date('Y-m-d');
						$this->smarty->assign('today',$today);
						if( substr( $row[RankCode], 0, 1 ) == 'C' ){ //임원(전무,상무,이사)
							$Detail4= "";
						}else{
							$Detail4=$this->NowVacation($memberID);
							$Detail4 = '기존 : '.$Detail4.', 변경 : '.$Detail4;
						}

						//echo date('Y').substr( $EntryDate, 4, 6 );

						if( $EntryDate < '2017-06-01' ){
							$end_date = date('Y').'-12-31';
						}elseif( date('Y').substr( $EntryDate, 4, 6 ) < $today ){
							$end_date = date("Y-m-d", strtotime((date('Y')+1).substr( $EntryDate, 4, 6 )." -1 day"));
						}else{
							$end_date = date("Y-m-d", strtotime(date('Y').substr( $EntryDate, 4, 6 )." -1 day"));
						}

						$this->smarty->assign('end_date',$end_date);

						$query_data = array();
						$sql = "select * from userstate_tbl where MemberNo = '$memberID' and state in ( 1, 18, 30, 31 ) and start_time >= '$today' order by start_time, state";
						//echo $sql."<br>";
						$re = mysql_query($sql,$db);
						while($re_row = mysql_fetch_array($re)) {
							$split_note = split("/n",$re_row['note']);
							$re_row['vacation_date'] = $re_row['start_time'];
							if( $re_row['state'] == '1' ){
								$re_row['start_time'] = '09';
								$re_row['end_time'] = '18';
							}elseif( $re_row['state'] == '18' ){
								$re_row['start_time'] = $split_note[1];
								$re_row['end_time'] = $split_note[2];
							}elseif( $re_row['state'] == '30' ){
								$re_row['start_time'] = '09';
								$re_row['end_time'] = '14';
							}elseif( $re_row['state'] == '31' ){
								$re_row['start_time'] = '14';
								$re_row['end_time'] = '18';
							}
							array_push($query_data,$re_row);
						}
						$this->smarty->assign('vacation_list',json_encode($query_data));

						$query_data = array();
						$sql = "select * from holyday_tbl where date >= '".date('Y-m-d')."'";
						//echo $sql."<br>";
						$re = mysql_query($sql,$db);
						while($re_row = mysql_fetch_array($re)) {
							array_push($query_data,$re_row['date']);
						}
						$this->smarty->assign('holy_list',json_encode($query_data));
						$this->smarty->assign('EndYear',date('Y'));

						if($vacation != ''){	//노무수령거부 통지
							//$this->smarty->assign('Detail1','연차사용일 변경');
							$this->smarty->assign('vacation','vacation');
						}
					}else{
						$vacation_list = array();

						//echo $Detail4;
						$Detail1_split1 = explode( 'i_', $Detail1 );	//기존 연차

						$ex_Detail1_split1 = '';

						for($i=1; $i < count($Detail1_split1); $i++){
							if( $ex_Detail1_split1 != $Detail1_split1[$i] ){
								$ex_Detail1_split1 = $Detail1_split1[$i];
								$Detail1_split2 = explode( '_', $Detail1_split1[$i] );

								$temp_list = array(
									'vacation_date' => $Detail1_split2[0]
									, 'state' => $Detail1_split2[1]
									, 'start_time' => $Detail1_split2[2]
									, 'end_time' => $Detail1_split2[3]
								);
								array_push($vacation_list,$temp_list);
							}
						}
						$this->smarty->assign('vacation_list',json_encode($vacation_list));
					}

					//연차 당일 변경시 당일이 넘어가면 가결 못하게 체크
					if(date("Y-m-d") > $vacation_list[0]['start_time'] and $Detail1 == "연차사용일 변경"){
						$this->smarty->assign('time_check',"over");
					}
			}

			if(strpos($FormNum, "HMF-5-") !== false || strpos($FormNum, "BRF-5-") !== false ){ //전표

				$this->smarty->assign('report_type',$Detail1);
				$this->smarty->assign('Account',$Account);

				$temp_code = split('-', $Detail2);
				$dateto = $temp_code[1];
				$dept = $temp_code[2];
				$seq = (int)$temp_code[3];

				//증빙자료 존재 유무 확인
				$AddLocation = "./../../../account_file/evidence/".substr($dateto, 0, 4)."/".substr($dateto, 4, 2)."/".substr($dateto, 6, 2)."/".$Detail2.".pdf";
				if(file_exists($AddLocation)){
					$Addfile = $Detail2.".pdf";
				}else{
					$Addfile = "";
				}

				//첨부파일 존재 유무 확인
				$AddLocation = "./../../../account_file/attachfile/".substr($dateto, 0, 4)."/".substr($dateto, 4, 2)."/".substr($dateto, 6, 2)."/".$Detail2;
				if(file_exists($AddLocation)){
					$handle  = opendir($AddLocation);
					$files = array();

					// 디렉터리에 포함된 파일을 저장한다.
					while (false !== ($filename = readdir($handle))) {
						if($filename == "." || $filename == ".."){
							continue;
						}

						// 파일인 경우만 목록에 추가한다.
						if(is_file($AddLocation . "/" . $filename)){
							$files[] = $filename;
						}
					}
					//print_r($files);

					// 핸들 해제
					closedir($handle);
					$this->smarty->assign('attachfile',$files[0]);
				}

				$sqlreceive="select DocSN from sanctiondoc_tbl where Detail2 like '$Detail2'";
				$re_sqlreceive = mysql_query($sqlreceive,$db);
				while($receive_row = mysql_fetch_array($re_sqlreceive)){
					if(strpos($receive_row[DocSN], "TEMP-") !== false){
						$this->smarty->assign('saction_type','TEMP');
					}
				}

				$this->smarty->assign('Doc_Code',$Detail2);
				$this->smarty->assign('dateto',$dateto);
				$this->smarty->assign('dept',$dept);
				$this->smarty->assign('seq',$seq);
			}



			if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1")	//발신공문
			{
				$Dt2=split("/n",$Detail2);

				if($Dt2[2] == ""){
					$Dt2[2] = $PG_Date;
				}
				$this->smarty->assign('Dt2',$Dt2);

				$Dt3=split("/n",$Detail3);
				$this->smarty->assign('Dt3',$Dt3);


				$multyfile=split("/n",$Addfile);

				for($i=0; $i<count($multyfile)-1; $i++) {
					$tmp=split("]",$multyfile[$i]);
					$no=count($tmp)-1;
					$multyfileName[$i]=$tmp[$no];

				}
				$this->smarty->assign('multyfile',$multyfile);
				$this->smarty->assign('multyfileName',$multyfileName);

			}

			if($FormNum=="HMF-6-2" || $FormNum=="BRF-6-2")	//수신공문
			{


				$Dt2=split("/n",$Detail2);
				$Detail2=$Dt2[0];
				$Detail3=$Dt2[1];

				$Dt4=split("/n",$Detail4);
				$this->smarty->assign('Dt4',$Dt4);

				$DocumentCodeName=Code2Name($Dt4[0],'DocumentCode','0') ;


				$this->smarty->assign('DocumentCodeName',$DocumentCodeName);

				$docreceiver="no";
				$this->smarty->assign('docreceiver',$docreceiver);

				$contentfile_temp = split('/SE2/demo/upload/',$Detail1);
				$contentfile = split('"',$contentfile_temp[1]);
				$this->smarty->assign('contentfile',$contentfile);

				$multyfile=split("/n",$Addfile);

				for($i=0; $i<count($multyfile)-1; $i++) {
					$tmp=split("]",$multyfile[$i]);
					$no=count($tmp)-1;
					$multyfileName[$i]=$tmp[$no];

				}
				$this->smarty->assign('multyfile',$multyfile);
				$this->smarty->assign('multyfileName',$multyfileName);
			}


			if($FormNum=="HMF-7-1" || $FormNum=="BRF-7-1")	//업무연락
			{
				$MemberInfo_tmp=split("/n",$MemberInfo);
				$ExtNo=$MemberInfo_tmp[1];



				$this->smarty->assign('MemberInfo2',$MemberInfo_tmp[0]);
				$this->smarty->assign('ExtNo',$ExtNo);

				$multyfile=split("/n",$Addfile);

				for($i=0; $i<count($multyfile)-1; $i++) {
					$tmp=split("]",$multyfile[$i]);
					$no=count($tmp)-1;
					$multyfileName[$i]=$tmp[$no];

				}
				$this->smarty->assign('multyfile',$multyfile);
				$this->smarty->assign('multyfileName',$multyfileName);

			}

			if($FormNum=="HMF-8-1" || $FormNum=="BRF-8-1" || $FormNum=="HMF-8-2" || $FormNum=="BRF-8-2")
			{
				$ProjectName=ProjectViewCode2Name($Detail2);
				$this->smarty->assign('viewProjectCode',$Detail2);

				if($FormNum=="HMF-8-2")
				{
					$PMDeptsql="SELECT
									Name
								FROM
									systemconfig_tbl
								WHERE SysKey='GroupCode'
								AND Code='".sprintf('%02d',$Detail5)."'";
					$PMDeptre=mysql_query($PMDeptsql,$db);
					$PMDeptName=mysql_result($PMDeptre,0,'Name');

					$this->smarty->assign('PMDeptName',$PMDeptName);
					$this->smarty->assign('PMDeptCode',$Detail5);
				}
			}

			if($FormNum=="HMF-10-1")	//외주품의서
			{
				$Detail1_arr=explode("_",$Detail1);

				$PJT_CODE=$Detail1_arr[0];
				$DGREE=$Detail1_arr[1];
				$WBS_CODE=$Detail1_arr[2];

				$procedure01="BEGIN usp_pm_cont_08_print(:entries,'11', '$PJT_CODE', '$DGREE','$WBS_CODE'); END;";

				$datarow=$this->oracle->LoadProcedure($procedure01,"list_data01","","0");

				$CommonCode = new CommonCodeList ( $this->smarty );
				$CommonCode->MakeOption ( "전문공종", 'select_item08', 'Project' );

				for($i=0; $i<count($datarow); $i++){

					$fulldata = array();
					$fulldata = $datarow[$i];

					if($fulldata[item03]==0)
					{
						$fulldata[per]=0;
					}
					else
					{
						$fulldata[per]=round($fulldata[item12]/$fulldata[item03]*100,1);
					}
					$item01_cnt=mb_strlen($fulldata[item01]);
					$fulldata[item01]=mb_substr($fulldata[item01],0,$item01_cnt-1)."_".$WBS_CODE.")";

					$fulldata[item04]=FN_date($fulldata[item04],"-");
					$fulldata[item05]=FN_date($fulldata[item05],"-");
					$fulldata[item09]=FN_date($fulldata[item09],"-");
					$fulldata[item10]=FN_date($fulldata[item10],"-");

				}

				$FolderName=$PJT_CODE.'-'.$DGREE.'-'.$WBS_CODE;

				$DirPath='./../../../intranet_file/documents/HMF-10-1/'.$FolderName;

				//$scanFolder=scandir($DirPath.'/');

				//$FileCount=count($scanFolder);

				if(is_dir($DirPath))
				{
					if($dh=opendir($DirPath))
					{
						while(($file=readdir($dh))!= false)
						{
							if($file!='.' && $file!='..')
							{
								$AttchFile=$file;
							}
						}
					}
				}

				$this->smarty->assign('PJT_CODE',$PJT_CODE);
				$this->smarty->assign('DGREE',$DGREE);
				$this->smarty->assign('WBS_CODE',$WBS_CODE);
				$this->smarty->assign('ORA_DEPTCODE',$ORA_DEPTCODE);
			}

			if($FormNum=="HMF-10-2")
			{
				$Detail1_arr=explode("_",$Detail1);

				$PJT_CODE=$Detail1_arr[0];
				$DGREE=$Detail1_arr[1];
				$WBS_CODE=$Detail1_arr[2];
				$ORA_DEPTCODE=$Detail1_arr[3];

				$ORDER_CONTENTS=$Detail2;

				$CommonCode = new CommonCodeList ( $this->smarty );
				$CommonCode->MakeOption ( "전문공종", 'select_item08', 'Project' );

				$procedure01="BEGIN usp_pm_cont_09_print(:entries,'11', '$PJT_CODE', '$DGREE','$WBS_CODE'); END;";
				$datarow=$this->oracle->LoadProcedure($procedure01,"list_data01","","0");

				for($i=0; $i<count($datarow); $i++){
					$fulldata = array();
					$fulldata = $datarow[$i];
					$item01_cnt=mb_strlen($fulldata[item01]);
					$fulldata[item01]=mb_substr($fulldata[item01],0,$item01_cnt-1)."_".$WBS_CODE.")";
					$fulldata[item04]=FN_date($fulldata[item04],'-');
					$fulldata[item05]=FN_date($fulldata[item05],'-');

				}

				$procedure02 = "BEGIN Usp_Pm_Cont_0901(:entries, '11', '$PJT_CODE', '$ORA_DEPTCODE', '$WBS_CODE'); END;";
				$datarow2 = $this->oracle->LoadProcedure ( $procedure02, "list_data","","0");

				$fulldata2 = array();

				for($i=0; $i<count($datarow2); $i++){
					$datarow2[$i][WORK_S_DATE]=FN_date($datarow2[$i][WORK_S_DATE],'-');
					$datarow2[$i][WORK_E_DATE]=FN_date($datarow2[$i][WORK_E_DATE],'-');
					$datarow2[$i][CONTRACT_DATE]=FN_date($datarow2[$i][CONTRACT_DATE],'-');

					array_push($fulldata2,$datarow2[$i]);
				}

				$FolderName=$PJT_CODE.'-'.$DGREE.'-'.$WBS_CODE;

				$DirPath='./../../../intranet_file/documents/HMF-10-2/'.$FolderName;

				//$scanFolder=scandir($DirPath.'/');

				//$FileCount=count($scanFolder);

				if(is_dir($DirPath))
				{
					if($dh=opendir($DirPath))
					{
						while(($file=readdir($dh))!= false)
						{
							if($file!='.' && $file!='..')
							{
								$AttchFile=$file;
							}
						}
					}
				}

				$this->smarty->assign("ORDER_CONTENTS",$ORDER_CONTENTS);
			}

			if ($doc_status == $DOC_STATUS_CREATE || $doc_status == $DOC_STATUS_EDIT)
			{
				$this->smarty->assign('backgroundcolor','#f5f5f6;');
				$this->smarty->assign('readonly','');
				$this->smarty->assign('Edit',true);
			}else
			{
				$this->smarty->assign('backgroundcolor','');
				$this->smarty->assign('readonly','readonly');
				$this->smarty->assign('Edit',false);
			}

			$ProcessEndChk =strrpos($RT_SanctionState,"FINISH");
			if($ProcessEndChk === false)
			{
				$ProcessEnd=false;
			}else //결재완료되었으면
			{
				$ProcessEnd=true;
			}


			$this->smarty->assign('ProcessEnd',$ProcessEnd);
			$this->smarty->assign('memberID',$memberID);//접속자
			$this->smarty->assign('CompanyKind',$CompanyKind);//회사코드

			$this->smarty->assign('doc_status',$doc_status);
			$this->smarty->assign('DocSN',$dbkey);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('doc_name',$doc_name);
			$this->smarty->assign('satis',$satis);
			$this->smarty->assign('targetKind',$targetKind);
			$this->smarty->assign('currentPage',$currentPage);
			$this->smarty->assign('fulldata',$fulldata);
			$this->smarty->assign('fulldata2',$fulldata2);


			$this->smarty->assign('RegDate',$RegDate);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('ProjectCode',$ProjectCode);
			$this->smarty->assign('ProjectName',$ProjectName);
			$this->smarty->assign('AttchFile',$AttchFile);
			$this->smarty->assign('now_vacation',$now_vacation);

			$this->smarty->assign('MemberNo',$MemberNo);//기안자
			$this->smarty->assign('MemberInfo',$MemberInfo);
			$this->smarty->assign('RG_Code',$RG_Code);
			$this->smarty->assign('RG_Date',$RG_Date);
			$this->smarty->assign('GroupName',$GroupName);
			$this->smarty->assign('PG_Date',$PG_Date);
			$this->smarty->assign('PG_Code',$PG_Code);
			$this->smarty->assign('ConservationYear',$ConservationYear);
			$this->smarty->assign('RT_Sanction',$RT_Sanction);
			$this->smarty->assign('RT_SanctionState',$RT_SanctionState);
			$this->smarty->assign('Security',$Security);
			$this->smarty->assign('Account',$Account);
			$this->smarty->assign('DocTitle',$DocTitle);
			$this->smarty->assign('FinishMemberNo',$FinishMemberNo);
			$this->smarty->assign('confirm_members',$confirm_members);
			$this->smarty->assign('confirm_members_name',$confirm_members_name);

			$this->smarty->assign('Detail1',$Detail1);
			$this->smarty->assign('Detail2',$Detail2);
			$this->smarty->assign('Detail3',$Detail3);
			$this->smarty->assign('Detail4',$Detail4);
			$this->smarty->assign('Detail5',$Detail5);

			$this->smarty->assign('Addfile',$Addfile);
			$this->smarty->assign('open_type',$open_type);
			$this->smarty->assign('printYN',$printYN);
			$this->smarty->assign('mobile',$mobile);

			$this->smarty->assign('PROCESS_APPROVE',$PROCESS_APPROVE);
			$this->smarty->assign('PROCESS_TEMPORARY',$PROCESS_TEMPORARY);
			$this->smarty->assign('PROCESS_ACCEPT',$PROCESS_ACCEPT);
			$this->smarty->assign('PROCESS_REJECTION',$PROCESS_REJECTION);
			$this->smarty->assign('PROCESS_RETURN',$PROCESS_RETURN);
			$this->smarty->assign('PROCESS_BACK',$PROCESS_BACK);
			$this->smarty->assign('PROCESS_FINISH',$PROCESS_FINISH);
			$this->smarty->assign('PROCESS_DECISION',$PROCESS_DECISION);
			$this->smarty->assign('PROCESS_RECEIVE',$PROCESS_RECEIVE);
			$this->smarty->assign('PROCESS_DELETE',$PROCESS_DELETE);

			$this->smarty->assign('DOC_STATUS_CREATE',$DOC_STATUS_CREATE);
			$this->smarty->assign('DOC_STATUS_EDIT',$DOC_STATUS_EDIT);
			$this->smarty->assign('DOC_STATUS_VIEW',$DOC_STATUS_VIEW);
			$this->smarty->assign('DOC_STATUS_APPROVE',$DOC_STATUS_APPROVE);
			$this->smarty->assign('DOC_STATUS_ACCEPT',$DOC_STATUS_ACCEPT);

			$this->smarty->assign('PROCESS_CODE',$PROCESS_CODE);
			$this->smarty->assign('TEMPORARY_CODE',$TEMPORARY_CODE);
			$this->smarty->assign('SANCTION_CODE',$SANCTION_CODE);
			$this->smarty->assign('SANCTION_CODE2',$SANCTION_CODE2);
			$this->smarty->assign('STEP_NO',$STEP_NO);

			$this->smarty->assign("page_action","document_controller.php");
			//echo "RG_Date".$RG_Date."<Br>";
			$this->smarty->display("intranet/common_contents/work_approval/document_input_mvc_jmj.tpl");
		}


		//============================================================================
		// 전자결재 Update Logic
		//============================================================================
		function UpdateAction()
		{

			include "../inc/approval_function.php";
			extract($_REQUEST);
			global $db,$memberID,$db01;

			global $FormNum,$TmpArrCount;

			global $NewSN,$FormNum,$ProjectCode,$DocTitle,$AttchFile,$MemberNo;
			global $RT_Sanction_,$RT_SanctionState,$RT_Sanction;
			global $RG_Code,$Security,$ConservationYear,$Account;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5;
			global $MemberInfo,$DocSN;

			global $Position,$ConservationYear,$RG_Date,$RG_Code,$PG_Code,$PG_Date;
			global $mLabel,$mName,$mCode;

			global $Detail_1,$Detail_2,$Detail_3,$Detail_4,$Detail_5;
			global $menu_cmd,$kind;
			global $Comment,$FinishMemberNo,$MemberNo;

			global $ExtNo,$Rank,$subId,$subName;
			global $AfterMember,$targetKind,$open_type;
			global $confirm_date_input;
			global $Detail6,$Detail_6,$confirm_members, $mobile;



			if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" || $FormNum=="HMF-6-2" || $FormNum=="BRF-6-2"){ //발신공문.수신공문
				$dbinsert="No";
				$dbinsert="yes";
			}else
			{
				$dbinsert="yes";
			}

			if(FN_DevConfirm($memberID)){
				$dbinsert="No";
			}
			if( $MemberNo == 'M20330' ){
				//$dbinsert="No";
			}
			if ($memberID=="B17305")
			{
					//$dbinsert="No";
			}
			if($FormNum=="HMF-10-1")
			{
				//$dbinsert="No";
			}

			$TmpState = split(":",$RT_SanctionState);
			$SanctionOrder = $TmpState[0].":".$TmpState[1];
			$MemberNum = $TmpState[2];
			$ReceiveDate = $TmpState[3];


			$SanctionDate = date('Y-m-d');
			if($menu_cmd == $PROCESS_RECEIVE) {
				$MemberNum = $memberID."-접수";
			}

			$tmpFinishMemberNo=$FinishMemberNo.$MemberNum.",".$SanctionDate.":"; //결재자 정보 누적
			$SendName=MemberNo2Name($memberID);



			//----------------------------------------------------------------------------------
			$sql = "select RT_SanctionState,RG_Date from sanctiondoc_tbl where DocSN like '$DocSN'";
			//echo $sql."<br>";
			$re = @mysql_query($sql,$db);
			$Now_RT_SanctionState = mysql_result($re,0,"RT_SanctionState");
			$Now_RG_Date = mysql_result($re,0,"RG_Date");
			//----------------------------------------------------------------------------------



			if(strpos($FormNum, "HMF-5-") !== false){
				$temp_code = split('-', $Detail2);
				$dateto = $temp_code[1];
				$dept = $temp_code[2];
				$seq = (int)$temp_code[3];
			}

			//가결,부결등 결재처리-------------------------------------------------------------

			switch ($menu_cmd) {
			//가결-------------------------------------------------------
				case $PROCESS_ACCEPT:

					//결재 순서가 맞으면
					if( (strpos($Now_RT_SanctionState, $memberID) !== false and (strpos($Now_RT_SanctionState, $SANCTION_CODE) !== false or strpos($Now_RT_SanctionState, $SANCTION_CODE2) !== false)) ){

						$RT_Sanction_ = "";
						if(strpos($RT_SanctionState ,$SANCTION_CODE) == true)  //처리부서내 //T07301-담당:T03225-팀장:T02211-부서장:RECEIVE:
						{
							for($i=0; $i<=count($mLabel); $i++)
							{
									if($mLabel[$i] == $PROCESS_RECEIVE)
									{
										$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
										break;
									}
									else
									{
										if($mCode[$i] <> "")
										{
												if($i == 0) {
													$RT_Sanction_ = $mCode[$i];
												} else {
													$RT_Sanction_ = $RT_Sanction_.":".$mCode[$i];
												}
										}
										else
										{
												if($i == 0)	{
													$RT_Sanction_ = "";
												}else{
													$RT_Sanction_ = $RT_Sanction_.":";
												}
										}
									}
							}
						}
						else //결의부서내 //T07301-담당:T03225-팀장:T02211-부서장:RECEIVE:B09301-담당:B09201-팀장:M02204-부서장:M01104-대표이사:FINISH
						{
							for($i=0; $i<=count($mLabel); $i++)
							{
									if($mLabel[$i] == $PROCESS_FINISH)
									{
										$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_FINISH;
										break;
									}
									else if($mLabel[$i] == $PROCESS_RECEIVE)
									{
										$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
									} else {
										if($mCode[$i] <> "")
										{
											$mCode_1 = split("-",$mCode[$i]);
											if($i == 0) {
												$RT_Sanction_ = $mCode_1[0]."-".$mLabel[$i];
											} else {
												$RT_Sanction_ = $RT_Sanction_.":".$mCode_1[0]."-".$mLabel[$i];
											}
										}
										else
										{
											if($i == 0) {
												$RT_Sanction_ = "";
											} else {
												$RT_Sanction_ = $RT_Sanction_.":";
											}
										}
									}

								}

						}


						if($open_type == 'package'){
							$RT_Sanction_ = $RT_Sanction;
							$AfterMember2 = $AfterMember;
						}

						//가결 : 상신된 기안 내용을 인정하여 결재하는 행위
						$SanctionState = $PROCESS_ACCEPT;
						if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1" || $FormNum=="HMF-6-2" || $FormNum=="BRF-6-2"|| $FormNum=="HMF-8-1" || $FormNum=="HMF-8-2"){ //발신공문.수신공문

							//******결재자정보재지정(RT_SanctionState)********************************************************************************/

								$RT_Sanction_ = "";
								for($i=0; $i<=7; $i++) {
									if($mLabel[$i] == $PROCESS_RECEIVE) {
										$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
									}else if($mLabel[$i] == $PROCESS_FINISH) {
										$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_FINISH;
									}
									else {
										if($mCode[$i] <> "") {
											if($i == 0) {
												$RT_Sanction_ = $mCode[$i];
											} else {
												$RT_Sanction_ = $RT_Sanction_.":".$mCode[$i];
											}
										} else {
											if($i == 0) {
												$RT_Sanction_ = "";
											} else {
												$RT_Sanction_ = $RT_Sanction_.":";

											}
										}
									}
								}
							//******결재자정보재지정(RT_SanctionState)********************************************************************************/


								//$RT_Sanction_ =":B14302-부서장::RECEIVE:T03225-담당::FINISH:";

								record_log('doccument', 'state1_'.$memberID.'_NextSanctionState22', $RT_Sanction_.'_'.$RT_SanctionState);
								$NEW_SanctionState=NextSanctionState22($RT_Sanction_,$RT_SanctionState);  //다음결재선 지정

								//접수일
								$ArrState = split(":",$RT_SanctionState);

								if($ArrState[0]>4)
								{
										$sql6 ="select PG_Date from SanctionDoc_tbl where DocSN='$DocSN'";
										$re6 = mysql_query($sql6,$db);
										if(mysql_num_rows($re6) > 0)
										{
											$TmpPG_Date=mysql_result($re6,0,"PG_Date");

											if($TmpPG_Date=="0000-00-00" || $TmpPG_Date=="")
											{
													$upsql6 ="update SanctionDoc_tbl set PG_Date='$SanctionDate' where DocSN='$DocSN'";

													if($dbinsert =="yes"){
														record_log('document', 'UpdateAction_1_'.$memberID, $upsql6);
														$result=mysql_query($upsql6,$db);
													}else{
														echo "[HMF-6-1-- ".$upsql6."<br>";
													}
											}
										}
								}else  //수신공문 검토부서 없이 부서에서 만 결재하면 완료처리
								{

									if(strpos($NEW_SanctionState,"FINISH:") !== false)
									{
										$upsql6 ="update SanctionDoc_tbl set PG_Date='$SanctionDate' where DocSN='$DocSN'";

										if($dbinsert =="yes"){
											record_log('document', 'UpdateAction_2_'.$memberID, $upsql6);
											$result=mysql_query($upsql6,$db);
										}else{
											echo "[HMF-6-1-- ".$upsql6."<br>";
										}
									}
								}

								//echo "NEW_SanctionState >  ".$NEW_SanctionState."<br>";

						}else
						{

							record_log('doccument', 'state2_'.$memberID.'_NextSanctionState', $RT_Sanction_.'_'.$RT_SanctionState);
							$NEW_SanctionState=NextSanctionState($RT_Sanction_,$RT_SanctionState);  //다음결재선 지정
						}

						//처리부서 결재선 설정 및 입력 처리부서 접수일 입력
						$azSQL="update SanctionDoc_tbl set RT_Sanction='$RT_Sanction_' where DocSN='$DocSN'";

						if($dbinsert =="yes"){
							record_log('document', 'UpdateAction_3_'.$memberID, $azSQL);
							$result=mysql_query($azSQL,$db);
						}else{
							echo "[1--- ".$azSQL."<br>";
						}

						//  전표관련
						if(strpos($FormNum, "HMF-5-") !== false and $Detail4 != ""){
							$azSQL="update SanctionDoc_tbl set Detail4=CONCAT(Detail4 ,'\n\n$Detail4 - ".MemberNo2Name($memberID)."') where DocSN='$DocSN'";

							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_4_'.$memberID, $azSQL);
								$result=mysql_query($azSQL,$db);
							}else{
								echo "[add_Detail4--- ".$azSQL."<br>";
							}

						}
						/*
						// 21/07/20 정명준 확정순서 변경으로 인한 주석처리
						if(strpos($FormNum, "HMF-5-") !== false and $confirm_date_input){
							$prosql ="BEGIN Usp_Am_Slip_Confirm_Intra_02('$dateto', '$dept', '$seq', '$confirm_date_input', '$memberID', 'UP' ); END;";
							if($dbinsert =="yes"){
								$this->oracle->ProcedureExcuteQuery($prosql);
							}else{
								echo "확정 oracle : ".$prosql."<br>";
							}
						}
						*/

					}//결재 순서가 맞으면

					break;

			//--전결(결의부서 또는 기안부서의 부서장이 대표이사의 결재를 대신하는 행위(결재자 서명란에 "전결"표기, 대표이사 서명란에 "결재자 서명 표기")--
				case $PROCESS_DECISION:

					$SanctionState = $PROCESS_DECISION;
					$NEW_SanctionState=":".$PROCESS_DECISION.":".$MemberNum.":".date('Y-m-d');    //처리부서 결재 완료 처리
					if(ProcessingGroup($DocSN) == $SANCTION_CODE) {
						$NEW_SanctionState=":".$PROCESS_RECEIVE.":".$MemberNum.":".date('Y-m-d'); //결의 부서 결재 완료 처리
					}

					//대표이사란에 전결표기
					$TmpMember = split("-",$MemberNum);
					$MemberNum_ = $TmpMember[0]."-대표이사";
					$SanctionState_ = $PROCESS_ACCEPT;
					$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN', '$MemberNum_', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState_','$Comment')";



					if($dbinsert =="yes"){
						record_log('document', 'UpdateAction_5_'.$memberID, $azSQL);
						$result=mysql_query($azSQL,$db);
					}else{
						echo "[2--- ".$azSQL."<br>";
					}

					break;

			//--부결사용안함--(결의 내용을 승인하지않는 행위(재기안 불가))
				case $PROCESS_REJECTION:

					$SanctionState = $PROCESS_REJECTION;
					$NEW_SanctionState=":".$PROCESS_REJECTION.":".$MemberNum.":".date('Y-m-d').":".$Comment;
					$tmpFinishMemberNo="";
					break;

			//--부결 : 결의 내용을 승인하지않는 행위(반송의견을 반영하여 재기안 가능)
				case $PROCESS_RETURN:

					if($FormNum=="HMF-10-1" || $FormNum=="HMF-10-2")
					{
						$MemberNum=$memberID;
					}

					//부결 로 명칭바꿈
					$SanctionState = $PROCESS_RETURN;
					$NEW_SanctionState=":".$PROCESS_RETURN.":".$MemberNum.":".date('Y-m-d').":".$Comment;
					$tmpFinishMemberNo="";

					//새롭게 결재하기위해서
					$azSQL="delete from sanctionstate_tbl where DocSN='$DocSN'";


					if($dbinsert =="yes"){
						record_log('document', 'UpdateAction_6_'.$memberID, $azSQL);
						$result=mysql_query($azSQL,$db);
					}else{
						echo "[3--- ".$azSQL."<br>";
					}



						//접수후 반려했을때 확정 체크후 승인일자 삭제
						if(strpos($FormNum, "HMF-5-") !== false && strpos($RT_SanctionState,"처리부서내") !== false){	//전표고, 처리부서일때
							/*
							// 21/07/20 정명준 확정순서 변경으로 인한 주석처리
							//승인일자 체크
							$azsql ="BEGIN Usp_Am_Slip_Confirm_Intra_01(:entries,'$dateto','$dept','$seq'); END;";
							$check_value = $this->oracle->LoadProcedure($azsql,"list_data01",$short_name);
							if($check_value[0][1] > 0 ){	//승인일자 존재하면
								$prosql ="BEGIN Usp_Am_Slip_Confirm_Intra_02('$dateto', '$dept', '$seq', '$confirm_date_input', '$memberID', 'DEL' ); END;";
								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($prosql);
								}else{
									echo "승인일자 삭제 oracle : ".$prosql."<br>";
								}
							}
							*/
						}

						if(strpos($FormNum, "HMF-5-") !== false)
						{

							$cfile="../log/".date("Y-m-d")."_HMF-5_RETURN.txt";
							$exist = file_exists("$cfile");
							if($exist) {
								$fd=fopen($cfile,'r');
								$con=fread($fd,filesize($cfile));
								fclose($fd);
							}
							$fp=fopen($cfile,'w');
							$aa=date("Y-m-d H:i");
							$cond=$con.$aa." ".$DocSN." ".$RT_SanctionState." ".$azsql." ".$check_value[0][1]." ".$prosql."\n";
							fwrite($fp,$cond);
							fclose($fp);

						}

						//토스했을경우 삭제
						if(strpos($FormNum, "HMF-5-") !== false){
							$sql = "DELETE FROM approval_account_tbl where DocSN like '$DocSN'";
							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_7_'.$memberID, $sql);
								$result=mysql_query($sql,$db);
							}else{
								echo "[toss del-- ".$sql."<br>";
							}
						}

						if($FormNum=="HMF-10-1")
						{
							$Detail1_arr=explode('_',$Detail1);

							$PJT_CODE=$Detail1_arr[0];
							$DGREE=$Detail1_arr[1];
							$WBS_CODE=$Detail1_arr[2];

							$procedure01="BEGIN USP_PM_CONT_0801_APPROVAL('11','$PJT_CODE','$DGREE','$WBS_CODE','N','R','$FormNum','$MemberNo');END;";

							if($dbinsert =="yes"){
								$this->oracle->ProcedureExcuteQuery($procedure01);
							}else{
								echo "확정 oracle : ".$procedure01."<br>";
							}

						}
						elseif($FormNum=="HMF-10-2")
						{
							$Detail1_arr=explode('_',$Detail1);

							echo print_r($Detail1_arr);

							$PJT_CODE=$Detail1_arr[0];
							$DGREE=$Detail1_arr[1];
							$WBS_CODE=$Detail1_arr[2];
							$ORA_DeptCode=$Detail1_arr[3];

							$procedure02="BEGIN USP_Pm_Cont_Intra_Approval('11','$PJT_CODE','$DGREE','$WBS_CODE','R','$MemberNo');END;";

							if($dbinsert =="yes"){
								$this->oracle->ProcedureExcuteQuery($procedure02);
							}else{
								echo "확정 oracle : ".$procedure02."<br>";
							}
						}

					break;

			//-- 결재선 편집 내용 저장
				case $PROCESS_RECEIVE:
					//결재 순서가 receive가 맞으면
					if( (strpos($Now_RT_SanctionState, "RECEIVE") !== false and $menu_cmd == $PROCESS_RECEIVE) or (strpos($Now_RT_SanctionState, $memberID) !== false and (strpos($Now_RT_SanctionState, $SANCTION_CODE) !== false or strpos($Now_RT_SanctionState, $SANCTION_CODE2) !== false or strpos($Now_RT_SanctionState, $PROCESS_BACK) !== false)) ){

						$RT_Sanction_ = "";
						for($i=0; $i<=count($mLabel); $i++)
						{
								if($mLabel[$i] == $PROCESS_FINISH)
								{
									$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_FINISH;
									break;
								} else if($mLabel[$i] == $PROCESS_RECEIVE) {
									$RT_Sanction_ = $RT_Sanction_.":".$PROCESS_RECEIVE;
								} else {
										if($mCode[$i] <> "") {
												$mCode_1 = split("-",$mCode[$i]);
												if($i == 0) {
													$RT_Sanction_ = $mCode_1[0]."-".$mLabel[$i];
												} else {
													$RT_Sanction_ = $RT_Sanction_.":".$mCode_1[0]."-".$mLabel[$i];
												}
										} else {
												if($i == 0) {
													$RT_Sanction_ = "";
												} else {
													$RT_Sanction_ = $RT_Sanction_.":";
												}
										}
								}
						}

						//접수하기 : 결의부서의 결재 완료시 처리부서로 문서전달된 서류를 담당자가 접수하여 처리부서의 결재 진행
						$SanctionState = $PROCESS_RECEIVE;
						$NEW_SanctionState=NextSanctionState($RT_Sanction_,$RT_SanctionState); // 처리부서의 결재선으로 결재 진행

						if(strpos($FormNum, "HMF-6-") !== false)
						{

							$cfile="../log/".date("Y-m-d")."_HMF-6_RECEIVE.txt";
							$exist = file_exists("$cfile");
							if($exist) {
								$fd=fopen($cfile,'r');
								$con=fread($fd,filesize($cfile));
								fclose($fd);
							}
							$fp=fopen($cfile,'w');
							$aa=date("Y-m-d H:i");
							$cond=$con.$aa." ".$DocSN." ".$RT_Sanction_." ".$RT_SanctionState." ".$NEW_SanctionState."\n";
							fwrite($fp,$cond);
							fclose($fp);

						}

						//부서 접수시 다음 결재선이 본인 인경우 접수를 결재로 처리 다음으로 결재진행
						if(strpos($NEW_SanctionState,$memberID) !== false)
						{ //"-담당"
							//결재자 상세정보 기록
							$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','$Comment')";

							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_8_'.$memberID, $azSQL);
								$result=mysql_query($azSQL,$db);
							}else{
								echo "[4--- ".$azSQL."<br>";
							}

							$TmpState = split(":",$NEW_SanctionState);
							$SanctionOrder = $TmpState[0].":".$TmpState[1];

							$MemberNum = $TmpState[2]; //$n_num."-담당";
							$tmpFinishMemberNo = $tmpFinishMemberNo.$MemberNum.",".$SanctionDate.":";

							$NEW_SanctionState=NextSanctionState($RT_Sanction_,$NEW_SanctionState);
							$SanctionState = $PROCESS_ACCEPT;

							//$SendIP = MemberNo2BossIP($NEW_SanctionState,'2');
							$SendIP = "";
							if($SendIP <> "")
							{
								$send_string="CMD:ESIGNSEND=".$SendName."=".$SendIP;

								$this->smarty->assign('mode',"msg");
								$this->smarty->assign('send_string',$send_string);
								$this->smarty->display("intranet/js_page.tpl");
							}


							//전표
							if(strpos($FormNum, "HMF-5-") !== false){
								$temp_code = split('-', $Detail2);
								$dateto = $temp_code[1];
								$dept = $temp_code[2];
								$seq = (int)$temp_code[3];


								//echo "confirm_date_input".$confirm_date_input."<br>";
								/*
								// 21/07/20 정명준 확정순서 변경으로 인한 주석처리
								if($confirm_date_input){
									$prosql ="BEGIN Usp_Am_Slip_Confirm_Intra_02('$dateto', '$dept', '$seq', '$confirm_date_input', '$memberID', 'UP' ); END;";
									if($dbinsert =="yes"){
										$this->oracle->ProcedureExcuteQuery($prosql);
									}else{
										echo "확정 oracle : ".$prosql."<br>";
									}
								}
								*/

								$prosql ="BEGIN Usp_slipreport_0001('$dateto', '$dept', '$seq', '5' ); END;";
								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($prosql);
									//2018.08.08 주석 품
								}else{
									echo "검토부서결재중 oracle : ".$prosql."<br>";
								}

								//토스 내역 삭제
								if(strpos($FormNum, "HMF-5-") !== false){
									$sql = "DELETE FROM approval_account_tbl where DocSN like '$DocSN'";
									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_9_'.$memberID, $sql);
										$result=mysql_query($sql,$db);
									}else{
										echo "[toss del-- ".$sql."<br>";
									}
								}

								if(strpos($FormNum, "HMF-5-") !== false and $Detail4 != ""){
									$azSQL="update SanctionDoc_tbl set Detail4=CONCAT(Detail4 ,'\n\n$Detail4 - ".MemberNo2Name($memberID)."') where DocSN='$DocSN'";

									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_10_'.$memberID, $azSQL);
										$result=mysql_query($azSQL,$db);
									}else{
										echo "[add_Detail4--- ".$azSQL."<br>";
									}
								}
							}
						}


						//처리부서 결재선 설정 및 입력 처리부서 접수일 입력
						$sql4_1 ="update SanctionDoc_tbl set RT_Sanction='$RT_Sanction_', PG_Date='$SanctionDate' where DocSN='$DocSN'";

						if($dbinsert =="yes"){
							record_log('document', 'UpdateAction_11_'.$memberID, $sql4_1);
							$result=mysql_query($sql4_1,$db);
						}else{
							echo "[4_1-- ".$sql4_1."<br>";
						}

						//업무연락 파일처리
						if($FormNum=="HMF-7-1" or $FormNum=="BRF-7-1")
						{

							$path ="./../../../intranet_file/documents/".$FormNum."/";
							$path_is ="./../../../intranet_file/documents/".$FormNum;

							$sql="select * from sanctiondoc_tbl where DocSN='$DocSN'";
							//echo $sql."<Br>";
							$re = mysql_query($sql,$db);
							while($re_row = mysql_fetch_array($re))
							{
								$Addfile=$re_row[Addfile];
							}
								$multyfile_exist_name=split("/n",$Addfile);
								$multyfile_exist_cnt=count($multyfile_exist_name);


							//여러파일 입력시
							global $multyfile,$multyfile_name,$multyfile_size;

							for($i=0; $i<count($multyfile); $i++) {

								if ($multyfile_exist_name[$i]<>"" and $multyfile[$i]=="")
								{
									$multyfile_exist="yes";
								}

								if ($multyfile_exist_name[$i]=="" and $multyfile[$i]<>"")
								{
									$multyfile_exist="yes";
								}

								if ($multyfile_exist_name[$i]<>"" and $multyfile[$i]<>"")
								{
									$multyfile_exist="yes";
								}

								if ($multyfile_exist=="yes")
								{ //첨부파일 있으면서 수정이면
										if (is_dir ($path_is))
										{}
										else
										{ mkdir($path_is, 0777);	}

										$prefile=time();
										if($multyfile_name[$i] <> "" or $multyfile_exist_name[$i] <> "")
										{

											$multyfile[$i]=stripslashes($multyfile[$i]);
											$_FILES['multyfile']['name'][$i] = iconv("UTF-8", "EUC-KR",$_FILES['multyfile']['name'][$i]);
											$vupload = $path."[".$prefile."]".$_FILES['multyfile']['name'][$i];
											$vupload = str_replace(" ","",$vupload);
											$vupload = str_replace("#","",$vupload);
											//$vupload = str_replace("'","",$vupload);

											$_FILES['multyfile']['tmp_name'][$i] = iconv("UTF-8", "EUC-KR",$_FILES['multyfile']['tmp_name'][$i]);

											if($multyfile_name[$i]<>"")
											{
												$Resultfile_org = file_exists("$multyfile_exist_name[$i]");
												if($Resultfile_org)	{ $re=unlink("$multyfile_exist_name[$i]");}
												move_uploaded_file($_FILES['multyfile']['tmp_name'][$i], $vupload);
											}

											$filename_m="./".$FormNum."/"."[".$prefile."]".$multyfile_name[$i];
											$filename_m = str_replace(" ","",$filename_m);
											$filename_m = str_replace("#","",$filename_m);
											//$filename_m = str_replace("'","",$filename);

											if($multyfile_name[$i]=="")
											{
												$filename_m=$multyfile_exist_name[$i];
											}
											$filename= $filename . $filename_m."/n";
										}
								}else
								{
									//$filename= $filename ."/n";
								}

							}

							$sql4_2 ="update SanctionDoc_tbl set Addfile='$filename' where DocSN='$DocSN'";

							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_12_'.$memberID, $sql4_2);
								$result=mysql_query($sql4_2,$db);
							}else{
								echo "[sql4_2-- ".$sql4_2."<br><br>";
							}

						}

						if($FormNum=="HMF-10-1")
						{
							$docinfosql="SELECT Detail1 FROM sanctiondoc_tbl where DocSN='$DocSN' AND FormNum='$FormNum'";
							$docinfore=mysql_query($docinfosql,$db);

							$Detail1=mysql_result($docinfore,0,"Detail1");

							$Detail1_arr=explode('_',$Detail1);

							$PJT_CODE=$Detail1_arr[0];
							$DGREE=$Detail1_arr[1];
							$WBS_CODE=$Detail1_arr[2];

							$procedure01="BEGIN USP_PM_CONT_0801_APPROVAL('11','$PJT_CODE','$DGREE','$WBS_CODE','N','Y','$FormNum','$MemberNo');END;";

							//여기다여기
							if($dbinsert =="yes"){
								$this->oracle->ProcedureExcuteQuery($procedure01);
							}else{
								echo "확정 oracle : ".$procedure01."<br>";
							}


						}

						else if($FormNum=="HMF-10-2")
						{
							$docinfosql="SELECT Detail1 FROM sanctiondoc_tbl WHERE DocSN='$DocSN' AND FormNum='$FormNum'";

							$docinfore=mysql_query($docinfosql,$db);

							$Detail1=mysql_result($docinfore,0,"Detail1");

							$Detail1_arr=explode('_',$Detail1);

							$PJT_CODE=$Detail1_arr[0];
							$DGREE=$Detail1_arr[1];
							$WBS_CODE=$Detail1_arr[2];
							$ORA_DeptCode=$Detail1_arr[3];

							$procedure02="BEGIN Usp_Pm_Cont_Intra_Approval('11','$PJT_CODE','$DGREE','$WBS_CODE','Y','$MemberNo');END;";

							if($dbinsert =="yes"){
								$this->oracle->ProcedureExcuteQuery($procedure02);
							}else{
								echo "확정 oracle : ".$procedure02."<br>";
							}
						}

					}//결재 순서가 receive가 맞으면

					break;

				case 5:  // 현재 대상 문서 없음, 적용 않음
				//대결 : 결재선의 임직원이 부재시 타임직원이 결재를 대행하는 행위 [전자결재 처리불가, 결재선 변경으로 처리]
				//       (결재자의 서명란에 "결재자 서명" 및 "대결" 표기)
				break;

				case 6:  // 현재 대상 문서 없음, 적용 않음
				//후열 : 현재 결재자 결재보류후 차상위 결재자로 결재진행, 후열된 결재자는 추후 열람 및 결재
				//       (후열시 결재자의 서명란에 "결재자 서명" 및 "후열" 표기)
				break;

				case 7:  // 현재 대상 문서 없음, 적용 않음
				//후결 : 대표이사 부재시 부서장이 결재를 대행 선시행, 대표이사는 추후 열람 및 결재
				//       (대표이사 서명란에 후결로 표시, 대표이사 후결시 서명란에 "결재자 서명" 및 "후결" 표기)
				break;
			}


			//가결,부결등 결재처리-------------------------------------------------------------


			//부서문서함의 부결된문서(자기부서에와서 처리시 PG_Date입력으로 구분)------------------
				if ($menu_cmd == $PROCESS_RETURN){ //부결
					if(strpos($RT_SanctionState ,$PROCESS_RECEIVE) == true){  //처리부서 에서 부결
						$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo',PG_Date='$SanctionDate' where DocSN='$DocSN'";
					}
					else  //기안부서에서 부결
					{
						$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo' where DocSN='$DocSN'";
					}


						//전표 부결일때
						if(strpos($FormNum, "HMF-5-") !== false){

							$temp_code = split('-', $Detail2);
							$dateto = $temp_code[1];
							$dept = $temp_code[2];
							$seq = (int)$temp_code[3];

							$prosql ="BEGIN Usp_slipreport_0001('$dateto', '$dept', '$seq', '1' ); END;";
							if($dbinsert =="yes"){
								$this->oracle->ProcedureExcuteQuery($prosql);
								//$this->oracle->ProcedureExcuteQuery($smssql);
							}else{
								echo "부결 oracle : ".$prosql."<br>";
								//echo "smssql : ".$smssql."<br>";
							}
						}

				}
				else
				{
						if($FormNum=="HMF-7-1" or $FormNum=="BRF-7-1")  //업무연락인경우 수신부서 저장
						{
							if(strpos($NEW_SanctionState,"RECEIVE") !== false) //수신부서로 넘어가면
							{

								$tmp=explode(",",$Detail3);
								$tmpcount=count($tmp)-1;
								for($i=0; $i<$tmpcount; $i++)
								{

									if($i==0)
									{
										$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo',PG_Code='$tmp[$i]' where DocSN='$DocSN'";

									}
									if($i>0)
									{
										$DocSN_tmp=$DocSN."-".$i;
										$insql  = "insert into SanctionDoc_tbl (  ";
										$insql .= " DocSN,RT_SanctionState,FinishMemberNo,PG_Code, FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, Security, ConservationYear, Account, ";
										$insql .= " Detail1, Detail2, Detail3, Detail4, Detail5, MemberInfo ";
										$insql .= " ) select '$DocSN_tmp','$NEW_SanctionState','$tmpFinishMemberNo','$tmp[$i]',";
										$insql .= " FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, Security, ConservationYear, Account,Detail1, Detail2, Detail3, Detail4, Detail5, MemberInfo,AfterMember ";
										$insql .= " from SanctionDoc_tbl where DocSN='$DocSN'";

										if($dbinsert =="yes"){
											record_log('document', 'UpdateAction_13_'.$memberID, $insql);
											$result=mysql_query($insql,$db);
										}else{
											echo "[9sub--- ".$insql."<br><br>";
										}

									}
								}

							}

						}else
						{
								$azSQL = "update SanctionDoc_tbl set RT_SanctionState = '$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo' where DocSN='$DocSN'";
						}
				}
				/*
				if(strpos($FormNum, "HMF-5-") !== false){

						$cfile="../log/".date("Y-m-d")."_5-1.txt";
						$exist = file_exists($cfile);
						if($exist) {
							$fd=fopen($cfile,'r');
							$con=fread($fd,filesize($cfile));
							fclose($fd);
						}

						$fp=fopen($cfile,'w');
						$aa=date("Y-m-d H:i:s");

						$cond=$con."결재=문서명:".$DocSN."#결재자:".$memberID."#일시:".$aa."#sql=".$azSQL." \n";
						//echo $cond."<br>";
						fwrite($fp,$cond);
						fclose($fp);
				}
				*/

				if($dbinsert =="yes"){
					record_log('doccument', 'state3_'.$memberID, $menu_cmd.' - '.$azSQL);
					if($NEW_SanctionState != ''){	//결재가 두번돌아서 NEW_SanctionState가 빈값이 들어갈 경우가 생김. 두번째 들어오는 빈값일때는 예외처리.
						record_log('document', 'UpdateAction_14_'.$memberID, $azSQL);
						$result=mysql_query($azSQL,$db);
					}
				}else{
					echo "[9--- ".$azSQL."<br>";
				}

			//부서문서함의 부결된문서   끝------------------------------------------------------------

			//결재자 상세정보 기록------------------------------------------------------------
				if ($menu_cmd !==$PROCESS_RETURN) // 반송인경우 SanctinSattion를 모두 지워야 한다 / 다시 모든결재 새로하기위해서
				{
					if($FormNum=="HMF-7-1" or $FormNum=="BRF-7-1") {  //업무연락인경우

							if(strpos($RT_SanctionState ,$SANCTION_CODE) == true) //결의(신청)부서내 (1~4단계)
							{
								$tmp=explode(",",$Detail3);
								$tmpcount=count($tmp)-1;
								for($i=0; $i<$tmpcount; $i++)
								{
									$PG_Code=$tmp[$i];

									if($i==0)
									{
										$NewSN_tmp=$DocSN;
									}else
									{
										$NewSN_tmp=$DocSN."-".$i;
									}

									$insql  = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) ";
									$insql .= "values('$NewSN_tmp', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','$Comment')";

									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_15_'.$memberID, $insql);
										$result=mysql_query($insql,$db);
									}else{
											echo "[10*sub--- ".$insql."<br>";
									}
								}//for

							}else{

								//처리부서
								$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','$Comment')";								}
					}else
					{
						$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','$Comment')";
					}

					if($dbinsert =="yes"){
						record_log('document', 'UpdateAction_16_'.$memberID, $azSQL);
						$result=mysql_query($azSQL,$db);
					}else{
						echo "[10--- ".$azSQL."<br>";
					}

					if( strpos($FormNum, "HMF-5-") !== false and $menu_cmd == $PROCESS_RECEIVE ){	//전표 접수일때
						$temp_state1 = explode(":",$NEW_SanctionState);
						//print_r($temp_state1);
						$temp_state2 = explode("-",$temp_state1[2]);
						//print_r($temp_state2);
						$temp_state3 = explode("-",$MemberNum);
						//print_r($temp_state3);
						if( $temp_state2[0] == $temp_state3[0] ){
							$NEW_SanctionState = NextSanctionState($RT_Sanction_,$NEW_SanctionState);
							$tmpFinishMemberNo = $tmpFinishMemberNo.$temp_state1[2].",".$SanctionDate.":";

							$azSQL = "update SanctionDoc_tbl set RT_SanctionState = '$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo' where DocSN='$DocSN'";
							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_16_1'.$memberID, $azSQL);
								$result=mysql_query($azSQL,$db);
							}else{
								echo "[10_1--- ".$azSQL."<br>";
							}

							$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN', '".$temp_state1[2]."', '".$temp_state1[0].":".$temp_state1[1]."', '$ReceiveDate', '$SanctionDate', '$SanctionState','') ";
							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_16_2'.$memberID, $azSQL);
								$result=mysql_query($azSQL,$db);
							}else{
								echo "[10_2--- ".$azSQL."<br>";
							}
						}


					}
				}
			//결재자 상세정보 기록--끝----------------------------------------------------------


			//수신부서 결재자에게 메세지 보내기 -----------------------------------------------------------------
			/*
				if(strpos($NEW_SanctionState,$PROCESS_RECEIVE) !== false) {

					//처리부서 담당자 체크
					$sql="select distinct(NoticeMember) from approval_tbl where FormName='$FormNum'";
					$re = mysql_query($sql,$db);
					$re_row = mysql_num_rows($re);
					if($re_row > 0)
					{
						$NoticeMember=mysql_result($re,0,"NoticeMember");
					}

					$SendIP = MemberNo2Ip($NoticeMember);

					if($SendIP <> "")
					{
						$send_string="CMD:ESIGNSEND=".$SendName."=".$SendIP;

						$this->smarty->assign('mode',"msg");
						$this->smarty->assign('send_string',$send_string);
						$this->smarty->display("intranet/js_page.tpl");
					}

				}
			*/
			//수신부서 결재자에게 메세지 보내기 -----------------------------------------------------------------


			//수신부서 결재처리완료후 처리 -----------------------------------------------------------------------

				if(strpos($NEW_SanctionState,$PROCESS_FINISH) !== false) { //"FINISH" 결재완료, "FINISH-DECISION" 전결

					switch ($FormNum)
					{
						//출장신청서
						case "HMF-2-4":case "BRF-2-4":

							$query01 = "select max(num) from userstate_tbl";
							$result01 = mysql_query($query01,$db);
							$result_num_01 = current(mysql_fetch_array($result01));
							$num_01 = $result_num_01 + 1;
							//$query02 = "insert into userstate_tbl values('$num_01','$MemberNo','$RG_Code','3','$Detail_4[0]','$Detail_4[1]','$ProjectCode','$Detail1','')";

							//-----------------------------------------------------------------------------------------------------
							$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
							//-----------------------------------------------------------------------------------------------------

							$tmp_note="[".$Detail1."]".$DocTitle;
							$query02 = "insert into userstate_tbl (num,MemberNo,GroupCode,state,start_time,end_time,ProjectCode,NewProjectCode,note,sub_code)";
							$query02.=" values('$num_01','$MemberNo','$RG_Code','3','$Detail_4[0]','$Detail_4[1]','$ProjectCode','$NewProjectCode','$tmp_note','')";

							$Today=date("Y-m-d");

							if($Today>=$Detail_4[0] && $Today<=$Detail_4[1] )
							{
								$upabset0="update member_absent_tbl set absent='5',comment='$tmp_note',InputDate=now() where MemberNo='$MemberNo'";

								if($dbinsert =="yes"){
									record_log('document', 'UpdateAction_17_'.$memberID, $upabset0);
									$result=mysql_query($upabset0,$db);
								}else{
									echo "[11-HMF-2-4-- ".$upabset0."<br>";
								}
							}

							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_18_'.$memberID, $query02);
								$result=mysql_query($query02,$db);
							}else{
								echo "[11--- ".$query02."<br>";
							}

							//-- 출장정보 ERP 국내출장품의(정산)서 등록 ---------------------------

							//include "../util/OracleClass.php";
							//$this->oracle=new OracleClass($smarty);

							$Now_RG_Date = str_replace("-","",$Now_RG_Date);
							$Now_StartDay = str_replace("-","",$Detail_4[0]);
							$Now_EndDay = str_replace("-","",$Detail_4[1]);

							$Now_ProjectCode	= FN_projectToColumn($ProjectCode,'oldProjectCode');
							if(strlen($Now_ProjectCode) !="6"){
								$Now_ProjectCode="ZZZZZZ";
							}

							$orasql="BEGIN USP_TRAVEL_INTRANET_IN( '11',  '$MemberNo',  '$Now_RG_Date',  '$Now_RG_Date',  '$Detail1',  '$Now_StartDay',  '$Now_EndDay',  '0',  '0',  '$DocTitle',    '$Now_ProjectCode', '',  'N',  '1',  'N',  'N',  'N',  'N',  'N', '1','$MemberNo',  'N', '$MemberNo'); END;";

							/*
							$cfile="../log/".date("Y-m")."_HMF-2-4.txt";
							$exist = file_exists("$cfile");
							if($exist) {
								$fd=fopen($cfile,'r');
								$con=fread($fd,filesize($cfile));
								fclose($fd);
							}
							$fp=fopen($cfile,'w');
							$aa=date("Y-m-d H:i");
							$cond=$con.$aa." ".$orasql."\n";
							fwrite($fp,$cond);
							fclose($fp);
							*/

							if($dbinsert =="yes"){
								//if($MemberNo=="T03225")
								//{
									//record_log('ProcedureExcuteQuery', $memberID, $orasql);
									$orasql=trim(ICONV("UTF-8","EUC-KR",$orasql));
									$this->oracle->ProcedureExcuteQuery($orasql);
								//}
							}else{
									echo "[11-HMF-2-4-- ".$orasql."<br>";
							}

							//-- 출장정보 ERP 국내출장품의(정산)서 등록 ---------------------------


							//동행자
							for($i=0; $i<=4; $i++)
							{
								if($Detail_2[$i] <> "" and $Detail_2[$i] <> $MemberNo){
									$num_01=$num_01+1;
									$query03 = "select * from member_tbl where MemberNo = '$Detail_2[$i]'";
									$result03 = mysql_query($query03,$db);
									$groupcode = mysql_result($result03,0,"GroupCode");

									//-----------------------------------------------------------------------------------------------------
									$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
									//-----------------------------------------------------------------------------------------------------

									$tmp_note="[".$Detail1."]".$DocTitle;
									$query04 = "insert into userstate_tbl (num,MemberNo,GroupCode,state,start_time,end_time,ProjectCode,NewProjectCode,note,sub_code)";
									$query04.=" values('$num_01','$Detail_2[$i]','$groupcode','3','$Detail_4[0]','$Detail_4[1]','$ProjectCode','$NewProjectCode','$tmp_note','$DocSN')";


									if($Today>=$Detail_4[0] && $Today<=$Detail_4[1] )
									{
										$upabset="update member_absent_tbl set absent='5',comment='$tmp_note',InputDate=now() where MemberNo='$Detail_2[$i]'";
									}


									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_19_'.$memberID, $query04);
										$result=mysql_query($query04,$db);
										record_log('document', 'UpdateAction_20_'.$memberID, $upabset);
										$result=mysql_query($upabset,$db);
									}else{
										echo "[12--- ".$query04."<br>";
									}

									//if( $MemberNo == 'M20330' ){
										//출장정산서 생성
										$orasql="BEGIN USP_TRAVEL_AD_INTRANET_IN( '11',  '$Detail_2[$i]',  '$Now_StartDay',  '$Now_EndDay',  '$MemberNo'); END;";
										if($dbinsert =="yes"){
											$this->oracle->ProcedureExcuteQuery($orasql);
										}else{
											echo "[orasql--- ".$orasql."<br>";
										}
									//}
								}
							}



							if($Detail_4[0] < $Today)
							{
								 $_date1 = explode("-",$Detail_4[1]);
								 $_date2 = explode("-",$Detail_4[0]);

								 $tm1 = mktime(0,0,0,$_date1[1],$_date1[2],$_date1[0]);
								 $tm2 = mktime(0,0,0,$_date2[1],$_date2[2],$_date2[0]);

								 $datediff=($tm1 - $tm2) / 86400;
								 for($i=0;$i<=$datediff;$i++)
								{

									 $EnterDate=date("Y-m-d",strtotime("$Detail_4[0] $i day"));

									 if(holy($EnterDate)=="weekday"){
										for($j=0; $j<=7; $j++)
										{
											if($Detail_2[$j] != "")
											{
												$sql_chk="select * from dallyproject_tbl where EntryTime like '$EnterDate%' and MemberNo='$Detail_2[$j]'";
												$re_chk = mysql_query($sql_chk,$db);
												if(mysql_num_rows($re_chk) == 0)
												{
													if($Detail_2[$j] <> "")
													{
														$SortKey=$this->SortKeyCombination($Detail_2[$j]);
													}

													// 													$insql="insert into dallyproject_tbl(MemberNo,EntryTime,EntryPCode,EntryJobCode,EntryJob,LeaveTime,LeavePCode,LeaveJobCode,LeaveJob,SortKey)";
													// 													$insql.=" values('$Detail_2[$j]','$EnterDate 08:50:00','$ProjectCode','출장','$DocTitle','$EnterDate 18:00:00','$ProjectCode','출장','$DocTitle','$SortKey')";

													$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode

													$insql="insert into dallyproject_tbl(MemberNo,EntryTime,EntryPCode,EntryPCode2,EntryJobCode,EntryJob,LeaveTime,LeavePCode,LeavePCode2,LeaveJobCode,LeaveJob,SortKey)";
													$insql.=" values('$Detail_2[$j]','$EnterDate 08:50:00','$ProjectCode','$NewProjectCode','출장','$DocTitle','$EnterDate 18:00:00','$ProjectCode','$NewProjectCode','출장','$DocTitle','$SortKey')";


													if($dbinsert =="yes"){
														record_log('document', 'UpdateAction_21_'.$memberID, $insql);
														$result=mysql_query($insql,$db);
													}else{
														echo "[12-1-- ".$insql."<br>";
													}

												}

											}
										}


									 }
								}
							}


							//office_plan 입력
							for($j=0; $j<=7; $j++)
							{
								if($Detail_2[$j] != "")
								{
									$msql="select * from member_tbl where MemberNo='$Detail_2[$j]'";
									//echo $msql."<Br>";
									$mre = @mysql_query($msql,$db);
									if(mysql_num_rows($mre) > 0)
									{
										$o_name=$o_name.",".mysql_result($mre,0,"korName");
										$o_memberno=$o_memberno.",".mysql_result($mre,0,"MemberNo");
									}

								}
							}
							//-----------------------------------------------------------------------------------------------------
							$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
							//-----------------------------------------------------------------------------------------------------
							$o_name=$Detail_5[2].$o_name;
							$o_memberno=$MemberNo.$o_memberno;
							$insql2 = "insert into official_plan_tbl (DocSN, o_area, o_itinerary, o_group, o_name, o_start, o_end, o_object, o_traffic, o_passwd, o_note, projectcode, NewProjectCode, memberno, o_change) ";
							$insql2 .= " values('$DocSN', '$Detail1', '$Detail1', '$RG_Code', '$o_name', '$Detail_4[0]', '$Detail_4[1]', '$DocTitle', '$o_traffic', '$o_passwd', '$Detail_4[0]', '$ProjectCode', '$NewProjectCode','$o_memberno', '2')";

							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_22_'.$memberID, $insql2);
								$result=mysql_query($insql2,$db);
							}else{
								echo "[12-2-- ".$insql2."<br>";
							}


							//출장기록 Mystation입력
							/*
							$date_tmp=substr($Detail_4[0],2)."~".substr($Detail_4[1],2);
							$sql_mng="update person_tbl set client_stat='3',description='[$date_tmp]<br>$Detail1' where person_name='$MemberNo'";
							if($dbinsert =="yes")
								$result=mysql_query($sql_mng,$db01);
							else
								echo "[12mng--- ".$sql_mng."<br>";
							*/
						break;


						// 근태사유서
						case "HMF-4-7":case "BRF-4-7":

							$Today=date("Y-m-d");
							if($menu_cmd == "RECEIVE")
							{
								if ($DocTitle !="업무")  //업무시작미입력은 수동으로 넣게처리 /야근미입력은 연장근무확인서에서 처리
								{

									$query01 = "select max(num) from userstate_tbl";
									$result01 = mysql_query($query01,$db);
									$result_num_01 = current(mysql_fetch_array($result01));
									$max_num = $result_num_01 + 1;


									$query02 = "select * from systemconfig_tbl where SysKey = 'UserStateCode' and Code = $Detail_5[0]";
									$result02 = mysql_query($query02,$db);
									$StateCode = mysql_result($result02,0,"Code");
									$ProjectCode=change_code(mysql_result($result02,0,"CodeORName"));

									for($i=0; $i<=7; $i++)
									{
										if($Detail_2[$i] != "")
										{
											//mDt1:직급 mDt2:이름 Detail_3:사유 Detail_4:비고 Detail_2 : 사번

											$query03 = "select * from member_tbl where MemberNo = '$Detail_2[$i]'";
											$result03 = mysql_query($query03,$db);
											$groupcode = mysql_result($result03,0,"GroupCode");



											if ($DocTitle =="연차" || $DocTitle =="오전반차" ||$DocTitle =="오후반차" ||$DocTitle =="경조휴가" ||$DocTitle =="보건휴가" ||$DocTitle =="출산휴가" || $DocTitle =="특별휴가")
											{
												if ($Detail_3[0] !="")
												{
													$Note=$DocTitle."(".$Detail_3[0].")";
												}
												else
												{
													$Note=$DocTitle;
												}
												$tmpMemberNo=$MemberNo;

												$absent_code="7";
											}
											else if ($DocTitle =="시차")
											{


												$Note=$DocTitle.":".$Detail_3[$i]."(".$Detail_1[2]."시~".$Detail_1[3]."시)/n".$Detail_1[2]."/n".$Detail_1[3];

												if($Detail_1[2] < "12" && $Detail_1[3] < "12")
												{
													$sub_code=$Detail_1[3]-$Detail_1[2];
												}else if($Detail_1[2] < "12" && $Detail_1[3] == "12")
												{
													$sub_code=$Detail_1[3]-$Detail_1[2];
												}else if($Detail_1[2] < "12" && $Detail_1[3] > "12")
												{
													$sub_code=$Detail_1[3]-$Detail_1[2]-1;
												}else if($Detail_1[2] == "12" && $Detail_1[3] > "12")
												{
													$sub_code=$Detail_1[3]-$Detail_1[2]-1;
												}else
												{
													$sub_code=$Detail_1[3]-$Detail_1[2];
												}

												$tmpMemberNo=$MemberNo;

												$Detail_1[1]=$Detail_1[0];



											}
											else
											{
												$Note=$DocTitle."(".$Detail_3[$i].")";
												$tmpMemberNo=$Detail_2[$i];
											}

											if ($DocTitle =="경유")  // 경유인경우는 systemconfig_tbl에 프로젝트 코드가 없음 만들어 넣어줌
											{
												//$ProjectCode="H".date("y")."-교휴-06";
												$Dt4=split("=",$Detail_4[$i]);
												$ProjectCode=$Dt4[3];
												$absent_code="13";
											}else if ($DocTitle =="훈련")
											{
												$absent_code="10";
											}else if ($DocTitle =="기타")
											{

												$sqld="select * from dallyproject_tbl where MemberNo='$Detail_2[0]' and EntryTime like '$Detail_1[0]%'";
												$red = mysql_query($sqld,$db);
												if(mysql_num_rows($red) == 0)
												{
													$ProjectCode="H".date("y")."-교휴-06";
													$Note="";
												}else
												{
													$ProjectCode = mysql_result($red,0,"EntryPCode");
													$Note=mysql_result($red,0,"EntryJob");
												}
												$absent_code="13";
											}



											if ($DocTitle =="연차" || $DocTitle =="오전반차" ||$DocTitle =="오후반차" )  //연차인경우 일단위로 넣어줌
											{
													$count = 0;
													$insert_date = "2017-01-01";
													if($Detail_1[1]=="")
													{
														$Detail_1[1]=$Detail_1[0];
													}

													while($insert_date != $Detail_1[1]){
														$insert_date = date("Y-m-d", strtotime($Detail_1[0]."+".$count."day"));
														if(holy($insert_date) == "weekday"){
															//-----------------------------------------------------------------------------------------------------
															$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
															//-----------------------------------------------------------------------------------------------------
															//echo "insert_date --- ".$insert_date."<br>";
															$inSql = "insert into userstate_tbl (num,MemberNo,GroupCode,state,start_time,end_time,ProjectCode,NewProjectCode,note,sub_code)";
															$inSql .= " values('$max_num','$tmpMemberNo','$groupcode','$StateCode','$insert_date','$insert_date','$ProjectCode','$NewProjectCode','$Note','');";


															if($Today==$insert_date)
															{
																$upabset="update member_absent_tbl set absent='$absent_code',comment='$Note',InputDate=now() where MemberNo='$tmpMemberNo'";
															}


															if($dbinsert =="yes"){
																record_log('document', 'UpdateAction_23_'.$memberID, $inSql);
																$result=mysql_query($inSql,$db);
																record_log('document', 'UpdateAction_24_'.$memberID, $upabset);
																$result=mysql_query($upabset,$db);
															}else{
																echo "[7--- ".$inSql."<br>";
															}
															$max_num++;
														}
														$count++;
													}
											}else
											{
												//-----------------------------------------------------------------------------------------------------
												$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
												//-----------------------------------------------------------------------------------------------------
												$inSql = "insert into userstate_tbl (num,MemberNo,GroupCode,state,start_time,end_time,ProjectCode,NewProjectCode,note,sub_code)";
												$inSql.=" values('$max_num','$tmpMemberNo','$groupcode','$StateCode','$Detail_1[0]','$Detail_1[1]','$ProjectCode','$NewProjectCode','$Note','$sub_code')";


													if($Today>=$Detail_1[0] && $Today<=$Detail_1[1] )
													{
														$upabset="update member_absent_tbl set absent='$absent_code',comment='$Note',InputDate=now() where MemberNo='$tmpMemberNo'";
													}

													if($dbinsert =="yes"){
														record_log('document', 'UpdateAction_25_'.$memberID, $inSql);
														$result=mysql_query($inSql,$db);
														record_log('document', 'UpdateAction_26_'.$memberID, $upabset);
														$result=mysql_query($upabset,$db);
													}else{echo "[7--- ".$inSql."<br>";}

													$max_num=$max_num+1;
											}



										}

									}
								}



								if ($DocTitle =="교육")  //지난경우 DaiilyProject에 입력
								{
									$ProjectCode="H".date("y")."-교휴-01";

									$Today=date("Y-m-d");
									if($Detail1 < $Today)
									{
										 $_date1 = explode("-",$Detail_1[1]);
										 $_date2 = explode("-",$Detail_1[0]);

										 $tm1 = mktime(0,0,0,$_date1[1],$_date1[2],$_date1[0]);
										 $tm2 = mktime(0,0,0,$_date2[1],$_date2[2],$_date2[0]);

										 $datediff=($tm1 - $tm2) / 86400;
										 for($i=0;$i<=$datediff;$i++)
										{

											 $EnterDate=date("Y-m-d",strtotime("$Detail_1[0] $i day"));

											 if(holy($EnterDate)=="weekday"){
												for($j=0; $j<=7; $j++)
												{
													if($Detail_2[$j] != "")
													{
														$sql_chk="select * from dallyproject_tbl where EntryTime like '$EnterDate%' and MemberNo='$Detail_2[$j]'";
														//echo $sql_chk."<br>";
														$re_chk = mysql_query($sql_chk,$db);
														if(mysql_num_rows($re_chk) == 0)
														{

															if($Detail_2[$j] <> "")
															{
																$SortKey=$this->SortKeyCombination($Detail_2[$j]);
															}

															// 															$insql="insert into dallyproject_tbl(MemberNo,EntryTime,EntryPCode,EntryJobCode,EntryJob,LeaveTime,LeavePCode,LeaveJobCode,LeaveJob,SortKey)";
															// 															$insql.=" values('$Detail_2[$j]','$EnterDate 08:50:00','$ProjectCode','교육','$Detail_3[$j]','$EnterDate 18:00:00','$ProjectCode','교육','$Detail_3[$j]','$SortKey')";

															$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
															$insql="insert into dallyproject_tbl(MemberNo,EntryTime,EntryPCode,EntryPCode2,EntryJobCode,EntryJob,LeaveTime,LeavePCode,LeavePCode2,LeaveJobCode,LeaveJob,SortKey)";
															$insql.=" values('$Detail_2[$j]','$EnterDate 08:50:00','$ProjectCode','$NewProjectCode','교육','$Detail_3[$j]','$EnterDate 18:00:00','$ProjectCode','$NewProjectCode','교육','$Detail_3[$j]','$SortKey')";


															if($Today==$EnterDate)
															{
																$upabset="update member_absent_tbl set absent='11',comment='$Detail_3[$j]',InputDate=now() where MemberNo='$Detail_2[$j]'";
															}


															if($dbinsert =="yes"){
																record_log('document', 'UpdateAction_27_'.$memberID, $insql);
																$result=mysql_query($insql,$db);
																record_log('document', 'UpdateAction_28_'.$memberID, $upabset);
																$result=mysql_query($upabset,$db);
															}else{echo "[7-1-- ".$insql."<br>";}

														}

													}
												}


											 }
										}
									}



								}//	if ($DocTitle =="교육")


								if ($DocTitle =="업무")  //업무
								{

									 $_date1 = explode("-",$Detail_1[1]);
									 $_date2 = explode("-",$Detail_1[0]);

									 $tm1 = mktime(0,0,0,$_date1[1],$_date1[2],$_date1[0]);
									 $tm2 = mktime(0,0,0,$_date2[1],$_date2[2],$_date2[0]);

									 $datediff=($tm1 - $tm2) / 86400;//두 날짜의 차이 계산 (단위: 일)


									 for($i=0;$i<=$datediff;$i++)
									{

										$EnterDate=date("Y-m-d",strtotime("$Detail_1[0] $i day"));

											for($j=0; $j<=7; $j++)
											{
												if($Detail_2[$j] != "")
												{
													//echo "-------".$Detail_4[$j]."<br>";
													$Dt4=split("=",$Detail_4[$j]);

													$EnterTime =$EnterDate." ".$Dt4[1].":00";
													$EntryJob=$Dt4[2];
													$EntryPCode=$Dt4[3];
													$EntryJobCode=$Dt4[4];

													if($Detail_2[$j] <> "")
													{
														$SortKey=$this->SortKeyCombination($Detail_2[$j]);
													}

													$sql_chk="select * from dallyproject_tbl where EntryTime like '$EnterDate%' and MemberNo='$Detail_2[$j]'";
													//echo $sql_chk."<br>";
													$re_chk = mysql_query($sql_chk,$db);

													// 													if(mysql_num_rows($re_chk) == 0){
													// 														$insql="insert into dallyproject_tbl(MemberNo,EntryTime,EntryPCode,EntryJobCode,EntryJob,SortKey)";
													// 														$insql.=" values('$Detail_2[$j]','$EnterTime','$EntryPCode','$EntryJobCode','$EntryJob','$SortKey')";
													// 													}else{
													// 														$insql="update dallyproject_tbl set EntryTime='$EnterTime' ,EntryPCode='$EntryPCode',EntryJobCode='$EntryJobCode',EntryJob='$EntryJob',SortKey='$SortKey' where MemberNo = '$Detail_2[$j]' and EntryTime like '$EnterDate%'";
													// 													}

													$NewProjectCode	= FN_projectToColumn($EntryPCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
													if(mysql_num_rows($re_chk) == 0){
														$insql="insert into dallyproject_tbl(MemberNo,EntryTime,EntryPCode,EntryPCode2,EntryJobCode,EntryJob,SortKey)";
														$insql.=" values('$Detail_2[$j]','$EnterTime','$EntryPCode','$EntryPCode2','$EntryJobCode','$EntryJob','$SortKey')";
													}else{
														$insql="update dallyproject_tbl set EntryTime='$EnterTime' ,EntryPCode='$EntryPCode' ,EntryPCode2='$NewProjectCode',EntryJobCode='$EntryJobCode',EntryJob='$EntryJob',SortKey='$SortKey' where MemberNo = '$Detail_2[$j]' and EntryTime like '$EnterDate%'";
													}


													if($dbinsert =="yes"){
														record_log('document', 'UpdateAction_29_'.$memberID, $insql);
														$result=mysql_query($insql,$db);
													}else{echo "[7-11-- ".$insql."<br>";}


												}
											}

									}

								} // if ($DocTitle =="업무")

							}

						break; // 근태사유 경영지원부 입력 내용 대체

						//  휴가계
						case "HMF-4-8":case "BRF-4-8":
							if($menu_cmd == "RECEIVE")
							{
								$query01 = "select max(num) from userstate_tbl";
								$result01 = mysql_query($query01,$db);
								$result_num_01 = current(mysql_fetch_array($result01));
								$max_num = $result_num_01 + 1;

								//$query02 = "select * from systemconfig_tbl where SysKey = 'UserStateCode' and Name = '휴가'";
								if($Detail5 == "")
									$Detail5="01";
								$query02 = "select * from systemconfig_tbl where SysKey = 'UserStateCode' and Code = $Detail5";
								$result02 = mysql_query($query02,$db);
								$StateCode = mysql_result($result02,0,"Code");
								$ProjectCode=change_code(mysql_result($result02,0,"CodeORName"));


								for($i=0; $i<=7; $i++)
								{
									$Detail4=$Detail4.str_replace("'","",$Detail_4[$i]);
								}

								if ($Detail4 !="")
								{
									$Note=$DocTitle."(".$Detail4.")";
								}
								else
								{
									$Note=$DocTitle;
								}
								//-----------------------------------------------------------------------------------------------------
								$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
								//-----------------------------------------------------------------------------------------------------
								//$StateCode=change_code($StateCode);
								$inSql  = "insert into userstate_tbl (num ,MemberNo ,GroupCode ,state ,start_time ,end_time ,ProjectCode ,NewProjectCode ,note ,sub_code) ";
								$inSql .= "values('$max_num','$MemberNo','$RG_Code','$StateCode','$Detail1','$Detail2','$ProjectCode','$NewProjectCode','$Note','')";

								if($dbinsert =="yes"){
									record_log('document', 'UpdateAction_30_'.$memberID, $inSql);
									$result=mysql_query($inSql,$db);
								}else{echo "[8--- ".$inSql."<br>";}
							}
						break; // 근태사유 경영지원부 입력 내용 대체


						// 연장근무확인서
						case "HMF-9-1":case "BRF-9-1":
							if(strpos($NEW_SanctionState,$PROCESS_FINISH) !== false)  //"FINISH" 결재완료, "FINISH-DECISION" 전결
							{
								for($i=0; $i<=2; $i++)
								{
									$Detail4=$Detail4.str_replace("'","",$Detail_4[$i]);
								}
								//echo "Detail4 :".$Detail4."<br>";
								for($i=0; $i<=2; $i++)
								{
									$Detail5=$Detail5.str_replace("'","",$Detail_5[$i]);
								}
								//echo "Detail5 :".$Detail5."<br>";

								$EntryTimelike=$Detail1;
								$SelSql = "select * from dallyproject_tbl where MemberNo = '$MemberNo' and EntryTime like '$EntryTimelike%'";
								//echo $SelSql."<br>";
								$re_SelSql = mysql_query($SelSql,$db);

								if(mysql_num_rows($re_SelSql) > 0) {
									$EntryPCode = mysql_result($re_SelSql,0,"EntryPCode");
									$EntryJobCode = mysql_result($re_SelSql,0,"EntryJobCode");

									//echo "EntryPCode".$EntryPCode."<br>";
									//echo "EntryJobCode".$EntryJobCode."<br>";


									$OverTime =$Detail1." ".$Detail2.":00";

									if($Detail3 < "06:00") {$Detail1 = next_day($Detail1);}
									$LeaveTime=$Detail1." ".$Detail3.":00";

									// 									$UpSql="update dallyproject_tbl set LeaveTime='$LeaveTime' ,LeavePCode='$ProjectCode',LeaveJobCode='$EntryJobCode',LeaveJob='$Detail4',OverTime='$OverTime',modify='1'  where MemberNo = '$MemberNo' and EntryTime like '$EntryTimelike%'";

									$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode

									$holy_sc = holycheck($Detail1);
									if ($holy_sc =="holyday") //휴일
									{
										$UpSql="update dallyproject_tbl set LeaveTime='$LeaveTime' ,LeavePCode='$ProjectCode' ,LeavePCode2='$NewProjectCode',LeaveJobCode='$EntryJobCode',LeaveJob='$Detail4',EntryTime='$OverTime',modify='1'  where MemberNo = '$MemberNo' and EntryTime like '$EntryTimelike%'";
									}
									else
									{
										$UpSql="update dallyproject_tbl set LeaveTime='$LeaveTime' ,LeavePCode='$ProjectCode' ,LeavePCode2='$NewProjectCode',LeaveJobCode='$EntryJobCode',LeaveJob='$Detail4',OverTime='$OverTime',modify='1'  where MemberNo = '$MemberNo' and EntryTime like '$EntryTimelike%'";
									}

									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_31_'.$memberID, $UpSql);
										$result=mysql_query($UpSql,$db);
									}else{echo "[11---1 ".$UpSql."<br>";}
								}
								else  //근무기록이 없을때 입력하기
								{

									$OverTime =$Detail1." ".$Detail2.":00";

									if($Detail3 < "06:00") {$Detail1 = next_day($Detail1);}
									$LeaveTime=$Detail1." ".$Detail3.":00";

									$sub_code="a.연장근무";

									$holy_sc = holycheck($Detail1);
									if ($holy_sc =="holyday") //휴일
									{
										$EntryTime=$Detail1." ".$Detail2.":00";
									}
									else
									{
										$EntryTime=$Detail1." "."08:50:00";
									}

									if($MemberNo <> "")
									{
										$SortKey=$this->SortKeyCombination($MemberNo);
									}

									// 									$dallyin = "insert into dallyproject_tbl (MemberNo,EntryTime,EntryPCode,EntryJobCode,EntryJob,LeaveTime,LeavePCode,LeaveJobCode,LeaveJob,OverTime,modify,SortKey) values('$MemberNo','$EntryTime','$ProjectCode','$sub_code','$Detail4','$LeaveTime','$ProjectCode','$sub_code','$Detail4','$OverTime','1','$SortKey')";

									$NewProjectCode	= FN_projectToColumn($ProjectCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
									$dallyin = "insert into dallyproject_tbl (MemberNo,EntryTime,EntryPCode,EntryPCode2,EntryJobCode,EntryJob,LeaveTime,LeavePCode,LeavePCode2,LeaveJobCode,LeaveJob,OverTime,modify,SortKey) values('$MemberNo','$EntryTime','$ProjectCode','$NewProjectCode','$sub_code','$Detail4','$LeaveTime','$ProjectCode','$NewProjectCode','$sub_code','$Detail4','$OverTime','1','$SortKey')";


									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_32_'.$memberID, $dallyin);
										$result=mysql_query($dallyin,$db);
									}else{echo "[11--- ".$dallyin."<br>";}
								}

							}
						break; // 근무시간 경영지원부 입력 내용 대체


						//연장근무신청서(팀장)
						case "HMF-9-2":case "BRF-9-2":
							// 접수일어나면 dallyproject_tbl 에 연장근무 접수 update해줌


							$inMemberNo="";
							$azSQL = "select * from SanctionDoc_tbl where DocSN='$DocSN'";
							$re_SelSql = mysql_query($azSQL,$db);

							if(mysql_num_rows($re_SelSql) > 0) {


								$MemberNo_tmp = mysql_result($re_SelSql,0,"MemberNo");
								$RG_Code_tmp = mysql_result($re_SelSql,0,"RG_Code");
								$Member_num = mysql_result($re_SelSql,0,"Detail5");// M06505/nM08306/nM16314/nM19305/nM20210/n
								$Entry_Time = mysql_result($re_SelSql,0,"Detail1");



								if($memberID=="기존코드"){
									$TmpEntry = split("/n",$Entry_Time);
									if($TmpEntry[1]=="1" || $TmpEntry[1]=="")
									{
										$sql = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState',PG_Date='$SanctionDate' where FormNum in ('HMF-9-2-s','BRF-9-2-s')  and Detail1='$TmpEntry[0]' and Detail3='$MemberNo_tmp'";
									}else
									{
										$sql = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState',PG_Date='$SanctionDate' where FormNum in ('HMF-9-2-s','BRF-9-2-s')  and Detail1='$TmpEntry[0]' and Detail3='$MemberNo_tmp' and Detail5='$TmpEntry[1]'";
									}
									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_33_'.$memberID, $sql);
										$result=mysql_query($sql,$db);
									}else{
										echo  "[17--- ".$sql."<br>";
									}

								}else{
									//검증코드 20201020 ~  20201022까지 테스트후 이상없으면 적용할것
									//안전진단부 이기종 이사  연장근무신청서(팀장) 처리시 팀원별 상신문서 정보 업데이트
									//코드 추가사유 : 팀장이 해당날짜의 연장근무신청서(팀장)을 부서장에게 상신한 후
									//				추가(팀장상신안된상태)로 다른 팀원이 연장근무신청서(개인)을 올린상태에서
									//				부서장이 결재시, 추가로 올려진 연장근무신청서(개인)의 결재정보도 FINISH 로 처리되어버림
									//해결 : 업데이트시 조건추가 (팀장상신 결재문서내 포함된 사번들만 업데이트)

									//검증코드 20201020 ~  20201022까지 테스트후 이상없으면 적용할것
									// ~20201203 테스트후 이상없음
									//신규적용코드 20201204~
									$array_MemberNo =explode("/n",$Member_num); //팀장이 올린 문서내 신청직원들 사원번호  // M06505/nM08306/nM16314/nM19305/nM20210/n
									$cnt55 = count($array_MemberNo);
									for($jj=0; $jj<$cnt55-1; $jj++) {

										if($array_MemberNo[$jj] <> "")
										{
											$TmpEntry = split("/n",$Entry_Time);
											if($TmpEntry[1]=="1" || $TmpEntry[1]=="")
											{
												$sql = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState',PG_Date='$SanctionDate' where FormNum in ('HMF-9-2-s','BRF-9-2-s')  and Detail1='$TmpEntry[0]' and Detail3='$MemberNo_tmp' and MemberNo='$array_MemberNo[$jj]' ";
											}else
											{
												$sql = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState',PG_Date='$SanctionDate' where FormNum in ('HMF-9-2-s','BRF-9-2-s')  and Detail1='$TmpEntry[0]' and Detail3='$MemberNo_tmp' and MemberNo='$array_MemberNo[$jj]' and Detail5='$TmpEntry[1]'";
											}
											if($dbinsert =="yes"){
												record_log('document', 'UpdateAction_33_'.$memberID, $sql);
												$result=mysql_query($sql,$db);
											}else{
												echo  "[17--- ".$sql."<br>";
											}
										}
									}//for






								}



								$TmpState = split("/n",$Member_num);
								$Count_re = count ($TmpState);


								for($i=0; $i<$Count_re; $i++)
								{
									if($TmpState[$i] <> "")
									{

										if($TmpEntry[1]=="2") //휴일근로신청서 금요일날 신청하므로 토,일에 dallyproject_tbl 에 로그인기록이 없을수 있으므로 체크해서 없으면 날짜랑 modify값만 넣어놓음,있으면 연장근무랑 동일하게
										{
											$query11 = "select * from dallyproject_tbl where MemberNo = '$TmpState[$i]' and  EntryTime like '$TmpEntry[0]%'";
											//echo $query11."<br>";
											$result11 = mysql_query($query11,$db);
											$result11_num = mysql_num_rows($result11);
											if($result11_num != 0) { // 있으면
												$aZSql="update dallyproject_tbl set modify='1' where MemberNo = '$TmpState[$i]' and EntryTime like '$TmpEntry[0]%'";
											}else  //없으면
											{


												$aZSql = "insert into dally_reserve_tbl (MemberNo,WorkDate,ReserveTime) values('$TmpState[$i]','$TmpEntry[0]',now())";
												/*
												if($TmpEntry[0]>=$SanctionDate)
												{
													if($TmpState[$i] <> "")
													{
														$SortKey=$this->SortKeyCombination($TmpState[$i]);
													}

													$aZSql = "insert into dallyproject_tbl (MemberNo,EntryTime,modify,SortKey) values('$TmpState[$i]','$TmpEntry[0] 00:00:00','1','$SortKey')";
												}else
												{
													$aZSql ="";
												}
												*/
											}


											if($dbinsert =="yes"){
												record_log('document', 'UpdateAction_34_'.$memberID, $aZSql);
												$result=mysql_query($aZSql,$db);
											}else{
												echo  "[18--- ".$aZSql."<br>";
											}
										}else
										{
											$sql2="update dallyproject_tbl set modify='1' where MemberNo = '$TmpState[$i]' and EntryTime like '$TmpEntry[0]%'";

											if($dbinsert =="yes"){
												record_log('document', 'UpdateAction_35_'.$memberID, $aZSql);
												$result=mysql_query($aZSql,$db);
											}else{
												echo  "[18--- ".$sql2."<br>";
											}
										}

										$inMemberNo=$inMemberNo."'".$TmpState[$i]."',";
									}
								}

								$inMemberNo=substr($inMemberNo,0,strlen($inMemberNo)-1);
								$UpSql="update dallyproject_tbl set modify='1' where MemberNo in($inMemberNo) and EntryTime like '$TmpEntry[0]%'";
								if($dbinsert =="yes"){
									record_log('document', 'UpdateAction_36_'.$memberID, $UpSql);
									$result=mysql_query($UpSql,$db);
								}else{
									echo  "[18--- ".$UpSql."<br>";
								}


								$UpSql2="update dallyproject_tbl set modify='1' where MemberNo in($inMemberNo) and EntryTime like '$TmpEntry[0]%'";
								if($dbinsert =="yes"){
									record_log('document', 'UpdateAction_37_'.$memberID, $UpSql2);
									$result2=mysql_query($UpSql2,$db);
								}else{
									echo  "[18-2-- ".$UpSql2."<br>";
								}



							}

						break;

						//휴일근무신청서(팀장)
						case "HMF-4-5":case "BRF-4-5":
							//금요일날 신청하므로 토,일에 dallyproject_tbl 에 로그인기록이 없을수 있으므로 체크해서 없으면 날짜랑 modify값만 넣어놓음,있으면 연장근무랑 동일하게처리
							$azSQL = "select * from SanctionDoc_tbl where DocSN='$DocSN'";
							//echo  "[155--- ".$azSQL."<br>";

							$re_SelSql = mysql_query($azSQL,$db);

							if(mysql_num_rows($re_SelSql) > 0) {

								$MemberNo_tmp = mysql_result($re_SelSql,0,"MemberNo");
								$RG_Code_tmp = mysql_result($re_SelSql,0,"RG_Code");
								$Member_num = mysql_result($re_SelSql,0,"Detail5");
								$Entry_Time = mysql_result($re_SelSql,0,"Detail1");


								$sql = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState',PG_Date='$SanctionDate' where FormNum in ('HMF-4-5-s','BRF-4-5-s') and RG_Code='$RG_Code_tmp' and Detail1='$Entry_Time' and Detail3='$MemberNo_tmp'";

								if($dbinsert =="yes"){
									record_log('document', 'UpdateAction_38_'.$memberID, $sql);
									$result=mysql_query($sql,$db);
								}else{
									echo  "[171--- ".$sql."<br>";
								}

								$TmpState = split("/n",$Member_num);
								$Count_re = count ($TmpState);

								for($i=0; $i<$Count_re; $i++)
								{
									if($TmpState[$i] <> "")
									{

										$query11 = "select * from dallyproject_tbl where MemberNo = '$TmpState[$i]' and  EntryTime like '$Entry_Time%'";
										//echo $query11."<br>";
										$result11 = mysql_query($query11,$db);
										$result11_num = mysql_num_rows($result11);
										if($result11_num != 0) { // 있으면
											$aZSql="update dallyproject_tbl set modify='1' where MemberNo = '$TmpState[$i]' and EntryTime like '$Entry_Time%'";
										}else  //없으면
										{
											if($TmpState[$i] <> "")
											{
												$SortKey=$this->SortKeyCombination($TmpState[$i]);
											}

											$aZSql = "insert into dallyproject_tbl (MemberNo,EntryTime,modify,SortKey) values('$TmpState[$i]','$Entry_Time 00:00:00','1','$SortKey')";
										}


										//if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_39_'.$memberID, $aZSql);
										$result=mysql_query($aZSql,$db);
										//}else
										//{
										//	echo  "[18--- ".$aZSql."<br>";
										//}
									}
								}
							}

						break;


						//연차휴가 변경계획서------------------------------------------------------------------------------
						case "HMF-4-9":case "BRF-4-9":

							//연차일 변경
							$upsql = "update userstate_tbl set start_time='$Detail2' ,end_time='$Detail2' where MemberNo = '$MemberNo' and start_time = '$Detail1' and state ='1' and ( note like '연차%' or note like '%반차%' ) ";


							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_40_'.$memberID, $upsql);
								mysql_query($upsql, $db);
							}else{
								echo "[HMF-4-9--upsql=".$upsql."<br>";
							}

							if(date("Y-m-d") == $Detail1){
								//로그인정보 검색
								$sql4 = "SELECT InsertDate, EntryPCode, EntryJobCode, EntryJob, ConnectIP, SortKey FROM check_vacation_tbl WHERE MemberNo LIKE '".$MemberNo."' and InsertDate like '$Detail1%' order by InsertDate asc";

								//echo $sql4."<br>";

								$re4     = mysql_query($sql4,$db);
								$re4_num = mysql_num_rows($re4);

								if($re4_num > 0) { // 있으면

									$InsertDate = substr(mysql_result($re4,0,"InsertDate"), 0, 17)."00";
									$EntryPCode = mysql_result($re4,0,"EntryPCode");
									$EntryJobCode = mysql_result($re4,0,"EntryJobCode");
									$EntryJob = mysql_result($re4,0,"EntryJob");
									$ConnectIP = mysql_result($re4,0,"ConnectIP");
									$SortKey = mysql_result($re4,0,"SortKey");

									// 									$dallyin = " INSERT INTO DALLYPROJECT_TBL ";
									// 									$dallyin = $dallyin." (MemberNo, EntryTime, EntryPCode, EntryJobCode, EntryJob, ConnectIP, SortKey) ";
									// 									$dallyin = $dallyin." VALUES ";
									// 									$dallyin = $dallyin." ('".$MemberNo."','".$InsertDate."','".$EntryPCode."','".$EntryJobCode."','".$EntryJob."','".$ConnectIP."','".$SortKey."')";

									$NewProjectCode	= FN_projectToColumn($EntryPCode,'NewProjectCode');		//프로젝트코드 NewProjectCode

									$dallyin = " INSERT INTO DALLYPROJECT_TBL ";
									$dallyin = $dallyin." (MemberNo, EntryTime, EntryPCode, EntryPCode2, EntryJobCode, EntryJob, ConnectIP, SortKey) ";
									$dallyin = $dallyin." VALUES ";
									$dallyin = $dallyin." ('".$MemberNo."','".$InsertDate."','".$EntryPCode."', '".$NewProjectCode."', '".$EntryJobCode."','".$EntryJob."','".$ConnectIP."','".$SortKey."')";

									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_41_'.$memberID, $dallyin);
										mysql_query($dallyin, $db);
									}else{
										echo "[HMF-4-9--dallyin=".$dallyin."<br>";
									}

								}


								$upabset="update member_absent_tbl set absent='7',comment='연차(연차휴가사용계획서)',InputDate=now() where MemberNo='$MemberNo'";

								if($dbinsert =="yes"){
									record_log('document', 'UpdateAction_42_'.$memberID, $upabset);
									mysql_query($upabset, $db);

								}else{
									echo "[HMF-4-9--upabset=".$upabset."<br>";
								}
							}

						break;
						//연차휴가 변경계획서------------------------------------------------------------------------------

						//연차휴가 변경계획서2------------------------------------------------------------------------------
						case "HMF-4-10":case "BRF-4-10":
							if( $DocSN < '2021-35701-M20330' ){

								$Today=date("Y-m-d");

								for($i=0; $i<=count($Detail_1)-1; $i++) {

									if($Detail_2[$i] <> "")
									{
											//연차일 변경
											$upsql = "update userstate_tbl set start_time='$Detail_2[$i]' ,end_time='$Detail_2[$i]' where MemberNo = '$MemberNo' and start_time = '$Detail_1[$i]' and state ='1' and ( note like '연차%' or note like '%반차%' ) ";

											if($dbinsert =="yes"){
												record_log('document', 'UpdateAction_43_'.$memberID, $upsql);
												mysql_query($upsql, $db);
											}else{
												echo "[HMF-4-10--upsql=".$upsql."<br>";
											}

											//로그인정보 검색
											$sql4 = "SELECT InsertDate, EntryPCode, EntryJobCode, EntryJob, ConnectIP, SortKey FROM check_vacation_tbl WHERE MemberNo LIKE '".$MemberNo."' and InsertDate like '$Detail_1[$i]%' order by InsertDate asc";

											if($dbinsert =="yes"){
											}else{
												echo $sql4."<br>";
											}


											$re4     = mysql_query($sql4,$db);
											$re4_num = mysql_num_rows($re4);

											if($re4_num > 0) { // 있으면

												$InsertDate = substr(mysql_result($re4,0,"InsertDate"), 0, 17)."00";
												$EntryPCode = mysql_result($re4,0,"EntryPCode");
												$EntryJobCode = mysql_result($re4,0,"EntryJobCode");
												$EntryJob = mysql_result($re4,0,"EntryJob");
												$ConnectIP = mysql_result($re4,0,"ConnectIP");
												$SortKey = mysql_result($re4,0,"SortKey");

												// 												$dallyin = " INSERT INTO DALLYPROJECT_TBL ";
												// 												$dallyin = $dallyin." (MemberNo, EntryTime, EntryPCode, EntryJobCode, EntryJob, ConnectIP, SortKey) ";
												// 												$dallyin = $dallyin." VALUES ";
												// 												$dallyin = $dallyin." ('".$MemberNo."','".$InsertDate."','".$EntryPCode."','".$EntryJobCode."','".$EntryJob."','".$ConnectIP."','".$SortKey."')";

												$projectViewCode	= FN_projectToColumn($EntryPCode,'projectViewCode');		//프로젝트코드 ViewCode
												$NewProjectCode	= FN_projectToColumn($EntryPCode,'NewProjectCode');		//프로젝트코드 NewProjectCode

												$dallyin = " INSERT INTO DALLYPROJECT_TBL ";
												$dallyin = $dallyin." (MemberNo, EntryTime, EntryPCode, EntryPCode2, EntryJobCode, EntryJob, ConnectIP, SortKey) ";
												$dallyin = $dallyin." VALUES ";
												$dallyin = $dallyin." ('".$MemberNo."','".$InsertDate."','".$EntryPCode."', '".$NewProjectCode."', '".$EntryJobCode."','".$EntryJob."','".$ConnectIP."','".$SortKey."')";

												if($dbinsert =="yes"){
													record_log('document', 'UpdateAction_44_'.$memberID, $dallyin);
													mysql_query($dallyin, $db);
												}else{
													echo "[HMF-4-10--dallyin=".$dallyin."<br>";
												}
											}




											if($Today==$Detail_2[$i])
											{
												$upabset="update member_absent_tbl set absent='7',comment='연차(연차휴가사용계획서)',InputDate=now() where MemberNo='$MemberNo'";
												if($dbinsert =="yes"){
													record_log('document', 'UpdateAction_45_'.$memberID, $upabset);
													mysql_query($upabset, $db);
												}else{
													echo "[HMF-4-10--upabset=".$upabset."<br>";
												}
											}





									}
								}
							}else{
								//연차일 삭제후 새로 추가. 연차를 반차 두개로 나눌경우가 있어서 수정으로는 안됨.

								//인트라넷 연차 삭제
									// ex) i_2021-11-25_18_16_18i_2021-11-29_30_09_14i_2021-12-30_1_09_18i_2021-12-31_1_09_18i_2021-12-31_1_09_18
									$Detail1_split1 = explode( 'i_', $Detail1 );	//원래 연차
									$DelSql = "delete from userstate_tbl where MemberNo = '$MemberNo' and ( ";
									for($i=1; $i < count($Detail1_split1); $i++){
										$Detail1_split2 = explode( '_', $Detail1_split1[$i] );
										if($i != 1){ $DelSql .= " or "; }
										$DelSql .= " ( start_time='".$Detail1_split2[0]."' and state = '".$Detail1_split2[1]."' ";
										//시차일때 시차도 체크.
										if( $Detail1_split2[1] == '18' ){
											$DelSql .= " and note like '%/n".$Detail1_split2[2]."/n".$Detail1_split2[3]."'";
										}
										$DelSql .= " ) ";
									}
									$DelSql .= " ); ";
									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_53_'.$memberID, $DelSql);
										mysql_query($DelSql, $db);
									}else{
										echo "HMF-4-10--DelSql = ".$DelSql."<br>";
									}

								//인트라넷 연차 생성
									$Detail2_split1 = explode( 'i_', $Detail2 );	//변경 연차
									sort($Detail2_split1);	//날짜순서로 정렬

									$note_arr = array( '1' => '연차' , '18' => '시차', '30' => '오전반차', '31' => '오후반차' );

									for($i=1; $i < count($Detail2_split1); $i++){
										$Detail2_split2 = explode( '_', $Detail2_split1[$i] );
										if( $Detail2_split2[1] != '99' ){
											if( $Detail2_split2[1] == '18' ){
												$note = '시차:테스트(17시~18시)/n17/n18';
												$note = $note_arr[$Detail2_split2[1]].":$Detail3(".$Detail2_split2[2]."시~".$Detail2_split2[3]."시)/n".$Detail2_split2[2]."/n".$Detail2_split2[3]."";
												$sub_code = ( $Detail2_split2[3]*1 - $Detail2_split2[2]*1 );
												if( $Detail2_split2[2]*1 < 13 and $Detail2_split2[3]*1 > 12 ){
													$sub_code--;
												}
											}else{
												$note = $note_arr[$Detail2_split2[1]]."($Detail3)";
												$sub_code = '';
											}
											$InSql = "
												insert into userstate_tbl (
													MemberNo, GroupCode, state
													, start_time, end_time, ProjectCode
													, NewProjectCode, note, sub_code
												) values (
													'$MemberNo', '$RG_Code', '".$Detail2_split2[1]."'
													, '".$Detail2_split2[0]."', '".$Detail2_split2[0]."', 'HXX-교휴-04'
													, 'HV009104', '$note', '$sub_code'
												)
											";
											if($dbinsert =="yes"){
												record_log('document', 'UpdateAction_54_'.$memberID, $InSql);
												mysql_query($InSql, $db);
											}else{
												echo "HMF-4-10--InSql = ".$InSql."<br>";
											}
										}
									}

							}


						break;
						//연차휴가 변경계획서2------------------------------------------------------------------------------


						//전표
						//case "HMF-5-1": case "HMF-5-2": case "HMF-5-3": case "HMF-5-4": case "HMF-5-5": case "HMF-5-6": case "HMF-5-7": case "HMF-5-8":
						case "HMF-5-1": case "HMF-5-2": case "HMF-5-3": case "HMF-5-4": case "HMF-5-5": case "HMF-5-6": case "HMF-5-7": case "HMF-5-8":
							$sql = "update SanctionDoc_tbl set Detail4='' where DocSN='$DocSN'";

							$temp_code = split('-', $Detail2);
							$dateto = $temp_code[1];
							$dept = $temp_code[2];
							$seq = (int)$temp_code[3];

							//전표 확정처리
							$prosql ="BEGIN Usp_Am_Slip_Confirm_Intra_02('$dateto', '$dept', '$seq', '".date('Ymd')."', '$memberID', 'UP' ); END;";
							if($dbinsert =="yes"){
								$this->oracle->ProcedureExcuteQuery($prosql);
							}else{
								echo "확정 oracle : ".$prosql."<br>";
							}

							$prosql ="BEGIN Usp_slipreport_0001('$dateto', '$dept', '$seq', '6' ); END;";
							if($dbinsert =="yes"){
								record_log('document', 'UpdateAction_46_'.$memberID, $sql);
								$result=mysql_query($sql,$db);
								$this->oracle->ProcedureExcuteQuery($prosql);
							}else{
								echo "[HMF-5--after-- ".$sql."<br>";
								echo "결재완료 oracle : ".$prosql."<br>";
							}
						break;


						//발신공문
						case "HMF-6-1": case "BRF-6-1":
								$ThisYear=date("Y");
								$sql="select lpad(substr(Max(Detail4),8,4)+1,4,0) as docnumber from sanctiondoc_tbl where FormNum in('HMF-6-1','BRF-6-1') and RG_Date like '".date("Y")."%'";

								$re = @mysql_query($sql,$db);
								if(mysql_num_rows($re) > 0)
								{
									$docnumber=mysql_result($re,0,"docnumber");
									if($docnumber=="")	{
										$docnumber="0001";
										//$docnumber="4000";
									}
								}

								$Detail4=date('Ym')."-".$docnumber;
								$sql2 = "update SanctionDoc_tbl set Detail4='$Detail4' where DocSN='$DocSN'";

								//결재완료후 Finish 안들어가는경우 예외처리
								$sql3="select RT_SanctionState from SanctionDoc_tbl where DocSN='$DocSN'";
								$re3 = @mysql_query($sql3,$db);
								if(mysql_num_rows($re3) > 0)
								{
									$RT_SanctionState=mysql_result($re3,0,"RT_SanctionState");
									if($RT_SanctionState=="")
									{
										if($NEW_SanctionState=="")
										{
											$NEW_SanctionState="7:FINISH::".date("Y-m-d");
										}
										$upsql = "update SanctionDoc_tbl set RT_SanctionState ='$NEW_SanctionState' where DocSN='$DocSN'";

											if($dbinsert =="yes"){
												record_log('document', 'UpdateAction_47_'.$memberID, $upsql);
												$result=mysql_query($upsql,$db);
											}else{
												echo "[발신공문-after-- ".$upsql."<br>";
											}
									}

								}




								if($dbinsert =="yes"){
									record_log('document', 'UpdateAction_48_'.$memberID, $sql2);
									$result=mysql_query($sql2,$db);
								}else{
									echo "[발신공문-after-- ".$sql2."<br>";
								}
						break;

						//자료신청서(프로젝트 자료)----------------------------------------------------------
						case "HMF-8-1": case "BRF-8-1":

							//$sql = "update Sanctionc_tbl set Detail4='' where DocSN='$DocSN'";
							$sql="select * from SanctionDoc_tbl where DocSN='$DocSN'";
							$re=mysql_query($sql,$db);

							while($re_row=mysql_fetch_array($re))
							{
								if($re_row['PG_Date']>"0000-00-00")
								{
									$Sancstate_arr=explode(':',$re_row['RT_SanctionState']);
									$Finish_arr=explode(':',$re_row['FinishMemberNo']);

									for($row=0;$row<count($Finish_arr)-1;$row++)
									{
										$Fin_Value=$Finish_arr[$row];

									}

									$Finish_member_arr=explode(',',$Fin_Value);
									$Finish_memberNo=$Finish_member_arr[0];

									for($index=0;$index<count($Sancstate_arr);$index++)
									{
										if($index==0)
										{
											$Sancstate=$Sancstate_arr[$index];
										}
										elseif($index>1)
										{
											if($Sancstate_arr[$index]=='FINISH')
											{
												$Sancstate.=":".$Sancstate_arr[$index].":".$Finish_memberNo;
											}
											else
											{
												$Sancstate.=":".$Sancstate_arr[$index];
											}
										}
									}

									$sql2="UPDATE SanctionDoc_tbl SET RT_SanctionState='$Sancstate' WHERE DocSN='$DocSN'";

									$sql4="SELECT Mobile FROM member_tbl where MemberNo='".$re_row['MemberNo']."'";
									$re4=mysql_query($sql4,$db);

									$MemberMobile=mysql_result($re4,0,'Mobile');

									if($dbinsert =="yes"){
										include "../util/OracleClass.php";

										$this->oracle=new OracleClass($smarty,'SAMAN');
									}

									$msg=iconv("utf-8","euc-kr","상신하신 자료 요청서 결재가 완료되었습니다.");

									$orapro="BEGIN usp_sms_send_iu('$MemberMobile', '01080096172', '$msg' , 'SMS'); END;";

									$sql3="UPDATE tn_file_revise_tbl SET DOC_NO='$DocSN' WHERE PAGE='$re_row[Detail4]' AND NO='$re_row[Detail5]'";

									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_49_'.$re_row['MemberNo'], $sql2);
										mysql_query($sql2,$db);
										$this->oracle->ProcedureExcuteQuery($orapro);
										record_log('document', 'UpdateAction_50_'.$re_row['MemberNo'], $sql3);
										mysql_query($sql3, $db);
									}else{
										echo "자료 신청 결재완료 : ".$sql."<br>";
									}
								}
							}

							break;

							//자료신청서(성과물)----------------------------------------------------------
						case "HMF-8-2": case "BRF-8-2":

							$sql="select * from SanctionDoc_tbl where DocSN='$DocSN'";
							$re=mysql_query($sql,$db);

							while($re_row=mysql_fetch_array($re))
							{
								if($re_row['PG_Date']>"0000-00-00")
								{
									$Sancstate_arr=explode(':',$re_row['RT_SanctionState']);
									$Finish_arr=explode(':',$re_row['FinishMemberNo']);

									for($row=0;$row<count($Finish_arr)-1;$row++)
									{
										$Fin_Value=$Finish_arr[$row];

									}

									$Finish_member_arr=explode(',',$Fin_Value);
									$Finish_memberNo=$Finish_member_arr[0];

									for($index=0;$index<count($Sancstate_arr);$index++)
									{
										if($index==0)
										{
											$Sancstate=$Sancstate_arr[$index];
										}
										elseif($index>1)
										{
											if($Sancstate_arr[$index]=='FINISH')
											{
												$Sancstate.=":".$Sancstate_arr[$index].":".$Finish_memberNo;
											}
											else
											{
												$Sancstate.=":".$Sancstate_arr[$index];
											}
										}
									}

									$sql2="UPDATE SanctionDoc_tbl SET RT_SanctionState='$Sancstate' WHERE DocSN='$DocSN'";

									$sql4="SELECT Mobile FROM member_tbl where MemberNo='".$re_row['MemberNo']."'";
									$re4=mysql_query($sql4,$db);

									$MemberMobile=mysql_result($re4,0,'Mobile');

									$Detail4_arr=explode('_',$re_row['Detail4']);

									$sql3="UPDATE tn_file_revise_tbl SET DOC_NO='$DocSN' WHERE PAGE='".$Detail4_arr[0]."' AND NO='".$Detail4_arr[1]."'";

									$oradownauth= "insert into saman_default.person_access_project_tbl
														(memberID, projectcode, start_date, end_date, download_count, download_run, download_mode)
													values
														('".$re_row['MemberNo']."', '".$re_row['ProjectCode']."', '".date("Ymd")."', '".date("Ymd",strtotime ("+1 week"))."', 10, 0, '0')
													";
									include "../util/OracleClass.php";

									if($dbinsert =="yes"){
										record_log('document', 'UpdateAction_51_'.$re_row['MemberNo'], $sql2);
										mysql_query($sql2,$db);
										record_log('document', 'UpdateAction_52_'.$re_row['MemberNo'], $sql3);
										$re3=mysql_query($sql3, $db);

										$this->oracle=new OracleClass($smarty);
										$this->oracle->ProcedureExcuteQuery($oradownauth);

									}else{
										echo "자료 신청 결재완료 : ".$sql."<br>";
									}


									if($dbinsert =="yes"){

										$this->oracle->ChangeDBConnection();

										$msg=iconv("utf-8","euc-kr","상신하신 자료 요청서 결재가 완료되었습니다.");

										$orapro="BEGIN usp_sms_send_iu('$MemberMobile', '01080096172', '$msg' , 'SMS'); END;";

										$this->oracle->ProcedureExcuteQuery($orapro);
									}



								}

							}

							break;

							case "HMF-10-1":
								$Detail1_arr=explode("_",$Detail1);

								$Item01='11'; //회사코드
								$Item02=$Detail1_arr[0]; //프로젝트코드
								$Item03=$Detail1_arr[1];   //차수사업코드
								$Item04=$Detail1_arr[2]; //공종
								$Item05="N"; //최종결재 유무
								$Item06="Z"; //결재진행상태
								$Item07=$FormNum;//문서
								$Item08=$Detail1_arr[3]; //PM부서
								$Item09=$MemberNo; //직원사번
								$Item10=$Detail1_arr[4]; //사업자번호

								$procedure01="BEGIN USP_PM_CONT_0801_APPROVAL('$Item01','$Item02','$Item03','$Item04','$Item05','$Item06','$Item07','$Item09');END;";

								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($procedure01);
								}else{
									echo "확정 oracle : ".$procedure01."<br>";
								}

								$procedure02="BEGIN PROC_PM_CONT_MASTER_CREATE('$Item01','$Item02','$Item08','$Item04','$Item09','$Item10');END;";

								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($procedure02);
								}else{
									echo "<br>";
									echo "확정 oracle : ".$procedure02."<br>";
								}

								/*USP_PM_CONT_0801_APPROVAL와 중복되서 주석처리함(김한결)
								$procedure03="BEGIN USP_PM_CONT_08_UP('$Item01','$Item02','$Item08','$Item04','$Item03','$Item10','Y','$Item09');END;";

								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($procedure03);
								}else{
									echo "<br>";
									echo "확정 oracle : ".$procedure03."<br>";
								}
								*/

								$procedure05="BEGIN USP_PM_CONT_INTRA_APPROVAL ('$Item01','$Item02','$Item03','$Item04','$Item06','$Item09');END;";

								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($procedure05);
								}else{
									echo "확정 oracle : ".$procedure05."<br>";
								}

								break;

							case "HMF-10-2":

								$Detail1_arr=explode("_",$Detail1);

								$Item01='11'; //회사코드
								$Item02=$Detail1_arr[0]; //프로젝트코드
								$Item03=$Detail1_arr[1];   //차수사업코드
								$Item04=$Detail1_arr[2]; //공종
								$Item05="N"; //최종결재 유무
								$Item06="Z"; //결재진행상태
								$Item07=$FormNum;//문서
								$Item08=$Detail1_arr[3]; //PM부서
								$Item09=$MemberNo; //직원사번
								$Item10=$Detail1_arr[4]; //사업자번호

								$procedure01="BEGIN USP_PM_CONT_INTRA_APPROVAL ('$Item01','$Item02','$Item03','$Item04','$Item06','$Item09');END;";

								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($procedure01);
								}else{
									echo "확정 oracle : ".$procedure01."<br>";
								}
								/*
								$procedure02="BEGIN USP_PM_CONT_02_UP('11','$Item02','$Item08','$Item04','Y','$Item09');END;";

								if($dbinsert =="yes"){
									$this->oracle->ProcedureExcuteQuery($procedure02);
								}else{
									echo "<br>";
									echo "확정 oracle : ".$procedure02."<br>";
								}
								*/

								break;

					}



					//처음기안자에게 결재완료 메세지 보내기
					/*
					$SendIP = MemberNo2Ip($MemberNo);
					$SendName= DocCode2Name($FormNum);
					if($SendIP <> "")
					{
						$send_string="CMD:ESIGNENDSEND=".$SendName."=".$SendIP;

						$this->smarty->assign('mode',"msg");
						$this->smarty->assign('send_string',$send_string);
						$this->smarty->display("intranet/js_page.tpl");
					}
					*/


				}

				//수신부서 결재처리완료후 처리 끝----------------------------------------------------------------
				if($dbinsert =="yes"){
					if($open_type == "ApprovalList"){
						$this->smarty->assign('target','RemoveRow');
						$this->smarty->display("intranet/move_page.tpl");
					}elseif($open_type == "package"){  //관리자단체 다중결재
					}elseif($targetKind=="0")
					{
						$this->smarty->assign('target',"no");
						$this->smarty->display("intranet/move_page.tpl");
					}else
					{
						$this->smarty->assign('target',"opener");
						$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=$kind");
						$this->smarty->display("intranet/move_page.tpl");
					}
				}
		}



		//============================================================================
		// 전자문서 파일삭제
		//============================================================================
		function delete_file()
		{
			//echo "-document_logic_HANM.php : InsertPage<br>----------------------<br>";
			//include "../inc/approval_function.php";
			include "../inc/approval_function.php";

			global $db;
			global $memberID,$current_id,$FormNum,$dbkey;

			global $CompanyKind;	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
			global $WorkPosition;	//워크포지션(WorkPosition)

			global $End_index,$Receive_index,$Now_Step,$menu_cmd;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5,$doc_status;

			if($FormNum=="HMF-7-1" or $FormNum=="BRF-7-1")
			{
				$azSQL="select * from SanctionDoc_tbl where DocSN='$dbkey'";
				//echo $azSQL."<br>";
					$res_sanction = mysql_query($azSQL,$db);
					$Addfile = mysql_result($res_sanction,0,"Addfile");
					$Addfile_Arr=split("/n",$Addfile);


				$Addfile_Arr2=split("/",$Addfile_Arr[$current_id]);

				$path ="./../../../intranet_file/documents/".$FormNum."/";
				$path_is ="./../../../intranet_file/documents/".$FormNum;
				$del_path = $path.$Addfile_Arr2[2];

				$Resultfile_org = file_exists("$del_path");

				if($Resultfile_org)	{ $re=unlink("$del_path");}

				unset($Addfile_Arr[$current_id]);

				for($i=0; $i<count($Addfile_Arr); $i++)
				{
					if($Addfile_Arr[$i]<>"")
					{
						$re_Addfile= $re_Addfile . str_replace("'","",$Addfile_Arr[$i])."/n";
					}
				}
			}



				$sql = "update SanctionDoc_tbl set Addfile ='$re_Addfile' where DocSN='$dbkey'";
				//echo $sql;
				mysql_query($sql,$db);

				$this->smarty->assign('MoveURL',"document_controller.php?ActionMode=update_page&docno=$FormNum&dbkey=$dbkey&doc_status=EDIT&memberID=$memberID&CompanyKind=HALL&targetKind=self&tab_index=4");
				$this->smarty->display("intranet/move_page.tpl");

		}


		//============================================================================
		// 전자결재 결재자 표시 관련 Logic
		//============================================================================
		function ApprovalName($ItemData)
		{
				include "../inc/approval_var.php";

				global	$db,$memberID;
				global	$FormNum,$doc_CodeORName,$RT_Sanction  ;
				global	$End_index,$Receive_index,$Now_Step;
				global  $RG_Code,$PG_Code;

				$MemberNoName=MemberNo2Name(substr($ItemData,0,6));
				$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6));


				$TmpMember =split("-",$ItemData);
				if ($TmpMember[1] =="임원")
				{
					$StrFinish=strpos($RT_Sanction,"FINISH");
					if ($StrFinish === false) //FINISH 란 말이없으면 현재 기안부서
					{
						$Tmp_GroupCode=$RG_Code;
					}
					else //FINISH 란 말이없으면 현재 처리부서(경영지원부)
					{
						if($i < $Receive_index){
							$Tmp_GroupCode=$RG_Code;
						}
						else
						{
							$Tmp_GroupCode=$PG_Code;
						}
					}

					if ($TmpMember[0] != Group2Manager($Tmp_GroupCode) ) //부서장이 아니라면 대리결재 표시
					{

						$StrResign=strpos($ItemData,"대결");
						if ($StrResign === false) //대결이 없으면 대결-대결 중복방지(처음상신시는 부서장이 달라도 대결이라는 말이 안붙음
						{
							$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6));
							$TmpArr[$i]=$TmpArr[$i];
							$Msg="";
						}

						$StrResign1=strpos($ItemData,"부서장-대결");
						if ($StrResign1 !== false) //대결이 없으면 대결-대결 중복방지(팀장,부서장 결재시 대결이란 말이 있으면
						{
							$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6));
						}

					}
				}


			return $MemberNoName." ".$MemberNoRank.$Msg;
		}


		//============================================================================
		// 전자결재 결재현황 및 결재자 변경 Logic
		//============================================================================
		function ApprovalCheck($ItemData,$i)
		{
				include "../inc/approval_var.php";

				global	$db,$memberID;
				global	$FormNum,$DocSN,$doc_CodeORName,$RT_Sanction  ;
				global	$End_index,$Receive_index,$Now_Step;
				global	$doc_status;


				if($ItemData <> "")
				{
							if($Now_Step > $Receive_index) { //처리부서내 결재중인경우
									if($i >= $Receive_index) {
										if(Process_FinishCheck($DocSN,($i+1)) == "NO") {
											if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
												$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
											}
											else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
											}
										} else {
												$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
										}
									} else {
										if(FinishCheck($DocSN,($i+1)) == "NO") {
											if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
												$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
											}
											else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
											}
										} else {
												$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
										}
									}
							} else {  //결의부서내 결재중인경우
								if($i < $Receive_index) {
									if(FinishCheck($DocSN,($i+1)) == "NO") {
										if ($doc_status == $DOC_STATUS_APPROVE  && $memberID ==  substr($ItemData,0,6))  //결재자가 자신의 결재를 다른사람으로 변경못하게 처리
										{
											$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
										}
										elseif($doc_status == $DOC_STATUS_CREATE  && $memberID ==  substr($ItemData,0,6) && $i==0)  //처음기안자 담당항목이 있는경우
										{
											$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='기안자' title='$i' />";
										}
										else{
											if($FormNum=="BRF-9-2" || $FormNum=="HMF-9-2")
											{
												$msg="<div class=bt_63x23 style=margin-left:36px;><a href=# onClick=cmd_SanctionChange('{$i}');>변　경</a></div>";
											}else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>변　경</a></div>";
											}
										}
									} else {
											$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
									}
								}
							}
				}else
				{

						if($Now_Step > $Receive_index) {
							if(($i+1) > $Now_Step) {
								if($i > $Receive_index) {
									$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
								}
							} else {
								$msg="-"; //Skip
							}
						} else {

							if($i < $Receive_index) {
								if(($i+1) > $Now_Step) {
									if($FormNum=="BRF-9-2" || $FormNum=="HMF-9-2")
									{
										$msg="<div class=bt_63x23 style=margin-left:36px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
									}else
									{
										$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
									}

								} else {
									$msg="-"; //Skip

								}
							}
						}


				}

				return $msg;
		}



		function ApprovalCheck2($ItemData,$i)
		{
				include "../inc/approval_var.php";

				global	$db,$memberID;
				global	$FormNum,$DocSN,$doc_CodeORName,$RT_Sanction  ;
				global	$End_index,$Receive_index,$Now_Step;
				global	$doc_status;

				if($ItemData <> "")
				{
						if($i >= $Receive_index) {
							if(Process_FinishCheck($DocSN,($i+1)) == "NO") {
								if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
									$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
								}
								else
								{
									$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
								}
							} else {
									$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
							}
						} else {
							if(FinishCheck($DocSN,($i+1)) == "NO") {
								if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
									$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
								}
								else
								{
									$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
								}
							} else {
									$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
							}
						}
				}else
				{
						$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
				}

				return $msg;
		}

		function ApprovalCheckFile($ItemData,$i)
		{
			include "../inc/approval_var.php";

			global	$db,$memberID;
			global	$FormNum,$DocSN,$doc_CodeORName,$RT_Sanction  ;
			global	$End_index,$Receive_index,$Now_Step;
			global	$doc_status;

			if($ItemData <> "")
			{
				if($i >= $Receive_index) {
					if(Process_FinishCheck($DocSN,($i+1)) == "NO") {
						if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
							$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
						}
						else
						{
							//$msg="<div class=btn_confirm style=margin-bottom:6px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
							$msg="<button type='button'  style=margin-bottom:6px; onclick=cmd_SanctionChange2('{$i}'); class=btn_pick>변 경</button>";
						}
					} else {
						$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
					}
				} else {
					if(FinishCheck($DocSN,($i+1)) == "NO") {
						if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
							$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
						}
						else
						{
							//$msg="<div class=btn_confirm style=margin-bottom:6px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
							$msg="<button type='button'  style=margin-bottom:6px; onclick=cmd_SanctionChange2('{$i}'); class=btn_pick>변 경</button>";
						}
					} else {
						$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
					}
				}
			}else
			{
				//$msg="<div class=btn_confirm style=margin-bottom:6px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
				$msg="<button type='button' style=margin-bottom:6px; onclick=cmd_SanctionChange('{$i}'); class=btn_pick>지 정</button>";
			}

			return $msg;
		}


		// 휴가계 잔여연차 계산 함수
		//============================================================================
		function NowVacation($MemberNo)
		{
			global	$db;
			$ThisYear=date("Y");
			$StartDay = $ThisYear."-01-01";
			$EndDay = $ThisYear."-12-31";

			$sql ="select a.MemberNo as MemberNo,a.korName as Name,b.Name as GroupName,a.Name as Position,a.EntryDate as EntryDate,c.vacationplus as vacation,a.Pasword as Pasword,a.RankCode as RankCode
			from
			(
				select * from
				(
					select * from member_tbl where MemberNo='$MemberNo'
				)a1 left JOIN
				(
					select * from systemconfig_tbl where SysKey='PositionCode'
				)a2 on a1.RankCode = a2.code

			) a left JOIN
			(
				select * from systemconfig_tbl where SysKey='GroupCode'
			)b on a.GroupCode = b.code ";
			$sql .=" left JOIN ( select * from vacation_set where MemberNo='$MemberNo' and year='$ThisYear') c on a.MemberNo=c.MemberNo";

			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			while($re_row = mysql_fetch_array($re))
			{
					$EntryDate=$re_row[EntryDate];
					$vacation=$re_row[vacation];

			}

			//============================================================================
			// 휴가 전년이월
			//============================================================================

				$sql2 = "select * from diligence_tbl where MemberNo = '$MemberNo' and date like '%$ThisYear%'";

				$re2 = mysql_query($sql2,$db);
				$re_num2 = mysql_num_rows($re2);
				if($re_num2 > 0)
				{
					$rest_day = mysql_result($re2,0,"rest_day");

					if($rest_day > 0)  //전년월차 남아있으면 모두 0으로처리
					{
						$rest_day=0;
					}

					$rest_day = $rest_day + (double)mysql_result($re2,0,"spend_day");
				}
				else
				{
					$rest_day = "&nbsp;";
				}


			//============================================================================
			// 올해 생성년차
			//============================================================================

				$new_day=0;
				$EnterYear = substr($EntryDate,0,4);  //입사년도
				$EnterMonth = substr($EntryDate,5,2);  //입사월
				$EnterDay = substr($EntryDate,8,2);  //입사일


				if($EntryDate <"2017-05-30")
				{
						$JoinYear = $ThisYear - $EnterYear; //현제년-입사년
						if($JoinYear <= 0) //1년미만은 없음
						{
							$new_day = 0;
						}
						elseif($JoinYear == 1) //1년이상은 월별 차등지급
						{

							if($EnterMonth == "01"){$new_day = 15;}
							elseif($EnterMonth == "02"){$new_day = 14;}
							elseif($EnterMonth == "03"){$new_day = 13;}
							elseif($EnterMonth == "04"){$new_day = 11;}
							elseif($EnterMonth == "05"){$new_day = 10;}
							elseif($EnterMonth == "06"){$new_day = 9;}
							elseif($EnterMonth == "07"){$new_day = 7;}
							elseif($EnterMonth == "08"){$new_day = 6;}
							elseif($EnterMonth == "09"){$new_day = 5;}
							elseif($EnterMonth == "10"){$new_day = 3;}
							elseif($EnterMonth == "11"){$new_day = 2;}
							elseif($EnterMonth == "12"){$new_day = 0;}
						}
						else  //그외는 2년에 1일씩 증가
						{
							$remainder=$JoinYear % 2;
							if ($remainder == 0 )
							{
								$division=(int)($JoinYear/2);
								$new_day= $division-1+15;
							}
							else
							{
								$division=(int)($JoinYear/2);
								$new_day= $division+15-1;
							}
						}

						$new_day=$new_day+$vacation;

					//============================================================================
					// MY Vacation 사용휴가
					//============================================================================
						$spend_day=0;
						$sql_use = "select * from userstate_tbl where (state = 1 or state = 18 or state = 30 or state = 31) and MemberNo = '$MemberNo' and start_time like '$ThisYear%' and end_time <>'0000-00-00'";
						$re_use = mysql_query($sql_use,$db);
						$re_num_use = mysql_num_rows($re_use);
						if($re_num_use > 0)
						{
							while($re_row_use = mysql_fetch_array($re_use))
							{
								if($re_row_use[state]=="1")
								{
									if($re_row_use[start_time] >= $StartDay && $re_row_use[end_time] <= $EndDay)
									{
										$spend = calculate($re_row_use[start_time],$re_row_use[end_time],$re_row_use[note]);
									}
									elseif($re_row_use[start_time] < $StartDay)
									{
										if($re_row_use[end_time] > $EndDay)
										{
											$spend = calculate($StartDay,$EndDay,$re_row_use[note]);
										}
										else
										{
											$spend = calculate($StartDay,$re_row_use[end_time],$re_row_use[note]);
										}
									}
									else
									{
										$spend = calculate($re_row_use[start_time],$EndDay,$re_row_use[note]);
									}
									$spend_day = $spend_day + $spend;
								}else if( $re_row_use[state]=="30" or $re_row_use[state]=="31" ){
									$spend_day = $spend_day + 0.5;
								}else{
									$spend_hour+=$re_row_use[sub_code];
								}
							}
						}

						$rest_day=$rest_day*8;
						$new_day=$new_day*8;
						$sum_day=$rest_day+$new_day;
						$use_day=$spend_day*8+$spend_hour;
						$remaind_day=$sum_day-$use_day;

						$now_vacation=hourtodatehour($remaind_day);
				}else{
					$this_year=$ThisYear;
					//입사일
					$enter_start=$this_year."-".$EnterMonth."-".$EnterDay;
					//오늘날짜
					$now_start=$this_year."-".date("m-d");

					if($EntryDate>$now_start)
					{
						$now_vacation="0"; //잔여연차
					}else
					{
						if($enter_start > $now_start)
						{
							$this_year2=$this_year-1;
							$year_start=$this_year2."-".$EnterMonth."-".$EnterDay;

						}else
						{
							$year_start=$enter_start;
						}

						$year_end = date("Y-m-d", strtotime("+1 year", strtotime($year_start)));
						$year_end = date("Y-m-d", strtotime("-1 day", strtotime($year_end)));

						$ThisDay=$this_year."-".date("m-d");
						if($ThisDay < $year_end )
						{
							$ThisDay=$year_end;
						}
						$Today=date("Y-m-d");


						$arryear=getDiffdate($Today, $EntryDate);
						$yeargap=$arryear[yeargap];

						if($yeargap==0)  //1년미만
						{
							$ThisDay=$this_year."-".date("m-d");
						}

						//$tmpData=getAnnualLeaveNew2($ThisDay,$EntryDate,$MemberNo);
						//$now_vacation=$tmpData[9]; //잔여연차

						//$tmpData=getAnnualLeaveNew2_v3($ThisDay,$EntryDate,$MemberNo,$vacationplus,$sel_year);
						$tmpData=getAnnualLeaveNew2_v3($ThisDay,$EntryDate,$MemberNo,$vacationplus,$this_year);

						$createvacation_sum=($tmpData[0]+$tmpData[10])/8;
						$rest_day_e= ($createvacation_sum*8)-($tmpData[3]);
						$f_rest_day_e=hourtodatehour_v3($rest_day_e);

						$now_vacation=$f_rest_day_e; //잔여연차
					}

					$StartDay= $year_start;
					$EndDay= $year_end;

				}


			 return ($now_vacation);
		}

		function Passcheck()
		{
			global	$db,$memberID,$menu_cmd,$CMD_TYPE;

			$user_ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];   /// remote ip 저장
			$sql = "select * from member_tbl where MemberNo = '$memberID'";
			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			$re_row = mysql_fetch_array($re);
			$db_pw = $re_row[Pasword];

			$this->smarty->assign('db_pw',$db_pw);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('menu_cmd',$menu_cmd);
			$this->smarty->assign('CMD_TYPE',$CMD_TYPE);

			$this->smarty->display("intranet/common_contents/work_approval/pass_check_mvc.tpl");
		}

		function ParkingPrint()
		{
			include "../inc/approval_function.php";
			include "../inc/approval_var.php";

			global	$db,$memberID,$menu_cmd,$CMD_TYPE,$dbkey;

			$DocSN = $dbkey;
			$azSQL="select * from SanctionDoc_tbl where DocSN='$DocSN'";
			//echo $azSQL."<br>";
			$res_sanction = mysql_query($azSQL,$db);
			$DocTitle = mysql_result($res_sanction,0,"DocTitle");
			$Detail4 = mysql_result($res_sanction,0,"Detail4");
			$Dt4=split("/n",$Detail4);
			$Detail5 = mysql_result($res_sanction,0,"Detail5");
			$Dt5=split("/n",$Detail5);

		if($Dt4[4]=="회사차량")
			{
			$azSQL1="select * from systemconfig_tbl where SysKey='bizcarno' and Code='$Dt5[3]'";
			//echo $azSQL1."<br>";

			$res_device = mysql_query($azSQL1,$db);
			$CarName = mysql_result($res_device,0,"Name");
			}
			$CheckSign_member =	CheckSign($DocSN,"담당",$SANCTION_CODE2);

			$this->smarty->assign('db_pw',$db_pw);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('menu_cmd',$menu_cmd);
			$this->smarty->assign('CMD_TYPE',$CMD_TYPE);
			$this->smarty->assign('DocTitle',$DocTitle);
			$this->smarty->assign('Detail4',$Detail4);
			$this->smarty->assign('Dt4',$Dt4);
			$this->smarty->assign('Detail5',$Detail5);
			$this->smarty->assign('Dt5',$Dt5);
			$this->smarty->assign('CheckSign_member',$CheckSign_member);

			$this->smarty->display("intranet/common_contents/work_approval/Parking_Print_mvc.tpl");
		}

		function SortKeyCombination($m_MemberNo)
		{
			global	$db;

			$_SortKeyCombination = "000E0";
			$azSQL = "SELECT * FROM member_tbl WHERE MemberNo='$m_MemberNo'";
			//echo $azSQL;
			$res_sortkey = mysql_query($azSQL,$db);
			if(mysql_num_rows($res_sortkey) > 0) {
				$GroupCode = mysql_result($res_sortkey,0,"GroupCode");
				$RankCode = mysql_result($res_sortkey,0,"RankCode");
				$_SortKeyCombination = sprintf("%03d",$GroupCode) . $RankCode;
			}
			return $_SortKeyCombination;
		}



		function fnGetData()
		{
			global $db,$workdate;
			include "../inc/approval_function.php";

			if(holycheck($workdate)=="weekday")
			{
				$msg="<input type='radio' id='Detail5' name='Detail5' value='1' checked onclick='txtview(1);'><span class='ml_0'>연장근무</span> ";
				$msg.="<input type='radio' id='Detail5' name='Detail5' value='2' onclick='txtview(2);'><span class='ml_0'>휴일근무</span>";
			}else
			{
				$msg="<input type='radio' id='Detail5' name='Detail5' value='1' onclick='txtview(1);'><span class='ml_0'>연장근무</span> ";
				$msg.="<input type='radio' id='Detail5' name='Detail5' value='2' checked onclick='txtview(2);'><span class='ml_0'>휴일근무</span> (하루씩 1올려주시기 바랍니다)";
			}

			echo  $msg;

		}




			//============================================================================
			// 전자문서 파일삭제
			//============================================================================
			function DeleteFile()
			{

				include "../inc/approval_function.php";

				global $db;
				global $memberID,$current_id,$FormNum,$dbkey,$doc_status;


				$azSQL="select * from SanctionDoc_tbl where DocSN='$dbkey'";
				//echo $azSQL."<br>";
				$res_sanction = mysql_query($azSQL,$db);
				$Addfile = mysql_result($res_sanction,0,"Addfile");

				$path ="./../../../intranet_file/documents/".$FormNum."/";
				$path_is ="./../../../intranet_file/documents/".$FormNum;

				if($FormNum=="HMF-6-1" or $FormNum=="HMF-6-2")
				{

					$Addfile_Arr=split("/n",$Addfile);
					$Addfile_Arr2=split("/",$Addfile_Arr[$current_id]);
					$del_path = $path.$Addfile_Arr2[2];
					$Resultfile_org = file_exists("$del_path");

					if($Resultfile_org)	{
						$re=unlink("$del_path");
					}

					unset($Addfile_Arr[$current_id]);
					for($i=0; $i<count($Addfile_Arr); $i++)
					{
						if($Addfile_Arr[$i]<>"")
						{
							$re_Addfile= $re_Addfile . str_replace("'","",$Addfile_Arr[$i])."/n";
						}
					}
				}
				else
				{
					$Addfile_Arr2=split("/",$Addfile);
					$del_path = $path.$Addfile_Arr2[2];
					$Resultfile_org = file_exists("$del_path");
					if($Resultfile_org)	{
						$re=unlink("$del_path");
					}
					$re_Addfile="";
				}



				$sql = "update SanctionDoc_tbl set Addfile ='$re_Addfile' where DocSN='$dbkey'";
				//echo $sql;
				mysql_query($sql,$db);

				$this->smarty->assign('target','self');
				$this->smarty->assign('MoveURL',"document_controller.php?ActionMode=update_page&docno=$FormNum&dbkey=$dbkey&doc_status=EDIT&memberID=$memberID&CompanyKind=HANM");
				$this->smarty->display("intranet/move_page.tpl");
			}

			//============================================================================
			// 전자 전표 결재 문서 접수 전달
			//============================================================================
			function doc_toss(){
				include "../inc/approval_function.php";

				extract($_REQUEST);
				global $db, $memberID, $FormNum;

				$dbinsert = "yes";
				//$dbinsert = "no";
				if($fun_type == "list"){
					$azSQL = "
						SELECT
							ReceiveMember
							, (SELECT korName FROM member_tbl WHERE MemberNo LIKE ReceiveMember) AS korName
						FROM approval_tbl
						WHERE FormName LIKE 'HMF-5-%'
						GROUP BY ReceiveMember
						ORDER BY FormName
					";

					echo "[''";
					$re = mysql_query($azSQL,$db);
					while($re_row = mysql_fetch_array($re)) {
						echo ",['".$re_row[ReceiveMember]."', '".$re_row[korName]."']";
					}
					echo "]";
				}else{
					$azSQL = "
						SELECT
							DocSN
						FROM approval_account_tbl
						WHERE DocSN LIKE '$DocSN'
					";
					$re = mysql_query($azSQL,$db);
					if(mysql_num_rows($re) > 0){
						$sql = "UPDATE approval_account_tbl SET MemberNo = '$TargetMemberNo' WHERE DocSN LIKE '$DocSN'";
					}else{
						$sql = "INSERT INTO approval_account_tbl (DocSN, MemberNo, OriginMemberNo) VALUES ('$DocSN', '$TargetMemberNo', '$memberID')";
					}
					mysql_query($sql,$db);

					$this->smarty->assign('target','account_reload');
					$this->smarty->display("intranet/move_page.tpl");
				}
			}



			//============================================================================
			// 발신문서 발행일 변경
			//============================================================================
			function doc_date()
			{
				$dbinsert="yes";
				//$dbinsert="no";

				include "../inc/approval_function.php";
				global $db,$memberID;

				global $DocSN;
				global $FormNum,$tab_index,$currentPage;
				global $PG_Y,$PG_M,$PG_D,$Detail_2;

				for($i=0; $i<=1; $i++) {
					$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";
				}

				$azSQL = "update SanctionDoc_tbl set Detail2='".$Detail2.$PG_Y."-".$PG_M."-".$PG_D."' where DocSN='$DocSN'";

				if($dbinsert =="yes"){
					$result=mysql_query($azSQL,$db);


					$this->smarty->assign('target','reload');
					$this->smarty->assign('MoveURL',"approval_controller.php?Category=MyListView&FormNum=".$FormNum."&tab_index=".$tab_index."&memberID=".$memberID."&currentPage=".$currentPage);
					$this->smarty->display("intranet/move_page.tpl");

					//$this->smarty->assign('target','no');
					//$this->smarty->display("intranet/move_page.tpl");

				}else{
					echo "[save--- ".$azSQL."<br>";


				}
			}

			//============================================================================
			// 발신공문 출력
			//============================================================================
			function doc_print(){
				include "../inc/approval_function.php";
				include "../inc/approval_var.php";

				global	$db,$memberID,$menu_cmd,$CMD_TYPE,$dbkey,$FormNum,$printYN;

				$DocSN = $dbkey;
				$docsn_num = substr(str_replace("-", "",$DocSN),0,9);
				$azSQL="select * from SanctionDoc_tbl where DocSN='$DocSN'";
				//echo $azSQL."<br>";
				$res_sanction = mysql_query($azSQL,$db);

				$DocTitle = mysql_result($res_sanction,0,"DocTitle");
				$Detail1 = mysql_result($res_sanction,0,"Detail1");
				$Detail2 = mysql_result($res_sanction,0,"Detail2");
				$Detail3 = mysql_result($res_sanction,0,"Detail3");
				$Detail4 = mysql_result($res_sanction,0,"Detail4");
				$Detail5 = mysql_result($res_sanction,0,"Detail5");
				$ProjectCode = mysql_result($res_sanction,0,"ProjectCode");
				$PG_Date = mysql_result($res_sanction,0,"PG_Date");
				$RT_Sanction = mysql_result($res_sanction,0,"RT_Sanction");
				$RT_SanctionState = mysql_result($res_sanction,0,"RT_SanctionState");
				$MemberNo = mysql_result($res_sanction,0,"MemberNo");
				$Account = mysql_result($res_sanction,0,"Account");



					$Dt2=split("/n",$Detail2);
					if($Dt2[2] == ""){
						$Dt2[2] = $PG_Date;
					}
					$this->smarty->assign('Dt2',$Dt2);
					$Dt3=split("/n",$Detail3);
					$this->smarty->assign('Dt3',$Dt3);
					$Dt4 = split("/n",$Detail4);
					$this->smarty->assign('Dt4',$Dt4);
					$Coop = split("/",$ProjectCode);
					$RT_Sanction_member_origin = split(":",$RT_Sanction);
					for($i=0; ; $i++){
						if( $RT_Sanction_member_origin[$i] == "RECEIVE"){
							break;
						}
						$temp_member = split("-",$RT_Sanction_member_origin[$i]);
						//$RT_Sanction_member[$i] = $temp_member[1]." ".MemberNo2Name($temp_member[0]);
						$duty_name = MemberNo2Rank($temp_member[0]);
						//echo $temp_member[0];
						if( $docsn_num > '202131286' or substr($DocSN, 0, 4) == "TEMP" ){
						//if($memberID=="M20330"){
							if( $temp_member[1] == '팀장' ){
								$RT_Sanction_member[$i] = $temp_member[1]."&nbsp;&nbsp;".MemberNo2Name($temp_member[0]);
							}elseif( $temp_member[1] == '관리자' ){
								$RT_Sanction_member[$i] = "부서장&nbsp;&nbsp;".MemberNo2Name($temp_member[0]);
							}
						}else{
							if($Coop[$i] == '0' and ($duty_name == "본부장" or $duty_name == "부서장" or $duty_name == "원장" or $duty_name == "실장" or $duty_name == "회장" or $duty_name == "대표이사"or $duty_name == "팀장"or $duty_name == "사장")){
								$RT_Sanction_member[$i] = $duty_name." : ".MemberNo2Name($temp_member[0]);
							}
						}
					}

					if(strpos($RT_SanctionState, "FINISH") !== false and $Detail5 == "1"){
						$printYN = 'N';
					}else{
						$printYN = 'Y';
					}

				$this->smarty->assign('db_pw',$db_pw);
				$this->smarty->assign('memberID',$MemberNo);
				if($Account=="")
				{
					$this->smarty->assign('MemberName',MemberNo2Name($MemberNo));
				}else
				{
					$this->smarty->assign('MemberName',$Account);
				}
				$this->smarty->assign('menu_cmd',$menu_cmd);
				$this->smarty->assign('CMD_TYPE',$CMD_TYPE);
				$this->smarty->assign('printYN',$printYN);

				$this->smarty->assign('Sanction_data',$Sanction_data);
				$this->smarty->assign('GroupName',$GroupName);
				$this->smarty->assign('DocSN',$DocSN);
				$this->smarty->assign('DocTitle',$DocTitle);
				$this->smarty->assign('Detail1',$Detail1);
				$this->smarty->assign('Detail2',$Detail2);
				$this->smarty->assign('Detail3',$Detail3);
				$this->smarty->assign('Detail4',$Detail4);
				$this->smarty->assign('Detail5',$Detail5);
				$this->smarty->assign('PG_Date',str_replace("-", " . ", $PG_Date));
				$this->smarty->assign('RT_Sanction_member',$RT_Sanction_member);

				//if($memberID=="M20329" || $memberID=="M20330" || $memberID=="B20320"  || $memberID=="M20331" || $memberID=="J08305"){

				if( $docsn_num < '202100000' ){	//발신공문 2021-00000 이전
					if($FormNum=="BRF-6-1"){  //발신공문
						$this->smarty->display("intranet/common_contents/work_approval/BRF-6-1_print.tpl");
					}else{
						$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print_2020.tpl");
					}
				}elseif( $docsn_num < '202131287' ){	//발신공문 202131287 이전
					if($FormNum=="BRF-6-1"){  //발신공문
						$this->smarty->display("intranet/common_contents/work_approval/BRF-6-1_print.tpl");
					}else{
						$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print_2021.tpl");
					}
				}else{
					if($FormNum=="BRF-6-1"){  //발신공문
						$this->smarty->display("intranet/common_contents/work_approval/BRF-6-1_print.tpl");
					}else{
						$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print.tpl");
					}
				}

				/*if(substr(str_replace("-", "",$DocSN),0,9) < '202100000'){
					if($FormNum=="BRF-6-1")  //발신공문 2021-00000 이전
					{
						$this->smarty->display("intranet/common_contents/work_approval/BRF-6-1_print.tpl");
					}else {
						$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print_2020.tpl");
					}

				}else if(substr(str_replace("-", "",$DocSN),0,9) < '202126800'){
					if($FormNum=="BRF-6-1")  //발신공문 202126800 이전
					{
						$this->smarty->display("intranet/common_contents/work_approval/BRF-6-1_print.tpl");
					}else {
						$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print.tpl");
					}
				}else{
					if($FormNum=="BRF-6-1")  //발신공문 20210908 이후
					{
						$this->smarty->display("intranet/common_contents/work_approval/BRF-6-1_print.tpl");
					}else {
						$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print_new.tpl");
					}
				}*/
			}



			//============================================================================
			// 발신공문 출력
			//============================================================================
			function doc_print_test(){
				include "../inc/approval_function.php";
				include "../inc/approval_var.php";

				global	$db,$memberID,$menu_cmd,$CMD_TYPE,$dbkey,$FormNum,$printYN;

				$DocSN = $dbkey;
				$azSQL="select * from SanctionDoc_tbl where DocSN='$DocSN'";
				//echo $azSQL."<br>";
				$res_sanction = mysql_query($azSQL,$db);

				$DocTitle = mysql_result($res_sanction,0,"DocTitle");
				$Detail1 = mysql_result($res_sanction,0,"Detail1");
				$Detail2 = mysql_result($res_sanction,0,"Detail2");
				$Detail3 = mysql_result($res_sanction,0,"Detail3");
				$Detail4 = mysql_result($res_sanction,0,"Detail4");
				$Detail5 = mysql_result($res_sanction,0,"Detail5");
				$ProjectCode = mysql_result($res_sanction,0,"ProjectCode");
				$PG_Date = mysql_result($res_sanction,0,"PG_Date");
				$RT_Sanction = mysql_result($res_sanction,0,"RT_Sanction");
				$RT_SanctionState = mysql_result($res_sanction,0,"RT_SanctionState");
				$MemberNo = mysql_result($res_sanction,0,"MemberNo");
				$Account = mysql_result($res_sanction,0,"Account");



					$Dt2=split("/n",$Detail2);
					if($Dt2[2] == ""){
						$Dt2[2] = $PG_Date;
					}
					$this->smarty->assign('Dt2',$Dt2);
					$Dt3=split("/n",$Detail3);
					$this->smarty->assign('Dt3',$Dt3);
					$Dt4 = split("/n",$Detail4);
					$this->smarty->assign('Dt4',$Dt4);
					$Coop = split("/",$ProjectCode);
					$RT_Sanction_member_origin = split(":",$RT_Sanction);
					for($i=0; ; $i++){
						if( $RT_Sanction_member_origin[$i] == "RECEIVE"){
							break;
						}
						$temp_member = split("-",$RT_Sanction_member_origin[$i]);
						//$RT_Sanction_member[$i] = $temp_member[1]." ".MemberNo2Name($temp_member[0]);
						$duty_name = MemberNo2Rank($temp_member[0]);
						if($Coop[$i] == '0' and ($duty_name == "본부장" or $duty_name == "부서장" or $duty_name == "원장" or $duty_name == "실장" or $duty_name == "회장" or $duty_name == "대표이사")){
							$RT_Sanction_member[$i] = $duty_name." : ".MemberNo2Name($temp_member[0]);
						}
					}
					if(strpos($RT_SanctionState, "FINISH") !== false and $Detail5 == "1"){
						$printYN = 'N';
					}else{
						$printYN = 'Y';
					}


				$this->smarty->assign('db_pw',$db_pw);
				$this->smarty->assign('memberID',$MemberNo);
				if($Account=="")
				{
					$this->smarty->assign('MemberName',MemberNo2Name($MemberNo));
				}else
				{
					$this->smarty->assign('MemberName',$Account);
				}
				$this->smarty->assign('menu_cmd',$menu_cmd);
				$this->smarty->assign('CMD_TYPE',$CMD_TYPE);
				$this->smarty->assign('printYN',$printYN);

				$this->smarty->assign('Sanction_data',$Sanction_data);
				$this->smarty->assign('GroupName',$GroupName);
				$this->smarty->assign('DocSN',$DocSN);
				$this->smarty->assign('DocTitle',$DocTitle);
				$this->smarty->assign('Detail1',$Detail1);
				$this->smarty->assign('Detail2',$Detail2);
				$this->smarty->assign('Detail3',$Detail3);
				$this->smarty->assign('Detail4',$Detail4);
				$this->smarty->assign('Detail5',$Detail5);
				$this->smarty->assign('PG_Date',str_replace("-", " . ", $PG_Date));
				$this->smarty->assign('RT_Sanction_member',$RT_Sanction_member);


				$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print_test.tpl");

			}

	//============================================================================
	// 근태사유서 - 결재중인 연차사용일 변경이 존재하는지 체크.
	//============================================================================
	function CheckEditVacation(){
		include "../inc/approval_function2.php";
		global $db,$memberID;
		extract($_REQUEST);

		$azSQL = " select count(DocSN) as cnt from sanctiondoc_tbl where FormNum in ( 'BRF-4-9', 'HMF-4-9', 'BRF-4-10', 'HMF-4-10' ) and MemberNo = '$MemberNo' and DocSN not like 'TEMP-%' and RT_SanctionState not like '%FINISH%' ";
		//echo $azSQL."<br>";

		$re = mysql_query($azSQL,$db);
		while($re_row = mysql_fetch_array($re)) {
			echo $re_row[cnt];
		}
	}

}
//END============================================================================
?>