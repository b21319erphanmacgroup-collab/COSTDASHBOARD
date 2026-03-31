<?php
if ( $_GET["ActionMode"] <> 'baby_vacation' and $_GET["ActionMode"] <> 'Admin' and $_REQUEST["mobile_view"] <> 'data' ){
	?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?php
}

	/***************************************
	* 전자결재 리스트
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../util/OracleClass.php";
//	include "../util/OracleClass.php";


	$now_day=date("Y-m-d h:i:s");

	extract($_GET);
	class ApprovalLogic {
		var $smarty;
		function ApprovalLogic($smarty)
		{
			//$this->oracle=new OracleClass($smarty);
			$this->smarty=$smarty;
		}

		//============================================================================
		// 전자결재 Delete
		//============================================================================
		function DeleteAction()
		{
			include "../inc/approval_function.php";
			global $db,$memberID;
			global $auth,$menu_cmd,$sub_index,$docno, $mobile;
			global $Start,$page,$currentPage,$last_page,$kind,$Category,$FormNum,$CompanyKind, $ProcessEnd;

				switch ($menu_cmd) {
				case $PROCESS_CANCEL:  //기안취소 (임시저장으로 변경)
					$query03 = "select * from SanctionDoc_tbl where DocSN='$docno'";
					$result03 = mysql_query($query03,$db);
					$Detail1 = mysql_result($result03,0,"Detail1");
					$Detail4 = mysql_result($result03,0,"Detail4");
					$FormNum= mysql_result($result03,0,"FormNum");

					$TmpDocSN=TempSerialNo2($memberID);
					$RT_SanctionState=TempState($docno);  //사번으로 입력
					$azSQL = "update SanctionDoc_tbl set DocSN='$TmpDocSN', RT_SanctionState='$RT_SanctionState', FinishMemberNo='', PG_Date='' where DocSN='$docno'";
					$result=mysql_query($azSQL,$db);

					$azSQL2 = "update official_plan_tbl set DocSN='$TmpDocSN' where DocSN='$docno'";
					$result=mysql_query($azSQL2,$db);

					if(strpos($FormNum, "HMF-10-1") !== false){

						$this->oracle=new OracleClass($smarty);
						//전표 삭제
						$Detail1_arr=explode('_',$Detail1);

						$PJT_CODE=$Detail1_arr[0];
						$DGREE=$Detail1_arr[1];
						$WBS_CODE=$Detail1_arr[2];

						$procedure01="BEGIN USP_PM_CONT_0801_APPROVAL('11','$PJT_CODE','$DGREE','$WBS_CODE','N','C','$FormNum','$MemberNo');END;";

						$this->oracle->ProcedureExcuteQuery($procedure01);

					}
					elseif(strpos($FormNum, "HMF-10-2") !== false){

						$this->oracle=new OracleClass($smarty);
						//전표 삭제
						$Detail1_arr=explode('_',$Detail1);

						$PJT_CODE=$Detail1_arr[0];
						$DGREE=$Detail1_arr[1];
						$WBS_CODE=$Detail1_arr[2];
						$ORA_DeptCode=$Detail1_arr[3];
						/*
						$procedure01="BEGIN USP_Pm_Cont_09_Up('11','$PJT_CODE','$ORA_DeptCode','$WBS_CODE','N','$DGREE','$MemberNo');END;";
						$this->oracle->ProcedureExcuteQuery($procedure01);
						*/

						$procedure02="BEGIN USP_Pm_Cont_Intra_Approval('11','$PJT_CODE','$DGREE','$WBS_CODE','C','$MemberNo');END;";
						$this->oracle->ProcedureExcuteQuery($procedure02);
					}elseif( ( $FormNum == 'BRF-2-4' or $FormNum == 'HMF-2-4' ) ){
						

						$split_d4 = explode('/n',$Detail4);
						$azSQL2 = "delete from userstate_tbl where MemberNo = '$memberID' and state = 3 and start_time = '".$split_d4[0]."' and end_time = '".$split_d4[1]."'; ";
						//echo $azSQL2;

						$log_txt = date("Y-m-d H:i:s",time())." , ".preg_replace('/\r\n|\r|\n/',' ',$azSQL2);
						$log_file = "../log/".date("Y-m-d",time())."_mysql_cancle.txt";
						if(is_dir($log_file)){
							$log_option = 'w';
						}else{
							$log_option = 'a';
						}

						$log_file = fopen($log_file, $log_option);
						fwrite($log_file, $log_txt."\r\n");
						fclose($log_file);

						$result=mysql_query($azSQL2,$db);
						
						if($docno != ""){
							$schCarSQL = "DELETE FROM schedule_car_tbl WHERE DocSN='$docno'";
							mysql_query($schCarSQL,$db);
						}
					}

					break;
				case $PROCESS_DELETE:  //문서삭제

					//첨부파일 삭제 추가 필요
					$query03 = "select * from SanctionDoc_tbl where DocSN='$docno'";
					$result03 = mysql_query($query03,$db);
					$Addfile = mysql_result($result03,0,"Addfile");
					$Detail2= mysql_result($result03,0,"Detail2");
					$Detail3= mysql_result($result03,0,"Detail3");
					$Detail4= mysql_result($result03,0,"Detail4");
					$FormNum= mysql_result($result03,0,"FormNum");


					if($Addfile!="")
					{
						$Addfile = "./../../../intranet_file/documents/".$Addfile;
					}
					if ($Addfile != "")
					{
						$exist = file_exists("$Addfile");
						if($exist)
						{
							$re=unlink("$Addfile");
						}
					}

					$azSQL = "delete from SanctionDoc_tbl where DocSN='$docno'";
					$result=mysql_query($azSQL,$db);

					$azSQL2 = "delete from official_plan_tbl where DocSN='$docno'";
					$result=mysql_query($azSQL2,$db);

					if(strpos($FormNum, "HMF-5-") !== false){

						//include "../util/OracleClass.php";
						$this->oracle=new OracleClass($this->smarty);
						//전표 삭제
						$temp_code = split('-', $Detail2);
						$dateto = $temp_code[1];
						$dept = $temp_code[2];
						$seq = (int)$temp_code[3];

						$prosql ="BEGIN Usp_slipreport_0001('$dateto', '$dept', '$seq', '0' ); END;";
						$this->oracle->ProcedureExcuteQuery($prosql);

						$cfile="../log/".date("Y-m-d")."_HMF-5_DELETE.txt";
						$exist = file_exists("$cfile");
						if($exist) {
							$fd=fopen($cfile,'r');
							$con=fread($fd,filesize($cfile));
							fclose($fd);
						}
						$fp=fopen($cfile,'w');
						$aa=date("Y-m-d H:i");
						$cond=$con.$aa." ".$docno." ".$prosql."\n";
						fwrite($fp,$cond);
						fclose($fp);

					}elseif(strpos($FormNum, "HMF-10-1") !== false){
						$Detail1= mysql_result($result03,0,"Detail1");

						$this->oracle=new OracleClass($this->smarty);

						$Detail1_arr=explode('_',$Detail1);
						$AttchFile = mysql_result($result03,0,"AttchFile");

						$PJT_CODE=$Detail1_arr[0];
						$DGREE=$Detail1_arr[1];
						$WBS_CODE=$Detail1_arr[2];

						$FolderName=$FormNum."/".$PJT_CODE."-".$DGREE."-".$WBS_CODE;

						if($AttchFile!="")
						{
							$Addfile = "./../../../intranet_file/documents/".$FolderName."/".$AttchFile;
						}
						if ($Addfile != "")
						{
							$exist = file_exists("$Addfile");
							if($exist)
							{
								$re=unlink("$Addfile");
							}
						}

						$procedure01="BEGIN USP_PM_CONT_0801_APPROVAL('11','$PJT_CODE','$DGREE','$WBS_CODE','N','N','$FormNum','$MemberNo');END;";

						$this->oracle->ProcedureExcuteQuery($procedure01);
					}
					elseif(strpos($FormNum, "HMF-10-2") !== false){
						$Detail1= mysql_result($result03,0,"Detail1");

						$this->oracle=new OracleClass($this->smarty);
						//전표 삭제
						$Detail1_arr=explode('_',$Detail1);
						$AttchFile = mysql_result($result03,0,"AttchFile");

						$PJT_CODE=$Detail1_arr[0];
						$DGREE=$Detail1_arr[1];
						$WBS_CODE=$Detail1_arr[2];
						$ORA_DeptCode=$Detail1_arr[3];

						$FolderName=$FormNum."/".$PJT_CODE."-".$DGREE."-".$WBS_CODE;

						if($AttchFile!="")
						{
							$Addfile = "./../../../intranet_file/documents/".$FolderName."/".$AttchFile;
						}
						if ($Addfile != "")
						{
							$exist = file_exists("$Addfile");
							if($exist)
							{
								$re=unlink("$Addfile");
							}
						}

						$procedure02="BEGIN USP_Pm_Cont_Intra_Approval('11','$PJT_CODE','$DGREE','$WBS_CODE','N','$MemberNo');END;";
						$this->oracle->ProcedureExcuteQuery($procedure02);
					}


					break;
				}



				$azSQL = "delete from SanctionState_tbl where DocSN='$docno'";
				$result=mysql_query($azSQL,$db);

				mysql_close($db);

				$azSQL2 = "delete from official_plan_tbl where DocSN='$docno'";
				$result=@mysql_query($azSQL2,$db);

				if($Category=="MyListView")
				{
					$this->smarty->assign('MoveURL',"approval_controller.php?Category=MyListView&FormNum=$FormNum");
				}
				else if($Category=="Selfclose")
				{
					$this->smarty->assign('target',"no");
				}
				else
				{
					$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=$kind");

				}

				if($mobile=="y"){
					$this->smarty->assign('target',"self");
					$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&memberID=".$memberID."&tab_index=4&mobile=y");
				}

				$this->smarty->display("intranet/move_page.tpl");



		}



		//============================================================================
		// 전자결재 결재상황별 리스트
		//============================================================================
		function View()
		{
			include "../inc/approval_function.php";
			global $db,$memberID,$ComKind;
			global $auth,$tab_index,$sub_index,$doc_state;
			global $Start,$page,$currentPage,$last_page,$CompanyKind;
			global $sdate,$edate,$keyword;
			global $mobile, $mobile_view;

			/* ----------------------------------- */
			//$WorkPosition = getWorkPositionByMemberNo2($memberID); //워크포지션(WorkPosition)
			//$this->smarty->assign("WorkPosition",$WorkPosition);

			$office_type = getOfficeTypeByMemberNo($memberID); //office_type
			$this->smarty->assign("office_type",$office_type);

			/* ----------------------------------- */


			if($edate == ""){
				$edate = date("Y-m-d", mktime(0,0,0, date("m"), date("d"), date("Y")));
				$sdate = date("Y-m-d", mktime(0,0,0, date("m"), date("d")-14, date("Y")));
			}

			$CompanyKind=searchCompanyKind2();
			$MyGroupCode=MemberNo2GroupCode($memberID);
			
			

			$page=15;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();

			if($tab_index=="") $tab_index=1;

			$GroupCode=MemberNo2GroupCode($memberID);

			if($ComKind==""){
				// if($GroupCode =="02" )
				// 	$ComKind=1;
				// else
					// $ComKind=2;
				// 바론으로 강제
				$ComKind=2;

			}

			if($tab_index=="1") //결재서류목록
			{
				$go_url="intranet/common_contents/work_approval/approval_list_mvc.tpl";
			}
			else if($tab_index=="2") //결재할문서
			{
				$title_1="기안자";
				$title_2="받은날";
				$title_3="결재";


				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";

				//처리부서 담당자 체크
				$sql="select * from approval_tbl where ReceiveMember='$memberID'  ";
				$re = mysql_query($sql,$db);
				$re_row = mysql_num_rows($re);//총 개수 저장

				while($re_row = mysql_fetch_array($re)){
					$FormList=$FormList."'".$re_row[FormName]."',";
				}
				


				if($FormList == "")  //일반사용자
				{

					$sql = "select * from view_sanctiondoc_tbl where
					RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%'
					or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%'
					or ( RT_SanctionState like '%".$PROCESS_FINISH."%' and confirm_members like '%".$memberID."/0%' )
					or (FormNum IN ('HMF-4-7','BRF-4-7','HMF-5-5') and confirm_members like '%".$memberID."/0%' ) 
					order by RG_Date asc";
					$sql2 =$sql." limit $Start, $page";
					
				}
				else  //처리부서
				{

					$FormList=substr($FormList,0,strlen($FormList)-1);
					$sql = "select * from view_sanctiondoc_tbl where (RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%') or (PG_Code='".$MyGroupCode."' and RT_SanctionState like '%".$PROCESS_RECEIVE."%' and FormNum in ($FormList))
					or ( RT_SanctionState like '%".$PROCESS_FINISH."%' and confirm_members like '%".$memberID."/0%' )

					order by RG_Date asc";
					$sql2 =$sql." limit $Start, $page";
					
				}

				//업무협조전
				$sql3="select * from sanction_notice_tbl where send_member like '%".$memberID."%' and read_member not like '%".$memberID."%' order by no desc";
				$re3 = mysql_query($sql3,$db);
				$TotalRow_notice = mysql_num_rows($re3);
				while($re_row3 = mysql_fetch_array($re3))
				{
					$re_row2[FormName]="업무협조전/회람";
					$re_row2[DocTitle]=$re_row3[title];
					$re_row2[value1]=$re_row3[name];
					$re_row2[value2]=$re_row3[write_day];
					$re_row2[value3]=$value3;
					$re_row2[State]="처리중";
					$re_row2[BtnText]="문서접수";
					$re_row2[Addfile]=$re_row3[filename];
					$re_row2[Command]="approvedoc";

					$CommandDo = "noticedoc"."('".$re_row3[no]."','".$memberID."','accept');";
					$re_row2[CommandDo]=$CommandDo;
					array_push($query_data,$re_row2);
				}

			}
			else if($tab_index=="3") //결재한문서
			{
				$title_1="기안자";
				$title_2="결재일";
				$title_3="진행사항";

				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";

				//$sql = "select * from SanctionDoc_tbl where FinishMemberNo like '%".$memberID."%' order by PG_Date desc";
				/*
				if($memberID=="T03225")
				{
					$sql = "select * from view_sanctiondoc_3year_tbl where FinishMemberNo like '%".$memberID."%' and RG_Date between '$sdate' and '$edate' order by RG_Date desc";
				}else
				{
					$sql = "select * from SanctionDoc_tbl where FinishMemberNo like '%".$memberID."%' order by RG_Date desc";
				}
				*/
				$sql = "select * from view_sanctiondoc_3year_tbl
						where (FinishMemberNo like '%".$memberID."%' or confirm_members like '%".$memberID."/1%' )
						  and RG_Date between '$sdate' and '$edate'
						  and DocTitle LIKE '%$keyword%'
						order by RG_Date desc";
				$sql2 =$sql." limit $Start, $page";
				$this->smarty->assign("keyword",$keyword);
			}
			else if($tab_index=="4") //기안한문서
			{
				$title_1="기안일";
				$title_2="진행사항";
				$title_3="취소,상신";

				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";

				$sql = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$PROCESS_CODE."%' or RT_SanctionState like '%".$PROCESS_RECEIVE."%') order by RG_Date desc";
				$sql2 =$sql." limit $Start, $page";

			}else if($tab_index=="5") //결재완료된문서
			{
				$title_1="기안일";
				$title_2="완료일";
				$title_3="&nbsp;";

				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";

				/*
				if($memberID=="T03225")
				{
					$sql = "select * from view_sanctiondoc_3year_tbl where MemberNo='".$memberID."' and RT_SanctionState like '%".$PROCESS_FINISH."%' and FormNum <> 'HM-NOTICE' and RG_Date between '$sdate' and '$edate' order by RG_Date desc";
				}else
				{
					$sql = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and RT_SanctionState like '%".$PROCESS_FINISH."%' and FormNum <> 'HM-NOTICE' order by RG_Date desc";
				}
				*/
				$sql = "select * from view_sanctiondoc_3year_tbl where MemberNo='".$memberID."' and RT_SanctionState like '%".$PROCESS_FINISH."%' and FormNum <> 'HM-NOTICE' and RG_Date between '$sdate' and '$edate' order by RG_Date desc";

				$sql2 =$sql." limit $Start, $page";

			}else if($tab_index=="6") //부결,반송된문서
			{
				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";

				$title_1="기안일";
				$title_2="반송일";
				$title_3="재기안";

				//$sql = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$PROCESS_REJECTION."%' or RT_SanctionState like '%".$PROCESS_RETURN."%') order by RG_Date desc";

				$sql = "select * from SanctionDoc_tbl where ((MemberNo='".$memberID."' and (RT_SanctionState like '%".$PROCESS_REJECTION."%' or RT_SanctionState like '%".$PROCESS_RETURN."%')) or (RT_SanctionState like '%RETURN:".$memberID."%') or (RT_SanctionState like '%RE:".$memberID."%') or (RT_SanctionState like '%BACK:".$memberID."%') or (RT_SanctionState like '%BACKPERSON:".$memberID."%'))";

				$sql2 =$sql." limit $Start, $page";

			}else if($tab_index=="7") //출금전표
			{
				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";

				$title_1="기안일";
				$title_2="반송일";
				$title_3="재기안";

				//$sql = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$PROCESS_REJECTION."%' or RT_SanctionState like '%".$PROCESS_RETURN."%') order by RG_Date desc";

				$sql = "select * from SanctionDoc_tbl where (MemberNo='".$memberID."' and (FormNum like 'HMF-5%' OR FormNum like 'BRF-5%'))";

				$sql2 =$sql." limit $Start, $page";

			}
			// echo $sql2;

			if($tab_index<>"1" and ( $mobile != "y" or $mobile_view == 'data' ))
			{
				$re = mysql_query($sql,$db);
				//$TotalRow = mysql_num_rows($re);//총 개수 저장
				$TotalRow = mysql_num_rows($re)+$TotalRow_notice;//총 개수 저장

				$last_start = ceil($TotalRow/10)*10+1;;
				$last_page=ceil($TotalRow/10);
			
				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2))
				{
						$DocSN=$re_row2[DocSN]; //문서번호
						$FormNum=$re_row2[FormNum];
						$FormName = Code2Name($FormNum, 'bizform', 0); //양식명

						$AttchFile=$re_row2[Addfile];
						$DocTitle=$re_row2[DocTitle];
						$RG_Date=$re_row2[RG_Date];
						$MemberNo=$re_row2[MemberNo];
						$KorName = MemberNo2Name($MemberNo); //기안자 성명
						$RankName = ViewRankName($MemberNo);
						if($FormNum=="HM-NOTICE")
							$SanctionDate=$re_row2[PG_Date];
						else
							$SanctionDate=FindSanctionDate($DocSN, $memberID); //기결함:결재일
						$RT_SanctionState=$re_row2[RT_SanctionState];  //결재상태
						$StateArray=split(":",$RT_SanctionState);
						$DocSN=$re_row2[DocSN]; //문서번호

						if(substr($DocSN,0,5) == $PROCESS_TEMPORARY) {
							$doc_state = $PROCESS_TEMPORARY;

						} else {
							$doc_state = $PROCESS_APPROVE;
						}

						
						$State="";
						if((strpos($RT_SanctionState,"부서내") !==false) or (strpos($RT_SanctionState,"RECEIVE") !==false)) {
							if((strpos($RT_SanctionState,"결의부서내:".$memberID) !==false) or (strpos($RT_SanctionState,"처리부서내:".$memberID) !==false))
							{
								$State="처리중";
							}else
							{
								if(strpos($RT_SanctionState,"임시저장") !==false)
								{
										$State="임시저장";
								}else
								{
										$State="처리중";
								}

							}
						}else if(strpos($RT_SanctionState,"FINISH") !==false) {
								$State="결재완료";
						}else if((strpos($RT_SanctionState,"REJECT") !==false) or (strpos($RT_SanctionState,"RETURN") !==false)) {
								$State="부결반송";
						}


						//---------------------------------------------------------------------------------------------------
						$value1 = "";
						$value2 = "";
						$value3 = "";
					

						if($RG_Date<"2016-06-01")
						{
							$tmp_old=true;
						}else
						{
							$tmp_old=false;
						}
						
						switch ($tab_index ) {
							case 2: //미결함(결재할문서)
								if($StateArray[1] == $PROCESS_FINISH || (strstr($re_row2['confirm_members'],$_SESSION["memberID"]) != '' && strstr($re_row2['RT_Sanction'],$_SESSION["memberID"]) == '')){
									$BtnText="참조하기";
									if($tmp_old){
										$Command = "viewdoc_old";   //참조하기
									}else{
										$Command = "viewdoc";  //참조하기
									}									
								}
								else if($StateArray[1] == $PROCESS_RECEIVE) {
									$BtnText="접수하기";

									if($tmp_old)
									{
										$Command = "acceptdoc_old";   //접수하기
									}else
									{
										$Command = "acceptdoc";   //접수하기
									}
								}else {
									
									$BtnText="결재하기";
									if($tmp_old)
									{
										$Command = "approvedoc_old";   //접수하기
									}else
									{
										$Command = "approvedoc";  //결재하기
									}
									

								}
								$value1 = $KorName;
								$value2 = $StateArray[3]; //받은날
								$value3 = "&nbsp;";
								break;

							case 3: //기결함(결재한문서)
								if($tmp_old)
								{
									$Command = "viewdoc_old";     //문서보기
								}else
								{
									$Command = "viewdoc";     //문서보기
								}

								$value1 = $KorName;
								$value2 = $SanctionDate;
								$value3=ProcessingState($DocSN, $StateArray[1], $StateArray[2]);
								break;

							case 4: //기안함

								if($doc_state == $PROCESS_TEMPORARY) {
									if($tmp_old)
									{
										$Command = "editdoc_old"; //편집하기 및 결재상신
									}else
									{
										$Command = "editdoc"; //편집하기 및 결재상신
									}
									$value2 = "임시파일";
								} else {
									if($tmp_old)
									{
										$Command = "viewdoc_old";     //결재상신 후 보기만
									}else
									{
										$Command = "viewdoc"; //결재상신 후 보기만
									}
									$value2 = ProcessingState($DocSN, $StateArray[1], substr($StateArray[2],0,6)).$FinishMemberNo; //임시저장/접수대기/부서결재1/처리부서1
								} //임시저장인 경우
								$value1 = $RG_Date;
								$value3 = "&nbsp;";

								break;

							case 5:	//완료함

								if($tmp_old)
								{
									$Command = "viewdoc_old"; //문서보기
								}else
								{
									$Command = "viewdoc";     //문서보기
								}

								$value1 = $RG_Date;
								$value2 = $StateArray[3]; //완료일
								$value3 = "&nbsp;";
								break;

							case 6:	//부결/반송함
								if($StateArray[1] == $PROCESS_RETURN) {

									if($tmp_old)
									{
										$Command = "editdoc_old"; //부결인경우 보기만
									}else
									{
										$Command = "editdoc"; // 반송인경우 재기안
									}
								} else {
									if($tmp_old)
									{
										$Command = "viewdoc_old"; //부결인경우 보기만
									}else
									{
										$Command = "viewdoc";     //부결인경우 보기만
									}
								}

								$Command2="";
								$RETURNID="RETURN:".$memberID;
								if(strpos($RT_SanctionState,$RETURNID) !== false and $MemberNo != $memberID)
								{
									$Command = "viewdoc";
									$Command2 = "viewdoc";
								}

								if(strpos($FormNum,'HMF-5') !== false)
								{	if($MemberNo == $memberID)
									{
										$Command2 = "";
									}
								}

								$RETURNID2="RE:".$memberID;
								if(strpos($RT_SanctionState,$RETURNID2) !== false and $MemberNo != $memberID)
								{
									$Command = "viewdoc";
									$Command2 = "viewdoc2";
								}

								$value1 =$RG_Date;
								$value2 =$StateArray[3];  //반송일
								//$value3 = "&nbsp;";
								$value3 =$StateArray[4];  //반송이유
								break;
						}

						//연장근무신청서 [개인]
							if($FormNum=="HMF-9-2-s" && $tab_index == "2")
							{

								if($tmp_old)
								{
									$CommandDo = "acceptdoc_all_old"."('".$re_row2[Detail1]."','HMF-9-2','".$memberID."');";
								}else
								{
									$CommandDo = "acceptdoc_all"."('".$re_row2[Detail1]."','HMF-9-2','".$memberID."');";
								}
								$BtnText="통합결재";
							}
							else if($FormNum=="HMF-4-5-s" && $tab_index == "2") //휴일근무
							{
								if($tmp_old)
								{
									$CommandDo = "acceptdoc_all_old"."('".$re_row2[Detail1]."','HMF-4-5','".$memberID."');";
								}else
								{
									$CommandDo = "acceptdoc_all"."('".$re_row2[Detail1]."','HMF-4-5','".$memberID."');";
								}
								$BtnText="통합결재";
							}
							else if($FormNum=="BRF-9-2-s" && $tab_index == "2")
							{
								if($tmp_old)
								{
									$CommandDo = "acceptdoc_all_old"."('".$re_row2[Detail1]."','BRF-9-2','".$memberID."');";
								}else
								{
									$CommandDo = "acceptdoc_all"."('".$re_row2[Detail1]."','BRF-9-2','".$memberID."');";
								}
								$BtnText="통합결재";
							}
							else if($FormNum=="BRF-4-5-s" && $tab_index == "2") //휴일근무
							{
								if($tmp_old)
								{
									$CommandDo = "acceptdoc_all_old"."('".$re_row2[Detail1]."','BRF-4-5','".$memberID."');";
								}else
								{
									$CommandDo = "acceptdoc_all"."('".$re_row2[Detail1]."','BRF-4-5','".$memberID."');";
								}

								$BtnText="통합결재";
							}
							else if($FormNum=="HM-NOTICE") //업무협조전
							{
								$CommandDo = "noticedoc"."('".$re_row2[Detail1]."','".$memberID."','view');";
								$BtnText="문서접수";
							}
							else
							{

								$CommandDo = $Command."('".$FormNum."','".$DocSN."','".$memberID."');";

							}

							if($tab_index==5 && ($FormNum=="HMF-8-1" || $FormNum=="HMF-8-2"))
							{
								$filesql="Select
								*
								From
								tn_file_revise_tbl
								Where
								PAGE='$re_row2[Detail4]'
								AND NO='$re_row2[Detail5]'";
								$filere=mysql_query($filesql,$db);

								while($file_row=mysql_fetch_array($filere))
								{
									$ProjectCode=$file_row['PROJECTCODE'];
									$FilePath=$file_row['FILEPATH'];
									$FileName=$file_row['FILENAME'];
									$ViewFileName=$file_row['VIEW_FILENAME'];
									$SecurityRank=$file_row['SECURITYLEVEL'];
									$DownUser=$file_row['DOWNUSER'];
								}

								$re_row2[ProjectCode]=$ProjectCode;
								$re_row2[FilePath]=$FilePath;
								$re_row2[FileName]=$FileName;
								$re_row2[ViewFileName]=$ViewFileName;
								$re_row2[SecurityRank]=$SecurityRank;
								$re_row2[DownUser]=$DownUser;

								$re_row2[Addfile]='Addfile';
							}


						$re_row2[FormName]=$FormName;
						$re_row2[KorName] = $KorName;
						$re_row2[RankName]=$RankName;
						$re_row2[value1]=$value1;
						$re_row2[value2]=$value2;
						$re_row2[value3]=$value3;
						$re_row2[State]=$State;
						$re_row2[BtnText]=$BtnText;

						$re_row2[Command]=$Command;
						$re_row2[Command2]=$Command2;
						$re_row2[CommandDo]=$CommandDo;

						if(strpos($FormNum, "HMF-6-") !== false){

							$Addfile=$re_row2[Addfile];
							$Addfile_arr=split("/n",$Addfile);
							$re_row2[Addfile] = $Addfile_arr[0];
						}

						if(strpos($FormNum, "HMF-5-") !== false){
							$Addfile=$re_row2[Addfile];
							$Addfile_arr=split("/n",$Addfile);
							$re_row2[Addfile] = $Addfile_arr[0];
						}

						if(strpos($FormNum, "HMF-10-") !== false){
							$re_row2[Addfile]=$re_row2[AttchFile];
						}

						array_push($query_data,$re_row2);

				}


				if($currentPage == "") $currentPage = 1;
				$PageHandler =new PageControl($this->smarty);
				$PageHandler->SetMaxRow($TotalRow);
				$PageHandler->SetCurrentPage($currentPage);
				$PageHandler->PutTamplate();
			}


			$sql3="select * from approval_tbl where ReceiveMember='$memberID' or NoticeMember='$memberID'";

			$re3 = mysql_query($sql3,$db);
			if(mysql_num_rows($re3) > 0){				
				$staff=true;
			}
			else{
				$staff=false;
			}
			
			
			if($GroupCode == "02"){
				$staff02 = true;
			}
			else{
				$staff02 = false;
			}


			if($mobile=="y"){
				$tab_Titel = array('결재서류<br>작성','결재할<br>문서','기안한<br>문서','부결,반송<br>문서','출금<br>전표');
				$tab_value = array('1','2','4','6','7');
			}else{
				$tab_Titel = array('결재서류작성','결재(참조)할 문서','결재(참조)한 문서','기안한 문서','결재완료된 문서','부결,반송된 문서');
				$tab_value = array('1','2','3','4','5','6');
			}

			$this->smarty->assign("page_action","approval_controller.php");




			

			//전표 접수 및 결재자 체크 시작
				//처리부서 담당자 체크
				$sql="select FormName from approval_tbl where ReceiveMember='$memberID' group by FormName ";
				
				//echo $sql."<br>";
				$re = mysql_query($sql,$db);
				
				
				while($re_row = mysql_fetch_array($re)){
					if($FormList == ""){
						$FormList = $re_row["FromName"];
					}
					else if($FormList != ""){
						$FormList = $FormList.",'".$re_row[FormName]."'";
					}
				}
				
				//$FormList = substr($FormList,0,strlen($FormList)-1);
				//문서 담당자 체크
				//echo "FormList----".$FormList."<Br>";
				
				

				$sql = "
					select
						*
					from
						sanctiondoc_tbl
					where";
				if($FormList != ""){
					$sql .= "
						( FormNum in ($FormList) and RT_SanctionState like '%".$PROCESS_RECEIVE."%')
						OR";
				}
				$sql .= "
						(
							FormNum like 'HMF-5-%'
							AND RT_SanctionState like '%$memberID%'
							AND SUBSTRING_INDEX(RT_SanctionState, ':', 1) > 3
							AND RT_SanctionState not like '%RECEIVE%'
						)
					order by RG_Date, Detail2;
				";
			
				//echo "<div style='display:none'>".$sql."</div>";
				//echo $sql;
				$re = mysql_query($sql,$db);
				if(mysql_num_rows($re) > 0 or $FormList != ""){
					$this->smarty->assign('account_approval',"account_approval");
				}
			//전표 접수 및 결재자 체크 종료
			/*
				echo $sql;
				echo $sql2;
			*/


			if($tab_index<>"1")
			{
				$this->smarty->assign('query_data',$query_data);
				$this->smarty->assign('Start',$Start);
				$this->smarty->assign('TotalRow',$TotalRow);
				$this->smarty->assign('last_start',$last_start);
				$this->smarty->assign('last_page',$last_page);
				$this->smarty->assign('currentPage',$currentPage);
			}

			$this->smarty->assign('sdate',$sdate);
			$this->smarty->assign('edate',$edate);
			$this->smarty->assign('DOC_STATUS_CREATE',$DOC_STATUS_CREATE);
			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign("ComKind",$ComKind);
			$this->smarty->assign('tab_Titel',$tab_Titel);
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('title_1',$title_1);
			$this->smarty->assign('title_2',$title_2);
			$this->smarty->assign('title_3',$title_3);
			$this->smarty->assign('doc_state',$doc_state);
			$this->smarty->assign('StateArray',$StateArray);
			$this->smarty->assign('staff',$staff);
			$this->smarty->assign('staff02',$staff02);
			$this->smarty->assign('Command',$Command);
			$this->smarty->assign('CommandDo',$CommandDo);
			$this->smarty->assign('PROCESS_TEMPORARY',$PROCESS_TEMPORARY);
			$this->smarty->assign('PROCESS_RETURN',$PROCESS_RETURN);
			$this->smarty->assign('PROCESS_DELETE',$PROCESS_DELETE);
			$this->smarty->assign('PROCESS_CANCEL',$PROCESS_CANCEL);


			if($mobile=="y")
			{
				if($tab_index=="1") //결재서류목록
				{
					$go_url="intranet/common_contents/work_approval/approval_list_mobile_mvc.tpl";
				}
				else
				{
					$go_url="intranet/common_contents/work_approval/approval_contents_mobile_mvc.tpl";
				}
			}

			if( $mobile_view == 'data' ){
				echo json_encode($query_data);
			}else{
				$this->smarty->display($go_url);
			}


		}


		//============================================================================
		// 전자결재 문서별 리스트
		//============================================================================
		function MyListView()
		{
			include "../inc/approval_function.php";
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page;
			global $FormNum,$Category,$tab_index,$CompanyKind;



			$page=15;
			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();

			if($FormNum=="")
				$FormNum = $tab_index;

			//기존인트라넷  회사구분없이 올린경우 예외처리
			if($FormNum=="HMF-4-8" || $FormNum=="BRF-4-8")
				$FormNumIn="'HMF-4-8','BRF-4-8'";
			else if($FormNum=="HMF-4-7" || $FormNum=="BRF-4-7")
				$FormNumIn="'HMF-4-7','BRF-4-7'";
			else if($FormNum=="HMF-9-1" || $FormNum=="BRF-9-1")
				$FormNumIn="'HMF-9-1','BRF-9-1'";
			else if($FormNum=="HMF-2-4" || $FormNum=="BRF-2-4")
				$FormNumIn="'HMF-2-4','BRF-2-4'";
			else if($FormNum=="HMF-9-2" || $FormNum=="BRF-9-2")
				$FormNumIn="'HMF-9-2','BRF-9-2'";
			else if($FormNum=="HMF-4-5" || $FormNum=="BRF-4-5")
				$FormNumIn="'HMF-4-5','BRF-4-8'";
			else if($FormNum=="HMF-2-3" || $FormNum=="BRF-2-3")
				$FormNumIn="'HMF-2-3','BRF-2-3'";
			else if($FormNum=="HMF-9-3" || $FormNum=="BRF-9-3")
				$FormNumIn="'HMF-9-3','BRF-9-3'";
			else if($FormNum=="HMF-9-4" || $FormNum=="BRF-9-4")
				$FormNumIn="'HMF-9-4','BRF-9-4'";
			else if($FormNum=="HMF-9-6" || $FormNum=="BRF-9-6")
				$FormNumIn="'HMF-9-6','BRF-9-6'";
			else if($FormNum=="HMF-9-7" || $FormNum=="BRF-9-7")
				$FormNumIn="'HMF-9-7','BRF-9-7'";
			else if($FormNum=="HMF-9-2-s" || $FormNum=="BRF-9-2-s")
				$FormNumIn="'HMF-9-2-s','BRF-9-2-s'";
			else if($FormNum=="HMF-4-5-s" || $FormNum=="BRF-4-5-s")
				$FormNumIn="'HMF-4-5-s','BRF-4-5-s'";
			else if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1")
				$FormNumIn="'HMF-6-1','BRF-6-1'";
			else if($FormNum=="HMF-6-2" || $FormNum=="BRF-6-2")
				$FormNumIn="'HMF-6-2','BRF-6-2'";
			else if($FormNum=="HMF-10-1" || $FormNum=="HMF-10-2")
				$FormNumIn="'HMF-10-1','HMF-10-2'";

			if(strpos($FormNum, "HMF-5-") !== false){

					if($selt == ""){
						$selt = "%";
					}

					$pre_sql = "select GroupCode, Certificate from member_tbl where MemberNo = '$memberID'";
					
					$result = mysql_query($pre_sql,$db);
					$row = mysql_fetch_array($result);
					$GroupCode = $row[GroupCode];
					$Certificate = $row[Certificate];

					$sql="select * from (";
					if(strpos($Certificate, "전표A") !== false){
						//상위 부서코드로 부서들 검색
						$sql2="select group_code from top_grouping_tbl where topgroup_code = (select topgroup_code from top_grouping_tbl where group_code='$GroupCode' limit 1)";
						//echo $sql2;
						$re = mysql_query($sql2,$db);
						$re_row = mysql_num_rows($re);//총 개수 저장
						while($re_row = mysql_fetch_array($re)){
							$GroupList=$GroupList."'".$re_row[group_code]."',";
						}
						$GroupList=substr($GroupList,0,strlen($GroupList)-1);

						$sql.="	select DocSN, Detail2, RG_Code, FormNum, DocTitle, RT_SanctionState, Addfile, MemberNo, Detail5 from SanctionDoc_tbl where RG_Code in ($GroupList) and FormNum like 'HMF-5-%' ORDER BY RG_Date desc, Detail2 desc";
					}else{
						$sql.="	select DocSN, Detail2, RG_Code, FormNum, DocTitle, RT_SanctionState, Addfile, MemberNo, Detail5 from SanctionDoc_tbl where MemberNo='$memberID' and FormNum like 'HMF-5-%' ORDER BY RG_Date desc, Detail2 desc";
					}
					$sql.="	) a left join
							( select Name, Code from systemconfig_tbl where SysKey='bizform')b
							on a.FormNum=b.Code";

					$sql.="	where
								FormNum like '$selt'
								and (
									DocTitle like '%$searchv%'
									or Detail2 like '%$searchv%'";
					$sql.="		)";
					$this->smarty->assign("selt",$selt);
					$this->smarty->assign("searchv",$searchv);
			}elseif($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1"){  //발신공문
				global $searchv,$selt,$group_code,$sdate,$edate;

				$pre_sql = "select GroupCode from member_tbl where MemberNo = '$memberID'";
				$result = mysql_query($pre_sql,$db);
				$row = mysql_fetch_array($result);
				$G_Code = $row[GroupCode];
				$G_Code = sprintf("%02d",$G_Code);

				if($edate == ""){
					$edate = date("Y-m-d", mktime(0,0,0, date("m"), date("d"), date("Y")));
					$sdate = date("Y-m-d", mktime(0,0,0, date("m")-1, date("d"), date("Y")));
				}

				$sql="select * from (
					select * from SanctionDoc_tbl where FormNum='$FormNum' and not DocSN like '%TEMP%' and RG_Date >= date('$sdate') and RG_Date <= date('$edate')";


				//고현진,정미희 모든문서 표시
				if($memberID != 'B16304' && $memberID != 'T03225' && $memberID != 'B16308' && $memberID != 'M20329' && $memberID != 'TADMIN'){
					$sql = $sql." and (RG_Code like '".$G_Code."' or RT_sanction like '%$memberID%')";
				}
				
				//검색
				if($searchv <> ""){	//단어검색
					if($selt=="제목"){
						$sql=$sql." and DocTitle like '%".$searchv."%'";
					}else if($selt=="문서번호"){
						$sql=$sql." and Detail4 like '%".$searchv."%'";
					}else if($selt == "내용"){
						$sql=$sql." and Detail1 like '%".$searchv."%' ";
					}else if($selt == "발행일자"){
						$sql=$sql." and Detail2 like '%".$searchv."%'";
					}else if($selt == "수신처"){
						$sql=$sql." and Detail3 like '%".$searchv."%'";
					}
				}
				if($group_code <> ""){	//부서선택
					$sql=$sql." and RG_Code like '".$group_code."'";
				}


				//$sql=$sql." order by Detail4 desc,RG_Date desc

				//고현진,정미희 모두 볼수 있게

				if($memberID != 'B16304' && $memberID != 'T03225' ){
					$sql=$sql." order by RG_Date desc,Detail4
						) a left join
						( select * from systemconfig_tbl where SysKey='bizform')b
						on a.FormNum=b.Code";
				}else
				{
						$sql=$sql." order by RG_Date desc,Detail4
						) a left join
						( select * from systemconfig_tbl where SysKey='bizform')b
						on a.FormNum=b.Code";
				}

				$this->smarty->assign("sdate",$sdate);
				$this->smarty->assign("edate",$edate);
				$this->smarty->assign("group_code",$group_code);
				$this->smarty->assign("selt",$selt);
				$this->smarty->assign("searchv",$searchv);
				

				//echo $sql."<br>";
			}else if($FormNum=="HMF-6-2" || $FormNum=="BRF-6-2"){  //수신공문
				global $searchv,$selt,$group_code,$sdate,$edate,$sub_group_code,$send_group,$open_check;

				$pre_sql = "select GroupCode from member_tbl where MemberNo = '$memberID'";
				$result = mysql_query($pre_sql,$db);
				$row = mysql_fetch_array($result);
				$G_Code = $row[GroupCode];
				$G_Code = sprintf("%02d",$G_Code);


				//부서가 건설사업관리본부의 행정및기술지원팀이거나 PQ작성및수주팀 일경우 수신공문 공유
				/*
				if($G_Code == '101' or $G_Code == '104'){
					$G_Code = "101','104";
				}
				*/

				if($edate == ""){
					$edate = date("Y-m-d", mktime(0,0,0, date("m"), date("d"), date("Y")));
					$sdate = date("Y-m-d", mktime(0,0,0, date("m"), date("d")-7, date("Y")));
				}


				$sql="select * from SanctionDoc_tbl where FormNum in ('HMF-6-2','BRF-6-2') and RG_Date >= date('$sdate') and RG_Date <= date('$edate')";

				/*
				if($memberID == '207123' or $memberID == '203266'){
					$sql = $sql." and (LENGTH(DocSN) = 17 or LENGTH(DocSN) = 22)";
				}
				*/

				//고현진,정미희 모든문서 표시
				if($memberID != 'B16304'  && $memberID != 'T03225' ){
					//$sql = $sql." and (RG_Code in ('".$G_Code."') or (PG_Code in ('".$G_Code."') and Detail5 = '1') or (PG_Code in ('".$G_Code."') and Detail5 = '0' and (RT_SanctionState like '%$memberID%')) )";

					//$sql = $sql." and ((RG_Code in ('".$G_Code."') and Detail5 = '1') or ( RT_sanction like '%$memberID%') or ( MemberNo ='$memberID') )";
					$sql = $sql." and ((RG_Code in ('".$G_Code."') and Detail5 = '1') or RG_Code in ('".$G_Code."') )";
				}

				//검색
				if($send_group <> ""){//발신기관
					$sql=$sql." and Detail4 like '%".$send_group."%'";
				}
				if($sub_group_code <> ""){//수신부서
					$sql=$sql." and Detail2 like '%/n".$sub_group_code.",'";
				}

				if($group_code <> ""){//주처리부서
					$sql=$sql." and Detail4 like '%/n".$group_code."/n'";
				}
				if($open_check <> ""){//공개여부
					$sql=$sql." and Detail5 like '".$open_check."'";
				}
				if($searchv <> ""){
					if($selt=="제목"){
						$sql=$sql." and DocTitle like '%".$searchv."%'";
					}else if($selt=="문서번호"){
						$sql=$sql." and ProjectCode like '%".$searchv."%'";
					}
				}

				$sql=$sql." order by ProjectCode desc,RG_Date desc";

				/*
				$sql=$sql." order by ProjectCode desc,RG_Date desc
					) a left join
					( select * from systemconfig_tbl where SysKey='bizform')b
					on a.FormNum=b.Code";
					*/

				//echo "HMF-6-2 = ".$sql;

				$this->smarty->assign("sdate",$sdate);
				$this->smarty->assign("edate",$edate);
				$this->smarty->assign("send_group",$send_group);
				$this->smarty->assign("sub_group_code",$sub_group_code);
				$this->smarty->assign("group_code",$group_code);
				$this->smarty->assign("open_check",$open_check);
				$this->smarty->assign('selt',$selt);
				$this->smarty->assign("searchv",$searchv);

			}else if( $FormNum=="HMF-10-1" ){  //외주품의서
				global $searchv,$selt,$sdate,$edate;
				if($edate == ""){
					$edate = date("Y-m-d", mktime(0,0,0, date("m"), date("d"), date("Y")));
					$sdate = date("Y-m-d", mktime(0,0,0, date("m"), date("d")-14, date("Y")));
				}
				$sql="
					select *, ( select Name from systemconfig_tbl where SysKey='bizform' and a.FormNum = code) as Name from SanctionDoc_tbl a where FormNum in( $FormNumIn ) and RG_Date >= date('$sdate') and RG_Date <= date('$edate')";
				if($searchv <> ""){
					if($selt=="제목"){
						$sql=$sql." and DocTitle like '%".$searchv."%'";
					}else if($selt=="문서번호"){
						$sql=$sql." and ProjectCode like '%".$searchv."%'";
					}
				}
				$sql=$sql." order by RG_Date desc";
				$this->smarty->assign("sdate",$sdate);
				$this->smarty->assign("edate",$edate);
				$this->smarty->assign("selt",$selt);
				$this->smarty->assign("searchv",$searchv);
			}else if( $FormNum=="HMF-9-2-s" || $FormNum=="BRF-9-2-s" ){  //연장근무신청서(개인)
					$sql="
						select * from (
							select
								*
								, '연장근무신청서(개인)' as Name
							from
								SanctionDoc_tbl
							where
								MemberNo='$memberID'
								and FormNum in ( 'HMF-9-2-s', 'BRF-9-2-s' )
							) A
							left join
							(
								select
									RT_SanctionState as head_state
									, SUBSTR(detail1, 1, 10) as set_date
								from SanctionDoc_tbl
								where FormNum in ( 'HMF-9-2', 'BRF-9-2' )
								and detail5 like '%$memberID%'
							) B
							on A.detail1 = B.set_date
						order by A.RG_Date desc
					";
			}else{
					$sql="select * from (
						select * from SanctionDoc_tbl where MemberNo='$memberID' and FormNum in($FormNumIn) order by RG_Date desc
						) a left join
						( select * from systemconfig_tbl where SysKey='bizform')b
						on a.FormNum=b.Code";
			}
			$sql2 =$sql." limit $Start, $page";

			//echo $sql2."<br>";

			if($memberID=="M07238"){
				 				//echo $sql2;
				// 				exit();
			}else{
			}


			$re = mysql_query($sql,$db);
			$TotalRow = @mysql_num_rows($re);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/10);
			$FormTitle=DocCode2Name($FormNum);

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = @mysql_fetch_array($re2))
			{
					$DocSN=$re_row2[DocSN]; //문서번호
					$FormNum=$re_row2[FormNum];

					$AttchFile=$re_row2[Addfile];
					$RG_Date=$re_row2[RG_Date];
					$MemberNo=$re_row2[MemberNo];
					$KorName = MemberNo2Name($MemberNo); //기안자 성명





					if($FormNum=="HMF-6-1" || $FormNum=="BRF-6-1")  //발신공문
					{
						$RG_Code=$re_row2[RG_Code];

						$tmpdata = split("/n",$re_row2[Detail2]);

						if($tmpdata[2]=="")
						{
							$re_row2[ViewDate]=$re_row2[PG_Date];
						}else
						{
							$re_row2[ViewDate]=$tmpdata[2];
						}

						$re_row2[DocTitle]=str_replace("\"","",$re_row2[DocTitle]);
						$re_row2[GroupName]=Code2Name($RG_Code,'GroupCode','');

						$Detail2=$re_row2[Detail2];
						$Dt2=split("/n",$Detail2);

						$re_row2[GroupSubName]=$Dt2[3];
						$re_row2[ReceiveName]=$Dt2[0];
						$re_row2[WriterName]=$KorName." ".$re_row2[MemberInfo];

					}

					if($FormNum=="HMF-6-2" || $FormNum=="BRF-6-2")  //수신공문
					{
						$temp_group = split("/n",$re_row2[Detail4]);
						$re_row2[submit_group] = $temp_group[1];

						$re_row2[ReceiveName]=$KorName;
						$re_row2[RGName]= Code2Name($re_row2[RG_Code],'GroupCode','0'); //기안자 부서


					}

					$RT_SanctionState=$re_row2[RT_SanctionState];  //결재상태
					$StateArray=split(":",$RT_SanctionState);


					//---------------------------------------------------------------------------------------------------
					$State="";
					$tab_index="0";


					if((strpos($RT_SanctionState,"부서내") !==false) or (strpos($RT_SanctionState,"RECEIVE") !==false)) {


						if((strpos($RT_SanctionState,"결의부서내:".$memberID) !==false) or (strpos($RT_SanctionState,"처리부서내:".$memberID) !==false))
						{
							$State="처리중";
							$tab_index="0";
						}else
						{
							if(strpos($RT_SanctionState,"임시저장") !==false)
							{
									$State="임시저장";
									$tab_index="7";
							}else
							{
									$State="처리중";
									$tab_index="4";
							}

						}

						/* 개인이 연장근무 올렸을 때
						 * 전자결재 연장근무신청서(개인) :HMF-9-2-s/BRF-9-2-s
						 * 목록 내 [진행상태] 표시 관련코드추가
						 * 20201019 moon
						 * : 코드추가사유 = 팀장이 연장근무(팀장)에 해당인원을 포함하지않았는데
						 * 해당인원의 결재 진행상태가 결재완료로 표시됨
						 * */
						if($FormNum=="HMF-9-2-s" || $FormNum=="BRF-9-2-s")  //연장근무 신청서 개인
						{
							if( strpos($re_row2[head_state],"부서내임시저장") !==false ){
								//팀장미상신
								//팀장이 해당일자의 연장근무신청서(팀장)에 해당인원을 포함 시키지 않음
								$State="팀장미상신";
							}elseif( strpos($RT_SanctionState,"RECEIVE") !==false and $re_row2[head_state] == '' ){
								//팀장이 결재할문서 삭제했을때
								$State="문서삭제됨";
							}
						}

					}else if(strpos($RT_SanctionState,"FINISH") !==false) {
							$State="결재완료";
							$tab_index="5";

							$now_day=date("Y-m-d h:i:s");

							if($RG_Date<"2020-10-20"){
								$State="결재완료";
								$tab_index="5";
							}



					}else if((strpos($RT_SanctionState,"REJECT") !==false) or (strpos($RT_SanctionState,"RETURN") !==false)) {
							$State="부결반송";
							$tab_index="6";
					}


					$value1 = "";
					$value2 = "";
					$value3 = "";



					if(substr($DocSN,0,5) == $PROCESS_TEMPORARY) {
						$doc_state = $PROCESS_TEMPORARY;

					} else {
						$doc_state = $PROCESS_APPROVE;
					}

					if($RG_Date<"2016-06-01")
					{
						$tmp_old=true;
					}else
					{
						$tmp_old=false;
					}

					switch ($tab_index ) {
						case 2: //미결함(결재할문서)
							if($StateArray[1] == $PROCESS_RECEIVE) {

								if($tmp_old)
								{
									$Command = "acceptdoc_old";   //접수하기
								}else
								{
									$Command = "acceptdoc";   //접수하기
								}


							} else {

								if($tmp_old)
								{
									$Command = "approvedoc_old";  //결재하기
								}else
								{
									$Command = "approvedoc";  //결재하기
								}


							}
							$value1 = $KorName;
							$value2 = $StateArray[3]; //받은날
							$value3 = "&nbsp;";

							break;

						case 3: //기결함(결재한문서)
								if($tmp_old)
								{
									$Command = "viewdoc_old";     //문서보기
								}else
								{
									$Command = "viewdoc";     //문서보기
								}



							$value1 = $KorName;
							$value2 = $SanctionDate;
							$value3=ProcessingState($DocSN, $StateArray[1], $StateArray[2]);
							break;

						case 4:case 7: //기안함
							if($doc_state == $PROCESS_TEMPORARY) {

								if($tmp_old)
								{
									$Command = "editdoc_old"; //편집하기 및 결재상신
								}else
								{
									$Command = "editdoc"; //편집하기 및 결재상신
								}


								$value2 ="임시파일";
							} else {
								$Command = "viewdoc"; //결재상신 후 보기만
								$value2 = ProcessingState($DocSN, $StateArray[1], substr($StateArray[2],0,6)).$FinishMemberNo; //임시저장/접수대기/부서결재1/처리부서1
							} //임시저장인 경우
							$value1 = $RG_Date;
							$value3 = "&nbsp;";
							break;

						case 5:	//완료함
							if($tmp_old)
							{
								$Command = "viewdoc_old";     //문서보기
							}else
							{
								$Command = "viewdoc";     //문서보기
							}

							$value1 = $RG_Date;
							$value2 = $StateArray[3]; //완료일
							$value3 = "&nbsp;";
							break;

						case 6:	//부결/반송함
							if($StateArray[1] == $PROCESS_RETURN) {
								if($tmp_old)
								{
									$Command = "editdoc_old"; // 반송인경우 재기안
								}else
								{
									$Command = "editdoc"; // 반송인경우 재기안
								}


							} else {
								if($tmp_old)
								{
									$Command = "viewdoc_old"; // 부결인경우 보기만
								}else
								{
									$Command = "viewdoc"; // 부결인경우 보기만
								}


							}
							$value1 =$RG_Date;  //기안일
							$value2 =$StateArray[3];  //반송일
							$value3 = "&nbsp;";
							break;
					}
					if( $FormNum=="HMF-10-1" ){
						$tab_index="0";
					}

					$CommandDo = $Command."('".$FormNum."','".$DocSN."','".$memberID."');";

					// 					if($CommandDo==""){
					// 						echo "리스트 생성중 오류발생(관리자에게 문의하세요)";
					// 						exit();
					// 					}


					if($memberID=="B18214"){
						// 				echo $go_url;
						// 				exit();
						// 						echo $CommandDo;
						// 						echo $CommandDo;
						// 						exit();

					}else{
					}



					if(strpos($FormNum, "HMF-5-") !== false){
						$CommandDo = "viewdoc('".$FormNum."','".$DocSN."','".$memberID."');";
					}
					$re_row2[value1]=$value1;
					$re_row2[value2]=$value2;
					$re_row2[value3]=$value3;
					$re_row2[State]=$State;
					$re_row2[tab_index]=$tab_index;
					$re_row2[Command]=$Command;
					$re_row2[CommandDo]=$CommandDo;



					if(strpos($FormNum, "HMF-5-") !== false){
						$re_row2[Doc_Code] = $re_row2[Detail2];
						$Addfile=$re_row2[Addfile];
						$Addfile_arr=split("/n",$Addfile);
						$re_row2[Addfile] = $Addfile_arr[0];
					}



					if(strpos($FormNum, "HMF-6-") !== false){
						$Addfile=$re_row2[Addfile];
						$Addfile_arr=split("/n",$Addfile);
						if(count($Addfile_arr) >1)
						{
							for($i=0; $i<count($Addfile_arr); $i++) {
								if($Addfile_arr[$i] <> "")
								{
									$re_row2[Addfile_arr][$i] = $Addfile_arr[$i];

								}
							}
						}
					}



					array_push($query_data,$re_row2);

			}


			if($currentPage == "") $currentPage = 1;
			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			$tab_index=$FormNum ;

			$this->smarty->assign("page_action","approval_controller.php");
			$this->smarty->assign("CompanyKind",$CompanyKind);
			$this->smarty->assign("FormTitle",$FormTitle);
			$this->smarty->assign("Category",$Category);
			$this->smarty->assign("tab_index",$tab_index);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);
			$this->smarty->assign('PROCESS_TEMPORARY',$PROCESS_TEMPORARY);
			$this->smarty->assign('PROCESS_RETURN',$PROCESS_RETURN);
			$this->smarty->assign('PROCESS_DELETE',$PROCESS_DELETE);
			$this->smarty->assign('PROCESS_CANCEL',$PROCESS_CANCEL);







			$this->smarty->display("intranet/common_contents/work_approval/approval_mycontents_mvc.tpl");
		}




		//============================================================================
		// 전자결재 ADMIN 결재리스트 (경영지원부용)
		//============================================================================
		function AdminListView()
		{
			include "../inc/approval_function.php";
			global $db,$memberID;
			global $auth,$tab_index,$sub_index,$searchv,$Category;
			global $Start,$page,$currentPage,$last_page;
			global $sel_group,$sel_doc;

			$page=15;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();



			//부서
			$group_data = array();
			$doc_data = array();

			$sql_g="select * from systemconfig_tbl where SysKey = 'GroupCode' order by Code";
			$re_g = mysql_query($sql_g,$db);
			while($re_row_g = mysql_fetch_array($re_g))
			{
				array_push($group_data,$re_row_g);
			}
			$this->smarty->assign('group_data',$group_data);

			//문서
			$sql_d = "select * from systemconfig_tbl where SysKey='bizform' and Note in('hanmac','tbaron') order by Note,Code asc";
			$re_d = mysql_query($sql_d,$db);
			while($re_row_d = mysql_fetch_array($re_d))
			{
				if($re_row_d[Note] =="hanmac"){
					$re_row_d[DocName]="[한맥]&nbsp;".$re_row_d[Name];
				}else if($re_row_d[Note] =="tbaron"){
					$re_row_d[DocName]="[바론]&nbsp;".$re_row_d[Name];
				}

				array_push($doc_data,$re_row_d);
			}
			$this->smarty->assign('doc_data',$doc_data);

			//====================================================================================
			//$sql = "SELECT * FROM SANCTIONDOC_TBL WHERE DocSN <>'' ";
			$sql = "SELECT * FROM SANCTIONDOC_TBL WHERE DocSN <>'' and RT_SanctionState like '%:FINISH%'";
			//$sql = "select * from SanctionDoc_tbl where FinishMemberNo like '%".$memberID."%'";

			if($sel_doc <> "ALL"){
				$sql .= "AND FormNum='$sel_doc'";
			}
			if($sel_group <> "ALL"){
				$sql .= "AND RG_Code='$sel_group'";
			}
			$sql .= "order by RG_Date desc";
			//====================================================================================
			$sql2 =$sql." limit $Start, $page";
			//====================================================================================

				//echo "------".$sql."<br>";
				//echo "------".$sql2."<br>";

				$re = mysql_query($sql,$db);
				$TotalRow = mysql_num_rows($re);//총 개수 저장

				$last_start = ceil($TotalRow/10)*10+1;;
				$last_page=ceil($TotalRow/10);

				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2))
				{

						$DocSN=$re_row2[DocSN];
						$FormNum=$re_row2[FormNum];
						$FormName = Code2Name($FormNum, 'bizform', 0); //양식명
						$MemberNo=$re_row2[MemberNo];
						$KorName = MemberNo2Name($MemberNo); //기안자 성명

						$RG_Code=$re_row2[RG_Code];
						$GroupName=Code2Name($RG_Code,'GroupCode','0');
						$re_row2[FormName]=$FormName;
						$re_row2[GroupName]=$GroupName;
						$re_row2[KorName]=$KorName;

						$CommandDo = "viewdoc('".$FormNum."','".$DocSN."','".$memberID."');";
						$re_row2[CommandDo]=$CommandDo;
						array_push($query_data,$re_row2);

				}


				if($currentPage == "") $currentPage = 1;
				$PageHandler =new PageControl($this->smarty);
				$PageHandler->SetMaxRow($TotalRow);
				$PageHandler->SetCurrentPage($currentPage);
				$PageHandler->PutTamplate();



				$this->smarty->assign('query_data',$query_data);
				$this->smarty->assign('Start',$Start);
				$this->smarty->assign('TotalRow',$TotalRow);
				$this->smarty->assign('last_start',$last_start);
				$this->smarty->assign('last_page',$last_page);
				$this->smarty->assign('currentPage',$currentPage);
				$this->smarty->assign('sel_group',$sel_group);
				$this->smarty->assign('sel_doc',$sel_doc);

				$this->smarty->assign('searchv',$searchv);
				$this->smarty->assign('Category',$Category);
				$this->smarty->assign('tab_index',$tab_index);
				$this->smarty->assign('sub_index',$sub_index);

				$this->smarty->assign('memberID',$memberID);

				$this->smarty->assign("page_action","approval_controller.php?ActionMode=Admin");
				$this->smarty->display("intranet/common_contents/work_approval/approval_adminlist_mvc.tpl");

		}

		//============================================================================
		// 전자결재 ADMIN 결재리스트 (경영지원부용)
		//============================================================================
		function Admin(){
			extract($_REQUEST);
			switch($MainAction){
				case "HTML_Page_01": 	$this->smarty->display("intranet/common_contents/work_approval/approval_admin_grid.tpl");	break;
				case "HTML_Ajax_01": 	$this->Admin_Ajax_01();	break;
				case "Main";
				default:
					global $db;
					//부서
					$group_data = array();
					$doc_data = array();

					$sql_g="select * from systemconfig_tbl where SysKey = 'GroupCode' order by Code";
					$re_g = mysql_query($sql_g,$db);
					while($re_row_g = mysql_fetch_array($re_g))
					{
						array_push($group_data,$re_row_g);
					}
					$this->smarty->assign('group_data',$group_data);

					//문서
					$sql_d = "select * from systemconfig_tbl where SysKey='bizform' and Note in('hanmac','tbaron') order by Note,Code asc";
					$re_d = mysql_query($sql_d,$db);
					while($re_row_d = mysql_fetch_array($re_d))
					{
						if($re_row_d[Note] =="hanmac"){
							$re_row_d[DocName]="[한맥]&nbsp;".$re_row_d[Name];
						}else if($re_row_d[Note] =="tbaron"){
							$re_row_d[DocName]="[바론]&nbsp;".$re_row_d[Name];
						}

						array_push($doc_data,$re_row_d);
					}
					$this->smarty->assign('doc_data',$doc_data);
					$this->smarty->assign('s_date',date("Y-m-d", strtotime("-1 day", time())));
					$this->smarty->assign('e_date',date("Y-m-d"));
					$this->smarty->display("intranet/common_contents/work_approval/approval_admin_main_mvc.tpl");
					break;
			}
		}

		function Admin_Ajax_01(){
			include "../inc/approval_function.php";
			extract($_REQUEST);
			global $db;
			if( $FormNum == 'ALL' ){
				$FormNum = '%';
			}
			if( $RG_Code == 'ALL' ){
				$RG_Code = '%';
			}

			$sql = "
				SELECT
					B.Name AS FormName
					, A.DocTitle
					, C.Name AS RG_Name
					, ( select korName from member_tbl where MemberNo = A.MemberNo )AS korname
					, A.RG_Date
					, A.PG_Date
					, A.Addfile
					, A.DocSN
					, A.FormNum
				FROM
					sanctiondoc_tbl A
					, ( select Code, Name from systemconfig_tbl where SysKey = 'bizform' ) B
					, ( select Code, Name from systemconfig_tbl where SysKey = 'GroupCode' ) C
				WHERE
					DocSN <> ''
					and RT_SanctionState like '%:FINISH%'
					and RG_Date between '$s_date' and '$e_date'
					and RG_Code like '$RG_Code'
					and FormNum like '$FormNum'
					and A.FormNum = B.Code
					and A.RG_Code = C.Code
			";
			if( $search_val <> '' ){
				$sql .= "
					and (
						DocTitle like '%$search_val%'
						or MemberNo in (
							select MemberNo from member_tbl where korName like '%$search_val%'
						)
					)
				";
			}
			$sql .= "
				ORDER BY
					A.docsn desc
			";
			//echo $sql;

			$re = mysql_query($sql,$db);
			$i = 1;
			$query_data = array();

			while($re_row2 = mysql_fetch_array($re)){
				$re_row2[recid] = $i;
				array_push($query_data,$re_row2);
				$i++;
			}

			echo json_encode($query_data);
		}

		function baby_vacation(){
			extract($_REQUEST);
			switch($MainAction){
				case "HTML_Page_01": 	$this->smarty->display("intranet/common_contents/work_approval/approval_baby_grid.tpl");	break;
				case "HTML_Ajax_01": 	$this->baby_vacation_Ajax_01();	break;
				case "Main";
				default:
					$this->smarty->display("intranet/common_contents/work_approval/approval_baby_mvc.tpl");
					break;
			}
		}

		function baby_vacation_Ajax_01(){
			include "../inc/approval_function.php";
			extract($_REQUEST);
			global $db;

			$sql = "SELECT * FROM sanctiondoc_tbl WHERE FormNum in ( 'HMF-4-7', 'BRF-4-7' ) and DocTitle LIKE '%출산휴가%' ORDER BY docsn desc";
			//echo $sql;

			$re = mysql_query($sql,$db);
			$i = 1;
			$query_data = array();

			while($re_row2 = mysql_fetch_array($re)){
				$re_row2[recid] = $i;
				$re_row2[FormName] = Code2Name($re_row2[FormNum], 'bizform', 0);
				$re_row2[korname] = MemberNo2Name($re_row2[MemberNo]);
				$temp_date = explode('/n',$re_row2[Detail1]);
				$re_row2[start_date] = $temp_date[0];
				$re_row2[end_date] = $temp_date[1];
				array_push($query_data,$re_row2);
				$i++;
			}

			echo json_encode($query_data);
		}
}
//END============================================================================
?>