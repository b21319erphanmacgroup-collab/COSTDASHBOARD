<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

	/***************************************
	* 기술자료,경제특강,기획특강
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/
	include "../inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";

	$CompanyKind=searchCompanyKind();

	extract($_GET);
	class LectureLogic {
		var $smarty;
		function LectureLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//==================================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 I	등록 페이지 Logic=========================================//
		//==================================================================================//

		function InsertPage()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $mode,$page,$currentPage,$out;
			global $techNo,$kind,$company;
			global $Category,$no,$file_type,$Com;
			global $Company_Kind,$group_code,$group_name,$rank_code,$rank_name,$writer_name;



			if($Category =="1") //기술번역서
			{

				$sigon="n";
				$query_data = array();

				$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName= '1'  order by code";
				$re = mysql_query($query01,$db);
				while($re_row = mysql_fetch_array($re))
				{
					array_push($query_data,$re_row);
				}

				$query_data2 = array();
				// systemconfig_tbl에 리스트 추가
				// $query02 = "select distinct(type1) from publication_list_tbl where datetype ='10'";
				$query02 = "select CodeOrName as type1 from systemconfig_tbl where SysKey = 'PublicationList'";
				$re2 = mysql_query($query02,$db);
				while($re_row2 = mysql_fetch_array($re2))
				{
					if($re_row2[type1] =="시공")
					{
						$sigon="y";
					}
					array_push($query_data2,$re_row2);
				}

				$this->smarty->assign('sigon',$sigon);
				$query_data3 = array();

				/*
				$query03 = "select distinct(type2) from publication_list_tbl where datetype ='10' order by type2";
					$re3 = mysql_query($query03,$db);
					while($re_row3 = mysql_fetch_array($re3))
					{
						array_push($query_data3,$re_row3);
					}
				*/

				$type1="시공";
				$this->smarty->assign('type1',$type1);

				$go_url="intranet/common_contents/work_lecture/technicalbook_inputpage_mvc.tpl";
			}
			else if($Category =="2") //교양특강
			{

				$query_data = array();

				$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName in('13','12','4')  order by code desc";
					$re = mysql_query($query01,$db);
					while($re_row = mysql_fetch_array($re))
					{
						array_push($query_data,$re_row);
					}

					$go_url="intranet/common_contents/work_lecture/economy_inputpage_mvc.tpl";
			}
			else if($Category =="3") //기획특강
			{

				$query_data = array();

				$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName in('10','5','9','11','300') order by code";
					$re = mysql_query($query01,$db);
					while($re_row = mysql_fetch_array($re))
					{
						array_push($query_data,$re_row);
					}

					$go_url="intranet/common_contents/work_lecture/havard_inputpage_mvc.tpl";
			}
			else if($Category =="4") //마음연구소
			{

				$query_data = array();

				$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName = '12' order by code";
					$re = mysql_query($query01,$db);
					while($re_row = mysql_fetch_array($re))
					{
						array_push($query_data,$re_row);
					}

					$go_url="intranet/common_contents/work_lecture/mindlab_inputpage_mvc.tpl";
			}
			else if($Category =="5") //입이트이는영어
			{

				$query_data = array();

				$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName = '13' order by code";
					$re = mysql_query($query01,$db);
					while($re_row = mysql_fetch_array($re))
					{
						array_push($query_data,$re_row);
					}

					$go_url="intranet/common_contents/work_lecture/English_inputpage_mvc.tpl";
			}

			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('query_data2',$query_data2);
			$this->smarty->assign('query_data3',$query_data3);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('out',$out);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('Com',$Com);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign('tab_index',$tab_index);

			$this->smarty->assign('Company_Kind',$Company_Kind);			
			$this->smarty->assign('group_code',$group_code);		
			$this->smarty->assign('group_name',$group_name);		
			$this->smarty->assign('rank_code',$rank_code);		
			$this->smarty->assign('rank_name',$rank_name);		
			$this->smarty->assign('writer_name',$writer_name);		

			$this->smarty->assign("page_action","lecture_controller.php");

			$this->smarty->display($go_url);
		}//function InsertPage()

		//============================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 I	등록 Action Logic (insert)==============================//
		//============================================================================//
		function InsertAction()
		{

			global $db,$memberID,$Category;
			global $booktitle,$bookwriter,$publishing;
			global $bookdate,$datetype,$datecustody;
			global $sum,$date,$filename;
			global $userfile,$userfile_name,$userfile_size;
			global $note,$type1,$type2,$out,$Com, $content;



			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘
			$date = $date1."-".$date2."-".$date3;

			//$savedir = "./file";
			$savedir = "./../file";
			$sum_size = '95000000';

			if($bookcode3 == "") {
				$sum = $bookcode."-".$bookcode1."-".$bookcode2;
			} else {
				$sum = $bookcode."-".$bookcode1."-".$bookcode2."-".$bookcode3;
			}

			$query0 = "select max(no)+1 aa from publication_list_tbl";
			$result0 = mysql_query($query0,$db);
			$sum = mysql_result($result0,0,"aa");

			$booktitle2 = str_replace(" ","",$booktitle);
			$booktitle2 = str_replace(".","",$booktitle2);
			$booktitle2 = str_replace("/","",$booktitle2);
			$booktitle2 = str_replace("'","",$booktitle2);
			$booktitle2 = str_replace("!","",$booktitle2);
			$booktitle2 = str_replace("?","",$booktitle2);
			$booktitle2 = str_replace(":","",$booktitle2);
			$booktitle2 = str_replace(";","",$booktitle2);
			$booktitle2 = str_replace("~","",$booktitle2);
			$booktitle2 = str_replace("`","",$booktitle2);
			$booktitle2 = str_replace("|","",$booktitle2);
			$booktitle2 = str_replace("@","",$booktitle2);
			$booktitle2 = str_replace("#","",$booktitle2);
			$booktitle2 = str_replace("$","",$booktitle2);
			$booktitle2 = str_replace("%","",$booktitle2);
			$booktitle2 = str_replace("^","",$booktitle2);
			$booktitle2 = str_replace("&","",$booktitle2);
			$booktitle2 = str_replace("*","",$booktitle2);
			$booktitle2 = str_replace("=","",$booktitle2);
			$booktitle2 = str_replace("+","",$booktitle2);
			$booktitle2 = str_replace("(","",$booktitle2);
			$booktitle2 = str_replace(")","",$booktitle2);

			$booktitle2=$this->bear3StrCut($booktitle2,15,"");

			$bookdate2= explode("-", $bookdate);
			$ext = explode(".", $userfile_name);
			$ext = strtolower(trim($ext[count($ext)-1]));
			if ($bookdate2[0] =="")
			{
				$orifile_name = "[".$date."]"."$booktitle2".".$ext";
			}else
			{
				$orifile_name = "["."$bookdate2[0]"."-"."$bookdate2[1]"."-"."$bookdate2[2]"."]"."$booktitle2".".$ext";
			}


			if ($datecustody==90)
			{
				$date2= explode("-", $date);
				$ext = explode(".", $userfile_name);
				$ext = strtolower(trim($ext[count($ext)-1]));
				$userfile_name = "$date2[0]"."$date2[1]"."$date2[2]"."-"."$sum".".$ext";

			}


			$query10 = "select * from publication_list_tbl where bookcode = '$sum'";
			$result10 = mysql_query($query10,$db);
			$result_10 = mysql_num_rows($result10);



		//******************************************************************************************//
		//****************첨부파일 첨부 부분*****************************************************//
		//******************************************************************************************//

			$path ="./../../../intranet_file/lecture/bookfile/";
			$path_is ="./../../../intranet_file/lecture/bookfile";

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
				$orgfilename = $path."[".$date."]".$filename;
				$exist_org = file_exists("$orgfilename");

				if($exist_org) {
						echo(" <script>
								  window.alert('\"$userfile_name\" 이미 존재합니다.')
								  history.go(-1)
								 </script>
							   ");exit;
							}

				$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
				$vupload = $path.$userfile_name;
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
		//******************************************************************************************//
		//****************첨부파일 첨부 부분********************************* 끝*****************//
		//******************************************************************************************//


			if($Category =="1") //기술번역서
			{
				if($filename <> "")
				{
					$bookdate=date("Y-m-d");

					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,filename,original_filename,filesize,note,type1,type2) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$filename','$orifile_name','$filesize','$note','$type1','$type2')";
				}
				else
				{
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,note,type1,type2) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$note','$type1','$type2')";
				}
			}else if($Category <>"1")//기술번역서가 아니면
			{
				if($filename != ""){
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,filename,original_filename,filesize,note, content) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$filename','$orifile_name','$filesize','$note','$content')";
				}
				else
				{
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,note) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$note')";
				}
				/*
			}else if($Category <>"1")//기술번역서가 아니면
			{
				if($filename != "")
				{
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,filename,original_filename,filesize,note) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$filename','$orifile_name','$filesize','$note')";
				}
				else
				{
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,note) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$note')";
				}
				*/
			}

			//echo $query00."<br>";
			mysql_query($query00);

			if($out=="out")
			{

				$this->smarty->assign('target',"reload");
				$this->smarty->assign('MoveURL',"http://intranet.hallasanup.com/intranet/sys/controller/lecture_controller.php?ActionMode=ActionMode=view&Category=1&sub_index=1&tab_index=5");
				$this->smarty->display("intranet/move_page.tpl");
			}else
			{
				if($Category =="1") //기술번역서
				{
					$this->smarty->assign('target',"reload");

					if($type1=="도로")
					{
						$tab_index=2;
					}else if($type1=="구조")
					{
						$tab_index=3;
					}
					else if($type1=="지반")
					{
						$tab_index=4;
					}
					else if($type1=="시공")
					{
						$tab_index=5;
					}

				}
				if($Com=="jangheon")
				{
					$this->smarty->assign('MoveURL',"http://erp.jangheon.co.kr/intranet/sys/controller/lecture_controller.php?ActionMode=view&Category=$Category&tab_index=$tab_index&memberID=$memberID");
				}else
				{
					$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=view&Category=$Category&tab_index=$tab_index");
				}


				$this->smarty->display("intranet/move_page.tpl");

			}


		}//function InsertAction()

		//==================================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 LIST Logic=================================================//
		//==================================================================================//
		function UpdateReadPage()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $mode,$page,$currentPage;
			global $techNo,$kind,$mode,$out;
			global $Category,$no,$file_type,$ActionMode,$Com;

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$Admin=true;
				$this->smarty->assign('Admin',$Admin);
			}else{
				$Admin=false;
				$this->smarty->assign('Admin',$Admin);
			}
				$this->smarty->assign('memberID',$memberID);

			$query_data = array();
			$query_data3 = array();

			$sql = "select * from publication_list_tbl where no = '$no'";
			$re = mysql_query($sql,$db);
			$type1 = mysql_result($re,0,"type1");
			$type2 = mysql_result($re,0,"type2");
			$booktitle = mysql_result($re,0,"booktitle");
			$bookwriter = mysql_result($re,0,"bookwriter");
			$publishing = mysql_result($re,0,"publishing");
			$bookdate = mysql_result($re,0,"bookdate");
			$datetype = mysql_result($re,0,"datetype");
			$filename = mysql_result($re,0,"filename");
			$original_filename = mysql_result($re,0,"original_filename");

			$type1 = mysql_result($re,0,"type1");

			$note = mysql_result($re,0,"note");
			$note2 = mysql_result($re,0,"note2");
			$content = mysql_result($re,0,"content");

			if($mode=="read")
			{
				$note = nl2br($note);
				$note2 = nl2br($note2);
			}
			if($Category =="1") //기술번역서
			{
				if($mode=="read")
				{
					$go_url="intranet/common_contents/work_lecture/technicalbook_input_mvc.tpl";
				}
				else if($mode=="mod")
				{

					$query_data = array();

					$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName= '1'  order by code";
						$re = mysql_query($query01,$db);
						while($re_row = mysql_fetch_array($re))
						{
							array_push($query_data,$re_row);
						}

					$query_data2 = array();

					$query02 = "select distinct(type1) from publication_list_tbl where datetype ='10'";
						$re2 = mysql_query($query02,$db);
						while($re_row2 = mysql_fetch_array($re2))
						{
							array_push($query_data2,$re_row2);
						}

					$query_data3 = array();

					$query03 = "select distinct(type2) from publication_list_tbl where datetype ='10' order by type2";
						$re3 = mysql_query($query03,$db);
						while($re_row3 = mysql_fetch_array($re3))
						{
							array_push($query_data3,$re_row3);
						}

					$go_url="intranet/common_contents/work_lecture/technicalbook_inputpage_mvc.tpl";
				}


			}
			else  //경제특강,하버드특강
			{
				if($mode=="read")
				{
					if($file_type =="music")
					{
						$go_url="intranet/common_contents/work_lecture/music_mvc.tpl";
					}else if($file_type =="video")
					{
						$go_url="intranet/common_contents/work_lecture/video_mvc.tpl";
					}
					else if($file_type =="doc")  //하버드특강->경영보고서
					{
						$i=1;
						$sql3 = "select * from publication_list_tbl where type1 = '$type1'";

						$re3 = mysql_query($sql3,$db);
						while($re_row3 = mysql_fetch_array($re3))
						{
							if($i==1)
							{

								$index_name=$re_row3[note];
								$index_summary=$re_row3[note2];
								if($mode=="read")
								{
									$index_name=nl2br($index_name);
									$index_summary=nl2br($index_summary);
								}

								$re_row3[filename_IS]=iconv("UTF-8", "EUC-KR",$re_row3[filename]);

								$this->smarty->assign('index_name',$index_name);
								$this->smarty->assign('index_summary',$index_summary);
							}
							array_push($query_data3,$re_row3);
							$i++;
						}

						$this->smarty->assign('query_data3',$query_data3);
						$go_url="intranet/common_contents/work_lecture/doc_mvc.tpl";
					}
				}
				else if($mode=="mod" and $Category=="2")
				{

					$query_data = array();

					$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName= '4'  order by code";
						$re = mysql_query($query01,$db);
						while($re_row = mysql_fetch_array($re))
						{
							array_push($query_data,$re_row);
						}

						$go_url="intranet/common_contents/work_lecture/economy_inputpage_mvc.tpl";
				}
				else if($mode=="mod" and $Category=="3")
				{

						$query_data = array();

						$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName in('10','5','9','11','300') order by code";
							$re = mysql_query($query01,$db);
							while($re_row = mysql_fetch_array($re))
							{
								array_push($query_data,$re_row);
							}

						$go_url="intranet/common_contents/work_lecture/havard_inputpage_mvc.tpl";
				}
				else if($mode=="mod" and $Category=="4")
				{

						$query_data = array();

						$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName = '12' order by code";
							$re = mysql_query($query01,$db);
							while($re_row = mysql_fetch_array($re))
							{
								array_push($query_data,$re_row);
							}

						$go_url="intranet/common_contents/work_lecture/mindlab_inputpage_mvc.tpl";
				}
				else if($mode=="mod" and $Category=="5")
				{

						$query_data = array();

						$query01 =  "select * from systemconfig_tbl where SysKey = 'LIBPartCode' and CodeOrName ='13' order by code";
							$re = mysql_query($query01,$db);
							while($re_row = mysql_fetch_array($re))
							{
								array_push($query_data,$re_row);
							}

						$go_url="intranet/common_contents/work_lecture/English_inputpage_mvc.tpl";
				}
			}


			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('file_type',$file_type);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('booktitle',$booktitle);
			$this->smarty->assign('type1',$type1);
			$this->smarty->assign('type2',$type2);
			$this->smarty->assign('bookwriter',$bookwriter);
			$this->smarty->assign('publishing',$publishing);
			$this->smarty->assign('bookdate',$bookdate);
			$this->smarty->assign('datetype',$datetype);
			$this->smarty->assign('filename',$filename);
			$this->smarty->assign('original_filename',$original_filename);
			$this->smarty->assign('filename2',$filename2);
			$this->smarty->assign('note',$note);
			$this->smarty->assign('note2',$note2);
			$this->smarty->assign('content',$content);
			$this->smarty->assign('index_name',$index_name);
			$this->smarty->assign('index_summary',$index_summary);
			$this->smarty->assign('no',$no);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('query_data2',$query_data2);
			$this->smarty->assign('query_data3',$query_data3);
			$this->smarty->assign('out',$out);
			$this->smarty->assign('Com',$Com);
			$this->smarty->display($go_url);//intranet/common_contents/work_lecture/video_mvc.tpl
		}

		//============================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 I	등록 Action Logic (insert)==============================//
		//============================================================================//
		function UpdateAction()
		{

			global $db,$memberID,$Category,$mode;
			global $booktitle,$bookwriter,$publishing;
			global $bookdate,$datetype,$datecustody;
			global $sum,$date,$filename,$no;
			global $userfile,$userfile_name,$userfile_size;
			global $note,$type1,$type2,$out,$tab_index,$Com, $content;

			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘
			$date = $date1."-".$date2."-".$date3;

			//$savedir = "./file";
			$savedir = "./../file";
			$sum_size = '95000000';


			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$Admin=true;
				$this->smarty->assign('Admin',$Admin);
			}else{
				$Admin=false;
				$this->smarty->assign('Admin',$Admin);
			}

			if($bookcode3 == "") {
				$sum = $bookcode."-".$bookcode1."-".$bookcode2;
			} else {
				$sum = $bookcode."-".$bookcode1."-".$bookcode2."-".$bookcode3;
			}

			$query0 = "select max(no)+1 aa from publication_list_tbl";
			$result0 = mysql_query($query0,$db);
			$sum = mysql_result($result0,0,"aa");
			$sum2 = $sum-1;

			$booktitle2 = str_replace(" ","",$booktitle);
			$booktitle2 = str_replace(".","",$booktitle2);
			$booktitle2 = str_replace("/","",$booktitle2);
			$booktitle2 = str_replace("'","",$booktitle2);
			$booktitle2 = str_replace("!","",$booktitle2);
			$booktitle2 = str_replace("?","",$booktitle2);
			$booktitle2 = str_replace(":","",$booktitle2);
			$booktitle2 = str_replace(";","",$booktitle2);
			$booktitle2 = str_replace("~","",$booktitle2);
			$booktitle2 = str_replace("`","",$booktitle2);
			$booktitle2 = str_replace("|","",$booktitle2);
			$booktitle2 = str_replace("@","",$booktitle2);
			$booktitle2 = str_replace("#","",$booktitle2);
			$booktitle2 = str_replace("$","",$booktitle2);
			$booktitle2 = str_replace("%","",$booktitle2);
			$booktitle2 = str_replace("^","",$booktitle2);
			$booktitle2 = str_replace("&","",$booktitle2);
			$booktitle2 = str_replace("*","",$booktitle2);
			$booktitle2 = str_replace("=","",$booktitle2);
			$booktitle2 = str_replace("+","",$booktitle2);
			$booktitle2 = str_replace("(","",$booktitle2);
			$booktitle2 = str_replace(")","",$booktitle2);

			$booktitle2=$this->bear3StrCut($booktitle2,15,"");

			if ($datecustody==90)
			{
				$date2= explode("-", $date);
				$ext = explode(".", $userfile_name);
				$ext = strtolower(trim($ext[count($ext)-1]));
				$userfile_name = "$date2[0]"."$date2[1]"."$date2[2]"."-"."$sum2".".$ext";

			}


			$query10 = "select * from publication_list_tbl where bookcode = '$sum'";
			$result10 = mysql_query($query10,$db);
			$result_10 = mysql_num_rows($result10);


			$query02 = "select * from publication_list_tbl where no ='$no'";
				$re2 = mysql_query($query02,$db);
				while($re_row2 = mysql_fetch_array($re2))
				{
					$filename=$re_row2[filename];
				}

		//******************************************************************************************//
		//****************첨부파일 첨부 부분*****************************************************//
		//******************************************************************************************//

			$path ="./../../../intranet_file/lecture/bookfile/";
			$path_is ="./../../../intranet_file/lecture/bookfile";
			$userfile_name = str_replace(" ","",$userfile_name);

			if($userfile)
			{
				if($userfile_name <>"" && $userfile_size <>0)
					{ //첨부파일 있으면서 수정이면

						$bookdate2= explode("-", $bookdate);
						$ext = explode(".", $userfile_name);
						$ext = strtolower(trim($ext[count($ext)-1]));

						if ($bookdate2[0] =="")
						{
							$orifile_name = "[".$date."]"."$booktitle2".".$ext";
						}else
						{
							$orifile_name = "["."$bookdate2[0]"."-"."$bookdate2[1]"."-"."$bookdate2[2]"."]"."$booktitle2".".$ext";
						}

						//echo $orifile_name."<Br>";

						$filename=iconv("UTF-8", "EUC-KR",$filename);
						$orgfilename = $path.$filename;
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
					$orgfilename = $path."[".$date."]".$filename;
					$exist_org = file_exists("$orgfilename");

					if($exist_org) {
							echo(" <script>
									  window.alert('\"$userfile_name\" 이미 존재합니다.')
									  history.go(-1)
									 </script>
								   ");exit;
								}

					$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
					$vupload = $path.$userfile_name;
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
			}
		//******************************************************************************************//
		//****************첨부파일 첨부 부분********************************* 끝*****************//
		//******************************************************************************************//


			if($Category =="1") //기술번역서
			{
				$bookdate=date("Y-m-d");
				if($orifile_name <> "")
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',bookcode='$sum',updateuser='$memberID' ,insertdate='$date',filename='$filename',original_filename='$orifile_name',filesize='$filesize',note='$note',type1='$type1',type2='$type2' where no='$no'";
				}
				else
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',bookcode='$sum',updateuser='$memberID' ,insertdate='$date',note='$note',type1='$type1',type2='$type2' where no='$no'";
				}
			}
			else if($Category <>"1")//기술번역서가 아니면
			{
				if($orifile_name != "")
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',updateuser='$memberID' ,insertdate='$date',filename='$filename',original_filename='$orifile_name',filesize='$filesize',note='$note', content='$content' where no='$no'";
				}
				else
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',updateuser='$memberID' ,insertdate='$date',note='$note', content='$content' where no='$no'";
				}
			}
			//echo $query00."<br>";
			mysql_query($query00);

			if($Category<>1)
			{
				$this->smarty->assign('target',"edu");
			}



			if($out=="out")
			{
				$this->smarty->assign('target',"reload");
				$this->smarty->assign('MoveURL',"http://intranet.hallasanup.com/intranet/sys/controller/lecture_controller.php?ActionMode=ActionMode=view&Category=1&sub_index=1&tab_index=5");
				$this->smarty->display("intranet/move_page.tpl");
			}else
			{

				if($Category =="1") //기술번역서
				{
					$this->smarty->assign('target',"reload");

					if($Com=="jangheon")
					{
						$this->smarty->assign('MoveURL',"http://erp.jangheon.co.kr/intranet/sys/controller/lecture_controller.php?ActionMode=update_page&mode=read&Category=$Category&memberID=$memberID&no=$no&tab_index=$tab_index");
					}else
					{
						$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=update_page&mode=read&Category=$Category&memberID=$memberID&no=$no&sub_index=1&tab_index=$tab_index");
					}

					$this->smarty->display("intranet/move_page.tpl");

				}else
				{
					$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=view&Category=$Category&memberID=$memberID");
					$this->smarty->display("intranet/move_page.tpl");
				}


			}


		}//function UpdateAction()


		//===================================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 삭제 Logic=================================================//
		//===================================================================================//
		function DeleteAction()
		{

			global $db,$memberID,$Category;
			global $booktitle,$bookwriter,$publishing;
			global $bookdate,$datetype,$datecustody;
			global $sum,$date,$filename,$no;
			global $userfile,$userfile_name,$userfile_size;
			global $note,$type1,$type2,$out,$tab_index,$Com;

			$sql="select * from publication_list_tbl where no=$no";
			$re=mysql_query($sql,$db);
			$filename = mysql_result($re, 0, "filename");

			$file_path ="./../../../intranet_file/lecture/bookfile/";

			$filename=iconv("UTF-8", "EUC-KR",$filename);
			$orgfilename = $file_path.$filename;
			$exist_org = file_exists("$orgfilename");
			if($exist_org)
				{
					$re=unlink("$orgfilename");
				}
			$sql = "delete from publication_list_tbl where no='$no'";
			//echo $sql."<br>";
			mysql_query($sql,$db);




			if($out=="out")
			{
				$this->smarty->assign('target',"reload");
				$this->smarty->assign('MoveURL',"http://intranet.hallasanup.com/intranet/sys/controller/lecture_controller.php?ActionMode=ActionMode=view&Category=1&sub_index=1&tab_index=5");
				$this->smarty->display("intranet/move_page.tpl");
			}else
			{
				if($Category<>1)
				{
					$this->smarty->assign('target',"edu2");
				}



				if($Com=="jangheon")
				{
					$this->smarty->assign('MoveURL',"http://erp.jangheon.co.kr/intranet/sys/controller/lecture_controller.php?ActionMode=view&Category=$Category&memberID=$memberID&tab_index=$tab_index");
				}else
				{
					$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=view&Category=$Category&memberID=$memberID&tab_index=$tab_index");
				}


				$this->smarty->display("intranet/move_page.tpl");
			}


		}//function DeleteAction()


		//==================================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 읽기 Logic=================================================//
		//==================================================================================//
		function View()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $Category,$searchv,$memberID,$editmode;
			global $Company_Kind,$group_code,$group_name,$rank_code,$rank_name,$writer_name;

			/*
				Category=1 기술번역서
				Category=2 경제특강
				Category=3 하버드특강
			*/

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$Admin=true;
				$this->smarty->assign('Admin',$Admin);
			}else{
				$Admin=false;
				$this->smarty->assign('Admin',$Admin);
			}

			if($Company_Kind=="")
			{
				$ChkCompany=$_SESSION['SS_CompanyKind'];
			}else
			{
				$ChkCompany=$Company_Kind;
			}

			$page=15;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array();


			if($Category =="1")  //기술번역서
			{
				$cut_length ="80";
				if($tab_index=="") $tab_index=2;

				//김윤하,정태원,김수현
				/*
				if($memberID=="T03225" || $memberID=="M21201" || $memberID=="M10202" )
				{
					$tab_Titel = array('도로','구조','지반','시공');
					$tab_value = array('2','3','4','5');
				}else
				{
					$tab_Titel = array('도로','구조','지반');
					$tab_value = array('2','3','4');
				}
				*/

				$tab_Titel = array('도로','구조','지반','시공');
				$tab_value = array('2','3','4','5');

				$go_url="intranet/common_contents/work_lecture/technicalbook_contents_mvc.tpl";

				if($tab_index=="1") //기술번역서 전체
				{
					if($selt=="제목")//제목찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and booktitle like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10' and booktitle like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and note like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10' and note like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else
					{
						$sql="select * from publication_list_tbl where datetype ='10'";
						$sql2 = "select * from publication_list_tbl where datetype='10' order by bookdate desc limit $Start, $page";
					}
				}
				else if($tab_index=="2") //기술번역서 도로
				{
					if($selt=="제목")//제목찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='도로' and booktitle like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='도로' and booktitle like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='도로' and note like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='도로' and note like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='도로'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='도로' order by bookdate desc limit $Start, $page";
					}
				}
				else if($tab_index=="3") //기술번역서 구조
				{
					if($selt=="제목")//제목찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='구조' and booktitle like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='구조' and booktitle like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='구조' and note like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='구조' and note like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='구조'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='구조' order by bookdate desc limit $Start, $page";
					}
				}
				else if($tab_index=="4") //기술번역서 지반
				{
					if($selt=="제목")//제목찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='지반' and booktitle like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='지반' and booktitle like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='지반' and note like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='지반' and note like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='지반'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='지반' order by bookdate desc limit $Start, $page";
					}
				}
				else if($tab_index=="5") //기술번역서 시공
				{
					if($selt=="제목")//제목찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='시공' and booktitle like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='시공' and booktitle like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='시공' and note like '%$searchv%'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='시공' and note like '%$searchv%' order by bookdate desc limit $Start, $page";
					}
					else
					{
						$sql="select * from publication_list_tbl where datetype ='10' and type1='시공'";
						$sql2 = "select * from publication_list_tbl where datetype='10'  and type1='시공' order by bookdate desc limit $Start, $page";
					}
				}
			}else if ($Category =="2")  //교양특강
			{
				$cut_length="130";
				if($tab_index=="") $tab_index=91;
				//$tab_Titel = array('최근자료15','자기개발/리더십','마케팅','경영기법/전략','비즈니스','경제전망','경제정책','기타');
				$tab_Titel = array('입이트이는영어','마음연구소','자기개발/리더십','마케팅','경영기법/전략','비즈니스','경제전망','경제정책','기타');
				$tab_value = array('91','90','40','41','42','43','44','45','46');

				$go_url="intranet/common_contents/work_lecture/economy_contents_mvc.tpl";
				if($tab_index==0)
				{ //최근자료 인경우
					$sql = "select * from publication_list_tbl where datetype in('40','41','42','43','44','45','46')";
					if($selt == "")
					{
						$sql.=" order by bookdate desc limit 0, 15";
					}

				}else
				{
					//$sql = "select * from publication_list_tbl where datetype ='$tab_index'";
					$sql = "select *,(select count(*) from publication_read_tbl where publication_no= publication_list_tbl.no and  writer_code='$memberID' and CompanyKind='$ChkCompany') as read_no from publication_list_tbl where datetype ='$tab_index'";
					
				}

				if($selt=="제목")//제목찾기
				{	$sql .= " and booktitle like '%$searchv%'";
				}
				else if($selt=="내용")//내용찾기
				{
					$sql .= " and note like '%$searchv%'";
				}
				else if($selt=="강연자")//강연자찾기
				{
					$sql = " and bookwriter like '%$searchv%'";
				}

				if($tab_index==0){ //최근자료 인경우
					$sql2 .=$sql;
				}else{
					$sql2 .=$sql." order by bookdate desc limit $Start, $page";
				}

			}else if ($Category =="3")  //기획특강
			{
				$cut_length="140";
				if($tab_index=="") $tab_index=48;
				/*
				$tab_Titel = array('최근자료15','하버드특강','EBS특강','경영보고서','영상보고서','인문학특강','기타');
				$tab_value = array('0','48','49','200','47','300','80');
				*/
				$tab_Titel = array('하버드특강','EBS특강','경영보고서','영상보고서','인문학특강','기타');
				$tab_value = array('48','49','200','47','300','80');

				$go_url="intranet/common_contents/work_lecture/havard_contents_mvc.tpl";


				if($tab_index==0){ //최근자료 인경우
					$sql = "select * from publication_list_tbl where datetype in('48','49','200','47','300','100','80')";
					if($selt == "")
					{
						$sql.=" order by bookdate desc limit 0, 15";
					}

				}else
				{
					$sql = "select *,(select  count(*) from publication_read_tbl where publication_no= publication_list_tbl.no and  writer_code='$memberID' and CompanyKind='$ChkCompany') as read_no from publication_list_tbl where datetype ='$tab_index'";
				}




				if($tab_index=="200" ){   ////기획특강 경영보고서

					if($selt=="제목")//제목찾기
					{
						$sql = " and booktitle like '%$searchv%' group by type1 desc";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql = " and note like '%$searchv%' group by type1 desc";
					}
					else if($selt=="강연자")
					{
						$sql = " and bookwriter like '%$searchv%' group by type1 desc";
					}
				}
				else   //기획특강 중 경영보고서가 아니면
				{

					if($selt=="제목")//제목찾기
					{	$sql .= " and booktitle like '%$searchv%'";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql .= " and note like '%$searchv%'";
					}
					else if($selt=="강연자")//강연자찾기
					{
						$sql = " and bookwriter like '%$searchv%'";
					}
				}




				if($tab_index==0){ //최근자료 인경우
					$sql2 .=$sql;
				}else{
					//$sql2 .=$sql." group by type1 order by bookdate desc limit $Start, $page";
					$sql2 .=$sql." order by bookdate desc limit $Start, $page";
				}

			}

			else if ($Category =="4")  //마음연구소
			{
				$cut_length="130";
				if($tab_index=="") $tab_index=0;
				//$tab_Titel = array('최근자료15','자기개발/리더십','마케팅','경영기법/전략','비즈니스','경제전망','경제정책','기타');
				//$tab_value = array('0','40','41','42','43','44','45','46');

				$go_url="intranet/common_contents/work_lecture/mind_lab_mvc.tpl";

				if($tab_index==0)
				{ //최근자료 인경우
					$sql = "select * from publication_list_tbl where datetype = '90'";

				}
				else
				{
					$sql = "select * from publication_list_tbl where datetype ='$tab_index'";
				}

				if($selt=="제목")//제목찾기
				{	$sql .= " and booktitle like '%$searchv%'";
				}

				$sql2 .=$sql." order by bookdate desc limit $Start, $page";

			}

			else if ($Category =="5")  //입이트이는영어
			{
				$cut_length="130";
				if($tab_index=="") $tab_index=0;
				//$tab_Titel = array('최근자료15','자기개발/리더십','마케팅','경영기법/전략','비즈니스','경제전망','경제정책','기타');
				//$tab_value = array('0','40','41','42','43','44','45','46');

				$go_url="intranet/common_contents/work_lecture/English_contents_mvc.tpl";
				if($tab_index==0)
				{ //최근자료 인경우
					$sql = "select * from publication_list_tbl where datetype = '91'";
				}
				else
				{
					$sql = "select * from publication_list_tbl where datetype ='$tab_index'";
				}

				if($selt=="제목")//제목찾기
				{	$sql .= " and booktitle like '%$searchv%'";
				}

				$sql2 .=$sql." order by bookdate desc limit $Start, $page";

			}

			//echo "sql : ".$sql."<br>";
			//echo "sql2 : ".$sql2."<br>";
			$re = mysql_query($sql,$db);
			$TotalRow = mysql_num_rows($re);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/$page);

			//echo "sql2 : ".$sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{

				if(eregi("mp3",$re_row2[filename]) or eregi("wma",$re_row2[filename]))
				{   $re_row2[file_type]="music";
					$re_row2[file_img]="voice_icon.png";
				}else if(eregi("flv",$re_row2[filename]) or eregi("wmv",$re_row2[filename]) or eregi("mp4",$re_row2[filename]) or eregi("asf",$re_row2[filename]) )
				{	$re_row2[file_type]="video";
					$re_row2[file_img]="movie_icon.png";
				}else
				{
					if($re_row2[datetype]=="47" || $re_row2[datetype]=="49")
					{
						$re_row2[file_type]="video";
						$re_row2[file_img]="movie_icon.png";
					}else
					{
						$re_row2[file_type]="doc";
						$re_row2[file_img]="document_icon.png";
					}
				}


				if($Category =="1")  //기술번역서
				{
					$re_row2[booktitle]=utf8_strcut($re_row2[booktitle],'28','...');
					$re_row2[note_cut]=utf8_strcut($re_row2[note],'36','...');
				}else
				{
					$re_row2[note_cut]=utf8_strcut($re_row2[note],$cut_length,'...');
				}



				$re_row2[bookwriter]=str_replace("(","<br>(",$re_row2[bookwriter]);
				//$re_row2[bookdate]= substr($re_row2[bookdate],2,2).".".substr($re_row2[bookdate],5,2).".".substr($re_row2[bookdate],8,2);

				//echo $re_row2[booktitle]."<br>";


				//소감문
				$sql_read="select count(*) readcount from publication_read_tbl where publication_no='$re_row2[no]'";
				$re_read = mysql_query($sql_read,$db);
				if(mysql_num_rows($re_read) > 0)
				{
					$re_row2[readcount]= mysql_result($re_read,0,"readcount");
				}
				else
				{
					$re_row2[readcount]=0;
				}

				array_push($query_data,$re_row2);
			}


			if($currentPage == "") $currentPage = 1;

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();




			$this->smarty->assign("page_action","lecture_controller.php");
			$this->smarty->assign('selt',$selt);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('tab_Titel',$tab_Titel);
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('editmode',$editmode);
			$this->smarty->assign('searchv',$searchv);
			

			$this->smarty->assign('Company_Kind',$Company_Kind);
			$this->smarty->assign('group_code',$group_code);
			$this->smarty->assign('group_name',$group_name);
			$this->smarty->assign('rank_code',$rank_code);
			$this->smarty->assign('rank_name',$rank_name);
			$this->smarty->assign('writer_name',$writer_name);


			$this->smarty->display($go_url);

		}



		//==================================================================================//
		//==소감문 리스트 Logic=============================================================//
		//==================================================================================//
		function ReadList()
		{
			global $db,$memberID,$CompanyKind;
			global $Category,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $publication_no;
			global $Company_Kind,$group_code,$group_name,$rank_code,$rank_name,$writer_name;

			$sql="select * from publication_list_tbl where no='$publication_no'";
			$re = mysql_query($sql,$db);
			$booktitle=mysql_result($re,0,"booktitle");


			$query_data2 = array();
			$sql2="select * from publication_read_tbl where publication_no='$publication_no' order by CompanyKind,group_code,rank_code";
			//echo $sql2,"<br>";

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				$re_row2[contents]=utf8_strcut($re_row2[contents],'50','...');
				array_push($query_data2,$re_row2);
			}

			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('booktitle',$booktitle);
			$this->smarty->assign('query_data2',$query_data2);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('publication_no',$publication_no);

			$this->smarty->assign('Company_Kind',$Company_Kind);
			$this->smarty->assign('group_code',$group_code);
			$this->smarty->assign('group_name',$group_name);
			$this->smarty->assign('rank_code',$rank_code);
			$this->smarty->assign('rank_name',$rank_name);
			$this->smarty->assign('writer_name',$writer_name);

			$this->smarty->display("intranet/common_contents/work_lecture/read_list_mvc.tpl");
		}


		//==================================================================================//
		//==소감문 입력폼 Logic=============================================================//
		//==================================================================================//
		function ReadInput()
		{
			global $db,$memberID,$CompanyKind;
			global $Category,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $publication_no;
			global $Company_Kind,$group_code,$group_name,$rank_code,$rank_name,$writer_name;

			$write_date=date("Y-m-d");
			$this->smarty->assign('write_date',$write_date);


			$sql =      " select a.korName as Name,a.GroupCode,b.Name as GroupName,a.Name as Position,a.RankCode as RankCode,a.ExtNo as ExtNo		 ";
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

			//echo $sql."<br>";

			$result=mysql_query($sql,$db);
			$row=mysql_fetch_array($result);
			$Name=$row[Name];
			$GroupCode=$row[GroupCode];
			$GroupCode = sprintf("%02d", $GroupCode);
			$GroupName=$row[GroupName];
			$Position=$row[Position];
			$RankCode=$row[RankCode];

			$this->smarty->assign('writer_code',$memberID);
			$this->smarty->assign('Name',$Name);
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('GroupName',$GroupName);
			$this->smarty->assign('Position',$Position);
			$this->smarty->assign('RankCode',$RankCode);

			if($Company_Kind=="")
			{
				$writer_name=$Name;
				$group_code=$GroupCode;
				$group_name=$GroupName;
				$rank_code=$RankCode;
				$rank_name=$Position;

			}
		
			$sql2="select * from publication_list_tbl where no='$publication_no'";
			$re2 = mysql_query($sql2,$db);
			$booktitle=mysql_result($re2,0,"booktitle");


			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('booktitle',$booktitle);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('publication_no',$publication_no);

			$this->smarty->assign('Company_Kind',$Company_Kind);
			$this->smarty->assign('group_code',$group_code);
			$this->smarty->assign('group_name',$group_name);
			$this->smarty->assign('rank_code',$rank_code);
			$this->smarty->assign('rank_name',$rank_name);
			$this->smarty->assign('writer_name',$writer_name);

			$this->smarty->display("intranet/common_contents/work_lecture/read_input_mvc.tpl");
		}

		//==================================================================================//
		//==소감문 입력 Logic=============================================================//
		//==================================================================================//
		function ReadSave()
		{
			global $db,$memberID,$CompanyKind;
			global $Category,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $publication_no;
			global $contents,$readfilename;
			global $Company_Kind,$group_code,$group_name,$rank_code,$rank_name,$writer_name,$writer_code;

			$write_date=date("Y-m-d");
			if($Company_Kind <>"")
			{
				$CompanyKind=$Company_Kind;
			}

			$sql="insert into publication_read_tbl (publication_no,CompanyKind,group_code,group_name,rank_code,rank_name,writer_code,writer_name,write_date,contents,readfilename)";
			$sql.= "values('$publication_no','$CompanyKind','$group_code','$group_name','$rank_code','$rank_name','$memberID','$writer_name','$write_date','$contents','$readfilename')";

			//echo $sql."<br>";
			mysql_query($sql,$db);


			$ToDayEndTime = date('Y-m-d H:i:s');

			$cfile="../log/lecture.txt";
			$fd=fopen($cfile,'r');
			$con=fread($fd,filesize($cfile));
			fclose($fd);


			$fp=fopen($cfile,'w');
			$aa=$ToDayEndTime."-".$Company_Kind."-".$CompanyKind."-".$publication_no."-".$memberID."-".$writer_code."-".$write_date;
			$cond=$con.$aa."\n";
			fwrite($fp,$cond);
			fclose($fp);


			$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=read_list&publication_no=$publication_no&memberID=$memberID&Category=$Category&sub_index=$sub_index&tab_index=$tab_index&Company_Kind=$Company_Kind&group_code=$group_code&group_name=$group_name&rank_code=$rank_code&rank_name=$rank_name&writer_name=$writer_name");
			$this->smarty->display("intranet/move_page.tpl");
		}

		//==================================================================================//
		//==소감문 상세보기 Logic===========================================================//
		//==================================================================================//
		function ReadDetail()
		{

			global $db,$memberID,$CompanyKind;
			global $Category,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $read_no,$publication_no;
			global $contents,$readfilename;
			global $Company_Kind,$group_code,$group_name,$rank_code,$rank_name,$writer_name;

			$sql="select * from publication_read_tbl A,publication_list_tbl B where A.read_no='$read_no' and A.publication_no=B.no";
			//echo $sql,"<br>";

			$re = mysql_query($sql,$db);
			if(mysql_num_rows($re) > 0)
			{

				$publication_no=mysql_result($re,0,"publication_no");
				$group_name=mysql_result($re,0,"group_name");
				$writer_code=mysql_result($re,0,"writer_code");
				$writer_name=mysql_result($re,0,"writer_name");
				$rank_name=mysql_result($re,0,"rank_name");
				$write_date=mysql_result($re,0,"write_date");
				$booktitle=mysql_result($re,0,"booktitle");
				$contents=mysql_result($re,0,"contents");
				//$contents=nl2br($contents);
			}


			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('booktitle',$booktitle);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('read_no',$read_no);
			$this->smarty->assign('publication_no',$publication_no);

			$this->smarty->assign('Company_Kind',$Company_Kind);
			$this->smarty->assign('group_code',$group_code);
			$this->smarty->assign('group_name',$group_name);
			$this->smarty->assign('rank_code',$rank_code);
			$this->smarty->assign('rank_name',$rank_name);
			$this->smarty->assign('writer_name',$writer_name);

			$this->smarty->assign('writer_code',$writer_code);
			$this->smarty->assign('Name',$writer_name);
			$this->smarty->assign('rank_name',$rank_name);
			$this->smarty->assign('write_date',$write_date);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('Edit',true);

			$this->smarty->display("intranet/common_contents/work_lecture/read_input_mvc.tpl");

		}


		//==================================================================================//
		//==소감문 업데이트 Logic=============================================================//
		//==================================================================================//
		function ReadUpdate()
		{
			global $db,$memberID,$CompanyKind;
			global $Category,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $publication_no,$read_no;

			global $writer_name,$group_code,$group_name,$rank_code,$rank_name,$write_date;
			global $contents,$readfilename,$group_code,$group_name,$rank_code,$rank_name;


			$sql = "update publication_read_tbl set contents='$contents' where read_no='$read_no'";

			//echo $sql."<br>";
			mysql_query($sql,$db);

			$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=read_detail&publication_no=$publication_no&read_no=$read_no&memberID=$memberID&Category=$Category&sub_index=$sub_index&tab_index=$tab_index");
			$this->smarty->display("intranet/move_page.tpl");
		}

		//==================================================================================//
		//==소감문 삭제 Logic=============================================================//
		//==================================================================================//
		function ReadDelete()
		{
			global $db,$memberID,$CompanyKind;
			global $Category,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $publication_no,$read_no;

			global $writer_name,$group_code,$group_name,$rank_code,$rank_name,$write_date;
			global $contents,$readfilename,$group_code,$group_name,$rank_code,$rank_name;


			$sql = "delete from publication_read_tbl where read_no=$read_no";
			//echo $sql."<br>";
			mysql_query($sql,$db);

			$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=read_list&publication_no=$publication_no&read_no=$read_no&memberID=$memberID&Category=$Category&sub_index=$sub_index&tab_index=$tab_index");
			$this->smarty->display("intranet/move_page.tpl");
		}

		function bear3StrCut($str,$len,$tail="..."){
			$rtn = array();
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
		}

		function Film_banner(){
			$this->smarty->display("intranet/common_layout/film_popup.tpl");
		}

		function Web_soket(){

			$this->smarty->display("intranet/common_layout/web_soket_test.tpl");
		}

}

?>