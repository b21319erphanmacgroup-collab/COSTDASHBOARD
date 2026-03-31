<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

	/***************************************
	* 투입인건비
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/

	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";


	extract($_GET);
	class LaborCostLogic {
		var $smarty;
		function LaborCostLogic($smarty)
		{
			$this->smarty=$smarty;
		}



		function Loading()
		{
			global $db,$memberID,$GroupCode;
			global $sel_year,$sel_month;

			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('sel_year',$sel_year);
			$this->smarty->assign('sel_month',$sel_month);

			$this->smarty->display("intranet/common_contents/work_laborcost/laborcost_loading.tpl");
		}

		//============================================================================
		// 부서별 투입인건비 보기
		//============================================================================
		function View()
		{

			global $db,$memberID,$GroupCode;
			global $sel_year,$sel_month;

			$unit=1000;

			$uyear = date("Y")+1;
			if($sel_year==""){$sel_year=date("Y");}
			if($sel_month==""){$sel_month=date("m");}

			$this->smarty->assign('uyear',$uyear);
			$this->smarty->assign('sel_year',$sel_year);
			$this->smarty->assign('sel_month',$sel_month);


			$start_tmp=$sel_year."-".sprintf("%02d",$sel_month)."-01";
			$end_tmp=$sel_year."-".sprintf("%02d",$sel_month)."-31";


			$query_data = array();

			$sql="select a.ProjectCode,a.ProjectNickname,a.ContractStart,a.ContractEnd,a.Outside,b.labor_cost from
			(
				select ProjectCode,ProjectNickname,ContractStart,ContractEnd,Outside from project_tbl where MainGroup='$GroupCode' and WorkStatus='수행중' and ContractDate>'1990-01-01' order by ProjectCode
			)a left join
			(
				select * from project_budget_tbl
			)b on a.ProjectCode=b.ProjectCode";
			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
					$ProjectCode=$re_row[ProjectCode];
					$Outside=$re_row[Outside];
					$labor_cost=$re_row[labor_cost];

					if(substr($ProjectCode,1,2) == "XX")
					{
						$ProjectCode = str_replace("XX",substr($sel_year,2,2),$ProjectCode);
						$re_row[ProjectCode]=$ProjectCode ;
					}

					//$sql3="select * from timesheet_tbl where PCode = '$ProjectCode'";
					//임원제외
					$sql3="select * from timesheet_tbl where PCode = '$ProjectCode' and substring(sortkey,2,2) <> '01'";

					$re3 = mysql_query($sql3,$db);
					while($re_row3 = mysql_fetch_array($re3))
					{

						$WorkDate=$re_row3[WorkDate];
						$WorkTime=$re_row3[WorkTime];
						$grade=$re_row3[Note];

						$positionpay=GradePay($Outside,$grade);

						//근무시간 X 직급투입비용
						$WorkTimeCal=($WorkTime/32400)/22;  //1일 8시간으로 계산 ,1달 22일 기준으로 계산

						$laborcost=$WorkTimeCal*$positionpay;

						if($WorkDate >= $start_tmp && $WorkDate <= $end_tmp)  //이번달 투입비용구하기
						{
							$laborcost_month_sum+=$laborcost;
						}

						$laborcost_sum+=$laborcost;

					}

					if($labor_cost>0)
					{
						$re_row[labor_cost]=$labor_cost/$unit;
						$laborcost_month_rate_sum=($laborcost_month_sum/$labor_cost)*100;
						$laborcost_rate_sum=($laborcost_sum/$labor_cost)*100;
					}else
					{
						$laborcost_month_rate_sum=0;
						$laborcost_rate_sum=0;
					}

					$re_row[laborcost_month_sum]=$laborcost_month_sum/$unit;
					$re_row[laborcost_sum]=$laborcost_sum/$unit;
					$re_row[laborcost_month_rate_sum]=$laborcost_month_rate_sum;
					$re_row[laborcost_rate_sum]=$laborcost_rate_sum;

					array_push($query_data,$re_row);

					$laborcost_month_sum=0;
					$laborcost_sum=0;
			}




			if($sub_index == "")
				$sub_index=$GroupCode;

			$tab_index=$GroupCode;

			$GroupList = array();

			$Group_Row="0";
			$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드

			if($CompanyKind=="JANG")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'07' and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="PILE")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="HANM")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="BARO")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				if($GroupCode==$re_row2[Code])
				{
					$this->smarty->assign('GroupName',$re_row2[Name]);
				}
				array_push($GroupList,$re_row2);
			}


			$this->smarty->assign('GroupCode',$GroupCode);
			$this->smarty->assign('GroupList',$GroupList);
			$this->smarty->assign('sub_index',$sub_index);

			$this->smarty->assign('query_data',$query_data);
			$this->smarty->display("intranet/common_contents/work_laborcost/laborcost_mvc.tpl");





		}





		//============================================================================
		// 프로젝트별 투입인건비 상세보기
		//============================================================================
		function DetailView()
		{

			global $db,$memberID,$GroupCode,$ProjectCode;
			global $start_date,$end_date,$firstwork;

			$ProjectCode = urldecode($ProjectCode);


			if($start_date==""){$start_date=date("Y-m-d");}
			if($end_date==""){$end_date=date("Y-m-d");}


			//최초근무일 검색
			if($firstwork =="yes"){
				$sql = "select min(WorkDate) WorkDate from timesheet_tbl where PCode = '$ProjectCode'";
				$re = mysql_query($sql,$db);
				if(mysql_num_rows($re) > 0){
						$start_date=mysql_result($re,0,"WorkDate");
				}
			}


			$this->smarty->assign('start_date',$start_date);
			$this->smarty->assign('end_date',$end_date);
			$this->smarty->assign('ProjectCode',$ProjectCode);


			if(substr($ProjectCode,1,2) == "XX")
			{
				$ProjectCode = str_replace("XX",substr($sel_year,2,2),$ProjectCode);
				$re_row[ProjectCode]=$ProjectCode ;
			}



			//프로젝트정보
			$sqlinfo="select * from
			(
				select * from project_tbl where ProjectCode ='$ProjectCode'
			)a left join
			(
				select * from contact_point_tbl
			)b on a.ProjectCode=b.ProjectCode";

			$sqlinfo="select * from
			(
				select a.* ,b.RelationGroup RelationGroup,b.Phone Phone,b.Fax Fax  from
				(
					select * from project_tbl where ProjectCode ='$ProjectCode'
				)a left join
				(
					select * from contact_point_tbl
				)b on a.ProjectCode=b.ProjectCode
			)aa left join
			(
				select * from project_budget_tbl
			)bb on aa.ProjectCode=bb.ProjectCode";


			$reinfo = mysql_query($sqlinfo,$db);
			if(mysql_num_rows($reinfo) > 0)
			{
				$labor_cost=mysql_result($reinfo,0,"labor_cost");
				$Outside=mysql_result($reinfo,0,"Outside");
				$this->smarty->assign('ProjectCode',$ProjectCode);
				$this->smarty->assign('ProjectName',mysql_result($reinfo,0,"ProjectName"));
				$this->smarty->assign('ProjectNickname',mysql_result($reinfo,0,"ProjectNickname"));
				$this->smarty->assign('WorkStatus',mysql_result($reinfo,0,"WorkStatus"));
				$this->smarty->assign('OrderNickname',mysql_result($reinfo,0,"OrderNickname"));
				$this->smarty->assign('RelationGroup',mysql_result($reinfo,0,"RelationGroup"));
				$this->smarty->assign('Phone',mysql_result($reinfo,0,"Phone"));
				$this->smarty->assign('Name',MemberNo2Name(mysql_result($reinfo,0,"Name")));
				$this->smarty->assign('Fax',mysql_result($reinfo,0,"Fax"));
				$this->smarty->assign('labor_cost',$labor_cost);

			}


			$query_data = array();
			/*
			$sql="select * from
			(
				select distinct(substring(sortkey,2,2)) disgroup from timesheet_tbl where PCode = '$ProjectCode' and WorkDate >= '$start_date' and WorkDate <='$end_date'
			)a left join
			(
				select * from systemconfig_tbl where (SysKey='GroupCode' or SysKey='GroupCode_del')
			)b on a.disgroup=b.Code";
			*/
			//임원실제외
			$sql="select * from
			(
				select distinct(substring(sortkey,2,2)) disgroup from timesheet_tbl where PCode = '$ProjectCode' and WorkDate >= '$start_date' and WorkDate <='$end_date' and substring(sortkey,2,2) <> '01'
			)a left join
			(
				select * from systemconfig_tbl where (SysKey='GroupCode' or SysKey='GroupCode_del')
			)b on a.disgroup=b.Code";

			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{

					$disgroup=$re_row[disgroup];
					$GroupName=$re_row[Name];

					$sql2="select distinct(MemberNo) from timesheet_tbl where PCode = '$ProjectCode' and substring(sortkey,2,2)='$disgroup' and WorkDate >= '$start_date' and WorkDate <='$end_date' order by SortKey,Note";
					//echo $sql2."<Br>";
					$re2 = mysql_query($sql2,$db);
					while($re_row2 = mysql_fetch_array($re2))
					{

							$MemberNo=$re_row2[MemberNo];

							/*
							$sql3="select aa.* ,bb.Name RankName from
							(
								select WorkTime,Note,b.RankCode RankCode,korName from
								(
									select * from timesheet_tbl where PCode = '$ProjectCode' and MemberNo='$MemberNo' and WorkDate >= '$start_date' and WorkDate <='$end_date'
								)a left join
								(
									select * from member_tbl
								)b on a.MemberNo=b.MemberNo
							)aa left join
							(
								select * from systemconfig_tbl where SysKey='PositionCode'
							)bb on aa.RankCode=bb.code";
							*/

							$sql3="select aa.* ,bb.Name RankName from
							(
								select WorkTime,Note,b.RankCode RankCode,korName from
								(
									select * from timesheet_tbl where PCode = '$ProjectCode' and MemberNo='$MemberNo' and WorkDate >= '$start_date' and WorkDate <='$end_date' and substring(sortkey,2,2)='$disgroup'
								)a left join
								(
									select * from member_tbl
								)b on a.MemberNo=b.MemberNo
							)aa left join
							(
								select * from systemconfig_tbl where SysKey='PositionCode'
							)bb on aa.RankCode=bb.code";

							//echo "==".$sql3."<Br>";


							$re3 = mysql_query($sql3,$db);
							while($re_row3 = mysql_fetch_array($re3))
							{
								$WorkTime=$re_row3[WorkTime];
								$grade=$re_row3[Note];
								$RankName=$re_row3[RankName];
								$korName=$re_row3[korName];

								$GradeIndex=Grade($grade);
								$positionpay=GradePay($Outside,$grade);

								//직급별 man/month
								$WorkTimeCal=($WorkTime/32400)/22;  //1일 8시간으로 계산 ,1달 22일 기준으로 계산

								//직급별 투입기간 누적
								$WorkTimeLavel[$GradeIndex]+=$WorkTimeCal;
								$WorkTimeRow+=$WorkTimeCal;


								//투입인건비
								$LaborCost=$WorkTimeCal*$positionpay;
								$LaborCostRow+=$LaborCost;
							}

							for($i=0;$i<6;$i++)
							{
								$WorkTimeLavelSum[$i] +=$WorkTimeLavel[$i];
								if($WorkTimeLavel[$i] >0)
									$WorkTimeLavel[$i] = sprintf("%.2f",$WorkTimeLavel[$i]);
								else
									$WorkTimeLavel[$i] = "&nbsp;";
							}


							//소계저장
							$WorkTimeRowSum+=$WorkTimeRow;
							$LaborCostRowSum+=$LaborCostRow;

							$WorkTimeRow = sprintf("%.2f",$WorkTimeRow);
							$LaborCostRow = sprintf("%.2f",$LaborCostRow);


							$ItemData=array("GroupName"=> $GroupName,
											"RankName"=> $RankName,
											"korName"=> $korName,
											"WorkTimeLavel_0" =>$WorkTimeLavel[0],
											"WorkTimeLavel_1" =>$WorkTimeLavel[1],
											"WorkTimeLavel_2" =>$WorkTimeLavel[2],
											"WorkTimeLavel_3" =>$WorkTimeLavel[3],
											"WorkTimeLavel_4" =>$WorkTimeLavel[4],
											"WorkTimeLavel_5" =>$WorkTimeLavel[5],
											"WorkTimeRow"=>$WorkTimeRow,
											"LaborCostRow"=>$LaborCostRow,
											"Row"=>"row");

							array_push($query_data,$ItemData);

							$WorkTimeRow=0;
							$LaborCostRow=0;
							$WorkTimeLavel="";

					}



					for($i=0;$i<6;$i++)
					{
						$WorkTimeLavelSum[$i] = sprintf("%.2f",$WorkTimeLavelSum[$i]);
					}

					$WorkTimeRowSum = sprintf("%.2f",$WorkTimeRowSum);

					$ItemData=array("GroupName"=> '',
									"RankName"=> '',
									"korName"=> '',
									"WorkTimeLavel_0" =>$WorkTimeLavelSum[0],
									"WorkTimeLavel_1" =>$WorkTimeLavelSum[1],
									"WorkTimeLavel_2" =>$WorkTimeLavelSum[2],
									"WorkTimeLavel_3" =>$WorkTimeLavelSum[3],
									"WorkTimeLavel_4" =>$WorkTimeLavelSum[4],
									"WorkTimeLavel_5" =>$WorkTimeLavelSum[5],
									"WorkTimeRow"=>$WorkTimeRowSum,
									"LaborCostRow"=>$LaborCostRowSum,
									"Row"=>"sum");

					array_push($query_data,$ItemData);

					//총계계산
					for($i=0;$i<6;$i++)
					{
						$WorkTimeLavelTotal[$i] +=$WorkTimeLavelSum[$i];
					}


					$WorkTimeRowTotal+=$WorkTimeRowSum;
					$LaborCostRowTotal+=$LaborCostRowSum;


					$WorkTimeRowSum=0;
					$LaborCostRowSum=0;
					$WorkTimeLavelSum="";
			}


				for($i=0;$i<6;$i++)
				{
					$WorkTimeLavelTotal[$i] = sprintf("%.2f",$WorkTimeLavelTotal[$i]);
				}

				$WorkTimeRowTotal = sprintf("%.2f",$WorkTimeRowTotal);

				$ItemData=array("GroupName"=> '',
									"RankName"=> '',
									"korName"=> '',
									"WorkTimeLavel_0" =>$WorkTimeLavelTotal[0],
									"WorkTimeLavel_1" =>$WorkTimeLavelTotal[1],
									"WorkTimeLavel_2" =>$WorkTimeLavelTotal[2],
									"WorkTimeLavel_3" =>$WorkTimeLavelTotal[3],
									"WorkTimeLavel_4" =>$WorkTimeLavelTotal[4],
									"WorkTimeLavel_5" =>$WorkTimeLavelTotal[5],
									"WorkTimeRow"=>$WorkTimeRowTotal,
									"LaborCostRow"=>$LaborCostRowTotal,
									"Row"=>"total");

				array_push($query_data,$ItemData);


				if($labor_cost>0){

					$Prate=($LaborCostRowTotal/$labor_cost)*100;
					$Prate2=sprintf("%.1f", $Prate);
				}else
				{
					$Prate2="0";
				}
				$this->smarty->assign('Prate2',$Prate2);
				$this->smarty->assign('query_data',$query_data);

				$this->smarty->display("intranet/common_contents/work_laborcost/laborcost_detail_mvc.tpl");

		}

}




