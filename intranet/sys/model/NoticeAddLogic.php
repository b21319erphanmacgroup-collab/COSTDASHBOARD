<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

	/***************************************
	* 공지사항
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/
	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";


	$now_day=date("Y-m-d H:i:s");
	$today=date("Y-m-d");


	extract($_GET);
	class NoticeAddLogic {
		var $smarty;

		function NoticeAddLogic($smarty)
		{
			$this->smarty=$smarty;
		}//function NoticeAddLogic($smarty)


		//============================================================================
		// 공지사항 입력 logic
		//============================================================================
		function InsertPage()
		{

			global $db,$memberID;
			global $today,$mode,$korname,$notice_sub;
			global $GroupCode,$CK_CompanyKind;

			$CK_CompanyKind=$_SESSION['CK_CompanyKind'];

			$sql="select a.korName,a.ViewRankName,b.Name as RankName from
				(
					select * from member_tbl where MemberNo='$memberID'
				)a left join
				(
					select * from systemconfig_tbl where SysKey='PositionCode'
				)b on a.RankCode=b.Code";
		//echo $sql;
			$re = mysql_query($sql,$db);
			if(mysql_num_rows($re) > 0)
			{
				$korName= mysql_result($re,0,"korName");
				$ViewRankName= mysql_result($re,0,"ViewRankName");
				$RankName= mysql_result($re,0,"RankName");
				if($ViewRankName=="")
				{
					$korname=$korName." ".$RankName;
				}else
				{
					$korname=$korName." ".$ViewRankName;
				}

			}
			else
			{
				$korname=$_SESSION['korName'];
			}

			$url="noticeAdd_controller.php";
			$this->smarty->assign('CK_CompanyKind',$CK_CompanyKind);
			$this->smarty->assign('pop_start',$today);
			$this->smarty->assign('pop_end',$today);
			$this->smarty->assign('view_start',$today);
			$this->smarty->assign('view_end',$today);
			$this->smarty->assign('notice_sub',$notice_sub);

			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('korname',$korname);
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","noticeAdd_controller.php");

			
			
			
			
			
// 			$host_halla = "1.234.37.143";//한라
// 			$host_saman = "118.220.172.237";//삼안
// 			$host = $host_halla;
// 			$user="root";
// 			$pass="vbxsystem";
//             $dataname="hallaerp"; 
			
// 			/*
// 			$host_jang = "192.168.10.6";
// 			$host=$host_jang;
// 			$user="root";
// 			$pass="erp";
// 			$dataname="hanmacerp";
// 			*/
			
// 			$conn_db=mysql_connect($host,$user,$pass);
			
