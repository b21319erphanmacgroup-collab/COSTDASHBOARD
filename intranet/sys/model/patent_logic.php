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

	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드

	extract($_GET);
	class PatentLogic {
		var $smarty;
		function PatentLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//================================================================================
		// 지적재산권 입력화면  Logic
		//================================================================================
		
		function InsertPage()
		{
			global $db,$memberID;
			global $mode,$kind,$CompanyKind;

			if($kind=="") $kind=1;
			if($regdate=="") $regdate=date("Y-m-d");
			
				if($CompanyKind=="JANG")
				{
					switch ($kind) {
						case 1: 
							$titlemsg="출원명";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="등록권리자";
							break;
						case 2: 
							$titlemsg="고안의 명칭";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="등록권리자";
							break;
						case 3: 
							$titlemsg="디자인 명칭";
							$regnummsg="발급번호";
							$regdatemsg= "발급일";
							$agentmsg="등록권리자";
							break;
						case 4: 
							$titlemsg="명칭";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="지정/인정";
							break;
						case 5: 
							$titlemsg="서비스표를 사용할<br> 서비스업 및 구분";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="서비스표권자";
							break;
							
						}
				}
				else if ($CompanyKind=="PILE")
				{
						switch ($tab_index) {
							case 1: 
								$titlemsg="출원명";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 2: 
								$titlemsg="디자인의 대상이 되는 물품";
								$regnummsg="발급번호";
								$regdatemsg= "발급일";
								$agentmsg="등록권리자";
								break;
							case 3: 
								$titlemsg="명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="지정/인정";
								break;
							case 4: 
								$titlemsg="상표";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="상표권자";
								break;
							
						}
				}
			

			$url="patent_controller.php";
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('company',$company);
			$this->smarty->assign('kind',$kind);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('titlemsg',$titlemsg);
			$this->smarty->assign('regnummsg',$regnummsg);
			$this->smarty->assign('regdatemsg',$regdatemsg);
			$this->smarty->assign('agentmsg',$agentmsg);

			$this->smarty->assign('regdate',$regdate);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","patent_controller.php");

			$this->smarty->display("intranet/common_contents/work_documents/patent_input_mvc.tpl");
		}


		//================================================================================
		// 한맥 지적재산권 입력화면  Logic
		//================================================================================	
		function HMInsertPage()
		{
			global $db,$memberID;
			global $mode,$kind,$CompanyKind;

			if($kind=="") $kind=1;
			if($regdate=="") $regdate=date("Y-m-d");
			
		

			$url="patent_controller.php";
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('company',$company);
			$this->smarty->assign('kind',$kind);
			$this->smarty->assign('mode',$mode);

			$this->smarty->assign('regdate',$regdate);
			$this->smarty->assign('move_list',$url);
			$this->smarty->assign("page_action","patent_controller.php");

			$this->smarty->display("intranet/common_contents/work_documents/patentHM_input_mvc.tpl");
		}


		//================================================================================
		// /지적재산권 insert logic
		//================================================================================	
		function HMInsertAction()
		{

			global $db,$memberID;
			global $mode,$kind,$techNo,$CompanyKind;
			global $title,$viewtitle,$appnum,$applydate;
			global $contents,$contentsfile,$regnum,$confirmdate;
			global $register;
			global $filename,$userfile,$userfile_name,$userfile_size;

			if($company=="") $company=1;	

			$now_day=date("Y-m-d h:i:s");

			$file_path="./../../../intranet_file/documents/patentHM/contentsfile/";
			$file_path_is="./../../../intranet_file/documents/patentHM/contentsfile";
			$registration_path="./../../../intranet_file/documents/patentHM/registrationfile/";

			$contentsfile2 = substr($contentsfile, 15);

			if($userfile_name <>"" && $userfile_size <>0) 
			{ //첨부파일 있으면서 수정이면

				$ext = explode(".", $userfile_name);
				$ext = strtolower(trim($ext[count($ext)-1]));
				$userfile_name = "$appnum".".$ext";
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
				$contentsfile = "./contentsfile/".$filename;
			
			$sql1="select max(techNo) No from tech_info_new_tbl";
				$re1=mysql_query($sql1,$db);
				$MaxtechNo=mysql_result($re1,0,"No");
				$techNo=$MaxtechNo+1;


				$sql="insert into tech_info_new_tbl (techNo,kind,title,viewtitle,viewcontents,appnum,applydate,regnum,confirmdate,contents,contentsfile) values";
				$sql=$sql."('$techNo','$kind','$title','$viewtitle','$title','$appnum','$applydate','$regnum','$confirmdate','$contents','$contentsfile')";
			}
			
			else
			{
				$sql="insert into tech_info_new_tbl (techNo,kind,title,viewtitle,viewcontents,appnum,applydate,regnum,confirmdate,contents) values";
				$sql=$sql."('$techNo','$kind','$title','$viewtitle','$title','$appnum','$applydate','$regnum','$confirmdate','$contents')";
			}
			//echo $sql."<br>";
			mysql_query($sql,$db);	

			for($j=0; $j <= 4; $j++)	
			{
			$k=$j+1;
			
				if($register[$j] <> "")
				{
						$sql4="insert into tech_register_tbl (techNo,register,contact,remark,no) values";
						$sql4=$sql4."('$techNo','$register[$j]','$contact','$remark','$k')";
						
						mysql_query($sql4,$db);	
				}
			}

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"patent_controller.php?ActionMode=HMread_page&tab_index=$kind&techNo=$techNo");
			$this->smarty->display("intranet/move_page.tpl");
		}


		//================================================================================
		// 지적재산권 읽기 logic
		//================================================================================	
		function UpdateReadPage()
		{
			global $db,$memberID;
			global $mode,$page,$currentPage;
			global $techNo,$kind,$CompanyKind;
			
			if($_SESSION['auth_patent'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);
		
			$query_data = array(); 
			
			$techsql="select * from tech_list_tbl where techNo='$techNo'";
			$retech = mysql_query($techsql,$db);
			while($row_tech = mysql_fetch_array($retech))
			{
				$kind2=$row_tech[kind];
				$title=$row_tech[title];
				$regnum=$row_tech[regnum];
				$regdate=$row_tech[regdate];
				$agent=$row_tech[agent];
				$etc=$row_tech[etc];
				$service=$row_tech[service];
				$filename=$row_tech[filename];
			}

			if($kind==""){$kind=$kind2;}

					if($CompanyKind=="JANG")
					{
						switch ($kind) {
							case 1: 
								$titlemsg="출원명";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 2: 
								$titlemsg="고안의 명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 3: 
								$titlemsg="디자인 명칭";
								$regnummsg="발급번호";
								$regdatemsg= "발급일";
								$agentmsg="등록권리자";
								break;
							case 4: 
								$titlemsg="명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="지정/인정";
								break;
							case 5: 
								$titlemsg="서비스표를 사용할<br> 서비스업 및 구분";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="서비스표권자";
								break;
						}
					}
					else if ($CompanyKind=="PILE")
					{
						switch ($tab_index) {
							case 1: 
								$titlemsg="출원명";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 2: 
								$titlemsg="디자인의 대상이 되는 물품";
								$regnummsg="발급번호";
								$regdatemsg= "발급일";
								$agentmsg="등록권리자";
								break;
							case 3: 
								$titlemsg="명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="지정/인정";
								break;
							case 4: 
								$titlemsg="상표";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="상표권자";
								break;
						}
					}
				


				$this->smarty->assign('kind',$kind);	
				$this->smarty->assign('patent_kind',$patent_kind);					
				$this->smarty->assign('techNo',$techNo);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('title',$title);
				$this->smarty->assign('regnum',$regnum);
				$this->smarty->assign('regdate',$regdate);
				$this->smarty->assign('agent',$agent);
				$this->smarty->assign('etc',$etc);
				$this->smarty->assign('service',$service);
				$this->smarty->assign('filename',$filename);
				$this->smarty->assign('titlemsg',$titlemsg);
				$this->smarty->assign('regnummsg',$regnummsg);
				$this->smarty->assign('regdatemsg',$regdatemsg);
				$this->smarty->assign('agentmsg',$agentmsg);
				
				$this->smarty->assign("page_action","patent_controller.php");
			
			if($mode=="mod")
				$this->smarty->display("intranet/common_contents/work_documents/patent_input_mvc.tpl");
			else
				$this->smarty->display("intranet/common_contents/work_documents/patent_read_mvc.tpl");
			
		}

		//================================================================================
		// 지적재산권 읽기 (한맥-전체) logic
		//================================================================================	
		function HMUpdateReadPage()
		{
			global $db,$memberID;
			global $mode,$page,$currentPage;
			global $techNo,$kind,$CompanyKind;
			
			if($_SESSION['auth_patent'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);
		
			$file_path="./../../../intranet_file/documents/patentHM/contentsfile/";
			$registration_path="./../../../intranet_file/documents/patentHM/registrationfile/";

			$query_data = array(); 
			
			$techsql="select * from tech_info_new_tbl where techNo='$techNo'";
			
			$retech = mysql_query($techsql,$db);
			while($row_tech = mysql_fetch_array($retech))
			{
				$filed=$row_tech[filed];
				$kind2=$row_tech[kind];
				$title=$row_tech[title];
				$applydate=$row_tech[applydate];
				$confirmdate=$row_tech[confirmdate];
				$validity=$row_tech[validity];
				$contents=$row_tech[contents];
				$remark=$row_tech[remark];

				$filename=$row_tech[filename];

				$filename2 = substr($filename, 7);

				$filename_all=$registration_path.$filename2;

				$appnum=$row_tech[appnum];
				$regnum=$row_tech[regnum];
				$designer=$row_tech[designer];
				$agent=$row_tech[agent];
				$process=$row_tech[process];
				$contentsfile=$row_tech[contentsfile];
				$contentsfile2 = substr($contentsfile, 15);

				$contentsfile_all=$file_path.$contentsfile2;

				$techSerialNo=$row_tech[techSerialNo];
				$techmemo=$row_tech[techmemo];
				$originalfile=$row_tech[originalfile];
				$resultfile=$row_tech[resultfile];
				$viewtitle=$row_tech[viewtitle];
				$viewcontents=$row_tech[viewcontents];
				$PQuse=$row_tech[PQuse];

						$k=0;
						$sql5="select * from tech_register_tbl where techNo = '$techNo' order by no";
							$re5 = mysql_query($sql5);
							$Reg_Row = mysql_num_rows($re5);//출원인 수
								while($re_row5 = mysql_fetch_array($re5)) 
								{
									$register[$k]=$re_row5[register];
								$k++;}
			}

			if($kind==""){$kind=$kind2;}

			
				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('techNo',$techNo);
				$this->smarty->assign('filed',$filed);	
				$this->smarty->assign('kind',$kind);					
				$this->smarty->assign('title',$title);
				$this->smarty->assign('appnum',$appnum);
				$this->smarty->assign('regnum',$regnum);

				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('title',$title);
				$this->smarty->assign('applydate',$applydate);
				$this->smarty->assign('confirmdate',$confirmdate);
				$this->smarty->assign('validity',$validity);
				$this->smarty->assign('contents',$contents);
				$this->smarty->assign('remark',$remark);
				$this->smarty->assign('filename',$filename);
				$this->smarty->assign('filename2',$filename2);

				$this->smarty->assign('designer',$designer);
				$this->smarty->assign('agent',$agent);
				$this->smarty->assign('process',$process);
				$this->smarty->assign('contentsfile',$contentsfile);
				$this->smarty->assign('contentsfile_all',$contentsfile_all);

				$this->smarty->assign('techSerialNo',$techSerialNo);
				$this->smarty->assign('techmemo',$techmemo);
				$this->smarty->assign('originalfile',$originalfile);
				$this->smarty->assign('resultfile',$resultfile);
				$this->smarty->assign('viewtitle',$viewtitle);
				$this->smarty->assign('viewcontents',$viewcontents);
				$this->smarty->assign('PQuse',$PQuse);
				$this->smarty->assign('Reg_Row',$Reg_Row);
				$this->smarty->assign('register',$register);

				$this->smarty->assign("page_action","patent_controller.php");
			
			if($mode=="mod")
				$this->smarty->display("intranet/common_contents/work_documents/patentHM_input_mvc.tpl");
			else
				$this->smarty->display("intranet/common_contents/work_documents/patentHM_read_mvc.tpl");
			
		}

		//================================================================================
		// 한맥지적재산권 수정 logic
		//================================================================================	
		function HMUpdateAction()
		{
			global $db,$memberID;
			global $mode,$kind,$techNo;
			global $title,$viewtitle,$appnum,$applydate;
			global $contents,$contentsfile,$regnum,$confirmdate;
			global $register,$CompanyKind;
			global $filename,$userfile,$userfile_name,$userfile_size;

			if($company=="") $company=1;	

			$now_day=date("Y-m-d h:i:s");

			$file_path="./../../../intranet_file/documents/patentHM/contentsfile/";
			$file_path_is="./../../../intranet_file/documents/patentHM/contentsfile";
			$registration_path="./../../../intranet_file/documents/patentHM/registrationfile/";

			$contentsfile2 = substr($contentsfile, 15);

			if($userfile) 
			{
				if($userfile_name <>"" && $userfile_size <>0) 
				{ //첨부파일 있으면서 수정이면
					$contentsfile2=iconv("UTF-8", "EUC-KR",$contentsfile2);
					$orgfilename = $file_path.$contentsfile2;
					$exist_org = file_exists("$orgfilename");
					if($exist_org) 
						{
							$re=unlink("$orgfilename");
						}
				}

				$ext = explode(".", $userfile_name);
				$ext = strtolower(trim($ext[count($ext)-1]));
				$userfile_name = "$appnum".".$ext";
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
				$contentsfile = "./contentsfile/".$filename;
				
				$sql  = "update tech_info_new_tbl set kind='$kind',title='$title',viewtitle='$viewtitle',viewcontents='$title',appnum='$appnum',applydate='$applydate',regnum='$regnum',confirmdate='$confirmdate',contents='$contents',contentsfile='$contentsfile' where techNo='$techNo'";
			}
			else
			{
				$sql  = "update tech_info_new_tbl set kind='$kind',title='$title',viewtitle='$viewtitle',viewcontents='$title',appnum='$appnum',applydate='$applydate',regnum='$regnum',confirmdate='$confirmdate',contents='$contents',contentsfile='$contentsfile' where techNo='$techNo'";
			}

				mysql_query($sql,$db);

				$sql3 = "delete from tech_register_tbl where techNo=$techNo";
				mysql_query($sql3,$db);

			for($j=0; $j <= 4; $j++)	
			{
			$k=$j+1;
			
				if($register[$j] <> "")
				{
					$sql4="insert into tech_register_tbl (techNo,register,contact,remark,no) values";
					$sql4=$sql4."('$techNo','$register[$j]','$contact','$remark','$k')";
					
					mysql_query($sql4,$db);	
				}
			}

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"patent_controller.php?ActionMode=HMread_page&tab_index=$kind&techNo=$techNo");
			$this->smarty->display("intranet/move_page.tpl");
		}


		//================================================================================
		// 지적재산권 수정 logic
		//================================================================================	
		function UpdateAction()
		{
			global $db,$memberID;
			global $mode,$kind,$techNo;
			global $title,$regnum,$regdate,$agent;
			global $etc,$service,$filename,$CompanyKind;
			global $userfile,$userfile_name,$userfile_size;
				
			if($company=="") $company=1;	

			$now_day=date("Y-m-d h:i:s");
			$file_path="./../../../intranet_file/documents/patent/";
			$file_path_is ="./../../../intranet_file/documents/patent";

			 $sql4 = "select * from tech_list_tbl where techNo='$techNo'";
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
				$vupload = $path.$_FILES['userfile']['name'];
				$vupload = str_replace(" ","",$vupload); 
				$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
				move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
				$userfile_size = number_format($userfile_size);
				$filename=$userfile_name;
				$filename = str_replace(" ","",$filename); 

				$sql  = "update tech_list_tbl set kind='$kind',title='$title',regnum='$regnum',regdate='$regdate',agent='$agent',filename='$filename',etc='$etc',service='$service' where techNo='$techNo'";
			}
			else
			{
				$sql  = "update tech_list_tbl set kind='$kind',title='$title',regnum='$regnum',regdate='$regdate',agent='$agent',etc='$etc',service='$service' where techNo='$techNo'";
			}

			mysql_query($sql,$db);

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"patent_controller.php?ActionMode=view&tab_index=$kind");
			$this->smarty->display("intranet/move_page.tpl");
		}


		//================================================================================
		// 한맥지적재산권 삭제 Logic
		//================================================================================	
		function HMDeleteAction()
		{

				global $db,$memberID;
				global $title,$mode,$techNo;
				global $comment,$wdate,$ip,$CompanyKind;
				
				$sql="select * from tech_list_tbl where techNo='$techNo'";

				$re=mysql_query($sql,$db);
				$filename = mysql_result($re, 0, "filename");	
				$kind = mysql_result($re, 0, "kind");	

				$file_path="./../../../intranet_file/documents/patent/";

				$filename=iconv("UTF-8", "EUC-KR",$filename);
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
					$sql2  = "update tech_list_tbl set filename='' where techNo='$techNo'";
					mysql_query($sql2,$db);
				}

				if($mode=="del")
				{
					$sql3 = "delete from tech_list_tbl where techNo=$techNo";
					mysql_query($sql3,$db);
				}
				
				$this->smarty->assign('target',"opener");
				$this->smarty->assign('MoveURL',"patent_controller.php?ActionMode=view&tab_index=$kind");
				$this->smarty->display("intranet/move_page.tpl");
		}
			

		//================================================================================
		// 지적재산권 삭제 Logic
		//================================================================================	
		function DeleteAction()
		{

				global $db,$memberID;
				global $title,$mode,$techNo;
				global $comment,$wdate,$ip,$CompanyKind;
				
				$sql="select * from tech_list_tbl where techNo='$techNo'";

				$re=mysql_query($sql,$db);
				$filename = mysql_result($re, 0, "filename");	
				$kind = mysql_result($re, 0, "kind");	

				$file_path="./../../../intranet_file/documents/patent/";

				$filename=iconv("UTF-8", "EUC-KR",$filename);
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
					$sql2  = "update tech_list_tbl set filename='' where techNo='$techNo'";
					mysql_query($sql2,$db);
				}

				if($mode=="del")
				{
					$sql3 = "delete from tech_list_tbl where techNo=$techNo";
					mysql_query($sql3,$db);
				}
				
				$this->smarty->assign('target',"opener");
				$this->smarty->assign('MoveURL',"patent_controller.php?ActionMode=view&tab_index=$kind");
				$this->smarty->display("intranet/move_page.tpl");
		}
			

		//================================================================================
		// 지적재산권 list logic
		//================================================================================	
		function View()
		{
			global $db,$memberID;
			global $auth,$tab_index,$sub_index,$selt,$searchv;
			global $Start,$page,$currentPage,$last_page,$CompanyKind;


			if($_SESSION['auth_patent'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);

			$page=15;
	
			if($tab_index=="") $tab_index=1;
			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);
	
				if($CompanyKind=="JANG")
				{
					switch ($kind) {
						case 1: 
							$titlemsg="출원명";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="등록권리자";
							break;
						case 2: 
							$titlemsg="고안의 명칭";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="등록권리자";
							break;
						case 3: 
							$titlemsg="디자인 명칭";
							$regnummsg="발급번호";
							$regdatemsg= "발급일";
							$agentmsg="등록권리자";
							break;
						case 4: 
							$titlemsg="명칭";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="지정/인정";
							break;
						case 5: 
							$titlemsg="서비스표를 사용할<br> 서비스업 및 구분";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="서비스표권자";
							break;
							
						}
				}
				else if ($CompanyKind=="PILE")
				{
						switch ($tab_index) {
							case 1: 
								$titlemsg="출원명";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 2: 
								$titlemsg="디자인의 대상이 되는 물품";
								$regnummsg="발급번호";
								$regdatemsg= "발급일";
								$agentmsg="등록권리자";
								break;
							case 3: 
								$titlemsg="명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="지정/인정";
								break;
							case 4: 
								$titlemsg="상표";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="상표권자";
								break;
							
						}
				}
				else if ($CompanyKind=="HANM")
				{
						switch ($tab_index) {
							case 1: 
								$titlemsg="출원명";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 2: 
								$titlemsg="디자인의 대상이 되는 물품";
								$regnummsg="발급번호";
								$regdatemsg= "발급일";
								$agentmsg="등록권리자";
								break;
							case 3: 
								$titlemsg="명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="지정/인정";
								break;
							case 4: 
								$titlemsg="상표";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="상표권자";
								break;
							
						}
				}
				
				else if ($CompanyKind=="BARO")
				{
						switch ($tab_index) {
							case 1: 
								$titlemsg="출원명";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 2: 
								$titlemsg="디자인의 대상이 되는 물품";
								$regnummsg="발급번호";
								$regdatemsg= "발급일";
								$agentmsg="등록권리자";
								break;
							case 3: 
								$titlemsg="명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="지정/인정";
								break;
							case 4: 
								$titlemsg="상표";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="상표권자";
								break;
							
						}
				}


			$query_data = array(); 
			
			if($selt=="명칭")
			{
				$sql_num="select * from tech_list_tbl where kind='$tab_index' and title  like '%$searchv%'";//개수 세기 쿼리문
			}
			else if($selt=="번호")
			{
				$sql_num="select * from tech_list_tbl where kind='$tab_index' and regnum  like '%$searchv%'";//개수 세기 쿼리문
			}
			else
			{
				$sql_num="select * from tech_list_tbl where kind='$tab_index'";;//개수 세기 쿼리문
			}
				$re_num = mysql_query($sql_num,$db);
				$TotalRow = mysql_num_rows($re_num);//총 개수 저장
				$last_start = ceil($TotalRow/10)*10+1;;
				$last_page=ceil($TotalRow/$page);

			$i=$Start;
			$sql = $sql_num." order by regdate desc limit $Start, $page";	

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				$re_row[title_IS]=utf8_strcut($re_row[title],44,'...');
				//$re_row[agent]=str_replace(",","<br>",$re_row[agent]);
				$agent=$re_row[agent];
				$agent=split(",",$agent);
				$agent_count[$i]=count($agent);
			
				array_push($query_data,$re_row);
				$i++;
			}


			if($currentPage == "") $currentPage = 1; 

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			
			if($CompanyKind=="JANG")
			{
				$tab_Titel = array('특허','실용신안','디자인','신기술','서비스표');
				$tab_value = array('1','2','3','4','5');
			}
			else if ($CompanyKind=="PILE")
			{
				$tab_Titel = array('특허/실용신안','디자인','신기술','상표');
				$tab_value = array('0','2','6','7');
			}

			$this->smarty->assign("page_action","patent_controller.php");
			$this->smarty->assign('selt',$selt);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('auth',$auth);
			$this->smarty->assign('titlemsg',$titlemsg);
			$this->smarty->assign('regnummsg',$regnummsg);
			$this->smarty->assign('regdatemsg',$regdatemsg);
			$this->smarty->assign('agentmsg',$agentmsg);
			$this->smarty->assign('agent_count',$agent_count);
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
			$this->smarty->display("intranet/common_contents/work_documents/patent_contents_mvc.tpl");
		}

		//================================================================================
		// 지적재산권 전체 법인 list logic
		//================================================================================	

		function ViewHM()
		{
			global $db,$memberID,$company_index;
			global $auth,$tab_index,$sub_index,$selt,$searchv;
			global $Start,$page,$currentPage,$last_page,$CompanyKind;

			if($_SESSION['auth_patent'])
				$this->smarty->assign('Auth',true);
			else
				$this->smarty->assign('Auth',false);

			$page=15;
			
			
			if($tab_index=="") $tab_index=0;
			if($company_index=="") $company_index=0;
			
			if($company_index=="1") $register="한맥";
			if($company_index=="2") $register="장헌";
			if($company_index=="3") $register="파일";

			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);
	
				if($CompanyKind=="JANG")
				{
					switch ($kind) {
						case 1: 
							$titlemsg="출원명";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="등록권리자";
							break;
						case 2: 
							$titlemsg="고안의 명칭";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="등록권리자";
							break;
						case 3: 
							$titlemsg="디자인 명칭";
							$regnummsg="발급번호";
							$regdatemsg= "발급일";
							$agentmsg="등록권리자";
							break;
						case 4: 
							$titlemsg="명칭";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="지정/인정";
							break;
						case 5: 
							$titlemsg="서비스표를 사용할<br> 서비스업 및 구분";
							$regnummsg="등록번호";
							$regdatemsg= "등록일";
							$agentmsg="서비스표권자";
							break;
							
						}
				}
				else if ($CompanyKind=="PILE")
				{
						switch ($tab_index) {
							case 1: 
								$titlemsg="출원명";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="등록권리자";
								break;
							case 2: 
								$titlemsg="디자인의 대상이 되는 물품";
								$regnummsg="발급번호";
								$regdatemsg= "발급일";
								$agentmsg="등록권리자";
								break;
							case 3: 
								$titlemsg="명칭";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="지정/인정";
								break;
							case 4: 
								$titlemsg="상표";
								$regnummsg="등록번호";
								$regdatemsg= "등록일";
								$agentmsg="상표권자";
								break;
							
						}
				}
			


			$query_data = array(); 
			if($company_index=="0")
			{
				if($selt=="명칭")
				{
					$sql_num="select * from tech_info_new_tbl where kind='$tab_index' and title  like '%$searchv%'";//개수 세기 쿼리문
				}
				else if($selt=="번호")
				{
					$sql_num="select * from tech_info_new_tbl where kind='$tab_index' and regnum  like '%$searchv%'";//개수 세기 쿼리문
				}
				else
				{
					$sql_num="select * from tech_info_new_tbl where kind='$tab_index'";;//개수 세기 쿼리문
				}
			}
			else
			{
				if($selt=="명칭")
				{
					$sql_num = "select * from 
						(
							select * from tech_register_tbl where register like '%$register%'
						) a INNER JOIN
						(
							select * from tech_info_new_tbl where kind='$tab_index and title  like '%$searchv%'
						)b on a.techNo=b.techNo";
				}
				else if($selt=="번호")
				{
					$sql_num = "select * from 
						(
							select * from tech_register_tbl where register like '%$register%'
						) a INNER JOIN
						(
							select * from tech_info_new_tbl where kind='$tab_index and regnum  like '%$searchv%'
						)b on a.techNo=b.techNo";
				}
				else
				{
					$sql_num = "select * from 
						(
							select * from tech_register_tbl where register like '%$register%'
						) a INNER JOIN
						(
							select * from tech_info_new_tbl where kind='$tab_index'
						)b on a.techNo=b.techNo";
				}
			}
