<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

	/***************************************
	* 전자결재문서 작성
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ KYH
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";


	extract($_GET);
	class DocumentLogic {
		var $smarty;
		function DocumentLogic($smarty)
		{
			$this->smarty=$smarty;
		}


		//============================================================================
		// 전자결재 문서보기
		//============================================================================
		function InsertPage()
		{
			include "../inc/approval_function.php";

			global $db,$memberID;
			global $FormNum,$End_index,$Receive_index,$Now_Step,$menu_cmd;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5;



			$DocSN=TempSerialNo2($memberID);
			$RG_Date = date('Y-m-d'); //기안일

			$sql =      " select a.korName as Name,a.GroupCode,b.Name as GroupName,a.Name as Position		 ";
			$sql = $sql." from                                                                   ";
			$sql = $sql."	(                                                                    ";
			$sql = $sql."		select * from                                                    ";
			$sql = $sql."		(                                                                ";
			$sql = $sql."			select * from member_tbl where MemberNo = '$memberID'        ";
			$sql = $sql."		)a1 left JOIN                                                    ";
			$sql = $sql."		(                                                                ";
			$sql = $sql."			select * from systemconfig_tbl where SysKey='PositionCode'   ";
			$sql = $sql."		)a2 on a1.RankCode = a2.code                                     ";
			$sql = $sql."	                                                                     ";
			$sql = $sql."	) a left JOIN                                                        ";
			$sql = $sql."	(                                                                    ";
			$sql = $sql."		select * from systemconfig_tbl where SysKey='GroupCode'          ";
			$sql = $sql."	)b on a.GroupCode = b.code											 ";


			$result=mysql_query($sql,$db);
			$row=mysql_fetch_array($result);
			$Name=$row[Name];
			$RG_Code=$row[GroupCode];
			$RG_Code = sprintf("%02d", $RG_Code);

			$GroupName=$row[GroupName];
			$MemberInfo=$row[Position];

				if($FormNum=="PTF-4-3")  //휴가계
				{	$now_vacation=  $this->NowVacation($memberID);
					if($Detail1 == "") { $Detail1=date('Y-m-d'); } //휴가시작일
					if($Detail2 == "") { $Detail2=date('Y-m-d'); } //휴가종료일
				}
				else if($FormNum=="PTF-4-2")//근태사유서
				{
					if($Detail1 == "") { $Detail1=date('Y-m-d'); } //휴가시작일
					if($Detail5 == "") { $Detail5=date('Y-m-d'); } //휴가종료일
				}
				else if($FormNum=="PTF-6-1")	//연장근무확인서
				{
					if($Detail1 == "") { $Detail1=date("Y-m-d", mktime(0,0,0,date("m")  , date("d")-1, date("Y"))); } //연장근무일어제날짜로 나오게
					if($Detail2 == "") { $Detail2="19:00"; } //연장근무 시작시간
					if($Detail3 == "") { $Detail3="21:00"; } //연장근무 종료시간
				}
				else if($FormNum=="PTF-2-3")	//출장,배차신청서
				{
					$Dt4[0] = date('Y-m-');
					$Dt4[1]= date('Y-m-');

					$Dt4[4]="09";
					$Dt4[5]="18";

					$SelectCar_data = array();
					//회사차량 선택-------------------------------------------------------
					$res_device ="select * from systemconfig_tbl where SysKey='bizcarno' order by code";
						//echo $res_device."<br>";
						$re_device = mysql_query($res_device,$db);
						while($rec_device = mysql_fetch_array($re_device))
						{
							array_push($SelectCar_data,$rec_device);
						}

					$this->smarty->assign('Dt4',$Dt4);
					$this->smarty->assign('SelectCar_data',$SelectCar_data);
				}
				else if($FormNum=="PTF-9-2-s" || $FormNum=="PTF-4-5-s")	//연장근무신청서[개인],휴일근로신청서[개인]
				{
					if($Detail1 == "") { $Detail1=date("Y-m-d"); }

				}
				else if($FormNum=="PTF-9-2")	//연장근무신청서[팀장],휴일근로신청서[팀장]
				{

					if($Detail1 == "") { $Detail1=date("Y-m-d"); }

					$query_data = array();
					$sql = "select * from SanctionDoc_tbl where FormNum='PTF-9-2-s' and RG_Code='$RG_Code' and Detail1='$Detail1' and Detail3='$memberID' and RT_SanctionState like '%".$SANCTION_CODE."%'";
					//echo $sql."<br>";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {
						$re_row[MemberName] = MemberNo2Name($re_row[MemberNo])." ".$re_row[MemberInfo];
						$re_row[ProjectName] = ProjectCode2Name($re_row[ProjectCode])." [".$re_row[ProjectCode]."]";
						array_push($query_data,$re_row);
					}
					$this->smarty->assign('query_data',$query_data);

				}
				else if($FormNum=="PTF-4-5")	//휴일근무신청서[팀장]
				{

					if($Detail1 == "") { $Detail1=date("Y-m-d"); }

					$query_data = array();
					$sql = "select * from SanctionDoc_tbl where FormNum='PTF-4-5-s' and RG_Code='$RG_Code' and Detail1='$Detail1' and Detail3='$memberID' and RT_SanctionState like '%".$SANCTION_CODE."%'";
					//echo $sql."<br>";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) {
						$re_row[MemberName] = MemberNo2Name($re_row[MemberNo])." ".$re_row[MemberInfo];
						$re_row[ProjectName] = ProjectCode2Name($re_row[ProjectCode])." [".$re_row[ProjectCode]."]";
						array_push($query_data,$re_row);
					}
					$this->smarty->assign('query_data',$query_data);

				}



				//결재선관련-------------------------------------------------------

				$Receive_index = -1;
				$End_index = 0;


				$sql_doc="select * from systemconfig_tbl where SysKey='bizform' and Code='$FormNum' and Note <> 'hidden' order by code";
				$re_doc = mysql_query($sql_doc,$db);
				$doc_name = mysql_result($re_doc,0,"Name");
				$doc_description = mysql_result($re_doc,0,"Description");

				//**수신부서,보전연한,1차결재자,2차결재자,1차결재자 action,2차결재자 action(02;1;관리자:임원:RECEIVE:FINISH)
				$doc_CodeORName = mysql_result($re_doc,0,"CodeORName");


				//**결재선정보************************************
				$DB_Sanction = split(";",$doc_CodeORName);


				//**결재자정보************************************ (//$RT_Sanction ="J14101-관리자:J09102-임원:RECEIVE";)

				$RT_Sanction = SanctionArange_Step1($memberID, $DB_Sanction[2]);
				$TmpArr = split(":",$RT_Sanction);
				$TmpArrCount=count($TmpArr);

				$Sanction_data = array();
				if($PG_Code == "") { $PG_Code = $DB_Sanction[0]; } //수신부서: 부서코드가 없는경우 DB의 수신부서로 처리
				if($ConservationYear == "") { $ConservationYear = $DB_Sanction[1]; } //보존년한 업으면 1년


				$sql="select * from systemconfig_tbl where SysKey = 'GroupCode' and Code='$PG_Code'";
				$re = mysql_query($sql,$db);
				$PGName = mysql_result($re, 0, "Name");


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


				if($Receive_index < 0) { $Receive_index = $End_index+1; }
				$Now_Step = Now_Step($DocSN);
				if(!$Now_Step) { $Now_Step = 0;}



				//**결재인 표시************************************
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

						$m_Status=$this->ApprovalCheck($TmpArr[$i],$i);
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
					array_push($Sanction_data,$ItemData);
				}


				$this->smarty->assign('PGName',$PGName);
				$this->smarty->assign('PG_Code',$PG_Code);
				$this->smarty->assign('ConservationYear',$ConservationYear);
				$this->smarty->assign('TmpArrCount',$TmpArrCount);
				$this->smarty->assign('Sanction_data',$Sanction_data);

				$this->smarty->assign('backgroundcolor','#f5f5f6;');
				$this->smarty->assign('readonly','');
				$this->smarty->assign('Edit',true);

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
				$this->smarty->display("intranet/common_contents/work_approval/document_input_mvc.tpl");

		}


		//============================================================================
		// 전자결재 문서작성
		//============================================================================
		function InsertAction()
		{

			include "../inc/approval_function.php";
			global $db,$memberID;

			global $FormNum,$TmpArrCount;

			global $NewSN,$FormNum,$ProjectCode,$DocTitle,$AttchFile,$MemberNo,$Project_Name,$Project_Code,$GroupName;
			global $RT_Sanction_,$RT_SanctionState, $MemberInfo;
			global $RG_Code,$Security,$ConservationYear,$Account;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5,$Detail_5tmp;

			global $Position,$ConservationYear,$RG_Date,$RG_Code,$PG_Code,$PG_Date;
			global $mLabel,$mName,$mCode,$menu_cmd,$DocSN;
			global $Detail_1,$Detail_2,$Detail_3,$Detail_4,$Detail_5;

			global $userfile,$userfile_name,$userfile_size,$filename,$Addfile;

			$dbinsert="yes";

			//********양식별 추가내용 저장(전처리) --------------------------------------------------------------------
				switch ($FormNum) {

					//휴가계
						case "PTF-4-3":

							$Detail4="";
							for($i=0; $i<=7; $i++) {
								$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
							}
						break;

					//근태사유서
						case "PTF-4-2":
							$Detail2="";
							$Detail3="";
							$Detail4="";
							for($i=0; $i<=7; $i++) {
								$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";
								$Detail3= $Detail3 . str_replace("'","",$Detail_3[$i])."/n";
								$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
							}
							break;

					//연장근무확인서
						case "PTF-6-1":
							$Detail4="";
							$Detail5="";
							for($i=0; $i<=3; $i++) {
								$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
								$Detail5= $Detail5 . str_replace("'","",$Detail_5[$i])."/n";
							}
							break;

					//출장차신청서
						case "PTF-2-3":


							$Detail2="";
							$Detail4="";
							$Detail5="";

							for($i=0; $i<=4; $i++) {
								$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";
							}

							for($i=0; $i<=5; $i++) {
								$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
							}

							for($i=0; $i<=17; $i++)
							{
								$Detail5= $Detail5 . str_replace("'","",$Detail_5[$i])."/n";
							}



							break;

					//연장근무신청서(개인),휴일근로신청서(개인)
					case "PTF-9-2-s": case "PTF-4-5-s":

							$TmpState = split("-",$mCode[0]);
							$Detail3=$TmpState[0];
							$DocTitle=$Detail1;
							$Detail4=MemberNo2Name($memberID);
							break;

					//연장근무신청서(팀장),휴일근로신청서(팀장)
					case "PTF-9-2": case "PTF-4-5":

							$DocTitle=$GroupName."(".$Detail1.")";

							if($Project_Name <> "")
							{
								$Detail_3[0]=$Project_Name." [".$Project_Code."]";
								$ProjectCode=$Project_Code;
							}

							for($i=0; $i<=25; $i++) {
								if($Detail_2[$i] <> "" && $Detail_3[$i] <> "" ){
									$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";
									$Detail3= $Detail3 . str_replace("'","",$Detail_3[$i])."/n";
									$Detail4= $Detail4 . str_replace("'","",$Detail_4[$i])."/n";
									$Detail5= $Detail5 . str_replace("'","",$Detail_5[$i])."/n";
								}
							}



						//팀원들이 올린 결재를 모두 처리해준것으로 업데이트
							$RT_value="1:FINISH::".date("Y-m-d");
							if($FormNum =="PTF-9-2")
							{
								$Tmp_FormNum="PTF-9-2-s";
							}
							elseif($FormNum =="PTF-4-5")
							{
								$Tmp_FormNum="PTF-4-5-s";
							}

							$sql = "update SanctionDoc_tbl set RT_SanctionState ='$RT_value' where FormNum='$Tmp_FormNum' and RG_Code='$RG_Code' and Detail1='$Detail1' and Detail3='$memberID'";
							if($dbinsert =="yes"){
								$result=mysql_query($sql,$db);
							}else
							{
								echo "[sub--- ".$sql."<br>";
							}
							break;




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


				if ($userfile)
				{ //첨부파일 있으면서 수정이면
						if (is_dir ($path_is))
						{}
						else
						{ mkdir($path_is, 0777);	}

						$prefile=time();
						if($userfile_name <>"" && $userfile_size <>0)
						{
							$userfile=stripslashes($userfile);
							$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
							$vupload = $path.$prefile.$_FILES['userfile']['name'];
							$vupload = str_replace(" ","",$vupload);
							$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
							move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
							$userfile_size = number_format($userfile_size);
							$filename="./".$FormNum."/".$prefile.$userfile_name;
							$filename = str_replace(" ","",$filename);
						}
				}


			// ******Case별 내용저장 -------------------------------------------------------------------------

			switch ($menu_cmd) {
				case $PROCESS_TEMPORARY: //신규파일 임시저장

					$tmpDocSN=TempSerialNo2($memberID);
					$RT_SanctionState=TempState($memberID); //현재 결재자

					$azSQL = "insert into SanctionDoc_tbl (DocSN, FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, RT_SanctionState, PG_Code, Security, ConservationYear, Account, FinishMemberNo, Detail1, Detail2, Detail3, Detail4, Detail5,MemberInfo) values('$tmpDocSN', '$FormNum', '$ProjectCode', '$DocTitle', '$filename', '$AttchFile', '$memberID', '$RG_Date', '$RG_Code',  '$RT_Sanction_', '$RT_SanctionState', '$PG_Code', '$Security', '$ConservationYear', '$Account', '', '$Detail1', '$Detail2', '$Detail3', '$Detail4', '$Detail5','$MemberInfo')";


					if($dbinsert =="yes")
						$result=mysql_query($azSQL,$db);
					else
						echo "[1--- ".$azSQL."<br>";


					break;

				case $DOC_STATUS_EDIT:  //임시저장 파일 편집후 다시 저장시

					$azSQL = "update SanctionDoc_tbl set ProjectCode='$ProjectCode', DocTitle='$DocTitle', Addfile='$filename', AttchFile='$AttchFile',  RG_Date='$RG_Date', RT_Sanction='$RT_Sanction_', RT_SanctionState='$RT_SanctionState', PG_Code='$PG_Code', Security='$Security', ConservationYear='$ConservationYear', Account='$Account', FinishMemberNo='', Detail1='$Detail1', Detail2='$Detail2', Detail3='$Detail3', Detail4='$Detail4', Detail5='$Detail5' ,MemberInfo='$MemberInfo' where DocSN='$DocSN'";


					if($dbinsert =="yes")
						$result=mysql_query($azSQL,$db);
					else
						echo "[2--- ".$azSQL."<br>";


					break;

				case $PROCESS_APPROVE: //결재상신

					$NewSN=NewSerialNo2($memberID);
					$RT_SanctionState = NextSanctionState($RT_Sanction_,"");

					$rescount = mysql_query("select * from SanctionDoc_tbl where DocSN='$DocSN'",$db);
					$rescountval = mysql_num_rows($rescount);

					if($rescountval > 0) {
						if(substr($DocSN,0,5) == $PROCESS_TEMPORARY) { //임시저장인경우 신규코드부여후 결재상신
							$azSQL = "update SanctionDoc_tbl set DocSN='$NewSN', ProjectCode='$ProjectCode', DocTitle='$DocTitle', Addfile='$filename', AttchFile='$AttchFile' ,  RG_Date='$RG_Date', RT_Sanction='$RT_Sanction_', RT_SanctionState='$RT_SanctionState', PG_Code='$PG_Code', Security='$Security', ConservationYear='$ConservationYear', Account='$Account', FinishMemberNo='', Detail1='$Detail1', Detail2='$Detail2', Detail3='$Detail3', Detail4='$Detail4', Detail5='$Detail5' ,MemberInfo='$MemberInfo'  where DocSN='$DocSN'";
						} else {                                       //재기안 인경우 기존 문서번호 사용 결재상신
							$azSQL = "update SanctionDoc_tbl set DocSN='$NewSN', ProjectCode='$ProjectCode', DocTitle='$DocTitle', Addfile='$filename', AttchFile='$AttchFile',  RG_Date='$RG_Date', RT_Sanction='$RT_Sanction_', RT_SanctionState='$RT_SanctionState', PG_Code='$PG_Code', Security='$Security', ConservationYear='$ConservationYear', Account='$Account', FinishMemberNo='', Detail1='$Detail1', Detail2='$Detail2', Detail3='$Detail3', Detail4='$Detail4', Detail5='$Detail5' ,MemberInfo='$MemberInfo'  where DocSN='$DocSN'";
						}
					} else {                                           //신규파일 작성후 바로 결재상신

						$azSQL = "insert into SanctionDoc_tbl (DocSN, FormNum, ProjectCode, DocTitle, Addfile, AttchFile, MemberNo, RG_Date, RG_Code,  RT_Sanction, RT_SanctionState, PG_Code, PG_Date, Security, ConservationYear, Account, FinishMemberNo, Detail1, Detail2, Detail3, Detail4, Detail5, MemberInfo) values('$NewSN', '$FormNum', '$ProjectCode', '$DocTitle', '$filename', '$AttchFile', '$memberID', '$RG_Date', '$RG_Code',  '$RT_Sanction_', '$RT_SanctionState', '$PG_Code', '', '$Security', '$ConservationYear', '$Account', '', '$Detail1', '$Detail2', '$Detail3', '$Detail4', '$Detail5', '$MemberInfo')";
					}



					if($dbinsert =="yes")
						$result=mysql_query($azSQL,$db);
					else
						echo "[3--- ".$azSQL."<br>";

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


							if($dbinsert =="yes")
								$result=mysql_query($azSQL,$db);
							else
								echo "[4--- ".$azSQL."<br>";


							$tmpFinishMemberNo = $MemberNum.",".$SanctionDate.":";
							$NEW_SanctionState=NextSanctionState($RT_Sanction_,$RT_SanctionState);

							//가결 정보 기록
							$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo' where DocSN='$NewSN'";

							if($dbinsert =="yes")
								$result=mysql_query($azSQL,$db);
							else
								echo "[5--- ".$azSQL."<br>";


					}
					else
					{
						$NEW_SanctionState=$RT_SanctionState;
					}

					$SendName=MemberNo2Name($memberID);
					//****************************************************************************************
					if(strpos($NEW_SanctionState,$PROCESS_RECEIVE) !== false) {  //수신부서 넘어갈때 알려줄 주담당자 정만입력

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
						$SendIP=MemberNo2BossIP($NEW_SanctionState,'2');  //NEW_SanctionState 로 변경해서 처리할것
					}

					if($SendIP <> "")
					{
						$send_string="CMD:ESIGNSEND=".$SendName."=".$SendIP;

						$this->smarty->assign('mode',"msg");
						$this->smarty->assign('send_string',$send_string);
						$this->smarty->display("intranet/js_page.tpl");
					}

				break;
			}


			// 양식별 추가내용 저장(후처리) --------------------------------------------------------------------
			switch ($FormNum) {


			//출장신청서
			case "PTF-2-3":

				$Dt1=split("/n",$Detail1);
				$Dt2=split("/n",$Detail2);
				$Dt3=split("/n",$Detail3);
				$Dt4=split("/n",$Detail4);
				$Dt5=split("/n",$Detail5);


				break;



			}

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=4");
			$this->smarty->display("intranet/move_page.tpl");
		}

		//============================================================================
		// 전자결재 수정
		//============================================================================
		function UpdateReadPage()
		{
			include "../inc/approval_function.php";

			global $db,$memberID;
			global $FormNum,$End_index,$Receive_index,$Now_Step,$DocSN;
			global $RG_Code,$PG_Code,$menu_cmd;
			global $dbkey,$doc_status,$DocSN;
			global $Comment;




			//결재서류 내용표시-----------
			$DocSN = $dbkey;
			$sql="select * from SanctionDoc_tbl where DocSN='$DocSN'";
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
			$ConservationYear = mysql_result($re,0,"ConservationYear"); //보존년한

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

			$RegDate = array();
			$R_Date = FindSandDate($DocSN); //접수일
			if($R_Date == "0000-00-00") {
				$ItemData=array("Year" =>'&nbsp;',"Month"=>'&nbsp;',"Day"=>'&nbsp;');
			} else {
				$ItemData=array("Year" =>substr($R_Date,2,2),"Month"=>substr($R_Date,5,2),"Day"=>substr($R_Date,8,2));
			}
			array_push($RegDate,$ItemData);



			//결재선관련-------------------------------------------------------

			$Receive_index = -1;
			$End_index = 0;

			//$FormNum="JHF-2-3";

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

			$Sanction_data = array();


			if($PG_Code == "") { $PG_Code = $DB_Sanction[0]; } //수신부서: 부서코드가 없는경우 DB의 수신부서로 처리

			$sql="select * from systemconfig_tbl where SysKey = 'GroupCode' and Code='$PG_Code'";
			$re = mysql_query($sql,$db);
			$PGName = mysql_result($re, 0, "Name");

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
					$ItemData=array("Label" =>'',"mLabel"=>$PROCESS_FINISH,"mName"=>'',"mCode"=>'',"mStatus"=>'');
				}
				else if($Sanction_Label[$i] == $PROCESS_RECEIVE) {
					$ItemData=array("Label" =>'수신부서',"mLabel"=>$PROCESS_RECEIVE,"mName"=>$PGName,"mCode"=>$PG_Code,"mStatus"=>'');
					$Receive=true;
				} else {

					if($Receive)  //경영지원
						$m_SignStatus=FindSanctionState2($DocSN,$Sanction_Label[$i],$SANCTION_CODE2);
					else  //부서
						$m_SignStatus=FindSanctionState2($DocSN,$Sanction_Label[$i],$SANCTION_CODE);


					$m_Status=$this->ApprovalCheck($TmpArr[$i],$i);
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

					/*---- 중간결재권자 자신의 결재 임원보여줌 ------------*/
						if($Now_Step <= $i && !$Receive )
						{
							$sql="select * from sanctionmember_tbl where MemberNo='$memberID' and SanctionStep='1'";
							$re = mysql_query($sql,$db);
							if(mysql_num_rows($re) > 0) {
									$SanctionMember = mysql_result($re,0,"SanctionMember");
							}

							if($SanctionMember <> ""){
								$m_Status=$this->ApprovalCheck($SanctionMember,$i);
								$m_tmpName=$this->ApprovalName($SanctionMember);
								$m_Name_arr = split("-",$m_tmpName);
								$m_Name=$m_Name_arr[0];
								if($m_Name_arr[1]=="대결")
								{
									$m_Code=$TmpArr[$i]."-".$m_Name_arr[1];
								}else
								{
									$m_Code=$TmpArr[$i];
								}
							}
						}
					/*---------------------------------------------------*/

					$ItemData=array("Label" =>$Sanction_Label[$i],"mLabel"=>$Sanction_Label[$i],"mName"=>$m_Name,"mCode"=>$m_Code,"mStatus"=>$m_Status);
				}

				array_push($Sanction_data,$ItemData);

			}

			$this->smarty->assign('Name',$Name);
			$this->smarty->assign('PGName',$PGName);
			$this->smarty->assign('PG_Code',$PG_Code);
			$this->smarty->assign('TmpArrCount',$TmpArrCount);
			$this->smarty->assign('Sanction_data',$Sanction_data);

			//결재선관련-끝------------------------------------------------------

			//문서별 처리
			if($FormNum=="PTF-4-2")  //근태사유서
			{
				if($Detail1 == "") { $Detail1=date('Y-m-d'); } //일시(시행일)
				if($Detail5 == "") { $Detail5=date('Y-m-d'); } //일시(시행일)

				$Dt2=split("/n",$Detail2);
				$Dt3=split("/n",$Detail3);
				$Dt4=split("/n",$Detail4);


				$DetailData = array();
				for($i=0; $i<count($Dt2)-2; $i++) {

						if($Dt2[$i] <> "")
						{
							$mRank = MemberNo2Rank($Dt2[$i]);
							$mName = MemberNo2Name($Dt2[$i]);
							$ItemData2=array("ID"=>$Dt2[$i],"Rank" =>$mRank,"Name"=>$mName,"Content"=>$Dt3[$i],"Note"=>$Dt4[$i]);

						}else{
							$ItemData2=array("ID"=>'',"Rank" =>'',"Name"=>'',"Content"=>'',"Note"=>'');
						}
						array_push($DetailData,$ItemData2);

				}

				$this->smarty->assign('Dt2',$Dt2);
				$this->smarty->assign('Dt3',$Dt3);
				$this->smarty->assign('Dt4',$Dt4);
				$this->smarty->assign('DetailData',$DetailData);

				$Signer_1=FindSanctionState2($DocSN,"담당",$SANCTION_CODE);
				$Signer_2=FindSanctionState2($DocSN,"관리자",$SANCTION_CODE);
				$Signer_3=FindSanctionState2($DocSN,"임원",$SANCTION_CODE);

				$this->smarty->assign('Signer_1',$Signer_1);
				$this->smarty->assign('Signer_2',$Signer_2);
				$this->smarty->assign('Signer_3',$Signer_3);

			}


			if($FormNum=="PTF-4-3")  //휴가계
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

				$Signer_1=FindSanctionState2($DocSN,"담당",$SANCTION_CODE);
				$Signer_2=FindSanctionState2($DocSN,"관리자",$SANCTION_CODE);
				$Signer_3=FindSanctionState2($DocSN,"임원",$SANCTION_CODE);

				$this->smarty->assign('Signer_1',$Signer_1);
				$this->smarty->assign('Signer_2',$Signer_2);
				$this->smarty->assign('Signer_3',$Signer_3);

			}

			if($FormNum=="PTF-6-1")	//연장근무확인서
			{
				$Dt4=split("/n",$Detail4); //야근시업무
				$Dt5=split("/n",$Detail5); //사유

				$this->smarty->assign('Dt4',$Dt4);
				$this->smarty->assign('Dt5',$Dt5);

				$Signer_1=FindSanctionState2($DocSN,"관리자",$SANCTION_CODE);
				$Signer_2=FindSanctionState2($DocSN,"담당",$SANCTION_CODE2);
				$Signer_3=FindSanctionState2($DocSN,"관리자",$SANCTION_CODE2);


				$this->smarty->assign('Signer_1',$Signer_1);
				$this->smarty->assign('Signer_2',$Signer_2);
				$this->smarty->assign('Signer_3',$Signer_3);


			}

			if($FormNum=="PTF-2-3")	//출장배차신청서
			{

				$DetailData = array();
				$Dt2=split("/n",$Detail2);
				for($i=0; $i<count($Dt2)-1; $i++) {
						if($Dt2[$i] <> "")
						{

							$mGroup= MemberNo2GroupName($Dt2[$i]);
							$mRank = MemberNo2Rank($Dt2[$i]);
							$mName = MemberNo2Name($Dt2[$i]);

							$ItemData2=array("ID"=>$Dt2[$i],"Group" =>$mGroup,"Rank" =>$mRank,"Name"=>$mName);

						}else{
							$ItemData2=array("ID"=>'',"Group" =>'',"Rank" =>'',"Name"=>'');
						}
						array_push($DetailData,$ItemData2);

				}
				$this->smarty->assign('DetailData',$DetailData);


				$Dt4=split("/n",$Detail4);
				$this->smarty->assign('Dt4',$Dt4);


				$DetailNote = array();
				$Dt5=split("/n",$Detail5);
				for($i=0; $i<count($Dt5)-1; $i++) {
						$ItemData3=array("note"=>$Dt5[$i]);
						array_push($DetailNote,$ItemData3);
				}
				$this->smarty->assign('DetailNote',$DetailNote);



				$this->smarty->assign('Name',$Name);

				$Signer_1=FindSanctionState2($DocSN,"관리자",$SANCTION_CODE);
				$Signer_2=FindSanctionState2($DocSN,"임원",$SANCTION_CODE);

				$this->smarty->assign('Signer_1',$Signer_1);
				$this->smarty->assign('Signer_2',$Signer_2);
			}

			if($FormNum=="PTF-9-2-s" || $FormNum=="PTF-4-5-s" )	//연장근무신청서[개인],휴일근로신청서[개인]
			{

				$Signer_1=FindSanctionState_tmp($DocSN);
				$this->smarty->assign('Signer_1',$Signer_1);
			}

			if($FormNum=="PTF-9-2" || $FormNum=="PTF-4-5")	//연장근무신청서[팀장],휴일근로신청서[팀장]
			{

				//echo $Detail4."<br>";
				$Dt2=split("/n",$Detail2);
				$Dt3=split("/n",$Detail3);
				$Dt4=split("/n",$Detail4);
				$Dt5=split("/n",$Detail5);

				$query_data = array();
				for($i=0; $i<count($Dt2)-1; $i++) {

					if($Dt2[$i] <> "")
					{
						$ItemData2=array("MemberName"=>$Dt2[$i],"ProjectName" =>$Dt3[$i],"Detail2"=>$Dt4[$i],"MemberNo"=>$Dt5[$i]);
						array_push($query_data,$ItemData2);
					}


				}

				$Signer_1=FindSanctionState2($DocSN,"임원",$SANCTION_CODE);
				$Signer_2=FindSanctionState_tmp($DocSN);

				$this->smarty->assign('Signer_1',$Signer_1);
				$this->smarty->assign('Signer_2',$Signer_2);
				$this->smarty->assign('query_data',$query_data);

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
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('doc_status',$doc_status);
			$this->smarty->assign('DocSN',$dbkey);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('RegDate',$RegDate);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('ProjectCode',$ProjectCode);
			$this->smarty->assign('ProjectName',$ProjectName);
			$this->smarty->assign('AttchFile',$AttchFile);
			$this->smarty->assign('now_vacation',$now_vacation);
			$this->smarty->assign('MemberNo',$MemberNo);
			$this->smarty->assign('MemberInfo',$MemberInfo);
			$this->smarty->assign('RG_Code',$RG_Code);
			$this->smarty->assign('GroupName',$GroupName);
			$this->smarty->assign('RG_Date',$RG_Date);
			$this->smarty->assign('PG_Code',$PG_Code);
			$this->smarty->assign('ConservationYear',$ConservationYear);
			$this->smarty->assign('RT_Sanction',$RT_Sanction);
			$this->smarty->assign('RT_SanctionState',$RT_SanctionState);
			$this->smarty->assign('Security',$Security);
			$this->smarty->assign('Account',$Account);
			$this->smarty->assign('DocTitle',$DocTitle);
			$this->smarty->assign('FinishMemberNo',$FinishMemberNo);
			$this->smarty->assign('Detail1',$Detail1);
			$this->smarty->assign('Detail2',$Detail2);
			$this->smarty->assign('Detail3',$Detail3);
			$this->smarty->assign('Detail4',$Detail4);
			$this->smarty->assign('Detail5',$Detail5);
			$this->smarty->assign('Addfile',$Addfile);


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
			$this->smarty->display("intranet/common_contents/work_approval/document_input_mvc.tpl");
		}


		//============================================================================
		// 전자결재 Update Logic
		//============================================================================
		function UpdateAction()
		{

			include "../inc/approval_function.php";
			global $db,$memberID;

			global $FormNum,$TmpArrCount;

			global $NewSN,$FormNum,$ProjectCode,$DocTitle,$AttchFile,$MemberNo;
			global $RT_Sanction_,$RT_SanctionState;
			global $RG_Code,$Security,$ConservationYear,$Account;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5;
			global $MemberInfo,$DocSN;

			global $Position,$ConservationYear,$RG_Date,$RG_Code,$PG_Code,$PG_Date;
			global $mLabel,$mName,$mCode;

			global $Detail_1,$Detail_2,$Detail_3,$Detail_4,$Detail_5;
			global $menu_cmd,$kind;
			global $Comment,$FinishMemberNo,$MemberNo;

			$dbinsert="yes";

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



			//가결,부결등 결재처리-------------------------------------------------------------

			switch ($menu_cmd) {
			//가결-------------------------------------------------------
				case $PROCESS_ACCEPT:

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


						//처리부서 결재선 설정 및 입력 처리부서 접수일 입력
						$azSQL="update SanctionDoc_tbl set RT_Sanction='$RT_Sanction_' where DocSN='$DocSN'";

						if($dbinsert =="yes")
							$result=mysql_query($azSQL,$db);
						else
							echo "[1--- ".$azSQL."<br>";



						//가결 : 상신된 기안 내용을 인정하여 결재하는 행위
						$SanctionState = $PROCESS_ACCEPT;

						$NEW_SanctionState=NextSanctionState($RT_Sanction_,$RT_SanctionState);  //다음결재선 지정

						if(strpos($NEW_SanctionState,$PROCESS_RECEIVE) == false && strpos($NEW_SanctionState,$PROCESS_FINISH) == false)
						{
							$ipv=MemberNo2BossIP($NEW_SanctionState,'2');
							if($ipv <> "")
							{
								$send_string="CMD:ESIGNSEND=".$SendName."=".$$ipv;

								$this->smarty->assign('mode',"msg");
								$this->smarty->assign('send_string',$send_string);
								$this->smarty->display("intranet/js_page.tpl");
							}

						}
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



					if($dbinsert =="yes")
						$result=mysql_query($azSQL,$db);
					else
						echo "[2--- ".$azSQL."<br>";

					break;

			//--부결--(결의 내용을 승인하지않는 행위(재기안 불가))
				case $PROCESS_REJECTION:

					$SanctionState = $PROCESS_REJECTION;
					$NEW_SanctionState=":".$PROCESS_REJECTION.":".$MemberNum.":".date('Y-m-d').":".$Comment;
					$tmpFinishMemberNo="";
					break;

			//--반송 : 결의 내용을 승인하지않는 행위(반송의견을 반영하여 재기안 가능)
				case $PROCESS_RETURN:

					//부결 로 명칭바꿈
					$SanctionState = $PROCESS_RETURN;
					$NEW_SanctionState=":".$PROCESS_RETURN.":".$MemberNum.":".date('Y-m-d').":".$Comment;
					$tmpFinishMemberNo="";

					//새롭게 결재하기위해서
					$azSQL="delete from sanctionstate_tbl where DocSN='$DocSN'";


					if($dbinsert =="yes")
						$result=mysql_query($azSQL,$db);
					else
						echo "[3--- ".$azSQL."<br>";

					break;

			//-- 결재선 편집 내용 저장
				case $PROCESS_RECEIVE:

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


					//부서 접수시 다음 결재선이 본인 인경우 접수를 결재로 처리 다음으로 결재진행
					if(strpos($NEW_SanctionState,$memberID) !== false)
					{ //"-담당"
							//결재자 상세정보 기록
							$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','$Comment')";

							if($dbinsert =="yes")
								$result=mysql_query($azSQL,$db);
							else
								echo "[4--- ".$azSQL."<br>";

							$TmpState = split(":",$NEW_SanctionState);
							$SanctionOrder = $TmpState[0].":".$TmpState[1];

							$MemberNum = $TmpState[2]; //$n_num."-담당";
							$tmpFinishMemberNo = $tmpFinishMemberNo.$MemberNum.",".$SanctionDate.":";

							$NEW_SanctionState=NextSanctionState($RT_Sanction_,$NEW_SanctionState);
							$SanctionState = $PROCESS_ACCEPT;

							$SendIP = MemberNo2BossIP($NEW_SanctionState,'2');

							if($SendIP <> "")
							{
								$send_string="CMD:ESIGNSEND=".$SendName."=".$SendIP;

								$this->smarty->assign('mode',"msg");
								$this->smarty->assign('send_string',$send_string);
								$this->smarty->display("intranet/js_page.tpl");
							}


					}


					//처리부서 결재선 설정 및 입력 처리부서 접수일 입력
					$sql4_1 ="update SanctionDoc_tbl set RT_Sanction='$RT_Sanction_', PG_Date='$SanctionDate' where DocSN='$DocSN'";

					if($dbinsert =="yes")
						$result=mysql_query($sql4_1,$db);
					else
						echo "[4_1-- ".$sql4_1."<br>";

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
				}
				else
				{
					$azSQL = "update SanctionDoc_tbl set RT_SanctionState='$NEW_SanctionState', FinishMemberNo='$tmpFinishMemberNo' where DocSN='$DocSN'";
				}


				if($dbinsert =="yes")
					$result=mysql_query($azSQL,$db);
				else
					echo "[9--- ".$azSQL."<br>";

			//부서문서함의 부결된문서   끝------------------------------------------------------------

			//결재자 상세정보 기록------------------------------------------------------------
				if ($menu_cmd !==$PROCESS_RETURN) // 반송인경우 SanctinSattion를 모두 지워야 한다 / 다시 모든결재 새로하기위해서
				{
				$azSQL = "insert into SanctionState_tbl (DocSN, MemberNo, SanctionOrder, ReceiveDate, SanctionDate, SanctionState, Comment) values('$DocSN', '$MemberNum', '$SanctionOrder', '$ReceiveDate', '$SanctionDate', '$SanctionState','$Comment')";


					if($dbinsert =="yes")
						$result=mysql_query($azSQL,$db);
					else
						echo "[10--- ".$azSQL."<br>";

				}
			//결재자 상세정보 기록--끝----------------------------------------------------------


			//수신부서 결재자에게 메세지 보내기 -----------------------------------------------------------------
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

					if($SendIP <> "")
					{
						$send_string="CMD:ESIGNSEND=".$SendName."=".$SendIP;

						$this->smarty->assign('mode',"msg");
						$this->smarty->assign('send_string',$send_string);
						$this->smarty->display("intranet/js_page.tpl");
					}

				}
			//수신부서 결재자에게 메세지 보내기 -----------------------------------------------------------------


			//수신부서 결재처리완료후 처리 -----------------------------------------------------------------------

				if(strpos($NEW_SanctionState,$PROCESS_FINISH) !== false) { //"FINISH" 결재완료, "FINISH-DECISION" 전결

					switch ($FormNum)
					{

							//출장신청서
								case "PTF-2-3":

										echo "==========".$Detail_2[1]."<br>";
										$query01 = "select max(num) from userstate_tbl";
										$result01 = mysql_query($query01,$db);
										$result_num_01 = current(mysql_fetch_array($result01));
										$num_01 = $result_num_01 + 1;
										$query02 = "insert into userstate_tbl values('$num_01','$MemberNo','$RG_Code','3','$Detail_4[0]','$Detail_4[1]','$ProjectCode','$Detail1','')";

										if($dbinsert =="yes")
										{
											$result=mysql_query($query02,$db);
										}
										else
										{	echo "[11--- ".$query02."<br>";
										}

										for($i=0; $i<=4; $i++)
										{
											if($Detail_2[$i] <> "")
											{

												$num_01=$num_01+1;
												$query03 = "select * from member_tbl where MemberNo = '$Detail_2[$i]'";
												$result03 = mysql_query($query03,$db);
												$groupcode = mysql_result($result03,0,"GroupCode");

												$query04 = "insert into userstate_tbl values('$num_01','$Detail_2[$i]','$groupcode','3','$Detail_4[0]','$Detail_4[1]','$ProjectCode','$Detail1','')";

												if($dbinsert =="yes"){
													$result=mysql_query($query04,$db);
												}
												else
												{	echo "[12--- ".$query04."<br>";
												}

											}
										}
									break;


								// 근태사유서
								case "PTF-4-2":
									if($menu_cmd == "RECEIVE")
									{
										if ($DocTitle !="업무")  //업무시작미입력은 수동으로 넣게처리 /야근미입력은 연장근무확인서에서 처리
										{
											$query01 = "select max(num) from userstate_tbl";
											$result01 = mysql_query($query01,$db);
											$result_num_01 = current(mysql_fetch_array($result01));
											$max_num = $result_num_01 + 1;


											$query02 = "select * from systemconfig_tbl where SysKey = 'UserStateCode' and Name = '$DocTitle'";
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

													if ($DocTitle =="경유")  // 경유인경우는 systemconfig_tbl에 프로젝트 코드가 없음 만들어 넣어줌
													{
														$ProjectCode=date("y")."-교휴-06";
													}

													$inSql = "insert into userstate_tbl values('$max_num','$Detail_2[$i]','$groupcode','$StateCode','$Detail1','$Detail5','$ProjectCode','$Detail_3[$i]	','')";


													if($dbinsert =="yes"){
													$result=mysql_query($inSql,$db);
													}else{echo "[7--- ".$inSql."<br>";}

												}
												$max_num=$max_num+1;
											}
										}
									}

									break; // 근태사유 경영지원부 입력 내용 대체

									//  휴가계
									case "PTF-4-3":
										if($menu_cmd == "RECEIVE")
										{
											$query01 = "select max(num) from userstate_tbl";
											$result01 = mysql_query($query01,$db);
											$result_num_01 = current(mysql_fetch_array($result01));
											$max_num = $result_num_01 + 1;

											$query02 = "select * from systemconfig_tbl where SysKey = 'UserStateCode' and Name = '휴가'";
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

											$inSql = "insert into userstate_tbl values('$max_num','$MemberNo','$RG_Code','$StateCode','$Detail1','$Detail2','$ProjectCode','$Note','')";

											if($dbinsert =="yes"){
											$result=mysql_query($inSql,$db);
											}else{echo "[8--- ".$inSql."<br>";}
										}
									break; // 근태사유 경영지원부 입력 내용 대체


									// 연장근무확인서
									case "PTF-6-1":
										if(strpos($NEW_SanctionState,$PROCESS_FINISH) !== false)  //"FINISH" 결재완료, "FINISH-DECISION" 전결
										{
											for($i=0; $i<=3; $i++)
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

												$UpSql="update dallyproject_tbl set LeaveTime='$LeaveTime' ,LeavePCode='$ProjectCode',LeaveJobCode='$EntryJobCode',LeaveJob='$Detail4',OverTime='$OverTime',modify='1'  where MemberNo = '$MemberNo' and EntryTime like '$EntryTimelike%'";


												if($dbinsert =="yes"){
												$result=mysql_query($UpSql,$db);
												}else{echo "[11--- ".$UpSql."<br>";}
											}
											else  //근무기록이 없을때 입력하기
											{

												$query_sk = "select * from member_tbl where MemberNo = '$MemberNo'";
												$result_sk_00 = mysql_query($query_sk,$db);
												if(mysql_num_rows($result_sk_00) > 0) {
													$result_sk_rk = mysql_result($result_sk_00,0,"RankCode");   /// 직급
													////////// sortkey 만드는곳
													$result_sk_gp = mysql_result($result_sk_00,0,"GroupCode");   /// 부서
												}
												if($result_sk_gp < 10) {
													$result_sk_gp = "0".$result_sk_gp;
												}
												$sortkey = "0".$result_sk_gp.$result_sk_rk;

												$OverTime =$Detail1." ".$Detail2.":00";

												if($Detail3 < "06:00") {$Detail1 = next_day($Detail1);}
												$LeaveTime=$Detail1." ".$Detail3.":00";

												$sub_code="";

												$holy_sc = holycheck($Detail1);
												if ($holy_sc =="holyday") //휴일
												{
													$EntryTime=$Detail1." ".$Detail2.":00";
												}
												else
												{
													$EntryTime=$Detail1." "."08:50:00";
												}

												$dallyin = "insert into dallyproject_tbl (MemberNo,EntryTime,EntryPCode,EntryJobCode,EntryJob,SortKey,LeaveTime,LeavePCode,LeaveJobCode,LeaveJob,OverTime,modify) values('$MemberNo','$EntryTime','$ProjectCode','$sub_code','$Detail4','$sortkey','$LeaveTime','$ProjectCode','$sub_code','$Detail4','$OverTime','1')";

												if($dbinsert =="yes"){
												$result=mysql_query($dallyin,$db);
												}else{echo "[11--- ".$dallyin."<br>";}
											}

										}
										break; // 근무시간 경영지원부 입력 내용 대체


										//연장근무신청서(팀장)
										case "PTF-9-2":
										// 접수일어나면 dallyproject_tbl 에 연장근무 접수됬다는 값 update해줌

											$azSQL = "select * from SanctionDoc_tbl where DocSN='$DocSN'";
											echo  "[15--- ".$azSQL."<br>";

											$re_SelSql = mysql_query($azSQL,$db);

											if(mysql_num_rows($re_SelSql) > 0) {
												$Member_num = mysql_result($re_SelSql,0,"Detail5");
												$Entry_Time = mysql_result($re_SelSql,0,"Detail1");

												$TmpState = split("/n",$Member_num);
												$Count_re = count ($TmpState);


												for($i=0; $i<$Count_re; $i++)
												{
													if($TmpState[$i] <> "")
													{
														$UpSql="update dallyproject_tbl set modify='1' where MemberNo = '$TmpState[$i]' and EntryTime like '$Entry_Time%'";
														if($dbinsert =="yes"){
															$result=mysql_query($UpSql,$db);
														}
														else
														{
															echo  "[18--- ".$UpSql."<br>";
														}
													}
												}
											}

											break;

										//휴일근무신청서(팀장)
										case "PTF-4-5":
											//금요일날 신청하므로 토,일에 dallyproject_tbl 에 로그인기록이 없을수 있으므로 체크해서 없으면 날짜랑 modify값만 넣어놓음,있으면 연장근무랑 동일하게처리
											$azSQL = "select * from SanctionDoc_tbl where DocSN='$DocSN'";
											//echo  "[155--- ".$azSQL."<br>";

											$re_SelSql = mysql_query($azSQL,$db);

											if(mysql_num_rows($re_SelSql) > 0) {
												$Member_num = mysql_result($re_SelSql,0,"Detail5");
												$Entry_Time = mysql_result($re_SelSql,0,"Detail1");

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
															$aZSql = "insert into dallyproject_tbl (MemberNo,EntryTime,modify) values('$TmpState[$i]','$Entry_Time 00:00:00','1')";
														}


														if($dbinsert =="yes"){
														$result=mysql_query($aZSql,$db);
														}else
														{
															echo  "[18--- ".$aZSql."<br>";
														}
													}
												}
											}

											break;
									}



								//처음기안자에게 결재완료 메세지 보내기
								$SendIP = MemberNo2Ip($MemberNo);  //처음기안자IP
								$SendName= DocCode2Name($FormNum);

								if($SendIP <> "")
								{
									$send_string="CMD:ESIGNENDSEND=".$SendName."=".$SendIP;

									$this->smarty->assign('mode',"msg");
									$this->smarty->assign('send_string',$send_string);
									$this->smarty->display("intranet/js_page.tpl");
								}


				}

				//수신부서 결재처리완료후 처리 끝----------------------------------------------------------------

				$this->smarty->assign('target',"opener");
				$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=$kind");
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
							$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6))."/대결";
							$TmpArr[$i]=$TmpArr[$i]."-대결";
							$Msg="-대결";
						}

						$StrResign1=strpos($ItemData,"부서장-대결");
						if ($StrResign1 !== false) //대결이 없으면 대결-대결 중복방지(팀장,부서장 결재시 대결이란 말이 있으면
						{
							$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6))."/대결";
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
												$msg="<input type=text size=3 readonly style='font-size: 12px;font-family:'돋움';padding:2px;text-align:center;' value='결재중'  />";
											}
											else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
											}
										} else {
												$msg="<input type=text size=4 readonly style='font-size: 12px;font-family:'돋움';padding:2px;text-align:center;' value='결재완료'  />";
										}
									} else {
										if(FinishCheck($DocSN,($i+1)) == "NO") {
											if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
												$msg="<input type=text size=3 readonly style='font-size: 12px;font-family:'돋움';padding:2px;text-align:center;' value='결재중'  />";
											}
											else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
											}
										} else {
												$msg="<input type=text size=4 readonly style='font-size: 12px;font-family:'돋움';padding:2px;text-align:center;' value='결재완료'  />";
										}
									}
							} else {  //결의부서내 결재중인경우
								if($i < $Receive_index) {
									if(FinishCheck($DocSN,($i+1)) == "NO") {
										if ($doc_status == $DOC_STATUS_APPROVE  && $memberID ==  substr($ItemData,0,6))  //결재자가 자신의 결재를 다른사람으로 변경못하게 처리
										{
											$msg="<input type=text size=3 readonly style='font-size: 12px;font-family:'돋움';padding:2px;text-align:center;' value='결재중'  />";
										}
										elseif($doc_status == $DOC_STATUS_CREATE  && $memberID ==  substr($ItemData,0,6) && $i==0)  //처음기안자 담당항목이 있는경우
										{
											$msg="<input type=text size=3 readonly style='font-size: 12px;font-family:'돋움';padding:2px;text-align:center;' value='기안자'  />";
										}
										else{
											$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>변　경</a></div>";
										}
									} else {
											$msg="<input type=text size=4 readonly style='font-size: 12px;font-family:'돋움';padding:2px;text-align:center;' value='결재완료'  />";
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
									$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
								} else {
									$msg="-"; //Skip

								}
							}
						}


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

			$sql ="select a.MemberNo as MemberNo,a.korName as Name,b.Name as GroupName,a.Name as Position,a.EntryDate as EntryDate,a.Pasword as Pasword
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
			)b on a.GroupCode = b.code";

			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			while($re_row = mysql_fetch_array($re))
			{
					$EntryDate=$re_row[EntryDate];

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

			//============================================================================
			// MY Vacation 사용휴가
			//============================================================================
				$spend_day=0;
				$sql_use = "select * from userstate_tbl where state = 1 and MemberNo = '$MemberNo' and start_time like '$ThisYear%' and end_time <>'0000-00-00'";
				$re_use = mysql_query($sql_use,$db);
				$re_num_use = mysql_num_rows($re_use);
				if($re_num_use > 0)
				{
					while($re_row_use = mysql_fetch_array($re_use))
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
					}
				}
			 $now_vacation=$rest_day+$new_day-$spend_day;
			 return ($now_vacation);
		}

		function Passcheck()
		{
			global	$db,$memberID,$menu_cmd,$CMD_TYPE;

			$user_ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];   /// remote ip 저장
			$sql = "select * from member_tbl where MemberNo = '$memberID'";

			$re = mysql_query($sql,$db);
			$re_row = mysql_fetch_array($re);
			$db_pw = $re_row[Pasword];

			$this->smarty->assign('db_pw',$db_pw);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('menu_cmd',$menu_cmd);
			$this->smarty->assign('CMD_TYPE',$CMD_TYPE);
			$this->smarty->display("intranet/common_contents/work_approval/pass_check_mvc.tpl");
		}
}
?>