// 			if(!$conn_db){ 
// 				die ("Unable to connect to MySql : ".mysql_error());
// 			}else{
// 				//echo "CON";
// 			};
// 			mysql_select_db($dataname,$conn_db);

			$this->smarty->assign('CompanyKind',$_SESSION['CK_CompanyKind']);
			
			$this->smarty->display("intranet/common_contents/work_noticeAdd/notice_input.tpl");
		}//function InsertPage()


		//============================================================================
		// 공지사항 insert logic
		//============================================================================
		function InsertAction_file()
		{
			extract($_REQUEST);
			global $db,$memberID;
			global $title,$button_type,$popup;
			global $wdate,$ip,$now_day;
			global $subtext;
			global $GroupCode,$id,$mode,$pass1;
			global $viewoption,$popoption,$forcepopup;
			global $userfile1,$userfile1_name,$filename,$userfile1_size;
			global $userfile,$userfile_name,$userfile_size;
			global $view_start,$view_end,$pop_start,$pop_end;
			global $uphanmac,$upfiletech,$upjangheon,$korname,$view_start,$view_end,$notice_sub;

			$wdate = date ("%Y/%m/%d");				/* 입력 날짜 저장 */
			$name = trim($name);							/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */
			$homepage = trim($homepage);			/* 홈페이지 앞 뒤 공백 제거 */
			$ip = getenv("REMOTE_ADDR");				/* Remote IP저장 */
			//$comment = nl2br($comment); 			/* 내용부분 /n 인식 시키기위한 */

			//+++++++++++++++++++++++++++++++++++++++++++++++++++
			if($_SESSION['CK_CompanyKind']=="HANM"){
				$uphanmac ="on";
			}else if($_SESSION['CK_CompanyKind']=="JANG"){
				$upjangheon ="on";
			}else if($_SESSION['CK_CompanyKind']=="PILE"){
				$upfiletech ="on";
			}else if($_SESSION['CK_CompanyKind']=="HALL"){
				$uphalla ="on";
			}else if($_SESSION['CK_CompanyKind']=="SAMA"){
				$upsaman ="on";
			}
			$insert_company = array();
			if($uphanmac =="on"){
				array_push($insert_company,'HANM');
			}
			if($upjangheon =="on"){
				array_push($insert_company,'JANG');
			}
			if($upfiletech =="on"){
				array_push($insert_company,'PILE');
			}
			if($uphalla =="on"){
				array_push($insert_company,'HALL');
			}
			if($upsaman =="on"){
				array_push($insert_company,'SAMA');
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++
			$cnt_company = count($insert_company);
			//+++++++++++++++++++++++++++++++++++++++++++++++++++
			
			
			if($GroupCode=="99") //전체공지
			{
				$path ="./../../../intranet_file/notice/temp/";
				$path_is ="./../../../intranet_file/notice/temp";
// 				$path ="./../../../intranet_file/notice/noticefile/";
// 				$path_is ="./../../../intranet_file/notice/noticefile";
			} //부서별
			else
			{
				$path ="./../../../intranet_file/notice/temp/";
				$path_is ="./../../../intranet_file/notice/temp/";
// 				$path ="./../../../intranet_file/notice/noticefile/".$GroupCode."/";
// 				$path_is ="./../../../intranet_file/notice/noticefile/".$GroupCode;
			}
			//---------------------------------------------------------------------------------
			$userfile_name = str_replace(" ","",$userfile_name);
			if($file_delete_check == 'Y'){
				$userfile_name = "";
			}
			
			$V_userfile_name='';
			
			//---------------------------------------------------------------------------------
			$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
			$userfile = $_FILES['userfile']['name'];
			//---------------------------------------------------------------------------------
			$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
			$userfile_tmp = $_FILES['userfile']['tmp_name'];
			//---------------------------------------------------------------------------------
			if ($userfile_name <>"" && $userfile_size <>0)
			{
				if (is_dir ($path_is)){
					echo "userfile=".$userfile_name;
					//$userfile = iconv("UTF-8","EUC-KR",$userfile) ? iconv("UTF-8","EUC-KR",$userfile) : $userfile;
				}else	{
					mkdir($path_is, 0777);
					//$userfile = iconv("UTF-8","EUC-KR",$userfile) ? iconv("UTF-8","EUC-KR",$userfile) : $userfile;
					exit();
				}
				//---------------------------------------------------------------------------------					
				$V_userfile_name = time()."_".$userfile;
				$vupload = $path.$V_userfile_name;
				$vupload = str_replace(" ","",$vupload);
				move_uploaded_file($userfile_tmp, $vupload);
				$userfile_size = number_format($userfile_size);
				//---------------------------------------------------------------------------------
			

				
				for($i=0; $i<$cnt_company; $i++) {
					
					echo '<br>'.$insert_company[$i].'<br>';

					if($insert_company[$i] =="HANM"){
						//=============================================
						$ftp_server 	= "211.206.127.70";	// FTP 주소
						$ftp_port 		= "23";	// FTP 주소
						$ftp_user_name 	= "noticeuser";		// 접속 ID
						$ftp_user_pass 	= "noticeuser!";	// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="JANG"){
						//=============================================
						$ftp_server 	= "211.206.127.71";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="PILE"){
						//=============================================
						$ftp_server 	= "211.206.127.72";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="HALL"){
						//=============================================
						$ftp_server 	= "1.234.37.143";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
// 						$ftp_user_name 	= "intranet@h0";			// 접속 ID
// 						$ftp_user_pass 	= "h@intranet0!%";		// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="SAMA"){
						//=============================================
						$ftp_server 	= "118.220.172.237";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
// 						$ftp_user_name 	= "samanerp";			// 접속 ID
// 						$ftp_user_pass 	= "samanerp1234!";		// 접속 PW
						//=============================================
						
					}
					
					//==========================================================================
					// B 호스트 접속
					if(!($conn_id = ftp_connect($ftp_server, $ftp_port))){
						die("$ftp_server : $server_post - connect failed");
					}else{
						// 				echo "connect success";
						// 				exit();
					}
					//----------------------------------------------------------------------
					// B 호스트 로그인
					if(!ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)){
						die("$ftp_user_name - login failed");
					}
					//----------------------------------------------------------------------
					if($GroupCode=="99"){
						//전체공지
						$FileLocation="/notice/noticefile/test/";
					} else{
						//부서별
						$FileLocation="/notice/noticefile/test/".$GroupCode."/";
					}
					//----------------------------------------------------------------------
					//업로드할 폴더로 이동한다.
					//	ftp_chdir($conn_id, $FileLocation);
					if (ftp_chdir($conn_id, $FileLocation)) {
						echo "successfully created $FileLocation\n"."<br>";
					} else {
						echo "There was a problem while creating $FileLocation\n";
					}
					//---------------------------------------------------------------------
						
					//---------------------------------------------------------------------
					//파일을 업로드 한다.
					if(!ftp_put($conn_id, $V_userfile_name, $vupload, FTP_BINARY)){
						echo"파일을 지정한 디렉토리로 복사 하는 데 실패했습니다.".$V_userfile_name;
						exit;
					}else{
						echo 1;
					}
					//===========================================================
						

					
					ftp_close($conn_id);
					//$re_unlink = unlink($vupload); //파일삭제
					//==========================================================================
					
					
				}//for
				$re_unlink = unlink($vupload); //파일삭제
				
				
			}else{
				$filename="";
				$userfile_size="";
			}
			
			
			
			
			
			

			
			//********************************************************************************************************************
			//** DB입력작업 start
			//********************************************************************************************************************
			
			if( ($userfile_name <>"" && $userfile_size <>0)){
				if($GroupCode=="99"){ //전체공지
					//$filename="./noticefile/".time()."_".$userfile_name;
					$V_filename_db="./noticefile/test/".$V_userfile_name;
						
				}else{
					$V_filename_db="./noticefile/test/".$GroupCode."/".$V_userfile_name;
				}
			}

				
			//해당 공지를 입력한 회사
			if($_SESSION['CK_CompanyKind']=="HANM"){		 $email="1";
			}else if($_SESSION['CK_CompanyKind']=="JANG"){$email="2";
			}else if($_SESSION['CK_CompanyKind']=="PILE"){$email="3";
			}else if($_SESSION['CK_CompanyKind']=="HALL"){$email="4";
			}else if($_SESSION['CK_CompanyKind']=="SAMA"){$email="5";
			}//if
			
			
			
			$auth_pk	= '';
			$auth_pk	= 'pk_'.date("YmdHis"); 
			//notice_sub (0) = 메인공지사항  ==> notice_new_test_tbl : sub
			$home="";
			for($i=0; $i<$cnt_company; $i++) {
				if($insert_company[$i] =="HANM"){
					$home .= "/H";
				}else if($insert_company[$i] =="JANG" ){
					$home .= "/J";
				}else if($insert_company[$i] =="PILE" ){
					$home .= "/P";
				}else if($insert_company[$i] =="HALL" ){
					$home .= "/L";
				}else if($insert_company[$i] =="SAMA" ){
					$home .= "/S";
				}//if
			}//for
			for($i=0; $i<$cnt_company; $i++) {
				if($insert_company[$i] =="SAMA" ){
					//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					if($_SESSION['CK_CompanyKind']!="SAMA"){
						$notice_sub="0"; //다른 회사에서 삼안쪽에 공지를 올리는 경우 [메인공지사항에 입력되게] 
					}
					//익스로 입력시 들어가는 오류코드 제거
					$subtext = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$subtext);
					$dbinsert="insert into notice_new_test_tbl (auth_pk , sub,name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
					$dbinsert=$dbinsert." values('$auth_pk','$notice_sub','$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$V_filename_db','$userfile_size','$forcepopup')";
					//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
				}else{
					if($_SESSION['CK_CompanyKind']=="PILE"){
						$V_filename_db=iconv( "EUC-KR","UTF-8",$V_filename_db);
					}
					//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					//익스로 입력시 들어가는 오류코드 제거
					$subtext = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$subtext);
					$dbinsert="insert into notice_new_test_tbl (auth_pk , sub,name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
					$dbinsert=$dbinsert." values('$auth_pk','$notice_sub','$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$V_filename_db','$userfile_size','$forcepopup')";
					//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
				}//if
			
				if($insert_company[$i] =="HANM"){
					//=============================================
					//한맥DB//////////////////////
					//$host="192.168.2.252";
					$host="211.206.127.70";
					$user="root";
					$pass="erp";
					$dataname="hanmacerp";
					$hanmac_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$hanmac_db);
					//mysql_query("set names euckr");
					mysql_set_charset("utf-8",$hanmac_db);
					mysql_query("set names utf8");
					//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
 					$result=mysql_query($dbinsert,$hanmac_db);
 					mysql_close($hanmac_db);
						
				}else if($insert_company[$i] =="JANG"){
					//=============================================
					//장헌DB//////////////////////
					//$host="192.168.2.250";
					$host="192.168.10.6";
					$user="root";
					$pass="erp";
					$dataname="hanmacerp";
					$jangheon_db=mysql_connect($host,$user,$pass);
					mysql_select_db("$dataname",$jangheon_db);
					mysql_set_charset("utf-8",$jangheon_db);
					mysql_query("set names utf8");
					//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
					$result=mysql_query($dbinsert,$jangheon_db);
					mysql_close($jangheon_db);
			
				}else if($insert_company[$i] =="PILE"){
					//=============================================
					//PTC DB//////////////////////
					//$host="192.168.2.249";
					$host="192.168.10.7";
					$user="root";
					$pass="erp";
					$dataname="hanmacerp";
					$piletech_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$piletech_db);
					mysql_set_charset("utf-8",$piletech_db);
					mysql_query("set names utf8");
					//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
					$result=mysql_query($dbinsert,$piletech_db);
					mysql_close($piletech_db);
	
				}else if($insert_company[$i] =="HALL"){
					//=============================================
					$host ='1.234.37.143';
					$user="root";
					$pass="vbxsystem";
					$dataname="hallaerp";
					$halla_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$halla_db);
					mysql_set_charset("utf-8",$halla_db);
					mysql_query("set names utf8");
					//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
					$result=mysql_query($dbinsert,$halla_db);
					mysql_close($halla_db);
						
				}else if($insert_company[$i] =="SAMA"){
					//=============================================
					$host="118.220.172.237";
					$user="root";
					$pass="vbxsystem";
					$dataname="hallaerp";
					$saman_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$saman_db);
					mysql_set_charset("utf-8",$saman_db);
					mysql_query("set names utf8");
					//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
					$result=mysql_query($dbinsert,$saman_db);
					mysql_close($saman_db);
			
				}//if
			
			}//for
			//************************************************************
			//** DB입력작업 end
			//************************************************************
			//exit();
			
			$this->smarty->assign('MoveURL',"noticeAdd_controller.php?ActionMode=view&notice_sub=$notice_sub");
			$this->smarty->display("intranet/move_page.tpl");
			
		}//InsertAction_file
		
		
		//============================================================================
		// 공지사항 insert logic
		//============================================================================
		function InsertAction_old()
		{
			extract($_REQUEST);
			global $db,$memberID;
			global $title,$button_type,$popup;
			global $wdate,$ip,$now_day;
			global $subtext;
			global $GroupCode,$id,$mode,$pass1;
			global $viewoption,$popoption,$forcepopup;
			global $userfile1,$userfile1_name,$filename,$userfile1_size;
			global $userfile,$userfile_name,$userfile_size;
			global $view_start,$view_end,$pop_start,$pop_end;
			global $uphanmac,$upfiletech,$upjangheon,$korname,$view_start,$view_end,$notice_sub;

			$wdate = date ("%Y/%m/%d");				/* 입력 날짜 저장 */
			$name = trim($name);							/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */
			$homepage = trim($homepage);			/* 홈페이지 앞 뒤 공백 제거 */
			$ip = getenv("REMOTE_ADDR");				/* Remote IP저장 */
			//$comment = nl2br($comment); 			/* 내용부분 /n 인식 시키기위한 */

//print_r($_REQUEST);
			if ($popoption=="on")
			{
			}else
			{
				$pop_start="";
				$pop_end="";
			}

			if ($viewoption=="on")
			{
			}else
			{
				$view_start="";
				$view_end="";
			}

			if($GroupCode=="0" || $GroupCode=="" )
			{
				$GroupCode="99";
			}

			
		//******************************************************************************************//
		//*******************첨부파일 첨부 부분(공지올리는 회사 서버에 저장)********************//
		//******************************************************************************************//

// 								$userfile_name = str_replace(" ","",$userfile_name);
// 								if($file_delete_check == 'Y'){
// 									$userfile_name = "";
// 								}
// 								if ($userfile_name <>"" && $userfile_size <>0)
// 								{
// 									if (is_dir ($path_is))
// 									{
// 									}
// 									else
// 									{
// 										mkdir($path_is, 0777);
// 									}
					
									
									
									
									
					
// 									$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
									
// 									$userfile = $_FILES['userfile']['name'];
// 									//$userfile = iconv("UTF-8","EUC-KR",$userfile) ? iconv("UTF-8","EUC-KR",$userfile) : $userfile;
									
									
// 									$V_userfile_name = time()."_".$_FILES['userfile']['name'];
// 									$vupload = $path.$V_userfile_name;
// 									$vupload = str_replace(" ","",$vupload);
									
// 									$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
									
									
// 									move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
									
									
// 									$userfile_size = number_format($userfile_size);
					
									
									
									
									
									
// 									//===========================================================
// 									//$this->FN_FTP_UPLOAD($userfile,$vupload,'','');
// 									//exit();
					
					
// 									// B 호스트 정보
// 									$ftp_server 	= "211.206.127.74";	// FTP 주소
// 									//$ftp_server 	= "27.96.135.161";	// FTP 주소
// 									$ftp_port 		= "21";	// FTP 주소
// 									$ftp_user_name 	= "noticeuser";			// 접속 ID
// 									$ftp_user_pass 	= "1234dkdlxl!";		// 접속 PW
										
// 									// 			$ftp_server = "211.206.127.74";	// FTP 주소
// 									// 			//$ftp_server = "27.96.135.161";	// FTP 주소
// 									// 			$ftp_port = "21";	// FTP 주소
// 									// 			$ftp_user_name = "hanmac";			// 접속 ID
// 									// 			$ftp_user_pass = "1234dkdlxl!";		// 접속 PW
										
// 									// B 호스트 접속
// 									if(!($conn_id = ftp_connect($ftp_server, $ftp_port))){
// 										die("$ftp_server : $server_post - connect failed");
// 									}else{
									
// 										// 				echo "connect success";
// 										// 				exit();
// 									}
// 									// B 호스트 로그인
// 									if(!ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)){
// 										die("$ftp_user_name - login failed");
// 									}
									
										 	
// 									/*
// 									 FileInfo['FileLocation']="pm/cont/"+pjt_code+"/"+wbs_code+"_"+seq+"/";
// 									 */
// 									//$FileLocation="notice/";
// 									$FileLocation="/notice/";
									
									
							
									
// 									//업로드할 폴더로 이동한다.
// 									//	ftp_chdir($conn_id, $FileLocation);
									
// 									if (ftp_chdir($conn_id, $FileLocation)) {
// 										echo "successfully created $FileLocation\n";
// 									} else {
// 										echo "There was a problem while creating $FileLocation\n";
// 									}
									
								
									
// 									//echo $set_vupload;
									
										
// 									// 디비에 저장될 파일 이름
// 									//$filename = "satisbook/notice/".$set_userfile;
// 									//$filename = $set_userfile;
// 									// B 호스트에 저장될 실제 파일
// 									//$tmpfile = $_FILES['userfile']['tmp_name'];
// 									//echo $vupload;
										
					
									
// 									echo "<br>vupload=".$vupload."<br>";
// 									//exit();
									
									
										
// 									//파일을 업로드 한다.
// 									if(!ftp_put($conn_id, $V_userfile_name, $vupload, FTP_BINARY)){
// 										echo"파일을 지정한 디렉토리로 복사 하는 데 실패했습니다.".$V_userfile_name;
// 										exit;
// 									}else{
// 										echo 1;
// 									}
									
									
									
// 									exit();
									
									
									
// 									//===========================================================
									
									
									
									
									
									
									
									
// 									if($GroupCode=="99"){ //전체공지
// 										//$filename="./noticefile/".time()."_".$userfile_name;
// 										$filename="noticefile/".time()."_".$userfile_name;
										
// 									}else{
// 										$filename="./noticefile/".$GroupCode."/".time()."_".$userfile_name;
// 									}
// 									$filename = str_replace(" ","",$filename);
// 								}
// 								else
// 								{
// 									$filename="";
// 									$userfile_size="";
// 								}


			
			
			
			if($upjangheon =="on")
			{
				///////////장헌DB//////////////////////
				//$host="192.168.2.250";
				$host="192.168.10.6";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$jangheon_db=mysql_connect($host,$user,$pass);
				mysql_select_db("$dataname",$jangheon_db);
				//mysql_query("set names euckr");
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");
			
				if($imgcommentoption=="yes")
				{
					$comment=$imgcomment;
				}
			
				$dbinsert="insert into notice_new_test_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
				$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";
			
				//echo "장헌   :".$dbinsert."<br>";
				$result=mysql_query($dbinsert,$jangheon_db);
			
				mysql_close($jangheon_db);
			}//if
			
			
			
			
			
			
			
			
			
		//******************************************************************************************//
		//***************************이미지 파일공지 회사별 체크 부분****************************//
		//******************************************************************************************//

			$userfile1_name = str_replace(" ","",$userfile1_name);

			if ($userfile1_name <>"" && $userfile1_size <>0)
			{

				$path2 ="./../../../intranet_file/notice/imgcontent/";
				$path_is2 ="./../../../intranet_file/notice/imgcontent";

				if (is_dir ($path_is2))
				{
				}
				else
				{
					mkdir($path_is2, 0777);
				}

				$prefile=time();
				$ext=explode(".",$_FILES['userfile1']['name']);
				$ext=$ext[sizeof($ext)-1];
				$uploadimgname=$prefile.".".$ext;

				$subtext="<img src=./../../../intranet_file/notice/imgcontent/".$uploadimgname.">";

				if($_SESSION['CK_CompanyKind']=="HANM")
				{
					$imgcomment="<img src=http://erp.hanmaceng.co.kr/intranet_new2/show_2/menu2_1/imgcontent/".$uploadimgname.">";
				}
				else if($_SESSION['CK_CompanyKind']=="JANG")
				{
					$imgcomment="<img src=http://erp.jangheon.co.kr/intranet_file/notice/imgcontent/".$uploadimgname.">";
				}
				else if($_SESSION['CK_CompanyKind']=="PILE")
				{
					$imgcomment="<img src=http://101.55.22.253/intranet_file/notice/imgcontent/".$uploadimgname.">";
				}

				$imgcommentoption="yes";

				$userfile1=stripslashes($userfile1);
				$vupload1 = $path2.$uploadimgname;
				$vupload1 = str_replace(" ","",$vupload1);
				$_FILES['userfile1']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile1']['tmp_name']);
				move_uploaded_file($_FILES['userfile1']['tmp_name'], $vupload1);
			}
			else
			{
				$imgcommentoption="no";
			}

		//******************************************************************************************//
		//********************************추가 공지 회사 표시 부분********************************//
		//******************************************************************************************//

			$home="";
			if($_SESSION['CK_CompanyKind']=="HANM")
			{
				if($upjangheon =="on"){ $home="/J";}
				if($upfiletech =="on"){ $home=$home."/P";}
				$email="1";
			}
			else if($_SESSION['CK_CompanyKind']=="JANG")
			{
				if($uphanmac =="on"){ $home="/H";}
				if($upfiletech =="on"){ $home=$home."/P";}
				$email="2";
			}
			else if($_SESSION['CK_CompanyKind']=="PILE")
			{
				if($uphanmac =="on"){ $home="/H";}
				if($upjangheon =="on"){ $home=$home."/J";}
				$email="3";
			}

			
			//notice_sub (0) = 메인공지사항  ==> notice_new_test_tbl : sub
			
			
			//익스로 입력시 들어가는 오류코드 제거
			$subtext = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$subtext);

			$dbinsert="insert into notice_new_test_tbl (sub,name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
			$dbinsert=$dbinsert." values('$notice_sub','$korname','','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";
			//echo $dbinsert;
			$result=mysql_query($dbinsert,$db);

			
			$auth_pk	= '';
			
			$auth_pk	= ''.date("YmdHis"); //총괄기획실 전체 공지 등록의 경우 입력됨
			
			
