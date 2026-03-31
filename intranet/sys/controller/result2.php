<?

include "../../sys/inc/dbcon.inc";
//include "../../sys/inc/phonex_css.inc";
//include "../../sys/inc/Auth_Common.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>상세보기</title>
	<link href="../../css/add.css" rel="stylesheet" type="text/css" />
	<link href="../../css/common.css" rel="stylesheet" type="text/css" />
	<link href="../../css/index.css" rel="stylesheet" type="text/css" />
	<link href="../../css/table_code.css" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" href="../../css/smoothness/jquery-ui-1.10.4.custom.css" type="text/css" media="all" />
<?
$date1 = date("Y");  /// 오늘
$date2 = date("m");  /// 오늘
$date3 = date("d");  /// 오늘

$DETAIL_OP1 = "계약금액";
$DETAIL_OP2 = "물가변동";

$LAST_GROUP_CODE_NO = 212;


if($sel_year == "" || $sel_year == null) { $sel_year = $date1; }
if($sel_month == "" || $sel_month == null) {
	$sel_month = $date2;
} else {
	if($sel_month <= 9) { $sel_month = "0".$sel_month; }
}
$uyear = date("Y")+1;  /////최대 보이는 년도 

$UNIT = 100000000; //단위:억만원


if($tab_index == "") { $tab_index = 2; }
if($sub_index == "") { $sub_index = 1; }

?>
<script language="javascript">
function cdate()
{
	var f=document.sform;
	var sel_year=f.sel_year.value;
	var tab_index=f.tab_index.value;
	var sub_index=f.sub_index.value;

	//var sel_month=f.sel_month.value;
	
	//location.href="result2.php?sel_year="+sel_year+"&sel_month="+sel_month;
	location.href="result2.php?tab_index="+tab_index+"&sub_index="+sub_index+"&sel_year="+sel_year;
	
}

function open1() {
	var f=document.sform;

	window.open("input.php", "input", "width=880, height=680, scrollbars=yes, status=no, location=no, directories=no, toolbar=no, menubar=yes, left=0, top=0, resizable=yes");
}

function ChangeImg(object,bgimg) {
	object.style.color="#ffffff";
}
function ChangeImgO(object,bgimg) {
	object.style.color="#333333";
}

function Tab_Selected(tab_index,sub_index) {
	var f = document.sform;
	var old_tab=f.old_tab.value;

	if(old_tab !=1){
		var sel_year=f.sel_year.value;
		location.href="result2.php?tab_index="+tab_index+"&sub_index="+sub_index+"&sel_year="+sel_year;
	}
	else
	{
		location.href="result2.php?tab_index="+tab_index+"&sub_index="+sub_index;
	}
}


function Profit_Print() {
	var f=document.sform;
	var sel_year=f.sel_year.value;
	var tab_index=f.tab_index.value;
	var sub_index=f.sub_index.value;
	
	var entryPop2 =window.open("result2.php?tab_index="+tab_index+"&sub_index="+sub_index+"&sel_year="+sel_year, "sales22", "width=850, height=800, scrollbars=Yes, status=no, location=no, directories=no, toolbar=no, menubar=yes, left=80, top=80, resizable=yes");
	entryPop2.focus();

}

