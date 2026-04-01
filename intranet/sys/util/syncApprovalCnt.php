<?
    // "approval auto count sync test";
	require('../../../SmartyConfig.php');
	include "../inc/dbcon.inc";
	include "./OracleClassMain.php";
    require_once($SmartyClassPath);
    $smarty = new Smarty($smarty);
    $oracle = new OracleClass($smarty);
    global $db;
    
    // ERP Approval 데이터 호출 (퇴사자 X)
    $azsql = "BEGIN USP_MAIN_INIT_07_Summary(:entries); END;";
    $list_data = array();
    $list_data = $oracle->LoadProcedure($azsql,"datarow","");
    
    // Intranet 기존데이터 삭제 후 데이터 입력
    $del_sql = "DELETE FROM Approval_count_tbl ;";
    $del_re = mysql_query($del_sql,$db);
    if($del_re){
        $member_data = array();
        $sql = "INSERT INTO Approval_count_tbl ( MemberNo, ApprovalCnt ) VALUES ";
        for($i=0; $i<count($list_data); $i++) {
            array_push($member_data,$list_data[$i]);
            $satis_count = ((int)$list_data[$i]['item01']) + ((int)$list_data[$i]['item02']) + ((int)$list_data[$i]['item05']) + ((int)$list_data[$i]['item06']);
            if($i < count($list_data)-1)
                $sql .= "('".$list_data[$i]['emp_no']."', ".$satis_count." ), " ; 
            else
                $sql .= "('".$list_data[$i]['emp_no']."', ".$satis_count." ) ; " ; 
        }

        $result = mysql_query($sql,$db);

        $thisMonth = date('Y-m');
        $timestamp = date('Y-m-d H:i:s');
        $log_file = "../log/".$thisMonth."_SyncApprovalCnt.txt";
        
        if(is_dir($log_file)){ $log_option = 'w';}
        else{ $log_option = 'a';}
        
        $log_file = fopen($log_file, $log_option);
        $log_txt = $timestamp.": Batch : Success";
        
        // Batch 성공 로그 작성
        if ($result) fwrite($log_file, $log_txt."\r\n");
        fclose($log_file);
    }
    $oracle->db_close_oracle();

?>  