// 			$host ='1.234.37.143';
// 			$user="root";
// 			$pass="vbxsystem";
// 			$dataname="hallaerp";
// 			$halla_db=mysql_connect($host,$user,$pass);
// 			mysql_select_db($dataname,$halla_db);
			
			
// 			//mysql_query("set names euckr");
// 			mysql_set_charset("utf-8",$db);
// 			mysql_query("set names utf8");
				
// 			if($imgcommentoption=="yes")
// 			{
// 				$comment=$imgcomment;
// 			}
				
// 			$dbinsert="insert into notice_new_test_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
// 			$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";
				
// 		 			echo $dbinsert;
// 			 			exit();
				
// 			$result=mysql_query($dbinsert,$halla_db);
// 			mysql_close($halla_db);
			
			
			
			
			//============================================================
			// 바론컨설턴트 입력
			//============================================================
			if($uphanmac =="on")
			{
				///////////한맥DB//////////////////////
				//$host="192.168.2.252";
				$host="192.168.10.5";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$hanmac_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$hanmac_db);
				//mysql_query("set names euckr");
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");


				if($imgcommentoption=="yes")
				{
					$comment=$imgcomment;
				}

				$dbinsert="insert into notice_new_test_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
				$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";


				$result=mysql_query($dbinsert,$hanmac_db);
				mysql_close($hanmac_db);
			}//if
 
			//============================================================
			// 장헌산업 입력
			//============================================================
			if($upjangheon =="on")
			{
				///////////장헌DB//////////////////////
				//$host="192.168.2.250";
				$host="192.168.10.6";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$jangheon_db=mysql_connect($host,$user,$pass);
				mysql_select_db("$dataname",$jangheon_db);
				//mysql_query("set names euckr");
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");

				if($imgcommentoption=="yes")
				{
					$comment=$imgcomment;
				}

				$dbinsert="insert into notice_new_test_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
				$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";

				//echo "장헌   :".$dbinsert."<br>";
				$result=mysql_query($dbinsert,$jangheon_db);

				mysql_close($jangheon_db);
			}//if

			//============================================================
			// PTC 입력
			//============================================================

			if($upfiletech =="on")
			{
				///////////파일DB//////////////////////
				//$host="192.168.2.249";
				$host="192.168.10.7";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$piletech_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$piletech_db);
				//mysql_query("set names euckr");
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");

				if($imgcommentoption=="yes")
				{
					$comment=$imgcomment;
				}

				$dbinsert="insert into notice_new_test_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
				$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";

				$result=mysql_query($dbinsert,$piletech_db);
				mysql_close($piletech_db);
			}//if
			
			//============================================================
			// 한라산업 입력
			//============================================================
			if($uphalla =="on")
			{
				/////////////////////////////////

				$host ='1.234.37.143';
				$user="root";
				$pass="vbxsystem";
				$dataname="hallaerp";
				$halla_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$halla_db);
				
				
				//mysql_query("set names euckr");
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");
			
				if($imgcommentoption=="yes")
				{
					$comment=$imgcomment;
				}
			
				$dbinsert="insert into notice_new_test_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
				$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";
			
