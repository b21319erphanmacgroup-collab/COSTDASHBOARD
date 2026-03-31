<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

	/***************************************
	* 권한설정
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";


	extract($_POST);
	class AuthLogic {
		var $smarty;
		function AuthLogic($smarty)
		{
			$this->smarty=$smarty;
		}


		//============================================================================
		// 개인별 설정보기
		//============================================================================
		function UpdateReadPage()
		{
			include "../inc/approval_function.php";
			global $db,$memberID,$SearchID;

			$MemberName=MemberNo2Name($SearchID);


			$sql="select * from member_tbl where MemberNo='$SearchID'";

			$re = mysql_query($sql,$db);
			$Certificate = mysql_result($re,0,"Certificate");

			if($Certificate) {

				if(strpos($Certificate,"인사A")  !== false) { $Person_auth=true; }
				if(strpos($Certificate,"인사B") !== false) { $Person2_auth=true; }

				if(strpos($Certificate,"업무A")  !== false) { $Project_auth=true; }
				if(strpos($Certificate,"업무B") !== false) { $Project2_auth=true; }

				if(strpos($Certificate,"경리A")  !== false) { $Account_auth=true; }
				if(strpos($Certificate,"경리B") !== false) { $Account2_auth=true; }

				if(strpos($Certificate,"총괄A") !== false) { $planning_auth=true; }

				if(strpos($Certificate,"노무A")  !== false) { $Labor_auth=true; }
				if(strpos($Certificate,"노무B") !== false) { $Labor2_auth=true; }

				if(strpos($Certificate,"주무") !== false) { $Part_auth=true; }

				if(strpos($Certificate,"총무") !== false) { $Manager_auth=true; }
				if(strpos($Certificate,"부서") !== false) { $Group_auth=true; }
				if(strpos($Certificate,"임원회의") !== false) { $DirectorMeeting_auth=true; }
				if(strpos($Certificate,"영업회의") !== false) { $SalesMeeting_auth=true; }
				if(strpos($Certificate,"일반회의") !== false) { $Meeting_auth=true; }
				if(strpos($Certificate,"지적") !== false) { $Intell_auth=true; }
				if(strpos($Certificate,"임원") !== false) { $Ceo_auth=true; }

				if(strpos($Certificate,"사업") !== false) { $Business_auth=true; }
				if(strpos($Certificate,"입찰") !== false) { $BidManager_auth=true; }
				if(strpos($Certificate,"협조") !== false) { $Cooper_auth=true; }
				if(strpos($Certificate,"설정") !== false) { $Setting_auth=true; }

				if(strpos($Certificate,"한맥") !== false) { $Hanmac_auth=true; }
				if(strpos($Certificate,"장헌") !== false) { $Jangheon_auth=true; }
				if(strpos($Certificate,"PTC") !== false) { $Ptc_auth=true; }

				if(strpos($Certificate,"실행") !== false) { $Runbudget_auth=true; }

				if(strpos($Certificate,"대화대표") !== false) { $ConverCeo_auth=true; }
				if(strpos($Certificate,"대화부서") !== false) { $ConverPart_auth=true; }
				if(strpos($Certificate,"팀장") !== false) { $Team_auth=true; }

				if(strpos($Certificate,"공간정보") !== false) { $auth_spatial=true; }

				if(strpos($Certificate,"조직") !== false) { $ogarnization_auth=true; }

				if(strpos($Certificate,"센터") !== false) { $center_auth=true; }

				if(strpos($Certificate,"EIS") !== false) { $eis_auth=true; }

			}
				$this->smarty->assign('MemberName',$MemberName);
				$this->smarty->assign('SearchID',$SearchID);
				$this->smarty->assign('Person_auth',$Person_auth);
				$this->smarty->assign('Person2_auth',$Person2_auth);
				$this->smarty->assign('Project_auth',$Project_auth);
				$this->smarty->assign('Project2_auth',$Project2_auth);
				$this->smarty->assign('Account_auth',$Account_auth);
				$this->smarty->assign('Account2_auth',$Account2_auth);
				$this->smarty->assign('planning_auth',$planning_auth);
				$this->smarty->assign('Labor_auth',$Labor_auth);
				$this->smarty->assign('Labor2_auth',$Labor2_auth);
				$this->smarty->assign('Manager_auth',$Manager_auth);
				$this->smarty->assign('Group_auth',$Group_auth);
				$this->smarty->assign('DirectorMeeting_auth',$DirectorMeeting_auth);
				$this->smarty->assign('SalesMeeting_auth',$SalesMeeting_auth);
				$this->smarty->assign('Meeting_auth',$Meeting_auth);
				$this->smarty->assign('Intell_auth',$Intell_auth);
				$this->smarty->assign('Ceo_auth',$Ceo_auth);
				$this->smarty->assign('Business_auth',$Business_auth);
				$this->smarty->assign('BidManager_auth',$BidManager_auth);
				$this->smarty->assign('Cooper_auth',$Cooper_auth);
				$this->smarty->assign('Setting_auth',$Setting_auth);
				$this->smarty->assign('Part_auth',$Part_auth);
				$this->smarty->assign('Runbudget_auth',$Runbudget_auth);

				$this->smarty->assign('Hanmac_auth',$Hanmac_auth);
				$this->smarty->assign('Jangheon_auth',$Jangheon_auth);
				$this->smarty->assign('Ptc_auth',$Ptc_auth);

				$this->smarty->assign('ConverCeo_auth',$ConverCeo_auth);
				$this->smarty->assign('ConverPart_auth',$ConverPart_auth);
				$this->smarty->assign('Team_auth',$Team_auth);

				$this->smarty->assign('auth_spatial',$auth_spatial);

				$this->smarty->assign('ogarnization_auth',$ogarnization_auth);

				$this->smarty->assign('center_auth',$center_auth);

				$this->smarty->assign('eis_auth',$eis_auth);

				$this->smarty->display("intranet/common_contents/work_auth/auth_detail.tpl");

		}

		//============================================================================
		// 개인별 설정저장
		//============================================================================
		function UpdateAction()
		{

			global $db,$memberID,$SearchID;
			global $cb,$cbv;

			for($i = 0;$i<=30;$i++)
			{
				if($cb[$i] == "on")
				{
					$Certificate = $Certificate.$cbv[$i].",";
				}

			}

			$sql = "update member_tbl set Certificate='$Certificate' where MemberNo ='$SearchID'";echo $sql;

			$re = mysql_query($sql,$db);

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"auth_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");

		}

		//============================================================================
		// 전체 사용자 설정 보기
		//============================================================================
		function View()
		{

			global $db,$memberID;


			$query_data = array();

			$sql= "      SELECT																		";
			$sql= $sql."	 a.MemberNo			as a_MemberNo									";	//사원번호
			$sql= $sql."	,a.korName			as a_korName									";	//한글이름
			$sql= $sql."	,a.Certificate		as a_Certificate								";	//권한
			$sql= $sql."	,a.Name				as a_position									";	//직위
			$sql= $sql."	,b.Name				as a_GroupName									";	//부서명
			$sql= $sql." FROM																	";
			$sql= $sql." (                                                                 		";
			$sql= $sql." 	select * from                                                 		";
			$sql= $sql." 	( select * from member_tbl where WorkPosition = 1 order by GroupCode,RankCode,MemberNo )a1     ";
			$sql= $sql." 	 left JOIN                                                 		    ";
			$sql= $sql." 	( select * from systemconfig_tbl where SysKey='PositionCode' )a2	";
			$sql= $sql." 	 on a1.RankCode = a2.code                                  		    ";
			$sql= $sql." ) a left JOIN                                                     		";
			$sql= $sql." ( select * from systemconfig_tbl where SysKey='GroupCode'  )b          ";
			$sql= $sql."  on a.GroupCode = b.code												";

			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}


			$this->smarty->assign('query_data',$query_data);
			$this->smarty->display("intranet/common_contents/work_auth/auth_mvc.tpl");


		}



}
?>