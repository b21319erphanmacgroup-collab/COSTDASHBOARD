<?php
	extract($_REQUEST);
	if( $mobile_view != 'data' ){
		?><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><?
	}
	if($_SESSION['mobile'] == 'y'){
		$mobile = $_SESSION['mobile'];
	}
	/***************************************
	* 공지사항
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/
	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../util/notice_to_phone.php";

	$now_day=date("Y-m-d H:i:s");
	$today=date("Y-m-d");

	$CompanyKind=searchCompanyKind();
	$CK_CompanyKind=searchCompanyKind();

	extract($_GET);
	class NoticeLogic {
		var $smarty;

		function NoticeLogic($smarty)
		{
			$this->smarty=$smarty;

			//사이트 주소
			$site_url = array();
			$site_url['1'] = 'http://erp.hanmaceng.co.kr';
			$site_url['2'] = 'http://erp.jangheon.co.kr';
			$site_url['3'] = 'http://erp.pre-cast.co.kr';
			$site_url['4'] = 'http://intranet.hallasanup.com';
			$site_url['5'] = 'http://erp.samaneng.com';
			$site_url['6'] = 'http://erp.baroncs.co.kr';

			$site_url['HANM'] = 'http://erp.hanmaceng.co.kr';
			$site_url['JANG'] = 'http://erp.jangheon.co.kr';
			$site_url['PTC'] = 'http://erp.pre-cast.co.kr';
			$site_url['HALL'] = 'http://intranet.hallasanup.com';
			$site_url['SAMA'] = 'http://erp.samaneng.com';
			$site_url['BARO'] = 'http://erp.baroncs.co.kr';
			//$site_url['TEST'] = 'http://1.234.37.144';
			$this->smarty->assign('site_url_json',json_encode($site_url));
			$this->smarty->assign('site_url',$site_url);
		}//function NoticeLogic($smarty)


		//============================================================================
		// 공지사항 입력 logic
		//============================================================================
		function InsertPage()
		{

			global $db,$memberID;
			global $today,$mode;
			global $GroupCode,$CK_CompanyKind,$CompanyKind;

			$korname=$_SESSION['korName'];

			$korname=MemberNo2Name($memberID);

			$url="notice_controller.php";
			$this->smarty->assign('CK_CompanyKind',$CK_CompanyKind);
			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('pop_start',$today);
			$this->smarty->assign('pop_end',$today);
			$this->smarty->assign('view_start',$today);
			$this->smarty->assign('view_end',$today);

			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('korname',$korname);
			$this->smarty->assign('rankname',memberNoToPositionName($memberID));
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","notice_controller.php");
			$this->smarty->assign('checkTemplate',"1"); //기본값 기타

			$this->smarty->display("intranet/common_contents/work_notice/notice_input.tpl");
		}//function InsertPage()

		//============================================================================
		// 공지사항 미리보기 logic
		//============================================================================
		function temp_page(){
			extract($_REQUEST);
			$id =$_REQUEST[id];
			$notice_sub =$_REQUEST[notice_sub];
			$korname =$_REQUEST[korname];
			$GroupCode =$_REQUEST[GroupCode];
			$pass =$_REQUEST[pass];
			$wdate=date("Y-m-d H:i:s");
			$title=$_REQUEST[title];
			$comment=$_REQUEST[comment];

			if($mode=="read" || $mode=="mod" || $mode=="click" || $mode=="popup" ){
				//$comment = nl2br($comment);
			}
			$comment = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$comment);
			$comment = str_replace('\"','"',$comment);

			$popup=$_REQUEST[popup];
			$forcepopup=$_REQUEST[forcepopup];
			$email=$_REQUEST[email];

			$filename=$_REQUEST[filename];
			$filesize=$_REQUEST[filesize];
			$filesize=round($filesize);


			$tmpfile=explode("/",$filename);
			$no= count($tmpfile)-1;
			$filename_is= $tmpfile[$no];

			/* 년/월/일 분리 부분================================================================*/
			/*
			$view_start=$_REQUEST[view_start];
			$view_end=$_REQUEST[view_end];
			$pop_start=$_REQUEST[pop_start];
			$pop_end=$_REQUEST[pop_end];
			*/
			/* 년/월/일 분리 부분 끝==============================================================*/

			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('company',$company);
			$this->smarty->assign('korname',$korname);
			$this->smarty->assign('pass',$pass);
			$this->smarty->assign('button_type',$button_type);
			$this->smarty->assign('password2',$password2);
			$this->smarty->assign('popup',$popup);
			$this->smarty->assign('email',$email);
			$this->smarty->assign('id',$id);
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('pop',$pop);
			$this->smarty->assign('popup',$popup);
			$this->smarty->assign('wdate',$wdate);
			$this->smarty->assign('title',$title);
			$this->smarty->assign('comment',$comment);
			$this->smarty->assign('filename',$filename);
			$this->smarty->assign('filename_is',$filename_is);
			$this->smarty->assign('filesize',$filesize);
			$this->smarty->assign('view_start',$view_start);
			$this->smarty->assign('view_end',$view_end);
			$this->smarty->assign('pop_start',$pop_start);
			$this->smarty->assign('pop_end',$pop_end);
			$this->smarty->assign('forcepopup',$forcepopup);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('notice_sub',$notice_sub);

			$this->smarty->assign("page_action","notice_controller.php");
			$this->smarty->display("intranet/common_contents/work_notice/notice_read_popup_mvc.tpl");

		}

		//============================================================================
		// 공지사항 insert logic
		//============================================================================
		function InsertAction()
		{
			extract($_REQUEST);

			global $db,$memberID;
			global $title,$button_type,$popup;
			global $comment,$wdate,$ip,$now_day;
			global $GroupCode,$id,$mode,$pass1;
			global $viewoption,$popoption,$forcepopup;
			global $userfile1,$userfile1_name,$filename,$userfile1_size;
			global $userfile,$userfile_name,$userfile_size;
			global $view_start,$view_end,$pop_start,$pop_end,$CompanyKind;
			global $uphanmac,$upfiletech,$upjangheon,$korname,$view_start,$view_end;

			$dbAction = true;	//	true	false

			$wdate = date ("%Y/%m/%d");				/* 입력 날짜 저장 */
			$name = trim($name);							/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */
			$homepage = trim($homepage);			/* 홈페이지 앞 뒤 공백 제거 */
			$ip = getenv("REMOTE_ADDR");				/* Remote IP저장 */
			//$comment = nl2br($comment); 			/* 내용부분 /n 인식 시키기위한 */


			if ($popoption=="on")
			{
			}else
			{
				$pop_start="";
				$pop_end="";
			}

			if ($viewoption=="on")
			{
				$view_end="2099-12-31";
			}else
			{
				$view_start="";
				$view_end="";
			}

			if($GroupCode=="0" || $GroupCode=="" )
			{
				$GroupCode="99";
			}


			//***********************************************************************************************//
			//*******************첨부파일 첨부 부분(공지올리는 회사 서버에 저장)********************//
			//***********************************************************************************************//

			if($GroupCode=="99") //전체공지
			{
				$path ="./../../../intranet_file/notice/noticefile/";
				$path_is ="./../../../intranet_file/notice/noticefile";
			} //부서별
			else
			{
				$path ="./../../../intranet_file/notice/noticefile/".$GroupCode."/";
				$path_is ="./../../../intranet_file/notice/noticefile/".$GroupCode;
			}

			$userfile_name = str_replace(" ","",$userfile_name);
			if($file_delete_check == 'Y'){
				$userfile_name = "";
			}
			if ($userfile_name <>"" && $userfile_size <>0)
			{
				if (is_dir ($path_is))
				{
				}
				else
				{
					mkdir($path_is, 0777);
				}
				/*
				$filename=iconv("UTF-8", "EUC-KR",$userfile_name);
				$orgfilename = $path.$filename;
				$exist_org = file_exists("$orgfilename");

				if($exist_org) {
						echo(" <script>
								  window.alert('\"$userfile_name\" 이미 존재합니다.')
								  history.go(-1)
								 </script>
							   ");exit;
							}
				*/

				$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
				$vupload = $path.time()."_".$_FILES['userfile']['name'];
				$vupload = str_replace(" ","",$vupload);
				$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
				move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
				$userfile_size = number_format($userfile_size);

				if($GroupCode=="99") //전체공지
					$filename="./noticefile/".time()."_".$userfile_name;
				else
					$filename="./noticefile/".$GroupCode."/".time()."_".$userfile_name;

				$filename = str_replace(" ","",$filename);
			}
			else
			{
				$filename="";
				$userfile_size="";
			}

			//*********************************************************************************************//
			//********************************추가 공지 회사 표시 부분********************************//
			//********************************************************************************************//

			if($GroupCode=="99"){
				//email : 공지를 입력한 회사
				//home : 공지가 같이 올라가는 회사
				$home="";
				//바론
				$email="6";
				$site_url = "http://erp.baroncs.co.kr";

				/*
				//한맥
					$email="1";
					$site_url = "http://erp.hanmaceng.co.kr";
				//장헌
					$email="2";
					$site_url = "http://erp.jangheon.co.kr";
				//PTC
					$email="3";
					$site_url = "http://erp.pre-cast.co.kr";
				//한라
					$email="4";
					$site_url = "http://intranet.hallasanup.com";
				//삼안
					$email="5";
					$site_url = "http://erp.samaneng.com";
				*/

				if($uphanmac =="on"){ $home .= "/H"; }
				if($upjangheon =="on"){ $home .= "/J"; }
				if($upfiletech =="on"){ $home .= "/P"; }
				if($uphalla =="on"){ $home .= "/L"; }
				if($upsaman =="on"){ $home .= "/S"; }
				if($upbaron =="on"){ $home .= "/B"; }
			}

			/*테스트 데이터*/
			if( $memberID == 'M20330' ){
				//$view_start = '2021-09-20';
				//$view_end = '2021-09-20';
			}
			/*테스트 데이터*/

			//익스로 입력시 들어가는 오류코드 제거
			$comment = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$comment);
			$comment = str_replace("/summernote-master/Upload/notice/",$site_url."/summernote-master/Upload/notice/",$comment);

			$dbinsert="insert into notice_new_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup, MemberNo)";
			$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$comment','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup', '$memberID')";

			$result = mysql_query($dbinsert,$db);
			if( !$dbAction ){
				echo $dbinsert.'<br>';
			}

			$azsql = "select id from notice_new_tbl where MemberNo = '$memberID' order by id desc limit 1";
			if( !$dbAction ){
				echo $azsql.'<br>';
			}
			$re = mysql_query($azsql,$db);
			$auth_pk = mysql_result($re,0,"id");

			if($GroupCode=="99"){
				$sql_other = "insert into notice_new_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup, MemberNo, auth_pk)";
				$sql_other = $sql_other." values('$korname','$email','$home','$pass1','$title','$comment','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup', '$memberID', '$auth_pk')";
				if( !$dbAction ){
					echo $sql_other.'<br>';
				}

				$this->set_intranet($dbAction, $home, $sql_other);

				if( $email == "1" ){
					//문자발송 클래스
					//echo '문자발송 : '.$auth_pk;
					$sms_send = new Synchronization();
					$sms_send->Process($auth_pk);

				}
			}


			$this->smarty->assign('MoveURL',"notice_controller.php?ActionMode=view&notice_sub=$notice_sub");
			if( $dbAction ){
				$this->smarty->display("intranet/move_page.tpl");
			}

		}//function InsertAction()

		//============================================================================
		// 공지사항 읽기 logic
		//============================================================================
		function UpdateReadPage()
		{
			global $db,$memberID;
			global $mode,$page,$currentPage,$pop;
			global $company,$id,$now_day,$button_type;
			global $pass1,$updatecode,$password2,$GroupCode;
			global $korname,$popoption,$forcepopup,$CompanyKind;
			global $view_start,$view_end,$pop_start,$pop_end;
			global $mobile;

			if( $mode == 'confirm_page' ){
				$azsql = "select id from notice_new_tbl where MemberNo = '$memberID' order by id desc limit 1";
				$re = mysql_query($azsql,$db);
				$id = mysql_result($re,0,"id");
			}


			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$this->smarty->assign('Auth',true);
			}else{
				$this->smarty->assign('Auth',false);
			}
			/*
			if( $_SESSION['auth_manager'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);
			*/

			$query_data = array();

			/*조회수 증가================================================================*/
			if( $mode=="click" ){
				//$update=mysql_query("update notice_new_tbl set see=see+1 where id=$id",$db);


					$sql="select * from member_tbl where MemberNo='$memberID'";
					$result=mysql_query($sql,$db);
					$row=mysql_fetch_array($result);
					$KorName=$row[korName];
					$RankCode=$row[RankCode];
					$GroupCode=$row[GroupCode];


					$sql2="select * from notice_read_tbl where notice_id='$id' and MemberNo='$memberID'";
					//echo $sql2."<br>";
					$re2 = mysql_query($sql2,$db);
					if(mysql_num_rows($re2) == 0)
					{
						$sql3="insert into notice_read_tbl (notice_id,MemberNo,korName,RankCode,GroupCode,ReadDate)";
						$sql3=$sql3."values($id,'$memberID','$KorName','$RankCode','$GroupCode',now())";

						//echo $sql3."<br>";
						mysql_query($sql3,$db);
					}



			}
			/*조회수 증가 끝==============================================================*/

			$sql = "select * from notice_new_tbl where id='$id'";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				$id =$re_row[id];
				$korname =$re_row[name];
				$GroupCode =$re_row[group_code];
				$pass =$re_row[pass];
				$wdate=$re_row[wdate];
				$title=$re_row[title];

				$auth_pk=$re_row[auth_pk];

				if ($korname=="")
				{
					$korname=MemberNo2Name($memberID);
				}
				$comment=$re_row[comment];

				if($mode=="read" || $mode=="mod" || $mode=="click" || $mode=="popup" || ($_COOKIE['CK_CompanyKind']!="SAMA" && $auth_pk=="")){
					$comment = nl2br($comment);
				}

				$popup=$re_row[popup];
				$forcepopup=$re_row[forcepopup];
				$email=$re_row[email];

				$filename=$re_row[filename];
				$filesize=$re_row[filesize];
				$filesize=round($filesize);




				//첨부파일다운관련-----------------------------------------
				// 				$tmpfile=explode("/",$filename);
				// 				$no= count($tmpfile)-1;
				// 				$filename_is= $tmpfile[$no];
				$filename_is = str_replace("./noticefile","", $filename);

				$tmpfile=explode("/",$filename);
				$no= count($tmpfile)-1;
				$filename_only= $tmpfile[$no];
				//-----------------------------------------

				$home = $re_row[home];
				if (strpos($home,"/H") === false){}else{ $this->smarty->assign('home_H',true); } //한맥
				if (strpos($home,"/J") === false){}else{ $this->smarty->assign('home_J',true); } //장헌
				if (strpos($home,"/P") === false){}else{ $this->smarty->assign('home_P',true); } //파일
				if (strpos($home,"/L") === false){}else{ $this->smarty->assign('home_L',true); } //한라
				if (strpos($home,"/S") === false){}else{ $this->smarty->assign('home_S',true); } //삼안
				if (strpos($home,"/B") === false){}else{ $this->smarty->assign('home_B',true); } //바론


				/* 년/월/일 분리 부분================================================================*/
				$view_start=$re_row[view_start];
				$view_end=$re_row[view_end];
				$pop_start=$re_row[pop_start];
				$pop_end=$re_row[pop_end];
				/* 년/월/일 분리 부분 끝==============================================================*/

				$auth_pk=$re_row[auth_pk];
				
				$this->smarty->assign('InsertMember',$re_row['MemberNo']);

				$this->smarty->assign('memberID',$memberID);//20150427 : 추가(memberID)
				
				
				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('company',$company);
				$this->smarty->assign('korname',$korname);
				$this->smarty->assign('pass',$pass);
				$this->smarty->assign('button_type',$button_type);
				$this->smarty->assign('password2',$password2);
				$this->smarty->assign('popup',$popup);
				$this->smarty->assign('email',$email);
				$this->smarty->assign('id',$id);
				$this->smarty->assign('GroupCode',$GroupCode);
				$this->smarty->assign('pop',$pop);
				$this->smarty->assign('popup',$popup);
				$this->smarty->assign('wdate',$wdate);
				$this->smarty->assign('title',$title);
				$this->smarty->assign('comment',$comment);

				$this->smarty->assign('filename',$filename);
				$this->smarty->assign('filename_is',$filename_is);
				$this->smarty->assign('filename_only',$filename_only);
				$this->smarty->assign('filesize',$filesize);

				$this->smarty->assign('view_start',$view_start);
				$this->smarty->assign('view_end',$view_end);
				$this->smarty->assign('pop_start',$pop_start);
				$this->smarty->assign('pop_end',$pop_end);
				$this->smarty->assign('forcepopup',$forcepopup);
				$this->smarty->assign('currentPage',$currentPage);
				$this->smarty->assign('auth_pk',$auth_pk);


				$this->smarty->assign("page_action","notice_controller.php");

				if($button_type=="insert_ok")
					$this->smarty->display("intranet/common_contents/work_notice/notice_input.tpl");
				else
					if($mode=="popup" || $mode=="click" || $mode=="confirm_page" )
						$this->smarty->display("intranet/common_contents/work_notice/notice_read_popup_mvc.tpl");
					else if($mobile=="y")
						$this->smarty->display("intranet/common_contents/work_notice/notice_read_mobile_mvc.tpl");
					else
						$this->smarty->display("intranet/common_contents/work_notice/notice_read_mvc.tpl");

			}

		}//function UpdateReadPage()


		//============================================================================
		// 공지사항 update logic
		//============================================================================
		function UpdateAction()
		{

			global $db,$memberID;
			global $name,$title,$pass;
			global $comment,$wdate,$ip,$GroupCode;
			global $pass1,$id,$mode,$filename;
			global $userfile,$userfile_name,$userfile_size;
			global $popoption,$forcepopup,$korname,$viewoption;
			global $pop_start,$pop_end;
			global $view_start,$view_end,$CompanyKind;
			global $uphanmac,$upfiletech,$upjangheon, $email;

			$dbAction = true;	//	true	false

			$wdate = date ("%Y/%m/%d ");			/* 입력 날짜 저장 */
			$name = trim($name);					/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */
			$homepage = trim($homepage);			/* 홈페이지 앞 뒤 공백 제거 */
			$ip = getenv("REMOTE_ADDR");			/* Remote IP저장 */
			//$comment = nl2br($comment); 			/* 내용부분 /n 인식 시키기위한 */
			$file_path ="./../../../intranet_file/notice";

			if ($popoption=="on")
			{

			}else
			{
				$pop_start="";
				$pop_end="";
			}

			if ($viewoption=="on")
			{
				$view_end="2099-12-31";

			}else
			{
				$view_start="";
				$view_end="";
			}

			if($GroupCode=="0" || $GroupCode=="" )
			{
				$GroupCode="99";
			}

			if($GroupCode=="99") //전체공지
			{
				$path ="./../../../intranet_file/notice/noticefile/";
				$path_is ="./../../../intranet_file/notice/noticefile";
			} //부서별
			else
			{
				$path ="./../../../intranet_file/notice/noticefile/".$GroupCode."/";
				$path_is ="./../../../intranet_file/notice/noticefile/".$GroupCode;
			}


			if($id){
				$result=mysql_query("select * from notice_new_tbl where id=$id",$db);
				$row=mysql_fetch_array($result);

				$file_name9=$row[filename];
				$file_name8 = explode("/", $file_name9);
				$pass9=$row[pass];
				$home9=$row[home];
				$title9=$row[title];
				$wdate9=$row[wdate];
				$group_code9=$row[group_code];
				$popup9=$row[popup];
				$pop_start9=$row[pop_start];
				$pop_end9=$row[pop_end];
				$filesize9=$row[filesize];

				$wdate9=explode(" ",$wdate9);
				$wdate9=$wdate9[0];

				if($file_name9)
				{
					if($userfile_name <>"" && $userfile_size <>0)
					{ //첨부파일 있으면서 수정이면
							$orgfilename = $path.$file_name8[2];
							$orgfilename=iconv("UTF-8", "EUC-KR",$orgfilename);
							$exist_org = file_exists("$orgfilename");
							if($exist_org)
							{
								$re=unlink("$orgfilename");
							}
					}

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

						if($exist_org) {
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

						if($GroupCode=="99") //전체공지
							$filename="./noticefile/".$userfile_name;
						else
							$filename="./noticefile/".$GroupCode."/".$userfile_name;

						$filename = str_replace(" ","",$filename);
					}
					else
					{
						$filename="";
						$userfile_size="";
					}
				}

				if($GroupCode=="99"){
					//email : 공지를 입력한 회사
					if($email=="1"){
						$site_url = "http://erp.hanmaceng.co.kr";
					}else if($email=="2"){
						$site_url = "http://erp.jangheon.co.kr";
					}else if($email=="3"){
						$site_url = "http://erp.pre-cast.co.kr";
					}else if($email=="4"){
						$site_url = "http://intranet.hallasanup.com";
					}else if($email=="5"){
						$site_url = "http://erp.samaneng.com";
					}else if($email=="6"){
						$site_url = "http://erp.baroncs.co.kr";
					}
				}

				/*테스트 데이터*/
				//$view_start = '2021-09-20';
				//$view_end = '2021-09-20';
				/*테스트 데이터*/

				$comment = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$comment);
				$comment = str_replace($site_url."/summernote-master/Upload/notice/", "/summernote-master/Upload/notice/",$comment);
				$comment = str_replace("/summernote-master/Upload/notice/", $site_url."/summernote-master/Upload/notice/",$comment);

				$dbup="
					update notice_new_tbl set
					name='$korname'
					,pass='$pass1'
					,title='$title'
					,comment='$comment'
					,popup='$popoption'
					,view_start='$view_start'
					,view_end='$view_end'
					,pop_start='$pop_start'
					,pop_end='$pop_end'
					,forcepopup='$forcepopup'
				";
				if($filename<>""){
					$dbup = $dbup." ,filename='$filename', filesize='$userfile_size' ";
				}
				$dbup_main = $dbup." where id = '$id' ";
				$dbup_other = $dbup." where email = '$email' and auth_pk = '$id' ";

				if( $dbAction ){
					$result = mysql_query($dbup_main,$db);
				}else{
					echo $dbup_main.'<br>';
				}

				if($GroupCode=="99"){
					$this->set_intranet($dbAction, $home9, $dbup_other);
				}
			}

			if( $dbAction ){
				$this->smarty->assign('MoveURL',"notice_controller.php?ActionMode=view");
				$this->smarty->display("intranet/move_page.tpl");
			}

		}//function UpdateAction()


		//============================================================================
		// 공지사항 delete logic
		//============================================================================
		function DeleteAction()
		{

			global $db,$memberID;
			global $id,$title,$password;
			global $comment,$wdate,$ip,$filename;

			$dbAction = true;	//	true	false

			$sql="select * from notice_new_tbl where id=$id";
			$re = mysql_query($sql,$db);

			$filename= mysql_result($re,0,"filename");
			$email= mysql_result($re,0,"email");
			$home= mysql_result($re,0,"home");
			$GroupCode= mysql_result($re,0,"group_code");

			if($filename <> "")
			{
				$file_path ="./../../../intranet_file/notice";
				$filename=iconv("UTF-8", "EUC-KR",$filename);
				$orgfilename = $file_path.$filename;
				$exist_org = file_exists("$orgfilename");

				if($exist_org)
				{
					$re=unlink("$orgfilename");
				}
			}

			$db_del="
				delete from notice_new_tbl
			";
			$db_del_main = $db_del." where id = '$id' ";
			$db_del_other = $db_del." where email = '$email' and auth_pk = '$id' ";

			if( $dbAction ){
				$result = mysql_query($db_del_main,$db);
			}else{
				echo $db_del_main.'<br>';
			}

			if($GroupCode=="99"){
				$this->set_intranet($dbAction, $home, $db_del_other);
			}

			if( $dbAction ){
				$this->smarty->assign('MoveURL',"notice_controller.php?ActionMode=view");
				$this->smarty->display("intranet/move_page.tpl");
			}
		}


		//============================================================================
		//  공지사항 확인 logic
		//============================================================================
		function UpdateRead()
		{

			global $db,$memberID;
			global $id;



			$sql="select * from member_tbl where MemberNo='$memberID'";
			$result=mysql_query($sql,$db);
			$row=mysql_fetch_array($result);
			$KorName=$row[korName];
			$RankCode=$row[RankCode];
			$GroupCode=$row[GroupCode];

			$sql2="select * from notice_read_tbl where notice_id='$id' and MemberNo='$memberID'";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			if(mysql_num_rows($re2) == 0)
			{
				$sql3="insert into notice_read_tbl (notice_id,MemberNo,korName,RankCode,GroupCode,ReadDate)";
				$sql3=$sql3."values($id,'$memberID','$KorName','$RankCode','$GroupCode',now())";

				//echo $sql3."<br>";
				mysql_query($sql3,$db);
			}


			$this->smarty->assign('target',"no");
			$this->smarty->display("intranet/move_page.tpl");
		}



		//============================================================================
		// 공지사항 리스트 보기
		//============================================================================
		function View()
		{


			global $db,$memberID;
			global $today,$searchv,$GroupCode;
			global $button_type,$Start,$page;
			global $currentPage,$last_page,$sub_index;
			global $mobile, $mobile_view;
			if( $_SESSION["memberID"] ){
				$memberID = $_SESSION["memberID"];
			}

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무') || $PersonAuthority->GetInfo($memberID,'공지')){
				$this->smarty->assign('Auth',true);
			}else{
				$this->smarty->assign('Auth',false);
			}
			
			/*
			if( $_SESSION['auth_manager'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);
			*/

			$page=15;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();


			/* 팝업 체크 부분 ---------------------------------------------------------------- */
			$all_groupcode="99";
			$GroupCode=$_SESSION['MyGroupCode'];
			if($GroupCode=="")
			{
				$GroupCode=MemberNo2Group($memberID);
			}

			/*
			$sql="select * from notice_new_tbl  where group_code in('$GroupCode','$all_groupcode') and ((popup='on' and pop_start <= '$today' and pop_end >= '$today') and ((view_start <= '$today' and view_end >= '$today') or (view_start is null  and view_end is null) or (view_start='0000-00-00' and view_end='0000-00-00'))) and level like 'a%' order by id desc";

			$popresult=mysql_query($sql,$db);
			$index=30;
			$topv=10;
			while($pop_row=mysql_fetch_array($popresult))
			{
				 if($pop_row[forcepopup]=="on"){

				 		$this->smarty->assign('mode',"noticeopen");
						$this->smarty->assign('id',$pop_row[id]);
						$this->smarty->assign('memberID',$memberID);
						$this->smarty->assign('level',$pop_row[level]);
						$this->smarty->assign('index',$index);
						$this->smarty->assign('topv',$topv);
						$this->smarty->display("intranet/js_page.tpl");

						$index=$index+30;
						$topv=$topv+30;
				}
				else
				{
					$ssql="select * from notice_read_tbl where notice_id='$pop_row[id]' and MemberNo='$memberID'";
					$presult=mysql_query($ssql,$db);
					$presult_row=mysql_num_rows($presult);

					if($presult_row <= 0 )
					{

						$this->smarty->assign('mode',"noticeopen");
						$this->smarty->assign('id',$pop_row[id]);
						$this->smarty->assign('memberID',$memberID);
						$this->smarty->assign('level',$pop_row[level]);
						$this->smarty->assign('index',$index);
						$this->smarty->assign('topv',$topv);
						$this->smarty->display("intranet/js_page.tpl");

						$index=$index+30;
						$topv=$topv+30;
					}
				}
			}
			*/
			/* 팝업 체크 부분 ---------------------------------------------------------------- */



			if($searchv=="")
			{
				
				$sql_num="select * from notice_new_tbl where ";
				if($GroupCode == "31" || $GroupCode == "33"){
					$sql_num.="group_code in('31','33','$all_groupcode') ";
				}else{
					$sql_num.="group_code in('$GroupCode','$all_groupcode') ";
				}
				
				$sql_num.="and ((view_start <= '$today' and view_end >= '$today') or (view_start is null and view_end is null) or (view_start='0000-00-00' and view_end='0000-00-00') or MemberNo = '$memberID')";

				$sql_num = mysql_query($sql_num,$db);
				$TotalRow = mysql_num_rows($sql_num);//총 개수 저장
				$last_start = ceil($TotalRow/10)*10+1;
				$last_page=ceil($TotalRow/$page);


				$sql="select * from notice_new_tbl where ";
				if($GroupCode == "31" || $GroupCode == "33"){
					$sql.="group_code in('31','33','$all_groupcode') ";
				}
				else{
					$sql.="group_code in('$GroupCode','$all_groupcode') ";
				}
				$sql.="and ((view_start <= '$today' and view_end >= '$today') or (view_start is null and view_end is null) or (view_start='0000-00-00' and view_end='0000-00-00') or MemberNo = '$memberID') order by id desc limit $Start, $page";
				$re = mysql_query($sql,$db);

			}else{
				if($sub_index=="1"){ //제목

					//$sql_num="select * from notice_new_tbl where group_code in('$GroupCode','$all_groupcode') and level like 'a%' and title like '%$searchv%'";
					$sql_num="select * from notice_new_tbl where ";
					if($GroupCode == "31" || $GroupCode == "33"){
						$sql_num.="group_code in('31','33','$all_groupcode') ";
					}else{
						$sql_num.="group_code in('$GroupCode','$all_groupcode') ";
					}
					$sql_num.="and level like 'a%' and title like '%$searchv%'";

					//$sql="select * from notice_new_tbl where group_code in('$GroupCode','$all_groupcode') and level like 'a%' and title like '%$searchv%' order by id desc  limit $Start, $page";
					$sql="select * from notice_new_tbl where ";
					if($GroupCode == "31" || $GroupCode == "33"){
						$sql.="group_code in('31','33','$all_groupcode') ";
					}else{
						$sql.="group_code in('$GroupCode','$all_groupcode') ";
					}
					$sql.="and level like 'a%' and title like '%$searchv%' order by id desc  limit $Start, $page";
				}else{ //내용

					//$sql_num="select * from notice_new_tbl where group_code in('$GroupCode','$all_groupcode') and level like 'a%' and comment like '%$searchv%'";
					$sql_num="select * from notice_new_tbl where ";
					if($GroupCode == "31" || $GroupCode == "33"){
						$sql_num.="group_code in('31','33','$all_groupcode') ";
					}else{
						$sql_num.="group_code in('$GroupCode','$all_groupcode') ";
					}
					$sql_num.="and level like 'a%' and comment like '%$searchv%'";

					//$sql="select * from notice_new_tbl where group_code in('$GroupCode','$all_groupcode') and level like 'a%' and comment like '%$searchv%' order by id desc  limit $Start, $page";
					$sql="select * from notice_new_tbl where ";
					if($GroupCode == "31" || $GroupCode == "33"){
						$sql.="group_code in('31','33','$all_groupcode') ";
					}else{
						$sql.="group_code in('$GroupCode','$all_groupcode') ";
					}
					$sql.="and level like 'a%' and comment like '%$searchv%' order by id desc  limit $Start, $page";
				}


				//$re = mysql_query($sql,$db);
				$re_num = mysql_query($sql_num,$db);
				$TotalRow = mysql_num_rows($re_num);//총 개수 저장
				$last_start = ceil($TotalRow/10)*10+1;
				$last_page=ceil($TotalRow/$page)+1;

				$re = mysql_query($sql,$db);
			}


			while($re_row = mysql_fetch_array($re))
			{

				//if(substr($re_row[wdate],0,10)==$today){
				if(substr($re_row[wdate],0,10)==$today or $re_row[view_start]==$today or $re_row[pop_start]==$today){
						$re_row[newicon]=true;
				}else
				{
						$re_row[newicon]=false;
				}

				$tmpfile=explode("/",$re_row[filename]);
				$no= count($tmpfile)-1;
				$re_row[filename_is]= $tmpfile[$no];

				//첨부파일다운관련-----------------------------------------
				// 				$tmpfile=explode("/",$re_row[filename]);
				// 				$no= count($tmpfile)-1;
				// 				$re_row[filename_is]= $tmpfile[$no];
				$re_row[filename_is] = str_replace("./noticefile","", $re_row[filename]);
				//-----------------------------------------





				$sqlcount="select count(*) as see from notice_read_tbl where notice_id='$re_row[id]' and  MemberNo <>''";
				$recount = mysql_query($sqlcount,$db);
				if(mysql_num_rows($recount) > 0)
				{
					$re_row[see]=mysql_result($recount,0,"see");
				}
				array_push($query_data,$re_row);
			}


			if($currentPage == "") $currentPage = 1;

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);

			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			$this->smarty->assign("page_action","notice_controller.php");

			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('button_type',$button_type);
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('searchv',$searchv);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);
			$this->smarty->assign('sub_index',$sub_index);

			if( $mobile_view == 'data' ){
				echo json_encode($query_data);
			}elseif($mobile=="y"){
				$this->smarty->display("intranet/common_contents/work_notice/notice_contents_mobile_mvc.tpl");
			}else{
				$this->smarty->display("intranet/common_contents/work_notice/notice_contents_mvc.tpl");
			}
		}

		//============================================================================
		// 공지사항 읽은사람 리스트 보기
		//============================================================================
		function Readlist()
		{

			global $db,$memberID;
			global $id;

			$query_data = array();
			$sql="select aa.*,bb.Name from
			(
				select a.*,b.title from
				(
					select * from notice_read_tbl where notice_id='$id' and  MemberNo <>'' order by GroupCode,RankCode
				)a left join
				(
					select * from notice_new_tbl where id='$id'
				)b on a.notice_id=b.id
			)aa left join
			(
				select * from systemconfig_tbl where SysKey='GroupCode'
			)bb on aa.GroupCode=bb.Code";


			//echo $sql."<Br>";

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				$title=$re_row[titile];
				array_push($query_data,$re_row);
			}
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('title',$title);
			$this->smarty->display("intranet/common_contents/work_notice/notice_readlist_mvc.tpl");


		}

		//각회사별 셋팅
		function set_intranet($dbAction, $home, $sql_other){
			//*************************한맥에 함께 공지되었던 기록이 있으면*************************//
			if (strpos($home,"/H") === false){}else{
				$host="192.168.10.5";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$hanmac_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$hanmac_db);
				//mysql_query("set names euckr");
				//mysql_set_charset("utf-8",$hanmac_db);
				mysql_query("set names utf8");

				if( $dbAction ){
					$result = mysql_query($sql_other,$hanmac_db);
				}else{
					echo $sql_other.'<br>';
				}
				mysql_close($hanmac_db);
			}

			//*************************장헌에 함께 공지되었던 기록이 있으면*************************//
			if (strpos($home,"/J") === false){}else{
				///////////장헌DB//////////////////////
				$host="192.168.10.6";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$jangheon_db=mysql_connect($host,$user,$pass);
				mysql_select_db("$dataname",$jangheon_db);
				//mysql_query("set names euckr");
				//mysql_set_charset("utf-8",$jangheon_db);
				mysql_query("set names utf8");

				if( $dbAction ){
					$result = mysql_query($sql_other,$jangheon_db);
				}else{
					echo $sql_other.'<br>';
				}
				mysql_close($jangheon_db);
			}

			//*************************파일에 함께 공지되었던 기록이 있으면*************************//
			if (strpos($home,"/P") === false){}else{
				$host="192.168.10.7";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$piletech_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$piletech_db);
				//mysql_query("set names euckr");
				//mysql_set_charset("utf-8",$piletech_db);
				mysql_query("set names utf8");

				if( $dbAction ){
					$result = mysql_query($sql_other,$piletech_db);
				}else{
					echo $sql_other.'<br>';
				}
				mysql_close($piletech_db);
			}

			//*************************한라에 함께 공지되었던 기록이 있으면*************************//
			if (strpos($home,"/L") === false){}else{
				$host="1.234.37.143:3306";
				$user="root";
				$pass="vbxsystem";
				$dataname="hallaerp";
				$halla_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$halla_db);
				//mysql_query("set names euckr");
				//mysql_set_charset("utf-8",$halla_db);
				mysql_query("set names utf8");

				if( $dbAction ){
					$result = mysql_query($sql_other,$halla_db);
				}else{
					echo $sql_other.'<br>';
				}
				mysql_close($halla_db);
			}

			//*************************삼안에 함께 공지되었던 기록이 있으면*************************//
				if (strpos($home,"/S") === false){}else{
				$host="erp.samaneng.com:3306";
				$user="root";
				$pass="vbxsystem";
				$dataname="hallaerp";
				$saman_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$saman_db);
				//mysql_query("set names euckr");
				//mysql_set_charset("utf-8",$saman_db);
				mysql_query("set names utf8");

				if( $dbAction ){
					$result = mysql_query($sql_other,$saman_db);
				}else{
					echo $sql_other.'<br>';
				}
				mysql_close($saman_db);
			}

		}

}
?>