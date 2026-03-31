<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 검색될 샘플 데이터 입니다.
$cities = array("자갈 및 실트 섞인 모래","실트 및 자갈 섞인 모래","실트 섞인 모래","더러운 모래","고운모래","큰자갈","작은자갈","모래로 분해");
// 넘어온 검색어 파라미터 입니다.
$term = $_GET['term'];
// 데이터를 루핑 하면서 찾습니다.
$result = array();
foreach($cities as $city) {
	if(strpos($city, $term) !== false) {
		$result[] = array("label" => $city, "value" => $city);
	}
}
// 찾아진 데이터를 json 데이터로 변환하여 전송합니다.
echo json_encode($result);


?>