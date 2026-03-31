
<?php

	/***************************************
	*
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/
	session_start();
	if($_GET['memberID'] <> "")
	{
		$memberID = $_GET['memberID'];
	}
	if($_SESSION['memberID']=="")
	{
		$_SESSION['memberID']=$memberID;
	}else{
		$memberID=$_SESSION['memberID'];
	}

	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();


	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../inc/function_intranet.php";

	extract($_GET);

	if($CompanyKind==""){
		$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	}//i

	class AbsentController {
		var $smarty;
		var $year;
		var $start_month;
		var $mobile;
		var $QueryDay;
		function AbsentController($smarty)
		{
			$this->smarty=$smarty;
			$ActionMode=$_GET['ActionMode'];
			$this->mobile=$_GET['mobile'];
			$this->smarty->assign('ActionMode',$ActionMode);
			$this->smarty->assign('mobile',$this->mobile);

		}
		function SetAbsent()
		{
			global $db,$memberID,$SearchID;
			$absent=$_GET['absent'];
			$comment=$_GET['comment'];
			$userid	=   $_SESSION['memberID'];		//사원번호

			if($userid != "")
			{
				$sql="select * from member_absent_tbl  where MemberNo='".$userid."'";
				$re = mysql_query($sql,$db);
				if($re_row = mysql_fetch_array($re))
				{
					$sql="update member_absent_tbl  set absent=".$absent." , comment='".$comment."' , InputDate=now() where MemberNo='".$userid."'";
				}
				else
				{
						$sql="insert into member_absent_tbl  (absent,comment,MemberNo,InputDate) values(".$absent." , '".$comment."','".$userid."',now() )";

				}

				//echo $sql;
				mysql_query($sql,$db);

			}
			$this->smarty->assign('Message',"저장이 완료되었습니다");
			$this->View();
		}


		function SetAbsent2()
		{
			extract($_REQUEST);
			global $db,$memberID,$SearchID;
			$userid	=  $userid==""? $_SESSION['memberID']:$userid;		//사원번호

			//absent="+absent+"&comment="+comment+"&userid="+userid;

			if($userid != "")
			{
				$sql="select * from member_absent_tbl  where MemberNo='".$userid."'";
				$re = mysql_query($sql,$db);
				if($re_row = mysql_fetch_array($re))
				{
					$sql="update member_absent_tbl  set absent='".$absent."', InputDate=now() , comment='".$comment."' where MemberNo='".$userid."'";
				}
				else
				{
						$sql="insert into member_absent_tbl  (absent,comment,MemberNo,InputDate) values('".$absent."' , '".$comment."','".$userid."',now())";

				}
				mysql_query($sql,$db);


				$cfile="../log/".date("Y-m-d")."_setAbsent.txt";
				$exist = file_exists($cfile);
				/* ----------------------------------------------- */
				if($exist) {
					$fd=fopen($cfile,'r');
					$con=fread($fd,filesize($cfile));
					fclose($fd);
				}
				/* ----------------------------------------------- */
				$fp=fopen($cfile,'w');
				$aa=date("Y-m-d H:i");
				/* ---------------------- */
				$ip=$_SERVER['REMOTE_ADDR'];
				/* ---------------------- */
				$username = $korName;
				$cond=$con.$aa." ".$get_value01."[".$user_ip."]".$sql."] \n";
				fwrite($fp,$cond);
				fclose($fp);
				/* ---------------------- */


				//echo $sql."<br>";
			}
			$this->ShowList2();
		}


		function ShowList()
		{
			global $db,$SearchID;
			$query_data = array();
			$memberID	=   $_SESSION['memberID'];		//사원번호

			$sql = "select a11.*,a12.absent,a12.absent_desc,a12.comment from ";
			$sql= $sql." (";
			$sql=$sql." select a.*,b.Name as GroupName,a.Name as Title		 ";
			$sql= $sql." from                                                                   ";
			$sql= $sql."	(                                                                    ";
			$sql= $sql."		select * from                                                    ";
			$sql= $sql."		(                                                                ";
			$sql= $sql."			select * from member_tbl  where workposition=1 order by  groupcode,RankCode";
			$sql= $sql."		)a1 left JOIN                                                    ";
			$sql= $sql."		(                                                                ";
			$sql= $sql."			select * from systemconfig_tbl where SysKey='PositionCode'   ";
			$sql= $sql."		)a2 on a1.RankCode = a2.code                                     ";
			$sql= $sql."	                                                                     ";
			$sql= $sql."	) a left JOIN                                                        ";
			$sql= $sql."	(                                                                    ";
			$sql= $sql."		select * from systemconfig_tbl where SysKey='GroupCode'          ";
			$sql= $sql."	)b on a.GroupCode = b.code	";
			$sql= $sql."	) a11 left join ";
			$sql= $sql."	(
						select b11.*,b12.name as absent_desc from
										(
											select * from member_absent_tbl
										)b11 left join
										(
											select code,name from systemconfig_tbl where SysKey='AbsentCode'
										)b12 on b11.absent=b12.code)";
			$sql = $sql."	 a12 on a11.MemberNO=a12.MemberNo ";
			//echo $sql;
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}
			$this->smarty->assign('list_data01',$query_data);
			$this->smarty->display("intranet/absent_list.tpl");
		}


		function ShowList2()
		{
			global $db,$SearchID;
			global $CompanyKind,$sub_index,$GroupCode;
			$query_data = array();
			$memberID	=   $_SESSION['memberID'];		//사원번호


			$query_data3 = array();
			$absent=0;
			//$sql3 =  " select  code, name as comment from systemconfig_tbl where SysKey='AbsentCode' and Code < 4";
			$sql3 =  " select  code, name from systemconfig_tbl where SysKey='AbsentCode' and Code < 4";
			$re3 = mysql_query($sql3,$db);
			while($re_row3 = mysql_fetch_array($re3))
				array_push($query_data3,$re_row3);
			$this->smarty->assign('query_data3',$query_data3);


			$query_data44 = array();
			$sql44 =  " select  code, name from systemconfig_tbl where SysKey='AbsentCode'";
			$re44 = mysql_query($sql44,$db);
			while($re_row44 = mysql_fetch_array($re44)){
				array_push($query_data44,$re_row44);
			}
				//print_r($query_data4);
				$this->smarty->assign('query_data4',$query_data44);




			$sql4 =  " select a11.MemberNo,a12.absent,a12.comment	";
			$sql4 = $sql4." from                                                                   ";
			$sql4 = $sql4."	(                                                                    ";
			$sql4 = $sql4."		select * from   member_tbl where MemberNo='".$memberID."' ";
			$sql4 = $sql4."	) a11 left join";
			$sql4 = $sql4."	(                                                                    ";
			$sql4 = $sql4."	    select * from member_absent_tbl   ";
			$sql4 = $sql4."	) a12 on a11.MemberNo=a12.MemberNo";

			//echo $sql4;

			$re4 = mysql_query($sql4,$db);
			if($re_row4 = mysql_fetch_array($re4))
			{
				$this->smarty->assign('absentinfo',$re_row4);
			}



			if($GroupCode=="")
				$GroupCode=$_SESSION['MyGroupCode'];

			if($sub_index == "")
				$sub_index=$GroupCode;

			$tab_index=$GroupCode;

			$GroupList = array();

			$Group_Row="0";
			//if($_COOKIE['CK_CompanyKind']=="JANG")
			if($CompanyKind=="JANG")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'07' and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="PILE")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="HANM")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code  not in ( '99','28','50','42','43' )  order by orderno  asc";
			else
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code  not in ( '99','28','50','42','43' )  order by orderno  asc";

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

			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('gCode',$gCode);
			$this->smarty->assign('gName',$gName);
			$this->smarty->assign('Group_Row',$Group_Row);
			$this->smarty->assign('GroupList',$GroupList);


			//echo $GroupCode;

			$sql = "select a11.*,a12.absent,a12.absent_desc,a12.comment from ";
			$sql= $sql." (";
			$sql=$sql." select a.*,b.Name as GroupName,a.Name as Title		 ";
			$sql= $sql." from                                                                   ";
			$sql= $sql."	(                                                                    ";
			$sql= $sql."		select * from                                                    ";
			$sql= $sql."		(                                                                ";
			if($GroupCode=="31")
			{
					$sql= $sql."			select * from member_tbl  where workposition in(1,'4') and GroupCode='".$GroupCode."'order by  groupcode,IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName)";
			}else
			{
				$sql= $sql."			select * from member_tbl  where workposition=1 and GroupCode='".$GroupCode."'order by  groupcode,IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName)";
			}


			$sql= $sql."		)a1 left JOIN                                                    ";
			$sql= $sql."		(                                                                ";
			$sql= $sql."			select * from systemconfig_tbl where SysKey='PositionCode'   ";
			$sql= $sql."		)a2 on a1.RankCode = a2.code                                     ";
			$sql= $sql."	                                                                     ";
			$sql= $sql."	) a left JOIN                                                        ";
			$sql= $sql."	(                                                                    ";
			$sql= $sql."		select * from systemconfig_tbl where SysKey='GroupCode'          ";
			$sql= $sql."	)b on a.GroupCode = b.code	";
			$sql= $sql."	) a11 left join ";
			$sql= $sql."	(
						select b11.*,b12.name as absent_desc from
										(
											select * from member_absent_tbl
										)b11 left join
										(
											select code,name from systemconfig_tbl where SysKey='AbsentCode'
										)b12 on b11.absent=b12.code)";
			$sql = $sql."	 a12 on a11.MemberNO=a12.MemberNo ";
			//일반합사, 경쟁합사 제외 220729-정명준  //221026 다시포함-김윤하
			
			/*
			$sql= $sql." where (a12.absent not in ('20','21') or a12.absent is null)
						 and (a11.office_type not in ('1','3') or a11.office_type is null)";
			*/
			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				if(strstr($re_row["comment"],"보건") != ""){
					$re_row["comment"] = "개인사정";
				}
				array_push($query_data,$re_row);
			}
			$this->smarty->assign('list_data01',$query_data);
			//$this->smarty->display("intranet/absent_list2.tpl");
			$this->smarty->display("intranet/common_contents/work_absence/absence_list_mvc.tpl");
		}

		function ShowListCom()
		{
			global $db,$SearchID;
			global $CompanyKind,$sub_index,$GroupCode;
			$query_data = array();


			if($GroupCode=="")
				$GroupCode="11";

			if($sub_index == "")
				$sub_index=$GroupCode;

			$tab_index=$GroupCode;

			$GroupList = array();

			$Group_Row="0";


			$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code  not in ( '99','28','98','50','42','43' )  order by orderno  asc";
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

			$this->smarty->assign('tab_index',$tab_index);
			$this->smarty->assign('sub_index',$sub_index);
			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('gCode',$gCode);
			$this->smarty->assign('gName',$gName);
			$this->smarty->assign('Group_Row',$Group_Row);
			$this->smarty->assign('GroupList',$GroupList);


			$sql = "select a11.*,a12.absent,a12.absent_desc,a12.comment from ";
			$sql= $sql." (";
			$sql=$sql." select a.*,b.Name as GroupName,a.Name as Title		 ";
			$sql= $sql." from                                                                   ";
			$sql= $sql."	(                                                                    ";
			$sql= $sql."		select * from                                                    ";
			$sql= $sql."		(                                                                ";
			$sql= $sql."			select * from member_tbl  where workposition=1 and GroupCode='".$GroupCode."'order by  groupcode,RankCode,order_index,birthday";
			$sql= $sql."		)a1 left JOIN                                                    ";
			$sql= $sql."		(                                                                ";
			$sql= $sql."			select * from systemconfig_tbl where SysKey='PositionCode'   ";
			$sql= $sql."		)a2 on a1.RankCode = a2.code                                     ";
			$sql= $sql."	                                                                     ";
			$sql= $sql."	) a left JOIN                                                        ";
			$sql= $sql."	(                                                                    ";
			$sql= $sql."		select * from systemconfig_tbl where SysKey='GroupCode'          ";
			$sql= $sql."	)b on a.GroupCode = b.code	";
			$sql= $sql."	) a11 left join ";
			$sql= $sql."	(
						select b11.*,b12.name as absent_desc from
										(
											select * from member_absent_tbl
										)b11 left join
										(
											select code,name from systemconfig_tbl where SysKey='AbsentCode'
										)b12 on b11.absent=b12.code)";
			$sql = $sql."	 a12 on a11.MemberNO=a12.MemberNo ";

		//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}
			$this->smarty->assign('list_data01',$query_data);
			//$this->smarty->display("intranet/absent_list2.tpl");
			$this->smarty->display("intranet/common_contents/work_absence/absence_list_com_mvc.tpl");
		}

		function View()
		{
			global $db,$SearchID;
			$memberID	=   $_SESSION['memberID'];		//사원번호

			$query_data = array();
			$absent=0;
			$sql =  " select  code, name as comment from systemconfig_tbl where SysKey='AbsentCode' and Code < 4";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
				array_push($query_data,$re_row);

			$this->smarty->assign('list_data01',$query_data);

			$sql =  " select a11.MemberNo,a12.absent,a12.comment	";
			$sql = $sql." from                                                                   ";
			$sql = $sql."	(                                                                    ";
			$sql = $sql."		select * from   member_tbl where MemberNo='".$memberID."' ";
			$sql = $sql."	) a11 left join";
			$sql = $sql."	(                                                                    ";
			$sql = $sql."	    select * from member_absent_tbl   ";
			$sql = $sql."	) a12 on a11.MemberNo=a12.MemberNo";


			$re = mysql_query($sql,$db);
			if($re_row = mysql_fetch_array($re))
			{
				$this->smarty->assign('absentinfo',$re_row);
			}


			$this->smarty->display("intranet/absent.tpl");

		}
		function GroupDetailView()
		{
			global $db,$memberID,$SearchID;

			$groupcode=$_GET['groupcode'];
			$query_data = array();
			$sql =      " select a.*,b.Name as GroupName,a.Name as Title		 ";
			$sql = $sql." from                                                                   ";
			$sql = $sql."	(                                                                    ";
			$sql = $sql."		select * from                                                    ";
			$sql = $sql."		(                                                                ";
			$sql = $sql."			select * from member_tbl where GroupCode='".$groupcode."'  order by  RankCode";
			$sql = $sql."		)a1 left JOIN                                                    ";
			$sql = $sql."		(                                                                ";
			$sql = $sql."			select * from systemconfig_tbl where SysKey='PositionCode'   ";
			$sql = $sql."		)a2 on a1.RankCode = a2.code                                     ";
			$sql = $sql."	                                                                     ";
			$sql = $sql."	) a left JOIN                                                        ";
			$sql = $sql."	(                                                                    ";
			$sql = $sql."		select * from systemconfig_tbl where SysKey='GroupCode'          ";
			$sql = $sql."	)b on a.GroupCode = b.code	";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				$filepath="c:\\APM_SETUP\\htdocs\\erpphoto\\".$re_row[MemberNo].".JPG";
				if(file_exists($filepath))
					$re_row[photo]=$re_row[MemberNo].".JPG";
				else
					$re_row[photo]="default.png";

				array_push($query_data,$re_row);
			}
			$this->smarty->assign('list_data01',$query_data);
			$this->smarty->display("mobile/telephone/groupview.tpl");
		}
		//============================================================================
		// 부서리스트
		//============================================================================
		function GroupList()
		{
			global $db,$memberID,$SearchID;

			$query_data = array();
			$sql="select * from systemconfig_tbl where SysKey='GroupCode'  order by code";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}
			$this->smarty->assign('list_data01',$query_data);
			$this->smarty->display("mobile/telephone/grouplist.tpl");

		}


		function bear3StrCut($str,$len,$tail="..."){
			$rtn = array();
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
		}

}



