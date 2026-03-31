<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	/***************************************
	* 
	* ------------------------------------
	****************************************/ 
	include "../../sys/inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../util/HanamcPageControl.php";
	include "../../../SmartyConfig.php";
	
	require_once($SmartyClassPath);
?>
<?
	extract($_GET);

		$MemberNo	=	"";	//사원번호
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호     
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호     

		$CompanyKind=   $_SESSION['SS_CompanyKind'];//장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)
		$korName	=	$_SESSION['SS_korName'];	//한글이름
		$RankCode	=	$_SESSION['SS_RankCode'];	//직급코드
		if($GroupCode=="")
		{
			$GroupCode	=	$_SESSION['SS_GroupCode'];	//부서코드
		}
		$SortKey	=	$_SESSION['SS_SortKey'];	//직급+부서코드
		$EntryDate	=	$_SESSION['SS_EntryDate'];	//입사일자
		$position	=	$_SESSION['SS_position'];	//직위명
		$GroupName	=	$_SESSION['SS_GroupName'];	//부서명
	}else if($_COOKIE['CK_memberID']!=""){				//쿠키값 유무확인
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_COOKIE['CK_memberID'];	//사원번호     
		$memberID	=   $_COOKIE['CK_memberID'];	//사원번호     

		$CompanyKind=	$_COOKIE['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$korName	=	$_COOKIE['CK_korName'];		//한글이름     
		$RankCode	=	$_COOKIE['CK_RankCode'];	//직급코드
		if($GroupCode=="")
		{
			$GroupCode	=	$_COOKIE['CK_GroupCode'];	//부서코드
		}
		$SortKey	=	$_COOKIE['CK_SortKey'];		//직급+부서코드
		$EntryDate	=	$_COOKIE['CK_EntryDate'];	//입사일자
		$position	=	$_COOKIE['CK_position'];	//직위명
		$GroupName	=	$_COOKIE['CK_GroupName'];	//부서명
	}else{
		/* ----------------------------------- */
		//$memberID	=	$_GET['memberID'];
		$memberID = ($_GET['memberID']==""?$_POST['memberID']:$_GET['memberID']);

		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../../sys/popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */

?>
<?
	class Conversation extends Smarty {
		
	
		function Conversation()
		{

			global $SmartyClass_TemplateDir;
			global $SmartyClass_CompileDir;
			global $SmartyClass_ConfigDir;
			global $SmartyClass_CacheDir;
			global $ProjectCode,$bridgeno,$n_num,$Item_no,$id,$mode;

			
			$this->Smarty();
			$this->template_dir	=$SmartyClass_TemplateDir;
			$this->compile_dir	=$SmartyClass_CompileDir;
			$this->config_dir	=$SmartyClass_ConfigDir;	

		}


		//============================================================================
		// 대화문서 표시
		//============================================================================		
		function ConversationList()
		{
			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$memberID;

			global $CompanyKind,$searchv;
			

	//Login Log 남김
	/*

	$user_ip = $HTTP_SERVER_VARS["REMOTE_ADDR"]; 
	$cfile="../log/".date("Y-m")."_conver.txt";

	$exist = file_exists("$cfile");
	if($exist) {
		$fd=fopen($cfile,'r');
		$con=fread($fd,filesize($cfile));
		fclose($fd);
	}
	$fp=fopen($cfile,'w');
	$aa=date("Y-m-d H:i");
	$cond=$con.$aa." ".$memberID."\n";
	fwrite($fp,$cond);
	fclose($fp);
*/



			$PersonAuthority=new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'대화대표'))
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);


			if($PersonAuthority->GetInfo($memberID,'대화부서'))
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);

			if($PersonAuthority->GetInfo($memberID,'인사B'))
				$this->assign('auth_insa',true);
			else
				$this->assign('auth_insa',false);


			$query_data = array(); 
			$query_data2 = array(); 
			$title_data = array(); 