// 			echo $dbinsert;
//  			exit();
			
				$result=mysql_query($dbinsert,$halla_db);
				mysql_close($halla_db);
			}//if
			
			//============================================================
			// 삼안 입력
			//============================================================
			if($upsaman =="on")
			{
				///////////한맥DB//////////////////////
				//$host="192.168.2.252";
				$host="118.220.172.237";
				$user="root";
				$pass="vbxsystem";
				$dataname="hallaerp";
				$saman_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$saman_db);
				//mysql_query("set names euckr");
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");
					
				
				
					
				if($imgcommentoption=="yes")
				{
					$comment=$imgcomment;
				}
					
				$dbinsert="insert into notice_new_test_tbl (name,email,home,pass,title,comment,wdate,see,group_code,popup,view_start,view_end,pop_start,pop_end,filename,filesize,forcepopup)";
				$dbinsert=$dbinsert." values('$korname','$email','$home','$pass1','$title','$subtext','$now_day',0,'$GroupCode','$popoption','$view_start','$view_end','$pop_start','$pop_end','$filename','$userfile_size','$forcepopup')";
					
					
				$result=mysql_query($dbinsert,$saman_db);
				mysql_close($saman_db);
			}//if

			$this->smarty->assign('MoveURL',"noticeAdd_controller.php?ActionMode=view&notice_sub=$notice_sub");
			$this->smarty->display("intranet/move_page.tpl");

		}//function InsertAction()

		
		//============================================================================
		// 공지사항 읽기 logic
		//============================================================================
		function UpdateReadPage()
		{
			global $db,$memberID;
			global $mode,$page,$currentPage,$pop;
			global $company,$id,$now_day,$button_type;
			global $pass1,$updatecode,$password2,$GroupCode,$notice_sub;
			global $korname,$popoption,$forcepopup;
			global $view_start,$view_end,$pop_start,$pop_end;

			if( $_SESSION['auth_manager'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);


			$query_data = array();


			/*조회수 증가================================================================*/
			if( $mode=="click" ){
				//$update=mysql_query("update notice_new_test_tbl set see=see+1 where id=$id",$db);


					$sql="select * from member_tbl where MemberNo='$memberID'";
					$result=mysql_query($sql,$db);
					$row=mysql_fetch_array($result);
					$KorName=$row[korName];
					$RankCode=$row[RankCode];
					$GroupCode=$row[GroupCode];


					$sql2="select * from notice_read_test_tbl where notice_id='$id' and MemberNo='$memberID'";
					//echo $sql2."<br>";
					$re2 = mysql_query($sql2,$db);
					if(mysql_num_rows($re2) == 0)
					{
						$sql3="insert into notice_read_test_tbl (notice_id,MemberNo,korName,RankCode,GroupCode,ReadDate)";
						$sql3=$sql3."values($id,'$memberID','$KorName','$RankCode','$GroupCode',now())";

						//echo $sql3."<br>";
						mysql_query($sql3,$db);
					}



			}
			/*조회수 증가 끝==============================================================*/
			$sql = "select * from notice_new_test_tbl where id='$id'";
			//echo $sql ."<Br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				$id =$re_row[id];
				$notice_sub =$re_row[sub];
				$korname =$re_row[name];
				$GroupCode =$re_row[group_code];
				$pass =$re_row[pass];
				$wdate=$re_row[wdate];
				$title=$re_row[title];
				$Writer=$re_row[name];
				$subtext=$re_row[comment];
				
				$auth_pk=$re_row[auth_pk];

				if($mode=="read" || $mode=="mod" || $mode=="click" || $mode=="popup" ){
					if($_SESSION['CK_CompanyKind']!="SAMA" && $auth_pk==""){
						//웹에디터에서 작성된 글이 아니면
						$subtext = nl2br($subtext);
					}
				}

				$popup=$re_row[popup];
				$forcepopup=$re_row[forcepopup];
				
				$email=$re_row[email];
				
				$home_check=$re_row[home];
  
				$home_check_str = "";
				if (strpos($home_check,"/H") === false){}else{ $home_check_str .="한맥/ "; $this->smarty->assign('uphanmac_on',"on");}
				if (strpos($home_check,"/J") === false){}else{ $home_check_str .="장헌/ "; $this->smarty->assign('upjangheon_on',"on");}
				if (strpos($home_check,"/P") === false){}else{ $home_check_str .="PTC/ "; $this->smarty->assign('upfiletech_on',"on");}
				if (strpos($home_check,"/L") === false){}else{ $home_check_str .="한라/ "; $this->smarty->assign('uphalla_on',"on");}
				if (strpos($home_check,"/S") === false){}else{ $home_check_str .="삼안/";  $this->smarty->assign('upsaman_on',"on");}

				$filename=$re_row[filename];
				$filesize=$re_row[filesize];
				$filesize=round($filesize);


				$tmpfile=explode("/",$filename);
				$no= count($tmpfile)-1;
				$filename_is= $tmpfile[$no];

				/* 년/월/일 분리 부분================================================================*/
				$view_start=$re_row[view_start];
				$view_end=$re_row[view_end];
				$pop_start=$re_row[pop_start];
				$pop_end=$re_row[pop_end];
				/* 년/월/일 분리 부분 끝==============================================================*/
				
				$auth_pk=$re_row[auth_pk];

				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('company',$company);
				$this->smarty->assign('korname',$korname);
				$this->smarty->assign('pass',$pass);
				$this->smarty->assign('button_type',$button_type);
				$this->smarty->assign('password2',$password2);
				$this->smarty->assign('popup',$popup);
				
				$this->smarty->assign('email',$email);
				$this->smarty->assign('home',$home);
				$this->smarty->assign('home_check_str',$home_check_str);
			
				
				
				
				$this->smarty->assign('id',$id);
				$this->smarty->assign('GroupCode',$GroupCode);
				$this->smarty->assign('pop',$pop);
				$this->smarty->assign('popup',$popup);
				$this->smarty->assign('wdate',$wdate);
				$this->smarty->assign('title',$title);
				$this->smarty->assign('comment',$subtext);
				
				
				
				
				$this->smarty->assign('filename',$filename);

				
				
				
				$this->smarty->assign('filename_is',$filename_is);
				$this->smarty->assign('filesize',$filesize);
				$this->smarty->assign('view_start',$view_start);
				$this->smarty->assign('view_end',$view_end);
				$this->smarty->assign('pop_start',$pop_start);
				$this->smarty->assign('pop_end',$pop_end);
				$this->smarty->assign('forcepopup',$forcepopup);
				$this->smarty->assign('Writer',$Writer);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('notice_sub',$notice_sub);
				
				$this->smarty->assign('auth_pk',$auth_pk);
				
				$this->smarty->assign('CompanyKind',$_SESSION['CK_CompanyKind']);

				$this->smarty->assign("page_action","noticeAdd_controller.php");

				if($button_type=="insert_ok"){
						$this->smarty->display("intranet/common_contents/work_noticeAdd/notice_input.tpl");
				}else{
						if($mode=="popup" || $mode=="click" )
							$this->smarty->display("intranet/common_contents/work_noticeAdd/notice_read_popup_mvc.tpl");
						else
							$this->smarty->display("intranet/common_contents/work_noticeAdd/notice_read_mvc.tpl");
				}
			}

		}//function UpdateReadPage()

		
		
		//============================================================================
		// 영상재생 TEST
		//============================================================================
		function VIDEO_PLAYER()
		{ 
			$this->smarty->display("intranet/common_contents/work_noticeAdd/video_player_mvc.tpl");
			//intranet/common_contents/work_lecture/video_mvc.tpl
		}
		
		
		//============================================================================
		//입력값 자동완성 test
		//============================================================================
		function AUTOCOMPLETE()
		{ 
			$this->smarty->display("intranet/common_contents/work_noticeAdd/autocomplete_mvc.tpl");
			//intranet/common_contents/work_lecture/video_mvc.tpl
		}
		
		
		//============================================================================
		//입력값 자동완성 test
		//============================================================================
		function AUTOCOMPLETE_DATA()
		{ 

			
				
			// 검색될 샘플 데이터 입니다.
			$cities = array("서울2222","서울3333","서울1111","서울","부산","대구","광주","울산");
			// 넘어온 검색어 파라미터 입니다.
			$term = $_GET['term'];
			// 데이터를 루핑 하면서 찾습니다.
			$result = array();
			foreach($cities as $city) {
			    if(strpos($city, $term) !== false) {
			        $result[] = array("label" => $city, "value" => $city);
			    }
			}
			// 찾아진 데이터를 json 데이터로 변환하여 전송합니다.
			echo json_encode($result);
			
			
			
// 			$my_array = [
// 					["label" => "206", "value" => "Peugeot 206"],
// 					["label" => "207", "value" => "Peugeot 207"],
// 					["label" => "208", "value" => "Peugeot 208"],
// 					["label" => "209", "value" => "Peugeot 209"],
// 					["label" => "307", "value" => "Peugeot 307"],
// 					["label" => "308", "value" => "Peugeot 308"],
// 					["label" => "309", "value" => "Peugeot 309"],
// 					["label" => "M3", "value" => "BMW M3"],
// 					["label" => "Quatro", "value" => "Audi Quatro"]
// 			];
			
// 			echo json_encode($my_array);
			
			
			
		}//AUTOCOMPLETE_DATA
		
		
		
		
		
		//============================================================================
		// 공지사항 update logic
		//============================================================================
		function UpdateAction_file()
		{
			extract($_REQUEST);
			global $db,$memberID;
			global $name,$title,$pass;
			global $subtext,$wdate,$ip,$GroupCode;
			global $pass1,$id,$mode,$filename;
			global $userfile,$userfile_name,$userfile_size;
			global $popoption,$forcepopup,$korname,$viewoption;
			global $pop_start,$pop_end, $notice_sub;
			global $view_start,$view_end;
			global $auth_pk;
			
			$wdate = date ("%Y/%m/%d");				/* 입력 날짜 저장 */
			$name = trim($name);					/* 이름 앞 뒤 공백 제거 */
			$name = ereg_replace(" ","",$name);		/* 이름 중간 공백 제거 */
			$homepage = trim($homepage);			/* 홈페이지 앞 뒤 공백 제거 */
			$ip = getenv("REMOTE_ADDR");			/* Remote IP저장 */
			//$comment = nl2br($comment); 			/* 내용부분 /n 인식 시키기위한 */

			$ECHO_BOOLEAN = false; // true false
			$DB_BOOLEAN = true; // true false
			
			if ($popoption=="on"){
			}else{
				$pop_start="0000-00-00";
				$pop_end="0000-00-00";
			}
			if ($viewoption=="on"){
			}else{
				$view_start="0000-00-00";
				$view_end="0000-00-00";
			}

			if($id) {
				$result=mysql_query("select pass, home,filename from notice_new_test_tbl where id=$id", $db);
				$row=mysql_fetch_array($result);
				$pass_confirm=$row[pass];
				$home_confirm=$row[home];
				$filename_confirm=$row[filename];
				
				$array_filename   = explode("/",$filename_confirm);
				$filename_confirm = $array_filename[sizeof($array_filename)-1];

				//+++++++++++++++++++++++++++++++++++++++++++++++++++
				$insert_company = array();
				if (strpos($home_confirm,"/H") === false){}else{ array_push($insert_company,'HANM'); }
				if (strpos($home_confirm,"/J") === false){}else{ array_push($insert_company,'JANG'); }
				if (strpos($home_confirm,"/P") === false){}else{ array_push($insert_company,'PILE'); }
				if (strpos($home_confirm,"/L") === false){}else{ array_push($insert_company,'HALL'); }
				if (strpos($home_confirm,"/S") === false){}else{ array_push($insert_company,'SAMA'); }
				$cnt_company = count($insert_company);
				//+++++++++++++++++++++++++++++++++++++++++++++++++++

				if($ECHO_BOOLEAN){
					echo 'insert_company'.'<br>';
					print_r($insert_company);
					echo '<br>';
				}

				if (($pass1==$pass_confirm) || ($pass1==$adminpass)){

					if($ECHO_BOOLEAN){
						echo 'insert_company'.'<br>';
						echo 'pass_confirm'.'<br>';
						echo 'file_delete_check='.$file_delete_check.'<br>';
					}

	 
					
					//==============================================================================================
					//파일저장 start
					//==============================================================================================

					if($GroupCode=="99") //전체공지
					{
						$path ="./../../../intranet_file/notice/temp/";
						$path_is ="./../../../intranet_file/notice/temp";
		// 				$path ="./../../../intranet_file/notice/noticefile/";
		// 				$path_is ="./../../../intranet_file/notice/noticefile";
					} //부서별
					else
					{
						$path ="./../../../intranet_file/notice/temp/";
						$path_is ="./../../../intranet_file/notice/temp/";
		// 				$path ="./../../../intranet_file/notice/noticefile/".$GroupCode."/";
		// 				$path_is ="./../../../intranet_file/notice/noticefile/".$GroupCode;
					}



					//---------------------------------------------------------------------------------
					$userfile_name = str_replace(" ","",$userfile_name);
					if($file_delete_check == 'Y'){
						if($ECHO_BOOLEAN){ echo '기존에 저장된 파일 삭제여부 = Y'.'<br>'; }
						
						$userfile_name = "";
					}
					
					$V_userfile_name='';
					
					//---------------------------------------------------------------------------------
					$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
					$userfile = $_FILES['userfile']['name'];
					//---------------------------------------------------------------------------------
					$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
					$userfile_tmp = $_FILES['userfile']['tmp_name'];
					//---------------------------------------------------------------------------------
					$error_str = "";
					$error_boolean=true;
					//---------------------------------------------------------------------------------
					if ( ($userfile_name <>"" && $userfile_size <>0) || ($file_delete_check == 'Y'))
					{
						
						if ( ($userfile_name <>"" && $userfile_size <>0) )
						{
							
							
							if($ECHO_BOOLEAN){ echo '저장할 파일이 존재함'.'<br>'; }
							
							if (is_dir ($path_is)){
								echo "userfile=".$userfile_name;
								//$userfile = iconv("UTF-8","EUC-KR",$userfile) ? iconv("UTF-8","EUC-KR",$userfile) : $userfile;
							}else	{
								mkdir($path_is, 0777);
								//$userfile = iconv("UTF-8","EUC-KR",$userfile) ? iconv("UTF-8","EUC-KR",$userfile) : $userfile;
								$error_str .= "ftp_chdir FAIL : FileLocation=".$FileLocation."<br>";
								$error_boolean=false;;
							
							}
							if ($error_boolean===true){
								//---------------------------------------------------------------------------------
								$V_userfile_name = time()."_".$userfile;
								$vupload = $path.$V_userfile_name;
								$vupload = str_replace(" ","",$vupload);
								move_uploaded_file($userfile_tmp, $vupload);
								$userfile_size = number_format($userfile_size);
								//---------------------------------------------------------------------------------
							}
						}
						
						
						if ($error_boolean===true){
							for($i=0; $i<$cnt_company; $i++) {
									
								
								if($ECHO_BOOLEAN){ echo '<br>체크된 회사 = '.$insert_company[$i].'<br>'; }
							
								if($insert_company[$i] =="HANM"){
									//=============================================
									$ftp_server 	= "211.206.127.70";	// FTP 주소
									$ftp_port 		= "23";	// FTP 주소
									$ftp_user_name 	= "noticeuser";		// 접속 ID
									$ftp_user_pass 	= "noticeuser!";	// 접속 PW
									//=============================================
								}else if($insert_company[$i] =="JANG"){
									//=============================================
									$ftp_server 	= "211.206.127.71";	// FTP 주소
									$ftp_port 		= "21";	// FTP 주소
									$ftp_user_name 	= "noticeuser";			// 접속 ID
									$ftp_user_pass 	= "noticeuser!";		// 접속 PW
									//=============================================
								}else if($insert_company[$i] =="PILE"){
									//=============================================
									$ftp_server 	= "211.206.127.72";	// FTP 주소
									$ftp_port 		= "21";	// FTP 주소
									$ftp_user_name 	= "noticeuser";			// 접속 ID
									$ftp_user_pass 	= "noticeuser!";		// 접속 PW
									//=============================================
								}else if($insert_company[$i] =="HALL"){
									//=============================================
									$ftp_server 	= "1.234.37.143";	// FTP 주소
									$ftp_port 		= "21";	// FTP 주소
									$ftp_user_name 	= "noticeuser";			// 접속 ID
									$ftp_user_pass 	= "noticeuser!";		// 접속 PW
									// 						$ftp_user_name 	= "intranet@h0";			// 접속 ID
									// 						$ftp_user_pass 	= "h@intranet0!%";		// 접속 PW
									//=============================================
								}else if($insert_company[$i] =="SAMA"){
									//=============================================
									$ftp_server 	= "118.220.172.237";	// FTP 주소
									$ftp_port 		= "21";	// FTP 주소
									$ftp_user_name 	= "noticeuser";			// 접속 ID
									$ftp_user_pass 	= "noticeuser!";		// 접속 PW
									// 						$ftp_user_name 	= "samanerp";			// 접속 ID
									// 						$ftp_user_pass 	= "samanerp1234!";		// 접속 PW
									//=============================================
							
								}
									
								//==========================================================================
								// B 호스트 접속
								if(!($conn_id = ftp_connect($ftp_server, $ftp_port))){
									die("$ftp_server : $server_post - connect failed");
								}else{
									// 				echo "connect success";
									// 				exit();
								}
								//----------------------------------------------------------------------
								// B 호스트 로그인
								if(!ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)){
									die("$ftp_user_name - login failed");
								}
								//----------------------------------------------------------------------
								if($GroupCode=="99"){
									//전체공지
									$FileLocation="/notice/noticefile/test/";
								} else{
									//부서별
									$FileLocation="/notice/noticefile/test/".$GroupCode."/";
								}
								//----------------------------------------------------------------------
								//업로드할 폴더로 이동한다.
								//	ftp_chdir($conn_id, $FileLocation);
								if (ftp_chdir($conn_id, $FileLocation)) {
									//echo "successfully created $FileLocation\n"."<br>";
								} else {
									//echo "There was a problem while creating $FileLocation\n";
									$error_str .= "ftp_chdir FAIL : FileLocation=".$FileLocation."<br>";
								}
								//---------------------------------------------------------------------
							
							
								if ( ($userfile_name <>"" && $userfile_size <>0) ){
									//---------------------------------------------------------------------
									//파일을 업로드 한다.
									if(!ftp_put($conn_id, $V_userfile_name, $vupload, FTP_BINARY)){
										$error_str .= "파일을 지정한 디렉토리로 복사 하는 데 실패했습니다.".$V_userfile_name.'<br>';
										//exit;
									}else{
										//echo 1;
									}
									//===========================================================
								}
							
							
							
								if($file_delete_check == 'Y'){
									//===========================================================
									//기존파일 삭제 start
									
									if($ECHO_BOOLEAN){ 
										echo '기존파일 삭제 start'.'<br>';
										echo 'filename_confirm='.$filename_confirm.'<br>';
										echo 'FileLocation='.$FileLocation.'<br>';
									}

									
									
									//ftp_chdir($conn_id, $FileLocation);
									
								
									$res = ftp_size($conn_id, $filename_confirm);
									if ($res != -1) {
										if(!ftp_delete($conn_id, $filename_confirm)){
											//echo" <script> window.alert ('파일을 지정한 디렉토리에서 삭제 하는 데 실패했습니다._1');</script>";
											//exit;
											$error_str .= "file delete fail = ".$filename_confirm.'<br>';
										}
									}
								
									
									if($ECHO_BOOLEAN){
										echo '기존파일 삭제 end'.'<br>';
									}
									
									
									//ftp_quit($fc);
							
									//기존파일 삭제 end
									//===========================================================
								}
							
							
							
							
							
							
								ftp_close($conn_id);
								//$re_unlink = unlink($vupload); //파일삭제
								//==========================================================================
									
									
							}//for
							if ( ($userfile_name <>"" && $userfile_size <>0) ){
								$re_unlink = unlink($vupload); //파일삭제 : intranet_file/notice/temp/
							}
							
							
							
						}//$error_boolean===true
						
						

						
						
					}else{
						
						$error_str .= "저장할 파일없음"."<br>";
						
						$filename="";
						$userfile_size="";
					}
					//==============================================================================================
					//파일저장 end
					//==============================================================================================

					
					
					if($ECHO_BOOLEAN){
						echo 'error_str='.$error_str.'<br>';
						echo '파일저장 end'.'<br><br>';
					}
					
					

					


					
					
					
					

					//==============================================================================================
					// DB 업데이트 start
					//==============================================================================================
					if( ($userfile_name <>"" && $userfile_size <>0)){
						
						if($GroupCode=="99"){ //전체공지
							$V_filename_db="./noticefile/".$V_userfile_name;
						}else{
							$V_filename_db="./noticefile/".$GroupCode."/".$V_userfile_name;
						}	
					}


					if($ECHO_BOOLEAN){
						echo "<br><br>select pass,email,home,title,wdate,group_code,popup,pop_start,pop_end,filesize,filename,comment, auth_pk from notice_new_test_tbl where id='".$id."' <br><br>";
	
					} 
					$result=mysql_query("select pass,email,home,title,wdate,group_code,popup,pop_start,pop_end,filesize,filename,comment, auth_pk from notice_new_test_tbl where id=$id",$db);
					$row=mysql_fetch_array($result);

					$file_name9=$row[filename];
					$pass9=$row[pass];
					$home9=$row[home];
					$title9=$row[title];
					$wdate9=$row[wdate];
					$group_code9=$row[group_code];
					$popup9=$row[popup];
					$pop_start9=$row[pop_start];
					$pop_end9=$row[pop_end];
					$filesize9=$row[filesize];
					
					$auth_pk=$row[auth_pk];

					$wdate9=explode(" ",$wdate9);
					$wdate9=$wdate9[0];

					if($file_delete_check == 'Y'){
						$V_filename_db = "";
						$userfile_size = "";
					}

					if($ECHO_BOOLEAN){
						echo "<br><br>select pass,email,home,title,wdate,group_code,popup,pop_start,pop_end,filesize,filename,comment, auth_pk from notice_new_test_tbl where id='".$id."' <br><br>";
					
					}else{
						
						
					}

					
					//익스로 입력시 들어가는 오류코드 제거
					$subtext = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$subtext);

					if($_SESSION['CK_CompanyKind']=="PILE"){
						$V_filename_db=iconv( "EUC-KR","UTF-8",$V_filename_db);
					}
					
					$dbinsert = "update notice_new_test_tbl set name='$korname',title='$title',comment='$subtext',popup='$popoption',view_start='$view_start',view_end='$view_end',pop_start='$pop_start',pop_end='$pop_end',forcepopup='$forcepopup', sub='$notice_sub'";
					$dbinsert .= "  ,filename = '$V_filename_db' ,filesize='$userfile_size'  ";
					//$dbinsert=$dbinsert."  where pass='$pass9' and title ='$title9' and wdate like '$wdate9%' and group_code='$group_code9' and popup='$popup9' and pop_start='$pop_start9' and pop_end='$pop_end9' and filename='$file_name9' and filesize='$filesize9'";
					
					
					if($auth_pk==""){
						$dbinsert .= "  where id='".$id."'    ";
					}else{
						$dbinsert .= "  where auth_pk='".$auth_pk."'    ";
					}
					
					
					
					echo '<br> <br>dbinsert='.$dbinsert,'   <br>';
					
					
					for($i=0; $i<$cnt_company; $i++) {
						if($insert_company[$i] =="HANM"){
							//=============================================
							//한맥DB//////////////////////
							//$host="192.168.2.252";
							$host="211.206.127.70";
							$user="root";
							$pass="erp";
							$dataname="hanmacerp";
							$hanmac_db=mysql_connect($host,$user,$pass);
							mysql_select_db($dataname,$hanmac_db);
							//mysql_query("set names euckr");
							mysql_set_charset("utf-8",$hanmac_db);
							mysql_query("set names utf8");
							//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';

							if($DB_BOOLEAN){
								$result=mysql_query($dbinsert,$hanmac_db);
								mysql_close($hanmac_db);
							}else{
								echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
								//exit();	
							}
							
						}else if($insert_company[$i] =="JANG"){
							//=============================================
							//장헌DB//////////////////////
							//$host="192.168.2.250";
							$host="192.168.10.6";
							$user="root";
							$pass="erp";
							$dataname="hanmacerp";
							$jangheon_db=mysql_connect($host,$user,$pass);
							mysql_select_db("$dataname",$jangheon_db);
							mysql_set_charset("utf-8",$jangheon_db);
							mysql_query("set names utf8");
							//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';

							if($DB_BOOLEAN){
								$result=mysql_query($dbinsert,$jangheon_db);
								mysql_close($jangheon_db);
							}else{
								echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
								//exit();
							}
							
					
						}else if($insert_company[$i] =="PILE"){
							//=============================================
							//PTC DB//////////////////////
							//$host="192.168.2.249";
							$host="192.168.10.7";
							$user="root";
							$pass="erp";
							$dataname="hanmacerp";
							$piletech_db=mysql_connect($host,$user,$pass);
							mysql_select_db($dataname,$piletech_db);
							mysql_set_charset("utf-8",$piletech_db);
							mysql_query("set names utf8");
							//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';

							if($DB_BOOLEAN){
								$result=mysql_query($dbinsert,$piletech_db);
								mysql_close($piletech_db);
							}else{
								echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
								//exit();
							}
			
						}else if($insert_company[$i] =="HALL"){
							//=============================================
							$host ='1.234.37.143';
							$user="root";
							$pass="vbxsystem";
							$dataname="hallaerp";
							$halla_db=mysql_connect($host,$user,$pass);
							mysql_select_db($dataname,$halla_db);
							mysql_set_charset("utf-8",$halla_db);
							mysql_query("set names utf8");
							//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';

							if($DB_BOOLEAN){
								$result=mysql_query($dbinsert,$halla_db);
								mysql_close($halla_db);
							}else{
								echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
								//exit();
							}
								
						}else if($insert_company[$i] =="SAMA"){
							//=============================================
							$host="118.220.172.237";
							$user="root";
							$pass="vbxsystem";
							$dataname="hallaerp";
							$saman_db=mysql_connect($host,$user,$pass);
							mysql_select_db($dataname,$saman_db);
							mysql_set_charset("utf-8",$saman_db);
							mysql_query("set names utf8");
							//echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';

							if($DB_BOOLEAN){
								$result=mysql_query($dbinsert,$saman_db);
								mysql_close($saman_db);
							}else{
								echo '<br>'.$insert_company[$i].'   :   <br>'.$dbinsert,'   <br>';
								//exit();
							}
					
						}//if
					
					}//for

					if($ECHO_BOOLEAN){
						exit();
					}
					
					
					//==============================================================================================
					// DB 업데이트 end
					//==============================================================================================
					


					if ($error_boolean===true){
					
					}else{
						echo 'error_str'.$error_str.'<br>';
					}
						



				} //입력된 페스워드 일치시
			
			}// id 존재시

			$this->smarty->assign('MoveURL',"noticeAdd_controller.php?ActionMode=view&notice_sub=$notice_sub");
			$this->smarty->display("intranet/move_page.tpl");

		}//UpdateAction_file

		//============================================================================
		// 공지사항 update logic
		//============================================================================
		function UpdateAction_old()
		{
			extract($_REQUEST);
			global $db,$memberID;
			global $name,$title,$pass;
			global $subtext,$wdate,$ip,$GroupCode;
			global $pass1,$id,$mode,$filename;
			global $userfile,$userfile_name,$userfile_size;
			global $popoption,$forcepopup,$korname,$viewoption;
			global $pop_start,$pop_end, $notice_sub;
			global $view_start,$view_end;


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
				$pop_start="0000-00-00";
				$pop_end="0000-00-00";
			}

			if ($viewoption=="on")
			{

			}else
			{
				$view_start="0000-00-00";
				$view_end="0000-00-00";
			}




		if($id) {
			$result=mysql_query("select pass from notice_new_test_tbl where id=$id", $db);
			$row=mysql_fetch_array($result);


			if (($pass1==$row[pass]) || ($pass1==$adminpass)){

				//******************************************************************************************//
				//*******************첨부파일 첨부 부분(공지올리는 회사 서버에 저장)********************//
				//******************************************************************************************//

					$userfile_name = str_replace(" ","",$userfile_name);
					if ($userfile_name <>"" && $userfile_size <>0)
					{

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

						if (is_dir ($path_is))
						{
						}
						else
						{
							mkdir($path_is, 0777);
						}
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

				$result=mysql_query("select pass,email,home,title,wdate,group_code,popup,pop_start,pop_end,filesize,filename,comment, auth_pk from notice_new_test_tbl where id=$id",$db);
				$row=mysql_fetch_array($result);

				$file_name9=$row[filename];
				$pass9=$row[pass];
				$home9=$row[home];
				$title9=$row[title];
				$wdate9=$row[wdate];
				$group_code9=$row[group_code];
				$popup9=$row[popup];
				$pop_start9=$row[pop_start];
				$pop_end9=$row[pop_end];
				$filesize9=$row[filesize];
				
				$auth_pk=$row[auth_pk];

				$wdate9=explode(" ",$wdate9);
				$wdate9=$wdate9[0];

				//익스로 입력시 들어가는 오류코드 제거
				$subtext = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$subtext);

				$dbup9 = "update notice_new_test_tbl set name='$korname',title='$title',comment='$subtext',popup='$popoption',view_start='$view_start',view_end='$view_end',pop_start='$pop_start',pop_end='$pop_end',forcepopup='$forcepopup', sub='$notice_sub'";
				$dbup9 .= "  ,filename = '$V_filename_db' ,filesize='$userfile_size'  ";
				
				
				$dbup=   "update notice_new_test_tbl set name='$korname',title='$title',comment='$subtext',popup='$popoption',view_start='$view_start',view_end='$view_end',pop_start='$pop_start',pop_end='$pop_end',forcepopup='$forcepopup', sub='$notice_sub' ";
				
				
				//$dbup9=$dbup9."  where pass='$pass9' and title ='$title9' and wdate like '$wdate9%' and group_code='$group_code9' and popup='$popup9' and pop_start='$pop_start9' and pop_end='$pop_end9' and filename='$file_name9' and filesize='$filesize9'";
				if($auth_pk==""){
					$dbup9 .= "  where id=$id";
				}else{
					$dbup9 .= "  where auth_pk=$auth_pk";
				}
				

	//echo $dbup9."<br>";
		//******************************************************************************************//
		//*************************한맥에 함께 공지되었던 기록이 있으면*************************//
		//******************************************************************************************//

				$HanIn=strpos($home9,"/H");
				if ($HanIn === false) //한맥에 없으면;
				{
				}
				else
				{
					///////////한맥DB//////////////////////
					$host="192.168.2.252";
					$user="root";
					$pass="";
					$dataname="hanmacerp";
					$hanmac_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$hanmac_db);
					//mysql_query("set names euckr");
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					$result=mysql_query($dbup9,$hanmac_db);
					mysql_close($hanmac_db);

				}

		//******************************************************************************************//
		//*************************장헌에 함께 공지되었던 기록이 있으면*************************//
		//******************************************************************************************//

				$JangIn=strpos($home9,"/J");
				if ($JangIn === false) //장헌에 없으면;
				{
				}
				else
				{
					///////////장헌DB//////////////////////
					$host="192.168.2.250";
					$user="root";
					$pass="";
					$dataname="hanmacerp";
					$jangheon_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$jangheon_db);
					//mysql_query("set names euckr");
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					$result=mysql_query($dbup9,$jangheon_db);
					mysql_close($jangheon_db);

				}

		//******************************************************************************************//
		//*************************파일에 함께 공지되었던 기록이 있으면*************************//
		//******************************************************************************************//

				$PileIn=strpos($home9,"/P");
				if ($PileIn === false) //파일에 없으면;
				{
				}
				else
				{
					///////////파일DB//////////////////////
					$host="192.168.2.249";
					$user="root";
					$pass="";
					$dataname="hanmacerp";
					$piletech_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$piletech_db);
					//mysql_query("set names euckr");
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					$result=mysql_query($dbup9,$piletech_db);
					mysql_close($piletech_db);

				}

				$dbup="update notice_new_test_tbl set name='$korname',title='$title',comment='$subtext',popup='$popoption',view_start='$view_start',view_end='$view_end',pop_start='$pop_start',pop_end='$pop_end',forcepopup='$forcepopup', sub='$notice_sub' ";

				if($file_delete_check == 'Y'){
					$filename = "";
				}
				//echo $file_delete_check;

				if (($userfile_name <>"" and $userfile_size <>0) or $file_delete_check == 'Y'){
					$dbup=$dbup.",filename='$filename'";
				}
				$dbup=$dbup."where id=$id";

				//echo $dbup."<br>";
				$result=mysql_query($dbup,$db);

				}
				// else  $CHECK->err_msg("*^^* : 비밀번호가 틀립니다.");
			}


			$this->smarty->assign('MoveURL',"noticeAdd_controller.php?ActionMode=view&notice_sub=$notice_sub");
			$this->smarty->display("intranet/move_page.tpl");

		}//function UpdateAction()


		//============================================================================
		// 공지사항 delete logic
		//============================================================================
		function DeleteAction()
		{

			global $db,$memberID;
			global $id,$title,$password;
			global $subtext,$wdate,$ip,$filename,$notice_sub;

			$sql="select * from notice_new_test_tbl where id=$id";
			
			$re = mysql_query($sql,$db);

			$filename= mysql_result($re,0,"filename");
			
			$array_filename = explode("/",$filename);
			$filename=$array_filename[sizeof($array_filename)-1];
			
			$comment= mysql_result($re,0,"comment");
			$home= mysql_result($re,0,"home");
			$title= mysql_result($re,0,"title");
			$password= mysql_result($re,0,"pass");
			$wdate= mysql_result($re,0,"wdate");
			$wdate=substr($wdate,0,10);
			$GroupCode = mysql_result($re,0,"GroupCode");
			
			$auth_pk= mysql_result($re,0,"auth_pk");

			$debug_text = "";
			$ECHO_BOOLEAN = true;
			
			$home_confirm=$home;
			//+++++++++++++++++++++++++++++++++++++++++++++++++++
			$insert_company = array();
			if (strpos($home_confirm,"/H") === false){}else{ array_push($insert_company,'HANM'); }
			if (strpos($home_confirm,"/J") === false){}else{ array_push($insert_company,'JANG'); }
			if (strpos($home_confirm,"/P") === false){}else{ array_push($insert_company,'PILE'); }
			if (strpos($home_confirm,"/L") === false){}else{ array_push($insert_company,'HALL'); }
			if (strpos($home_confirm,"/S") === false){}else{ array_push($insert_company,'SAMA'); }
			$cnt_company = count($insert_company);
			//+++++++++++++++++++++++++++++++++++++++++++++++++++
		
			if($filename <> ""){
				

			
				for($i=0; $i<$cnt_company; $i++) {
						
					
					if($ECHO_BOOLEAN){ echo '<br>체크된 회사 = '.$insert_company[$i].'<br>'; }
				
					if($insert_company[$i] =="HANM"){
						//=============================================
						$ftp_server 	= "211.206.127.70";	// FTP 주소
						$ftp_port 		= "23";	// FTP 주소
						$ftp_user_name 	= "noticeuser";		// 접속 ID
						$ftp_user_pass 	= "noticeuser!";	// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="JANG"){
						//=============================================
						$ftp_server 	= "211.206.127.71";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="PILE"){
						//=============================================
						$ftp_server 	= "211.206.127.72";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="HALL"){
						//=============================================
						$ftp_server 	= "1.234.37.143";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
						// 						$ftp_user_name 	= "intranet@h0";			// 접속 ID
						// 						$ftp_user_pass 	= "h@intranet0!%";		// 접속 PW
						//=============================================
					}else if($insert_company[$i] =="SAMA"){
						//=============================================
						$ftp_server 	= "118.220.172.237";	// FTP 주소
						$ftp_port 		= "21";	// FTP 주소
						$ftp_user_name 	= "noticeuser";			// 접속 ID
						$ftp_user_pass 	= "noticeuser!";		// 접속 PW
						// 						$ftp_user_name 	= "samanerp";			// 접속 ID
						// 						$ftp_user_pass 	= "samanerp1234!";		// 접속 PW
						//=============================================
				
					}
						
					//==========================================================================
					// B 호스트 접속
					if(!($conn_id = ftp_connect($ftp_server, $ftp_port))){
						die("$ftp_server : $server_post - connect failed");
					}else{
						// 				echo "connect success";
						// 				exit();
					}
					//----------------------------------------------------------------------
					// B 호스트 로그인
					if(!ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)){
						die("$ftp_user_name - login failed");
					}
					//----------------------------------------------------------------------
					if($GroupCode=="99"){
						//전체공지
						$FileLocation="/notice/noticefile/test/";
					} else{
						//부서별
						$FileLocation="/notice/noticefile/test/".$GroupCode."/";
					}
					//----------------------------------------------------------------------
					//업로드할 폴더로 이동한다.
					//	ftp_chdir($conn_id, $FileLocation);
					if (ftp_chdir($conn_id, $FileLocation)) {
						//echo "successfully created $FileLocation\n"."<br>";
					} else {
						//echo "There was a problem while creating $FileLocation\n";
						$debug_text .= "ftp_chdir FAIL : FileLocation=".$FileLocation."<br>";
					}
					//---------------------------------------------------------------------
				
					//===========================================================
					//기존파일 삭제 start
					
					if($ECHO_BOOLEAN){ 
						echo '기존파일 삭제 start'.'<br>';
						echo 'filename_confirm='.$filename_confirm.'<br>';
						echo 'FileLocation='.$FileLocation.'<br>';
					}
					//ftp_chdir($conn_id, $FileLocation);
					
					$res = ftp_size($conn_id, $filename_confirm);
					if ($res != -1) {
						if(!ftp_delete($conn_id, $filename_confirm)){
							//echo" <script> window.alert ('파일을 지정한 디렉토리에서 삭제 하는 데 실패했습니다._1');</script>";
							//exit;
							$debug_text .= "file delete fail = ".$filename_confirm.'<br>';
						}
					}
					if($ECHO_BOOLEAN){
						echo '기존파일 삭제 end'.'<br>';
					}
					///ftp_quit($fc);
					//기존파일 삭제 end
					//===========================================================
			
					ftp_close($conn_id);
					//$re_unlink = unlink($vupload); //파일삭제
					//==========================================================================
						
						
				}//for
			
			}//파일네임 존재시 $filename<>""
			
