<?php

	//총괄기획실 업무 근태 분석 엑셀 데이터 생성 - 이병권

	/***************************************
	* 멤버 업무 일일 리스트	
	****************************************/
	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";
	include "../inc/function_timework.php";
	include "../util/HanamcPageControl.php";
	include "../../../SmartyConfig.php";

	require_once($SmartyClassPath);

	extract($_GET);

	class WorkCheck extends Smarty {


		function WorkCheck(){

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
		
		function ViewPage2(){
			global $db;
			extract($_REQUEST);

			$this->PrintExcelHeader02("2026_02_근태 현황 리스트2","excel");

			$start_date = '2026-01-01';
			$end_date   = '2026-02-01';
		
			$sql = "
					SELECT
						ctx.work_date,
						ctx.memberno,
						m.korName,

						ctx.raw_entry,
						ctx.raw_leave,
						ctx.raw_overtime,

						(
							SELECT sc.NAME
							FROM systemconfig_tbl sc
							WHERE sc.SysKey = 'PositionCode'
							  AND sc.CODE = COALESCE(
									(SELECT s1.CODE
									 FROM systemconfig_tbl s1
									 WHERE s1.SysKey = 'PositionCode'
									   AND s1.CODE = ctx.position_code
									 LIMIT 1),

									(SELECT s2.CODE
									 FROM systemconfig_tbl s2
									 WHERE s2.SysKey = 'PositionCode'
									   AND s2.CODE = RIGHT(ctx.position_code, 2)
									 LIMIT 1),

									(SELECT s3.CODE
									 FROM systemconfig_tbl s3
									 WHERE s3.SysKey = 'PositionCode'
									   AND s3.CODE = m.RankCode
									 LIMIT 1),

									(SELECT s4.CODE
									 FROM systemconfig_tbl s4
									 WHERE s4.SysKey = 'PositionCode'
									   AND s4.CODE = RIGHT(m.RankCode, 2)
									 LIMIT 1)
							  )
							LIMIT 1
						) AS PositionName,

						ctx.is_holiday,
						ctx.is_late,

						ctx.userstate_codes     AS userstate_code,
						ctx.userstate_names     AS userstate,
						ctx.userstate_sub_codes AS userstate_sub_code,

						/* 메인업무 : raw 기준 */
						COALESCE(NULLIF(ctx.base_project_code_raw, ''), ctx.base_project_code_alt) AS project_code,
						ctx.base_activity_code    AS activity_code,

						/* 메인업무 시간 : normal_cap - 추가업무(raw,N풀) 합 */
						CASE
							WHEN ctx.normal_cap_minutes IS NULL THEN NULL
							ELSE GREATEST(IFNULL(ctx.normal_cap_minutes, 0) - IFNULL(aw.sub_normal_total, 0), 0)
						END AS normal_minutes,

						/* 야근 프로젝트/업무 : raw 기준 */
						COALESCE(NULLIF(ctx.ot_project_code_raw, ''), ctx.ot_project_code_alt) AS overtime_project_code,
						ctx.ot_activity_code    AS overtime_activity_code,

						/* 실제 야근 시간 */
						ctx.real_overtime_minutes,

						/* 야근 시간 */
						IFNULL(ctx.ot_cap_minutes, 0) AS overtime_minutes,

						/* 전체 합계 */
						CASE
							WHEN ctx.normal_cap_minutes IS NULL AND ctx.ot_cap_minutes IS NULL THEN NULL
							ELSE (
								GREATEST(IFNULL(ctx.normal_cap_minutes, 0) - IFNULL(aw.sub_normal_total, 0), 0)
								+ IFNULL(ctx.ot_cap_minutes, 0)
							)
						END AS total_minutes,

						/* 추가업무 SEQ 1 */
						aw.project_code_1,
						aw.activity_code_1,
						aw.normal_minutes_1,

						/* 추가업무 SEQ 2 */
						aw.project_code_2,
						aw.activity_code_2,
						aw.normal_minutes_2,

						/* 추가업무 SEQ 3 */
						aw.project_code_3,
						aw.activity_code_3,
						aw.normal_minutes_3,

						/* 추가업무 SEQ 4 */
						aw.project_code_4,
						aw.activity_code_4,
						aw.normal_minutes_4,

						/* 추가업무 SEQ 5 */
						aw.project_code_5,
						aw.activity_code_5,
						aw.normal_minutes_5

					FROM mh_day_ctx_excel ctx

					LEFT JOIN member_tbl m
						ON m.MemberNo = ctx.memberno

					/* 추가업무(raw 기준, N풀만) */
					LEFT JOIN
					(
						SELECT
							r.memberno,
							r.work_date,

							SUM(IFNULL(r.normal_minutes, 0)) AS sub_normal_total,

							MAX(CASE WHEN r.new_seq = 1 THEN r.project_code_raw END)  AS project_code_1,
							MAX(CASE WHEN r.new_seq = 1 THEN r.activity_code END)     AS activity_code_1,
							SUM(CASE WHEN r.new_seq = 1 THEN IFNULL(r.normal_minutes, 0) ELSE 0 END) AS normal_minutes_1,

							MAX(CASE WHEN r.new_seq = 2 THEN r.project_code_raw END)  AS project_code_2,
							MAX(CASE WHEN r.new_seq = 2 THEN r.activity_code END)     AS activity_code_2,
							SUM(CASE WHEN r.new_seq = 2 THEN IFNULL(r.normal_minutes, 0) ELSE 0 END) AS normal_minutes_2,

							MAX(CASE WHEN r.new_seq = 3 THEN r.project_code_raw END)  AS project_code_3,
							MAX(CASE WHEN r.new_seq = 3 THEN r.activity_code END)     AS activity_code_3,
							SUM(CASE WHEN r.new_seq = 3 THEN IFNULL(r.normal_minutes, 0) ELSE 0 END) AS normal_minutes_3,

							MAX(CASE WHEN r.new_seq = 4 THEN r.project_code_raw END)  AS project_code_4,
							MAX(CASE WHEN r.new_seq = 4 THEN r.activity_code END)     AS activity_code_4,
							SUM(CASE WHEN r.new_seq = 4 THEN IFNULL(r.normal_minutes, 0) ELSE 0 END) AS normal_minutes_4,

							MAX(CASE WHEN r.new_seq = 5 THEN r.project_code_raw END)  AS project_code_5,
							MAX(CASE WHEN r.new_seq = 5 THEN r.activity_code END)     AS activity_code_5,
							SUM(CASE WHEN r.new_seq = 5 THEN IFNULL(r.normal_minutes, 0) ELSE 0 END) AS normal_minutes_5

						FROM
						(
							SELECT
								z.memberno,
								z.work_date,
								z.project_code_raw,
								z.activity_code,
								z.normal_minutes,
								@rownum := IF(@grp = CONCAT(z.memberno, '|', z.work_date), @rownum + 1, 1) AS new_seq,
								@grp := CONCAT(z.memberno, '|', z.work_date) AS grp_key
							FROM
							(
								SELECT
									a.memberno,
									a.work_date,
									a.project_code_raw,
									a.activity_code,
									SUM(a.minutes) AS normal_minutes,
									MIN(a.seq_no) AS min_seq
								FROM mh_addwork_trim_excel a
								LEFT JOIN mh_day_ctx_excel c
								  ON c.memberno = a.memberno
								 AND c.work_date = a.work_date
								WHERE a.work_date >= '$start_date'
								  AND a.work_date <  '$end_date'
								  AND a.pool = 'N'
								  AND a.seq_no BETWEEN 1 AND 5
								  AND NOT (
										IFNULL(a.project_code_raw, '') = IFNULL(c.base_project_code_raw, '')
									AND IFNULL(a.activity_code, '')    = IFNULL(c.base_activity_code, '')
								  )
								GROUP BY
									a.memberno,
									a.work_date,
									a.project_code_raw,
									a.activity_code
								ORDER BY
									a.memberno,
									a.work_date,
									min_seq,
									a.project_code_raw,
									a.activity_code
							) z
							CROSS JOIN (SELECT @rownum := 0, @grp := '') vars
						) r
						GROUP BY
							r.memberno,
							r.work_date
					) aw
						ON aw.memberno = ctx.memberno
					   AND aw.work_date = ctx.work_date

					WHERE ctx.work_date >= '$start_date'
					  AND ctx.work_date <  '$end_date'

					ORDER BY ctx.work_date, ctx.memberno";
			$re = mysql_query($sql, $db);
			$fulldata = array();
			while($re_row = mysql_fetch_assoc($re)){
				$EntryPCode = $re_row["project_code"];
				$OverPCode = $re_row["overtime_project_code"];
				$add1_project = $re_row["project_code_1"];
				$add2_project = $re_row["project_code_2"];
				$add3_project = $re_row["project_code_3"];
				$add4_project = $re_row["project_code_4"];
				$add5_project = $re_row["project_code_5"];

				if($re_row['overtime_project_code'] == '' || $re_row['overtime_project_code'] == null){
					$re_row['overtime_minutes'] = '';
				}

				if($re_row["is_holiday"] == '1'){
					$re_row["is_holiday"] = '주말';
				}else{
					$re_row["is_holiday"] = '';
				}

				if($re_row["is_late"] == '1'){
					$re_row["is_late"] = '지각';
				}else{
					$re_row["is_late"] = '';
				}

				$re_row['normal_minutes'] = $this->minToHHMM($re_row['normal_minutes']);

				if($re_row['normal_minutes_1'] == 0){
					$normal_minutes_1 = '';
				}else{
					$normal_minutes_1 = $this->minToHHMM($re_row['normal_minutes_1']);
				}
				$re_row['normal_minutes_1'] = $normal_minutes_1;

				if($re_row['normal_minutes_2'] == 0){
					$normal_minutes_2 = '';
				}else{
					$normal_minutes_2 = $this->minToHHMM($re_row['normal_minutes_2']);
				}
				$re_row['normal_minutes_2'] = $normal_minutes_2;

				if($re_row['normal_minutes_3'] == 0){
					$normal_minutes_3 = '';
				}else{
					$normal_minutes_3 = $this->minToHHMM($re_row['normal_minutes_3']);
				}
				$re_row['normal_minutes_3'] = $normal_minutes_3;

				if($re_row['normal_minutes_4'] == 0){
					$normal_minutes_4 = '';
				}else{
					$normal_minutes_4 = $this->minToHHMM($re_row['normal_minutes_4']);
				}
				$re_row['normal_minutes_4'] = $normal_minutes_4;

				if($re_row['normal_minutes_5'] == 0){
					$normal_minutes_5 = '';
				}else{
					$normal_minutes_5 = $this->minToHHMM($re_row['normal_minutes_5']);
				}
				$re_row['normal_minutes_5'] = $normal_minutes_5;

				if($re_row['real_overtime_minutes'] == 0){
					$real_overtime_minutes = '';
				}else{
					$real_overtime_minutes = $this->minToHHMM($re_row['real_overtime_minutes']);
				}
				$re_row['real_overtime_minutes'] = $real_overtime_minutes;

				if($re_row['overtime_minutes'] == 0){
					$overtime_minutes = '';
				}else{
					$overtime_minutes = $this->minToHHMM($re_row['overtime_minutes']);
				}
				$re_row['overtime_minutes'] = $overtime_minutes;
				//=== EntryPCode
				if(change_XXIS($EntryPCode)){
					$ProjectCodeXX = change_XX($EntryPCode);
					$sql2="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$EntryPCode' or ProjectCode='$ProjectCodeXX'";
				}
				else{
					$ProjectCodeXX ="";
					$sql2="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$EntryPCode'";
				}
		
				$re2= mysql_query($sql2,$db);
		
				$re_row["EntryPCodeView"]=@mysql_result($re2,0,"projectViewCode");
				$re_row["EntryPCodeProjectNickname"]=@mysql_result($re2,0,"ProjectNickname");
		
				//===OverPcode
				if(change_XXIS($OverPCode))	{
					$ProjectCodeXX = change_XX($OverPCode);
					$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$OverPCode' or ProjectCode='$ProjectCodeXX'";
				}
				else{
					$ProjectCodeXX = "";
					$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$OverPCode'";
				}
		
				$re3= mysql_query($sql3,$db);
		
				$re_row["OverPCodeView"]=@mysql_result($re3,0,"projectViewCode");
				$re_row["OverPCodeProjectNickname"]=@mysql_result($re3,0,"ProjectNickname");
		
				//===add1_project
				if(change_XXIS($add1_project))	{
					$ProjectCodeXX = change_XX($add1_project);
					$sql4="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add1_project' or ProjectCode='$ProjectCodeXX'";
				}
				else{
					$ProjectCodeXX ="";
					$sql4="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add1_project'";
				}
		
				$re4= mysql_query($sql4,$db);
		
				$re_row["add1_PCodeView"]=@mysql_result($re4,0,"projectViewCode");
				$re_row["add1_PCodeProjectNickname"]=@mysql_result($re4,0,"ProjectNickname");
		
				//===add2_project
				if(change_XXIS($add2_project))	{
					$ProjectCodeXX = change_XX($add2_project);
					$sql5="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add2_project' or ProjectCode='$ProjectCodeXX'";
				}
				else{
					$ProjectCodeXX ="";
					$sql5="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add2_project'";
				}
		
				$re5= mysql_query($sql5,$db);
		
				$re_row["add2_PCodeView"]=@mysql_result($re5,0,"projectViewCode");
				$re_row["add2_PCodeProjectNickname"]=@mysql_result($re5,0,"ProjectNickname");
		
				//===add3_project
				if(change_XXIS($add3_project))	{
					$ProjectCodeXX = change_XX($add3_project);
					$sql6="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add3_project' or ProjectCode='$ProjectCodeXX'";
				}
				else{
					$ProjectCodeXX ="";
					$sql6="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add3_project'";
				}
		
				$re6= mysql_query($sql6,$db);
		
				$re_row["add3_PCodeView"]=@mysql_result($re6,0,"projectViewCode");
				$re_row["add3_PCodeProjectNickname"]=@mysql_result($re6,0,"ProjectNickname");
		
				//===add4_project
				if(change_XXIS($add4_project))	{
					$ProjectCodeXX = change_XX($add4_project);
					$sql7="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add4_project' or ProjectCode='$ProjectCodeXX'";
				}
				else{
					$ProjectCodeXX ="";
					$sql7="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add4_project'";
				}
		
				$re7= mysql_query($sql7,$db);
		
				$re_row["add4_PCodeView"]=@mysql_result($re7,0,"projectViewCode");
				$re_row["add4_PCodeProjectNickname"]=@mysql_result($re7,0,"ProjectNickname");
		
				//===add5_project
				if(change_XXIS($add5_project))	{
					$ProjectCodeXX = change_XX($add5_project);
					$sql8="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add5_project' or ProjectCode='$ProjectCodeXX'";
				}
				else{
					$ProjectCodeXX ="";
					$sql8="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$add5_project'";
				}
		
				$re8= mysql_query($sql8,$db);
		
				$re_row["add5_PCodeView"]=@mysql_result($re8,0,"projectViewCode");
				$re_row["add5_PCodeProjectNickname"]=@mysql_result($re8,0,"ProjectNickname");

				array_push($fulldata, $re_row);
			}

			$this->assign('fulldata',$fulldata);
			$this->display("intranet/common_contents/work_check/work_check_Excel_mvc2.tpl");
		}
		//=========================================================//
		// 함수 영역
		//========================================================//
		
		function minToHHMM($min)
		{
			if ($min === null || $min === '') return '0:00';

			// 혹시 콤마 포함 문자열이 들어오는 경우 대비
			if (is_string($min)) {
				$min = str_replace(',', '', $min);
				$min = trim($min);
			}

			// 숫자만 캐스팅
			$min = (int)$min;
			if ($min < 0) $min = 0;

			$h = (int)floor($min / 60);
			$m = $min % 60;

			// 시간 천단위 콤마
			return number_format($h) . ':' . sprintf('%02d', $m);
		}

		function remainTime($total_minutes){
			$stnd_total_work_time = 8 * 60; // 8시간 = 480분
			$remaining_minutes = $stnd_total_work_time - $total_minutes;
			
			$final_hours = floor($remaining_minutes / 60); // 총 시
			$final_minutes = $remaining_minutes % 60;	// 남은 분
			
			// 4. 결과 출력
			$remaining_time_formatted = sprintf('%02d:%02d', $final_hours, $final_minutes);
			
			return $remaining_time_formatted;
		}

		function HangleEncodeUTF8_EUCKR($item)
		{
			$result=trim(ICONV("UTF-8","EUC-KR",$item));
			return $result;
		}
		
		function PrintExcelHeader02($filename,$excel)
		{
			$filename = $this->HangleEncodeUTF8_EUCKR($filename);
			if($excel != "")
			{
				header("Content-Type:application/vnd.ms-excel;charset=utf-8");
				header("Content-type:application/x-msexcel;charset=utf-8");
				header("Content-Disposition:attachment;filename=\"$filename.xls\"");
				header("Expires:0");
				header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
				header("Cache-Control:private",false);
			}
		
		}
}

// 끝
//==================================
?>