//$uyear = date("Y"); 
//$sel_year="2018";

$uyear = date("Y")+1; 
if($sel_year=="") $sel_year=date("Y");

			if($this_year=="") $this_year=date("Y");
			//if($this_month=="") $this_month=date("m");
			$this_month="1";

			//$this_year="2017";
			//$this_month="03";

			$this->assign('uyear',$uyear);
			$this->assign('sel_year',$sel_year);
			$this->assign('this_year',$this_year);
			$this->assign('this_month',$this_month);

			if($GroupCode=="")
				$GroupCode=$_SESSION['SS_GroupCode'];


			$MyGroupCode=$_SESSION['MyGroupCode'];



			if($sub_index == "")
				$sub_index=$GroupCode;
			
			$tab_index=$GroupCode;

			
			echo $GroupCode;
			//$sql_title="select * from conversation_title_tbl where conversation_year='$sel_year' and  conversation_title <> '' order by Conversation_step";
			if($GroupCod="03")
			{
					$sql_title="select * from conversation_title_tbl where conversation_year='$sel_year' and  conversation_title <> '' and  Conversation_group ='03' order by Conversation_step";
			}else
			{
				$sql_title="select * from conversation_title_tbl where conversation_year='$sel_year' and  conversation_title <> '' and (Conversation_group IS NULL or Conversation_group ='') 
				order by Conversation_step";
			}
			//echo $sql_title."<br>";
			$re_title = mysql_query($sql_title,$db);

			$title_count=mysql_num_rows($re_title);
			$title_width=1000/$title_count;

			while($re_row_title = mysql_fetch_array($re_title)) 
			{
				/*
				$Conversation_step=$re_row_title[Conversation_step];
				$re_row_title[title][$Conversation_step]=$re_row_title[Conversation_title];
				$re_row_title[subtitle][$Conversation_step]=$re_row_title[Conversation_sub_title];
				*/
				array_push($title_data,$re_row_title);		
			}

			if($searchv=="")
			{	
					if($GroupCode=="003")
					{
						$sql="select * from 
								(
									select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,RealRankCode,RankCode,EntryDate 
								)a1 left JOIN 	(
									 select * from systemconfig_tbl where SysKey='PositionCode' 
								)a2 on a1.RankCode = a2.code ";
					}else
					{	$sql="select * from 
								(
									select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,RankCode 
								)a1 left JOIN 	(
									 select * from systemconfig_tbl where SysKey='PositionCode' 
								)a2 on a1.RankCode = a2.code ";
					}	
			}else
			{
					if($auth_ceo  || $auth_insa)
					{
							$sql="select * from 
							(
								select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and (korName like '$searchv%' or MemberNo like '$searchv%') 
							)a1 left JOIN 	(
								 select * from systemconfig_tbl where SysKey='PositionCode' 
							)a2 on a1.RankCode = a2.code ";	
					}else
					{
							$sql="select * from 
							(
								select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$MyGroupCode' and (korName like '$searchv%' or MemberNo like '$searchv%') 
							)a1 left JOIN 	(
								 select * from systemconfig_tbl where SysKey='PositionCode' 
							)a2 on a1.RankCode = a2.code ";	
					}

							$tab_index="";
			}

			//echo $sql."<br>";
						
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				$Employee_id=$re_row[MemberNo];
				$RankCode=$re_row[RankCode];
				$re_row[EntryYear]=substr($re_row[EntryDate],0,4);
				
				$sql2="select a.*,b.korName as Adviser_name from 
				(
					select * from conversation_tbl where conversation_year='$sel_year' and Employee_id='$Employee_id'
				)a left join(
					select * from member_tbl
				)b on a.Manager_id=b.MemberNo";
				//echo $sql2."<br>";
				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2)) 
				{

					$Conversation_step=$re_row2[Conversation_step];

					$re_row[Conversation_year][$Conversation_step]=$re_row2[Conversation_year];
					$re_row[Conversation_step][$Conversation_step]=$re_row2[Conversation_step];
					$re_row[Employee_id][$Conversation_step]=$re_row2[Employee_id];
					$re_row[File_name][$Conversation_step]=$re_row2[File_name];
					$re_row[Real_file_name][$Conversation_step]=$re_row2[Real_file_name];
					$re_row[Manager_id][$Conversation_step]=$re_row2[Manager_id];
					$re_row[Adviser_name][$Conversation_step]=$re_row2[Adviser_name];
					$re_row[Upload_id][$Conversation_step]=$re_row2[Upload_id];
					$re_row[Input_date][$Conversation_step]=$re_row2[Input_date];
				}

				array_push($query_data,$re_row);
			}

			

			$GroupList = array();
			$Group_Row="0";

			//이성구 부사장은 환경평가부, 수자원부, 도시계획부, 상하수도부  /  이종호 부사장은 도로부, 구조부, 지반터널부, 교통부 정혜윰요청 18.10.16
			if($memberID=="M02202") //이성구
			{
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code in ('22','24','23','25') order by orderno  asc";
			}else if ($memberID=="M02106") //이종호
			{
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code in ('11','12','13','21') order by orderno  asc";
			}else
			{
					$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' and Code<>'28' order by orderno  asc";
			}

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				
				array_push($GroupList,$re_row2);
				$gCode[$Group_Row] = $re_row2[Code];
				$gName[$Group_Row] = $re_row2[Name];

				$Group_Row++;
			}

			if($Group_Row % 9 >0 )
			{
				$Group_Row_num= ceil($Group_Row/9)*9;
			}
			for($k=$Group_Row;$k<$Group_Row_num;$k++) {
  			   $re_row2[Name]="";;
				array_push($GroupList,$re_row2);
			}

			$this->assign('CK_CompanyKind',$CompanyKind);

			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('query_data',$query_data);
			$this->assign('query_data2',$query_data2);
			$this->assign('title_data',$title_data);
			
			$this->assign('memberID',$memberID);
			$this->assign('GroupCode',$GroupCode);
			$this->assign('searchv',$searchv);

			$this->assign('gCode',$gCode);	
			$this->assign('gName',$gName);	
			$this->assign('Group_Row',$Group_Row);	
			$this->assign('GroupList',$GroupList);
			$this->assign('title_width',$title_width);



			$this->display("intranet/common_contents/work_conversation/conversation_list_mvc.tpl");
		}


		
		//============================================================================
		// 대화문서 입력창
		//============================================================================
		function  ConversationInsert()
		{
			global $db;
			global $Conversation_year,$Conversation_step;
			global $Employee_id,$Manager_id,$Upload_id,$memberID;

		
			$title_data = array(); 


			if($this_year=="") $this_year=date("Y");
			if($this_month=="") $this_month=date("m");

/*
$this_year="2018";
$this_month="12";
*/

			//if($start_year=="") $start_year=date("Y");
			$start_year="2018";
			$end_year=date("Y")+1; 

			if($Conversation_year=="") $Conversation_year=$this_year;
						
			/*
			if($this_month>=1 && $this_month<=2 )
			{	
				$end_year=$start_year+1;
			}
			else if($this_month>=3 && $this_month<=11)			
			{	
				$end_year=$start_year+1;
				$start_year=$end_year-1;
			}
			else
			{	
				$end_year=$start_year+1;
			}
			

			if($this_year > $Conversation_year)
			{	
			
				$minmonth=$this_month+11;
				$maxmonth=10;
				$sql_title="select * from conversation_title_tbl where conversation_year='$Conversation_year' and Conversation_step between $minmonth  and $maxmonth order by Conversation_step";
				
			}else if($this_year < $Conversation_year)
			{
				$minmonth=$this_month-13;
				$maxmonth=$minmonth+2;
				$sql_title="select * from conversation_title_tbl where conversation_year='$Conversation_year' and Conversation_step between $minmonth  and $maxmonth order by Conversation_step";
			}else
			{
				//$minmonth=$this_month-5;
				//$maxmonth=$minmonth+4;
				$minmonth=0;;
				$maxmonth=$this_month-1;
				$sql_title="select * from conversation_title_tbl where conversation_year='$Conversation_year' and Conversation_step between $minmonth  and $maxmonth order by Conversation_step";
			}


			if($this_month < 3)
			{
				$Conversation_year=$Conversation_year-1;
			}
			$sql_title="select * from conversation_title_tbl where conversation_year='$Conversation_year' order by Conversation_step";

			*/
			

			
			$sql="select * from member_tbl where MemberNo='$Manager_id'";
			$re = mysql_query($sql,$db);
			if(mysql_num_rows($re) > 0)
			{
				$ManagerGroupCode= mysql_result($re,0,"GroupCode");
			}
			if($ManagerGroupCode=="3")
			{
				$sql_title="select * from conversation_title_tbl where conversation_year='$Conversation_year'  and Conversation_group ='03' order by Conversation_step";
			}else
			{
				$sql_title="select * from conversation_title_tbl where conversation_year='$Conversation_year' order by Conversation_step";
			}


			
			//echo $sql_title."<br>";
			$re_title = mysql_query($sql_title,$db);
			while($re_row_title = mysql_fetch_array($re_title)) 
			{
				
				array_push($title_data,$re_row_title);		
			}
			$this->assign('title_data',$title_data);

			$Employee_name=MemberNo2NamePosition($Employee_id);
			$Manager_name=MemberNo2NamePosition($Manager_id);
			$Employee_group=memberNoToGroupName($Employee_id);
			$Employee_group=str_replace("/", "", $Employee_group); 

			
			$uyear = date("Y")+1; 
			$this->assign('uyear',$uyear);

			$this->assign('Conversation_year',$Conversation_year);	
			$this->assign('Conversation_step',$Conversation_step);	
			
			$this->assign('this_year',$this_year);
			$this->assign('start_year',$start_year);
			$this->assign('end_year',$end_year);

			$this->assign('Employee_id',$Employee_id);	
			$this->assign('Manager_id',$Manager_id);	
			$this->assign('Upload_id',$Upload_id);	
			$this->assign('memberID',$memberID);

			$this->assign('Employee_name',$Employee_name);	
			$this->assign('Manager_name',$Manager_name);	
			$this->assign('Employee_group',$Employee_group);	
			
			/*
			echo $Employee_group;
			echo "Employee_id : ".$Employee_id."<br>";
			echo "Manager_id : ".$Manager_id."<br>";
			echo "Upload_id : ".$Upload_id."<br>";
			echo "memberID : ".$memberID."<br>";

			echo $Conversation_year."<Br>";
			echo $Conversation_step."<Br>";
			*/
			

			$this->display("intranet/common_contents/work_conversation/conversation_insert_mvc.tpl");

		}

		//============================================================================
		// 대화문서 업로드
		//============================================================================
		function  ConversationUpload()
		{


				global $db;
				global $Conversation_year,$Conversation_step;
				global $Employee_id,$Manager_id,$Upload_id,$memberID;
				global $Employee_name,$Manager_name,$Employee_group;
				global $userfile;

				$Conversation_step_2d = sprintf("%02d",$Conversation_step);	

				$path_is ="./../../../intranet_file/conversation";
				if (!is_dir ($path_is))	{ mkdir($path_is, 0777);}

				$path_is = $path_is."/".$Conversation_year;
				if (!is_dir ($path_is))	{ mkdir($path_is, 0777);}

				$path_is = $path_is."/".$Conversation_step_2d;
				if (!is_dir ($path_is)){ mkdir($path_is, 0777);	}

				$path =$path_is."/";

									
				$userfile_name =  $_FILES['userfile']['name'];
				$userfile_size =  $_FILES['userfile']['size'];
		
				$userfile_name = str_replace(" ","",$userfile_name);
				$userfile_name = iconv("UTF-8", "EUC-KR",$userfile_name);
				$userfile_size2 = number_format($userfile_size);

				if ($userfile_name <>"" && $userfile_size <>0)
				{
												
					$array_filename = explode('.',$userfile_name);
					$no=count($array_filename)-1;
					$filename_ext = strtolower($array_filename[$no]);
					$Full_filename_name = "[".$Conversation_year."-".$Conversation_step_2d."][".$Employee_group."]".$Employee_name.".".$filename_ext; //파일명+확장자 
					$Full_filename_name = str_replace(" ","-",$Full_filename_name);	
					$File_name = $Full_filename_name;
					$Full_filename_name = iconv("UTF-8", "EUC-KR",$Full_filename_name);

					$vupload = $path. $Full_filename_name;
					$vupload = str_replace(" ","",$vupload); 

					$_FILES['userfile']['tmp_name'] = iconv("UTF-8", "EUC-KR",$_FILES['userfile']['tmp_name']);
					move_uploaded_file($_FILES['userfile']['tmp_name'], $vupload);
		
				}	
				
				
				$insql ="insert into conversation_tbl (Conversation_year,Conversation_step,Employee_id,File_name,Manager_id,Upload_id,Input_date)";
				$insql .= "values('$Conversation_year','$Conversation_step','$Employee_id','$File_name','$Manager_id','$Upload_id',now())";
				
				//echo $insql;
				mysql_query($insql,$db);

				//$this->display("intranet/common_contents/work_conversation/conversation_insert_mvc.tpl");

				$this->assign('target',"reload2");
				$this->display("intranet/move_page.tpl");

		}
		
		
		//============================================================================
		// 대화문서 삭제
		//============================================================================
		function  ConversationDelete()
		{


				global $db;
				global $Conversation_year,$Conversation_step;
				global $Employee_id,$Manager_id,$Upload_id,$memberID;
				global $Employee_name,$Manager_name,$Employee_group;
				global $userfile,$File_name;
				global $GroupCode,$sel_year,$searchv;

				$Conversation_step2 = sprintf("%02d",$Conversation_step);	

				$path_is ="./../../../intranet_file/conversation";
				$path_is = $path_is."/".$Conversation_year;
				$path_is = $path_is."/".$Conversation_step2;
				$path =$path_is."/";
				
				$File_name= iconv("UTF-8", "EUC-KR",$File_name);				
				$orgfilename=$path.$File_name;
				//echo $orgfilename."<br>";

				$exist_org = file_exists($orgfilename);
				if($exist_org)
				{
					unlink($orgfilename);
				}
								
				$delsql ="delete from conversation_tbl where Conversation_year='$Conversation_year' and Conversation_step='$Conversation_step' and Employee_id='$Employee_id'";
				//echo $delsql;
				mysql_query($delsql,$db);

				$this->assign('target',"self");
				$this->assign('MoveURL',"conversation_controller.php?Conversation_year=$Conversation_year&Conversation_step=$Conversation_step&memberID=$memberID&GroupCode=$GroupCode&sel_year=$sel_year&searchv=$searchv");
				$this->display("intranet/move_page.tpl");

		}
		
		//============================================================================
		// 출력 화면
		//============================================================================
		function ConversationView()
		{
			
			global $db;
			global $Conversation_year,$Conversation_step;
			global $Employee_id,$Manager_id,$Upload_id,$memberID;
			global $Employee_name,$Manager_name,$Employee_group;
			global $userfile,$File_name;
			global $GroupCode,$sel_year,$searchv;
			global $Addfile;

			//echo "Addfile".$Addfile."<br>";

			$this->assign('file_src',$Addfile);
			//$this->assign('file_type',strtolower(array_pop(explode('.',$Addfile))));
			$this->display("intranet/common_contents/work_conversation/conversation_view_mvc.tpl");
		}




}


?>