// 						if($auth_pk!="" ){
// 							//저장된 첨부파일 new : 삼안방식 : 가족사 새롭게 적용된 방식
// 							if($filename <> "")
// 							{
							
// 								//test시
// 								$file_path ="./../../../intranet_file/notice/noticefile/test/";
// 								//적용시
// 								//$file_path ="./../../../intranet_file/notice/noticefile/test";
// 								$filename=iconv("UTF-8", "EUC-KR",$filename);
// 								$orgfilename = $file_path.$filename;
// 								$exist_org = file_exists("$orgfilename");
									
// 								$debug_text .= "orgfilename=".$orgfilename.'<br>';
							
// 								if($exist_org)
// 								{
// 									$re=unlink("$orgfilename");
// 								}
// 							}
								
								
								
// 							//새롭게 적용된 에디터 방식에 의해서
// 							// 내용에 첨부된 이미지파일은 삭제 안함
									
// 						}else{
// 							//저장된 첨부파일 : 기존 삼안을 제외한 가족사
// 							if($filename <> "")
// 							{
// 								$file_path ="./../../../intranet_file/notice/";
// 								$filename=iconv("UTF-8", "EUC-KR",$filename);
// 								$orgfilename = $file_path.$filename;
// 								$exist_org = file_exists("$orgfilename");
				