function GradePay($Outside,$grade)
{

	//합사
	if($Outside=="1"){
		switch ($grade) {

			case "C0":case "C1":case "C2":case "C3":case "C4":case "C5":case "C6":case "C7":case "C8":
				return "9700000";
			break;

			case "E1":case "E1A":case "E1B":
				return "6440000";
			break;

			case "E2":
				return "5920000";
			break;

			case "E3":
				return "5400000";
			break;

			case "E4":
				return "4880000";
			break;

			case "E5":case "E6":case "E7":case "E8":case "":
				return "4360000";
			break;
		}

	}else
	{
		switch ($grade) {
			case "C0":case "C1":case "C2":case "C3":case "C4":case "C5":case "C6":case "C7":case "C8":
				return "8838700";
			break;

			case "E1":case "E1A":case "E1B":
				return "5915000";
			break;

			case "E2":
				return "5357300";
			break;

			case "E3":
				return "4867200";
			break;

			case "E4":
				return "4377100";
			break;

			case "E5":case "E6":case "E7":case "E8":case "":
				return "3887000";
			break;
		}
		/*
		switch ($grade) {
			case "C0":case "C1":case "C2":case "C3":case "C4":case "C5":case "C6":case "C7":case "C8":
				return "9000000";
			break;

			case "E1":case "E1A":case "E1B":
				return "6000000";
			break;

			case "E2":
				return "5500000";
			break;

			case "E3":
				return "5000000";
			break;

			case "E4":
				return "4500000";
			break;

			case "E5":case "E6":case "E7":case "E8":case "":
				return "4000000";
			break;
		}
		*/
	}
}


