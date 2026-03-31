<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

	/***************************************
	* 업무협조전 작성
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";


	extract($_GET);
	class CooperationLogic {
		var $smarty;
		function CooperationLogic($smarty)
		{
			$this->smarty=$smarty;
		}


		//============================================================================
		// 업무협조전 리스트
		//============================================================================
		function ListPage()
		{
			include "../inc/approval_function.php";
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page;
			global $searchv,$selt,$sdate,$edate,$page_gb;

			if($page_gb == '') $page_gb = 0;

			$page=15;
			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();

			if($selt == ""){
				$selt = "%";
			}

			if($edate == ""){
					$edate = date("Y-m-d", mktime(0,0,0, date("m"), date("d"), date("Y")));
					$sdate = date("Y-m-d", mktime(0,0,0, date("m")-12, date("d"), date("Y")));
				}



			//$sql="select * from sanction_notice_tbl where GroupCode='$GroupCode' order by no desc";
			$sql="select * from sanction_notice_tbl where page_gb = $page_gb and write_day between '$sdate' and '$edate' ";

			//검색
			if($searchv <> ""){	//단어검색
				//echo 123;
				if($selt=="제목"){
					$sql=$sql." and title like '%".$searchv."%'";
				}else if($selt=="작성자"){
					$sql=$sql." and name like '%".$searchv."%'";
				}
			}
			$sql=$sql." order by no desc";


			//$sql="select * from sanction_notice_tbl order by no desc";
			$sql2 =$sql." limit $Start, $page";

//echo $sql;
			$re = mysql_query($sql,$db);
			$TotalRow = mysql_num_rows($re);//총 개수 저장
			$last_start = ceil($TotalRow/15)*15+1;;
			$last_page=ceil($TotalRow/15);


			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{

				$send_member_tmp = split("/",$re_row2[send_member]);
				$send_member_no =count($send_member_tmp);

				$read_member_tmp = split("/",$re_row2[read_member]);
				$read_member_no =count($read_member_tmp);

				if ($read_member_no >=$send_member_no )
				{
					$re_row2[State]="완료";
				}else
				{
					$re_row2[State]="진행중";
				}

				array_push($query_data,$re_row2);
			}


			if($currentPage == "") $currentPage = 1;
			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();


			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign("tab_index",$tab_index);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);

			$this->smarty->assign("sdate",$sdate);
			$this->smarty->assign("edate",$edate);
			$this->smarty->assign("page_gb",$page_gb);

			$this->smarty->assign("page_action","cooperation_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/cooperation_list_mvc_jmj.tpl");
		}


		//============================================================================
		// 업무협조전 입력화면 보기
		//============================================================================
		function InsertPage()
		{
			global $db,$memberID,$page_gb;

			$today=date("Y-m-d");

			$korName=$_SESSION['korName'];

			$arr_data = array();
			if( $page_gb == 1 ){
				$sql = "select memberno as CodeORName, korname as Name, (select name from systemconfig_tbl where SysKey = 'PositionCode' and code = a.RankCode) as Description from member_tbl a where WorkPosition = 1 and GroupCode = '2' order by RankCode, korname";
			}else{
				$sql = "select * from systemconfig_tbl where SysKey = 'NoticeGroup' and CodeOrName <> '' order by Code";
			}
			$re = mysql_query($sql,$db);
			while($re_row=mysql_fetch_array($re))
			{
				array_push($arr_data,$re_row);
			}

			$this->smarty->assign('korName',$korName);
			$this->smarty->assign('limit_day',$today);

			$this->smarty->assign('arr_data',$arr_data);
			$this->smarty->assign('mode',"add");
			$this->smarty->assign("page_gb",$page_gb);

			$this->smarty->assign("page_action","cooperation_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/cooperation_input_mvc_jmj.tpl");

		}


		//============================================================================
		// 업무협조전 저장
		//============================================================================
		function InsertAction()
		{

			global $db,$memberID;
			global $GroupCode,$name,$title,$pass,$viewoption;
			global $limit_day,$comment,$sendmember;
			global $userfile,$userfile_name,$userfile_size;
			global $userfile2,$userfile2_name,$userfile2_size,$page_gb;


			$path ="./../../../intranet_file/notice/cooperate/";
			$path_is ="./../../../intranet_file/notice/cooperate";

			$userfile_name = str_replace(" ","",$userfile_name);
			if ($userfile_name <>"" && $userfile_size <>0)
			{
				if (is_dir ($path_is))
				{
				}
				else
				{
					mkdir($path_is, 0777);
				}
				$filename=iconv("UTF-8", "EUC-KR",$userfile_name);
				$orgfilename = $path.$filename;
				$exist_org = file_exists("$orgfilename");
				/*
				if($exist_org) {
						echo(" <script>
								  window.alert('\"$userfile_name\" 이미 존재합니다.')
								  history.go(-1)
								 </script>
							   ");exit;
							}
				*/
				$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
				$vupload = $path. $_FILES['userfile']['name'];
				$vupload = str_replace(" ","",$vupload);
				$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
				move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
				$userfile_size = number_format($userfile_size);


				$filename="./cooperate/".$userfile_name;
				$filename = str_replace(" ","",$filename);
			}
			else
			{
				$filename="";
			}

			/**************************************************************/

			$userfile2_name = str_replace(" ","",$userfile2_name);
			if ($userfile2_name <>"" && $userfile2_size <>0)
			{

				$filename2=iconv("UTF-8", "EUC-KR",$userfile2_name);
				$orgfilename2 = $path.$filename2;
				$exist_org2 = file_exists("$orgfilename2");



				$_FILES['userfile2']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile2']['name']);
				$vupload2 = $path. $_FILES['userfile2']['name'];
				$vupload2 = str_replace(" ","",$vupload2);
				$_FILES['userfile2']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile2']['tmp_name']);
				move_uploaded_file($_FILES['userfile2']['tmp_name'], $vupload2);
				//$userfile_size = number_format($userfile_size);


				$filename2="./cooperate/".$userfile2_name;
				$filename2 = str_replace(" ","",$filename2);
			}
			else
			{
				$filename2="";
			}


			$start_day="0000-00-00";
			$end_day="0000-00-00";


			foreach($sendmember as $v){
				$send_member .= $v."/";
			}

			$sql="insert into sanction_notice_tbl (name,pass,title,start_day,end_day,limit_day,comment,write_day,filename,filename2,send_member,read_member,n_num,GroupCode,sanction_doc, page_gb)";
			$sql=$sql." values('$name','$pass','$title','$start_day','$end_day','$limit_day','$comment',now(),'$filename','$filename2','$send_member','$read_member','$memberID','$GroupCode','', '$page_gb')";



			mysql_query($sql,$db);
			$this->smarty->assign('MoveURL',"cooperation_controller.php?ActionMode=list&page_gb=".$page_gb);
			$this->smarty->display("intranet/move_page.tpl");

		}

		//============================================================================
		//  업무협조전 편집보기
		//============================================================================
		function UpdateReadPage()
		{

			global $db,$memberID;
			global $no,$mode,$page_gb;


			$arr_data = array();

			$sql="select * from sanction_notice_tbl where no='$no'";
			$re=mysql_query($sql,$db);

			while($row=mysql_fetch_array($re))
			{
				$no=$row[no];
				$name=$row[name];
				$title=$row[title];
				$write_day=$row[write_day];
				$pass=$row[pass];
				$limit_day=$row[limit_day];
				$comment=$row[comment];
				$send_member=$row[send_member];
				$read_member=$row[read_member];
				$filename=$row[filename];
				$tmpfile=explode("/",$filename);
				$tmpno= count($tmpfile)-1;
				$filename_is= $tmpfile[$tmpno];


				$filename2=$row[filename2];
				$tmpfile2=explode("/",$filename2);
				$tmpno2= count($tmpfile2)-1;
				$filename_is2= $tmpfile2[$tmpno2];

				$page_gb=$row[page_gb];

			}


			$k=0;
			$viewchk=false;
			$send_membertmp=split("/",$send_member);
			for($k;$k < count($send_membertmp)-1;$k++)
			{
					if ($read_member <> "")
					{
						if (strpos($read_member, $send_membertmp[$k]) > -1)
							$viewchk=true;
						else
							$viewchk=false;
					}


					$sql2="select a.korName,b.Name from
					(
						select * from member_tbl where MemberNo ='$send_membertmp[$k]'
					)a left join
					(
						select * from systemconfig_tbl where SysKey='GroupCode'
					)b on a.GroupCode =b.Code";


					$re2 = mysql_query($sql2,$db);
					$total_row = mysql_num_rows($re2);
					if ($total_row > 0)
					{

						$NoiceName=mysql_result($re2,0,"korName");
						$Name=mysql_result($re2,0,"Name");


						$ItemData=array("viewchk" =>$viewchk,"Name"=>$Name,"NoiceName"=>$NoiceName,"NoticeGroup"=>$NoticeGroup,"CodeORName"=>$send_membertmp[$k]);
						array_push($arr_data,$ItemData);
					}

			}

			$this->smarty->assign('arr_data',$arr_data);
			$this->smarty->assign('Auth',true);


			$this->smarty->assign('no',$no);
			$this->smarty->assign('korName',$name);
			$this->smarty->assign('title',$title);
			$this->smarty->assign('write_day',$write_day);
			$this->smarty->assign('pass',$pass);
			$this->smarty->assign('limit_day',$limit_day);
			$this->smarty->assign('comment',$comment);
			$this->smarty->assign('send_member',$send_member);
			$this->smarty->assign('read_member',$read_member);
			$this->smarty->assign('filename',$filename);
			$this->smarty->assign('filename_is',$filename_is);

			$this->smarty->assign('filename2',$filename2);
			$this->smarty->assign('filename_is2',$filename_is2);


			$this->smarty->assign('mode',$mode );
			$this->smarty->assign('page_gb',$page_gb );
			$this->smarty->assign("page_action","cooperation_controller.php");

			if($mode=="mod")
				$this->smarty->display("intranet/common_contents/work_approval/cooperation_input_mvc_jmj.tpl");
			else
				$this->smarty->display("intranet/common_contents/work_approval/cooperation_read_mvc_jmj.tpl");
		}


		//============================================================================
		// 업무협조전 편집
		//============================================================================
		function UpdateAction()
		{


			global $db,$memberID;
			global $no;
			global $GroupCode,$name,$title,$pass;
			global $limit_day,$comment,$sendmember,$filename_org;
			global $userfile,$userfile_name,$userfile_size;
			global $userfile2,$userfile2_name,$userfile2_size,$filename_org2,$page_gb;


			foreach($sendmember as $v){
				$send_member .= $v."/";
			}


			$path ="./../../../intranet_file/notice/cooperate/";
			$path_is ="./../../../intranet_file/notice/cooperate";

			$userfile_name = str_replace(" ","",$userfile_name);
			if ($userfile_name <>"" && $userfile_size <>0)
			{

				$orgfilename = $path.$filename_org;
				//echo "filename_org".$filename_org."<br>";
				$orgfilename=iconv("UTF-8", "EUC-KR",$orgfilename);
				$exist_org = file_exists("$orgfilename");
				if($exist_org)
				{
					$re=unlink("$orgfilename");
					echo "delete".$orgfilename."<br>";
				}

				$filename=iconv("UTF-8", "EUC-KR",$userfile_name);
				$orgfilename = $path.$filename;
				$exist_org2 = file_exists("$orgfilename");

				if($exist_org2) {
						echo(" <script>
								  window.alert('\"$userfile_name\" 이미 존재합니다.')
								  history.go(-1)
								 </script>
							   ");exit;
							}

				$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
				$vupload = $path. $_FILES['userfile']['name'];
				$vupload = str_replace(" ","",$vupload);
				$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
				move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
				$userfile_size = number_format($userfile_size);


				$filename="./cooperate/".$userfile_name;
				$filename = str_replace(" ","",$filename);
			}

			/**************************************************************/

			$userfile2_name = str_replace(" ","",$userfile2_name);
			if ($userfile2_name <>"" && $userfile2_size <>0)
			{


				$orgfilename2 = $path.$filename_org2;
				$orgfilename2=iconv("UTF-8", "EUC-KR",$orgfilename2);
				$exist_org2 = file_exists("$orgfilename2");
				if($exist_org2)
				{
					$re=unlink("$orgfilename2");
				}

				$filename2=iconv("UTF-8", "EUC-KR",$userfile2_name);
				$orgfilename2 = $path.$filename2;
				$exist_org2 = file_exists("$orgfilename2");



				$_FILES['userfile2']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile2']['name']);
				$vupload2 = $path. $_FILES['userfile2']['name'];
				$vupload2 = str_replace(" ","",$vupload2);
				$_FILES['userfile2']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile2']['tmp_name']);
				move_uploaded_file($_FILES['userfile2']['tmp_name'], $vupload2);
				//$userfile_size = number_format($userfile_size);


				$filename2="./cooperate/".$userfile2_name;
				$filename2 = str_replace(" ","",$filename2);
			}
			else
			{
				$filename2="";
			}

			$sql="update sanction_notice_tbl set page_gb='$page_gb', name='$name', pass='$pass', title='$title', comment='$comment', limit_day='$limit_day', comment='$comment', write_day=now(), send_member='$send_member', ";

			if($filename<>"")
				$sql.="filename='$filename',";

			if($filename2<>"")
				$sql.="filename2='$filename2',";

			$sql.="n_num='$memberID' where no='$no'";

			//echo $sql;
			mysql_query($sql,$db);


			//수신부서 결재처리완료후 처리 끝----------------------------------------------------------------

			$this->smarty->assign('MoveURL',"cooperation_controller.php?ActionMode=update_page&page_gb=$page_gb&no={$no}");
			$this->smarty->display("intranet/move_page.tpl");

		}


		//============================================================================
		// 업무협조전 삭제
		//============================================================================
		function DeleteAction()
		{


			global $db,$memberID;
			global $no;
			global $GroupCode,$name,$title,$pass,$viewoption;
			global $limit_day,$comment,$sendmember,$filename_org;
			global $userfile,$userfile_name,$userfile_size,$page_gb;



			$path ="./../../../intranet_file/notice/cooperate/";
			$orgfilename = $path.$filename_org;
			$orgfilename=iconv("UTF-8", "EUC-KR",$orgfilename);
			$exist_org = file_exists("$orgfilename");
			if($exist_org)
			{
				$re=unlink("$orgfilename");
			}

			$orgfilename2 = $path.$filename_org2;
			$orgfilename2=iconv("UTF-8", "EUC-KR",$orgfilename2);
			$exist_org2 = file_exists("$orgfilename2");
			if($exist_org2)
			{
				$re=unlink("$orgfilename2");
			}

			$sql="delete from sanction_notice_tbl where no='$no'";
			//echo $sql;
			mysql_query($sql,$db);


			//수신부서 결재처리완료후 처리 끝----------------------------------------------------------------

			$this->smarty->assign('MoveURL',"cooperation_controller.php?ActionMode=list&page_gb=$page_gb");
			$this->smarty->display("intranet/move_page.tpl");

		}


		//============================================================================
		// 업무협조전 부서담당자
		//============================================================================
		function MemberList()
		{

			global $db,$memberID;
			global $auth;
			global $SelectGroup,$page_gb;



			$query_data = array();

			$sql="select * from systemconfig_tbl where SysKey = 'NoticeGroup' order by Code";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}


			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('memberID',$memberID);

			$this->smarty->assign("page_action","cooperation_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/cooperation_member_mvc.tpl");
		}

		//============================================================================
		// 업무협조전 부서담당자 변경
		//============================================================================
		function MemberChangeList()
		{

			global $db,$memberID;
			global $auth,$SelectGroup,$page_gb;



			$member_data = array();

			$sql = "select aa.korName,aa.Name as RankName,aa.MemberNo,bb.Name as GroupName,aa.MemberNo as MemberNo from
			(
				select *  from
				(
					select * from member_tbl where WorkPosition ='1' and GroupCode='$SelectGroup' order by RankCode asc
				)a left JOIN
				(
					select * from systemconfig_tbl where SysKey='PositionCode'
				)b on a.RankCode = b.code
			)aa left JOIN
			(
				select * from systemconfig_tbl where SysKey='GroupCode'
			)bb on aa.GroupCode=bb.Code";
			//echo $sql;

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				$GroupName=$re_row[GroupName];
				array_push($member_data,$re_row);
			}


			$this->smarty->assign('member_data',$member_data);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('GroupName',$GroupName);
			$this->smarty->assign('SelectGroup',$SelectGroup);



			$this->smarty->assign("page_action","cooperation_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/cooperation_memberchange_mvc.tpl");
		}

		//============================================================================
		// 업무협조전 부서담당자 변경
		//============================================================================
		function MemberChange()
		{

			global $db,$memberID;
			global $MemberNo,$MemberName,$SelectGroup,$page_gb;

			$SelectGroup=sprintf('%02d',$SelectGroup);

			$sql="update systemconfig_tbl set CodeORName='$MemberNo',Description='$MemberName' where SysKey = 'NoticeGroup'  and Note='$SelectGroup'";
			mysql_query($sql,$db);
			//echo $sql;

			$this->smarty->assign("target","opener");
			$this->smarty->assign('MoveURL',"cooperation_controller.php?ActionMode=MemberList");
			$this->smarty->display("intranet/move_page.tpl");


		}


		function AcceptPage()
		{

			global $db,$memberID;
			global $no,$MemberName,$SelectGroup,$page_gb;
			include "../inc/approval_function.php";


			/*
			MemberNo 기안자
			RG_Code 기안부서
			RG_Date 기안일

			PG_Code 접수부서
			PG_Date 접수일
			FinishMemberNo 접수한사람
			Detail1 문서 sanction_notice_tbl no
			*/

			$sql="select * from sanction_notice_tbl where no='$no'";
			$re=mysql_query($sql,$db);

			if(mysql_num_rows($re) > 0)
			{
				$NewSN=NewSerialNo2($memberID);
				$FormNum="HM-NOTICE";
				$DocTitle=mysql_result($re,0,"title");
				$MemberNo=mysql_result($re,0,"n_num");
				$RG_Date=mysql_result($re,0,"write_day");
				$PG_Date=date("Y-m-d");
				$RT_SanctionState="1:FINISH:";
				$FinishMemberNo=$memberID;
				$Detail1=$no;
				$Addfile=mysql_result($re,0,"filename");


			}



			$insql = "insert into SanctionDoc_tbl (DocSN, FormNum,DocTitle, MemberNo,RG_Date,PG_Date, RT_SanctionState,Security, ConservationYear,FinishMemberNo, Detail1, Addfile ) values('$NewSN', '$FormNum','$DocTitle','$MemberNo', '$RG_Date','$PG_Date','$RT_SanctionState', 'LOW', '1', '$FinishMemberNo', '$Detail1', '$Addfile')";
		//	echo $insql."<br>";
			mysql_query($insql,$db);


			$sql="update sanction_notice_tbl set read_member=concat(read_member,'$memberID/'),sanction_doc=concat(sanction_doc,'$NewSN/') where no='$no'";
		//	echo $sql."<br>";
			mysql_query($sql,$db);




			$this->smarty->assign("target","opener");
			$this->smarty->assign('MoveURL',"approval_controller.php?ActionMode=view&tab_index=2");
			$this->smarty->display("intranet/move_page.tpl");


		}




}
?>