<?php

	/***************************************
	* 전자결재 리스트
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/approval_function2.php";

	$now_day=date("Y-m-d h:i:s");

	extract($_GET);

	$memberID	=	$_REQUEST['memberID'];
	$WorkPosition = getWorkPositionByMemberNo($memberID); //워크포지션(WorkPosition)

	class ApprovalAdminLogic {
		var $smarty;
		function ApprovalAdminLogic($smarty)
		{
			$this->smarty=$smarty;
		}



		//============================================================================
		// 전자결재 ADMIN 결재리스트 (경영지원부용)
		//============================================================================
		function AdminListView(){
			extract($_REQUEST);
			if($excel){
				$this->PrintExcelHeader($sel_doc."_");
			}

			include "../inc/approval_var.php";

			global $db,$memberID;
			global $auth,$tab_index,$sub_index,$searchv,$Category;
			global $Start,$page,$currentPage,$last_page;
			global $sel_group,$sel_doc,$report_kind,$excel;
			global $sdate,$edate;

			$page=15;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();

			$report_kind=1;

			if($sel_group=="")
				$sel_group="ALL";
			if($sel_doc=="")
				$sel_doc="ALL";


			if($edate == ""){
					$edate = date("Y-m-d", mktime(0,0,0, date("m"), date("d"), date("Y")));
					$sdate = date("Y-m-d", mktime(0,0,0, date("m"), date("d")-7, date("Y")));
			}
			$this->smarty->assign("sdate",$sdate);
			$this->smarty->assign("edate",$edate);
			//부서
			$group_data = array();
			$doc_data = array();

			$sql_g="select * from systemconfig_tbl where SysKey = 'GroupCode' order by orderno";
			//echo $sql_g."<Br>";
			$re_g = mysql_query($sql_g,$db);
			while($re_row_g = mysql_fetch_array($re_g))
			{
				array_push($group_data,$re_row_g);
			}
			$this->smarty->assign('group_data',$group_data);

			//문서
			$sql_d = "select * from systemconfig_tbl where SysKey='bizform' and code NOT IN('HMF-6-2','BRF-6-2') order by Note,Code asc";
			$re_d = mysql_query($sql_d,$db);
			while($re_row_d = mysql_fetch_array($re_d))
			{
				if($re_row_d[Name] != '자금일보'){
					if($re_row_d[Code] != 'HM-NOTICE'){
						$re_row_d[Code] = substr($re_row_d[Code] , 3, 6);
					}
					$re_row_d[DocName]=$re_row_d[Name];
					array_push($doc_data,$re_row_d);
				}
			}
			$this->smarty->assign('doc_data',$doc_data);


			$sdate_year = substr( $sdate, 0, 4 );
			if($sdate_year < 2016){ $sdate_year = 2016; }
			if($sdate_year > date('Y')){ $sdate_year = date('Y'); }
			$edate_year = substr( $edate, 0, 4 );
			if($edate_year < 2016){ $edate_year = 2016; }
			if($edate_year > date('Y')){ $edate_year = date('Y'); }

			$GroupCode="00, 02, 98";

			//조건문
			$sql_where = '';
			if($sel_doc == 'vacation'){
				$sql_where .= "
					WHERE
						FormNum IN ('BRF-4-7' , 'HMF-4-7')
						and (Detail1 > '$sdate' AND Detail1 < '$edate')
						AND RT_SanctionState NOT LIKE '%FINISH%'
				";
			}elseif($sel_doc == "trip"){
				$sql_where .= "
					WHERE
						FormNum IN ('BRF-2-4' , 'HMF-2-4')
						and (
							(Detail4 > '$sdate' AND Detail4 < '$edate')
							OR (substring(Detail4, 13, 10) > '$sdate' AND substring(Detail4, 13, 10) < '$edate')
						)
						AND RT_SanctionState NOT LIKE '%FINISH%'
				";
			}elseif($sel_doc == "explanation"){
				
			}elseif($sel_doc == "BRF-9-2-s" || $sel_doc == "HMF-9-2-s"){
				$sql_where .= "
					where
						A.member = B.MemberNo
						and A.work_date >= '$sdate' AND A.work_date <= '$edate'
				";
				if($member <> ""){
					if($search_type == 'member'){
						$sql_where .= "and A.member in (select MemberNo from member_tbl where korName like '%$member%')";
					}
				}
			}else{
				$sql_where .= "
					where
						RG_Date >= date('$sdate') and RG_Date <= date('$edate')
				";

				if($sel_doc == "BRF-9-2-s" || $sel_doc == "HMF-9-2-s"){
				}else{
					$sql_where .=" and PG_Code in (".$GroupCode.")";
				}

				if($sel_group <> "ALL"){
					$sql_where .= "and RG_Code='$sel_group'";
				}

				if($sel_doc <> "ALL"){
					if($sel_doc == "BRF-6-2" || $sel_doc == "HMF-6-2" ){
						$sql_where .=" and (FormNum='BRF-6-2' or FormNum='HMF-6-2') ";
					}else{
						$sql_where .= "and FormNum like '%$sel_doc'";
					}
				}

				if($member <> ""){
					if($search_type == 'member'){
						$sql_where .= " and MemberNo in (select MemberNo from member_tbl where korName like '%$member%')";
					}elseif($search_type == 'title'){
						$sql_where .= " and DocTitle like '%$member%'";
					}
				}
			}

			//결재 완료된 문서 년도별로 나눴을때 테이블 union
			$sanctiondoc_tbls = "
				SELECT
					DocSN, FormNum, ProjectCode, DocTitle, AttchFile, MemberNo, RG_Date, RG_Code, PG_Date, PG_Code, RT_Sanction, RT_Sanctionstate, Security,
					ConservationYear, Account, FinishMemberNo, Detail1, Detail2, Detail3, Detail4, Detail5, Addfile, Memberinfo, confirm_members, OriginCode
				FROM
					sanctiondoc_tbl
			";
			$sanctiondoc_tbls .= $sql_where;
			/*
			$sanctiondoc_tbls .= "
				union all

				SELECT
					DocSN, FormNum, ProjectCode, DocTitle, AttchFile, MemberNo, RG_Date, RG_Code, PG_Date, PG_Code, RT_Sanction, RT_Sanctionstate, Security,
					ConservationYear, Account, FinishMemberNo, Detail1, Detail2, Detail3, Detail4, Detail5, Addfile, Memberinfo, '' AS confirm_members, '' AS OriginCode
				FROM
					sanctiondoc_2010_2014_tbl
			";
			$sanctiondoc_tbls .= $sql_where;
		*/

			if($report_kind=="1"){
				if($sel_doc == 'vacation'){
					$sql = "
						SELECT
							*
						FROM
							( $sanctiondoc_tbls ) A
						ORDER BY Detail1 asc
					";
					
				}elseif($sel_doc == "trip"){
					$sql = "
						SELECT
							*
						FROM
							( $sanctiondoc_tbls ) A
						ORDER BY Detail4 asc
					";
					
				}elseif($sel_doc == "explanation"){
					$sql = "
						SELECT
							*
						FROM
							( $sanctiondoc_tbls ) A
						ORDER BY Detail4 asc
					";
					
				}elseif($sel_doc == "BRF-9-2-s" || $sel_doc == "HMF-9-2-s"){
					$sql = "
						SELECT
							CASE '$sel_doc' 
							WHEN 'BRF-9-2-s' THEN 'BRF-9-2-s' 
							WHEN 'HMF-9-2-s' THEN 'HMF-9-2-s' END AS FormNum
							, concat(A.work_date, ' / ', A.apply_time, ' / ', A.approval_check, ' / ', A.overwork_info)  AS DocTitle
							, A.member AS MemberNo
							, B.GroupCode AS RG_Code
							, A.work_date AS RG_Date
							, '0000-00-00' AS PG_Date
							, A.approval_check AS Addfile
						FROM
							sanction_over_tbl A
							, member_tbl B
						where
							A.member = B.MemberNo
							and A.work_date >= '$sdate' AND A.work_date <= '$edate'
					";
					if($member <> ""){
						$sql .= "and A.member in (select MemberNo from member_tbl where korName like '%$member%')";
					}
					/*
					*/
					$sql .= "order by A.work_date desc, A.approval_check asc, B.GroupCode asc, B.korName asc";
				}else{
					$sql = "select * from ( $sanctiondoc_tbls ) A ";
					
					$sql .= " order by RG_Date desc,FormNum";
				}

				if($excel){
					$sql2 =$sql;
				}else{
					$sql2 =$sql." limit $Start, $page";
				}

				// echo "<div style='display:none;'>".$sql2."</div>";
				// echo "<div style='display:none;'>".$sql."</div>";
				// echo $sql;
				$re = mysql_query($sql,$db);
				$TotalRow = @mysql_num_rows($re);//총 개수 저장

				$last_start = ceil($TotalRow/15)*10+1;
				$last_page=ceil($TotalRow/15);

				$re2 = mysql_query($sql2,$db);
				while($re_row2 = @mysql_fetch_array($re2))
				{

					$Addfile=$re_row2[Addfile];
					$Addfile_arr=split("/n",$Addfile);
					if(count($Addfile_arr) >1)
					{
						for($i=0; $i<count($Addfile_arr); $i++) {
							if($Addfile_arr[$i] <> "")
							{
								//echo "****************".$Addfile_arr[$i]."<br>";
								$re_row2[Addfile]=$Addfile_arr[$i];
								break;

							}
						}

					}

					//날짜표시
					$FormNum = $re_row2[FormNum];
					if($FormNum=="BRF-4-7" || $FormNum=="HMF-4-7"){  //근태신청서
						//$re_row2[DocTitle] = $re_row2[DocTitle]." / ".substr ($re_row2[Detail1], 0, 10);
						$re_row2[DocTitle] = "[".substr ($re_row2[Detail1], 0, 10)."] ".$re_row2[DocTitle];
					}
					
					if($FormNum=="BRF-2-4" || $FormNum=="HMF-2-4"){	//출장신청서
						//$re_row2[DocTitle] = $re_row2[DocTitle]." / ".substr ($re_row2[Detail4], 0, 10);
						$re_row2[DocTitle] = "[".substr ($re_row2[Detail4], 0, 10)."] ".$re_row2[DocTitle];
					}

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

					if($FormNum == "BRF-9-2-s" || $FormNum == "HMF-9-2-s"){
						//$CommandDo = "viewdoc('".$FormNum."','".$re_row2[RG_Date]."','".$MemberNo."');";	//2024.03.25 김한결 주석처리함
						$CommandDo = "viewdoc('".$FormNum."','".$DocSN."','".$MemberNo."');";
					}else{
						$CommandDo = "viewdoc('".$FormNum."','".$DocSN."','".$memberID."');";
					}
					$re_row2[CommandDo]=$CommandDo;
					if($sel_doc == 'vacation'){
						$temp = split('] ',$re_row2[DocTitle]);
						$re_row2[DocTitle] = "[".substr($re_row2[Detail1], 0, 10)."~".substr($re_row2[Detail1], 12, 10)."] ".$temp[1];
						if(substr($re_row2[RT_SanctionState], 0, 1) == '0'){
							$re_row2[FormName] = $FormName."[임시저장]";
						}
						$member_arr = split('/n',$re_row2[Detail2]);
						$re_row2[DocTitle] .= '<br>';
						foreach($member_arr as $value){
							if($value != ''){
								$re_row2[DocTitle] .= "[".$value."]".MemberNo2Name($value).", ";
							}
						}
					}elseif($sel_doc == 'trip'){
						$temp = split('] ',$re_row2[DocTitle]);
						$re_row2[DocTitle] = "[".substr($re_row2[Detail4], 0, 10)."~".substr($re_row2[Detail4], 12, 10)."] ".$temp[1];
						if(substr($re_row2[RT_SanctionState], 0, 1) == '0'){
							$re_row2[FormName] = $FormName."[임시저장]";
						}
						$member_arr = split('/n',$re_row2[Detail2]);
						$re_row2[DocTitle] .= '<br>';
						$re_row2[DocTitle] .= "[".$re_row2[MemberNo]."]".MemberNo2Name($re_row2[MemberNo]).", ";
						foreach($member_arr as $value){
							if($value != ''){
								$re_row2[DocTitle] .= "[".$value."]".MemberNo2Name($value).", ";
							}
						}
					}
					array_push($query_data,$re_row2);
				}
			}else{
				$sql0 = "select * from (
							select DISTINCT a.DocSN as DocSN_agree from (
							select * from sanction_agree_tbl where AgreeMember<>''
							) a left join
							( select * from member_tbl where GroupCode='11' or GroupCode='15' or GroupCode='11')b
							on a.AgreeMember=b.MemberNo
							)A left join
							(
							select * from ( $sanctiondoc_tbls ) A where FormNum='HLF-1-12' and RT_SanctionState like '%FINISH%'
							)B
							on  A.DocSN_agree=B.DocSN order by B.RG_Date desc";

				//echo $sql0;
				$re0 = mysql_query($sql0);
				while($re_row0 = mysql_fetch_array($re0))
				{
					$DocSN=$re_row0[DocSN];

					//$GroupCode="11";

					$sql2 = "select * from ( $sanctiondoc_tbls ) where RT_SanctionState like '%FINISH%' and DocSN = '$DocSN'";
					$sql2 .= "order by RG_Date asc";
					if(!$excel){
						$sql2 =$sql2." limit $Start, $page";
					}
					//echo $sql2."<br>";
					$re2 = mysql_query($sql2,$db);
					$TotalRow = mysql_num_rows($re2);//총 개수 저장

					$last_start = ceil($TotalRow/15)*10+1;;
					$last_page=ceil($TotalRow/15);

					$re2 = mysql_query($sql2,$db);
					while($re_row2 = mysql_fetch_array($re2))
					{

						$Addfile=$re_row2[Addfile];
						$Addfile_arr=split("/n",$Addfile);
						if(count($Addfile_arr) >1)
						{
							for($i=0; $i<count($Addfile_arr); $i++) {
								if($Addfile_arr[$i] <> "")
								{
									//echo "****************".$Addfile_arr[$i]."<br>";
									$re_row2[Addfile]=$Addfile_arr[$i];
									break;

								}
							}

						}

						//날짜표시
						if($FormNum=="BRF-4-7" || $FormNum=="HMF-4-7"){  //근태신청서
							//$re_row2[DocTitle] = $re_row2[DocTitle]." / ".substr ($re_row2[Detail1], 0, 10);
							$re_row2[DocTitle] = "[".substr ($re_row2[Detail1], 0, 10)."] ".$re_row2[DocTitle];
						}
						
						if($FormNum=="BRF-2-4" || $FormNum=="HMF-2-4"){	//출장신청서
							//$re_row2[DocTitle] = $re_row2[DocTitle]." / ".substr ($re_row2[Detail4], 0, 10);
							$re_row2[DocTitle] = "[".substr ($re_row2[Detail4], 0, 10)."] ".$re_row2[DocTitle];
						}

						
						if($FormNum == "BRF-9-2-s" || $FormNum == "HMF-9-2-s"){
							//$DocSN=$re_row2[RG_Date];	//2024.03.25 김한결 주서처리함.
							$DocSN=$re_row2[DocSN];
						}else{
							$DocSN=$re_row2[DocSN];
						}
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
				}
			}

			if($currentPage == "") $currentPage = 1;
			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			$this->smarty->assign('report_kind',$report_kind);

			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);
			$this->smarty->assign('sel_group',$sel_group);
			$this->smarty->assign('sel_doc',$sel_doc);
			$this->smarty->assign('member',$member);
			$this->smarty->assign('FormNum',$FormNum);

			$this->smarty->assign('search_type',$search_type);
			$this->smarty->assign('searchv',$searchv);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);

			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('searchv',$searchv);
			$this->smarty->assign('excel',$excel);



			$this->smarty->assign("page_action","approval_admin_controller.php?ActionMode=Admin");
			$this->smarty->display("intranet/common_contents/work_approval/approval_adminlist2_mvc.tpl");
		}

		//============================================================================
		// 결과값 엑셀 다운로드
		//============================================================================
		function ExcelDown(){

		}

		function PrintExcelHeader($filename)
		{
			$filename = $this->HangleEncodeUTF8_EUCKR($filename);
			header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
			header("Content-type:   application/x-msexcel; charset=utf-8");
			header("Content-Disposition: attachment; filename=$filename.xls");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
		}

		function HangleEncodeUTF8_EUCKR($item)
		{
				$result=trim(ICONV("UTF-8","EUC-KR",$item));
				return $result;
		}
	}
?>