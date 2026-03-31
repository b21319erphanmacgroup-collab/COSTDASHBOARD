<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

	/***************************************
	* 사내양식
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/
	require('../inc/function_intranet.php');	//자주쓰는 기능 Function
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";

	$now_day=date("Y-m-d h:i:s");

	extract($_GET);
	class form_logic {
		var $smarty;
		function form_logic($smarty)
		{
			$this->smarty=$smarty;
		}

		//==================================================================================//
		//==사내양식 입력 페이지 Logic==============================================================//
		//==================================================================================//
		function InsertPage()
		{
			global $db,$memberID;
			global $company;

			//$korName = $_SESSION['CK_korName'];
			$korName = MemberNo2Name($memberID);

			$url="form_controller.php";
			$this->smarty->assign('name',$korName);
			$this->smarty->assign('company',$company);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","form_controller.php");
			$this->smarty->assign("list_action","form_controller.php");

			$this->smarty->display("intranet/common_contents/work_notice/form_input_mvc.tpl");
		}

		//==================================================================================//
		//==사내양식 저장 Logic(insert)==============================================================//
		//==================================================================================//
		function InsertAction()
		{

			global $db,$memberID;
			global $name,$title,$password;
			global $comment,$wdate,$ip,$now_day;
			global $company,$tid,$mode,$filename;
			global $userfile,$userfile_name,$userfile_size;


			if($company=="") $company=5;

			$name = trim($name);					/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */


			$path ="./../../../intranet_file/notice/form/";
			$path_is ="./../../../intranet_file/notice/form";



			if($userfile)
			{

				if (is_dir ($path_is)){	}
				else{mkdir($path_is, 0777);}

				$userfile=stripslashes($userfile);

				$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
				$vupload = $path. $_FILES['userfile']['name'];
				$vupload = str_replace(" ","",$vupload);
				$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
				move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
				$userfile_size = number_format($userfile_size);

				$filename=$userfile_name;
				$filename = str_replace(" ","",$filename);

			}
			else
			{
				$filename="";
				$userfile_size="";
			}

			$sql = "insert into pds1_new_tbl (name,email,homepage,title,password,comment,filename,filesize,wdate,ip,count,company)
			values ('$name','','$homepage','$title','$password','$comment','$filename','$userfile_size',now(),'$ip','$count','$company')";

			mysql_query($sql,$db);

			$this->smarty->assign('target',"self");
			$this->smarty->assign('MoveURL',"form_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");

		}

		//==================================================================================//
		//==사내양식 읽기/수정 페이지Logic===========================================================//
		//==================================================================================//
		function UpdateReadPage()
		{
			global $db,$memberID;
			global $mode,$page,$currentPage;
			global $company,$tid,$now_day;
			global $pass,$updatecode,$password2;


			$PersonAuthority=new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);

			$query_data = array();

			//$sql = "select * from pds1_new_tbl where company='$company' and tid='$tid'";
			$sql = "select * from pds1_new_tbl where tid='$tid'";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				$tid =$re_row[tid];
				$name =$re_row[name];
				$password =$re_row[password];
				$wdate=$re_row[wdate];
				$title=$re_row[title];
				$comment=$re_row[comment];
				$company=$re_row[company];



				if($mode=="read" || $mode=="del"){
					$comment=ereg_replace("([A-Za-z0-9_-]+)@(([A-Za-z0-9-]+)\.)+([A-Za-z0-9-]+)","<a href=\"mailto:\\0\">\\0</a>",$comment);
					$comment=ereg_replace("\n","<br>&nbsp;",$comment);
					$comment=ereg_replace("  ","&nbsp;&nbsp;",$comment);
				}


				$filename=$re_row[filename];
				$filesize=$re_row[filesize];

				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('company',$company);
				$this->smarty->assign('pass',$pass);
				$this->smarty->assign('password',$password);
				$this->smarty->assign('password2',$password2);
				$this->smarty->assign('tid',$tid);
				$this->smarty->assign('name',$name);
				$this->smarty->assign('wdate',$wdate);
				$this->smarty->assign('title',$title);
				$this->smarty->assign('comment',$comment);
				$this->smarty->assign('filename',$filename);
				$this->smarty->assign('filesize',$filesize);
				$this->smarty->assign("page_action","form_controller.php");

				if($updatecode=="1")
					$this->smarty->display("intranet/common_contents/work_notice/form_input_mvc.tpl");
				else
					$this->smarty->display("intranet/common_contents/work_notice/form_read_mvc.tpl");

			}
		}


		//==================================================================================//
		//==사내양식 저장 Logic(update)=============================================================//
		//==================================================================================//
		function UpdateAction()
		{
			global $db,$memberID;
			global $name,$title,$password;
			global $comment,$wdate,$ip,$now_day;
			global $company,$tid,$mode,$filename;
			global $userfile,$userfile_name,$userfile_size;


			$wdate = date ("%Y/%m/%d ");			/* 입력 날짜 저장 */
			$name = trim($name);					/* 이름 앞 뒤 공백 제거 */

			$path ="./../../../intranet_file/notice/form/";

			if($userfile)
			{
				if($userfile_name <>"" && $userfile_size <>0)
					{ //첨부파일 있으면서 수정이면

						$filename=iconv("UTF-8", "EUC-KR",$filename);
						$orgfilename = $path.$filename;
						echo $orgfilename;
						$exist_org = file_exists("$orgfilename");
						if($exist_org)
							{
								$re=unlink("$orgfilename");
							}
					}

					$userfile=stripslashes($userfile);

					$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
					$vupload = $path. $_FILES['userfile']['name'];
					$vupload = str_replace(" ","",$vupload);
					$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
					move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
					$userfile_size = number_format($userfile_size);

					$filename=$userfile_name;
					$filename = str_replace(" ","",$filename);


				$sql  = "update pds1_new_tbl set name='$name',title='$title',password='$password',comment='$comment',wdate=now(),ip='$ip',company='$company',filename='$filename' ,filesize='$userfile_size' where tid='$tid'";
			}
			else
			{
				$sql  = "update pds1_new_tbl set name='$name',title='$title',password='$password',comment='$comment',ip='$ip',company='$company' where tid='$tid'";
			}


			mysql_query($sql,$db);

			$this->smarty->assign('target',"self");
			$this->smarty->assign('MoveURL',"form_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");

		}


		//==================================================================================//
		//==사내양식 삭제 Logic (delete)=============================================================//
		//==================================================================================//
		function DeleteAction()
		{

			global $db,$memberID;
			global $name,$title,$password;
			global $comment,$wdate,$ip;
			global $company,$tid;

			$sql="select * from pds1_new_tbl where tid=$tid";
			$re=mysql_query($sql,$db);
			$idx_imsi = mysql_result($re, 0, "idx");
			$filename = mysql_result($re, 0, "filename");

				$path ="./../../../intranet_file/notice/form/";
				$filename=iconv("UTF-8", "EUC-KR",$filename);
				$orgfilename = $path.$filename;

				$exist_org = file_exists("$orgfilename");
				if($exist_org)
				{
					$re=unlink("$orgfilename");
				}

			$sql = "delete from pds1_new_tbl where tid=$tid";

			mysql_query($sql,$db);

			$this->smarty->assign('target',"self");
			$this->smarty->assign('MoveURL',"form_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");
		}


		//==================================================================================//
		//==사내양식 LIST Logic===================================================================//
		//==================================================================================//
		function View()  //사내양식
		{
			global $db,$memberID;
			global $company,$searchv;
			global $Start,$page,$currentPage,$last_page,$company,$tab_index;

			$page=15;
			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			if($tab_index=="")
				$tab_index = 1;

			$company=$tab_index;


			$PersonAuthority=new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);

			$query_data = array();

			if($searchv=="")
			{
				if($_SESSION['CK_CompanyKind']=="HANM")
				{
						$sql_num="select * from pds1_new_tbl where company='$company'";
						$sql = "select * from pds1_new_tbl where company='$company' order by num,title asc limit $Start, $page";
				}
				else if($_SESSION['CK_CompanyKind']=="BARO")
				{
						$sql_num="select * from pds1_new_tbl where company='$company'";
						$sql = "select * from pds1_new_tbl where company='$company' order by num,title asc limit $Start, $page";
				}
				else
				{
						$sql_num="select * from pds1_new_tbl";
						$sql = "select * from pds1_new_tbl order by title asc limit $Start, $page";
				}

			}
			else
			{
				if($_SESSION['CK_CompanyKind']=="HANM")
				{
					$sql_num="select * from pds1_new_tbl where title like '%$searchv%' and company='$company'";
					$sql = "select * from pds1_new_tbl where title like '%$searchv%'  and company='$company' order by title asc limit $Start, $page";
				}else if($_SESSION['CK_CompanyKind']=="BARO")
				{
					$sql_num="select * from pds1_new_tbl where title like '%$searchv%' and company='$company'";
					$sql = "select * from pds1_new_tbl where title like '%$searchv%'  and company='$company' order by title asc limit $Start, $page";
				}else
				{
					$sql_num="select * from pds1_new_tbl where title like '%$searchv%'";
					$sql = "select * from pds1_new_tbl where title like '%$searchv%' order by title asc limit $Start, $page";
				}
			}

			//echo $sql;
			$re = mysql_query($sql_num,$db);
			$TotalRow = mysql_num_rows($re);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/10);

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}

			//한맥일경우 한맥,바론표시
			if($_SESSION['CK_CompanyKind']=="HANM")
			{
				//$ComName = array('바론컨설턴트','바론컨설턴트');
				$ComName = array('바론컨설턴트','바론컨설턴트', '기술개발센터');	//22.03.02 전나현 요청으로 기술개발센터 탭 추가
				//$ComCode = array('1','2');
				$ComCode = array('1','2','3');
			}else if($_SESSION['CK_CompanyKind']=="BARO")
			{
				//$ComName = array('바론컨설턴트','바론컨설턴트');
				$ComName = array('바론컨설턴트','바론컨설턴트', '기술개발센터');	//22.03.02 전나현 요청으로 기술개발센터 탭 추가
				//$ComCode = array('1','2');
				$ComCode = array('1','2','3');
			}

			if($currentPage == "") $currentPage = 1;

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			$this->smarty->assign("page_action","form_controller.php");


			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('searchv',$searchv);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);

			$this->smarty->assign('ComName',$ComName);
			$this->smarty->assign('ComCode',$ComCode);

			$this->smarty->display("intranet/common_contents/work_notice/form_contents_mvc.tpl");
		}

	}



