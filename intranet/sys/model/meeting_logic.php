<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
	
	/***************************************
	* 회의록 
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/ 

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";

	extract($_GET);
	class PDSLogic {
		var $smarty;
		function PDSLogic($smarty)
		{
			$this->smarty=$smarty;
		}//function PDSLogic($smarty)


		//============================================================================
		// 회의록 입력 logic
		//============================================================================
		function InsertPage()
		{
			global $db;
			global $company,$memberID;

			if($_SESSION['auth_directormetting'])//임원회의 접근권한
				$this->smarty->assign('auth_directormetting',true);
			else
				$this->smarty->assign('auth_directormetting',false);
			

			if($_SESSION['auth_salesmetting'])//영업회의 접근권한
				$this->smarty->assign('auth_salesmetting',true);
			else
				$this->smarty->assign('auth_salesmetting',false);
			

			if($_SESSION['auth_metting'])//일반회의 접근권한
				$this->smarty->assign('auth_metting',true);
			else
				$this->smarty->assign('auth_metting',false);
			
			if($company=="") $company=1;
			$url="meeting_controller.php";
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('company',$company);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","meeting_controller.php");

			$this->smarty->display("intranet/common_contents/work_notice/meeting_input_mvc.tpl");
		}//function InsertPage()



		//============================================================================
		// 회의록 insert logic
		//============================================================================
		function InsertAction()
		{

			global $db;
			global $compay_value,$title,$password;
			global $comment,$wdate,$ip,$now_day;
			global $company,$tid,$mode,$filename;
			global $userfile,$userfile_name,$userfile_size;

			if($company=="") $company=1;

			$now_day=date("Y-m-d h:i:s");
			$wdate = date ("%Y/%m/%d");				/* 입력 날짜 저장 */
			$name = trim($name);					/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */
			$homepage = trim($homepage);			/* 홈페이지 앞 뒤 공백 제거 */
			$ip = getenv("REMOTE_ADDR");			/* Remote IP저장 */
			$comment = nl2br($comment); 			/* 내용부분 /n 인식 시키기위한 */

			$path ="./../../../intranet_file/notice/meeting/".$compay_value."/";
			$path_is ="./../../../intranet_file/notice/meeting/".$compay_value;
			
			
			if($userfile) 
			{
				if (is_dir ($path_is)){
				}
				else{
					mkdir($path_is, 0777);
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
			}
			else
			{	
				$filename="";
				$userfile_size="";
			
			}

			$insql = "insert into meeting_reference_tbl (name,title,password,comment,filename,filesize,wdate,ip,count,company)	values ('$memberID','$title','$password','$comment','$filename','$userfile_size','$now_day','$ip','$count','$compay_value')";

			mysql_query($insql,$db);

			
			$this->smarty->assign('MoveURL',"meeting_controller.php?ActionMode=view&company=$compay_value");
			$this->smarty->display("intranet/move_page.tpl");

		}//function InsertAction()


		//============================================================================
		// 회의록 수정/읽기 logic/
		//============================================================================
		function UpdateReadPage()
		{
			global $db;
			global $mode,$page,$currentPage;
			global $company,$tid,$now_day,$memberID;
			global $pass,$updatecode,$password2;

			if($_SESSION['auth_directormetting'])//임원회의 접근권한
				$this->smarty->assign('auth_directormetting',true);
			else
				$this->smarty->assign('auth_directormetting',false);
			

			if($_SESSION['auth_salesmetting'])//영업회의 접근권한
				$this->smarty->assign('auth_salesmetting',true);
			else
				$this->smarty->assign('auth_salesmetting',false);
			

			if($_SESSION['auth_metting'])//일반회의 접근권한
				$this->smarty->assign('auth_metting',true);
			else
				$this->smarty->assign('auth_metting',false);

			$query_data = array(); 

			$sql = "select * from meeting_reference_tbl where company='$company' and tid='$tid'";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				$tid =$re_row[tid];			
				$name =$re_row[name];
				$password =$re_row[password];	
				$wdate=$re_row[wdate];
				$title=$re_row[title];
				$comment=$re_row[comment];
				$filename=$re_row[filename];
				$filesize=$re_row[filesize];

				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('company',$company);
				$this->smarty->assign('pass',$pass);
				$this->smarty->assign('password',$password);
				$this->smarty->assign('password2',$password2);
				$this->smarty->assign('tid',$tid);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('name',$name);
				$this->smarty->assign('wdate',$wdate);
				$this->smarty->assign('title',$title);
				$this->smarty->assign('comment',$comment);
				$this->smarty->assign('filename',$filename);
				$this->smarty->assign('filesize',$filesize);
				$this->smarty->assign("page_action","meeting_controller.php");

				if($updatecode=="1")
					$this->smarty->display("intranet/common_contents/work_notice/meeting_input_mvc.tpl");
				else 
					$this->smarty->display("intranet/common_contents/work_notice/meeting_read_mvc.tpl");

			}
		}//function UpdateReadPage()


		//============================================================================
		// 회의록 update logic
		//============================================================================
		function UpdateAction()
		{
			global $db;
			global $compay_value,$title,$password;
			global $comment,$wdate,$ip,$now_day;
			global $company,$tid,$mode,$filename;
			global $userfile,$userfile_name,$userfile_size;
				
			if($company=="") $company=1;	

			$now_day=date("Y-m-d h:i:s");
			$wdate = date ("%Y/%m/%d ");			/* 입력 날짜 저장 */
			$name = trim($name);					/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */
			$homepage = trim($homepage);			/* 홈페이지 앞 뒤 공백 제거 */
			$ip = getenv("REMOTE_ADDR");			/* Remote IP저장 */
			$comment = nl2br($comment); 			/* 내용부분 /n 인식 시키기위한 */
			

			$file_path ="./../../../intranet_file/notice/meeting/".$compay_value."/";
			$file_path_is ="./../../../intranet_file/notice/meeting/".$compay_value;


			$sql4 = "select * from meeting_reference_tbl where tid='$tid'";
			$re4 = mysql_query($sql4,$db);
			$filename = mysql_result($re4, 0, "filename");	

			if($userfile) 
			{
				if($userfile_name <>"" && $userfile_size <>0) 
					{ //첨부파일 있으면서 수정이면

						$filename=iconv("UTF-8", "EUC-KR",$filename);
						$orgfilename = $file_path.$filename;

						$exist_org = file_exists("$orgfilename");
						if($exist_org) 
						{
							
							$re=unlink("$orgfilename");
						}
					}

					$path=$file_path;

					if (is_dir ($file_path_is)){	}
					else{mkdir($file_path_is, 0777);}

					$userfile=stripslashes($userfile);
					$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
					$vupload = $file_path. $_FILES['userfile']['name'];
					$vupload = str_replace(" ","",$vupload); 

					$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
					move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);

					$userfile_size = number_format($userfile_size);
					$filename=$userfile_name;
					$filename = str_replace(" ","",$filename); 

				$sql  = "update meeting_reference_tbl set name='$memberID',title='$title',password='$password',comment='$comment',wdate='$now_day',ip='$ip',company='$compay_value',filename='$filename' ,filesize='$userfile_size' where tid='$tid'";
			}
			else
			{
				$sql  = "update meeting_reference_tbl set name='$memberID',title='$title',password='$password',comment='$comment',wdate='$now_day',ip='$ip',company='$compay_value' where tid='$tid'";
			}

			mysql_query($sql,$db);


			$this->smarty->assign('MoveURL',"meeting_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");

		}//function UpdateAction()


		//============================================================================
		// 회의록 delete logic
		//============================================================================
		function DeleteAction()
		{

			global $db;
			global $name,$title,$password;
			global $comment,$wdate,$ip;
			global $company,$tid;

			$sql="select * from meeting_reference_tbl where tid=$tid";
			$re=mysql_query($sql,$db);
			$idx_imsi = mysql_result($re, 0, "idx");	
			$filename = mysql_result($re, 0, "filename");	
			
			$file_path ="./../../../intranet_file/notice/meeting/".$company."/";

			$filename=iconv("UTF-8", "EUC-KR",$filename);
			$orgfilename = $file_path.$filename;
			$exist_org = file_exists("$orgfilename");
			if($exist_org) 
				{	
					$re=unlink("$orgfilename");
				}
			$sql = "delete from meeting_reference_tbl where tid=$tid";
			mysql_query($sql,$db);
				
		}//function DeleteAction()


		//============================================================================
		// 회의록 list logic
		//============================================================================
		function View()
		{
			
			global $db;
			global $company,$searchv,$memberID,$sub_index;
			global $Start,$page,$currentPage,$last_page;

			if($_SESSION['auth_directormetting'])//임원회의 접근권한
				$this->smarty->assign('auth_directormetting',true);
			else
				$this->smarty->assign('auth_directormetting',false);
			

			if($_SESSION['auth_salesmetting'])//영업회의 접근권한
				$this->smarty->assign('auth_salesmetting',true);
			else
				$this->smarty->assign('auth_salesmetting',false);
			

			if($_SESSION['auth_metting'])//일반회의 접근권한
				$this->smarty->assign('auth_metting',true);
			else
				$this->smarty->assign('auth_metting',false);

			$page=15;



			if($sub_index == "") {
				if($_SESSION['auth_directormetting'])
				{
					$sub_index = 1; 
					if( $company=="") { $company=1; }
				}
				else if(!$_SESSION['auth_directormetting'] and $_SESSION['auth_salesmetting']) 
				{
					$sub_index = 2; 
					if( $company=="") { $company=2; }
				}
				else if(!$_SESSION['auth_directormetting'] and !$_SESSION['auth_salesmetting']) 
				{
					$sub_index = 3; 
					if( $company=="") { $company=3; }
				}
			}

			$company=$sub_index;
			if($company=="") $company=1;


			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array(); 


			if($searchv==""){
				$sql_num="select * from meeting_reference_tbl where company='$company'";
				$sql = "select * from meeting_reference_tbl where company='$company' order by wdate desc limit $Start, $page";
			}
			else
			{
				$sql_num="select * from meeting_reference_tbl where company='$company' and title like '%$searchv%'";
				$sql = "select * from meeting_reference_tbl where company='$company' and title like '%$searchv%' order by wdate desc limit $Start, $page";
			}

			$re_num = mysql_query($sql_num,$db);
			$TotalRow = mysql_num_rows($re_num);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/$page);

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				array_push($query_data,$re_row);
			}
			
			
			if($currentPage == "") $currentPage = 1; 

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			if($_SESSION['auth_directormetting'] and !$_SESSION['auth_salesmetting'])
			{
				$tab_Titel2 = array('임원회의','설계회의','공사회의');
				$tab_value2 = array('1','3','4');
			}else if($_SESSION['auth_directormetting'] and $_SESSION['auth_salesmetting'])
			{
				$tab_Titel2 = array('임원회의','영업회의','설계회의','공사회의');
				$tab_value2 = array('1','2','3','4');

			}else if(!$_SESSION['auth_directormetting'] and $_SESSION['auth_salesmetting'])
			{
				$tab_Titel2 = array('영업회의','설계회의','공사회의');
				$tab_value2 = array('2','3','4');

			}else if(!$_SESSION['auth_directormetting'] and !$_SESSION['auth_salesmetting'])
			{
				$tab_Titel2 = array('설계회의','공사회의');
				$tab_value2 = array('3','4');
			}

			$this->smarty->assign("page_action","meeting_controller.php");
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('company',$company);
			$this->smarty->assign('searchv',$searchv);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('tab_Titel2',$tab_Titel2);
			$this->smarty->assign('tab_value2',$tab_value2);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);
			$this->smarty->display("intranet/common_contents/work_notice/meeting_contents_mvc.tpl");

		}//function View()

	}//class PDSLogic 

?>