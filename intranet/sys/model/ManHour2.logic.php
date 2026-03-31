<?php
	include "../../sys/inc/dbcon.inc";
	
	require_once($SmartyClassPath);

	extract($_GET);

	class ManhourLogic extends Smarty{

		function ManhourLogic($smarty)
		{
			$this->smarty=$smarty;
			$ActionMode=$_REQUEST['ActionMode'];
			$this->smarty->assign('ActionMode',$ActionMode);
		}

		function Manhour_my()
		{
			global $db;
			extract($_REQUEST);

				$memberNo = isset($_GET['MemberNo']) ? trim($_GET['MemberNo']) : '';

				$sql = "Select memberNo, korName From member_tbl where memberNo = '{$memberNo}'";
				$re = mysql_query($sql,$db);

				while($re_row = mysql_fetch_array($re)){
					$memberNo = $re_row['memberNo'];
					$korName = $re_row['korName'];
				}

				//$sql2 = "Select year(work_date) as y From mh_project_minutes Where memberno = '{$memberNo}' Group by YEAR(work_date)";
				$sql2 = "Select year(WorkDate) as y From mh_day_alltime Where MemberNo = '{$memberNo}' Group By YEAR(WorkDate)";
				$re2 = mysql_query($sql2,$db);

				$years = array();

				while($re_row2 = mysql_fetch_array($re2)){
					$years[] = $re_row2['y'];
				}

				$caseCols = array();
				foreach($years as $y){
					//$caseCols[] = "SUM(CASE WHEN YEAR(work_date) = {$y} THEN (normal_minutes + overtime_minutes) ELSE 0 END) AS y{$y}";
					$caseCols[] = "SUM(CASE WHEN YEAR(WorkDate) = {$y} THEN (WorkMinutes) ELSE 0 END) AS y{$y}";
				}

				$caseSql = implode(",\n", $caseCols);
                /*
				$sql3 = "SELECT
							m.project_code,
							COALESCE(
								p1.ProjectName,   -- 1) project_tbl.ProjectCode 정확히 일치
								p2.ProjectName,   -- 2) project_tbl.NewProjectCode 일치
								p3.ProjectName,   -- 3) 파생코드 치환 후 project_tbl.ProjectCode 매칭

								t1.ProjectName,   -- 4) project_temp_tbl.ProjectCode 정확히 일치

								m.project_code
							) AS project_name,
							{$caseSql},
							SUM(m.normal_minutes + m.overtime_minutes) AS total_min
						FROM mh_project_minutes m

						LEFT JOIN project_tbl p1
							ON p1.ProjectCode = m.project_code
						LEFT JOIN project_tbl p2
							ON p2.NewProjectCode = m.project_code
						LEFT JOIN project_tbl p3
							ON p3.ProjectCode =
							   CASE
								 WHEN m.project_code REGEXP '^[A-Za-z][0-9]{2}-'
								 THEN CONCAT(LEFT(m.project_code,1),'XX',SUBSTRING(m.project_code,4))
								 ELSE NULL
							   END

						LEFT JOIN project_temp_tbl t1
							ON t1.ProjectCode = m.project_code

						WHERE m.memberno = '{$memberNo}'
						AND m.project_code IS NOT NULL
						AND TRIM(m.project_code) <> ''
						GROUP BY m.project_code
						ORDER BY total_min DESC, m.project_code ASC";
                */
				$sql3 = "SELECT
                            m.ProjectCode,
                            COALESCE(
                                p1.ProjectName,
                                t1.ProjectName,
                                m.ProjectCode
                            ) AS ProjectName,
                            {$caseSql},
                            SUM(m.WorkMinutes) AS total_min
                        FROM mh_day_alltime m
                        LEFT JOIN project_tbl p1
                            ON p1.NewProjectCode = m.ProjectCode
                        LEFT JOIN project_temp_tbl t1
                            ON t1.ProjectCode = m.ProjectCode
                        WHERE m.MemberNo = '{$memberNo}'
                        AND m.ProjectCode NOT LIKE 'BV009%'
                        AND m.ProjectCode IS NOT NULL
                        AND m.ProjectCode <> ''
                        GROUP BY m.ProjectCode
                        ORDER BY total_min DESC, m.ProjectCode ASC";
				$re3 = mysql_query($sql3,$db);

				$query_data3 = array();

				while($re_row3 = mysql_fetch_array($re3))
				{
					$item = array(
						'project_code' => $re_row3['ProjectCode'],
						'project_name' => $re_row3['ProjectName'],

						'total_min_raw' => (int)$re_row3['total_min'],
						'total_min'  => $this->fmtNum($re_row3['total_min']),
						'total_hour' => $this->minToHHMM($re_row3['total_min']),

						'year_minutes'      => array(), // 화면표시용(콤마)
						'year_minutes_raw'  => array(), // 계산용(숫자)
						'year_hour'         => array(),
						'year_minutes_json' => '',      // data-attribute 용(JSON 문자열)
					);

					foreach($years as $y){
						$k = 'y'.$y;

						// 원본 분(min) 값: 숫자
						$raw = isset($re_row3[$k]) && $re_row3[$k] !== '' ? (int)$re_row3[$k] : 0;

						// 화면표시용
						$item['year_minutes'][$y] = $this->fmtNum($raw);
						$item['year_hour'][$y]    = $this->minToHHMM($raw);

						// 도넛 계산용
						$item['year_minutes_raw'][$y] = $raw;
					}

					// 도넛 계산용 JSON (HTML attribute 안전하게)
					$item['year_minutes_json'] = htmlspecialchars(
						json_encode($item['year_minutes_raw']),
						ENT_QUOTES,
						'UTF-8'
					);

					$query_data3[] = $item;
				}
                /*
				$sql4 = "SELECT
							m.project_code,
							COALESCE(
								p1.ProjectName,   -- 1) project_tbl.ProjectCode 정확히 일치
								p2.ProjectName,   -- 2) project_tbl.NewProjectCode 일치
								p3.ProjectName,   -- 3) 파생코드 치환 후 project_tbl.ProjectCode 매칭

								t1.ProjectName,   -- 4) project_temp_tbl.ProjectCode 정확히 일치

								m.project_code
							) AS project_name,
							{$caseSql},
							SUM(m.normal_minutes + m.overtime_minutes) AS total_min
						FROM mh_project_minutes m

						LEFT JOIN project_tbl p1
							ON p1.ProjectCode = m.project_code
						LEFT JOIN project_tbl p2
							ON p2.NewProjectCode = m.project_code
						LEFT JOIN project_tbl p3
							ON p3.ProjectCode =
							   CASE
								 WHEN m.project_code REGEXP '^[A-Za-z][0-9]{2}-'
								 THEN CONCAT(LEFT(m.project_code,1),'XX',SUBSTRING(m.project_code,4))
								 ELSE NULL
							   END

						LEFT JOIN project_temp_tbl t1
							ON t1.ProjectCode = m.project_code

						WHERE m.memberno = '{$memberNo}'
						AND m.project_code IS NOT NULL
						AND TRIM(m.project_code) <> ''
						GROUP BY m.project_code
						ORDER BY total_min ASC";
                */
                $sql4 ="SELECT
                            m.ProjectCode,
                            COALESCE(
                                p1.ProjectName,
                                t1.ProjectName,
                                m.ProjectCode
                            ) AS project_name,
                            {$caseSql},
                            SUM(m.WorkMinutes) AS total_min
                        FROM mh_day_alltime m
                        LEFT JOIN project_tbl p1
                            ON p1.NewProjectCode = m.ProjectCode
                        LEFT JOIN project_temp_tbl t1
                            ON t1.ProjectCode = m.ProjectCode
                        WHERE m.MemberNo = '{$memberNo}'
                        AND m.ProjectCode IS NOT NULL
                        AND m.ProjectCode <> ''
                        GROUP BY m.ProjectCode
                        ORDER BY total_min ASC";
				$re4 = mysql_query($sql4,$db);

				$donut = array();
				$usedIdx = array();
				while($r = mysql_fetch_array($re4)){
					$code = $r['project_code'];
					$name = $r['project_name'];
					$min  = (int)$r['total_min'];

					$donut[] = array(
						'label'      => $name,
						'value'      => $min,
						'value_hour' => $this->minToHHMM($min),
						'color'      => $this->colorFromCode($code, $usedIdx),
					);
				}

				$grandTotalMin = 0;
				for ($i=0; $i<count($donut); $i++) {
					$grandTotalMin += (int)$donut[$i]['value'];
				}

				$legend = array();
				$stops  = array();
				$accPct = 0.0;

				for ($i=0; $i<count($donut); $i++) {
					$name  = $donut[$i]['label'];
					$min   = (int)$donut[$i]['value'];
					$color = $donut[$i]['color'];

					if ($min <= 0) continue;

					$pct = ($grandTotalMin > 0) ? ($min * 100.0 / $grandTotalMin) : 0.0;
					$pctDisp = round($pct, 1);

					$legend[] = array(
						'label'     => $name,
						'min'       => $min,
						'min_hhmm'  => $this->minToHHMM($min),
						'pct'       => $pctDisp,
						'color'     => $color,
					);

					$start = $accPct;
					$end   = $accPct + $pct;
					$stops[] = $color . ' ' . $start . '% ' . $end . '%';
					$accPct = $end;
				}

				// 오차로 100%가 안 맞으면 마지막 구간을 100%로 맞춤
				if (!empty($stops) && $accPct < 100.0) {
					$lastIdx = count($stops) - 1;
					// 마지막 stop의 "start% end%"에서 end%만 100으로 교체
					$stops[$lastIdx] = preg_replace('/% [0-9.]+%$/', '% 100%', $stops[$lastIdx]);
				}

				$pieCss = 'conic-gradient(' . implode(', ', $stops) . ')';
				$donut_total_hhmm = $this->minToHHMM($grandTotalMin);

				$dir = dirname(__FILE__) . "/../log";
				if (!is_dir($dir)) {
					@mkdir($dir, 0777, true);
				}

				$cfile = $dir . "/" . date("Y-m") . "_ManHour2.txt";

				$korNameSafe  = str_replace(array("\r", "\n"), ' ', $korName);
				$sKorNameSafe = str_replace(array("\r", "\n"), ' ', $_SESSION['korName']);

				$log = date("Y-m-d H:i") . " / " . $sKorNameSafe . " / " . $korNameSafe . "-" . $memberNo . "-" . $_SERVER['REMOTE_ADDR'] . "\n";

				$fp = fopen($cfile, "a");     // append
				if ($fp) {
					if (function_exists("flock")) {
						flock($fp, LOCK_EX);
					}
					fwrite($fp, $log);
					if (function_exists("flock")) {
						flock($fp, LOCK_UN);
					}
					fclose($fp);
				}

				$this->smarty->assign('years',$years);
				$this->smarty->assign('memberNo',$memberNo);
				$this->smarty->assign('korName',$korName);
				$this->smarty->assign('query_data3',$query_data3);
				$this->smarty->assign('donut', $donut);
				$this->smarty->assign('legend', $legend);
				$this->smarty->assign('grandTotalMin', $grandTotalMin);
				$this->smarty->assign('pieCss', $pieCss);
				$this->smarty->assign('donut_total_hhmm', $donut_total_hhmm);
				$this->smarty->display("intranet/common_contents/work_manhour2/ManHour_my.tpl");
		}

		function Manhour_Project(){
			global $db;

			$year = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : 2026;
			if ($year < 2000 || $year > 2100) {
				$year = 2026;
			}

			$startDate = $year . '-01-01';
			$endDate   = ($year + 1) . '-01-01';

			$startDateEsc = mysql_real_escape_string($startDate);
			$endDateEsc   = mysql_real_escape_string($endDate);

			/* 1. 프로젝트 개수 */
			$total_projectCount = 0;
			$sql = "
				SELECT COUNT(DISTINCT project_code) AS project_count
				FROM mh_project_minutes
				WHERE work_date >= '" . $startDateEsc . "'
				  AND work_date <  '" . $endDateEsc . "'
				  AND project_code IS NOT NULL
				  AND project_code <> ''
			";
			$re = mysql_query($sql, $db);
			if ($re) {
				$row = mysql_fetch_assoc($re);
				if ($row && isset($row['project_count'])) {
					$total_projectCount = (int)$row['project_count'];
				}
			}

			/* 2. 참여 인원 수 */
			$total_memberCount = 0;
			$sql2 = "
				SELECT COUNT(DISTINCT memberno) AS member_count
				FROM mh_project_minutes
				WHERE work_date >= '" . $startDateEsc . "'
				  AND work_date <  '" . $endDateEsc . "'
				  AND project_code IS NOT NULL
				  AND project_code <> ''
			";
			$re2 = mysql_query($sql2, $db);
			if ($re2) {
				$row2 = mysql_fetch_assoc($re2);
				if ($row2 && isset($row2['member_count'])) {
					$total_memberCount = (int)$row2['member_count'];
				}
			}

			/* 3. 총 업무시간 */
			$totalMinutes = 0;
			$sql3 = "
				SELECT SUM(normal_minutes + overtime_minutes) AS total_minutes
				FROM mh_project_minutes
				WHERE work_date >= '" . $startDateEsc . "'
				  AND work_date <  '" . $endDateEsc . "'
				  AND project_code IS NOT NULL
				  AND project_code <> ''
			";
			$re3 = mysql_query($sql3, $db);
			if ($re3) {
				$row3 = mysql_fetch_assoc($re3);
				if ($row3 && isset($row3['total_minutes'])) {
					$totalMinutes = (int)$row3['total_minutes'];
				}
			}

			$totalHoursOnly = floor($totalMinutes / 60);
			$remainMinutes  = $totalMinutes % 60;

			/* 4. 프로젝트 목록 */
			$projectList = array();

			$sql4 = "
				SELECT 
					m.project_code,
					COALESCE(
						p1.ProjectName,
						p2.ProjectName,
						p3.ProjectName,
						t1.ProjectName,
						m.project_code
					) AS project_name,
					COUNT(DISTINCT m.memberno) AS member_count,
					SUM(m.normal_minutes + m.overtime_minutes) AS total_minutes
				FROM mh_project_minutes m
				LEFT JOIN project_tbl p1
					ON p1.ProjectCode = m.project_code
				LEFT JOIN project_tbl p2
					ON p2.NewProjectCode = m.project_code
				LEFT JOIN project_tbl p3
					ON p3.ProjectCode = CASE
						WHEN m.project_code REGEXP '^[A-Za-z][0-9]{2}-'
						THEN CONCAT(LEFT(m.project_code,1), 'XX', SUBSTRING(m.project_code,4))
						ELSE NULL
					END
				LEFT JOIN project_temp_tbl t1
					ON t1.ProjectCode = m.project_code
				WHERE m.work_date >= '" . $startDateEsc . "'
				  AND m.work_date <  '" . $endDateEsc . "'
				  AND m.project_code IS NOT NULL
				  AND m.project_code <> ''
				GROUP BY m.project_code
				ORDER BY total_minutes DESC, m.project_code ASC
			";
			$re4 = mysql_query($sql4, $db);
			if ($re4) {
				while ($re_row4 = mysql_fetch_assoc($re4)) {
					$projectCode   = isset($re_row4['project_code']) ? $re_row4['project_code'] : '';
					$projectName   = isset($re_row4['project_name']) ? $re_row4['project_name'] : '';
					$memberCount   = isset($re_row4['member_count']) ? (int)$re_row4['member_count'] : 0;
					$rowTotalMin   = isset($re_row4['total_minutes']) ? (int)$re_row4['total_minutes'] : 0;
					$hours         = floor($rowTotalMin / 60);
					$minutes       = $rowTotalMin % 60;

					$projectList[] = array(
						'project_code'   => $projectCode,
						'project_name'   => $projectName,
						'member_count'   => $memberCount,
						'total_minutes'  => $minutes,
						'total_hour'     => $hours
					);
				}
			}

			$projectCount = count($projectList);

			/* 5. 프로젝트별 참여 인원 목록 맵 */
			$memberSummaryMap = array();
			$tempMemberMap    = array();
			$projectMaxMap    = array();

			$sql5 = "
				SELECT
					m.project_code,
					m.memberno,
					mem.korName,
					SUM(m.normal_minutes + m.overtime_minutes) AS total_minutes
				FROM mh_project_minutes m
				LEFT JOIN member_tbl mem
					ON mem.MemberNo = m.memberno
				WHERE m.work_date >= '" . $startDateEsc . "'
				  AND m.work_date <  '" . $endDateEsc . "'
				  AND m.project_code IS NOT NULL
				  AND m.project_code <> ''
				GROUP BY m.project_code, m.memberno, mem.korName
				ORDER BY m.project_code ASC, total_minutes DESC, m.memberno ASC
			";
			$re5 = mysql_query($sql5, $db);

			if ($re5) {
				while ($row = mysql_fetch_assoc($re5)) {
					$projectCode   = isset($row['project_code']) ? $row['project_code'] : '';
					$memberno      = isset($row['memberno']) ? $row['memberno'] : '';
					$korName       = isset($row['korName']) ? $row['korName'] : $memberno;
					$rowTotalMin   = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;

					if (!isset($tempMemberMap[$projectCode])) {
						$tempMemberMap[$projectCode] = array();
					}

					if (!isset($projectMaxMap[$projectCode]) || $projectMaxMap[$projectCode] < $rowTotalMin) {
						$projectMaxMap[$projectCode] = $rowTotalMin;
					}

					$initial = '';
					if ($korName !== '') {
						$initial = function_exists('mb_substr')
							? mb_substr($korName, 0, 1, 'UTF-8')
							: substr($korName, 0, 1);
					}

					$tempMemberMap[$projectCode][] = array(
						'memberno'           => $memberno,
						'korName'            => $korName,
						'total_minutes_raw'  => $rowTotalMin,
						'total_work_hours'   => floor($rowTotalMin / 60),
						'total_work_minutes' => $rowTotalMin % 60,
						'initial'            => $initial,
						'color'              => '#2D6A4F'
					);
				}
			}

			foreach ($tempMemberMap as $projectCode => $members) {
				$maxMinutes = isset($projectMaxMap[$projectCode]) ? $projectMaxMap[$projectCode] : 1;
				if ($maxMinutes <= 0) {
					$maxMinutes = 1;
				}

				$memberSummaryMap[$projectCode] = array();

				foreach ($members as $m) {
					$m['percent'] = round(($m['total_minutes_raw'] / $maxMinutes) * 100, 1);
					$memberSummaryMap[$projectCode][] = $m;
				}
			}

			/* 8. TOP 인원 */
			$topMembers = array();

			$sql8 = "
				SELECT
					m.memberno,
					mem.korName,
					SUM(m.normal_minutes + m.overtime_minutes) AS total_minutes,
					COUNT(DISTINCT m.project_code) AS project_count
				FROM mh_project_minutes m
				LEFT JOIN member_tbl mem
					ON mem.MemberNo = m.memberno
				WHERE m.work_date >= '" . $startDateEsc . "'
				  AND m.work_date <  '" . $endDateEsc . "'
				  AND m.project_code IS NOT NULL
				  AND m.project_code <> ''
				GROUP BY m.memberno, mem.korName
				ORDER BY total_minutes DESC, m.memberno ASC
				LIMIT 5
			";
			$re8 = mysql_query($sql8, $db);

			if ($re8) {
				while ($row = mysql_fetch_assoc($re8)) {
					$memberno     = isset($row['memberno']) ? $row['memberno'] : '';
					$name         = isset($row['korName']) ? $row['korName'] : $memberno;
					$rowTotalMin  = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;
					$projectCountTop = isset($row['project_count']) ? (int)$row['project_count'] : 0;

					$initial = '';
					if ($name !== '') {
						$initial = function_exists('mb_substr')
							? mb_substr($name, 0, 1, 'UTF-8')
							: substr($name, 0, 1);
					}

					$topMembers[] = array(
						'name'          => $name,
						'memberno'      => $memberno,
						'role'          => $memberno,
						'project_count' => $projectCountTop,
						'hours'         => floor($rowTotalMin / 60),
						'initial'       => $initial,
						'color'         => '#2D6A4F'
					);
				}
			}

			/* 9. 상태/도넛 기본값 */
			$stats = array(
				'total_projects'   => $total_projectCount,
				'done_projects'    => 0,
				'ongoing_projects' => 0,
				'hold_projects'    => 0
			);

			$donut = array(
				'done_dash'       => '0 100',
				'ongoing_dash'    => '0 100',
				'ongoing_offset'  => '0',
				'hold_dash'       => '0 100',
				'hold_offset'     => '0'
			);

			/* assign */
			$this->smarty->assign('projectList', $projectList);
			$this->smarty->assign('projectCount', $projectCount);
			$this->smarty->assign('total_projectCount', $total_projectCount);
			$this->smarty->assign('total_memberCount', $total_memberCount);
			$this->smarty->assign('remainMinutes', $remainMinutes);
			$this->smarty->assign('totalHoursOnly', $totalHoursOnly);
			$this->smarty->assign('currentYear', $year);

			$this->smarty->assign('memberSummaryMapJson', json_encode($memberSummaryMap));

			$this->smarty->assign('topMembers', $topMembers);
			$this->smarty->assign('stats', $stats);
			$this->smarty->assign('donut', $donut);

			$this->smarty->display("intranet/common_contents/work_manhour2/ManHour_Project.tpl");
		}

		function Manhour_Myproject(){
			global $db;

            $ActionMode = isset($_REQUEST['ActionMode']) ? trim($_REQUEST['ActionMode']) : '';
			$MemberNo = isset($_GET['MemberNo']) ? trim($_GET['MemberNo']) : '';
			$year = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : 2026;
			if ($year < 2000 || $year > 2100) {
				$year = 2026;
			}

			$startDate = $year . '-01-01';
			$endDate   = ($year + 1) . '-01-01';

			$startDateEsc = mysql_real_escape_string($startDate);
			$endDateEsc   = mysql_real_escape_string($endDate);

			/* 1. 상단 통계 (프로젝트 수 / 참여 인원 수 / 총 업무시간) */
			$total_projectCount = 0;
			$total_memberCount  = 0;
			$totalMinutes       = 0;

			$sql = "
				SELECT
					COUNT(DISTINCT ProjectCode) AS project_count,
					COUNT(DISTINCT memberno) AS member_count,
					SUM(workMinutes) AS total_minutes,
					(Select korName From member_tbl Where memberNo = '$MemberNo') AS KorName
				From mh_day_alltime 
				WHERE WorkDate >= '2026-01-01'
				AND WorkDate < '2027-01-01'
				AND MemberNo = '$MemberNo'
				AND ProjectCode NOT LIKE 'BV009%'
				AND ProjectCode IS NOT NULL AND ProjectCode <> ''";
			$re = mysql_query($sql, $db);
			if ($re) {
				$row = mysql_fetch_assoc($re);
				if ($row) {
					$kor_Name			= isset($row['KorName']) ? $row['KorName'] : '';
					$total_projectCount = isset($row['project_count']) ? (int)$row['project_count'] : 0;
					$total_memberCount  = isset($row['member_count']) ? (int)$row['member_count'] : 0;
					$totalMinutes       = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;
				}
			}

			$totalHoursOnly = floor($totalMinutes / 60);
			$remainMinutes  = $totalMinutes % 60;

			/* 2. 프로젝트 목록 */
			$projectList = array();

			$sql2 = "
				SELECT
                    a.ProjectCode,
                    p1.ProjectName,
                    a.member_count,
                    a.total_minutes,
                    a.my_minutes,
                    t.top3_kornames
                FROM
                (
                    SELECT
                        m.ProjectCode,
                        COUNT(DISTINCT m.MemberNo) AS member_count,
                        SUM(m.WorkMinutes) AS total_minutes,
                        SUM(
                            CASE
                                WHEN m.MemberNo = '" . $MemberNo . "' THEN m.WorkMinutes
                                ELSE 0
                            END
                        ) AS my_minutes
                    FROM mh_day_alltime m
                    INNER JOIN
                    (
                        SELECT DISTINCT ProjectCode
                        FROM mh_day_alltime
                        WHERE WorkDate >= '" . $startDateEsc . "'
                        AND WorkDate <  '" . $endDateEsc . "'
                        AND MemberNo = '" . $MemberNo . "'
                        AND ProjectCode IS NOT NULL
                        AND ProjectCode <> ''
                        AND ProjectCode NOT LIKE 'BV009%'
                    ) myproj
                        ON myproj.ProjectCode = m.ProjectCode
                    WHERE m.WorkDate >= '" . $startDateEsc . "'
                    AND m.WorkDate <  '" . $endDateEsc . "'
                    AND m.ProjectCode IS NOT NULL
                    AND m.ProjectCode <> ''
                    AND m.ProjectCode NOT LIKE 'BV009%'
                    GROUP BY m.ProjectCode
                ) a
                LEFT JOIN project_tbl p1
                    ON p1.NewProjectCode = a.ProjectCode
                LEFT JOIN
                (
                    SELECT
                        x.ProjectCode,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(x.korName ORDER BY x.sum_minutes DESC, x.MemberNo ASC SEPARATOR ', '),
                            ', ',
                            3
                        ) AS top3_kornames
                    FROM
                    (
                        SELECT
                            mm.ProjectCode,
                            mm.MemberNo,
                            mt.korName,
                            SUM(mm.WorkMinutes) AS sum_minutes
                        FROM mh_day_alltime mm
                        INNER JOIN
                        (
                            SELECT DISTINCT ProjectCode
                            FROM mh_day_alltime
                            WHERE WorkDate >= '" . $startDateEsc . "'
                            AND WorkDate <  '" . $endDateEsc . "'
                            AND MemberNo = '" . $MemberNo . "'
                            AND ProjectCode IS NOT NULL
                            AND ProjectCode <> ''
                            AND ProjectCode NOT LIKE 'BV009%'
                        ) myproj2
                            ON myproj2.ProjectCode = mm.ProjectCode
                        LEFT JOIN member_tbl mt
                            ON mt.memberno = mm.MemberNo
                        WHERE mm.WorkDate >= '" . $startDateEsc . "'
                        AND mm.WorkDate <  '" . $endDateEsc . "'
                        AND mm.ProjectCode IS NOT NULL
                        AND mm.ProjectCode <> ''
                        AND mm.ProjectCode NOT LIKE 'BV009%'
                        GROUP BY mm.ProjectCode, mm.MemberNo, mt.korName
                    ) x
                    GROUP BY x.ProjectCode
                ) t
                    ON t.ProjectCode = a.ProjectCode
                ORDER BY a.my_minutes DESC, a.total_minutes DESC, a.ProjectCode ASC";
			$re2 = mysql_query($sql2, $db);
			if ($re2) {
				while ($row = mysql_fetch_assoc($re2)) {
					$projectCode = isset($row['ProjectCode']) ? $row['ProjectCode'] : '';
					$projectName = isset($row['ProjectName']) ? $row['ProjectName'] : '';
					$memberCount = isset($row['member_count']) ? (int)$row['member_count'] : 0;
					$rowTotalMin = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;
					$myMin		 = isset($row['my_minutes']) ? (int)$row['my_minutes'] : 0;
					$top_korName = isset($row['top3_kornames']) ? $row['top3_kornames'] : '';

                    $total_my_minutes += $myMin;

					$projectList[] = array(
						'ProjectCode'  => $projectCode,
						'ProjectName'  => $projectName,
						'member_count'  => $memberCount,
						'total_minutes' => $rowTotalMin % 60,
						'total_hour'    => floor($rowTotalMin / 60),
						'my_minutes'	=> $myMin % 60,
						'my_hour'		=> floor($myMin / 60),
						'top_korName'	=> $top_korName,
						'raw_total_minutes' => $rowTotalMin,
			            'raw_my_minutes'    => $myMin
					);
				}
			}

			if (!empty($projectList)) {
                foreach ($projectList as $k => $v) {
                    $rowTotalMin = (int)$v['raw_total_minutes'];
                    $myMin       = (int)$v['raw_my_minutes'];

                    $projectList[$k]['ProjectRatio'] = ($rowTotalMin > 0)
                        ? round(($myMin / $rowTotalMin) * 100, 2) . '%'
                        : '0%';

                    $projectList[$k]['MyRatio'] = ($total_my_minutes > 0)
                        ? round(($myMin / $total_my_minutes) * 100, 2) . '%'
                        : '0%';

                    $projectList[$k]['total_my_minutes'] = $total_my_minutes;
                }

                /* 필요 없으면 제거 */
                foreach ($projectList as $k => $v) {
                    unset($projectList[$k]['raw_total_minutes']);
                    unset($projectList[$k]['raw_my_minutes']);
                }
            }

            $projectCount = count($projectList);

			/* 3. 프로젝트별 참여 인원 목록 */
			$memberSummaryMap = array();
			$tempMemberMap    = array();
			$projectMaxMap    = array();

			$sql3 = "
				SELECT
                    x.ProjectCode,
                    x.MemberNo,
                    x.korName,
                    sc.Name AS PositionName,
                    x.total_minutes,
                    pt.project_total_minutes
                FROM
                (
                    SELECT
                        m.ProjectCode,
                        m.MemberNo,
                        mem.korName,
                        SUM(m.WorkMinutes) AS total_minutes
                    FROM mh_day_alltime m
                    LEFT JOIN member_tbl mem
                        ON mem.MemberNo = m.MemberNo
                    WHERE m.WorkDate >= '" . $startDateEsc . "'
                    AND m.WorkDate <  '" . $endDateEsc . "'
                    AND m.ProjectCode IS NOT NULL
                    AND m.ProjectCode <> ''
                    AND m.ProjectCode NOT LIKE 'BV009%'
                    GROUP BY m.ProjectCode, m.MemberNo, mem.korName
                ) x
                LEFT JOIN
                (
                    SELECT
                        a.MemberNo,
                        MAX(a.PostionCode) AS PostionCode
                    FROM mh_day_alltime a
                    INNER JOIN
                    (
                        SELECT
                            MemberNo,
                            MAX(WorkDate) AS max_workdate
                        FROM mh_day_alltime
                        WHERE WorkDate >= '2026-01-01'
                            AND WorkDate < '2027-01-01'
                            AND PostionCode IS NOT NULL
                            AND PostionCode <> ''
                        GROUP BY MemberNo
                    ) b
                        ON b.MemberNo = a.MemberNo
                    AND b.max_workdate = a.WorkDate
                    WHERE a.PostionCode IS NOT NULL
                        AND a.PostionCode <> ''
                    GROUP BY a.MemberNo
                    ) lp
                    ON lp.MemberNo = x.MemberNo
                    LEFT JOIN systemconfig_tbl sc
                    ON sc.SysKey = 'PositionCode'
                    AND sc.CODE = lp.PostionCode
                    LEFT JOIN
                    (
                    SELECT
                        ProjectCode,
                        SUM(WorkMinutes) AS project_total_minutes
                    FROM mh_day_alltime
                    WHERE WorkDate >= '" . $startDateEsc . "'
                    AND WorkDate <  '" . $endDateEsc . "'
                    AND ProjectCode IS NOT NULL
                    AND ProjectCode <> ''
                    AND ProjectCode NOT LIKE 'BV009%'
                    GROUP BY ProjectCode
                ) pt
                    ON pt.ProjectCode = x.ProjectCode
                ORDER BY x.ProjectCode ASC, x.total_minutes DESC, x.MemberNo ASC";
			$re3 = mysql_query($sql3, $db);

			if ($re3) {
				while ($row = mysql_fetch_assoc($re3)) {
					$projectCode = isset($row['ProjectCode']) ? $row['ProjectCode'] : '';
					$memberno    = isset($row['MemberNo']) ? $row['MemberNo'] : '';
					$korName     = isset($row['korName']) ? $row['korName'] : $memberno;
					$Position	 = isset($row['PositionName']) ? $row['PositionName'] : '';
					$rowTotalMin = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;
                    $projectTotalMin = isset($row['project_total_minutes']) ? (int)$row['project_total_minutes'] : 0;
                    $participationRatio = ($projectTotalMin > 0) ? round(($rowTotalMin / $projectTotalMin) * 100, 2) : 0;

					if (!isset($tempMemberMap[$projectCode])) {
						$tempMemberMap[$projectCode] = array();
					}

					if (!isset($projectMaxMap[$projectCode]) || $projectMaxMap[$projectCode] < $rowTotalMin) {
						$projectMaxMap[$projectCode] = $rowTotalMin;
					}

					$initial = '';
					if ($korName !== '') {
						$initial = function_exists('mb_substr')
							? mb_substr($korName, 0, 1, 'UTF-8')
							: substr($korName, 0, 1);
					}

					$tempMemberMap[$projectCode][] = array(
						'memberno'           => $memberno,
						'korName'            => $korName,
						'position'			 => $Position,
						'total_minutes_raw'  => $rowTotalMin,
						'total_work_hours'   => floor($rowTotalMin / 60),
						'total_work_minutes' => $rowTotalMin % 60,
                        'participation_ratio'=> $participationRatio,
						'initial'            => $initial,
						'color'              => '#2D6A4F'
					);
				}
			}

			foreach ($tempMemberMap as $projectCode => $members) {
				$maxMinutes = isset($projectMaxMap[$projectCode]) ? (int)$projectMaxMap[$projectCode] : 1;
				if ($maxMinutes <= 0) {
					$maxMinutes = 1;
				}

				$memberSummaryMap[$projectCode] = array();

				foreach ($members as $m) {
					$m['percent'] = round(($m['total_minutes_raw'] / $maxMinutes) * 100, 1);
					$memberSummaryMap[$projectCode][] = $m;
				}
			}

			/* assign */
            $this->smarty->assign('ActionMode', $ActionMode);
			$this->smarty->assign('kor_Name', $kor_Name);
			$this->smarty->assign('MemberNo', $MemberNo);
			$this->smarty->assign('projectList', $projectList);
			$this->smarty->assign('projectCount', $projectCount);
			$this->smarty->assign('total_projectCount', $total_projectCount);
			$this->smarty->assign('total_memberCount', $total_memberCount);
			$this->smarty->assign('remainMinutes', $remainMinutes);
			$this->smarty->assign('totalHoursOnly', $totalHoursOnly);
			$this->smarty->assign('currentYear', $year);
			$this->smarty->assign('memberSummaryMapJson', json_encode($memberSummaryMap));

			$this->smarty->display("intranet/common_contents/work_manhour2/ManHour_Myproject.tpl");
		}

        function Manhour_Fullproject(){
			global $db;

            $ActionMode = isset($_REQUEST['ActionMode']) ? trim($_REQUEST['ActionMode']) : '';
			$MemberNo = isset($_GET['MemberNo']) ? trim($_GET['MemberNo']) : '';
            if (!in_array($MemberNo, array('B21369', 'T03225'), true)) {
                // Unicode escape sequence로 고정 문자열 출력 (파일 인코딩과 관계없이 안정적인 출력)
                $msg = "\u{C5F0}\u{B7EC} \u{AD00}\u{D568}\u{C774} \u{C5C6}\u{C74C}\u{B2E4}."; // 열람 권한이 없습니다.
                echo $msg;
                exit;
            }

			$year = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : 2026;
			if ($year < 2000 || $year > 2100) {
				$year = 2026;
			}

			$startDate = $year . '-01-01';
			$endDate   = ($year + 1) . '-01-01';

			$startDateEsc = mysql_real_escape_string($startDate);
			$endDateEsc   = mysql_real_escape_string($endDate);

			/* 1. 상단 통계 (프로젝트 수 / 참여 인원 수 / 총 업무시간) */
			$total_projectCount = 0;
			$total_memberCount  = 0;
			$totalMinutes       = 0;

			$sql = "
				SELECT
					COUNT(DISTINCT ProjectCode) AS project_count,
					COUNT(DISTINCT memberno) AS member_count,
					SUM(workMinutes) AS total_minutes,
					(Select korName From member_tbl Where memberNo = '$MemberNo') AS KorName
				From mh_day_alltime 
				WHERE WorkDate >= '$startDateEsc'
				AND WorkDate < '$endDateEsc'
				AND ProjectCode NOT LIKE 'BV009%'
				AND ProjectCode IS NOT NULL AND ProjectCode <> ''";
			$re = mysql_query($sql, $db);
			if ($re) {
				$row = mysql_fetch_assoc($re);
				if ($row) {
					$kor_Name			= isset($row['KorName']) ? $row['KorName'] : '';
					$total_projectCount = isset($row['project_count']) ? (int)$row['project_count'] : 0;
					$total_memberCount  = isset($row['member_count']) ? (int)$row['member_count'] : 0;
					$totalMinutes       = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;
				}
			}

			$totalHoursOnly = floor($totalMinutes / 60);
			$remainMinutes  = $totalMinutes % 60;

			/* 2. 프로젝트 목록 */
			$projectList = array();

            $sql2 = "
                SELECT
                    a.ProjectCode,
                    COALESCE(p1.ProjectName, a.ProjectCode) AS ProjectName,
                    a.member_count,
                    a.total_minutes,
                    t.top5_kornames
                FROM
                (
                    SELECT
                        m.ProjectCode,
                        COUNT(DISTINCT m.MemberNo) AS member_count,
                        SUM(m.WorkMinutes) AS total_minutes
                    FROM mh_day_alltime m
                    INNER JOIN
                    (
                        SELECT DISTINCT ProjectCode
                        FROM mh_day_alltime
                        WHERE WorkDate >= '" . $startDateEsc . "'
                        AND WorkDate <  '" . $endDateEsc . "'
                        AND ProjectCode IS NOT NULL
                        AND ProjectCode <> ''
                        AND ProjectCode NOT LIKE 'BV009%'
                    ) myproj
                    ON myproj.ProjectCode = m.ProjectCode
                    WHERE m.WorkDate >= '" . $startDateEsc . "'
                    AND m.WorkDate <  '" . $endDateEsc . "'
                    AND m.ProjectCode IS NOT NULL
                    AND m.ProjectCode <> ''
                    AND m.ProjectCode NOT LIKE 'BV009%'
                    GROUP BY m.ProjectCode
                ) a
                LEFT JOIN (
                    SELECT NewProjectCode, MIN(ProjectName) AS ProjectName
                    FROM project_tbl
                    WHERE NewProjectCode IS NOT NULL AND NewProjectCode <> ''
                    GROUP BY NewProjectCode
                ) p1
                ON p1.NewProjectCode = a.ProjectCode
                LEFT JOIN
                (
                    SELECT
                        x.ProjectCode,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(x.korName ORDER BY x.sum_minutes DESC, x.MemberNo ASC SEPARATOR ', '),
                            ', ',
                            5
                        ) AS top5_kornames
                    FROM
                    (
                        SELECT
                            mm.ProjectCode,
                            mm.MemberNo,
                            mt.korName,
                            SUM(mm.WorkMinutes) AS sum_minutes
                        FROM mh_day_alltime mm
                        INNER JOIN
                        (
                            SELECT DISTINCT ProjectCode
                            FROM mh_day_alltime
                            WHERE WorkDate >= '" . $startDateEsc . "'
                            AND WorkDate <  '" . $endDateEsc . "'
                            AND ProjectCode IS NOT NULL
                            AND ProjectCode <> ''
                            AND ProjectCode NOT LIKE 'BV009%'
                        ) myproj2
                        ON myproj2.ProjectCode = mm.ProjectCode
                        LEFT JOIN member_tbl mt
                        ON mt.memberno = mm.MemberNo
                        WHERE mm.WorkDate >= '" . $startDateEsc . "'
                        AND mm.WorkDate <  '" . $endDateEsc . "'
                        AND mm.ProjectCode IS NOT NULL
                        AND mm.ProjectCode <> ''
                        AND mm.ProjectCode NOT LIKE 'BV009%'
                        GROUP BY mm.ProjectCode, mm.MemberNo, mt.korName
                    ) x
                    GROUP BY x.ProjectCode
                ) t
                ON t.ProjectCode = a.ProjectCode
                ORDER BY a.total_minutes DESC, a.ProjectCode ASC";
			$re2 = mysql_query($sql2, $db);
			if ($re2) {
				while ($row = mysql_fetch_assoc($re2)) {
					$projectCode = isset($row['ProjectCode']) ? $row['ProjectCode'] : '';
					$projectName = isset($row['ProjectName']) ? $row['ProjectName'] : '';
					$memberCount = isset($row['member_count']) ? (int)$row['member_count'] : 0;
					$rowTotalMin = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;
					$top_korName = isset($row['top5_kornames']) ? $row['top5_kornames'] : '';

					$projectList[] = array(
						'ProjectCode'  => $projectCode,
						'ProjectName'  => $projectName,
						'member_count'  => $memberCount,
						'total_minutes' => $rowTotalMin % 60,
						'total_hour'    => floor($rowTotalMin / 60),
						'top_korName'	=> $top_korName
					);
				}
			}

            $projectCount = count($projectList);

			/* 3. 프로젝트별 참여 인원 목록 */
			$memberSummaryMap = array();
			$tempMemberMap    = array();
			$projectMaxMap    = array();

			$sql3 = "
				SELECT
                    x.ProjectCode,
                    x.MemberNo,
                    x.korName,
                    sc.Name AS PositionName,
                    x.total_minutes,
                    pt.project_total_minutes
                FROM
                (
                    SELECT
                        m.ProjectCode,
                        m.MemberNo,
                        mem.korName,
                        SUM(m.WorkMinutes) AS total_minutes
                    FROM mh_day_alltime m
                    LEFT JOIN member_tbl mem
                        ON mem.MemberNo = m.MemberNo
                    WHERE m.WorkDate >= '" . $startDateEsc . "'
                    AND m.WorkDate <  '" . $endDateEsc . "'
                    AND m.ProjectCode IS NOT NULL
                    AND m.ProjectCode <> ''
                    AND m.ProjectCode NOT LIKE 'BV009%'
                    GROUP BY m.ProjectCode, m.MemberNo, mem.korName
                ) x
                LEFT JOIN
                (
                    SELECT
                        a.MemberNo,
                        MAX(a.PostionCode) AS PostionCode
                    FROM mh_day_alltime a
                    INNER JOIN
                    (
                        SELECT
                            MemberNo,
                            MAX(WorkDate) AS max_workdate
                        FROM mh_day_alltime
                        WHERE WorkDate >= '2026-01-01'
                            AND WorkDate < '2027-01-01'
                            AND PostionCode IS NOT NULL
                            AND PostionCode <> ''
                        GROUP BY MemberNo
                    ) b
                        ON b.MemberNo = a.MemberNo
                    AND b.max_workdate = a.WorkDate
                    WHERE a.PostionCode IS NOT NULL
                        AND a.PostionCode <> ''
                    GROUP BY a.MemberNo
                    ) lp
                    ON lp.MemberNo = x.MemberNo
                    LEFT JOIN systemconfig_tbl sc
                    ON sc.SysKey = 'PositionCode'
                    AND sc.CODE = lp.PostionCode
                    LEFT JOIN
                    (
                    SELECT
                        ProjectCode,
                        SUM(WorkMinutes) AS project_total_minutes
                    FROM mh_day_alltime
                    WHERE WorkDate >= '" . $startDateEsc . "'
                    AND WorkDate <  '" . $endDateEsc . "'
                    AND ProjectCode IS NOT NULL
                    AND ProjectCode <> ''
                    AND ProjectCode NOT LIKE 'BV009%'
                    GROUP BY ProjectCode
                ) pt
                    ON pt.ProjectCode = x.ProjectCode
                ORDER BY x.ProjectCode ASC, x.total_minutes DESC, x.MemberNo ASC";
			$re3 = mysql_query($sql3, $db);

			if ($re3) {
				while ($row = mysql_fetch_assoc($re3)) {
					$projectCode = isset($row['ProjectCode']) ? $row['ProjectCode'] : '';
					$memberno    = isset($row['MemberNo']) ? $row['MemberNo'] : '';
					$korName     = isset($row['korName']) ? $row['korName'] : $memberno;
					$Position	 = isset($row['PositionName']) ? $row['PositionName'] : '';
					$rowTotalMin = isset($row['total_minutes']) ? (int)$row['total_minutes'] : 0;
                    $projectTotalMin = isset($row['project_total_minutes']) ? (int)$row['project_total_minutes'] : 0;
                    $participationRatio = ($projectTotalMin > 0) ? round(($rowTotalMin / $projectTotalMin) * 100, 2) : 0;

					if (!isset($tempMemberMap[$projectCode])) {
						$tempMemberMap[$projectCode] = array();
					}

					if (!isset($projectMaxMap[$projectCode]) || $projectMaxMap[$projectCode] < $rowTotalMin) {
						$projectMaxMap[$projectCode] = $rowTotalMin;
					}

					$initial = '';
					if ($korName !== '') {
						$initial = function_exists('mb_substr')
							? mb_substr($korName, 0, 1, 'UTF-8')
							: substr($korName, 0, 1);
					}

					$tempMemberMap[$projectCode][] = array(
						'memberno'           => $memberno,
						'korName'            => $korName,
						'position'			 => $Position,
						'total_minutes_raw'  => $rowTotalMin,
						'total_work_hours'   => floor($rowTotalMin / 60),
						'total_work_minutes' => $rowTotalMin % 60,
                        'participation_ratio'=> $participationRatio,
						'initial'            => $initial,
						'color'              => '#2D6A4F'
					);
				}
			}

			foreach ($tempMemberMap as $projectCode => $members) {
				$maxMinutes = isset($projectMaxMap[$projectCode]) ? (int)$projectMaxMap[$projectCode] : 1;
				if ($maxMinutes <= 0) {
					$maxMinutes = 1;
				}

				$memberSummaryMap[$projectCode] = array();

				foreach ($members as $m) {
					$m['percent'] = round(($m['total_minutes_raw'] / $maxMinutes) * 100, 1);
					$memberSummaryMap[$projectCode][] = $m;
				}
			}

			/* assign */
            $this->smarty->assign('ActionMode', $ActionMode);
			$this->smarty->assign('kor_Name', $kor_Name);
			$this->smarty->assign('MemberNo', $MemberNo);
			$this->smarty->assign('projectList', $projectList);
			$this->smarty->assign('projectCount', $projectCount);
			$this->smarty->assign('total_projectCount', $total_projectCount);
			$this->smarty->assign('total_memberCount', $total_memberCount);
			$this->smarty->assign('remainMinutes', $remainMinutes);
			$this->smarty->assign('totalHoursOnly', $totalHoursOnly);
			$this->smarty->assign('currentYear', $year);
			$this->smarty->assign('memberSummaryMapJson', json_encode($memberSummaryMap));

			$this->smarty->display("intranet/common_contents/work_manhour2/ManHour_Myproject.tpl");
		}

		private function minToHHMM($min)
		{
			if ($min === null || $min === '') return '';

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
			return number_format($h) . '시간' . sprintf('%02d', $m) . '분';
		}

		private function fmtNum($n)
		{
			if ($n === null || $n === '') return '0';
			return number_format((int)$n);
		}

		private function colorFromCode($code, array &$usedIdx)
		{
			$palette = array(
				'#4E79A7','#F28E2B','#E15759','#76B7B2','#59A14F',
				'#EDC948','#B07AA1','#FF9DA7','#9C755F','#BAB0AC',
				'#1F77B4','#FF7F0E','#2CA02C','#D62728','#9467BD',
				'#8C564B','#E377C2','#7F7F7F','#BCBD22','#17BECF'
			);

			$code = strtoupper(trim((string)$code));

			// 기존 해시(31)
			$hash = 0;
			$len = strlen($code);
			for ($i=0; $i<$len; $i++) {
				$hash = ($hash * 31 + ord($code[$i])) & 0x7fffffff;
			}

			$n = count($palette);
			$idx = $hash % $n;

			// step을 해시에서 뽑아 "다음 후보"로 이동 (0 방지)
			$step = (($hash >> 8) % ($n - 1)) + 1;

			// 이미 쓰인 idx면 피해서 다음으로
			for ($k = 0; $k < $n; $k++) {
				if (empty($usedIdx[$idx])) {
					$usedIdx[$idx] = true;
					return $palette[$idx];
				}
				$idx = ($idx + $step) % $n;
			}

			// 최후: 다 썼으면(거의 없음) 그냥 원래 idx
			return $palette[$hash % $n];
		}
	}
?>