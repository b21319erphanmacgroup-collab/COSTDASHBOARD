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
			global $mode,$page,$currentPage;
			global $techNo,$kind,$company;
			global $Category,$no,$file_type;	


			if($Category =="1") //기술번역서
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
			else if($Category =="2") //경제특강
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
			
				
			$this->smarty->assign('query_data',$query_data);		
			$this->smarty->assign('query_data2',$query_data2);		
			$this->smarty->assign('query_data3',$query_data3);	
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('move_list',$url);
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
			global $note,$type1,$type2;	

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
				$vupload = $path. $userfile_name;
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
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,filename,filesize,note,type1,type2) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$filename','$filesize','$note','$type1','$type2')";
				}
				else
				{
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,note,type1,type2) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$note','$type1','$type2')";
				}
			}
			else if($Category <>"1")//기술번역서가 아니면
			{
				if($filename != "") 
				{
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,filename,filesize,note) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$filename','$filesize','$note')";
				}
				else
				{
					$query00 = "insert into publication_list_tbl (booktitle,bookwriter,publishing,bookdate,datetype,datecustody,bookcode,updateuser,insertdate,note) values('$booktitle','$bookwriter','$publishing','$bookdate','$datetype','$datecustody','$sum','$memberID','$date','$note')";
				}
			}

			mysql_query($query00);
			$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=view&Category=$Category");
			$this->smarty->display("intranet/move_page.tpl");

		}//function InsertAction()

		//==================================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 LIST Logic=================================================//
		//==================================================================================//
		function UpdateReadPage()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $mode,$page,$currentPage;
			global $techNo,$kind,$mode;
			global $Category,$no,$file_type,$ActionMode;	

			
			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$this->smarty->assign('Admin',true);
			}else{
				$this->smarty->assign('Admin',false);
			}	

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

			$type1 = mysql_result($re,0,"type1"); 
	
			$note = mysql_result($re,0,"note"); 
			$note2 = mysql_result($re,0,"note2"); 
			
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
			$this->smarty->assign('filename2',$filename2);
			$this->smarty->assign('note',$note);
			$this->smarty->assign('note2',$note2);
			$this->smarty->assign('index_name',$index_name);
			$this->smarty->assign('index_summary',$index_summary);
			$this->smarty->assign('no',$no);
			$this->smarty->assign('query_data',$query_data);		
			$this->smarty->assign('query_data2',$query_data2);			
			$this->smarty->assign('query_data3',$query_data3);						
			$this->smarty->display($go_url);
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
			global $note,$type1,$type2;	

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
			$sum2 = $sum-1;
			
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
					$vupload = $path. $userfile_name;
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
				if($filename <> "") 
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',bookcode='$sum',updateuser='$memberID' ,insertdate='$date',filename='$filename',filesize='$filesize',note='$note',type1='$type1',type2='$type2' where no='$no'";
				}
				else 
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',bookcode='$sum',updateuser='$memberID' ,insertdate='$date',note='$note',type1='$type1',type2='$type2' where no='$no'";
				}
			}
			else if($Category <>"1")//기술번역서가 아니면
			{
				if($filename != "") 
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',updateuser='$memberID' ,insertdate='$date',filename='$filename',filesize='$filesize',note='$note' where no='$no'";
				}
				else
				{
					$query00 = "update publication_list_tbl set booktitle='$booktitle',bookwriter='$bookwriter',publishing='$publishing',bookdate='$bookdate',datetype='$datetype',datecustody='$datecustody',updateuser='$memberID' ,insertdate='$date',note='$note' where no='$no'";
				}
			}
			mysql_query($query00);
			
			if($Category<>1)
			{
				$this->smarty->assign('target',"edu");
			}
			$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=view&Category=$Category");
			$this->smarty->display("intranet/move_page.tpl");

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
			global $note,$type1,$type2;	

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
			$sql = "delete from publication_list_tbl where no=$no";
			mysql_query($sql,$db);

			$this->smarty->assign('MoveURL',"lecture_controller.php?ActionMode=view&Category=$Category");
			$this->smarty->display("intranet/move_page.tpl");

		}//function DeleteAction()

	
		//==================================================================================//
		//==기술번역서 / 경제특강 / 하버트특강 읽기 Logic=================================================//
		//==================================================================================//		
		function View()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $Category,$searchv,$memberID;	

			/*
				Category=1 기술번역서
				Category=2 경제특강
				Category=3 하버드특강
			*/

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$this->smarty->assign('Admin',true);
			}else{
				$this->smarty->assign('Admin',false);
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
				$tab_Titel = array('도로','구조','지반');
				$tab_value = array('2','3','4');
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
			}else if ($Category =="2")  //경제특강
			{
				$cut_length="130";
				if($tab_index=="") $tab_index=0;
				$tab_Titel = array('최근자료15','자기개발/리더십','마케팅','경영기법/전략','비즈니스','경제전망','경제정책','기타');
				$tab_value = array('0','40','41','42','43','44','45','46');

				$go_url="intranet/common_contents/work_lecture/economy_contents_mvc.tpl";
				if($tab_index==0){ //최근자료 인경우
					$sql = "select * from publication_list_tbl where datetype in('40','41','42','43','44','45','46')";
					if($selt == "")
					{
						$sql.=" order by bookdate desc limit 0, 15";
					}
			
				}else
				{
					$sql = "select * from publication_list_tbl where datetype ='$tab_index'";
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
				$cut_length="130";
				if($tab_index=="") $tab_index=0;
				$tab_Titel = array('최근자료15','하버드특강','EBS특강','경영보고서','영상보고서','인문학특강','Traffic Simulation','기타');
				$tab_value = array('0','48','49','200','47','300','100','80');

				$go_url="intranet/common_contents/work_lecture/havard_contents_mvc.tpl";

				
				if($tab_index==0){ //최근자료 인경우
					$sql = "select * from publication_list_tbl where datetype in('48','49','200','47','300','100','80')";
					if($selt == "")
					{
						$sql.=" order by bookdate desc limit 0, 15";
					}
			
				}else
				{
					$sql = "select * from publication_list_tbl where datetype ='$tab_index'";
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
					$re_row2[file_type]="doc"; 
					$re_row2[file_img]="document_icon.png";
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
			$this->smarty->display($go_url);
			
		}

	}



