<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	
	
	include "../inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";

	
	
	
	
	$CompanyKind=searchCompanyKind();		

	extract($_GET);
	class PrivateLogic {
		var $smarty;
		function PrivateLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//============================================================================//
		//==등록화면 Logic (insert)==============================//
		//============================================================================//
		function InsertPage()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $mode,$page,$currentPage,$out;
			global $techNo,$kind,$company;
			global $Category,$no,$file_type,$Com;	

		
			$go_url="intranet/common_contents/work_private/private_inputpage_mvc.tpl";

			if($tab_index=="")
				$tab_index = 1;

			$query_data3 = array(); 

			$sql3  = "select * from systemconfig_tbl where SysKey='privateboardCode' order by orderno";
			$re3 = mysql_query($sql3,$db);
			while($re_row3 = mysql_fetch_array($re3))
			{
				array_push($query_data3,$re_row3);
			}
			
			$first=true;
			$query_data4 = array(); 
			$sql4  = "select * from systemconfig_tbl where SysKey='privateboardSubCode' and CodeORName='$tab_index' order by orderno";
			$re4 = mysql_query($sql4,$db);
			while($re_row4 = mysql_fetch_array($re4))
			{
				array_push($query_data4,$re_row4);

				if($sub_index=="")
				{
					if($first)
					{
						$sub_index = $re_row4[Code];
						$first=false;
					}
				}
			}


			$this->smarty->assign('query_data3',$query_data3);		
			$this->smarty->assign('query_data4',$query_data4);		

			
			$writedate=date("Y-m-d");

			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('writedate',$writedate);
			$this->smarty->assign('datatype','1');
			$this->smarty->assign('Com',$Com);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","private_controller.php");
			
			$this->smarty->display($go_url);
		}//function InsertPage()

		//============================================================================//
		//==등록 Action Logic (insert)==============================//
		//============================================================================//
		function InsertAction()
		{

			global $db,$memberID,$Category;
			global $datatitle,$datawriter,$publishing;
			global $writedate,$datatype,$datatype_sub;
			global $sum,$date,$filename;
			global $userfile,$userfile_name,$userfile_size;
			global $note,$type1,$type2,$out,$Com;	


			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘
			
			 
			$date=date("Y-m-d");
						
			$datatitle2 = str_replace(" ","",$datatitle);	
			$datatitle2 = str_replace(".","",$datatitle2);	
			$datatitle2 = str_replace("/","",$datatitle2);	
			$datatitle2 = str_replace("'","",$datatitle2);	
			$datatitle2 = str_replace("!","",$datatitle2);	
			$datatitle2 = str_replace("?","",$datatitle2);	
			$datatitle2 = str_replace(":","",$datatitle2);	
			$datatitle2 = str_replace(";","",$datatitle2);	
			$datatitle2 = str_replace("~","",$datatitle2);	
			$datatitle2 = str_replace("`","",$datatitle2);	
			$datatitle2 = str_replace("|","",$datatitle2);	
			$datatitle2 = str_replace("@","",$datatitle2);	
			$datatitle2 = str_replace("#","",$datatitle2);	
			$datatitle2 = str_replace("$","",$datatitle2);	
			$datatitle2 = str_replace("%","",$datatitle2);	
			$datatitle2 = str_replace("^","",$datatitle2);	
			$datatitle2 = str_replace("&","",$datatitle2);	
			$datatitle2 = str_replace("*","",$datatitle2);	
			$datatitle2 = str_replace("=","",$datatitle2);	
			$datatitle2 = str_replace("+","",$datatitle2);	
			$datatitle2 = str_replace("(","",$datatitle2);	
			$datatitle2 = str_replace(")","",$datatitle2);	

			$datatitle2=$this->bear3StrCut($datatitle2,15,"");
			
		
		//******************************************************************************************//
		//****************첨부파일 첨부 부분*****************************************************//
		//******************************************************************************************//
			

			$path ="./../../../intranet_file/private/".$datatype."/";
			$path_is ="./../../../intranet_file/private/".$datatype;

		
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
					$prefile=time();
					$userfile=stripslashes($userfile);
					$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
					$vupload = $path."[".$prefile."]".$_FILES['userfile']['name'];
					$vupload = str_replace(" ","",$vupload); 
					$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
					move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
					
					$filename_m="[".$prefile."]".$userfile_name;
					$filename_m = str_replace(" ","",$filename_m); 

					
					$filename=$userfile_name;
					$real_filename=$filename_m;

			}	
			else
			{
				$filename="";
				$userfile_size="";
			}
		//******************************************************************************************//
		//****************첨부파일 첨부 부분********************************* 끝*****************//
		//******************************************************************************************//

			if($filename != "") 
			{
				$query00 = "insert into private_list_tbl (datatitle,writedate,datatype,datatype_sub,updateuser,insertdate,note,filename,real_filename,filesize) values('$datatitle','$writedate','$datatype','$datatype_sub','$memberID','$date','$note','$filename','$real_filename','$userfile_size')";
			}
			else
			{
				$query00 = "insert into private_list_tbl (datatitle,writedate,datatype,datatype_sub,updateuser,insertdate,note) values('$datatitle','$writedate','$datatype','$datatype_sub','$memberID','$date','$note')";
			}

			//echo $query00."<br>";
			mysql_query($query00);
			
			$this->smarty->assign('target',"reload");
			$this->smarty->assign('MoveURL',"private_controller.php?ActionMode=ActionMode=view&Category=1&sub_index=$datatype_sub&tab_index=$datatype&memberID=$memberID");
			$this->smarty->display("intranet/move_page.tpl");
			

		}//function InsertAction()

		//==================================================================================//
		//==읽기  Logic=================================================//
		//==================================================================================//
		function UpdateReadPage()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $mode,$page,$currentPage;
			global $techNo,$kind,$mode,$out;
			global $Category,$no,$file_type,$ActionMode,$currentPage;	

			/*
			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$Admin=true;
				$this->smarty->assign('Admin',$Admin);
			}else{
				$Admin=false;
				$this->smarty->assign('Admin',$Admin);
			}
				$this->smarty->assign('memberID',$memberID);
			*/

			if ($memberID=="T08301" || $memberID=="T03225" || $memberID=="B16304" || $memberID=="B21308" || $memberID=="B21321" || $memberID=="M21421")
			{	
				$Admin=true;
			}else
			{
				$Admin=false;
			}
			$this->smarty->assign('Admin',$Admin);



			$sql = "select * from private_list_tbl where no = '$no'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);

			$datatitle = mysql_result($re,0,"datatitle");
			$writedate = mysql_result($re,0,"writedate");
			$datatype = mysql_result($re,0,"datatype"); 
			$datatype_sub = mysql_result($re,0,"datatype_sub"); 
			$filename = mysql_result($re,0,"filename"); 
			$real_filename = mysql_result($re,0,"real_filename"); 
			$note = mysql_result($re,0,"note"); 

			

			if($mode=="read")
			{
				$note = nl2br($note);
			}



			if($mode=="read")
			{
				$go_url="intranet/common_contents/work_private/private_input_mvc.tpl";
			}
			else if($mode=="mod")
			{
				$query_data3 = array(); 

				$sql3  = "select * from systemconfig_tbl where SysKey='privateboardCode' order by orderno";
				$re3 = mysql_query($sql3,$db);
				while($re_row3 = mysql_fetch_array($re3))
				{
					array_push($query_data3,$re_row3);
				}
				
				$first=true;
				$query_data4 = array(); 
				$sql4  = "select * from systemconfig_tbl where SysKey='privateboardSubCode' and CodeORName='$tab_index' order by orderno";
				$re4 = mysql_query($sql4,$db);
				while($re_row4 = mysql_fetch_array($re4))
				{
					array_push($query_data4,$re_row4);

					if($sub_index=="")
					{
						if($first)
						{
							$sub_index = $re_row4[Code];
							$first=false;
						}
					}
				}


				$this->smarty->assign('query_data3',$query_data3);		
				$this->smarty->assign('query_data4',$query_data4);		
	

				$go_url="intranet/common_contents/work_private/private_inputpage_mvc.tpl";
			}

			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('mode',$mode);	
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('datatitle',$datatitle);	
			$this->smarty->assign('writedate',$writedate);
			$this->smarty->assign('datatype',$datatype);
			$this->smarty->assign('datatype_sub',$datatype_sub);
			
			$this->smarty->assign('filename',$filename);
			$this->smarty->assign('real_filename',$real_filename);
			$this->smarty->assign('note',$note);
			$this->smarty->assign('no',$no);
			$this->smarty->assign('currentPage',$currentPage);
			

			$this->smarty->display($go_url);
		}

		//============================================================================//
		//==수정 Action Logic (Update)==============================//
		//============================================================================//
		function UpdateAction()
		{

			global $db,$memberID,$Category,$mode;
			global $datatitle,$datawriter,$publishing;
			global $writedate,$datatype,$datatype_sub;
			global $sum,$date,$filename,$no;
			global $userfile,$userfile_name,$userfile_size;
			global $note,$type1,$type2,$out,$tab_index,$currentPage,$sub_index;	

		
			$datatitle2 = str_replace(" ","",$datatitle);	
			$datatitle2 = str_replace(".","",$datatitle2);	
			$datatitle2 = str_replace("/","",$datatitle2);	
			$datatitle2 = str_replace("'","",$datatitle2);	
			$datatitle2 = str_replace("!","",$datatitle2);	
			$datatitle2 = str_replace("?","",$datatitle2);	
			$datatitle2 = str_replace(":","",$datatitle2);	
			$datatitle2 = str_replace(";","",$datatitle2);	
			$datatitle2 = str_replace("~","",$datatitle2);	
			$datatitle2 = str_replace("`","",$datatitle2);	
			$datatitle2 = str_replace("|","",$datatitle2);	
			$datatitle2 = str_replace("@","",$datatitle2);	
			$datatitle2 = str_replace("#","",$datatitle2);	
			$datatitle2 = str_replace("$","",$datatitle2);	
			$datatitle2 = str_replace("%","",$datatitle2);	
			$datatitle2 = str_replace("^","",$datatitle2);	
			$datatitle2 = str_replace("&","",$datatitle2);	
			$datatitle2 = str_replace("*","",$datatitle2);	
			$datatitle2 = str_replace("=","",$datatitle2);	
			$datatitle2 = str_replace("+","",$datatitle2);	
			$datatitle2 = str_replace("(","",$datatitle2);	
			$datatitle2 = str_replace(")","",$datatitle2);	

			$datatitle2=$this->bear3StrCut($datatitle2,15,"");

			
			$query02 = "select * from private_list_tbl where no ='$no'";
			$re2 = mysql_query($query02,$db);
			while($re_row2 = mysql_fetch_array($re2)) 
			{
				//$filename=$re_row2[filename];
				$real_filename_org=$re_row2[real_filename];
			}
			$real_filename_org=iconv("UTF-8", "EUC-KR",$real_filename_org);
		//******************************************************************************************//
		//****************첨부파일 첨부 부분*****************************************************//
		//******************************************************************************************//
			
			$path ="./../../../intranet_file/private/".$datatype."/";
			$path_is ="./../../../intranet_file/private/".$datatype;

		
			$userfile_name = str_replace(" ","",$userfile_name);
			if ($userfile_name <>"" && $userfile_size <>0)
			{
					
					$orgfilename = $path.$real_filename_org;
					$exist_org = file_exists($orgfilename);
					if($exist_org) 
					{
						
						$re=unlink($orgfilename);
					}
					$prefile=time();
					$userfile=stripslashes($userfile);
					$_FILES['userfile']['name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['name']);
					$vupload = $path."[".$prefile."]".$_FILES['userfile']['name'];
					$vupload = str_replace(" ","",$vupload); 
					$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
					move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
					
					$filename_m="[".$prefile."]".$userfile_name;
					$filename_m = str_replace(" ","",$filename_m); 

					
					$filename=$userfile_name;
					$real_filename=$filename_m;

			}	
			else
			{
				$filename="";
				$real_filename="";
			}

		//******************************************************************************************//
		//****************첨부파일 첨부 부분********************************* 끝*****************//
		//******************************************************************************************//



			if($real_filename <> "") 
			{
				$query00 = "update private_list_tbl set datatitle='$datatitle',datatype_sub='$datatype_sub',writedate='$writedate',datatype='$datatype',updateuser='$memberID' ,insertdate='$date',filename='$filename',real_filename='$real_filename',filesize='$userfile_size',note='$note' where no='$no'";
			}
			else 
			{

				$query00 = "update private_list_tbl set datatitle='$datatitle',datatype_sub='$datatype_sub',writedate='$writedate',datatype='$datatype',updateuser='$memberID' ,insertdate='$date',note='$note' where no='$no'";
			}
			
			//echo $query00."<br>";
			mysql_query($query00);
			
		
			$this->smarty->assign('target',"reload");
			$this->smarty->assign('MoveURL',"private_controller.php?ActionMode=view&mode=read&Category=1&no=$no&tab_index=$datatype&currentPage=1&memberID=$memberID&sub_index=$sub_index");
			$this->smarty->display("intranet/move_page.tpl");
		
		}//function UpdateAction()


		//===================================================================================//
		//==삭제 Logic=================================================//
		//===================================================================================//
		function DeleteAction()
		{

			global $db,$memberID,$Category;
			global $datatitle,$datawriter,$publishing;
			global $writedate,$datatype,$datecustody;
			global $sum,$date,$filename,$no;
			global $userfile,$userfile_name,$userfile_size;
			global $note,$type1,$type2,$out,$tab_index,$currentPage;	

			$sql="select * from private_list_tbl where no=$no";
			$re=mysql_query($sql,$db);
			$real_filename = mysql_result($re, 0, "real_filename");	
			$datatype = mysql_result($re, 0, "datatype");	
			$datatype_sub = mysql_result($re, 0, "datatype_sub");	
		
			$file_path ="./../../../intranet_file/private/".$datatype."/";

			$real_filename=iconv("UTF-8", "EUC-KR",$real_filename);
			$orgfilename = $file_path.$real_filename;
			$exist_org = file_exists($orgfilename);
			if($exist_org) 
			{	
				$re=unlink($orgfilename);
			}
			$sql = "delete from private_list_tbl where no='$no'";
			//echo $sql."<br>"; 
			mysql_query($sql,$db);
			

			$this->smarty->assign('target',"reload");
			$this->smarty->assign('MoveURL',"private_controller.php?ActionMode=ActionMode=view&Category=1&sub_index=$datatype_sub&tab_index=$datatype&memberID=$memberID");
			$this->smarty->display("intranet/move_page.tpl");

		}//function DeleteAction()

	
		//==================================================================================//
		//== 읽기 Logic=================================================//
		//==================================================================================//		
		function View()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index;
			global $Start,$page,$currentPage,$last_page,$selt;
			global $Category,$searchv,$memberID,$selt;	

			/*
			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$Admin=true;
				$this->smarty->assign('Admin',$Admin);
			}else{
				$Admin=false;
				$this->smarty->assign('Admin',$Admin);
			}
			*/
	
			if ($memberID=="T08301" || $memberID=="T03225" || $memberID=="B16304" || $memberID=="B21308" || $memberID=="B21321" || $memberID=="M21421")
			{	
				$Admin=true;
			}else
			{
				$Admin=false;
			}
			$this->smarty->assign('Admin',$Admin);
			
			$page=15;
			
			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);
			

			if($tab_index=="")
				$tab_index = 1;

			

			
			

			$query_data3 = array(); 

			$sql3  = "select * from systemconfig_tbl where SysKey='privateboardCode' order by orderno";
			$re3 = mysql_query($sql3,$db);
			while($re_row3 = mysql_fetch_array($re3))
			{
				array_push($query_data3,$re_row3);
			}
			
			if($searchv=="")
			{
				$first=true;
				$query_data4 = array(); 
				$sql4  = "select * from systemconfig_tbl where SysKey='privateboardSubCode' and CodeORName='$tab_index' order by orderno";
				//echo $sql4;
				$re4 = mysql_query($sql4,$db);
				while($re_row4 = mysql_fetch_array($re4))
				{
					array_push($query_data4,$re_row4);

					if($sub_index=="")
					{
						if($first)
						{
							$sub_index = $re_row4[Code];
							$first=false;
						}
					}

					//$sub_index=$re_row2[Code];
				}
			}

			
			$go_url="intranet/common_contents/work_private/private_contents_mvc.tpl";
			
			$query_data = array(); 

			if($searchv=="")
			{
				
					$sql  = "select * from private_list_tbl where datatype='$tab_index' and datatype_sub='$sub_index'";
					$sql2= $sql." order by writedate desc limit $Start, $page";	
				
			}else
			{
				
					$sql  = "select * from private_list_tbl where ";
					$sql.=" datatitle like '%$searchv%'";
					/*
					if($selt=="제목")//제목찾기
					{
						$sql.=" datatitle like '%$searchv%'";
					}
					else if($selt=="내용")//내용찾기
					{
						$sql.=" note like '%$searchv%'";
					}
					*/
					$sql2= $sql." order by writedate desc limit $Start, $page";	
					$tab_index="-1";
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
				//$re_row2[datatitle]=utf8_strcut($re_row2[datatitle],'28','...');
				$re_row2[note_cut]=utf8_strcut($re_row2[note],'60','...');
				$re_row2[file_img]="document_icon.png";

				$datatype=$re_row2[datatype];
				$re_row2[datatype_name]=$datatype_name;

				
				array_push($query_data,$re_row2);
			}
					
				
			if($currentPage == "") $currentPage = 1; 

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			$this->smarty->assign("page_action","private_controller.php");
			$this->smarty->assign('selt',$selt);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->assign('query_data3',$query_data3);
			$this->smarty->assign('query_data4',$query_data4);
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
			$this->smarty->assign('searchv',$searchv);
			
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
			

			$sql="select * from private_list_tbl where no='$publication_no'";
			$re = mysql_query($sql,$db);
			$datatitle=mysql_result($re,0,"datatitle");


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
			$this->smarty->assign('datatitle',$datatitle);
			$this->smarty->assign('query_data2',$query_data2);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('publication_no',$publication_no);

			$this->smarty->display("intranet/common_contents/work_private/read_list_mvc.tpl");
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



			$sql2="select * from private_list_tbl where no='$publication_no'";
			$re2 = mysql_query($sql2,$db);
			$datatitle=mysql_result($re2,0,"datatitle");





			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('datatitle',$datatitle);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('publication_no',$publication_no);

			$this->smarty->display("intranet/common_contents/work_private/read_input_mvc.tpl");
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

			global $writer_name,$group_code,$group_name,$rank_code,$rank_name,$write_date;
			global $contents,$readfilename,$group_code,$group_name,$rank_code,$rank_name;


			$sql="insert into publication_read_tbl (publication_no,CompanyKind,group_code,group_name,rank_code,rank_name,writer_code,writer_name,write_date,contents,readfilename)";
			$sql.= "values('$publication_no','$CompanyKind','$group_code','$group_name','$rank_code','$rank_name','$memberID','$writer_name','$write_date','$contents','$readfilename')";

			//echo $sql."<br>";
			mysql_query($sql,$db);

			$this->smarty->assign('MoveURL',"private_controller.php?ActionMode=read_list&publication_no=$publication_no&memberID=$memberID&Category=$Category&sub_index=$sub_index&tab_index=$tab_index");
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

			global $writer_name,$group_code,$group_name,$rank_code,$rank_name,$write_date;
			global $contents,$readfilename,$group_code,$group_name,$rank_code,$rank_name;

			
			$sql="select * from publication_read_tbl A,private_list_tbl B where A.read_no='$read_no' and A.publication_no=B.no";
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
				$datatitle=mysql_result($re,0,"datatitle");
				$contents=mysql_result($re,0,"contents");
				//$contents=nl2br($contents);
			}


			$this->smarty->assign('CompanyKind',$CompanyKind);
			$this->smarty->assign('datatitle',$datatitle);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('Category',$Category);
			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('read_no',$read_no);
			$this->smarty->assign('publication_no',$publication_no);


			$this->smarty->assign('group_name',$group_name);
			$this->smarty->assign('writer_code',$writer_code);
			$this->smarty->assign('Name',$writer_name);
			$this->smarty->assign('rank_name',$rank_name);
			$this->smarty->assign('write_date',$write_date);
			$this->smarty->assign('contents',$contents);
			$this->smarty->assign('Edit',true);


			$this->smarty->display("intranet/common_contents/work_private/read_input_mvc.tpl");

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

			$this->smarty->assign('MoveURL',"private_controller.php?ActionMode=read_detail&publication_no=$publication_no&read_no=$read_no&memberID=$memberID&Category=$Category&sub_index=$sub_index&tab_index=$tab_index");
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

			
			$sql = "delete from publication_read_tbl where publication_no=$publication_no";
			//echo $sql."<br>";
			mysql_query($sql,$db);
	
			$this->smarty->assign('MoveURL',"private_controller.php?ActionMode=read_list&publication_no=$publication_no&read_no=$read_no&memberID=$memberID&Category=$Category&sub_index=$sub_index&tab_index=$tab_index");
			$this->smarty->display("intranet/move_page.tpl");
		}
		
			


		function SubCode()
		{
			global $db,$memberID,$CompanyKind;
			global $MainCode,$SubProcessCode;
		
				$query_data4 = array(); 
				$sql4  = "select * from systemconfig_tbl where SysKey='privateboardSubCode' and CodeORName='$MainCode' order by orderno";
				//echo $sql4;
				$re4 = mysql_query($sql4,$db);
				while($re_row4 = mysql_fetch_array($re4))
				{
					array_push($query_data4,$re_row4);
				
				}

				$this->smarty->assign('query_data4',$query_data4);
				$this->smarty->display("intranet/common_contents/work_private/private_subcode.tpl");
		}	


		function bear3StrCut($str,$len,$tail="..."){ 
			$rtn = array(); 
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str; 
		}

}



