<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

	/***************************************
	* 출금전표
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/ 

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/approval_function.php";

	extract($_GET);
	class BusinessTripLogic {
		var $smarty;
		function BusinessTripLogic($smarty)
		{
			$this->smarty=$smarty;
		}

	
		//================================================================================
		// 출금전표 작성 Logic
		//================================================================================	
		function InsertPage()
		{

			global $db;
			global $auth,$memberID,$mode,$kind,$PRINT,$no;

			$date1 = date("Y");  /// 오늘
			$date2 = date("m");  /// 오늘
			$date3 = date("d");  /// 오늘
			$date_0 = $date1."년".$date2."월".$date3."일";
			$date_1 = $date1."-".$date2."-".$date3;

			$query1000 = "select * from member_tbl where MemberNo = '$memberID'";
				$result1000 = mysql_query($query1000,$db);
				$o_name1000 =  mysql_result($result1000,0,"korName");

			$sql = "select * from official_plan_tbl where no='$no'";
				//echo $sql."<br>"; 
				$re = mysql_query($sql,$db);
				while($re_row = mysql_fetch_array($re))
				{
					$no=$re_row[no];
					$o_area=$re_row[o_area];
					$o_itinerary=$re_row[o_itinerary];
					$o_group=$re_row[o_group];
					$o_name=$re_row[o_name];
					$o_start=$re_row[o_start];
					$o_end=$re_row[o_end];
					$o_object=$re_row[o_object];
					$o_traffic=$re_row[o_traffic];
					$o_note=$re_row[o_note];
					$o_passwd=$re_row[o_passwd];
					$ProjectCode=$re_row[ProjectCode];
					$contents=$re_row[contents];
					$result=$re_row[result];
					$memberno=$re_row[memberno];
					$o_change=$re_row[o_change];
					$DocSN=$re_row[DocSN];
				}
				
				if($o_area <> $o_itinerary)
				{
					$o_area=$o_area." ".$o_itinerary;
				}

			$sql2 = "select * from project_tbl where ProjectCode='$ProjectCode'";
				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2))
				{
					$ProjectNickname=$re_row2[ProjectNickname];
				}

			$query00 = "select * from official_cost_tbl where no='$no'";
			//echo $query00."<br>";
				$result00 = mysql_query($query00,$db);
				$result00_num = mysql_num_rows($result00);
				$result00_row = mysql_fetch_array($result00);

				$hotel_num=$result00_row[hotel_num];
				$hotel_day=$result00_row[hotel_day];
				$hotel_cost=$result00_row[hotel_cost];
				$hotel_total=$result00_row[hotel_total];
				$hotel_desc=$result00_row[hotel_desc];
				$recost_num=$result00_row[recost_num];
				$recost_day=$result00_row[recost_day];
				$recost_cost=$result00_row[recost_cost];
				$recost_total=$result00_row[recost_total];
				$recost_desc=$result00_row[recost_desc];
				$food_num=$result00_row[food_num];
				$food_count=$result00_row[food_count];
				$food_cost=$result00_row[food_cost];
				$food_total=$result00_row[food_total];
				$food_desc=$result00_row[food_desc];
				$etc_basic=$result00_row[etc_basic];
				$etc_cost=$result00_row[etc_cost];
				$etc_desc=$result00_row[etc_desc];
				$traffic_basic=$result00_row[traffic_basic];
				$traffic_cost=$result00_row[traffic_cost];
				$traffic_desc=$result00_row[traffic_desc];
				$official_total=$result00_row[official_total];
				$official_desc=$result00_row[official_desc];
				$business_date=$result00_row[business_date];
				$business_contents=$result00_row[business_contents];
				$business_note=$result00_row[business_note];
				$business_etc=$result00_row[business_etc];

			$query01 = "select * from official_gasbill_tbl where no='$no'";
				$result01 = mysql_query($query01,$db);
				$result01_num = mysql_num_rows($result01);
				$result01_row = mysql_fetch_array($result01);

				$Srart_place1=$result01_row[Srart_place1];
				$Arrival_place1=$result01_row[Arrival_place1];
				$distance1=$result01_row[distance1];
				$gas_money1=$result01_row[gas_money1];
				$passage_money1=$result01_row[passage_money1];
				$etc_money1=$result01_row[etc_money1];
				$total_money1=$result01_row[total_money1];
				$Srart_place2=$result01_row[Srart_place2];
				$Arrival_place2=$result01_row[Arrival_place2];
				$distance2=$result01_row[distance2];
				$gas_money2=$result01_row[gas_money2];
				$passage_money2=$result01_row[passage_money2];
				$etc_money2=$result01_row[etc_money2];
				$total_money2=$result01_row[total_money2];
				$Srart_place3=$result01_row[Srart_place3];
				$Arrival_place3=$result01_row[Arrival_place3];
				$distance3=$result01_row[distance3];
				$gas_money3=$result01_row[gas_money3];
				$passage_money3=$result01_row[passage_money3];
				$etc_money3=$result01_row[etc_money3];
				$total_money3=$result01_row[total_money3];
				$Srart_place4=$result01_row[Srart_place4];
				$Arrival_place4=$result01_row[Arrival_place4];
				$distance4=$result01_row[distance4];
				$gas_money4=$result01_row[gas_money4];
				$passage_money4=$result01_row[passage_money4];
				$etc_money4=$result01_row[etc_money4];
				$total_money4=$result01_row[total_money4];
				$oilcost=$result01_row[oilcost];
				

				
				if($mode=="")
				{
					if($result00_num>"0")
					{
						$mode="mod";
					}
					else
					{
						$mode="add";
					}
				}

				if($mode<>"add")
				{
					$total_distance=$distance1+$distance2+$distance3+$distance4;
					$total_gas_money=$gas_money1+$gas_money2+$gas_money3+$gas_money4;
					$total_passage_money=$passage_money1+$passage_money2+$passage_money3+$passage_money4;
					$total_etc_money=$etc_money1+$etc_money2+$etc_money3+$etc_money4;
					$extended_price=$total_money1+$total_money2+$total_money3+$total_money4;
				}


				$start_bak1 = substr($o_start,0,10);
				$divpre=explode("-",$start_bak1);
				$syear= $divpre[0];
				$smon=$divpre[1];
				$sday=$divpre[2];

				$start_bak2	= substr($o_end,0,10);
				$divpre1=explode("-",$start_bak2);
				$eyear= $divpre1[0];
				$emon=$divpre1[1];
				$eday=$divpre1[2];


				$S_date=$syear."년 ".$smon."월 ".$sday."일";
				$E_date=$eyear."년 ".$emon."월 ".$eday."일";

				$il2=(strtotime($start_bak2)-strtotime($start_bak1))/86400; 
				$il=$il2+1;
				$bak=$il-1;
				
				if ($il <= 0)
				{
					$il="";
					$bak="";
				}

				$ba_date=explode("/",$result00_row[business_date]);
				$budate1 = $ba_date[0];
				$budate2 = $ba_date[1];

				$b_contents=explode("/",$result00_row[business_contents]);
				$bucontents1 = $b_contents[0];
				$bucontents2 = $b_contents[1];
				$bucontents3 = $b_contents[2];
				$bucontents4 = $b_contents[3];
				$bucontents5 = $b_contents[4];

				$b_memberno=explode(",",$memberno);
				$mem_num=count($b_memberno);

				if($memberID == $b_memberno[0])
				{
					$bmemberno[1] = $b_memberno[1];
					$bmemberno[2] = $b_memberno[2];
					$bmemberno[3] = $b_memberno[3];
					$bmemberno[4] = $b_memberno[4];
					$bmemberno[5] = $b_memberno[5];
				}
				elseif($memberID == $b_memberno[1])
				{
					$bmemberno[1] = $b_memberno[0];
					$bmemberno[2] = $b_memberno[2];
					$bmemberno[3] = $b_memberno[3];
					$bmemberno[4] = $b_memberno[4];
					$bmemberno[5] = $b_memberno[5];
				}
				elseif($memberID == $b_memberno[2])
				{
					$bmemberno[1] = $b_memberno[0];
					$bmemberno[2] = $b_memberno[1];
					$bmemberno[3] = $b_memberno[3];
					$bmemberno[4] = $b_memberno[4];
					$bmemberno[5] = $b_memberno[5];
				}
				elseif($memberID == $b_memberno[3])
				{
					$bmemberno[1] = $b_memberno[0];
					$bmemberno[2] = $b_memberno[1];
					$bmemberno[3] = $b_memberno[2];
					$bmemberno[4] = $b_memberno[4];
					$bmemberno[5] = $b_memberno[5];
				}
				elseif($memberID == $b_memberno[4])
				{
					$bmemberno[1] = $b_memberno[0];
					$bmemberno[2] = $b_memberno[1];
					$bmemberno[3] = $b_memberno[2];
					$bmemberno[4] = $b_memberno[3];
					$bmemberno[5] = $b_memberno[5];
				}
				else
				{
					$bmemberno[1] = $b_memberno[0];
					$bmemberno[2] = $b_memberno[1];
					$bmemberno[3] = $b_memberno[2];
					$bmemberno[4] = $b_memberno[3];
					$bmemberno[5] = $b_memberno[4];
				}

				if ($memberID =="")
				{
					$result01_name = "";
					$result01_01_rank = "";
					$result01_02_group = "";
				}
				else
				{
						$self_name=	MemberNo2Name($memberID);
						$self_rank = MemberNo2Rank($memberID);
						$self_groupcode =	MemberNo2GroupCode($memberID);
						$self_group =	MemberNo2GroupName($memberID);

					for($k=1; $k<$mem_num; $k++)
					{
						$member_name[$k]=	MemberNo2Name($bmemberno[$k]);
						$member_rank[$k] = MemberNo2Rank($bmemberno[$k]);
						$member_groupcode[$k] =	MemberNo2GroupCode($bmemberno[$k]);
						$member_group[$k] =	MemberNo2GroupName($bmemberno[$k]);
					}
				}

				$busin_etc=explode("/",$result00_row[business_etc]);

				//동행자
				$DetailData = array(); 				
				$Dt2=split(",",$memberno);
				for($i=0; $i<count($Dt2); $i++) {

						if($Dt2[$i] <> "")
						{
							
							$mGroup= MemberNo2GroupName($Dt2[$i]);
							$mRank = MemberNo2Rank($Dt2[$i]);
							$mName = MemberNo2Name($Dt2[$i]);

							$ItemData2=array("ID"=>$Dt2[$i],"Group" =>$mGroup,"Rank" =>$mRank,"Name"=>$mName);			
	
						}else{
							$ItemData2=array("ID"=>'',"Group" =>'',"Rank" =>'',"Name"=>'');			
						}
						array_push($DetailData,$ItemData2);

				}
				$this->smarty->assign('DetailData',$DetailData);
				

				$hotel_cost=numberformat($hotel_cost);
				$hotel_total=numberformat($hotel_total);

				$recost_cost=numberformat($recost_cost);
				$recost_total=numberformat($recost_total);

				$food_cost=numberformat($food_cost);
				$food_total=numberformat($food_total);

				$etc_cost=numberformat($etc_cost);
				$traffic_cost=numberformat($traffic_cost);

				$official_total=numberformat($official_total);

				if($oilcost=="" || $oilcost=="0" ){$oilcost="1100";}

				$oilcost=numberformat($oilcost);

				$Srart_place1=numberformat($Srart_place1);
				$Arrival_place1=numberformat($Arrival_place1);
				$distance1=numberformat($distance1);
				$gas_money1=numberformat($gas_money1);
				$passage_money1=numberformat($passage_money1);
				$etc_money1=numberformat($etc_money1);
				$total_money1=numberformat($total_money1);

				$Srart_place2=numberformat($Srart_place2);
				$Arrival_place2=numberformat($Arrival_place2);
				$distance2=numberformat($distance2);
				$gas_money2=numberformat($gas_money2);
				$passage_money2=numberformat($passage_money2);
				$etc_money2=numberformat($etc_money2);
				$total_money2=numberformat($total_money2);

				$Srart_place3=numberformat($Srart_place3);
				$Arrival_place3=numberformat($Arrival_place3);
				$distance3=numberformat($distance3);
				$gas_money3=numberformat($gas_money3);
				$passage_money3=numberformat($passage_money3);
				$etc_money3=numberformat($etc_money3);
				$total_money3=numberformat($total_money3);

				$total_distance=numberformat($total_distance);
				$total_gas_money=numberformat($total_gas_money);
				$total_passage_money=numberformat($total_passage_money);
				$total_etc_money=numberformat($total_etc_money);
				$extended_price=numberformat($extended_price);


//============출장 기본정보 관련 (출장자 까지)=======================//

				$backgroundcolor="red";
				$this->smarty->assign('backgroundcolor',$backgroundcolor);	

				$this->smarty->assign('memberID',$memberID);	
				$this->smarty->assign('mode',$mode);	
				$this->smarty->assign('kind',$kind);
				$this->smarty->assign('date_1',$date_1);
				$this->smarty->assign('date_0',$date_0);
				$this->smarty->assign('no',$no);
				$this->smarty->assign('o_area',$o_area);
				$this->smarty->assign('o_itinerary',$o_itinerary);
				$this->smarty->assign('o_group',$o_group);
				$this->smarty->assign('o_name',$o_name);
				$this->smarty->assign('o_start',$o_start);
				$this->smarty->assign('o_end',$o_end);
				$this->smarty->assign('o_object',$o_object);
				$this->smarty->assign('o_traffic',$o_traffic);
				$this->smarty->assign('o_note',$o_note);
				$this->smarty->assign('o_passwd',$o_passwd);
				$this->smarty->assign('ProjectCode',$ProjectCode);
				$this->smarty->assign('contents',$contents);
				$this->smarty->assign('result',$result);
				$this->smarty->assign('memberno',$memberno);
				$this->smarty->assign('o_change',$o_change);
				$this->smarty->assign('ProjectNickname',$ProjectNickname);
				$this->smarty->assign('self_name',$self_name);
				$this->smarty->assign('self_rank',$self_rank);
				$this->smarty->assign('self_groupcode',$self_groupcode);
				$this->smarty->assign('self_group',$self_group);

//============기간 / 기타 관련====================================//

				$this->smarty->assign('member_name',$member_name);
				$this->smarty->assign('member_rank',$member_rank);
				$this->smarty->assign('member_groupcode',$member_groupcode);
				$this->smarty->assign('member_group',$member_group);
				$this->smarty->assign('busin_etc',$busin_etc);
				$this->smarty->assign('S_date',$S_date);
				$this->smarty->assign('E_date',$E_date);
				$this->smarty->assign('il',$il);
				$this->smarty->assign('bak',$bak);
				$this->smarty->assign('bucontents1',$bucontents1);
				$this->smarty->assign('bucontents2',$bucontents2);
				$this->smarty->assign('bucontents3',$bucontents3);
				$this->smarty->assign('bucontents4',$bucontents4);
				$this->smarty->assign('bucontents5',$bucontents5);


//============출장비용산출내용 관련================================//

				$this->smarty->assign('hotel_num',$hotel_num);	
				$this->smarty->assign('hotel_day',$hotel_day);	
				$this->smarty->assign('hotel_cost',$hotel_cost);
				$this->smarty->assign('hotel_total',$hotel_total);
				$this->smarty->assign('hotel_desc',$hotel_desc);

				$this->smarty->assign('recost_num',$recost_num);	
				$this->smarty->assign('recost_day',$recost_day);	
				$this->smarty->assign('recost_cost',$recost_cost);
				$this->smarty->assign('recost_total',$recost_total);
				$this->smarty->assign('recost_desc',$recost_desc);

				$this->smarty->assign('food_num',$food_num);	
				$this->smarty->assign('food_count',$food_count);	
				$this->smarty->assign('food_cost',$food_cost);
				$this->smarty->assign('food_total',$food_total);
				$this->smarty->assign('food_desc',$food_desc);

				$this->smarty->assign('etc_basic',$etc_basic);	
				$this->smarty->assign('etc_cost',$etc_cost);	
				$this->smarty->assign('etc_desc',$etc_desc);

				$this->smarty->assign('traffic_basic',$traffic_basic);
				$this->smarty->assign('traffic_cost',$traffic_cost);	
				$this->smarty->assign('traffic_desc',$traffic_desc);

				$this->smarty->assign('official_total',$official_total);
				$this->smarty->assign('official_desc',$official_desc);	

				$this->smarty->assign('business_date',$business_date);
				$this->smarty->assign('business_contents',$business_contents);
				$this->smarty->assign('business_note',$business_note);
				$this->smarty->assign('business_etc',$business_etc);

//============유류비 산출근거 관련==================================//

				$this->smarty->assign('oilcost',$oilcost);	

				$this->smarty->assign('Srart_place1',$Srart_place1);	
				$this->smarty->assign('Arrival_place1',$Arrival_place1);	
				$this->smarty->assign('distance1',$distance1);	
				$this->smarty->assign('gas_money1',$gas_money1);	
				$this->smarty->assign('passage_money1',$passage_money1);	
				$this->smarty->assign('etc_money1',$etc_money1);	
				$this->smarty->assign('total_money1',$total_money1);	

				$this->smarty->assign('Srart_place2',$Srart_place2);	
				$this->smarty->assign('Arrival_place2',$Arrival_place2);	
				$this->smarty->assign('distance2',$distance2);	
				$this->smarty->assign('gas_money2',$gas_money2);	
				$this->smarty->assign('passage_money2',$passage_money2);	
				$this->smarty->assign('etc_money2',$etc_money2);	
				$this->smarty->assign('total_money2',$total_money2);	

				$this->smarty->assign('Srart_place3',$Srart_place3);	
				$this->smarty->assign('Arrival_place3',$Arrival_place3);	
				$this->smarty->assign('distance3',$distance3);	
				$this->smarty->assign('gas_money3',$gas_money3);	
				$this->smarty->assign('passage_money3',$passage_money3);	
				$this->smarty->assign('etc_money3',$etc_money3);	
				$this->smarty->assign('total_money3',$total_money3);	

				$this->smarty->assign('Srart_place4',$Srart_place4);	
				$this->smarty->assign('Arrival_place4',$Arrival_place4);	
				$this->smarty->assign('distance4',$distance4);	
				$this->smarty->assign('gas_money4',$gas_money4);	
				$this->smarty->assign('passage_money4',$passage_money4);	
				$this->smarty->assign('etc_money4',$etc_money4);	
				$this->smarty->assign('total_money4',$total_money4);	

				$this->smarty->assign('total_distance',$total_distance);	
				$this->smarty->assign('total_gas_money',$total_gas_money);	
				$this->smarty->assign('total_passage_money',$total_passage_money);	
				$this->smarty->assign('total_etc_money',$total_etc_money);	
				$this->smarty->assign('extended_price',$extended_price);	

				$this->smarty->assign('PRINT',$PRINT);	
				$this->smarty->assign("page_action","businesstrip_controller.php");
				$this->smarty->display("intranet/common_contents/work_documents/businesstrip_input_mvc.tpl");
		}

	
		//================================================================================
		// 출금전표 Insert Logic
		//================================================================================	
		function InsertAction()
		{

			global $db;
			global $auth,$memberID,$officalno,$mode;
			global $hotel_num,$hotel_day,$hotel_cost,$hotel_total,$hotel_desc;
			global $recost_num,$recost_day,$recost_cost,$recost_total,$recost_desc;
			global $food_num,$food_count,$food_cost,$food_total,$food_desc;
			global $etc_basic,$etc_cost,$etc_desc,$traffic_basic,$traffic_cost,$traffic_desc;
			global $bak,$il,$b_contents_1,$b_contents_2,$b_contents_3,$b_contents_4,$b_contents_5,$b_member_etc0;
			global $official_total,$official_desc,$bc_date,$b_contents,$date_1,$b_etc,$contentsz,$result;
			global $member_name1,$member_name2,$member_name3,$member_name4,$member_name5;
			global $b_member_etc1,$b_member_etc2,$b_member_etc3,$b_member_etc4;
			global $Srart_place1,$Arrival_place1,$distance1,$gas_money1,$passage_money1,$etc_money1,$total_money1;
			global $Srart_place2,$Arrival_place2,$distance2,$gas_money2,$passage_money2,$etc_money2,$total_money2;
			global $Srart_place3,$Arrival_place3,$distance3,$gas_money3,$passage_money3,$etc_money3,$total_money3;
			global $Srart_place4,$Arrival_place4,$distance4,$gas_money4,$passage_money4,$etc_money4,$total_money4;
			global $Detail_2,$mDt1,$mDt2,$mDt3,$oilcost;
			
					
			$dbinsert="yes";
			//$dbinsert="no";

				$bc_date = $bak."/".$il;

				if($b_contents_1 != "" || $b_contents_2 != "" || $b_contents_3 != "" || $b_contents_4 != "" || $b_contents_5 != "")
				{
					$b_contents = $b_contents_1."/".$b_contents_2."/".$b_contents_3."/".$b_contents_4."/".$b_contents_5;
				}
				else
				{
					$b_contents = "";
				}
				$b_etc = $b_member_etc0."/".$b_member_etc1."/".$b_member_etc2."/".$b_member_etc3."/".$b_member_etc4;
				
				/*
				if($memberID != "")
				{
					$query01 = "select * from member_tbl where MemberNo = '$memberID' and WorkPosition <= '8'";
					$result01 = mysql_query($query01,$db);
					$result01_num = mysql_num_rows($result01);
					if($result01_num != 0)
					{
						$result_row_01 = mysql_result($result01,0,"korName");
						$o_name10 = $result_row_01;
						$o_member10 = $memberID;
					}
					else
					{
						echo("<script>
						 window.alert('성명이 유효하지 않습니다. 다시 선택하여 주십시요.')
						 history.go(-1)
						 </script>
					   ");exit;
					}
				}
				*/

				for($i=0;$i<7;$i++)
				{
					if($Detail_2[$i] != "")
					{
						if($i==0)
						{
							$o_name10 = $mDt3[$i];
							$o_member10 = $Detail_2[$i];
						}else
						{
							$o_name10 = $o_name10.",".$mDt3[$i];
							$o_member10 = $o_member10.",".$Detail_2[$i];
						}
					}
				}


				$hotel_cost=str_replace(",","",$hotel_cost);
				$hotel_total=str_replace(",","",$hotel_total);
				$recost_cost=str_replace(",","",$recost_cost);
				$recost_total=str_replace(",","",$recost_total);
				$food_cost=str_replace(",","",$food_cost);
				$food_total=str_replace(",","",$food_total);
				$etc_cost=str_replace(",","",$etc_cost);
				$traffic_cost=str_replace(",","",$traffic_cost);
				$official_total=str_replace(",","",$official_total);
				$oilcost=str_replace(",","",$oilcost);

				$Srart_place1=str_replace(",","",$Srart_place1);
				$Arrival_place1=str_replace(",","",$Arrival_place1);
				$distance1=str_replace(",","",$distance1);
				$gas_money1=str_replace(",","",$gas_money1);
				$passage_money1=str_replace(",","",$passage_money1);
				$etc_money1=str_replace(",","",$etc_money1);
				$total_money1=str_replace(",","",$total_money1);

				$Srart_place2=str_replace(",","",$Srart_place2);
				$Arrival_place2=str_replace(",","",$Arrival_place2);
				$distance2=str_replace(",","",$distance2);
				$gas_money2=str_replace(",","",$gas_money2);
				$passage_money2=str_replace(",","",$passage_money2);
				$etc_money2=str_replace(",","",$etc_money2);
				$total_money2=str_replace(",","",$total_money2);

				$Srart_place3=str_replace(",","",$Srart_place3);
				$Arrival_place3=str_replace(",","",$Arrival_place3);
				$distance3=str_replace(",","",$distance3);
				$gas_money3=str_replace(",","",$gas_money3);
				$passage_money3=str_replace(",","",$passage_money3);
				$etc_money3=str_replace(",","",$etc_money3);
				$total_money3=str_replace(",","",$total_money3);

				$total_distance=str_replace(",","",$total_distance);
				$total_gas_money=str_replace(",","",$total_gas_money);
				$total_passage_money=str_replace(",","",$total_passage_money);
				$total_etc_money=str_replace(",","",$total_etc_money);
				$extended_price=str_replace(",","",$extended_price);


				$insql="insert into official_cost_tbl (no,hotel_num,hotel_day,hotel_cost,hotel_total,hotel_desc,recost_num,recost_day,recost_cost,recost_total,recost_desc,food_num,food_count,food_cost,food_total,food_desc,etc_basic,etc_cost,etc_desc,traffic_basic,traffic_cost,traffic_desc,official_total,official_desc,business_date,business_contents,business_note,business_etc) values('$officalno','$hotel_num','$hotel_day','$hotel_cost','$hotel_total','$hotel_desc','$recost_num','$recost_day','$recost_cost','$recost_total','$recost_desc','$food_num','$food_count','$food_cost','$food_total','$food_desc','$etc_basic','$etc_cost','$etc_desc','$traffic_basic','$traffic_cost','$traffic_desc','$official_total','$official_desc','$bc_date','$b_contents','$date_1','$b_etc')";
				
				if($dbinsert=="yes")
					mysql_query($insql,$db);
				else
					echo $insql."<br>";
				

				// 유류비 내용 저장
				$insql2="insert into official_gasbill_tbl (no,Srart_place1,Arrival_place1,distance1,gas_money1,passage_money1,etc_money1,total_money1,Srart_place2,Arrival_place2,distance2,gas_money2,passage_money2,etc_money2,total_money2,Srart_place3,Arrival_place3,distance3,gas_money3,passage_money3,etc_money3,total_money3,Srart_place4,Arrival_place4,distance4,gas_money4,passage_money4,etc_money4,total_money4,oilcost) values('$officalno','$Srart_place1','$Arrival_place1','$distance1','$gas_money1','$passage_money1','$etc_money1','$total_money1','$Srart_place2','$Arrival_place2','$distance2','$gas_money2','$passage_money2','$etc_money2','$total_money2','$Srart_place3','$Arrival_place3','$distance3','$gas_money3','$passage_money3','$etc_money3','$total_money3','$Srart_place4','$Arrival_place4','$distance4','$gas_money4','$passage_money4','$etc_money4','$total_money4','$oilcost')";

				if($dbinsert=="yes")
					mysql_query($insql2,$db);
				else
					echo $insql2."<br>";
				

				// 출장결과 저장
				$dallyup1 = "update official_plan_tbl set contents = '$contentsz' , result = '$result' , memberno = '$o_member10' , o_name = '$o_name10' where no = '$officalno'";

				if($dbinsert=="yes")
					mysql_query($dallyup1,$db);
				else
					echo $dallyup1."<br>";
				
				
				if($dbinsert=="yes")
				{
					$this->smarty->assign('target',"self");
					$this->smarty->assign('MoveURL',"businesstrip_controller.php?ActionMode=insert_page&memberID=$memberID&no=$officalno&mode=mod");
					$this->smarty->display("intranet/move_page.tpl");
				}
		}

		//================================================================================
		// 출금전표 Update Logic
		//================================================================================	

		function UpdateAction()
		{

			global $db;
			global $auth,$memberID,$officalno,$mode;
			global $hotel_num,$hotel_day,$hotel_cost,$hotel_total,$hotel_desc;
			global $recost_num,$recost_day,$recost_cost,$recost_total,$recost_desc;
			global $food_num,$food_count,$food_cost,$food_total,$food_desc;
			global $etc_basic,$etc_cost,$etc_desc,$traffic_basic,$traffic_cost,$traffic_desc;
			global $bak,$il,$b_contents_1,$b_contents_2,$b_contents_3,$b_contents_4,$b_contents_5,$b_member_etc0;
			global $official_total,$official_desc,$bc_date,$b_contents,$date_1,$b_etc,$contentsz,$result;
			global $member_name1,$member_name2,$member_name3,$member_name4,$member_name5;
			global $b_member_etc1,$b_member_etc2,$b_member_etc3,$b_member_etc4,$PRINT;
			global $Srart_place1,$Arrival_place1,$distance1,$gas_money1,$passage_money1,$etc_money1,$total_money1;
			global $Srart_place2,$Arrival_place2,$distance2,$gas_money2,$passage_money2,$etc_money2,$total_money2;
			global $Srart_place3,$Arrival_place3,$distance3,$gas_money3,$passage_money3,$etc_money3,$total_money3;
			global $Srart_place4,$Arrival_place4,$distance4,$gas_money4,$passage_money4,$etc_money4,$total_money4;
			global $Detail_2,$mDt1,$mDt2,$mDt3,$oilcost;
			
			$dbinsert="yes";
			//$dbinsert="no";

				$bc_date = $bak."/".$il;

				if($b_contents_1 != "" || $b_contents_2 != "" || $b_contents_3 != "" || $b_contents_4 != "" || $b_contents_5 != "")
				{
					$b_contents = $b_contents_1."/".$b_contents_2."/".$b_contents_3."/".$b_contents_4."/".$b_contents_5;
				}
				else
				{
					$b_contents = "";
				}
				$b_etc = $b_member_etc0."/".$b_member_etc1."/".$b_member_etc2."/".$b_member_etc3."/".$b_member_etc4;
				
				/*
				if($memberID != "")
				{
					$query01 = "select * from member_tbl where MemberNo = '$memberID' and WorkPosition <= '8'";
					$result01 = mysql_query($query01,$db);
					$result01_num = mysql_num_rows($result01);
					if($result01_num != 0)
					{
						$result_row_01 = mysql_result($result01,0,"korName");
						$o_name10 = $result_row_01;
						$o_member10 = $memberID;
					}
					else
					{
						echo("<script>
						 window.alert('성명이 유효하지 않습니다. 다시 선택하여 주십시요.')
						 history.go(-1)
						 </script>
					   ");exit;
					}
				}
				*/

				for($i=0;$i<7;$i++)
				{
					if($Detail_2[$i] != "")
					{
						if($i==0)
						{
							$o_name10 = $mDt3[$i];
							$o_member10 = $Detail_2[$i];
						}else
						{
							$o_name10 = $o_name10.",".$mDt3[$i];
							$o_member10 = $o_member10.",".$Detail_2[$i];
						}
					}
				}



				
				$hotel_cost=str_replace(",","",$hotel_cost);
				$hotel_total=str_replace(",","",$hotel_total);
				$recost_cost=str_replace(",","",$recost_cost);
				$recost_total=str_replace(",","",$recost_total);
				$food_cost=str_replace(",","",$food_cost);
				$food_total=str_replace(",","",$food_total);
				$etc_cost=str_replace(",","",$etc_cost);
				$traffic_cost=str_replace(",","",$traffic_cost);
				$official_total=str_replace(",","",$official_total);
				$oilcost=str_replace(",","",$oilcost);

				$Srart_place1=str_replace(",","",$Srart_place1);
				$Arrival_place1=str_replace(",","",$Arrival_place1);
				$distance1=str_replace(",","",$distance1);
				$gas_money1=str_replace(",","",$gas_money1);
				$passage_money1=str_replace(",","",$passage_money1);
				$etc_money1=str_replace(",","",$etc_money1);
				$total_money1=str_replace(",","",$total_money1);

				$Srart_place2=str_replace(",","",$Srart_place2);
				$Arrival_place2=str_replace(",","",$Arrival_place2);
				$distance2=str_replace(",","",$distance2);
				$gas_money2=str_replace(",","",$gas_money2);
				$passage_money2=str_replace(",","",$passage_money2);
				$etc_money2=str_replace(",","",$etc_money2);
				$total_money2=str_replace(",","",$total_money2);

				$Srart_place3=str_replace(",","",$Srart_place3);
				$Arrival_place3=str_replace(",","",$Arrival_place3);
				$distance3=str_replace(",","",$distance3);
				$gas_money3=str_replace(",","",$gas_money3);
				$passage_money3=str_replace(",","",$passage_money3);
				$etc_money3=str_replace(",","",$etc_money3);
				$total_money3=str_replace(",","",$total_money3);

				$total_distance=str_replace(",","",$total_distance);
				$total_gas_money=str_replace(",","",$total_gas_money);
				$total_passage_money=str_replace(",","",$total_passage_money);
				$total_etc_money=str_replace(",","",$total_etc_money);
				$extended_price=str_replace(",","",$extended_price);


				$query20 = "delete from official_cost_tbl where no='$officalno'";

				if($dbinsert=="yes")
					mysql_query($query20,$db);
				else
					echo $query20."<br>";
				

				$query30 = "delete from official_gasbill_tbl where no='$officalno'";
				
				if($dbinsert=="yes")
					mysql_query($query30,$db);
				else
					echo $query30."<br>";
	

				$insql1="insert into official_cost_tbl (no,hotel_num,hotel_day,hotel_cost,hotel_total,hotel_desc,recost_num,recost_day,recost_cost,recost_total,recost_desc,food_num,food_count,food_cost,food_total,food_desc,etc_basic,etc_cost,etc_desc,traffic_basic,traffic_cost,traffic_desc,official_total,official_desc,business_date,business_contents,business_note,business_etc) values('$officalno','$hotel_num','$hotel_day','$hotel_cost','$hotel_total','$hotel_desc','$recost_num','$recost_day','$recost_cost','$recost_total','$recost_desc','$food_num','$food_count','$food_cost','$food_total','$food_desc','$etc_basic','$etc_cost','$etc_desc','$traffic_basic','$traffic_cost','$traffic_desc','$official_total','$official_desc','$bc_date','$b_contents','$date_1','$b_etc')";

				if($dbinsert=="yes")
					mysql_query($insql1,$db);
				else
					echo $insql1."<br>";
				

				// 유류비 내용 저장
				$insql2="insert into official_gasbill_tbl (no,Srart_place1,Arrival_place1,distance1,gas_money1,passage_money1,etc_money1,total_money1,Srart_place2,Arrival_place2,distance2,gas_money2,passage_money2,etc_money2,total_money2,Srart_place3,Arrival_place3,distance3,gas_money3,passage_money3,etc_money3,total_money3,Srart_place4,Arrival_place4,distance4,gas_money4,passage_money4,etc_money4,total_money4,oilcost) values('$officalno','$Srart_place1','$Arrival_place1','$distance1','$gas_money1','$passage_money1','$etc_money1','$total_money1','$Srart_place2','$Arrival_place2','$distance2','$gas_money2','$passage_money2','$etc_money2','$total_money2','$Srart_place3','$Arrival_place3','$distance3','$gas_money3','$passage_money3','$etc_money3','$total_money3','$Srart_place4','$Arrival_place4','$distance4','$gas_money4','$passage_money4','$etc_money4','$total_money4','$oilcost')";

				if($dbinsert=="yes")
					mysql_query($insql2,$db);
				else
					echo $insql2."<br>";
				

				// 출장결과 저장
				$dallyup1 = "update official_plan_tbl set contents = '$contentsz' , result = '$result' , memberno = '$o_member10' , o_name = '$o_name10' where no = '$officalno'";
				
				if($dbinsert=="yes")
					mysql_query($dallyup1,$db);
				else
					echo $dallyup1."<br>";


				if($dbinsert=="yes")
				{
					$this->smarty->assign('target',"self");
					$this->smarty->assign('MoveURL',"businesstrip_controller.php?ActionMode=insert_page&memberID=$memberID&no=$officalno&mode=mod");
					$this->smarty->display("intranet/move_page.tpl");
				}
			
		}



		
	}
?>