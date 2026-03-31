<?
include "../../../auto/function.php";
include "../../../auto/css_new_d.inc";


$ToDayStartTime = date('Y-m-d H:i:s');
//1) 임원 출장 입력시 양측에 동시입력됨(official_plan_tbl, userstate_tbl)
//2) 외출 입력시 양측에 동시입력됨(useractionlist_tbl, official_plan_tbl)
//3) 임원 직근시 경유 예약 입력 필요(userstate_tbl)
//function AM06_DayChangeProcess() {
$DayChangeTime = "06:00"; //전날 업무 종료 않을경우강제 종료후 재시작 시작
$Process = 0;
$CheckTime = date('H:i'); //'06:00';

$Todays=date("Y-m-d");
//$Todays="2022-11-09";
VacationBoostList_batch($Todays);

//월별연차사용촉진 대상자--------------------
function VacationBoostList_batch($Todays)
{
    include "../../../auto/dbcon.inc";
    include "../../../person_mng/inc/vacationfunction_v3.php";
    $DBINSERT="NO";
    $DBINSERT="YES";
    //global $db,$sel_year;
    //global $excel,$pageprint;

    //$this->assign('excel',$excel);
    //$this->assign('pageprint',$pageprint);


    //global $Auth;
    //$this->assign('Auth',$Auth);


    $uyear = date("Y")+1;
    $StartYear = date("Y")-2;

    if($sel_year=="") $sel_year=date("Y");

    //$this->assign('uyear',$uyear);
    //$this->assign('sel_year',$sel_year);
    //$this->assign('StartYear',$StartYear);

    $SearchTime=$sel_year."-01-01";

    //----------------------------------------
    //123412341234
    //$Today=date("Y-m-d");
    //$Today="2022-07-04";
    $Today=$Todays;
    $this_month=date("m");

    $this_year=date("Y");
    //$ThisYear=$sel_year;
    $ThisYear=date("Y");



    $query_data = array();
    
    $query_confirm = array();

    $sql="select A.*,B.vacationplus as vacation
  ,(select Name from systemconfig_tbl c where c.SysKey ='GroupCode' and c.code=A.GroupCode) as Groupnames
  ,(select Name from systemconfig_tbl c where c.SysKey ='PositionCode' and c.code=A.RankCode) as RankCodes
  from member_tbl A
  LEFT JOIN vacation_set B on A.MemberNo =B.MemberNo
  where A.WorkPosition ='1'
  and A.EntryDate !='0000-00-00'
  AND RankCode not LIKE 'C%'
    		
    		and
    		A.MemberNo='M20330'

  group by A.MemberNo ";
    // print_R($sql);

   // # MemberNo, Pasword, korName, RankCode, GroupCode, WorkPosition, chiName, engName, Degree, Technical, ExtNo, EntryDate, LeaveDate, JuminNo, Phone, Mobile, eMail, OrignAddress, Address, Author, Certificate, Meritorious, Disabled, UpdateDate, UpdateUser, Show_Insa, RegStDate, RegEdDate, Engineer, HeadType, Company, WorkCompany, EntryType, LeaveReason, PQProject, married, vacation, birthday, BirthdayType, originalbirthday, office_type, IntranetVer, order_index, RealRankCode, ViewRankName, UserInfoPW, vacation, Groupnames, RankCodes
   // 'M20330', '2167', '정명준', 'E1C', '3', '1', '鄭明俊', 'JEONG MYEONG JUN', '학사', '초급', '0', '2016-06-01', '0000-00-00', '861218-1253517', '0264888021', '01030622026', 'M20330@hanmaceng.co.kr', '경기도 성남시 분당구 장안로51번길 31, 101동 1102호 (분당동, 건영아파트)', '서울 송파구 마천로 524, 201호', '', ',업무B,인사A,인사B,경리B,총괄B,임원,총무,협조,설정,팀장,조직,맨아워A,단가,', '0', '0', '2021-04-14 16:52:19', 'M20328', '1', NULL, NULL, NULL, '216070', 'SAMAN', NULL, NULL, NULL, NULL, '미혼', '0', '1986-12-18', NULL, '1986-12-18', '0', NULL, NULL, 'E4', NULL, '2167', '5', '기술개발센터', '선임연구원'
    
    		
    //M21407
//and A.MemberNo in ('B21373','M22005')
// A.memberno='M21407'

    
//     echo $sql;
//     exit;
    
    $re = mysql_query($sql,$db);
    //$re = mysql_query($sql,$db);
    $num=1;

    $re_num = mysql_num_rows($re);
    if($re_num > 0) {

        while($re_row = mysql_fetch_array($re))
        {


        	array_push($query_confirm,$re_row);
        	
        	echo "111";
        	
        	
            //print_R($re_row);
            $MemberNo=$re_row[MemberNo];
            $EntryDate=$re_row[EntryDate];

            //============================================================================
            // MY Vacation 전년이월
            //============================================================================
            $sql2 = "select * from diligence_tbl where MemberNo = '$MemberNo' and date like '%$ThisYear%'";
            $re2 = mysql_query($sql2,$db);
            $re_num2 = mysql_num_rows($re2);
            if($re_num2 > 0)
            {
                $rest_day = mysql_result($re2,0,"rest_day");
            }
            else
            {
                $rest_day = "0";
            }

            if($rest_day > 0)  //전년월차 남아있으면 모두 0으로처리
            {
                $rest_day=0;
            }

            $re_row[rest_day]=$rest_day;

        $vb_member_info= $re_row[Groupnames].",".$re_row[korName].",".$re_row[RankCodes];
        //$vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
        //============================================================================
        // MY Vacation 연차생성  -임원은 표시안함   //J07202(김회성),T02303(신현우) +1
        //============================================================================
        $new_day=0;
        if($EntryDate <'2017-05-30'){
        	
        	

        	echo "<br>222<br>";
        	 
        	

                $EnterYear = substr($EntryDate,0,4);  //입사년도
                $EnterMonth = substr($EntryDate,5,2);  //입사월

                $StartDay = $ThisYear."-01-01";
                $EndDay   = $ThisYear."-12-31";

                $JoinYear = $ThisYear - $EnterYear; //현제년-입사년

                if($JoinYear <= 0) //1년미만은 없음
                {
                    $new_day = 0;
                }
                elseif($JoinYear == 1) //1년이상은 월별 차등지급
                {
                    if($EnterMonth == "01"){$new_day = 15;}
                    elseif($EnterMonth == "02"){$new_day = 14;}
                    elseif($EnterMonth == "03"){$new_day = 13;}
                    elseif($EnterMonth == "04"){$new_day = 11;}
                    elseif($EnterMonth == "05"){$new_day = 10;}
                    elseif($EnterMonth == "06"){$new_day = 9;}
                    elseif($EnterMonth == "07"){$new_day = 7;}
                    elseif($EnterMonth == "08"){$new_day = 6;}
                    elseif($EnterMonth == "09"){$new_day = 5;}
                    elseif($EnterMonth == "10"){$new_day = 3;}
                    elseif($EnterMonth == "11"){$new_day = 2;}
                    elseif($EnterMonth == "12"){$new_day = 0;}
                }
                else  //그외는 2년에 1일씩 증가
                {
                    $remainder=$JoinYear % 2;
                    if ($remainder == 0 )
                    {
                        $division=(int)($JoinYear/2);
                        $new_day= $division-1+15;
                    }
                    else
                    {
                        $division=(int)($JoinYear/2);
                        $new_day= $division+15-1;
                    }
                }
                
                
                echo "new_day <br>$new_day<br>";
                
                

                echo "re_row[vacation] <br>$re_row[vacation]<br>";
                

                $re_row[new_day]=$new_day+($re_row[vacation]);
                
                
                
                
                
                //$re_row[new_day]=$new_day;
                $re_row[cal_type]="0";
                $re_row[cal_name]="회계일";
                $spend_day=0;

                $sql_use = "select * from userstate_tbl where state in ( 1, 18, 30, 31 ) and MemberNo = '$MemberNo' and start_time like '$ThisYear%' and end_time <>'0000-00-00'";

                

                echo "<br>$sql_use<br>";
                
                
                $spend_hour = 0;
                $re_use = mysql_query($sql_use,$db);
                $re_num_use = mysql_num_rows($re_use);
                {
                    while($re_row_use = mysql_fetch_array($re_use))
                    {
                        if($re_row_use[state]=="1" or $re_row_use[state]=="30" or $re_row_use[state]=="31" )
                        {
                            if($re_row_use[start_time] >= $StartDay && $re_row_use[end_time] <= $EndDay)
                            {
                                @$spend = calculate($re_row_use[start_time],$re_row_use[end_time],$re_row_use[note]);
                            }
                            elseif($re_row_use[start_time] < $StartDay)
                            {
                                if($re_row_use[end_time] > $EndDay)
                                {
                                    @$spend = calculate($StartDay,$EndDay,$re_row_use[note]);
                                }
                                else
                                {
                                    @$spend = calculate($StartDay,$re_row_use[end_time],$re_row_use[note]);
                                }
                            }
                            else
                            {
                                @$spend = calculate($re_row_use[start_time],$EndDay,$re_row_use[note]);
                            }

                            $spend_day = $spend_day + $spend;
                        }else
                        {
                            $spend_hour+=$re_row_use[sub_code];
                        }

                    }
                }

                
                
                


                echo "spend_hour=<br>$spend_hour<br>";
                
                
                
                $re_row2[use_day]=$spend_day;
                
                
                
                echo "spend_day=<br>$spend_day<br>";
                
                
                
                $re_row2[rest_day]=$re_row[rest_day]*8;
                //$re_row[new_day]=$re_row[new_day]*8+($re_row[vacation]*8);
                
                
                echo "re_row new_day=<br> $re_row[new_day]<br>";
                
                
                $re_row2[new_day]=$re_row[new_day]*8;
                
                

                echo "re_row2 new_day=<br> $re_row2[new_day]<br>";
                

                $re_row[new_day_sum]=($re_row[rest_day])+($re_row[new_day]);



                //$re_row[use_day]=($re_row[use_day]*8)+$spend_hour;
                $re_row[use_day]=($re_row2[use_day]*8)+($spend_hour); //사용연차+사용시차 (시)
                $re_row[use_day]=($re_row[use_day]/8); //사용연차+사용시차 (일)
                $re_row[remaind_day]=$re_row[new_day_sum]-$re_row[use_day]; //잔여연차

                //----------매년 01-01 고지 초기화
                //1차_7월1일(고정)
                $year_end1=$this_year."-07-01";
                $year_end1_note="회계일_07-01";
                //2차_10월31일(고정)
                $year_end2=$this_year."-10-31";
                $year_end2_note="회계일_10-31";
                $year_end2_note="회계일_10-31";
                //$year_end2_note=iconv('euc-kr', 'utf-8', '회계일_10-31');
                //$re_row[year_end2_note]=iconv('euc-kr', 'utf-8', $year_end2_note);

                //$tmpData[3]=($tmpData[3]/8);
                //$tmpData[3]=number_format(($tmpData[3]/8),1);
                $vb_remaind_day=($vb_remaind_day/8);


                //$tmpData[10]=number_format(($tmpData[10]/8),1);
//       $rest_day_e2=number_format(($rest_day_e/8),1); //잔여연차

                //$vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
//$vb_member_info= $re_row[Groupnames].",".$re_row[korName].",".$re_row[RankCodes];
                /*
                print_R($re_row[Groupnames]);
                echo "||: ";
                print_R($re_row[korName]);
                echo "||: ";
                print_R($re_row[RankCodes]);
                echo "||: ";
                print_R($re_row[year_end2_note]);
                echo "</br>";
                */
                $re_row[remaind_day]=number_format($re_row[remaind_day],1);
                $re_row[use_day]=number_format($re_row[use_day],1);

                $isNoticeDay1 = ($Today - $year_end1) >= 0 ? true : false;
                $isNoticeDay2 = ($Today - $year_end2) >= 0 ? true : false;
                if($isNoticeDay2 && $re_row[remaind_day] > "0.4" && ($year_end2 <= $Todays)){
                    $vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
                    //$sql="insert into vacation_boost_tbl (MemberNo ,create_year ,create_month ,degree ,notice_day ,status ,submit_YN ,check_YN ,notes ,remaind_day ,UpdateDate)
                    //values('$re_row[MemberNo]','$this_year','$this_month','1','$year_end1','0','N','N','$year_end1_note','$re_row[remaind_day]',now()); ";
                    // 기존년도, 차수에 사용자 삭제
                    $sql  = " Delete ";
                    $sql .= " From ";
                    $sql .= "     vacation_boost_tbl ";
                    $sql .= " Where vb_member_no = '$re_row[MemberNo]' ";
                    $sql .= " And vb_degree = '2' ";
                    $sql .= " And vb_create_year = '$this_year' and vb_etc_05 ='' ";
                    $resource = mysql_query($sql, $db);

                    $sql="insert into vacation_boost_tbl (vb_member_no, vb_notice_dt, vb_degree, vb_create_year, vb_create_month, vb_status, vb_use_day, vb_remaind_day, vb_update_dt, vb_member_info, vb_entry_dt, vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05)";
                    //$sql .=" value('$re_row[MemberNo]','$year_end2','2','$this_year','$this_month','0','$re_row[use_day]','$re_row[remaind_day]',now(),'$vb_member_info','$re_row[EntryDate]','$year_end2_note','$year_start','$tmpData[10]','$year_end',''); ";
                    $sql .=" value('$re_row[MemberNo]','$year_end2','2','$this_year','$this_month','0','$re_row[use_day]','$re_row[remaind_day]',now(),'$vb_member_info','$re_row[EntryDate]','$year_end2_note','$StartDay','$re_row[new_day]','$EndDay',''); ";
                    mysql_query($sql,$db);
                    //echo $sql;
                    //echo "<br>";

                }else if($isNoticeDay1 && $re_row[remaind_day] > "0.4" && ($year_end1 <= $Todays)){
                    //$vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
                    // 기존년도, 차수에 사용자 삭제
                    $sql  = " Delete ";
                    $sql .= " From ";
                    $sql .= "     vacation_boost_tbl ";
                    $sql .= " Where vb_member_no = '$re_row[MemberNo]' ";
                    $sql .= " And vb_degree = '1' ";
                    $sql .= " And vb_create_year = '$this_year' and vb_etc_05 =''";
                    $resource = mysql_query($sql, $db);

                    //$sql2="insert into vacation_boost_tbl (MemberNo ,create_year ,create_month ,degree ,notice_day ,status ,submit_YN ,check_YN ,notes ,remaind_day ,UpdateDate)
                    //values('$re_row[MemberNo]','$this_year','$this_month','2','$year_end2','0','N','N','$year_end2_note','$re_row[remaind_day]',now()); ";
                    $sql2="insert into vacation_boost_tbl (vb_member_no, vb_notice_dt, vb_degree, vb_create_year, vb_create_month, vb_status, vb_use_day, vb_remaind_day, vb_update_dt, vb_member_info, vb_entry_dt, vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05)";
                    //$sql2.=" value('$re_row[MemberNo]','$year_end1','1','$this_year','$this_month','0','$re_row[use_day]','$re_row[remaind_day]',now(),'$vb_member_info','$re_row[EntryDate]','$year_end1_note','$year_start','$tmpData[10]','$year_end',''); ";
                    $sql2.=" value('$re_row[MemberNo]','$year_end1','1','$this_year','$this_month','0','$re_row[use_day]','$re_row[remaind_day]',now(),'$vb_member_info','$re_row[EntryDate]','$year_end1_note','$StartDay','$re_row[new_day]','$EndDay',''); ";
                    mysql_query($sql2,$db);
                    //echo $sql2;
                    //echo "<br>";
                }
                //array_push($query_data,$re_row0);

            }else{
                //$vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);

                $EnterYear = substr($EntryDate,0,4);  //입사년도
                $EnterMonth = substr($EntryDate,5,2);  //입사월
                $EnterDay = substr($EntryDate,8,2);  //입사일

                $enter_start=$this_year."-".$EnterMonth."-".$EnterDay;
                //입사일
                $enter_starts=$EnterYear."-".$EnterMonth."-".$EnterDay;
                //오늘날짜
                $now_start=$this_year."-".date("m-d");

                if($EntryDate>$now_start)
                {
                    $re_row1[rest_day]=""; //이월연차
                    $re_row1[new_day]="";  //생성연차
                    $re_row1[new_day_sum]="";  //연차합계
                    $re_row1[use_day]="";  //사용연차
                    $re_row1[remaind_day]=""; //잔여연차
                }else{
                    if($enter_start > $now_start)
                    {
                        $this_year2=$this_year-1;
                        $year_start=$this_year2."-".$EnterMonth."-".$EnterDay;

                    }else{
                        $year_start=$enter_start;
                    }

                    //입사후 만1년
                    $year_end = date("Y-m-d", strtotime("+1 year", strtotime($year_start)));
                    $year_end = date("Y-m-d", strtotime("-1 day", strtotime($year_end)));

                    $ThisDay=$this_year."-".date("m-d");
                    if($ThisDay < $year_end )
                    {
                        $ThisDay=$year_end;
                    }

                    $arryear=getDiffdate_v3($Today, $EntryDate);
                    $yeargap=$arryear[yeargap];

                    if($yeargap==0)  //1년미만
                    {
                        $ThisDay=$this_year."-".date("m-d");
                    }

                    $vacationplus = $re_row[vacation];
                    //$tmpData=getAnnualLeaveNew2($ThisDay,$EntryDate,$MemberNo);
                    $tmpData=getAnnualLeaveNew2_v3($ThisDay,$EntryDate,$MemberNo,$vacationplus,$sel_year);

                    
                    
                    
                    
                    
                    //잔여연차
                    //$vb_remaind_day=($tmpData[10])-($tmpData[3]);
//$vb_remaind_day=(($tmpData[0])+($tmpData[10]))-($tmpData[3]);

                    if($tmpData[12]<1) { //1년미만------
                        $re_row[rest_day2]=$tmpData[5]; //전년이월
                        $re_row[rest_day]=$tmpData[0]; //전년이월cnt
                        $re_row[new_day2]=$tmpData[11];  //생성연차
                        $re_row[new_day]=$tmpData[10];  //생성연차cnt
                        $createvacation_sum=($tmpData[0]+$tmpData[10]);
                        $re_row[new_day_sum2]=hourtodatehour_v3($createvacation_sum);  //총연차
                        $re_row[new_day_sum]=$createvacation_sum;  //총연차cnt

                        $re_row[use_day2]=$tmpData[8];  //사용연차
                        $re_row[use_day]=$tmpData[3];  //사용연차cnt

                        $rest_day_e= ($createvacation_sum)-($tmpData[3]); //잔여연차_시간
//vb_remaind_day
                        $rest_day_e2=number_format(($rest_day_e/8),1); //잔여연차

                        $f_rest_day_e=hourtodatehour_v3($rest_day_e);
                        $re_row[remaind_day2]=$f_rest_day_e;  //생성연차ct
                        //$re_row[remaind_day]=$tmpData[10];  //생성연차ct
                        $re_row[remaind_day]=$rest_day_e;  //생성연차ct

                        //----------
                        //1차_만료일 3개월전
                        $year_end1 = date("Y-m-d", strtotime("-3 months", strtotime($year_end)));
                        $year_end1_note="1년미만_3개월전";


                        //2차_만료일 1개월전
                        $year_end2 = date("Y-m-d", strtotime("-1 months", strtotime($year_end)));
                        $year_end2_note="1년미만_1개월전";


                        //3차_만료일로부터 10일전
                        $year_end3 = date("Y-m-d", strtotime("-1 day", strtotime($year_end)));
                        $year_end3_note="1년미만_10일전";

                        //$tmpData[3]=($tmpData[3]/8);
                        $tmpData[3]=number_format(($tmpData[3]/8),1);
//$vb_remaind_day=number_format(($vb_remaind_day/8),1);
                        $tmpData[10]=($tmpData[10]/8);
                        //

                        $isNoticeDay1 = ($Today - $year_end1) >= 0 ? true : false;
                        $isNoticeDay2 = ($Today - $year_end2) >= 0 ? true : false;
                        $isNoticeDay3 = ($Today - $year_end3) >= 0 ? true : false;



                        if($isNoticeDay3 && $re_row[remaind_day] > "3" && ($year_end3 <= $Todays)){
                            $vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
                            //$sql="insert into vacation_boost_tbl (MemberNo ,create_year ,create_month ,degree ,notice_day ,status ,submit_YN ,check_YN ,notes ,remaind_day ,UpdateDate)
                            //values('$re_row[MemberNo]','$this_year','$this_month','1','$year_end1','0','N','N','$year_end1_note','$re_row[remaind_day]',now()); ";
                            $sql  = " Delete ";
                            $sql .= " From ";
                            $sql .= "     vacation_boost_tbl ";
                            $sql .= " Where vb_member_no = '$re_row[MemberNo]' ";
                            $sql .= " And vb_degree = '3' ";
                            $sql .= " And vb_create_year = '$this_year' and vb_etc_05 ='' ";
                            $resource = mysql_query($sql, $db);

                            $sql3="insert into vacation_boost_tbl (vb_member_no, vb_notice_dt, vb_degree, vb_create_year, vb_create_month, vb_status, vb_use_day, vb_remaind_day, vb_update_dt, vb_member_info, vb_entry_dt, vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05)
                 value('$re_row[MemberNo]','$year_end2','3','$this_year','$this_month','0','$tmpData[3]','$rest_day_e2',now(),'$vb_member_info','$enter_starts','$year_end3_note','$year_start','$tmpData[10]','$year_end',''); ";
                            mysql_query($sql3,$db);
//vb_member_info
                            //echo $sql3;
                            //echo "<br>";
                        }else if($isNoticeDay2 && $re_row[remaind_day] > "3" && ($year_end2 <= $Todays)){
                            $vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
                            //$sql2="insert into vacation_boost_tbl (MemberNo ,create_year ,create_month ,degree ,notice_day ,status ,submit_YN ,check_YN ,notes ,remaind_day ,UpdateDate)
                            //values('$re_row[MemberNo]','$this_year','$this_month','2','$year_end2','0','N','N','$year_end2_note','$re_row[remaind_day]',now()); ";
                            $sql  = " Delete ";
                            $sql .= " From ";
                            $sql .= "     vacation_boost_tbl ";
                            $sql .= " Where vb_member_no = '$re_row[MemberNo]' ";
                            $sql .= " And vb_degree = '2' ";
                            $sql .= " And vb_create_year = '$this_year' and vb_etc_05 ='' ";
                            $resource = mysql_query($sql, $db);

                            $sql2="insert into vacation_boost_tbl (vb_member_no, vb_notice_dt, vb_degree, vb_create_year, vb_create_month, vb_status, vb_use_day, vb_remaind_day, vb_update_dt, vb_member_info, vb_entry_dt, vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05)
                 value('$re_row[MemberNo]','$year_end2','2','$this_year','$this_month','0','$tmpData[3]','$rest_day_e2',now(),'$vb_member_info','$enter_starts','$year_end2_note','$year_start','$tmpData[10]','$year_end',''); ";
                            mysql_query($sql2,$db);
                            //echo $sql2;

                        }else if($isNoticeDay1 && $re_row[remaind_day] > "3" && ($year_end1 <= $Todays)){
                            $vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
                            //$sql3="insert into vacation_boost_tbl (MemberNo ,create_year ,create_month ,degree ,notice_day ,status ,submit_YN ,check_YN ,notes ,remaind_day ,UpdateDate)
                            //values('$re_row[MemberNo]','$this_year','$this_month','3','$year_end3','0','N','N','$year_end3_note','$re_row[remaind_day]',now()); ";
                            $sql  = " Delete ";
                            $sql .= " From ";
                            $sql .= "     vacation_boost_tbl ";
                            $sql .= " Where vb_member_no = '$re_row[MemberNo]' ";
                            $sql .= " And vb_degree = '1' ";
                            $sql .= " And vb_create_year = '$this_year' and vb_etc_05 ='' ";
                            $resource = mysql_query($sql, $db);

                            $sql="insert into vacation_boost_tbl (vb_member_no, vb_notice_dt, vb_degree, vb_create_year, vb_create_month, vb_status, vb_use_day, vb_remaind_day, vb_update_dt, vb_member_info, vb_entry_dt, vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05)
                 value('$re_row[MemberNo]','$year_end1','1','$this_year','$this_month','0','$tmpData[3]','$rest_day_e2',now(),'$vb_member_info','$enter_starts','$year_end1_note','$year_start','$tmpData[10]','$year_end',''); ";

                            mysql_query($sql,$db);

                            //echo $sql3;
                            //echo "<br>";
                        }

                        
                        
                        array_push($query_confirm,iconv('UTF-8','EUC-KR',$sql));
                        
                    }else{
//vb_remaind_day
                        $re_row[rest_day2]=$tmpData[5]; //전년이월
                        $re_row[rest_day]=$tmpData[0]; //전년이월cnt
                        $re_row[new_day2]=$tmpData[11];  //생성연차
                        $re_row[new_day]=$tmpData[10];  //생성연차cnt
                        $createvacation_sum=($tmpData[0]+$tmpData[10]);
                        $re_row[new_day_sum2]=hourtodatehour_v3($createvacation_sum);  //총연차
                        $re_row[new_day_sum]=$createvacation_sum;  //총연차cnt

                        $re_row[use_day2]=$tmpData[8];  //사용연차
                        $re_row[use_day]=$tmpData[3];  //사용연차cnt

                        $rest_day_e= ($createvacation_sum)-($tmpData[3]);
                        $rest_day_e2=number_format(($rest_day_e/8),1); //잔여연차
                        $f_rest_day_e=hourtodatehour_v3($rest_day_e);
                        $re_row[remaind_day2]=$f_rest_day_e;  //잔여연차
                        $re_row[remaind_day]=$tmpData[4];  //잔여연차cnt
                        $re_row[remaind_day]=$rest_day_e;  //잔여연차cnt

                        $vb_member_info= iconv('euc-kr', 'utf-8', $vb_member_info);
                        //----------매년 입사월일 초기화
                        //1차_만료일 6개월전
                        $year_end1 = date("Y-m-d", strtotime("-6 months", strtotime($year_end)));
                        $year_end1_note="입사일_만료일6개월전";

                        //2차_만료일 2개월전
                        $year_end2 = date("Y-m-d", strtotime("-2 months", strtotime($year_end)));
                        $year_end2_note="입사일_만료일2개월전";

                        //$tmpData[3]=($tmpData[3]/8);
                        $tmpData[3]=number_format(($tmpData[3]/8),1);
//$vb_remaind_day=($vb_remaind_day/8);
                        $tmpData[10]=($tmpData[10]/8);

                        $isNoticeDay1 = ($Today - $year_end1) >= 0 ? true : false;
                        $isNoticeDay2 = ($Today - $year_end2) >= 0 ? true : false;
                        //if($isNoticeDay2 && $re_row[remaind_day] > "3"){
                        if($isNoticeDay2 && $rest_day_e2 > "0.4" && ($year_end2 <= $Todays)){
                            $sql  = " Delete ";
                            $sql .= " From ";
                            $sql .= "     vacation_boost_tbl ";
                            $sql .= " Where vb_member_no = '$re_row[MemberNo]' ";
                            $sql .= " And vb_degree = '2' ";
                            $sql .= " And vb_create_year = '$this_year' and vb_etc_05 ='' ";
                            $resource = mysql_query($sql, $db);

                            $sql2="insert into vacation_boost_tbl (vb_member_no, vb_notice_dt, vb_degree, vb_create_year, vb_create_month, vb_status, vb_use_day, vb_remaind_day, vb_update_dt, vb_member_info, vb_entry_dt, vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05)
                 value('$re_row[MemberNo]','$year_end2','2','$this_year','$this_month','0','$tmpData[3]','$rest_day_e2',now(),'$vb_member_info','$enter_starts','$year_end2_note','$year_start','$tmpData[10]','$year_end',''); ";
                            mysql_query($sql2,$db);

                            //echo $sql;
                            //exit;

                            //}else if($isNoticeDay1 && $re_row[remaind_day] > "3"){
                        }else if($isNoticeDay1 && $rest_day_e2 > "0.4" && ($year_end1 <= $Todays)){
                            $sql  = " Delete ";
                            $sql .= " From ";
                            $sql .= "     vacation_boost_tbl ";
                            $sql .= " Where vb_member_no = '$re_row[MemberNo]' ";
                            $sql .= " And vb_degree = '1' ";
                            $sql .= " And vb_create_year = '$this_year' and vb_etc_05 ='' ";
                            $resource = mysql_query($sql, $db);

                            $sql="insert into vacation_boost_tbl (vb_member_no, vb_notice_dt, vb_degree, vb_create_year, vb_create_month, vb_status, vb_use_day, vb_remaind_day, vb_update_dt, vb_member_info, vb_entry_dt, vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05)
                 value('$re_row[MemberNo]','$year_end1','1','$this_year','$this_month','0','$tmpData[3]','$rest_day_e2',now(),'$vb_member_info','$enter_starts','$year_end1_note','$year_start','$tmpData[10]','$year_end','');";

                            mysql_query($sql,$db);
                            //echo $sql2;
                            //echo "<br>";
                            
                        }
                        
                        
                        array_push($query_confirm,iconv('UTF-8','EUC-KR',$sql));
                    }


                }
                //array_push($query_data,$re_row0);
            }

            //$this->assign('query_data',$query_data);
        }


        $log_txt = date("Y-m-d H:i:s",time())." : BATCH : SUCCESS";//로그남기기 msg

    }else{
        $log_txt = date("Y-m-d H:i:s",time())." : BATCH : FAIL";//로그남기기 msg
    }

    
    print_r($query_confirm);
//==============================================
//로그남기기
    $log_file = "../log/boost_batch_hanmac.txt";
    if(is_dir($log_file)){
        $log_option = 'w';
    }else{
        $log_option = 'a';
    }

    $log_file = fopen($log_file, $log_option);
    fwrite($log_file, $log_txt."\r\n");
    fclose($log_file);


    //$this->assign('Today',$Today);
    //$this->display("person_mng/sub/vacation_boost_tbl_list.tpl");
}
//월별연차사용촉진 대상자end-----

function HangleEncode($item)
{
    $result=trim(ICONV("EUC-KR","UTF-8",$item));
    if(trim($result)=="")  $result="&nbsp";
    return $result;
}

function HangleEncodeUTF8_EUCKR($item)
{
    $result=trim(ICONV("UTF-8","EUC-KR",$item));
    return $result;
}





?>