function Grade($grade)
{
	switch ($grade) {

		case "C0":case "C1":case "C2":case "C3":case "C4":case "C5":case "C6":case "C7":case "C8":
			return "0";
		break;

		case "E1":case "E1A":case "E1B":
			return "1";
		break;

		case "E2":
			return "2";
		break;

		case "E3":
			return "3";
		break;

		case "E4":
			return "4";
		break;

		case "E5":case "E6":case "E7":case "E8":case "":
			return "5";
		break;
	}
}

/*
PositionCode	C0	회장	임원	회장
PositionCode	C2	사장	임원	사장
PositionCode	C3	원장	임원	원장
PositionCode	C4	상임고문	임원	상임고문
PositionCode	C5	부사장	임원	부사장
PositionCode	C6	전무이사	임원	전무이사
PositionCode	C7	상무이사	임원	상무이사
PositionCode	C8	이사	임원	이사
PositionCode	E1	부장	직원	부장
PositionCode	E2	차장	직원	차장
PositionCode	E3	과장	직원	과장
PositionCode	E4	대리	직원	대리
PositionCode	E5	사원	직원	사원(5급)
PositionCode	E6	사원	직원	사원(6급)
PositionCode	E7	사원	직원	사원(7급)
PositionCode	C1	부회장	임원	부회장
PositionCode	E0	책임연구원	직원	책임연구원
PositionCode	E1C	선임연구원	직원	선임연구원
PositionCode	E4A	연구원	직원	연구원
PositionCode	C4A	센터장	센터장	센터장
PositionCode	C8A	수석연구원	임원	수석연구원
PositionCode	C7A	기술위원	임원	기술위원
PositionCode	C0A	명예회장	임원	명예회장
PositionCode_del	E8	계약직	직원	계약직
PositionCode_del	E1A	실장	직원	실장
PositionCode_del	E1B	팀장	직원	팀장
PositionCode_del	C5A	수석	임원	수석
PositionCode_del	C6B	상무	임원	상무

*/

?>