// 								$debug_text .= "orgfilename=".$orgfilename.'<br>';
								
// 								if($exist_org)
// 								{
// 									$re=unlink("$orgfilename");
// 								}
// 							}
				
// 							//에디터사용 전 사진업로드된 파일의 경우
// 							//이미지파일 삭제
// 							if (strpos($comment, "imgcontent") > 0 )
// 							{
				
// 								$file_path ="./../../../intranet_file/notice/imgcontent/";
				
// 								$tmpfile=explode("/",$comment);
// 								$no= count($tmpfile)-1;
// 								$filename_is= $tmpfile[$no];
// 								$filename_is=str_replace ( '>', '',$filename_is);
				
// 								$tmpimgfile=$file_path.$filename_is;
								
// 								$debug_text .= "tmpimgfile=".$tmpimgfile.'<br>';
								
// 								$exist = file_exists("$tmpimgfile");
// 								if($exist)
// 								{
// 									$re=unlink("$tmpimgfile");
// 								}
				
// 							}
// 						}
			
			
			
			//======================================================
			// [한맥] 선택시 : 여러개 회사 동시에 공지 : 
			//======================================================
			//---------------------------------------------------
			if($auth_pk=="" ){
				$delsql="delete from notice_new_test_tbl where pass='$password' and title ='$title' and wdate like '$wdate%'";
			}else{
				$delsql="delete from notice_new_test_tbl where auth_pk='$auth_pk' ";
			}
			
			$debug_text .= "delsql=".$delsql.'<br>';
			
			$debug_text .= "delsql2="."delete from notice_new_test_tbl where pass='$password' and title ='$title' and wdate like '$wdate%'".'<br>';
			
			$debug_text .= "home=".$home.'<br>';
			
			
			
			//---------------------------------------------------
			
			$HanmacIn=strpos($home,"/H"); //한맥체크
			if ($HanmacIn === false) {}
			else
			{
				
				$debug_text .= "한맥체크 <br>";
				
				$host="211.206.127.70";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$hanmac_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$hanmac_db);
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");

				//$delsql="delete from notice_new_test_tbl where pass='$password' and title ='$title' and wdate like '$wdate%'";
				
				$result=mysql_query($delsql,$hanmac_db);
				mysql_close($hanmac_db);
			
			}
			
			//======================================================
			// [장헌] 선택시 : 여러개 회사 동시에 공지 : 
			//======================================================
			$JangIn=strpos($home,"/J"); //장헌체크
			if ($JangIn === false) {}
			else
			{
				$debug_text .= "장헌체크 <br>";
				$host="192.168.10.6";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$jangheon_db=mysql_connect($host,$user,$pass);
				mysql_select_db($dataname,$jangheon_db);
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");

				//$delsql="delete from notice_new_test_tbl where pass='$password' and title ='$title' and wdate like '$wdate%'";
			
				$result=mysql_query($delsql,$jangheon_db);
				mysql_close($jangheon_db);
			
			}

			//======================================================
			// [PTC] 선택시 : 여러개 회사 동시에 공지 : 
			//======================================================
			$PileIn=strpos($home,"/P"); //PTC
			if ($PileIn === false) {}
			else
			{
				$debug_text .= "PTC <br>";
				$host="192.168.10.7";
				$user="root";
				$pass="erp";
				$dataname="hanmacerp";
				$pile_db=mysql_connect($host,$user,$pass);
				mysql_select_db("$dataname",$pile_db);
				mysql_set_charset("utf-8",$db);
				mysql_query("set names utf8");

				//$delsql="delete from notice_new_test_tbl where pass='$password' and title ='$title' and wdate like '$wdate%'";
		
				$result=mysql_query($delsql,$pile_db);
				mysql_close($pile_db);
		
			}

			//======================================================
			// [한라산업개발] 선택시 : 여러개 회사 동시에 공지 : 
			//======================================================
			$HallIn=strpos($home,"/L"); //한라산업개발
			if ($HallIn === false) {}
			else
			{
				$debug_text .= "한라 <br>";
				$host ='1.234.37.143';
				$user="root";
				$pass="vbxsystem";
				$dataname="hallaerp";
				$halla_db=mysql_connect($host,$user,$pass);
				mysql_select_db("$dataname",$halla_db);
				mysql_set_charset("utf-8",$halla_db);
				mysql_query("set names utf8");
			
				//$delsql="delete from notice_new_test_tbl where pass='$password' and title ='$title' and wdate like '$wdate%'";
			
				$result=mysql_query($delsql,$halla_db);
				mysql_close($halla_db);
			
			}	

			//======================================================
			//[삼안] 선택시 : 여러개 회사 동시에 공지 : 
			//======================================================
			$SamaIn=strpos($home,"/S"); //삼안
			if ($SamaIn === false) {}
			else
			{
				$debug_text .= "삼안 <br>";
				$host="118.220.172.237";
				$user="root";
				$pass="vbxsystem";
				$dataname="hallaerp";
				$saman_db=mysql_connect($host,$user,$pass);
				mysql_select_db("$dataname",$saman_db);
				mysql_set_charset("utf-8",$saman_db);
				mysql_query("set names utf8");
			
				//$delsql="delete from notice_new_test_tbl where pass='$password' and title ='$title' and wdate like '$wdate%'";
			
				$result=mysql_query($delsql,$saman_db);
				mysql_close($saman_db);
			
			}	
			
