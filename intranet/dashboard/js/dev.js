function isNull(value) {
	if (value == null || value == undefined || value.toString().replace(/\s/g,"") == "") { 
		return true;
	}

	return false;
}

function formatCurrency(value, options = {}) {
  const {
    useWon = false,      // ₩ 표시 여부
    fixed = null         // 소수점 자리수 (예: 2 → 1.23)
  } = options;

  // 숫자 변환
  let num = Number(value);

  // 숫자가 아니면 0 반환
  if (isNaN(num)) return "0";

  // 소수점 자리 고정
  if (fixed !== null) {
    num = num.toFixed(fixed);
  }

  // 음수 처리
  const isNegative = num < 0;
  num = Math.abs(num);

  // 정수 / 소수 분리
  let [integer, decimal] = String(num).split(".");

  // 정수 부분 콤마 처리
  integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

  // 다시 합치기
  let result = decimal ? `${integer}.${decimal}` : integer;

  // 음수 붙이기
  if (isNegative) result = "-" + result;

  // 원화 표시
  if (useWon) result = "₩" + result;

  return result;
}

function toDateOnly(dateStr) {
    const d = new Date(dateStr);
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

// 날짜만 검증
function isValidPeriod(startDate, endDate, maxDays = 30) {
    const start = toDateOnly(startDate);
    const end = toDateOnly(endDate);

    if (isNaN(start) || isNaN(end)) return false;
    if (start > end) return false;

    const diffDays = Math.floor((end - start) / 86400000) + 1;

    return diffDays <= maxDays;
}

function toDateOnly(dateStr) {
    const d = new Date(dateStr);
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function validatePeriod(startDate, endDate, maxDays = 30) {
    const start = toDateOnly(startDate);
    const end = toDateOnly(endDate);

    if (isNaN(start) || isNaN(end)) {
        return {valid: false, message: "유효하지 않은 날짜형식입니다."};
    }

    if (start > end) {
        return {valid: false, message: "시작일, 종료일을 확인해주세요."};
    }

    const diffDays = Math.floor((end - start) / 86400000) + 1;

    if (diffDays > maxDays) {
        return {valid: false, message: "최대 " + maxDays + "일 기간조회만 가능합니다.", diffDays};
    }

    return {valid: true, diffDays};
}