//echo $sql_num."<br>";
			$re_num = mysql_query($sql_num);
			$TotalRow = mysql_num_rows($re_num);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;;
			$last_page=ceil($TotalRow/$page);

			$i=$Start;
			$sql = $sql_num." order by applydate desc limit $Start, $page";
				$re = mysql_query($sql);
				while($re_row = mysql_fetch_array($re)) 
				{
					$re_row[title_IS]=utf8_strcut($re_row[title],44,'...');
					//$re_row[agent]=str_replace(",","<br>",$re_row[agent]);
					$techNo=$re_row[techNo];
					$process=$re_row[process];
					$agent=$re_row[agent];
					$agent=split(",",$agent);
					$agent_count[$i]=count($agent);

					$file_arr=explode("/",$re_row[filename]);
					$file_arr_cnt=count($file_arr);
					$re_row[re_filename]=$file_arr[$file_arr_cnt-1];
				
					array_push($query_data,$re_row);

					$k=0;
					$sql5="select * from tech_register_tbl where techNo = '$techNo'";
						$re5 = mysql_query($sql5);
						while($re_row5 = mysql_fetch_array($re5)) 
						{
							$register2[$i][$k]=$re_row5[register];
						$k++;}
						
					$sql6="select * from tech_processtype_tbl where processno = '$process'";
						$re6 = mysql_query($sql6);

							while($re_row6 = mysql_fetch_array($re6)) 
							{
								$processname[$i]=$re_row6[processname];
							}

					$i++;
				}
			
			if($currentPage == "") $currentPage = 1; 

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);
			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();

			
			if($CompanyKind=="JANG")
			{
				$tab_Titel = array('특허','실용신안','디자인','신기술','서비스표');
				$tab_value = array('1','2','3','4','5');
			}
			else if ($CompanyKind=="PILE")
			{
				$tab_Titel = array('특허,실용신안','디자인','신기술','상표');
				$tab_value = array('1','2','3','4');
			}
			else if ($CompanyKind=="HANM")
			{
				$tab_Titel = array('특허/실용신안','디자인','신기술','상표');
				$tab_value = array('0','2','6','7');
			}

			$this->smarty->assign("page_action","patent_controller.php");
			$this->smarty->assign('selt',$selt);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('auth',$auth);
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
			$this->smarty->display("intranet/common_contents/work_documents/patentHM_contents_mvc.tpl");
		}

}

?>