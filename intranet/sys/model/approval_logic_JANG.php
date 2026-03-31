<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	
	/***************************************
	* 전자결재 리스트
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH 
	****************************************/ 
	
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";

	$now_day=date("Y-m-d h:i:s");

	extract($_GET);
	class ApprovalLogic {
		var $smarty;
		function ApprovalLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//============================================================================
		// 전자결재 Delete
		//============================================================================
		function DeleteAction()
		{
			include "../inc/approval_function.php";
			global $db,$memberID;
			global $auth,$menu_cmd,$sub_index,$docno;
			global $Start,$page,$currentPage,$last_page,$kind,$Category,$FormNum;

				switch ($menu_cmd) {
				case $PROCESS_CANCEL:  //기안취소 (임시저장으로 변경)

					$TmpDocSN=TempSerialNo2($memberID);
					$RT_SanctionState=TempState($docno);  //사번으로 입력
					$azSQL = "update SanctionDoc_tbl set DocSN='$TmpDocSN', RT_SanctionState='$RT_SanctionState', FinishMemberNo='', PG_Date='' where DocSN='$docno'";
					$result=mysql_query($azSQL,$db);

					break;
				case $PROCESS_DELETE:  //문서삭제

					//첨부파일 삭제 추가 필요
					$query03 = "select * from SanctionDoc_tbl where DocSN='$docno'";
					$result03 = mysql_query($query03,$db);
					$Addfile = mysql_result($result03,0,"Addfile");
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

					break;
				}

				$azSQL = "delete from SanctionState_tbl where DocSN='$docno'";
				$result=mysql_query($azSQL,$db);

				mysql_close($db);


		
				if($Category=="MyListView")
				{
					$this->smarty->assign('MoveURL',"approval_controller.php?Category=MyListView&FormNum=$FormNum");
				}else
				{
					$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=$kind");
				}
				$this->smarty->display("intranet/move_page.tpl");

		}
		
			
		//============================================================================
		// 전자결재 결재상황별 리스트
		//============================================================================
		function View()
		{
			include "../inc/approval_function.php";
			global $db,$memberID;
			global $auth,$tab_index,$sub_index,$doc_state;
			global $Start,$page,$currentPage,$last_page;

			/*
				tab_index=1 결재서류목록
				tab_index=2 결재할문서
				tab_index=3 결재한문서
				tab_index=4 기안한문서
				tab_index=5 결재완료된문서
				tab_index=6 부결,반송된문서
			*/

			$page=15;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);
			
			$query_data = array(); 
			
			if($tab_index=="") $tab_index=1;

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

				while($re_row = mysql_fetch_array($re))
				{
					$FormList=$FormList."'".$re_row[FormName]."',";
				}

				if($FormList == "")  //일반사용자
				{
					
					$sql = "select * from SanctionDoc_tbl where RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%' order by RG_Date desc";
					$sql2 =$sql." limit $Start, $page";					
				}
				else  //처리부서
				{
					
					$FormList=substr($FormList,0,strlen($FormList)-1);
					$sql = "select * from SanctionDoc_tbl where (RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%') or (PG_Code='".$_SESSION['MyGroupCode']."' and RT_SanctionState like '%".$PROCESS_RECEIVE."%' and FormNum in ($FormList)) order by RG_Date desc";
					$sql2 =$sql." limit $Start, $page";
				}
				


			}
			else if($tab_index=="3") //결재한문서
			{
				$title_1="기안자";
				$title_2="결재일";
				$title_3="진행사항";
				
				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";

				$sql = "select * from SanctionDoc_tbl where FinishMemberNo like '%".$memberID."%' order by RG_Date desc";	
				$sql2 =$sql." limit $Start, $page";

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

				$sql = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and RT_SanctionState like '%".$PROCESS_FINISH."%' order by RG_Date desc";
				$sql2 =$sql." limit $Start, $page";

			}else if($tab_index=="6") //부결,반송된문서
			{
				$go_url="intranet/common_contents/work_approval/approval_contents_mvc.tpl";
				
				$title_1="기안일";
				$title_2="반송일";
				$title_3="재기안";

				$sql = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$PROCESS_REJECTION."%' or RT_SanctionState like '%".$PROCESS_RETURN."%') order by RG_Date desc";		
				$sql2 =$sql." limit $Start, $page";

			}
		
			
			if($tab_index<>"1")
			{
				$re = mysql_query($sql,$db);
				$TotalRow = mysql_num_rows($re);//총 개수 저장
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
						
						switch ($tab_index ) {
							case 2: //미결함(결재할문서)
								if($StateArray[1] == $PROCESS_RECEIVE) {
									$Command = "acceptdoc";   //접수하기
								} else {
									$Command = "approvedoc";  //결재하기
								}
								$value1 = $KorName;
								$value2 = $StateArray[3]; //받은날
								$value3 = "&nbsp;";
								break;

							case 3: //기결함(결재한문서)
								$Command = "viewdoc";     //문서보기
								$value1 = $KorName;
								$value2 = $SanctionDate;
								$value3=ProcessingState($DocSN, $StateArray[1], $StateArray[2]);
								break;

							case 4: //기안함
					
								if($doc_state == $PROCESS_TEMPORARY) {
									$Command = "editdoc"; //편집하기 및 결재상신
									$value2 = "임시파일";
								} else {
									$Command = "viewdoc"; //결재상신 후 보기만
									$value2 = ProcessingState($DocSN, $StateArray[1], substr($StateArray[2],0,6)).$FinishMemberNo; //임시저장/접수대기/부서결재1/처리부서1
								} //임시저장인 경우
								$value1 = $RG_Date;
								$value3 = "&nbsp;";
								
								break;

							case 5:	//완료함
								$Command = "viewdoc";     //문서보기
								$value1 = $RG_Date;    
								$value2 = $StateArray[3]; //완료일
								$value3 = "&nbsp;";
								break;

							case 6:	//부결/반송함
								if($StateArray[1] == $PROCESS_RETURN) {
									$Command = "editdoc"; // 반송인경우 재기안
								} else {
									$Command = "viewdoc"; // 부결인경우 보기만
								}

								$value1 =$RG_Date;
								$value2 =$StateArray[3];  //반송일
								$value3 = "&nbsp;";
								break;
						}

						$CommandDo = $Command."('".$FormNum."','".$DocSN."','".$memberID."');";

				
						$re_row2[FormName]=$FormName;
						$re_row2[value1]=$value1;
						$re_row2[value2]=$value2;
						$re_row2[value3]=$value3;
						$re_row2[State]=$State;
						
						$re_row2[Command]=$Command;
						$re_row2[CommandDo]=$CommandDo;
						array_push($query_data,$re_row2);
				
				}
						
				
				if($currentPage == "") $currentPage = 1; 
				$PageHandler =new PageControl($this->smarty);
				$PageHandler->SetMaxRow($TotalRow);
				$PageHandler->SetCurrentPage($currentPage);
				$PageHandler->PutTamplate();
			}

		
			$tab_Titel = array('결재서류작성','결재할 문서','결재한 문서','기안한 문서','결재완료된 문서','부결,반송된 문서');
			$tab_value = array('1','2','3','4','5','6');

			$this->smarty->assign("page_action","approval_controller.php");
		
			if($tab_index<>"1")
			{
				$this->smarty->assign('query_data',$query_data);
				$this->smarty->assign('Start',$Start);
				$this->smarty->assign('TotalRow',$TotalRow);
				$this->smarty->assign('last_start',$last_start);
				$this->smarty->assign('last_page',$last_page);
				$this->smarty->assign('currentPage',$currentPage);
			}
			
			$this->smarty->assign('DOC_STATUS_CREATE',$DOC_STATUS_CREATE);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_Titel',$tab_Titel);
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('title_1',$title_1);
			$this->smarty->assign('title_2',$title_2);
			$this->smarty->assign('title_3',$title_3);
			$this->smarty->assign('doc_state',$doc_state);
			$this->smarty->assign('StateArray',$StateArray);
			$this->smarty->assign('Command',$Command);
			$this->smarty->assign('CommandDo',$CommandDo);
			$this->smarty->assign('PROCESS_TEMPORARY',$PROCESS_TEMPORARY);
			$this->smarty->assign('PROCESS_RETURN',$PROCESS_RETURN);
			$this->smarty->assign('PROCESS_DELETE',$PROCESS_DELETE);
			$this->smarty->assign('PROCESS_CANCEL',$PROCESS_CANCEL);

			$this->smarty->display($go_url);
			

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
			global $FormNum,$Category,$tab_index;
		


			$page=15;
			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array(); 
			
			if($FormNum=="")
				$FormNum = $tab_index;

			$sql="select * from (
				select * from SanctionDoc_tbl where MemberNo='$memberID' and FormNum ='$FormNum' order by RG_Date desc
				) a left join
				( select * from systemconfig_tbl where SysKey='bizform')b
				on a.FormNum=b.Code";

			$sql2 =$sql." limit $Start, $page";

			

			$re = mysql_query($sql,$db);
			$TotalRow = mysql_num_rows($re);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/10);
			$FormTitle=DocCode2Name($FormNum);

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
					$DocSN=$re_row2[DocSN]; //문서번호
					$FormNum=$re_row2[FormNum];

					$AttchFile=$re_row2[Addfile];
					$RG_Date=$re_row2[RG_Date];
					$MemberNo=$re_row2[MemberNo];
					$KorName = MemberNo2Name($MemberNo); //기안자 성명

					
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
					}else if(strpos($RT_SanctionState,"FINISH") !==false) {
							$State="결재완료";
							$tab_index="5";
					
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

					switch ($tab_index ) {
						case 2: //미결함(결재할문서)
							if($StateArray[1] == $PROCESS_RECEIVE) {
								$Command = "acceptdoc";   //접수하기
							} else {
								$Command = "approvedoc";  //결재하기
							}
							$value1 = $KorName;
							$value2 = $StateArray[3]; //받은날
							$value3 = "&nbsp;";
							break;

						case 3: //기결함(결재한문서)
							$Command = "viewdoc";     //문서보기
							$value1 = $KorName;
							$value2 = $SanctionDate;
							$value3=ProcessingState($DocSN, $StateArray[1], $StateArray[2]);
							break;

						case 4:case 7: //기안함
							if($doc_state == $PROCESS_TEMPORARY) {
								$Command = "editdoc"; //편집하기 및 결재상신
								$value2 ="임시파일";
							} else {
								$Command = "viewdoc"; //결재상신 후 보기만
								$value2 = ProcessingState($DocSN, $StateArray[1], substr($StateArray[2],0,6)).$FinishMemberNo; //임시저장/접수대기/부서결재1/처리부서1
							} //임시저장인 경우
							$value1 = $RG_Date;
							$value3 = "&nbsp;";							
							break;

						case 5:	//완료함
							$Command = "viewdoc";     //문서보기
							$value1 = $RG_Date;    
							$value2 = $StateArray[3]; //완료일
							$value3 = "&nbsp;";
							break;

						case 6:	//부결/반송함
							if($StateArray[1] == $PROCESS_RETURN) {
								$Command = "editdoc"; // 반송인경우 재기안
							} else {
								$Command = "viewdoc"; // 부결인경우 보기만
							}
							$value1 =$RG_Date;  //기안일
							$value2 =$StateArray[3];  //반송일
							$value3 = "&nbsp;";
							break;
					}
					$CommandDo = $Command."('".$FormNum."','".$DocSN."','".$memberID."');";
						
					$re_row2[value1]=$value1;
					$re_row2[value2]=$value2;
					$re_row2[value3]=$value3;
					$re_row2[State]=$State;
					$re_row2[tab_index]=$tab_index;
					$re_row2[Command]=$Command;
					$re_row2[CommandDo]=$CommandDo;

					array_push($query_data,$re_row2);
			
			}
					
			
			if($currentPage == "") $currentPage = 1; 
			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			$tab_index=$FormNum ;
			

			$this->smarty->assign("page_action","approval_controller.php");
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

}



