<?php
/****************************************************************************
  기 능 : Mysqli DB 구현
  관 련 DB :
  프로시져 :
  사용메뉴 :
  기 타 :
  		1. php 5.1에서 mysqli를 사용하기 위함 
  변경이력 :
  		1. 2026-04-07 / - / 김병철 / 최초작성
****************************************************************************/
class MysqliCommon {
    protected $conn;

    public function __construct($conn = null) {
        if ($conn !== null) {
            $this->conn = $conn;
        } else {
            // 경로 유지
            $dbcon_path = realpath(dirname(__FILE__) . "/../../../inc/mysqli_dbcon.inc");
            if (file_exists($dbcon_path)) {
                include_once $dbcon_path;
            }
            
            global $mysqli_conn;
            $this->conn = $mysqli_conn;
        }
    }

    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }

    public function executeMysqli($sql, $args = null) {
    	$result = array(
			"success"       => false,
			"rows"          => array(),
			"affected_rows" => 0,
			"insert_id"     => 0,
			"error"         => ""
    	);
    	
    	$placeholders_count = substr_count($sql, '?');
    	$args_count = ($args === null) ? 0 : count($args);
    	
    	if ($placeholders_count !== $args_count) {
    		return $result; 
    	}
    	
    	$stmt = $this->conn->prepare($sql);
    	if ($stmt) {
    		// 1. 파라미터 값이 배열로 넘어온 경우 처리
    		if ($args !== null && is_array($args)) {
    			$types = "";
    			$bind_values = array();
    			
    			foreach ($args as $key => $value) {
    				// 데이터 타입 자동 판별
    				if (is_int($value)) {
    					$types .= "i"; // Integer (정수)
    				} elseif (is_float($value) || is_double($value)) {
    					$types .= "d"; // Double (실수)
    				} elseif (is_string($value)) {
    					$types .= "s"; // String (문자열)
    				} else {
    					$types .= "b"; // Blob (바이너리 등 기타)
    				}
    				
    				// 가변 변수를 생성하여 고유한 참조(주소) 확보
    				$var_name = 'arg' . $key;
    				$$var_name = $value;
    				$bind_values[] = &$$var_name;
    			}
    			
    			// [타입, 값1, 값2...] 형태의 배열 생성
    			$bind_params = array_merge(array($types), $bind_values);
    			
    			// 동적 바인딩 실행
    			call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    		}
    		
    		if ($stmt->execute()) {
    			$result["success"] = true;
    			$result["affected_rows"] = $stmt->affected_rows;
    			$result["insert_id"] = $stmt->insert_id;
    			
    			$meta = $stmt->result_metadata();
    			if ($meta) {
    				$stmt->store_result();
    				while ($row = $this->fetchAssocStatement($stmt)) {
    					$result["rows"][] = $row;
    				}
    				
    				// 메타데이터 메모리 해제
    				$meta->free(); 
    			}
    		} else {
    			$result["error"] = "Execute failed: " . $stmt->error;
    		}
    		
    		$stmt->close();
    	} else {
    		$result["error"] = "Prepare failed: " . $this->conn->error;
    		return $result;
    	}
    	
    	return $result;
    }
    
    public function executeMysqli2($sql, $args = null) {
    	// 리턴값 표준화 구조
    	$result = array(
			"success"       => false,
			"rows"          => array(),
			"affected_rows" => 0,
			"insert_id"     => 0,
			"error"         => ""
    	);
    	
    	// 1. 파라미터 개수 검증
    	$placeholders_count = substr_count($sql, '?');
    	$args_count = ($args === null) ? 0 : count($args);
    	
    	if ($placeholders_count !== $args_count) {
    		$result["error"] = "Placeholder count ($placeholders_count) does not match args count ($args_count)";
    		return $result;
    	}
    	
    	// 2. Statement 준비
    	$stmt = $this->conn->prepare($sql);
    	if (!$stmt) {
    		$result["error"] = "Prepare failed: " . $this->conn->error;
    		return $result;
    	}
    	
    	// 3. 동적 바인딩 (PHP 5.1 참조 방식 대응)
    	if ($args_count > 0) {
    		$types = "";
    		$bind_params = array();
    		
    		foreach ($args as $val) {
    			if (is_int($val)) $types .= "i";
    			elseif (is_double($val) || is_float($val)) $types .= "d";
    			else $types .= "s";
    		}
    		
    		$bind_params[] = &$types;
    		foreach ($args as $key => $val) {
    			// PHP 5.1/5.2의 안정적인 참조 전달을 위해 원본 배열 요소를 직접 참조
    			$bind_params[] = &$args[$key];
    		}
    		
    		call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    	}
    	
    	// 4. 실행
    	if ($stmt->execute()) {
    		$result["success"] = true;
    		$result["affected_rows"] = $stmt->affected_rows;
    		$result["insert_id"]     = $stmt->insert_id;
    		
    		// 5. 결과 페치 (SELECT인 경우에만)
    		$meta = $stmt->result_metadata();
    		if ($meta) {
    			$stmt->store_result();
    			while ($row = $this->fetchAssocStatement($stmt)) {
    				$result["rows"][] = $row;
    			}
    			$meta->free(); // 메타데이터 메모리 해제
    		}
    	} else {
    		$result["error"] = "Execute failed: " . $stmt->error;
    	}
    	
    	$stmt->close();
    	return $result;
    }

    protected function fetchAssocStatement($stmt) {
        $meta = $stmt->result_metadata();
        if (!$meta) return null;

        $columns = array();
        $row = array();

        while ($field = $meta->fetch_field()) {
            // 결과 컬럼명들을 배열 키로 설정하고 참조로 연결
            $columns[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $columns);

        if ($stmt->fetch()) {
            $copy = array();
            foreach ($row as $key => $val) {
                $copy[$key] = $val; // 참조를 끊고 값을 복사
            }
            return $copy;
        }

        return null;
    }
}
?>