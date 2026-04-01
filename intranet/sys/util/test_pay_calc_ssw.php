<?
    // "approval auto count sync test";
	require('../../../SmartyConfig.php');
	include "../inc/dbcon.inc";
	include "./OracleClassMain.php";
    require_once($SmartyClassPath);
    $smarty = new Smarty($smarty);
    $oracle = new OracleClass($smarty);
    global $db;
    
    $log_file = "../log/test_pay_calc.txt";
    
    if(is_dir($log_file)){ $log_option = 'w';}
    else{ $log_option = 'a';}
    
    $log_file = fopen($log_file, $log_option);
    $sql = "select memberNo from member_tbl where workposition != '9'";
    $re = mysql_query($sql,$db);
    $yyymm = date("Ym");
    while($re_row = mysql_fetch_array($re)) {
        // echo json_encode($re_row);
        $memberNo = $re_row['memberNo'];
        $azsql = "
                    UPDATE hr_payx_result_mst 
                    SET 	HOURLY_WAGE  	= F_HR_GET_TIME_AMT_HM2('$memberNo', '$yyymm') 
                    WHERE  work_yymm = '$yyymm'
                    and	  emp_no		= '$memberNo'
                    and	  pay_kind		= 'P'
                ";
        // echo $azsql."<br>"; 
        $oracle ->ExcuteQuery($azsql);

        $thisMonth = date('Y-m');
        $timestamp = date('Y-m-d H:i:s');
        echo  $timestamp.": $memberNo : Done<br>";
        // -- 기초임금 UPDATE 230117
        // UPDATE hr_payx_result_mst 
        // SET 	HOURLY_WAGE  	= F_HR_GET_TIME_AMT_HM2(ls_emp_no,to_char(ld_work_ym, 'YYYYMM')) 
        // WHERE  work_yymm = to_char(ld_work_ym, 'YYYYMM')
        // and	  emp_no		= ls_emp_no
        // and	  pay_kind		= 'P'
        // ;	
        // COMMIT WORK ; 	
        // $azsql = "BEGIN PROC_HR_PAYX_CALC(); END;";
    
        // $result = mysql_query($sql,$db);
        $log_txt = $timestamp.": $memberNo : Done";
        
        // Batch 성공 로그 작성
        fwrite($log_file, $log_txt."\r\n");
    }
    fclose($log_file);
    $oracle->db_close_oracle();
?>  