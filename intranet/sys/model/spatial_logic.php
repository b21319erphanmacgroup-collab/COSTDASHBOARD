<?php
	/***************************************
	* 
	* ------------------------------------
	****************************************/
	include "../inc/dbcon_spatial.inc";
	
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";

	include "../util/MysqlClass_spatial.php";
	
	extract($_GET);
	class SpatialLogic {
		var $smarty;
		function SpatialLogic($smarty)
		{
			$this->smarty=$smarty;
			$this->mysql=new MysqlClass($smarty);
		}
  
		
		
		//====================================================
		//다운로드 받을 파일 갯수
		function SPATIAL_CH_STTUS_DW()
		{
			extract($_REQUEST);
			//	echo "11111";
				
			/*
			 * 		http://61.98.205.244:8080/spatial/sys/controller/location/Location_controller.php?
					ActionMode=SCREEN_01
					&MainAction=Ajax_01
					&SubAction=UPDATE
					&SPATIAL_REQUST_CD=요청자사번
					&SPATIAL_REQUST_TM=요청시간
					&SPATIAL_DW_STTUS=다운로드 상태값=Y
			 */
			$SET_PROCEDURE_SQL = "CALL USP_SPATIAL_INFO_UP('$SPATIAL_REQUST_CD','$SPATIAL_REQUST_TM','$SPATIAL_DW_STTUS');";
			$datarow =  $this->mysql->ClassFN_LoadData_Procedure($SET_PROCEDURE_SQL,'');
		
			//$REMAIN_DW_COUNT = $datarow[0][item01];
		
			echo "1";
			//echo $SET_PROCEDURE_SQL;
				
		}
		
		
		//====================================================
		//사용자별 스킨타입
		function SPATIAL_CHECK_SKIN_TYPE()
		{
			extract($_REQUEST);
			//	echo "11111";
			$SET_PROCEDURE_SQL = "CALL USP_SPATIAL_SKIN_CHECK('$myinfo_memberID')";
			$datarow =  $this->mysql->ClassFN_LoadData_Procedure($SET_PROCEDURE_SQL,'');
		
			echo json_encode($datarow[0]);
				
		}//SPATIAL_CHECK_SKIN_TYPE
		
		
		
		
		//====================================================
		//다운로드 받을 파일 갯수
		function REMAIN_DW_COUNT()
		{
			extract($_REQUEST);
		//	echo "11111";
			
			$SET_PROCEDURE_SQL = "CALL USP_SPATIAL_REMAIN_DW_COUNT('$myinfo_memberID')";
			$datarow =  $this->mysql->ClassFN_LoadData_Procedure($SET_PROCEDURE_SQL,'');
				
			$REMAIN_DW_COUNT = $datarow[0][item01];
				
			echo $REMAIN_DW_COUNT;
			//echo $SET_PROCEDURE_SQL;
			
		}	
		
		//====================================================
		//공간정보 다운로드 받아야할 파일 존재시 파일상태
		function STATUS_CHECK()
		{
			extract($_REQUEST);
		//	echo "11111";
		
			$SET_PROCEDURE_SQL = "CALL USP_SPATIAL_STATUS_CHECK('$myinfo_memberID')";
			$datarow =  $this->mysql->ClassFN_LoadData_Procedure($SET_PROCEDURE_SQL,'');
				
			$SPATIAL_REQUST_CD		= $datarow[0][item01];
			$SPATIAL_REQUST_TM		= $datarow[0][item02];
			$SPATIAL_PJT_NM			= $datarow[0][item03];
			$SPATIAL_MBR_INFO_01	= $datarow[0][item04];
			$SPATIAL_MBR_INFO_02	= $datarow[0][item05];
			$SPATIAL_MBR_INFO_03	= $datarow[0][item06];
			$SPATIAL_MBR_INFO_04	= $datarow[0][item07];
			$SPATIAL_MBR_INFO_05	= $datarow[0][item08];
			$SPATIAL_LATLNG			= $datarow[0][item09];
			$SPATIAL_FL_STTUS		= $datarow[0][item10];
			$SPATIAL_DW_STTUS		= $datarow[0][item11];
			$SPATIAL_URL			= $datarow[0][item12];
			$SPATIAL_DW_STR			= $datarow[0][item13];
			$SPATIAL_DW_END			= $datarow[0][item14];
				
			
			if($myinfo_memberID=="B14306" || myinfo_memberID=="M20104"){
//  				echo $SET_PROCEDURE_SQL;
//  				echo $datarow[0];
//  				print_r($datarow[0]);
				
			}
			//echo $datarow[0];
			//print_r($datarow[0]);
			$re_cnt = count($datarow);
			
			if($re_cnt>1){
				$datarow[0][item01]="99";
				echo json_encode($datarow[0]);
			
			}else{
				echo json_encode($datarow[0]);
				
			}
		}//STATUS_CHECK	
		
		
		function STATUS_PING()
		{
			extract($_REQUEST);
			
			$domain="110.8.170.20";
		    $starttime = microtime(true);
		    
		    $file      = fsockopen ($domain,201, $errno, $errstr, 10);
		   // $file      = fsockopen ($domain,1234, $errno, $errstr, 10);
		    
		    $stoptime  = microtime(true);
		    $status    = "";
		    if(!$file)
		    {
		       // $status = -1;
		       $status = "pingfailfail";
		    }
		    else
		    {
// 		        fclose($file);
// 		        $status = ($stoptime - $starttime) * 1000;
// 		        $status = floor($status);
		    	$status = "success";
		    }
			echo $status;
		}
		
		function InsertPage()
		{}//function InsertPage()




		
		
		function InsertAction()
		{}//function InsertAction()

		
		
			
		function bear3StrCut($str,$len,$tail="..."){ 
			$rtn = array(); 
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str; 
		}

		
		
		function Web_soket(){	
			
			$this->smarty->display("intranet/common_layout/web_soket_test.tpl");
		}

}



