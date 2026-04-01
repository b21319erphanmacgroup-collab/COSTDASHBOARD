<?
//include "../../../SmartyConfig.php";
ini_set("memory_limit", -1);//메모리 제한 삭제
/***************************************
 * MYSQL 관련된 클래스
 * 생성일자 : 20180125
 * 생성자 : 문형석
 * ------------------------------------
 ****************************************/
extract($_GET);
class MysqlClass {

    var $connection;
    var $mysqli;
    //function MysqlClass($smarty)

    function MysqlClass()
    {
        //$this->smarty=$smarty;

        $this->connection = mysqli_connect('localhost','root','','hanmacerp');
        $this->mysqli = new mysqli('localhost','root','','hanmacerp');

        $db_hostname ='localhost';
        $db_database='hanmacerp';
        $db_username='root';
        $db_password='';

        if ($this->mysqli->connect_errno) {
            $this->remain_log('connect_errno',$SET_PROCEDURE_SQL.' : re_cnt='.$re_cnt);
        }

        mysqli_set_charset($this->connection, "utf8");
        mysqli_set_charset($this->mysqli, "utf8");
    }




    ///////////////////// ///////////////////////////////////
    Function Close_connection($mysql_con, $con_kind) 	{
// 			if($con_kind=="mysqli"){
// 				//$mysql_end = mysqli_close($mysql_con);
// 				$mysql_end = mysqli_close($mysql_con);
// 			}else{
// 				$mysql_end = mysql_close($mysql_con);
// 			}
// 			if($mysql_end){
// 				//정상종료
// 				return "1";
// 			}else{
// 				//종료실패
// 				return "2";
// 			}

    }//Close_connection

    Function remain_log($str_title,$str_content="",$str3="") 	{
        $cfile="../../log/".date("Y-m-d")."_".$str_title.".txt";
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
        $cond=$con.$str_content.' : '.date("Y-m-d H:i:s")." \n";
        fwrite($fp,$cond);
        fclose($fp);
        /* ---------------------- */
    }//remain_log


    ///////////////////// ///////////////////////////////////
    Function ClassFN_LoadData($setSql, $setAssignName, $setOutput="",  $setEtc="") 	{
        //include "../../inc/dbcon.inc";
        global $db;
        $returnData = array();
        $setAssignName=$setAssignName==""?"arrayData":$setAssignName;
        $ExecuteQuery=$setSql;
        $returnData=array();
        //------------------------------------------------
        $result2 = mysqli_query($this->connection, $ExecuteQuery);
        $result_row = mysqli_num_rows($result2);
        //------------------------------------------------
        if($result_row>0){
            //++++++++++++++++++++++++++++++++++++++++++++++++
            //컬럼명 get
            //쿼리 셀렉트 결과값을 반환시 지정한 별칭값(alias)이 아닌 [0],[1],[2]...배열명으로만 값이 담겨지기 때문에
            //별칭값(alias)의 결과값을 필요로 할 경우 사용할 수가 없기 때문에 이 작업이 필요함
            $arrayColNames=array();
            $finfo = mysqli_fetch_fields($result2);
            foreach ($finfo as $val) {
                //printf("Name:      %s\n",   $val->name);
                array_push($arrayColNames, $val->name);
// 					printf("Table:     %s\n",   $val->table);
// 					printf("Max. Len:  %d\n",   $val->max_length);
// 					printf("Length:    %d\n",   $val->length);
// 					printf("charsetnr: %d\n",   $val->charsetnr);
// 					printf("Flags:     %d\n",   $val->flags);
// 					printf("Type:      %d\n\n", $val->type);
            }
            //++++++++++++++++++++++++++++++++++++++++++++++++


            //++++++++++++++++++++++++++++++++++++++++++++++++
            $i=0;
            while($row = mysqli_fetch_array($result2))
            {
                $row[$arrayColNames[$i]]=$row[$i];
                array_push($returnData, $row);
                $i++;
            }
            //++++++++++++++++++++++++++++++++++++++++++++++++

            //++++++++++++++++++++++++++++++++++++++++++++++++*
            //mysqli_query:실패하면 FALSE를 반환합니다. SELECT, SHOW, DESCRIBE 또는 EXPLAIN 쿼리가 성공하면 mysqli_query ()는 mysqli_result 객체를 리턴한다. 성공한 다른 쿼리의 경우 mysqli_query ()는 TRUE를 반환합니다.

            //print_r($returnData);
            //echo "<br>--------------------<br>";

            if(strtolower($setOutput)=="array"){
                return $returnData;

            }else if(strtolower($setOutput) == "json"){

                //echo json_encode($returnData);
                return json_encode($returnData);

            }else if(strtolower($setOutput) == "print"){
                print_r($returnData);

            }else if(strtolower($setOutput) == "print_json"){
                print_r($returnData);

// 				}else if(strtolower($setOutput) == "assign"){
// 					$this->smarty->assign($setAssignName,$returnData);
            }else{
                //assign
                //$this->smarty->assign($setAssignName,$returnData);
                return $returnData;
            }

        }else{

            //echo "결과값이 없습니다.<br>--------------------<br>";
        }



// 			$result = mysql_query($setSql,$db);

// 			$resultnum = mysql_num_rows($result);
// 			if($resultnum > 0){
// 				while($result_row = mysql_fetch_array($result)){
// 					array_push($returnData,$result_row);
// 					//--------------------------------------------------------------
// 				}//while

// 				if(strtolower($setOutput)=="array"){
// 					return $returnData;
// 				}else if(strtolower($setOutput) == "json" || strtolower($setOutput) == "ajax"){
// 					print_r(json_encode($returnData));
// 				}else{
// 					//assign
// 					$smarty_param->smarty->assign($setAssignName,$returnData);
// 				}
// 			}else{}

    }//ClassFN_LoadData