</script>
<style>
@media print {
.noprint {display: none;}
html, body {background: #fff;}
#graph1{
		margin-top: -58px;
	}

.page2 { page-break-before: always }

.wrap{width:1020px;margin-top:0px;margin-left:auto;margin-right:auto;}
table { page-break-inside:auto }
tr    { page-break-inside:avoid; page-break-after:auto }
{
    margin:auto;
    padding:auto;
    /*text-align:center;*/
    font-size:1em;
    }

/*The folowing rule will help to minimaze the differences between browesers*/
h1,h2,h3,h4,h5,h6,pre,code,address,caption,cite,code,em,strong,th {font-weight:normal; font-style:normal;}
ul,ol {list-style:none;}
img {border:none;}
caption,th {text-align:left;}

table {
border-spacing:0;
font-size:1em; 
font-weight:normal; 
font-style:normal; 
font-family:Times New Roman;


}
tr{ 
    page-break-inside:avoid; 
    page-break-after:auto 
}

</style>
<head>
<STYLE TYPE="text/css">
.img_box  {font-family:새굴림,arial; font-size:12px; color:#333333; text-align:center;background-image:url(./img/box.gif)   ; background-repeat:no-repeat;width:100;height:22;}
.img_boxs  {font-family:새굴림,arial; font-size:12px; color:#ffffff; text-align:center;background-image:url(./img/boxs.gif) ; background-repeat:no-repeat;width:100;height:22;}

.img_box2  {font-family:새굴림,arial; font-size:12px; color:#333333; text-align:center;background-image:url(./img/box2.gif)   ; background-repeat:no-repeat;width:100;height:22;}
.img_boxs2  {font-family:새굴림,arial; font-size:12px; color:#ffffff; text-align:center;background-image:url(./img/boxs2.gif) ; background-repeat:no-repeat;width:100;height:22;}

.img_tab  {font-family:새굴림,arial; font-size:12px; color:#333333; text-align:center;background-image:url(./img/tab.gif)   ; background-repeat:no-repeat;width:94;height:24;}
.img_tabs  {font-family:새굴림,arial; font-size:12px; color:#ffffff; text-align:center;background-image:url(./img/tabs.gif) ; background-repeat:no-repeat;width:94;height:24;}
</style>

<style type="text/css"> 
@media print {
.noprint {display: none;}
#graph1{
		margin-top: -58px;
		
	}
}
.page2 { page-break-before: always }

</style>
</head>
<TITLE> :: </TITLE>
<body>
<form name="sform">
<input type="hidden" name="n_num" value="<?=$n_num?>">
<input type="hidden" name="old_tab" value="<?=$tab_index?>">
<input type="hidden" name="tab_index" value="<?=$tab_index?>">
<input type="hidden" name="sub_index" value="<?=$sub_index?>">

<div class="noprint">
<?if($tab_index == 3) {  //수주현황상세?>

<table border=0 cellpadding=0 cellspacing=0  width='800'>
	<div class="noprint tbl_bottom_border_blue" style="width:800px;height:40px;float:left;clear:none;">
		<div class="tab">
			<?
				$tab_Titel2 = array('신규','계약금액변경','물가변동');
				$tab_value2 = array('1','2','3');
				for($i=0;$i<3;$i++) {
					if ($sub_index == $tab_value2[$i]) { ?>
						<div class="tab1"><a href="#" onclick="Tab_Selected('3','<?=$tab_value2[$i]?>');"><?
					} else { ?>
						<div class="tab2"><a href="#" onclick="Tab_Selected('3','<?=$tab_value2[$i]?>');"> <?	
					} 
					echo $tab_Titel2[$i]."</a></div>";
				} ?>

	<div style="float:right;clear:none;">
		<div style="float:left">   
			<select name="sel_year"  onchange="cdate();"  style="margin-top:5px;margin-right:5px;;"><?
				for($j=2005;$j<$uyear;$j++) { ?>
					<option value=<?=$j?> <?if($j == $sel_year){echo "selected";}?>><?=$j?>년</option> <?
				} ?>
			</select>

			<? if($type=="1"){?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<button class="btn2ws" onclick="Profit_Print();">출력</button>
		<?}?>
</table>
<table cellspacing=1 cellspacing=1 border=0 class="tb_linecolor" width=800>
	<tr><td height=2></td></tr>
</table>
<?}else if($tab_index == 4 || $tab_index == 5) {?>
<table border=0 cellpadding=0 cellspacing=0  width=800>
	<tr><td align="right">
		<select name=sel_year  onchange="cdate();"  class="inputnormal"> <?
				for($j=2005;$j<$uyear;$j++) { ?>
					<option value=<?=$j?> <?if($j == $sel_year){echo "selected";}?>><?=$j?>년</option> <?
				} ?>
		</select></td>
	</tr>
</table>
<?}?>
</div>
<table>
<tr><td valign="top">
<?

if($tab_index == 1) //수주/매출계획
{
	//$titlemsg="수주/매출 계획 입력 (".$sel_year.")";
?>
	<iframe scrolling=no frameborder=0 marginwidth=0 marginheight=0 width="800" height="700" src="./input.php"></iframe>

<?
}
else if($tab_index == 2) //수주/매출현황
{
	$ana_option=$sub_index;
	$ana_month="1";
	include "total.php";

}
else if($tab_index == 3) //수주현황(상세)
{
	//$ana_type=1;
	//include "contract_report.php";
	$ana_type=$sub_index;

	if($ana_type == "1") { //수주현황상세- 수주현황 보기
		include "contract_report.php";
		
	}
	if($ana_type == "2") { //수주현황상세-계약변경내역 보기
		//include "contracts_report_detail2.php";
		include "contract_report2.php";
	}

	if($ana_type == "3") { //수주현황상세-물가변동
		//include "contracts_report_detail3.php";
		include "contract_report3.php";
	}

}
else if($tab_index == 4) //매출현황(상세)
{
	$chk=true;
	$ana_month=1;
	include "sales_report.php";
}
else if($tab_index == 5) //수금현황(상세)
{	
	$chk=true;
	$ana_month=1;
	include "collection_report.php";
}
?>
</td></tr>
</table>
