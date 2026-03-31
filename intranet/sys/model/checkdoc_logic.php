<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	
	include "../../sys/inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../util/HanamcPageControl.php";
	include "../../../SmartyConfig.php";
	
	require_once($SmartyClassPath);

	//extract($_GET);

	if($CompanyKind==""){
		$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	}//if End

	class CheckDoc extends Smarty {
		
	
		function CheckDoc()
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
		// 회람지 작성표
		//============================================================================		
		function MakeDoc()
		{
			global $db,$memberID;
			global $title,$write_date,$end_date,$comment,$colnum,$rownum,$itype;

			
			if ($colnum=="") 
				$colnum=2;
			if ($rownum=="") 
				$rownum=1;
			if ($write_date=="") 
				$write_date=date("Y-m-d");
			if ($end_date=="") 
				$end_date="0000-00-00";
			if ($itype=="") 
				$itype="1";

			
			$this->assign('memberID',$memberID);
			$this->assign('title',$title);
			$this->assign('write_date',$write_date);
			$this->assign('end_date',$end_date);
			$this->assign('comment',$comment);
			$this->assign('colnum',$colnum);
			$this->assign('rownum',$rownum);
			$this->assign('itype',$itype);

			$this->display("intranet/common_contents/work_checkdoc/checkdoc_make.tpl");
		}

		//============================================================================
		// 회람지 작성표
		//============================================================================		
		function SaveDoc()
		{
			global $db,$memberID;
			global $title,$write_date,$end_date,$comment,$colnum,$rownum,$itype;
			global $coltitle;


			$sql="insert into checkdoc_tbl (title,comment,write_date,end_date,rowcount,itype,writer) values('$title','$comment','$write_date','$end_date','$rownum','$itype','$memberID')";
			echo $sql."<br>"; 
			mysql_query($sql,$db);

			for($i=0;$i<count($coltitle);$i++) {
				$title=$coltitle[$i];
				$sql2="insert into checkdoc_item_tbl (chk_no,col_no,title) values(last_insert_id(),'$i','$title')";
				//echo $sql2."<br>"; 
				mysql_query($sql2,$db);
			}



			/*
			$this->assign('target',"opener");
			$this->assign('MoveURL',"checkdoc_controller.php?ActionMode=list&memberID=$memberID");
			$this->display("intranet/move_page.tpl");
			*/
		}
		

		//============================================================================
		// 회람지 편집
		//============================================================================		
		function EditDoc()
		{
			global $db,$memberID;
			global $chk_no;
			global $colnum,$rownum,$itype;


			$sql="select * from checkdoc_tbl where chk_no ='$chk_no'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			if($re_num != 0) 
			{
				$title = mysql_result($re,0,"title");  
				$comment = mysql_result($re,0,"comment");  
				$write_date = mysql_result($re,0,"write_date");
				$end_date = mysql_result($re,0,"end_date");
				$rowcount = mysql_result($re,0,"rowcount");
				$itype2 = mysql_result($re,0,"itype");

			}


			$colnum2=0;
			
			$CheckList = array();
			$sql2="select * from checkdoc_item_tbl where chk_no = '$chk_no' order by col_no asc";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				
				array_push($CheckList,$re_row2);
				$colnum2++;
			}

			if ($rownum=="") 
				$this->assign('rownum',$rowcount);
			else
				$this->assign('rownum',$rownum);
			
			if ($colnum=="") 
				$this->assign('colnum',$colnum2);
			else
				$this->assign('colnum',$colnum);

			if ($itype=="") 
				$this->assign('itype',$itype2);
			else
				$this->assign('itype',$itype);



			$this->assign('memberID',$memberID);
			$this->assign('chk_no',$chk_no);
			$this->assign('title',$title);
			$this->assign('write_date',$write_date);
			$this->assign('end_date',$end_date);
			$this->assign('comment',$comment);
			$this->assign('CheckList',$CheckList);

			$this->assign('ActionMode',"edit");
			
			
			$this->display("intranet/common_contents/work_checkdoc/checkdoc_make.tpl");

		}


		//============================================================================
		// 회람지 편집저장
		//============================================================================		
		function UpdateDoc()
		{
			global $db,$memberID;
			global $chk_no;
			global $title,$write_date,$end_date,$comment,$colnum,$rownum,$itype;
			global $coltitle;


			$sql="update checkdoc_tbl set title='$title',comment='$comment',write_date='$write_date',end_date='$end_date',rowcount='$rownum',itype='$itype',writer='$memberID' where chk_no='$chk_no'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);




			$del="delete from checkdoc_item_tbl where chk_no='$chk_no'";
			mysql_query($del,$db);
			//echo $del."<br>"; 

			for($i=0;$i<count($coltitle);$i++) {
				$title=$coltitle[$i];
				$sql2="insert into checkdoc_item_tbl (chk_no,col_no,title) values('$chk_no','$i','$title')";
				//echo $sql2."<br>"; 
				mysql_query($sql2,$db);
			}


			
			//$this->assign('target',"opener");
			$this->assign('target',"self");
			$this->assign('MoveURL',"checkdoc_controller.php?ActionMode=list");
			$this->display("intranet/move_page.tpl");
			

			
		}


		//============================================================================
		// 회람지 보기
		//============================================================================		
		function ViewDoc()
		{
			global $db,$memberID;
			global $chk_no,$chk_id;

			//echo "memberID".$memberID."<br>"; 
			//echo "chk_id".$chk_id."<br>"; 

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$Admin=true;
				$this->assign('Admin',true);
			}else{
				$Admin=false;
				$this->assign('Admin',false);
			}


			$sql="select * from checkdoc_tbl where chk_no ='$chk_no'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			if($re_num != 0) 
			{
				$title = mysql_result($re,0,"title");  
				$comment = mysql_result($re,0,"comment");  
				$write_date = mysql_result($re,0,"write_date");
				$end_date = mysql_result($re,0,"end_date");
				$rowcount = mysql_result($re,0,"rowcount");
				$itype = mysql_result($re,0,"itype");
				
				$comment=ereg_replace("([A-Za-z0-9_-]+)@(([A-Za-z0-9-]+)\.)+([A-Za-z0-9-]+)","<a href=\"mailto:\\0\">\\0</a>",$comment); 
				$comment=ereg_replace("\n","<br>&nbsp;",$comment);
				$comment=ereg_replace("  ","&nbsp;&nbsp;",$comment);
			}

			$Today=date("Y-m-d");
			if ($end_date >=$Today)
			{	
				$Modify="YES";
			}else
			{
				if($Admin)
				{
					$Modify="YES";
				}else
				{
					$Modify="NO";
				}
			}

			$this->assign('Modify',$Modify);

			$Check_Row=0;
			
			$CheckList = array();
			$sql2="select * from checkdoc_item_tbl where chk_no = '$chk_no' order by col_no asc";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				array_push($CheckList,$re_row2);
				$Check_Row++;
			}

			$reply = array();
			$sql3="select * from checkdoc_answer_tbl where  chk_no = '$chk_no' and memberID='$chk_id'";
			//echo $sql3."<br>";
			$re3 = mysql_query($sql3,$db);
			while($re_row3 = mysql_fetch_array($re3))
			{
				$row_no=$re_row3[row_no];
				$col_no=$re_row3[col_no];

				$reply[$row_no][$col_no]=$re_row3[answer];

				//array_push($CheckList,$re_row2);
			}

			$this->assign('reply',$reply);


			$korName=MemberNo2Name($chk_id);
			$this->assign('korName',$korName);

			$this->assign('chk_no',$chk_no);
			$this->assign('chk_no',$chk_no);
			$this->assign('title',$title);
			$this->assign('comment',$comment);
			$this->assign('write_date',$write_date);
			$this->assign('end_date',$end_date);
			$this->assign('rowcount',$rowcount);
			$this->assign('itype',$itype);
			$this->assign('memberID',$memberID);
			$this->assign('chk_id',$chk_id);
			

			
			

			$this->assign('CheckList',$CheckList);
			$this->assign('Check_Row',$Check_Row);

			$this->display("intranet/common_contents/work_checkdoc/checkdoc_view.tpl");
		}



		//============================================================================
		// 회람지 답변
		//============================================================================		
		function AnswerDoc()
		{
			global $db,$memberID;
			global $chk_no,$col_count,$row_count,$chk_id;
			global $reply,$itype;

			$del="delete from checkdoc_answer_tbl where chk_no='$chk_no' and memberID='$chk_id'";
			mysql_query($del,$db);
			//echo $del."<br>"; 

			//echo "chk_no".$chk_no."<br>";

			$GCode=MemberNo2Group($chk_id);

			for($i=0;$i<$row_count;$i++) {
				for($j=0;$j<$col_count;$j++) {

					$answer=$reply[$i][$j];


						if($itype==1 && $answer=="") //텍스트일경우 빈답변 처리
						{

						}else
						{
							$sql="insert into checkdoc_answer_tbl (chk_no,col_no,row_no,GroupCode,memberID,answer) values('$chk_no','$j','$i','$GCode','$chk_id','$answer')";
							//echo $sql."<br>"; 
							mysql_query($sql,$db);
						}

				}
			}

			//메인팝업
			//$this->assign('target',"no");
			//$this->display("intranet/move_page.tpl");
			
			//질문지에서 답변시
			$this->assign('target',"edu");
			$this->display("intranet/move_page.tpl");



		}
		

		//============================================================================
		// 회람지 리스트
		//============================================================================		
		function ListDoc()
		{
			global $db,$memberID;
			

			
			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$this->assign('Admin',true);
			}else{
				$this->assign('Admin',false);
			}

			if($PersonAuthority->GetInfo($memberID,'부서')){
				$this->assign('PartAuth',true);
			}else{
				$this->assign('PartAuth',false);
			}

			if($PersonAuthority->GetInfo($memberID,'주무')){
				$this->assign('PartAuth',true);
			}else{
				$this->assign('PartAuth',false);
			}

			$GCode=MemberNo2Group($memberID);

			$Today=date("Y-m-d");
			$this->assign('Today',$Today);


			$CheckList = array();
			$sql="select * from checkdoc_tbl order by chk_no desc";
			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($CheckList,$re_row);
			}
			
			/* 팝업 체크 부분 ---------------------------------------------------------------- */
			$Today=date("Y-m-d");
			$sql2="select * from checkdoc_tbl where end_date >='$Today'";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{

				$chk_no=$re_row2[chk_no];
				$sql3="select * from checkdoc_answer_tbl where  chk_no = '$chk_no' and memberID='$memberID'";
				//echo $sql3."<br>";
				$re3 = mysql_query($sql3,$db);
				$re_num3 = mysql_num_rows($re3);
							if ($re_num3 ==0)
				{?>
					<script>
						window.open("checkdoc_controller.php?ActionMode=view&chk_no=<?=$chk_no?>&memberID=<?=$memberID?>&chk_id=<?=$memberID?>","<?=$chk_no?>","width=800,height=750,scrollbars=yes,status=no,location=no,directories=no,toolbar=no,menubar=no,left=10,top=10,resizable=yes");
					</script>
				<?}
				
			 }

			$this->assign('memberID',$memberID);
			$this->assign('GroupCode',$GCode);
			$this->assign('CheckList',$CheckList);
			$this->display("intranet/common_contents/work_checkdoc/checkdoc_list.tpl");
		}

		//============================================================================
		// 회람지 통계
		//============================================================================		
		function ReportDoc()
		{
			global $db,$memberID;
			global $chk_no;
			global $excel;
			

			//$chk_no="2";

			$sql="select * from checkdoc_tbl where chk_no ='$chk_no'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			if($re_num != 0) 
			{
				$title = mysql_result($re,0,"title");  
				$comment = mysql_result($re,0,"comment");  
				$write_date = mysql_result($re,0,"write_date");
				$end_date = mysql_result($re,0,"end_date");
				$rowcount = mysql_result($re,0,"rowcount");
				$itype = mysql_result($re,0,"itype");

			}

			$colcount=0;
			
			$CheckList = array();
			$sql2="select * from checkdoc_item_tbl where chk_no = '$chk_no' order by col_no asc";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				
				array_push($CheckList,$re_row2);
				$colcount++;
			}


			$GroupList = array();
			$col_sum = array();
			
			if($CompanyKind=="JANG")
				$gsql_sub="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'07' and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="PILE")
				$gsql_sub="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="HANM")
				$gsql_sub="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' and Code<>'28' order by orderno  asc";
			else
				$gsql_sub="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";


			
			$gsql="select * from 
			(".$gsql_sub."

			)a left join 
			(
				select GroupCode,count(*) cnt from member_tbl where  workposition='1'  group by GroupCode
			)b on a.Code=b.GroupCode";

			//echo $gsql."<br>";

			$reg = mysql_query($gsql,$db);
			while($reg_row = mysql_fetch_array($reg))
			{
				
				$GroupCode=$reg_row[Code];
				
				
				$sql3="select * from checkdoc_answer_tbl where chk_no = '$chk_no' and GroupCode='$GroupCode' order by row_no,col_no asc";
				//echo $sql3."<br>";
				$re3 = mysql_query($sql3,$db);
				while($re_row3 = mysql_fetch_array($re3))
				{
					$row_no=$re_row3[row_no];
					$col_no=$re_row3[col_no];
					$answer=$re_row3[answer];
					
					for($i=0;$i<$colcount;$i++) {
						if($col_no==$i)
						{
											
							if($itype=="3") //선택박스
							{
								if($answer=="Y")
								{
									$col[$i]++;
									$col_sum[$i]++;
								}
							}else if($itype=="2") //체크박스
							{
								if($answer=="on")
								{
									$col[$i]++;
									$col_sum[$i]++;
								}
							}else if($itype=="1") //텍스트박스
							{
									$col[$i]++;
									$col_sum[$i]++;
							}

						}
					}

				}


				for($i=0;$i<$colcount;$i++) {
					$reg_row[$i]=$col[$i];
					$col[$i]="";
				}
			
				array_push($GroupList,$reg_row);
	
			}
			

			$this->assign('col_sum',$col_sum);

			$this->assign('GroupCode',$GroupList);
			$this->assign('GroupList',$GroupList);

			$AnswerList = array();
			$sql3="select memberID,count(*) cnt from checkdoc_answer_tbl where chk_no = '$chk_no' group by memberID";
			//echo $sql3."<br>";
			$re3 = mysql_query($sql3,$db);
			while($re_row3 = mysql_fetch_array($re3))
			{	$id=$re_row3[memberID];
				$cnt=$re_row3[cnt];


				$sql4="select * from checkdoc_answer_tbl where chk_no = '$chk_no' and memberID='$id' order by row_no,col_no asc";
				//echo $sql4."<br>";
				$re4 = mysql_query($sql4,$db);
				while($re_row4 = mysql_fetch_array($re4))
				{

					$re_row4[cnt]=$cnt/$colcount;
					array_push($AnswerList,$re_row4);
				}


			}


			$this->assign('memberID',$memberID);
			$this->assign('chk_no',$chk_no);
			$this->assign('title',$title);
			$this->assign('comment',$comment);
			$this->assign('write_date',$write_date);
			$this->assign('end_date',$end_date);
			$this->assign('rowcount',$rowcount);
			$this->assign('colcount',$colcount);

			$this->assign('excel',$excel);
			$this->assign('CheckList',$CheckList);
			$this->assign('AnswerList',$AnswerList);


			
			$this->display("intranet/common_contents/work_checkdoc/checkdoc_report.tpl");
		}
		


		//============================================================================
		// 그룹별 회람지 답변
		//============================================================================		
		function GroupDoc()
		{
			global $db,$memberID,$CompanyKind,$GroupCode;
			global $chk_no;
			
			//echo "memberID".$memberID."<br>";

			$PersonAuthority = new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'총무')){
				$this->assign('Admin',true);
			}else{
				$this->assign('Admin',false);
			}

			if($PersonAuthority->GetInfo($memberID,'부서')){
				$this->assign('PartAuth',true);
			}else{
				$this->assign('PartAuth',false);
			}

			$Today=date("Y-m-d");
			$this->assign('Today',$Today);

			$GroupList = array();

			if($CompanyKind=="JANG")
				$gsql="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'07' and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="PILE")
				$gsql="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="HANM")
				$gsql="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' and Code<>'28' order by orderno  asc";
			else
				$gsql="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";

			//echo $gsql."<br>";
			$reg = mysql_query($gsql,$db);
			while($reg_row = mysql_fetch_array($reg))
			{
				if($reg_row[Code]==$GroupCode)
				{
					$this->assign('GroupName',$reg_row[Name]);
				}
				array_push($GroupList,$reg_row);
				
			}
			
			$this->assign('GroupCode',$GroupCode);
			$this->assign('GroupList',$GroupList);



			$sql="select * from checkdoc_tbl where chk_no ='$chk_no'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			if($re_num != 0) 
			{
				$title = mysql_result($re,0,"title");  
				$comment = mysql_result($re,0,"comment");  
				$write_date = mysql_result($re,0,"write_date");
				$end_date = mysql_result($re,0,"end_date");
				$rowcount = mysql_result($re,0,"rowcount");
			}


			

			
			


			$colcount=0;
			
			$CheckList = array();
			$sql2="select * from checkdoc_item_tbl where chk_no = '$chk_no' order by col_no asc";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				
				array_push($CheckList,$re_row2);
				$colcount++;
			}

			$AnswerList = array();
			$sql3="select a.MemberNo,b.cnt,a.Name,a.korName from 
				(
					select *  from
					(
						select * from member_tbl where groupcode='$GroupCode' and workposition='1'  order by rankcode
					)a2 left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)b2 on a2.RankCode = b2.code
				)a left join
				(
					select memberID,count(*) as cnt from checkdoc_answer_tbl where chk_no = '$chk_no'  group by memberID
				)b on a.memberNo=b.memberID";
			//echo $sql3."<br>";
			$re3 = mysql_query($sql3,$db);
			while($re_row3 = mysql_fetch_array($re3))
			{	
				$id=$re_row3[MemberNo];
				$cnt=$re_row3[cnt];
				$RankName=$re_row3[Name];
				$korName=$re_row3[korName];

				
				$sql4="select * from checkdoc_answer_tbl where chk_no = '$chk_no' and memberID='$id' order by row_no,col_no asc";
				//echo $sql4."<br>";
				$re4 = mysql_query($sql4,$db);
				$cnt = mysql_num_rows($re4);
				if($cnt>0)
				{
					while($re_row4 = mysql_fetch_array($re4))
					{	
						$re_row4[cnt]=$cnt/$colcount;
						$re_row4[RankName]=$RankName;
						$re_row4[korName]=$korName;
						
						
						array_push($AnswerList,$re_row4);
					}
				}else
				{	

					$re_row4[memberID]=$id;
					$re_row4[RankName]=$RankName;
					$re_row4[korName]=$korName;
					$re_row4[reply]="no";
					array_push($AnswerList,$re_row4);
				}

			}

			$this->assign('memberID',$memberID);
			$this->assign('AdminID',$memberID);
			$this->assign('chk_no',$chk_no);
			$this->assign('title',$title);
			$this->assign('comment',$comment);
			$this->assign('write_date',$write_date);
			$this->assign('end_date',$end_date);
			$this->assign('rowcount',$rowcount);
			$this->assign('colcount',$colcount);


			$this->assign('CheckList',$CheckList);
			$this->assign('AnswerList',$AnswerList);


			
			$this->display("intranet/common_contents/work_checkdoc/checkdoc_group.tpl");
		}


		
		//============================================================================
		// 회람지 삭제
		//============================================================================		
		function DeleteDoc()
		{
			global $db,$memberID;
			global $chk_no,$col_count,$row_count;
			global $reply,$itype;

			$del="delete from checkdoc_tbl  where chk_no='$chk_no'";
			mysql_query($del,$db);
			//echo $del."<br>"; 

			$del2="delete from checkdoc_item_tbl where chk_no='$chk_no'";
			mysql_query($del2,$db);
			//echo $del2."<br>"; 

			$del3="delete from checkdoc_answer_tbl where chk_no='$chk_no' and memberID='$memberID'";
			mysql_query($del3,$db);
			//echo $del3."<br>"; 


			$this->assign('target',"self");
			$this->assign('MoveURL',"checkdoc_controller.php?ActionMode=list");
			$this->display("intranet/move_page.tpl");
		}



}


?>