    function ClassFN_LoadData_Procedure($SET_PROCEDURE_SQL,$ETC="")
    {
        $PROCEDURE_SQL = $SET_PROCEDURE_SQL;
// 			$mysqli   = $this->mysqli;
// 			$result   = $mysqli->query($PROCEDURE_SQL);



        //$result   = $this->mysqli->query($PROCEDURE_SQL);
        $num=1;
        $ret = array();
        $re_cnt=0;

        if ($result = mysqli_query($this->connection, $PROCEDURE_SQL)) {

            $re_cnt =  mysqli_num_rows($result);

            while($data = $result->fetch_assoc()){
                if($ETC == 'w2ui'){
                    $data["recid"] = $num;
                }

                array_push($ret, $data);
                $num++;
            }//while
            //$result->free();
            $result->close();



            /* free result set */
            // mysqli_free_result($result);
        }

        //mysqli_free_result($result);

        if($ETC=="NEXT"){ //셀렉트 프로시저 연속사용시
            mysqli_next_result($this->connection);
        }else{
            mysqli_close($this->connection);

        }


        //$this->mysqli->close();

        //$this->remain_log('ClassFN_LoadData_Procedure',$SET_PROCEDURE_SQL.' : re_cnt='.$re_cnt);
        if($ETC=="log"){
            //$this->remain_log('ClassFN_LoadData_Procedure',$SET_PROCEDURE_SQL.' : re_cnt='.$re_cnt);

            //$con_re = $this->Close_connection($this->mysqli, 'mysqli');
            //$this->remain_log('Close_connection',$con_re);
        }

        //print_r($ret);
        //echo "<br>--------------------<br>";
        return $ret;
    }//ClassFN_LoadData : 20180124 by Moon


    function ClassFN_LoadData_Procedure_back($SET_PROCEDURE_SQL,$ETC="")
    {
        $PROCEDURE_SQL = $SET_PROCEDURE_SQL;
        $mysqli   = $this->mysqli;
        $result   = $mysqli->query($PROCEDURE_SQL);
        $num=1;
        $ret = array();
        if($result){
            while($data = $result->fetch_assoc()){
                if($ETC == 'w2ui'){
                    $data["recid"] = $num;
                }

                array_push($ret, $data);
                $num++;
            }//while
            $result->free();
        }//if

        if($ETC=="log"){
            $add_msg= "";
            if($ret[0][SPATIAL_REQUST_CD]!=""){
                $add_msg=$ret[0][SPATIAL_REQUST_CD];
            }
            $this->remain_log('ClassFN_LoadData_Procedure',$SET_PROCEDURE_SQL.' : '.$add_msg);

            $con_re = $this->Close_connection($this->connection, 'mysqli');

            $this->remain_log('Close_connection',$con_re);
        }

        //print_r($ret);
        //echo "<br>--------------------<br>";
        return $ret;
    }//ClassFN_LoadData : 20180124 by Moon


    function ClassFN_ExecuteQuery_Procedure($SET_PROCEDURE_SQL,$ETC="")
    {

        if($ETC=="utf8"){
            $PROCEDURE_SQL = $this->HangleEncodeUTF8_EUCKR($SET_PROCEDURE_SQL);
        }else{

            $PROCEDURE_SQL = $SET_PROCEDURE_SQL;
        }

        $Return_result = "1";
        $results1 = $this->mysqli->query($SET_PROCEDURE_SQL);
        $results2 = $this->mysqli->query("SELECT @OUT_RESULT_SUCCESS");
// 			$results1 = $this->connection->query($SET_PROCEDURE_SQL);
// 			$results2 = $this->connection->query("SELECT @OUT_RESULT_SUCCESS");
        $num_rows = $results2->num_rows;
        if ($num_rows > 0) {
            while($row = $results2->fetch_object()){
                $result_query = $row->{"@OUT_RESULT_SUCCESS"};
                if($result_query==1){
                    //echo "쿼리 정상실행!";
                }else{
                    //echo "쿼리 실행 실패!";
                    $Return_result=$result_query; //DB내에서 트랜젝션 처리 ROLL BACK
                    break;
                }
            }
        }
        return $Return_result;
    }//FN_ExecuteQuery : 20180124 by Moon


