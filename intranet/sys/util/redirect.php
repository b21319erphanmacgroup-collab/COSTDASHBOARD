<?
session_start();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<?
extract($_REQUEST);

$memberID		=	$_REQUEST['memberID'];
$Company_Kind	=	$_REQUEST['CompanyKind'];
$group_code		=	$_REQUEST['group_code'];
$group_name		=	$_REQUEST['group_name'];
$rank_code		=	$_REQUEST['rank_code'];
$rank_name		=	$_REQUEST['rank_name'];
$writer_name	=	$_REQUEST['writer_name'];
$JuminNo		=	$_REQUEST['JuminNo'];

$EduNo		=	$_REQUEST['EduNo'];

$group_code=(int)$group_code;

$_SESSION['memberID']=$memberID;
$_SESSION['SS_memberID']=$memberID;


if($EduNo=="1"){     //한맥 하버드특강
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=3&sub_index=1&tab_index=48";
}else if($EduNo=="2"){	//한맥 EBS특강
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=3&sub_index=1&tab_index=49";
}else if($EduNo=="3"){	//한맥 경영보고서
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=3&sub_index=1&tab_index=200";
}else if($EduNo=="4"){	//한맥 영상보고서
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=3&sub_index=1&tab_index=47";
}else if($EduNo=="5"){	//한맥 인문학특강
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=3&sub_index=1&tab_index=300";
}else if($EduNo=="6"){	//한맥 기타
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=3&sub_index=1&tab_index=80";
}else if($EduNo=="7"){	//한맥 자기개발/리더십
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=40";
}else if($EduNo=="8"){	//한맥 마케팅
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=41";
}else if($EduNo=="9"){	//한맥 경영기법/전략
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=42";
}else if($EduNo=="10"){	//한맥 비즈니스
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=43";
}else if($EduNo=="11"){	//한맥 경제전망
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=44";
}else if($EduNo=="12"){ //한맥 경제정책
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=45";
}else if($EduNo=="13"){	//한맥 기타
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=46";
}else if($EduNo=="14"){	//한맥 윤대편의 마음연구소
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=90";
}else if($EduNo=="15"){	//한맥 입이트이는영어
	$re_url="../controller/lecture_controller.php?ActionMode=view&Category=2&sub_index=1&tab_index=91";
}else if($EduNo=="20"){ // 삼안 도서요약
	$re_url="../controller/notice_controller.php?ActionMode=view&notice_sub=2";
}else if($EduNo=="21"){	// 삼안 기초영어
	$re_url="../controller/notice_controller.php?ActionMode=view&notice_sub=3";
}else if($EduNo=="22"){	// 한맥 MyClass
	$re_url="../controller/MyClass_controller.php?ActionMode=MyClass";
}else if($EduNo=="23"){	// 한맥 추천컨텐츠
	$re_url="../controller/ClassRecommendController.php?ActionMode=Main";
}else if($EduNo=="24"){	// 한맥 배움터
	$re_url="../controller/Study_Controller2.php?ActionMode=MyClass_Mobile&MainAction=HanmacPick&mobile=y&get_memberID=$memberID";
}else{

}



$re_url.="&memberID=$memberID&Company_Kind=$Company_Kind&group_code=$group_code&group_name=$group_name&rank_code=$rank_code&rank_name=$rank_name&writer_name=$writer_name&registration=$JuminNo&insert_member=$writer_name&position=$rank_name&position_code=$rank_code&dept_code=$group_code&dept=$group_name";


?>
<script type="text/javascript">
	//alert("<?=$re_url?>");
	location.href="<?=$re_url?>";
</script>