// 			echo $debug_text ;
// 			exit();
			
			//$sql = "delete from notice_new_test_tbl where id=$id";
			//mysql_query($sql,$db);

			$this->smarty->assign('MoveURL',"noticeAdd_controller.php?ActionMode=view&notice_sub=$notice_sub");
			$this->smarty->display("intranet/move_page.tpl");



		}//DeleteAction


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

			$sql2="select * from notice_read_test_tbl where notice_id='$id' and MemberNo='$memberID'";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			if(mysql_num_rows($re2) == 0)
			{
				$sql3="insert into notice_read_test_tbl (notice_id,MemberNo,korName,RankCode,GroupCode,ReadDate)";
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
			global $button_type,$Start,$page,$notice_sub;
			global $currentPage,$last_page,$sub_index;

			if( $_SESSION['auth_manager'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);

			if($notice_sub == null){
				$notice_sub = 0;
			}

			$page=15;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();


			/* 팝업 체크 부분 ---------------------------------------------------------------- */
			$all_groupcode="99";
			$GroupCode=$_SESSION['MyGroupCode'];

			/*
			$sql="select * from notice_new_test_tbl  where group_code in('$GroupCode','$all_groupcode') and ((popup='on' and pop_start <= '$today' and pop_end >= '$today') and ((view_start <= '$today' and view_end >= '$today') or (view_start is null  and view_end is null) or (view_start='0000-00-00' and view_end='0000-00-00'))) and level like 'a%' order by id desc";

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
					$ssql="select * from notice_read_test_tbl where notice_id='$pop_row[id]' and MemberNo='$memberID'";
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
				$sql="select korName from member_tbl where MemberNo = '$memberID'";
				$re = mysql_query($sql,$db);

				$korName= mysql_result($re,0,"korName");


				if($notice_sub == 0){
					$sql_total="select * from notice_new_test_tbl where (sub=$notice_sub or sub is null )and group_code in('$GroupCode','$all_groupcode') and ((view_start <= '$today' and view_end >= '$today') or (view_start is null and view_end is null) or (view_start='0000-00-00' and view_end='0000-00-00') or name like '$korName%')";
					
				}else{
					$sql_total="select * from notice_new_test_tbl where sub=$notice_sub and group_code in('$GroupCode','$all_groupcode') and ((view_start <= '$today' and view_end >= '$today') or (view_start is null and view_end is null) or (view_start='0000-00-00' and view_end='0000-00-00') or name like '$korName%')";
						
				}
		
				//select * from notice_new_test_tbl where (sub=0 or sub is null )and group_code in('03','99') and ((view_start <= '2020-11-03' and view_end >= '2020-11-03') or (view_start is null and view_end is null) or (view_start='0000-00-00' and view_end='0000-00-00') or name like '문형석%') order by id desc limit 0, 15 
				
				$sql_num = mysql_query($sql_total,$db);
				
			
				
				
				$TotalRow = mysql_num_rows($sql_num);//총 개수 저장
				$last_start = ceil($TotalRow/15)*10+1;
				$last_page=ceil($TotalRow/$page);
				
		

				$sql = $sql_total." order by id desc limit $Start, $page";
				
				//echo $sql;
				
				$re = mysql_query($sql,$db);
				

			}
			else
			{
				if($sub_index=="1"){ //제목
					$sql_total="select * from notice_new_test_tbl where sub=$notice_sub and group_code in('$GroupCode','$all_groupcode') and level like 'a%' and title like '%$searchv%'";
				}elseif($sub_index=="2"){ //내용
					$sql_total="select * from notice_new_test_tbl where sub=$notice_sub and group_code in('$GroupCode','$all_groupcode') and level like 'a%' and comment like '%$searchv%'";
				}else{ //작성자
					$sql_total="select * from notice_new_test_tbl where sub=$notice_sub and group_code in('$GroupCode','$all_groupcode') and level like 'a%' and name like '%$searchv%'";
				}
				$sql_num = mysql_query($sql_total,$db);
				$TotalRow = mysql_num_rows($sql_num);//총 개수 저장
				$last_start = ceil($TotalRow/15)*10+1;
				$last_page=ceil($TotalRow/$page);

				$sql = $sql_total." order by id desc limit $Start, $page";
				$re = mysql_query($sql,$db);
			}
			//echo "<div style='display:none;'>$sql_total</div>";


			while($re_row = mysql_fetch_array($re))
			{

				if(substr($re_row[wdate],0,10)==$today)
				{
						$re_row[newicon]=true;
				}else
				{
						$re_row[newicon]=false;
				}

				$tmpfile=explode("/",$re_row[filename]);
				$no= count($tmpfile)-1;
				$re_row[filename_is]= $tmpfile[$no];
				
				
// 				$tmpfile=explode("/",$filename);
// 				$no= count($tmpfile)-1;
// 				$filename_is= $tmpfile[$no];
				
				
				
				
				$sqlcount="select count(*) as see from notice_read_test_tbl where notice_id='$re_row[id]' and  MemberNo <>''";
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

			$this->smarty->assign("page_action","noticeAdd_controller.php");

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
			$this->smarty->assign('notice_sub',$notice_sub);

			$this->smarty->display("intranet/common_contents/work_noticeAdd/notice_contents_mvc.tpl");
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
					select * from notice_read_test_tbl where notice_id='$id' and MemberNo <>'' order by GroupCode,RankCode
				)a left join
				(
					select * from notice_new_test_tbl where id='$id'
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
			$this->smarty->display("intranet/common_contents/work_noticeAdd/notice_readlist_mvc.tpl");


		}

		function temp_page(){
			extract($_REQUEST);
			$id =$_REQUEST[id];
			$notice_sub =$_REQUEST[notice_sub];
			$korname =$_REQUEST[korname];
			$GroupCode =$_REQUEST[GroupCode];
			$pass =$_REQUEST[pass];
			$wdate=date("Y-m-d H:i:s");
			$title=$_REQUEST[title];
			$Writer=$_REQUEST[korname];
			$subtext=$_REQUEST[subtext];

			if($mode=="read" || $mode=="mod" || $mode=="click" || $mode=="popup" ){
				//$subtext = nl2br($subtext);
			}
			$subtext = str_replace("<!--[if !supportEmptyParas]-->&nbsp;<!--[endif]--><o:p></o:p>","&nbsp;",$subtext);
			$subtext = str_replace('\"','"',$subtext);

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
			$view_start=$_REQUEST[view_start];
			$view_end=$_REQUEST[view_end];
			$pop_start=$_REQUEST[pop_start];
			$pop_end=$_REQUEST[pop_end];
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
			$this->smarty->assign('comment',$subtext);
			$this->smarty->assign('filename',$filename);
			$this->smarty->assign('filename_is',$filename_is);
			$this->smarty->assign('filesize',$filesize);
			$this->smarty->assign('view_start',$view_start);
			$this->smarty->assign('view_end',$view_end);
			$this->smarty->assign('pop_start',$pop_start);
			$this->smarty->assign('pop_end',$pop_end);
			$this->smarty->assign('forcepopup',$forcepopup);
			$this->smarty->assign('Writer',$Writer);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('notice_sub',$notice_sub);

			$this->smarty->assign("page_action","noticeAdd_controller.php");
			$this->smarty->display("intranet/common_contents/work_noticeAdd/notice_read_popup_mvc.tpl");

		}

}
?>