    function ClassFN_ExecuteQuery_Procedure_one($SET_PROCEDURE_SQL,$ETC="")
    {

        if($ETC=="utf8"){
            $PROCEDURE_SQL = $this->HangleEncodeUTF8_EUCKR($SET_PROCEDURE_SQL);
        }else{

            $PROCEDURE_SQL = $SET_PROCEDURE_SQL;
        }

        $Return_result = "1";
        if($this->mysqli->query($PROCEDURE_SQL)){
            return "1";
        }else{

            return "db fail";
        }

    }//FN_ExecuteQuery : 20180124 by Moon



    function ClassFN_LoadData_old($Set_kind,$Set_ReturnType,$Param_01,$Param_02="",$Param_03="",$Param_04="",$Param_05="",$Param_06="",$Param_07="",$Param_08="",$Param_09="",$Param_10="")
    {
        $Set_ReturnType=$Set_ReturnType=="array"?"":""; //array/json/assign:ArrayData
        $ExecuteQuery="";
        $ReturnArray=array();
        //------------------------------------------------

        //ClassFN_ExecuteQuery('01','$safety_project_id','$input_item_01','$input_item_01);
        $MakeQuery = GetReferenceQuery($Set_kind, '');
        $ExecuteQuery =sprintf($MakeQuery,$Param_01,$Param_02,$Param_03,$Param_04,$Param_05,$Param_06,$Param_07,$Param_08,$Param_09,$Param_10);

        //echo $ExecuteQuery;
        $result2 = mysqli_query($this->connection, $ExecuteQuery);
        $result_row = mysqli_num_rows($result2);

        if($result_row>0){

            $row7 = mysqli_fetch_assoc($result2);
            $Array_fieldNames = array_keys($row7);


            if ($result = mysqli_query($this->connection, $ExecuteQuery)) {
                /* fetch associative array */
                while ($row = mysqli_fetch_assoc($result)) {



                    array_push($ReturnArray, $row);
                }
            }

            //mysqli_query:실패하면 FALSE를 반환합니다. SELECT, SHOW, DESCRIBE 또는 EXPLAIN 쿼리가 성공하면 mysqli_query ()는 mysqli_result 객체를 리턴한다. 성공한 다른 쿼리의 경우 mysqli_query ()는 TRUE를 반환합니다.

            //echo print_r($ReturnArray);
            //echo "<br>--------------------<br>";
        }else{

            //echo "결과값이 없습니다.<br>--------------------<br>";
        }

        return $ReturnArray;

    }//ClassFN_LoadData : 20180124 by Moon



    function ClassFN_ExecuteQuery_old($ArrayData,$ArrayItem, $Set_kind="")
    {
        $azsql_param="";
        if(count($ArrayItem)>0){
            for($j = 0; $j < count($ArrayItem); $j++){
                $azsql_param .= " '".$ArrayData[$ArrayItem[$j]]."' , ";
            }
        }
        $procedureName = 'PROC_SAFETY_CALENDAR_01_UP';
        $procedure = "CALL $procedureName($azsql_param @OUT_RESULT_SUCCESS)";
        //echo $procedure;
        $results1 = $this->connection->query($procedure);
        $results2 = $this->connection->query("SELECT @OUT_RESULT_SUCCESS");
        $num_rows = $results2->num_rows;
        if ($num_rows > 0) {
            while($row = $results2->fetch_object())
            {
                $result_query = $row->{"@OUT_RESULT_SUCCESS"};
                if($result_query==1){
                    //echo "쿼리 정상실행!";
                }else{
                    //echo "쿼리 실행 실패!";
                }
            }
        }
    }//FN_ExecuteQuery : 20180124 by Moon




    function HangleEncodeUTF8_EUCKR($item)
    {
        $result=trim(ICONV("UTF-8","EUC-KR",$item));
        return $result;
    }

    function HangleEncode($item)
    {		$result=trim(ICONV("EUC-KR","UTF-8",$item));
        if(trim($result)=="") 	$result="&nbsp";
        return $result;
    }

    function HangleEncodeAjax($item)
    {		$result=trim(ICONV("EUC-KR","UTF-8",$item));
        return $result;
    }

    function bear3StrCut($str,$len,$tail="..."){
        $rtn = array();
        return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
    }

    function resizeString($Str, $size, $addStr="...")  {
        if(mb_strlen($Str, "UTF-8") > $size) return mb_substr($Str, 0, $size, "UTF-8").$addStr;
        else return $Str;
    }




}
?>