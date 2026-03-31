<?
include "../../../auto/function.php";
include "../../../auto/css_new_d.inc";

//$ToDayStartTime = date('Y-m-d H:i:s');
//1) 임원 출장 입력시 양측에 동시입력됨(official_plan_tbl, userstate_tbl)
//2) 외출 입력시 양측에 동시입력됨(useractionlist_tbl, official_plan_tbl)
//3) 임원 직근시 경유 예약 입력 필요(userstate_tbl)
//function AM06_DayChangeProcess() {
$DayChangeTime = "06:00"; //전날 업무 종료 않을경우강제 종료후 재시작 시작
$Process = 0;
$CheckTime = date('H:i'); //'06:00';

$Todays=date("Y-m-d");
//$Todays="2023-12-10";
VacationBoostList_batch($Todays);

//월별연차사용촉진 대상자--------------------
function VacationBoostList_batch($Todays)
{
    include "../../../auto/dbcon.inc";
    include "../../../person_mng/inc/vacationfunction_v3.php";
    include "../../../person_mng/inc/vacationfunction.php";
    
    $DBINSERT="NO";
    $DBINSERT="YES";
    
    extract($_GET);
    $DEBUG_BOOL= $DEBUG_YN==""?false:true;
    
//     ECHO $DEBUG_YN."<==";
//     EXIT;
    
    $StartYear = date("Y")-2;
    if($sel_year=="") $sel_year=date("Y");
    
    $SearchTime=$sel_year."-01-01";
    
    //----------------------------------------
    //오픈일
    $openstartday="2022-12-15";
    
    $Today=$Todays;
//$Today="2023-09-01";
    $this_month=date("m");
    
    $this_year=date("Y");
    //$ThisYear=$sel_year;
    $ThisYear=date("Y");
    
    $query_data = array();
    
    /*
     Groupnames 부서코드 한글
     RankCodes 직급코드
     vacationplus
     */
    
    $sql="select A.*
     ,(select Name from systemconfig_tbl c where c.SysKey ='GroupCode' and c.code=A.GroupCode) as Groupnames
     ,(select Name from systemconfig_tbl c where c.SysKey ='PositionCode' and c.code=A.RankCode) as RankCodes
     ,(select ifnull(max(vacationplus),0) from vacation_set  where MemberNo = A.MemberNo and year = '$sel_year') as vacationplus
     from member_tbl A
     where A.WorkPosition ='1'
     and A.EntryDate !='0000-00-00'
     AND RankCode not LIKE 'C%'
	 AND A.EntryDate < date_format(date_add(NOW(), INTERVAL -1 MONTH) , '%Y-%m-%d') 

     group by A.MemberNo 
	 ";
	 //and A.MemberNo in ('B23019','M20318')
    // print_R($sql);
	// and A.MemberNo in ('M20306','M21464','B23007')
    //  exit;
    // and A.MemberNo in ('B23007')
//print_R();
    if($DEBUG_BOOL){
            echo $sql."<br>";
            
      
    }
    
    $re = mysql_query($sql,$db);
    $num=1;
    
    $re_num = mysql_num_rows($re);
    if($re_num > 0) {
        
        
        while($re_row = mysql_fetch_array($re))
        {
            $re_MemberNo=$re_row[MemberNo];
            $re_EntryDate=$re_row[EntryDate];
            
            /*
             $vacation_info = array(
             'Member_No' => $MemberNo				//사번
             , 'kor_name' => $kor_name				//이름
             , 'entry_date' => $entry_date			//입사일
             , 'rank_code' => $rank_code				//직위코드
             , 'rank_name' => $rank_name				//직위명
             , 'dept_code' => $dept_code				//부서코드
             , 'dept_name' => $dept_name				//부서명
             , 'tar_year' => $tar_year				//해당년도
             , 'start_date' => $start_date			//연차 시작일
             , 'end_date' => $end_date				//연차 종료일
             , 'work_year' => $work_year				//재직기간 년
             , 'work_month' => $work_month			//재직기간 년
             , 'vacation_gb' => $vacation_gb			//기준정보
             , 'new_day' => $new_time/8				//생성연차(일수)
             , 'new_day_text' => TimeToText($new_time)//생성연차 한글
             , 'new_time' => $new_time				//생성연차 (시간)
             , 'rest_day' => $rest_time/8				//전년 이월
             , 'rest_day_text' => TimeToText($rest_time)	//전년 이월 한글
             , 'rest_time' => $rest_time					//전년 이월 시간
             , 'plus_day' => $plus_time/8				//연차증감
             , 'plus_day_text' => TimeToText($plus_time)	//연차증감 한글
             , 'plus_time' => $plus_time					//연차증감
             , 'plus_meno' => $plus_meno					//연차증감 메모
             , 'total_day' => ($total_time)/8			//총 연차
             , 'total_day_text' => TimeToText($total_time)//총 연차 한글
             , 'total_time' => $total_time				//총 연차 시간
             , 'use_day' => $use_time/8					//사용연차(일수)
             , 'use_day_text' => TimeToText($use_time)	//사용연차 한글
             , 'use_time' => $use_time					//사용연차 (시간)
             , 'left_day' => ($total_time - $use_time)/8	//잔여연차
             , 'left_time' => $total_time - $use_time	//잔여연차 시간
             , 'left_day_text' => TimeToText($total_time - $use_time)	//잔여연차 한글
             );
             */
            

            if( $re_EntryDate < '2017-05-30' ){	//회계일 기준
                //echo 1;
                $set_sel_year = $sel_year;
            }else{	//입사일 기준
                if( date("m-d") < substr($re_EntryDate,5,5) ){	//입사일 기준
                    //현재 사용중인 연차가 
                    //입사년월일에 따라 이전년도 중간에 생성된 연차일수있다. 
                    $set_sel_year = $sel_year-1;
                }else{
                    $set_sel_year = $sel_year;
                }
                
               // echo "set_sel_year===============".$set_sel_year."<br>";
            }
            
//             if($DEBUG_BOOL==true){
//                 echo "==============="."<br>";
//                 if($re_MemberNo=="B21369"){
//                     if( date("m-d") < substr($re_EntryDate,5,5) ){	//입사일 기준은 기준일 체크
//                         $set_sel_year = $sel_year-1;
//                     }else{
//                         $set_sel_year = $sel_year;
//                     }
//                     echo "set_sel_year===============".$set_sel_year."<br>";
//                 }
//             }
            
            $VacationInfo   = GetVacationInfo($db, $set_sel_year, $re_MemberNo ,"2017-05-30","iconv");
/*
            echo "==============="."<br>";
              */
            if($re_MemberNo=="B22016" && $set_sel_year=="2025"){
               // echo "==============="."<br>";
             //  print_r($VacationInfo);
               
               /*

Array ( [Member_No] => B22016 [kor_name] => 최창인 [entry_date] => 2022-03-07 [rank_code] => E0 [rank_name] => 책임연구원 
[dept_code] => 3 [dept_name] => 기술개발센터 [tar_year] => 2025 [start_date] => 2025-03-07 [end_date] => 2026-03-06 
[work_year] => [work_month] => [vacation_gb] => 1 

[new_day] => 16 
[new_day_text] => 16일 0시간 
[new_time] => 128 

[rest_day] => -2.125 
[rest_day_text] => -2일 -1시간 
[rest_time] => -17 

[plus_day] => 0 
[plus_day_text] => 0일 0시간 
[plus_time] => 0 [plus_meno] => 

[total_day] => 13.875 
[total_day_text] => 13일 7시간 
[total_time] => 111 

[use_day] => 10.5 
[use_day_text] => 10일 4시간 
[use_time] => 84 
[left_day] => 3.375 

[left_time] => 27 
[left_day_text] => 3일 3시간 

[WORK_PERIOD_TYPE] => 2 [error] => [standard_year] => 2025 )
                */
            }
          
            
            
/*
print_r($VacationInfo);
echo "<br>";

print_r($set_sel_year." | ".$re_MemberNo);
echo "<br>";
  */          
//             if($DEBUG_BOOL==true){
//                 echo "==============="."<br>";
//                 if($re_MemberNo=="B21369"){
//                     print_r($VacationInfo);
//                 }
//             }
            
            
            //-------------------------------------------------------------------
            $VC_start_date	= $VacationInfo['start_date'];	//연차사용 시작일
            $VC_end_date	= $VacationInfo['end_date'];	//연차사용 종료일
            $VC_left_day	= $VacationInfo['left_day'];	//잔여연차(일수)
            $VC_left_time   = $VacationInfo['left_time'];	//잔여연차(시간)
            $VC_entry_date	= $VacationInfo['entry_date'];	//입사일자
            $VC_new_day		= $VacationInfo['new_day'];		//발생연차(일수)
            $VC_use_day		= $VacationInfo['use_day'];		//사용연차(일수)
            
            //$vb_member_info= $VacationInfo['dept_name'].",".$VacationInfo['kor_name'].",".$VacationInfo['rank_code'];
            $vb_member_info= $VacationInfo['dept_name'].",".$VacationInfo['kor_name'].",".$VacationInfo['rank_name'];
            
            $VC_vacation_gb = $VacationInfo['vacation_gb']; //0=회계일 기준, 1=입사일 기준
            
            //$VacationInfo['rest_day'];
            
            //print_R($VacationInfo);
            
            //============================================
            // $VC_vacation_gb = 0=회계일 기준,
            if($VC_vacation_gb=="0"){

                //1차_7월1일(고정)
                $year_end1=$this_year."-07-01";//1차알림촉진 고지일자
                $year_end1_note="회계일_07-01";
                //2차_10월31일(고정)
                $year_end2=$this_year."-10-31";//2차알림촉진 고지일자
                $year_end2_note="회계일_10-31";
                
                //--------------------------------------------------
                $isNoticeDay1 = ($Today - $year_end1) >= 0 ? true : false; //1차 고지알림 bool
                $isNoticeDay2 = ($Today - $year_end2) >= 0 ? true : false; //2차 고지알림 bool
                
                //--------------------------------------------------
                $SET_vb_degree = "1";	//고지차수
                $SET_vb_notice_dt = ""; //고지알림일자
                $SET_vb_etc_01 = "";	//비고1: 회계일자/1년이상/
                $SET_this_month = "";// 미사용
                
                //--------------------------------------------------
                if($isNoticeDay2 && $VC_left_time > "0.4" && $year_end2 < $Today && $openstartday < $year_end2){
                    //회계일자 기준인원
                    //잔여연차 시간이 0.4 초과일경우
                    //연차촉진2차 고지일자(해당년도 10월31일) < 현재일자
                    $SET_vb_degree		= "2"; //고지차수
                    $SET_vb_notice_dt	= $year_end2; //고지알림일자
                    $SET_vb_etc_01		= iconv('utf-8', 'euc-kr', $year_end2_note);//비고1: 회계일자/1년이상/
                    
                }else if($isNoticeDay1 && $VC_left_time > "0.4" && $year_end1 < $Today && $openstartday < $year_end1){
                    //회계일자 기준인원
                    //잔여연차 시간이 0.4 초과일경우
                    //연차촉진2차 고지일자(해당년도 7월1일) < 현재일자
                    $SET_vb_degree		= "1"; //고지차수
                    $SET_vb_notice_dt	= $year_end1; //고지알림일자
                    $SET_vb_etc_01		= iconv('utf-8', 'euc-kr', $year_end1_note);//비고1: 회계일자/1년이상/
                    
                }
                
                /*
                 vb_member_no		사원번호
                 vb_notice_dt		고지일자(2022-06-30)
                 vb_degree			고지차수 (1/2/3..)
                 vb_create_year		연차생성연도
                 vb_create_month		생성월(해당컬럼 사용안함)
                 vb_status			상태(0:미제출, 1:고지기간, 3:결재중, 4:제출완료)
                 vb_use_day			고지일자 기준 사용한 연차(해당 발생연차기준)
                 vb_remaind_day		고지일자 기준_잔여연차_일수
                 vb_update_dt		수정일
                 vb_member_info		부서명,이름,직급(고지일자기준)
                 vb_entry_dt			입사일자
                 vb_etc_01			비고1: 회계일자/1년이상/
                 vb_etc_02			비고2: 촉진대상 연차의 발생일자
                 vb_etc_03			비고3: 촉진대상 연차의 발생연차 일수
                 vb_etc_04			비고4: 촉진대상 연차의 만료일자
                 vb_etc_05			비고5: 연차사용계획서 결재완료일자 기준의 실시간 잔여연차 UPDATE
                 */
                
                /*
                 // 기존년도, 차수에 사용자 삭제
                 $sql  = "
                 Delete From vacation_boost_tbl
                 Where
                 vb_member_no = '$re_MemberNo'
                 And
                 vb_degree = '$SET_vb_degree'
                 And
                 vb_create_year = '$this_year'
                 AND
                 vb_status = '0'
                 ";
                 */
                
                if($SET_vb_notice_dt != ""){
                    // 기존년도, 차수에 사용자 삭제
                    $sql  = "
							Delete From vacation_boost_tbl
							Where
								vb_member_no = '$re_MemberNo'
								And
								vb_degree = '$SET_vb_degree'
								And
								SUBSTR(vb_notice_dt,1,4)=SUBSTR('$SET_vb_notice_dt',1,4)
								AND
								vb_status = '0'
							";
                    
                    $resource = mysql_query($sql, $db);
                    
                    $sql="
						insert into vacation_boost_tbl
						(
						vb_member_no,
						vb_notice_dt,
						vb_degree,
						vb_create_year,
						vb_create_month,
						vb_status,
						vb_use_day,
						vb_remaind_day,
						vb_update_dt,
						vb_member_info,
						vb_entry_dt,
						vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05
						)value(
						'$re_MemberNo',
						'$SET_vb_notice_dt',
						'$SET_vb_degree',
						'$this_year',
						'$SET_this_month',
						'0',
						'$VC_use_day',
						'$VC_left_day',
						now(),
						'$vb_member_info',
						'$VC_entry_date',
						'$SET_vb_etc_01','$VC_start_date','$VC_new_day','$VC_end_date',''); ";

                    mysql_query($sql,$db);
                }
                
                
                
                //============================================
                //$VC_vacation_gb = 1=입사일 기준
                //============================================
            }else{
                
                /*
                 $re_MemberNo=$re_row[MemberNo];
                 $re_EntryDate=$re_row[EntryDate];
                 
                 
                 //                  $vacation_info = array(
                 //                  'Member_No' => $MemberNo				//사번
                 //                  , 'kor_name' => $kor_name				//이름
                 //                  , 'entry_date' => $entry_date			//입사일
                 //                  , 'rank_code' => $rank_code				//직위코드
                 //                  , 'rank_name' => $rank_name				//직위명
                 //                  , 'dept_code' => $dept_code				//부서코드
                 //                  , 'dept_name' => $dept_name				//부서명
                 //                  , 'tar_year' => $tar_year				//해당년도
                 //                  , 'start_date' => $start_date			//연차 시작일
                 //                  , 'end_date' => $end_date				//연차 종료일
                 //                  , 'work_year' => $work_year				//재직기간 년
                 //                  , 'work_month' => $work_month			//재직기간 년
                 //                  , 'vacation_gb' => $vacation_gb			//기준정보
                 //                  , 'new_day' => $new_time/8				//생성연차(일수)
                 //                  , 'new_day_text' => TimeToText($new_time)//생성연차 한글
                 //                  , 'new_time' => $new_time				//생성연차 (시간)
                 //                  , 'rest_day' => $rest_time/8				//전년 이월
                 //                  , 'rest_day_text' => TimeToText($rest_time)	//전년 이월 한글
                 //                  , 'rest_time' => $rest_time					//전년 이월 시간
                 //                  , 'plus_day' => $plus_time/8				//연차증감
                 //                  , 'plus_day_text' => TimeToText($plus_time)	//연차증감 한글
                 //                  , 'plus_time' => $plus_time					//연차증감
                 //                  , 'plus_meno' => $plus_meno					//연차증감 메모
                 //                  , 'total_day' => ($total_time)/8			//총 연차
                 //                  , 'total_day_text' => TimeToText($total_time)//총 연차 한글
                 //                  , 'total_time' => $total_time				//총 연차 시간
                 //                  , 'use_day' => $use_time/8					//사용연차(일수)
                 //                  , 'use_day_text' => TimeToText($use_time)	//사용연차 한글
                 //                  , 'use_time' => $use_time					//사용연차 (시간)
                 //                  , 'left_day' => ($total_time - $use_time)/8	//잔여연차
                 //                  , 'left_time' => $total_time - $use_time	//잔여연차 시간
                 //                  , 'left_day_text' => TimeToText($total_time - $use_time)	//잔여연차 한글
                 //                  );
                 
                 $VacationInfo   = GetVacationInfo($db, $sel_year, $re_MemberNo );
                 //-------------------------------------------------------------------
                 $VC_start_date	= $VacationInfo['start_date'];	//연차사용 시작일
                 $VC_end_date	= $VacationInfo['end_date'];	//연차사용 종료일
                 $VC_left_day	= $VacationInfo['left_day'];	//잔여연차(일수)
                 $VC_left_time   = $VacationInfo['left_time'];	//잔여연차(시간)
                 $VC_entry_date	= $VacationInfo['entry_date'];	//입사일자
                 $VC_new_day		= $VacationInfo['new_day'];		//발생연차(일수)
                 $VC_use_day		= $VacationInfo['use_day'];		//사용연차(일수)
                 
                 $vb_member_info= $VacationInfo['dept_name'].",".$VacationInfo['kor_name'].",".$VacationInfo['rank_code'];
                 
                 $VC_vacation_gb = $VacationInfo['vacation_gb']; //0=회계일 기준, 1=입사일 기준
                 */
//print_R($VacationInfo);
//echo "<br>";
                if($VacationInfo['error']==""){
                    $start_day0=$VC_entry_date; //입사일자
                    $end_day0=$VC_end_date; //연차사용 종료일
                    $diff_day=date_diff($end_day0,$start_day0);

/*
echo "<br>";
print_R("Member: ".$VacationInfo['Member_No']);
echo " | ";
print_R("start: ".$start_day0);
echo " | ";
print_R("end: ".$end_day0);
echo " | ";
print_R("diff_day: ".$diff_day);
echo "<br>";
*/


                    if($diff_day <= 365){
					//if($diff_day < 365){
                        //----------
                        //1차_만료일 3개월전
                        $year_end1 = date("Y-m-d", strtotime("-3 months", strtotime($VC_end_date)));
                        $year_end1_note="1년미만_3개월전";
                        
                        //2차_만료일 1개월전
                        $year_end2 = date("Y-m-d", strtotime("-1 months", strtotime($VC_end_date)));
                        $year_end2_note="1년미만_1개월전";
                        
                        //3차_만료일로부터 10일전
                        $year_end3 = date("Y-m-d", strtotime("-1 day", strtotime($VC_end_date)));
                        $year_end3_note="1년미만_10일전";
                    }else{
                        //1차_만료일 6개월전
                        $year_end1 = date("Y-m-d", strtotime("-6 months", strtotime($VC_end_date)));
                        $year_end1_note="입사일_만료일6개월전";
   
                        //2차_만료일 2개월전
                        $year_end2 = date("Y-m-d", strtotime("-2 months", strtotime($VC_end_date)));
                        $year_end2_note="입사일_만료일2개월전";
                        
                    }
//B17206        
/*
print_R("Today: ".$Today);
echo " | ";					
print_R("year_end1: ".$year_end1);
echo " | ";					
print_R("year_end2:".$year_end2);
echo "<br>";
  */                 
					  
                    //--------------------------------------------------
                    $isNoticeDay1 = ($Today - $year_end1) >= 0 ? true : false; //1차 고지알림 bool
                    $isNoticeDay2 = ($Today - $year_end2) >= 0 ? true : false; //2차 고지알림 bool
					                    
					if($diff_day <= 365){
                        $isNoticeDay3 = ($Today - $year_end3) >= 0 ? true : false; //3차 고지알림 bool
                    }


                    //--------------------------------------------------
                    $SET_vb_degree = "1";	//고지차수
                    $SET_vb_notice_dt = ""; //고지알림일자
                    $SET_vb_etc_01 = "";	//비고1: 회계일자/1년이상/
                    $SET_this_month = "";// 미사용
                    
                    //$VC_vacation_gb = 1=입사일 기준


	

/*
print_R("Member: ".$VacationInfo['Member_No']);
echo " | ";					
print_R($VC_left_time);
echo "<br>";



print_R("isNoticeDay11: ".($Today - $year_end1));
echo " | ";					
print_R("isNoticeDay22: ".($Today - $year_end2));
echo " | ";		
print_R("isNoticeDay33: ".($Today - $year_end3));
echo " | ";		
*/

                    if($diff_day <= 365){
					//if($diff_day < 365){

                        if($isNoticeDay3 && $VC_left_time > "4" && $year_end3 < $Today && $openstartday < $year_end3){

                            //회계일자 기준인원
                            //잔여연차 시간이 0.4 초과일경우
                            //연차촉진2차 고지일자(해당년도 10월31일) < 현재일자
                            $SET_vb_degree		= "3"; //고지차수
                            $SET_vb_notice_dt	= $year_end3; //고지알림일자
                            $SET_vb_etc_01		= iconv('utf-8', 'euc-kr', $year_end3_note);//비고1: 회계일자/1년이상/
                            
                        }else if($isNoticeDay2 && $VC_left_time > "4" && $year_end2 < $Today && $openstartday < $year_end2){

                            //회계일자 기준인원
                            //잔여연차 시간이 0.4 초과일경우
                            //연차촉진2차 고지일자(해당년도 10월31일) < 현재일자
                            $SET_vb_degree		= "2"; //고지차수
                            $SET_vb_notice_dt	= $year_end2; //고지알림일자
                            $SET_vb_etc_01		= iconv('utf-8', 'euc-kr', $year_end2_note);//비고1: 회계일자/1년이상/
                            
                        }else if($isNoticeDay1 && $VC_left_time > "4" && $year_end1 < $Today && $openstartday < $year_end1){

							//789789
							//회계일자 기준인원
                            //잔여연차 시간이 0.4 초과일경우
                            //연차촉진2차 고지일자(해당년도 7월1일) < 현재일자
                            $SET_vb_degree		= "1"; //고지차수
                            $SET_vb_notice_dt	= $year_end1; //고지알림일자
                            $SET_vb_etc_01		= iconv('utf-8', 'euc-kr', $year_end1_note);//비고1: 회계일자/1년이상/
                        }
                    }else{
                        //1년 이상
                        //if($isNoticeDay2 && $VC_left_time > "0.4" && $year_end2 < $Today && $openstartday < $year_end2){
						if($isNoticeDay2 && $VC_left_time > "4" && $year_end2 < $Today && $openstartday < $year_end2){					
                            //회계일자 기준인원
                            //잔여연차 시간이 0.4 초과일경우
                            //연차촉진2차 고지일자(해당년도 10월31일) < 현재일자
                            $SET_vb_degree		= "2"; //고지차수
                            $SET_vb_notice_dt	= $year_end2; //고지알림일자
                            $SET_vb_etc_01		= iconv('utf-8', 'euc-kr', $year_end2_note);//비고1: 회계일자/1년이상/
                            
                        }else if($isNoticeDay1 && $VC_left_time > "4" && $year_end1 < $Today && $openstartday < $year_end1 ){
                            //회계일자 기준인원
                            //잔여연차 시간이 0.4 초과일경우
                            //연차촉진2차 고지일자(해당년도 7월1일) < 현재일자
                            $SET_vb_degree		= "1"; //고지차수
                            $SET_vb_notice_dt	= $year_end1; //고지알림일자
                            $SET_vb_etc_01		= iconv('utf-8', 'euc-kr', $year_end1_note);//비고1: 회계일자/1년이상/
                        }
                    }

                 
                    /*
                     vb_member_no		사원번호
                     vb_notice_dt		고지일자(2022-06-30)
                     vb_degree			고지차수 (1/2/3..)
                     vb_create_year		연차생성연도
                     vb_create_month		생성월(해당컬럼 사용안함)
                     vb_status			상태(0:미제출, 1:고지기간, 3:결재중, 4:제출완료)
                     vb_use_day			고지일자 기준 사용한 연차(해당 발생연차기준)
                     vb_remaind_day		고지일자 기준_잔여연차_일수
                     vb_update_dt		수정일
                     vb_member_info		부서명,이름,직급(고지일자기준)
                     vb_entry_dt			입사일자
                     vb_etc_01			비고1: 회계일자/1년이상/
                     vb_etc_02			비고2: 촉진대상 연차의 발생일자
                     vb_etc_03			비고3: 촉진대상 연차의 발생연차 일수
                     vb_etc_04			비고4: 촉진대상 연차의 만료일자
                     vb_etc_05			비고5: 연차사용계획서 결재완료일자 기준의 실시간 잔여연차 UPDATE
                     */
                    
                    /*
                     // 기존년도, 차수에 사용자 삭제
                     $sql  = "
                     Delete From vacation_boost_tbl
                     Where
                     vb_member_no = '$re_MemberNo'
                     And
                     vb_degree = '$SET_vb_degree'
                     And
                     vb_create_year = '$this_year'
                     AND
                     vb_status = '0'
                     ";
                     */
//print_R("re_MemberNo:".$re_MemberNo."openstartday:".$openstartday."SET_vb_notice_dt:".$SET_vb_notice_dt);
					//$openstartday="2022-12-15";
					//&& $openstartday < $SET_vb_notice_dt
                    //if( ($SET_vb_notice_dt != "") && ($openstartday < $SET_vb_notice_dt) ){
					if($SET_vb_notice_dt != ""){
                        // 기존년도, 차수에 사용자 삭제
                        $sql  = "
								Delete From vacation_boost_tbl
								Where
									vb_member_no = '$re_MemberNo'
									And
									vb_degree = '$SET_vb_degree'
									And
									SUBSTR(vb_notice_dt,1,4)=SUBSTR('$SET_vb_notice_dt',1,4)
									AND
									vb_status = '0'
								";
                        
                                if($re_MemberNo == 'M20327'){
                                    // echo $sql."<br>";
                                }
                        $resource = mysql_query($sql, $db);
						if($SET_vb_notice_dt > 2022-12-15){
							$sql="
								insert into vacation_boost_tbl
								(
								vb_member_no,
								vb_notice_dt,
								vb_degree,
								vb_create_year,
								vb_create_month,
								vb_status,
								vb_use_day,
								vb_remaind_day,
								vb_update_dt,
								vb_member_info,
								vb_entry_dt,
								vb_etc_01, vb_etc_02, vb_etc_03, vb_etc_04, vb_etc_05
								)value(
								'$re_MemberNo',
								'$SET_vb_notice_dt',
								'$SET_vb_degree',
								'$this_year',
								'$SET_this_month',
								'0',
								'$VC_use_day',
								'$VC_left_day',
								now(),
								'$vb_member_info',
								'$VC_entry_date',
								'$SET_vb_etc_01','$VC_start_date','$VC_new_day','$VC_end_date',''); ";
//print_R($sql);
//echo"<br>";
                            if($re_MemberNo == 'M20327'){
                                // echo $sql;
                            }
							mysql_query($sql,$db);
						}
                    }
                }
                
                //============================================
                //$VC_vacation_gb = 1=입사일 기준
                
                }//if
                
        }//while
        
        $log_txt = date("Y-m-d H:i:s",time())." : BATCH : SUCCESS";//로그남기기 msg
        
    }else{
        $log_txt = date("Y-m-d H:i:s",time())." : BATCH : FAIL";//로그남기기 msg
    }
    
    //==============================================
    //로그남기기
    
    $log_file = "../log/boost_batch_baron.txt";
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

function date_diff($date1, $date2){
    $_date1 = explode("-",$date1);
    $_date2 = explode("-",$date2);
    
    $tm1 = mktime(0,0,0,$_date1[1],$_date1[2],$_date1[0]);
    $tm2 = mktime(0,0,0,$_date2[1],$_date2[2],$_date2[0]);
    
    return ($tm1 - $tm2) / 86400;
}

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