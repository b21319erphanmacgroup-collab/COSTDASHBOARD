<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

	/***************************************
	* 지적재산권
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/ 

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/function_intranet.php";

	//$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드

	extract($_GET);
	class LibraryLogic {
		var $smarty;
		function LibraryLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//================================================================================
		// 한맥 도서내용 입력화면  Logic
		//================================================================================	
		function HMInsertPage()
		{
			global $db,$memberID;
			global $mode,$kind,$CompanyKind;

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
			{
				$this->smarty->assign('Auth',true);
			}
			else
			{
				$this->smarty->assign('Auth',false);
			}

			if($kind=="") $kind=1;
			if($regdate=="") $regdate=date("Y-m-d");
			
			if($registration_date=="") $registration_date=date("Y-m-d");
		
			$url="library_controller.php";
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('company',$company);
			$this->smarty->assign('kind',$kind);
			$this->smarty->assign('mode',$mode);

			$this->smarty->assign('regdate',$regdate);
			$this->smarty->assign('registration_date',$registration_date);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","library_controller.php");

			$this->smarty->display("intranet/common_contents/work_book/libraryHM_input_mvc.tpl");
		}


		//================================================================================
		// /도서내용 insert logic
		//================================================================================	
		function HMInsertAction()
		{

			global $db,$memberID;
			global $mode,$kind,$CompanyKind,$book_id;
			global $book_title,$book_kind,$book_writer,$book_publisher;
			global $book_contents,$bookcover_filename,$registration_date;
			global $filename,$userfile,$userfile_name,$userfile_size;

			if($company=="") $company=1;	

			$now_day=date("Y-m-d h:i:s");


			$sql1="select * from library_tbl where book_kind='$book_kind'";
				//echo "sql1 :".$sql1."<br>";
				$re1=mysql_query($sql1,$db);
				while($row_book = mysql_fetch_array($re1))
				{
					$book_id=$row_book[book_id];
					$book_id_arry = explode("-",$book_id); 
					//$Maxbook_no=$book_id_arry[1];
					$representative_id=$book_id_arry[0];
					//echo "book_id_arry[1]".$book_id_arry[1]."<br>";
					if($Maxbook_no < $book_id_arry[1])$Maxbook_no=$book_id_arry[1];
				}


				if($representative_id=="" && $book_kind=="10")
				{
					$representative_id="reco";
				}
				$Newbook_no=$Maxbook_no+1;
				$Newbook_no=sprintf('%04d',$Newbook_no);
				$Newbook_id=$representative_id."-".$Newbook_no;
				//echo "Maxbook_no :".$Maxbook_no."<br>";
				//echo "Newbook_no :".$Newbook_no."<br>";
				//echo "Newbook_id :".$Newbook_id."<br>";
				

				//$book_id=mysql_result($re1,0,"book_id");
				//echo "book_id".$book_id."<br>";
				//$BookNo=$MaxBookNo+1;
			

			$file_path="./../../../intranet_file/documents/libraryHM/bookcover_filename/";
			$file_path_is="./../../../intranet_file/documents/libraryHM/bookcover_filename";
			$registration_path="./../../../intranet_file/documents/libraryHM/registrationfile/";

			$bookcover_filename2 = substr($bookcover_filename, 15);

			if($userfile_name <>"" && $userfile_size <>0) 
			{ //첨부파일 있으면서 수정이면

				$ext = explode(".", $userfile_name);
				$ext = strtolower(trim($ext[count($ext)-1]));
				$userfile_name = "$book_id".".$ext";
				$path=$file_path;

				if (is_dir ($file_path_is))
				{
				}
				else{mkdir($file_path_is, 0777);}

				$userfile=stripslashes($userfile);
				$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
				$vupload = $path.$userfile_name;
				$vupload = str_replace(" ","",$vupload); 
				$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
				
				move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
				$userfile_size = number_format($userfile_size);
				$filename=$userfile_name;
				$filename = str_replace(" ","",$filename); 
				$bookcover_filename = "./bookcover_filename/".$filename;
			
				$sql="insert into library_tbl (book_id,book_kind,book_title,book_writer,book_publisher,book_contents,bookcover_filename,registration_date,book_status,UpdateDate,UpdateUser) values";
				$sql=$sql."('$Newbook_id','$book_kind','$book_title','$book_writer','$book_publisher','$book_contents','$bookcover_filename','$registration_date',0,now(),'$memberID')";
			}
			
			else
			{
				$sql="insert into library_tbl (book_id,book_kind,book_title,book_writer,book_publisher,book_contents,registration_date,book_status,UpdateDate,UpdateUser) values";
				$sql=$sql."('$Newbook_id','$book_kind','$book_title','$book_writer','$book_publisher','$book_contents','$registration_date',0,now(),'$memberID')";
			}
			//echo $sql."<br>";
			mysql_query($sql,$db);	

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=HMread_page&book_id=$Newbook_id");
			$this->smarty->display("intranet/move_page.tpl");
		}

		//================================================================================
		// 도서내용 읽기 (한맥-전체) logic
		//================================================================================	
		function HMUpdateReadPage()
		{
			global $db,$memberID,$ActionMode;
			global $mode,$page,$currentPage,$Member_Name,$Member_Position;
			global $book_id,$kind,$CompanyKind,$tab_index,$rent_index;

			$today=date("Y-m-d");

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
			{
				$this->smarty->assign('Auth',true);
			}
			else
			{
				$this->smarty->assign('Auth',false);
			}
			
			$file_path="./../../../intranet_file/documents/libraryHM/bookcover_filename/";
			$registration_path="./../../../intranet_file/documents/libraryHM/registrationfile/";
			
			$booksql="select * from library_tbl where book_id='$book_id'";
			//echo $booksql;
			$rebook = mysql_query($booksql,$db);
			while($row_book = mysql_fetch_array($rebook))
			{
				$book_id=$row_book[book_id];
				$book_kind=$row_book[book_kind];
				$book_title=$row_book[book_title];
				$book_writer=$row_book[book_writer];
				$book_publisher=$row_book[book_publisher];
				$book_contents=$row_book[book_contents];
				$bookcover_filename=$row_book[bookcover_filename];
				$registration_date=$row_book[registration_date];
				$book_status=$row_book[book_status];
				
				$bookcover_filename2 = substr($bookcover_filename, 21);

				$bookcover_filename_all=$file_path.$bookcover_filename2;
			}
			$sql_rent = "select * from library_rent_tbl where book_id='$book_id' and return_date is null";
			//echo $sql_rent."<br>";
			$re_rent = mysql_query($sql_rent);
			$RentRow = mysql_num_rows($re_rent);//총 개수 저장
			
				if($RentRow>"0")
				{
					$rent_index = mysql_result($re_rent,0,"rent_index");
					$MemberNo = mysql_result($re_rent,0,"MemberNo");
					$status = mysql_result($re_rent,0,"status");
				}

			if ($ActionMode=="HMread_page")
			{
				
				$rent_member_data = array(); 

				$sql_rent = "select * from library_rent_tbl where book_id='$book_id' order by start_date desc";
				//echo $sql_rent."<br>";
				$re_rent = mysql_query($sql_rent);
				while($row_rent = mysql_fetch_array($re_rent))
				{
					array_push($rent_member_data,$row_rent);
				}
					$this->smarty->assign('rent_member_data',$rent_member_data);
			}			

				$this->smarty->assign('currentPage',$currentPage);
				$this->smarty->assign('today',$today);
				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('CompanyKind',$CompanyKind);
				$this->smarty->assign('Member_Name',$Member_Name);
				$this->smarty->assign('Member_Position',$Member_Position);
				$this->smarty->assign('tab_index',$tab_index);
				$this->smarty->assign('rent_index',$rent_index);
				$this->smarty->assign('book_id',$book_id);
				$this->smarty->assign('book_kind',$book_kind);	
				$this->smarty->assign('book_title',$book_title);					
				$this->smarty->assign('book_writer',$book_writer);
				$this->smarty->assign('book_publisher',$book_publisher);
				$this->smarty->assign('book_contents',$book_contents);
				$this->smarty->assign('bookcover_filename',$bookcover_filename);
				$this->smarty->assign('bookcover_filename_all',$bookcover_filename_all);
				$this->smarty->assign('registration_date',$registration_date);
				$this->smarty->assign('book_status',$book_status);
				
				$this->smarty->assign('RentRow',$RentRow);
				$this->smarty->assign('MemberNo',$MemberNo);
				$this->smarty->assign('rent_index',$rent_index);
				$this->smarty->assign('status',$status);


				$this->smarty->assign("page_action","library_controller.php");
			
			if($mode=="mod")
				$this->smarty->display("intranet/common_contents/work_book/libraryHM_input_mvc.tpl");
			else
				$this->smarty->display("intranet/common_contents/work_book/libraryHM_read_mvc.tpl");
			
		}

		//================================================================================
		// 도서내용 수정 logic
		//================================================================================	
		function HMUpdateAction()
		{
			global $db,$memberID,$currentPage,$Member_Name;
			global $Member_Position,$mode,$kind,$CompanyKind,$book_id;
			global $book_title,$book_kind,$book_writer,$book_publisher,$book_status;
			global $book_contents,$bookcover_filename,$registration_date,$tab_index;
			global $filename,$userfile,$userfile_name,$userfile_size,$rent_index;

			$now_day=date("Y-m-d h:i:s");

			$file_path="./../../../intranet_file/documents/libraryHM/bookcover_filename/";
			$file_path_is="./../../../intranet_file/documents/libraryHM/bookcover_filename";
			$registration_path="./../../../intranet_file/documents/libraryHM/registrationfile/";

			$bookcover_filename2 = substr($bookcover_filename, 15);

			if($userfile) 
			{
				if($userfile_name <>"" && $userfile_size <>0) 
				{ //첨부파일 있으면서 수정이면
					$bookcover_filename2=iconv("UTF-8", "EUC-KR",$bookcover_filename2);
					if($bookcover_filename2<>"")
					$orgfilename = $file_path.$bookcover_filename2;

					$exist_org = file_exists("$orgfilename");
					//echo $exist_org."<br>";
					if($exist_org) 
						{
							$re=unlink("$orgfilename");
						}
				}

				$ext = explode(".", $userfile_name);
				$ext = strtolower(trim($ext[count($ext)-1]));
				$userfile_name = "$book_id".".$ext";
				$path=$file_path;

				if (is_dir ($file_path_is)){	}
				else{mkdir($file_path_is, 0777);}

				$userfile=stripslashes($userfile);
				$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
				$vupload = $path.$userfile_name;
				$vupload = str_replace(" ","",$vupload); 
				$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
				move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);

				$userfile_size = number_format($userfile_size);
				$filename=$userfile_name;
				$filename = str_replace(" ","",$filename); 
				$bookcover_filename = "./bookcover_filename/".$filename;
				
				$sql  = "update library_tbl set book_kind='$book_kind',book_title='$book_title',book_writer='$book_writer',book_publisher='$book_publisher',book_contents='$book_contents',bookcover_filename='$bookcover_filename',registration_date='$registration_date',book_status='$book_status',UpdateDate=now(),UpdateUser='$memberID' where book_id='$book_id'";
			}
			else
			{
				$sql  = "update library_tbl set book_kind='$book_kind',book_title='$book_title',book_writer='$book_writer',book_publisher='$book_publisher',book_contents='$book_contents',registration_date='$registration_date',book_status='$book_status',UpdateDate=now(),UpdateUser='$memberID' where book_id='$book_id'";
			}
				//echo $sql ."<br>";
				mysql_query($sql,$db);

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=HMread_page&mode=read&CompanyKind=$CompanyKind&memberID=$memberID&Member_Name=$Member_Name&Member_Position=$Member_Position&tab_index=$tab_index&rent_index=$rent_index&book_id=$book_id&currentPage=$currentPage");

			$this->smarty->display("intranet/move_page.tpl");
		}

		//================================================================================
		// 도서목록 삭제 Logic
		//================================================================================	
		function DeleteAction()
		{

			global $db,$memberID;
			global $mode,$kind,$CompanyKind,$book_id;
			global $book_title,$book_kind,$book_writer,$book_publisher;
			global $book_contents,$bookcover_filename,$registration_date;
			global $filename,$userfile,$userfile_name,$userfile_size;
				
				$sql="select * from library_tbl where book_id='$book_id'";
				$re=mysql_query($sql,$db);
				$bookcover_filename = mysql_result($re, 0, "bookcover_filename");	
				$book_kind = mysql_result($re, 0, "book_kind");	

				$file_path="./../../../intranet_file/documents/libraryHM/";

				$filename=iconv("UTF-8", "EUC-KR",$bookcover_filename);
				$orgfilename = $file_path.$filename;

				$exist_org = file_exists("$orgfilename");
	;
				if($filename<>"")
				{
					if($exist_org) 
					{	
						$re=unlink("$orgfilename");
					}
				}		
				if($mode=="delfile")
				{
					$sql2  = "update library_tbl set bookcover_filename='' where book_id='$book_id'";
					//echo $sql2."<br>";
					mysql_query($sql2,$db);
				}

				if($mode=="del")
				{
					$sql3 = "delete from library_tbl where book_id='$book_id'";
					//echo $sql3."<br>";					
					mysql_query($sql3,$db);
				}
				
				$this->smarty->assign('target',"opener");
				$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=view&tab_index=$book_kind");
				$this->smarty->display("intranet/move_page.tpl");
		}
			
		//================================================================================
		// 한맥 도서실 도서대출 입력화면  Logic
		//================================================================================	
		function HMrent_page()
		{
			global $db,$memberID,$MemberNo,$tab_index;
			global $mode,$kind,$book_id,$rent_index,$CompanyKind,$Member_Name,$Member_Position;
			
			if($CompanyKind<>"HANM")
			{
				/*
				$Member_Name=trim(ICONV("EUC-KR","UTF-8",$Member_Name));
				$Member_Position=trim(ICONV("EUC-KR","UTF-8",$Member_Position));
				*/
				
			}

			

			

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
			{
				$this->smarty->assign('Auth',true);
			}
			else
			{
				$this->smarty->assign('Auth',false);
			}

			$today=date("Y-m-d");
			
			if($mode=="add")
			{
				
				$sql="select  korName,Name as PositionName from
				(
					select * from member_tbl where memberNo='$memberID' and WorkPosition <>'9'
				)a1 left JOIN
				(
					select * from systemconfig_tbl where SysKey='PositionCode' 
				)a2 on a1.RankCode = a2.code";

				if($CompanyKind=="HANM" || $CompanyKind=="")
				{	
					//echo "hanm".$sql."<br>";
					$re = mysql_query($sql,$db);
					$re_num = mysql_num_rows($re);
					if($re_num >0)
					{
						while($re_row = mysql_fetch_array($re))
						{
							$MemberName=$re_row[korName];
							$MemberPosition=$re_row[PositionName];
						}

						$CompanyKind="HANM";
					}
					
				}

				if($CompanyKind=="JANG" || $CompanyKind=="")
				{	
					//echo "jang".$sql."<br>";
					$host="192.168.10.6";
					$user="root";
					$pass="erp";
					$dataname="hanmacerp";
					$jangheon_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$jangheon_db);
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					
					$re = mysql_query($sql,$jangheon_db);
					$re_num = mysql_num_rows($re);
					mysql_close($jangheon_db);
					if($re_num >0)
					{
						while($re_row = mysql_fetch_array($re))
						{
							$MemberName=$re_row[korName];
							$MemberPosition=$re_row[PositionName];
						}

						$CompanyKind="JANG";
					}
					
				}
				if($CompanyKind=="PILE" || $CompanyKind=="")
				{	
					//echo "PILE".$sql."<br>";
					$host="192.168.10.7";
					$user="root";
					$pass="erp";
					$dataname="hanmacerp";

					$piletech_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$piletech_db);
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					$result=mysql_query($dbup9,$piletech_db);
					$re = mysql_query($sql,$piletech_db);
					$re_num = mysql_num_rows($re);
					mysql_close($piletech_db);
					if($re_num >0)
					{
						while($re_row = mysql_fetch_array($re))
						{
							$MemberName=$re_row[korName];
							$MemberPosition=$re_row[PositionName];
						}
						$CompanyKind="PILE";
					}
					
				}

				if($CompanyKind=="HALL" || $CompanyKind=="")
				{	
					
					$host="1.234.37.143";
					$user="root";
					$pass="vbxsystem";
					$dataname="hallaerp";

					$halla_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$halla_db);
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					$result=mysql_query($dbup9,$halla_db);
					$re = mysql_query($sql,$halla_db);
					$re_num = mysql_num_rows($re);
					mysql_close($halla_db);
					if($re_num >0)
					{
						while($re_row = mysql_fetch_array($re))
						{
							$MemberName=$re_row[korName];
							$MemberPosition=$re_row[PositionName];
						}
						$CompanyKind="HALL";
					}
					
				}


				$Member_Name=$MemberName;
				$Member_Position=$MemberPosition;




				if($MemberNo=="")
				{
					$MemberNo=$memberID;
				}

				$booksql="select * from library_tbl where book_id='$book_id'";
				//echo $booksql."<br>";
				$rebook = mysql_query($booksql,$db);
				while($row_book = mysql_fetch_array($rebook))
				{
					$book_id=$row_book[book_id];
					$book_kind=$row_book[book_kind];
					$book_title=$row_book[book_title];
					$book_writer=$row_book[book_writer];
					$book_publisher=$row_book[book_publisher];
					$book_contents=$row_book[book_contents];
					$bookcover_filename=$row_book[bookcover_filename];
					$registration_date=$row_book[registration_date];
					$bookcover_filename2 = substr($bookcover_filename, 21);
					$bookcover_filename_all=$file_path.$bookcover_filename2;
					
				}

					if($start_date=="") $start_date=date("Y-m-d");
		
					if($end_date=="") $end_date= date("Y-m-d",strtotime("+14 day"));
			
					$this->smarty->assign('today',$today);
					$this->smarty->assign('tab_index',$tab_index);
					$this->smarty->assign('mode',$mode);
					$this->smarty->assign('memberID',$memberID);
					$this->smarty->assign('MemberNo',$MemberNo);
					$this->smarty->assign('book_id',$book_id);
					$this->smarty->assign('book_kind',$book_kind);	
					$this->smarty->assign('book_title',$book_title);					
					$this->smarty->assign('book_writer',$book_writer);
					$this->smarty->assign('book_publisher',$book_publisher);
					$this->smarty->assign('book_contents',$book_contents);
					$this->smarty->assign('bookcover_filename',$bookcover_filename);
					$this->smarty->assign('bookcover_filename_all',$bookcover_filename_all);
					$this->smarty->assign('registration_date',$registration_date);
					$this->smarty->assign('start_date',$start_date);
					$this->smarty->assign('end_date',$end_date);

			}
			else
			{
				$booksql="select * from library_rent_tbl where rent_index='$rent_index' and return_date is null";
				//echo $booksql."<br>";
				$rebook = mysql_query($booksql,$db);
				while($row_book = mysql_fetch_array($rebook))
				{
					$status=$row_book[status];
					$rent_index=$row_book[rent_index];
					$book_id=$row_book[book_id];
					$MemberNo=$row_book[MemberNo];
					$MemberName=$row_book[MemberName];
					$MemberPosition=$row_book[MemberPosition];
					$start_date=$row_book[start_date];
					$end_date=$row_book[end_date];
					$return_date=$row_book[return_date];
					$Check_MemberID=$row_book[Check_MemberID];
				}
					

					$sql="select * from member_tbl where MemberNo='$MemberNo'";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re))
					{
						$MemberName=$re_row[korName];
					}


					if($start_date=="") $start_date=date("Y-m-d");
				
					if($end_date=="") $end_date= date("Y-m-d",strtotime("+14 day"));
				
					//if($return_date=="") $return_date=date("Y-m-d");

					$this->smarty->assign('mode',$mode);
					$this->smarty->assign('tab_index',$tab_index);
					$this->smarty->assign('memberID',$memberID);
					$this->smarty->assign('status',$status);
					$this->smarty->assign('rent_index',$rent_index);
					$this->smarty->assign('book_id',$book_id);
					$this->smarty->assign('MemberNo',$MemberNo);
					$this->smarty->assign('MemberName',$MemberName);
					$this->smarty->assign('MemberPosition',$MemberPosition);
					$this->smarty->assign('start_date',$start_date);	
					$this->smarty->assign('end_date',$end_date);
					$this->smarty->assign('return_date',$return_date);					
					$this->smarty->assign('Check_MemberID',$Check_MemberID);

					$booksql2="select * from library_tbl where book_id='$book_id'";
					//echo $booksql2."<br>";
					$rebook2 = mysql_query($booksql2,$db);
					$book_title	= mysql_result($rebook2,0,"book_title");
					$this->smarty->assign('book_title',$book_title);
			}

				$this->smarty->assign('CompanyKind',$CompanyKind);
				$this->smarty->assign('Member_Name',$Member_Name);
				$this->smarty->assign('Member_Position',$Member_Position);

				$this->smarty->assign('MemberName',$MemberName);
				$this->smarty->assign('MemberPosition',$MemberPosition);


	
				$this->smarty->display("intranet/common_contents/work_book/libraryHM_rent_mvc.tpl");
		}

		//================================================================================
		// 한맥 도서실 도서대출 Logic
		//================================================================================	
		function HMRentInsertUpdate()
		{
			global $db,$memberID,$mode,$tab_index,$CompanyKind;
			global $book_id,$MemberNo,$Member_Name,$Member_Position,$start_date,$rent_index;
			global $end_date,$return_date,$Check_MemberID;

			if ($mode=="add")
			{

				$sql0="select max(rent_index) Max_rent_index from library_rent_tbl";
				//echo $sql0."<br>";
				$re0 = mysql_query($sql0,$db);
				$Max_rent_index=mysql_result($re0,0,"Max_rent_index");
				$insert_rent_index=$Max_rent_index+1;
				
				$sql="insert into library_rent_tbl (rent_index,book_id,CompanyKind,MemberNo,MemberName,MemberPosition,status,start_date,end_date) values('$insert_rent_index','$book_id','$CompanyKind','$MemberNo','$Member_Name','$Member_Position','APPLICATION','$start_date','$end_date')";
			}

			else if ($mode=="del")
			{
					$sql = "delete from library_rent_tbl where rent_index='$rent_index'";
			}

			else
			{
				if ($return_date=="")
				{
					$sql  = "update library_rent_tbl set MemberNo='$MemberNo',start_date='$start_date',end_date='$end_date' where rent_index='$rent_index'";
				}
				else
				{
					$sql  = "update library_rent_tbl set MemberNo='$MemberNo',start_date='$start_date',end_date='$end_date',return_date='$return_date',Check_MemberID='$memberID' where rent_index='$rent_index'";
				}
			}
				//echo $sql."<br>";					
				mysql_query($sql,$db);
			
				$this->smarty->assign('target',"opener");
				$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=view&CompanyKind=$CompanyKind&MemberNo=$MemberNo&Member_Name=$Member_Name&Member_Position=$Member_Position&tab_index=$tab_index");
				$this->smarty->display("intranet/move_page.tpl");
		}

		//================================================================================
		// 도서실 도서대여 승인 logic
		//================================================================================	

		function HMApprovalCheck()
		{
			global $db,$memberID,$company_index,$MemberNo,$Member_Name,$Member_Position;
			global $Auth,$tab_index,$sub_index,$selt,$searchv,$rent_index;
			global $Start,$page,$currentPage,$last_page,$CompanyKind,$check_mode;

			$sql  = "update library_rent_tbl set status='ACCEPTED' where rent_index='$rent_index'";
				//echo $sql."<br>";					
				mysql_query($sql,$db);
			
				$this->smarty->assign('target',"opener");
				$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=view&tab_index=$tab_index&CompanyKind=$CompanyKind&MemberNo=$MemberNo&Member_Name=$Member_Name&Member_Position=$Member_Position");
				$this->smarty->display("intranet/move_page.tpl");
		}
		//================================================================================
		// 도서실 도서대여 승인 logic2
		//================================================================================	

		function HMApprovalCheck2()
		{
			global $db,$memberID,$company_index,$MemberNo,$Member_Name,$Member_Position;
			global $Auth,$tab_index,$sub_index,$selt,$searchv,$rent_index;
			global $Start,$page,$currentPage,$last_page,$CompanyKind,$check_mode;

			$sql  = "update library_rent_tbl set status='ACCEPTED' where rent_index='$rent_index'";
				//echo $sql."<br>";					
				mysql_query($sql,$db);
			
				$this->smarty->assign('target',"self");
				$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=HMapplication_list&tab_index=$tab_index&CompanyKind=$CompanyKind&memberID=$memberID&Member_Name=$Member_Name&Member_Position=$Member_Position&check_mode=$check_mode");
				$this->smarty->display("intranet/move_page.tpl");
		}

		//================================================================================
		// 도서실 도서반납 확인 logic
		//================================================================================	

		function HMReturnCheck()
		{
			global $db,$memberID,$company_index,$MemberNo,$Member_Name,$Member_Position;
			global $Auth,$tab_index,$sub_index,$selt,$searchv,$rent_index;
			global $Start,$page,$currentPage,$last_page,$CompanyKind,$check_mode;

			$today=date("Y-m-d");

			$sql  = "update library_rent_tbl set return_date='$today',status='RETURNCHECKED',Check_MemberID='$memberID' where rent_index='$rent_index'";
				//echo $sql."<br>";					
				mysql_query($sql,$db);
			
				$this->smarty->assign('target',"opener");
				$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=view&tab_index=$tab_index&CompanyKind=$CompanyKind&MemberNo=$MemberNo&Member_Name=$Member_Name&Member_Position=$Member_Position");
				$this->smarty->display("intranet/move_page.tpl");
		}

		//================================================================================
		// 도서실 도서반납 확인 logic2
		//================================================================================	

		function HMReturnCheck2()
		{
			global $db,$memberID,$company_index,$MemberNo,$Member_Name,$Member_Position;
			global $Auth,$tab_index,$sub_index,$selt,$searchv,$rent_index;
			global $Start,$page,$currentPage,$last_page,$CompanyKind,$check_mode;

			$today=date("Y-m-d");

			$sql  = "update library_rent_tbl set return_date='$today',status='RETURNCHECKED',Check_MemberID='$memberID' where rent_index='$rent_index'";
				//echo $sql."<br>";					
				mysql_query($sql,$db);
			
				$this->smarty->assign('target',"self");
				$this->smarty->assign('MoveURL',"library_controller.php?ActionMode=HMapplication_list&tab_index=$tab_index&CompanyKind=$CompanyKind&memberID=$memberID&Member_Name=$Member_Name&Member_Position=$Member_Position&check_mode=$check_mode");
				$this->smarty->display("intranet/move_page.tpl");
		}
		//================================================================================
		// 도서실 도서목록 list logic
		//================================================================================	

		function ViewHM()
		{
			global $db,$memberID,$company_index;
			global $Auth,$tab_index,$sub_index,$selt,$searchv,$Member_Name,$Member_Position;
			global $Start,$page,$currentPage,$last_page,$CompanyKind;

			$today=date("Y-m-d");
		
			$pre_1month_date= date("Y-m-d",strtotime("-31 day"));
			
			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
			{
				$this->smarty->assign('Auth',true);
			}
			else
			{
				$this->smarty->assign('Auth',false);
			}

			$page=15;
	
			if($tab_index=="") $tab_index="10";


			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);
	

			$query_data = array(); 

			if($searchv<>"")
			{
				if($selt=="제목")
				{
					/*
					if($tab_index<>"0")
					{
						$sql_num = "select * from library_tbl where book_kind='$tab_index' and book_title like '%$searchv%'";
					}
					else
					{
						$sql_num = "select * from library_tbl where book_title like '%$searchv%'";
					}
					*/
						$sql_num = "select * from library_tbl where book_status='0' and book_title like '%$searchv%'";
				}
				else if($selt=="저자")
				{
					/*
					if($tab_index<>"0")
					{
						$sql_num = "select * from library_tbl where book_kind='$tab_index' and book_writer like '%$searchv%'";
					}
					else
					{
						$sql_num = "select * from library_tbl where book_writer like '%$searchv%'";
					}
					*/
						$sql_num = "select * from library_tbl where book_status='0' and book_writer like '%$searchv%'";
				}
				else
				{
					/*
					if($tab_index<>"0")
					{
						$sql_num = "select * from library_tbl where book_kind='$tab_index' and book_publisher like '%$searchv%'";					
					}
					else
					{
						$sql_num = "select * from library_tbl where book_publisher like '%$searchv%'";
					}
					*/
						$sql_num = "select * from library_tbl where book_status='0' and book_publisher like '%$searchv%'";

				}
			}
			else
			{
				if($tab_index<>"0")
				{
					$sql_num = "select * from library_tbl where book_kind='$tab_index' and book_status='0'";
				}
				else
				{
					$sql_num = "select * from library_tbl where book_id<>'' and registration_date>='$pre_1month_date' and book_status='0'";
				}
			}
			
			//echo $sql_num."<br>";
			$re_num = mysql_query($sql_num);
			$TotalRow = mysql_num_rows($re_num);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/$page);

			$i=$Start;
			$sql = $sql_num." order by book_id desc limit $Start, $page";	

				//echo $sql."<br>";
				$re = mysql_query($sql);
				while($re_row = mysql_fetch_array($re)) 
				{
					$book_id=$re_row[book_id];
					array_push($query_data,$re_row);

				$sql_rent = "select * from library_rent_tbl where book_id='$book_id' and return_date is null";
				//echo $sql_rent."<br>";
				$re_rent = mysql_query($sql_rent);
				$RentRow[$i] = mysql_num_rows($re_rent);//총 개수 저장
			
					if($RentRow[$i]>"0")
					{
						$rent_index[$i] = mysql_result($re_rent,0,"rent_index");
						$MemberNo[$i] = mysql_result($re_rent,0,"MemberNo");
						$status[$i] = mysql_result($re_rent,0,"status");
					}
				
				$i++;}
			
			if($currentPage == "") $currentPage = 1; 

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();


			$tab_Titel = array('권장도서','신규','경제/경영','자기계발','소설/에세이','인문/과학','역사/문화','생활/건강/여행','컴퓨터/IT','전문서적','기타');
			$tab_value = array('10','0','1','2','3','4','5','6','8','7','9');


			$this->smarty->assign("page_action","library_controller.php");
			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('selt',$selt);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Member_Name',$Member_Name);
			$this->smarty->assign('Member_Position',$Member_Position);
			$this->smarty->assign('titlemsg',$titlemsg);
			$this->smarty->assign('regnummsg',$regnummsg);
			$this->smarty->assign('regdatemsg',$regdatemsg);
			$this->smarty->assign('agentmsg',$agentmsg);
			$this->smarty->assign('agent_count',$agent_count);
			$this->smarty->assign('processname',$processname);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('query_data2',$query_data2);
			$this->smarty->assign('tab_Titel',$tab_Titel);
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('company_index',$company_index);
			$this->smarty->assign('register2',$register2);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);
			
			$this->smarty->assign('rent_index',$rent_index);			
			$this->smarty->assign('RentRow',$RentRow);			
			$this->smarty->assign('MemberNo',$MemberNo);
			$this->smarty->assign('status',$status);
			$this->smarty->display("intranet/common_contents/work_book/libraryHM_contents_mvc.tpl");
		}

		//================================================================================
		// 한맥 도서실 도서대출현황 List화면  Logic
		//================================================================================	
		function HMapplication_list()
		{
			global $db,$memberID,$MemberNo,$Member_Name,$Member_Position;
			global $mode,$kind,$check_mode,$CompanyKind,$tab_index;

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
			{
				$this->smarty->assign('Auth',true);
			}
			else
			{
				$this->smarty->assign('Auth',false);
			}

			$today=date("Y-m-d");
			
			$page=15;
	
			if($tab_index=="") $tab_index=0;

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			$query_data = array(); 
				
			if($check_mode=="application_check")
			{
				$booksql="select *,(select book_title from library_tbl where library_tbl.book_id=a.book_id) as book_title from library_rent_tbl a where status='APPLICATION'";
			}				
			else if($check_mode=="return_check")
			{
				$booksql="select *,(select book_title from library_tbl where library_tbl.book_id=a.book_id) as book_title from library_rent_tbl a where status='ACCEPTED'";
			}				
			else if($check_mode=="overdue_check")
			{
				$booksql="select *,(select book_title from library_tbl where library_tbl.book_id=a.book_id) as book_title from library_rent_tbl a where status='ACCEPTED' and end_date<'$today'";
			}

				//echo $booksql."<br>";
				$re = mysql_query($booksql);
				while($re_row = mysql_fetch_array($re)) 
				{
					$book_id=$re_row[book_id];
					$CompanyCode=$re_row[CompanyKind];

					if($CompanyCode=="HANM")
					{	$ComName="[ 한맥 ]";
					}else if($CompanyCode=="JANG")
					{	$ComName="[ 장헌 ]";
					}else if($CompanyCode=="PILE")
					{	$ComName="[피티씨]";
					}else if($CompanyCode=="HALL")
					{	$ComName="[ 한라 ]";
					}
					
					$re_row[CompanyName]=$ComName;
					array_push($query_data,$re_row);

					
					/*
					$sql_rent = "select * from library_rent_tbl where book_id='$book_id' and return_date is null";
					//echo $sql_rent."<br>";
					$re_rent = mysql_query($sql_rent);
					$RentRow[$i] = mysql_num_rows($re_rent);//총 개수 저장
				
						if($RentRow[$i]>"0")
						{
							$rent_index[$i] = mysql_result($re_rent,0,"rent_index");
							$MemberNo[$i] = mysql_result($re_rent,0,"MemberNo");
							$status[$i] = mysql_result($re_rent,0,"status");

						}
					
					$i++;
					*/
					
					
				}
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('MemberNo',$MemberNo);
			$this->smarty->assign('Member_Name',$Member_Name);
			$this->smarty->assign('Member_Position',$Member_Position);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('check_mode',$check_mode);

	
			$this->smarty->display("intranet/common_contents/work_book/libraryHM_application_list_mvc.tpl");
		}



		//================================================================================
		// 도서 상태목록 list logic
		//================================================================================	

		function HMstatus_list()
		{
			global $db,$memberID,$company_index;
			global $Auth,$tab_index,$sub_index,$selt,$searchv,$Member_Name,$Member_Position;
			global $Start,$page,$currentPage,$last_page,$CompanyKind;

			$today=date("Y-m-d");
		
			$pre_1month_date= date("Y-m-d",strtotime("-31 day"));
			
			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무'))
			{
				$this->smarty->assign('Auth',true);
			}
			else
			{
				$this->smarty->assign('Auth',false);
			}

			$page=15;
	
			if($tab_index=="") $tab_index=0;


			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);
	

			$query_data = array(); 

			$sql_num = "select * from library_tbl where book_status <> '0'";
			
			//echo $sql_num."<br>";
			$re_num = mysql_query($sql_num);
			$TotalRow = mysql_num_rows($re_num);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/$page);

			$i=$Start;
			$sql = $sql_num." order by book_id desc limit $Start, $page";	

			//echo $sql."<br>";
			$re = mysql_query($sql);
			while($re_row = mysql_fetch_array($re)) 
			{
				$book_id=$re_row[book_id];
				array_push($query_data,$re_row);

			}
			
			if($currentPage == "") $currentPage = 1; 

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();


			$tab_Titel = array('신간','경제/경영','자기계발','소설/에세이','인문/과학','역사/문화','생활/건강/여행','컴퓨터/IT','전문서적','기타');
			$tab_value = array('0','1','2','3','4','5','6','8','7','9');


			$this->smarty->assign("page_action","library_controller.php");
			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('selt',$selt);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Member_Name',$Member_Name);
			$this->smarty->assign('Member_Position',$Member_Position);
			$this->smarty->assign('titlemsg',$titlemsg);
			$this->smarty->assign('regnummsg',$regnummsg);
			$this->smarty->assign('regdatemsg',$regdatemsg);
			$this->smarty->assign('agentmsg',$agentmsg);
			$this->smarty->assign('agent_count',$agent_count);
			$this->smarty->assign('processname',$processname);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('tab_Titel',$tab_Titel);
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('company_index',$company_index);
			$this->smarty->assign('register2',$register2);
			$this->smarty->assign('Start',$Start);
			$this->smarty->assign('TotalRow',$TotalRow);
			$this->smarty->assign('last_start',$last_start);
			$this->smarty->assign('last_page',$last_page);
			$this->smarty->assign('currentPage',$currentPage);
			
			$this->smarty->assign('rent_index',$rent_index);			
			$this->smarty->assign('RentRow',$RentRow);			
			$this->smarty->assign('MemberNo',$MemberNo);
			$this->smarty->assign('status',$status);
			$this->smarty->display("intranet/common_contents/work_book/libraryHM_status_mvc.tpl");
		}
}

?>