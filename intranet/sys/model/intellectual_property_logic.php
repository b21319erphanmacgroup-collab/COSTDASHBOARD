<?php if($_REQUEST[excel]==""){ ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php }?>
<?php
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/function_intranet.php";

	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드

	extract($_GET);
	class IntellectualPropertyLogic {
		var $smarty;
		function IntellectualPropertyLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		

		//================================================================================
		// 한맥 지적재산권 입력화면  Logic
		//================================================================================	
		function InsertPage()
		{			
			global $db;
			extract($_REQUEST);
			
			if($_SESSION['auth_patent']=="1" || $_REQUEST['auth_patent']=="1")
			{
				$this->smarty->assign('auth_patent','1');
			}
			else
			{
				$this->smarty->assign('auth_patent','0');
			}
			
			$Controller="intellectual_property_controller.php";
			
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('companyname',$companyname);
			$this->smarty->assign('kind',$kind);
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('OPENER',$OPENER);
			$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_input_mvc.tpl");
		}


		//================================================================================
		// /지적재산권 insert logic
		//================================================================================	
		function InsertAction()
		{
			extract($_REQUEST);
		
			//$companyname 가족사별 명칭 /한맥 : HANMAC / 장헌 : JANGHEON / 피티씨 : PTC / 한라 : HALLA / 삼안 : SAMAN / 총괄기획실 : GPO
			global $db;
			global $CompanyKind,$companyname;
			global $auth_patent;
			
			$now_day=date("Y-m-d h:i:s");
			
			$QueryDataCnt=0;
			
			if($kind!=7)
			{
				if($regnum!="")
				{
					$sql0="SELECT * FROM intellectual_property_tbl WHERE kind='$kind' AND regnum='$regnum'";
					$re0=mysql_query($sql0,$db);
					$QueryDataCnt=mysql_num_rows($re0);
				}
				else 
				{
					$QueryDataCnt=0;
				}
			}
			elseif($kind==7)
			{
				$sql0="SELECT * FROM illt_new_tech_tbl WHERE kind='$kind' AND regnum='$regnum'";
				$re0=mysql_query($sql0,$db);
				$QueryDataCnt=mysql_num_rows($re0);
			}
			
			if($QueryDataCnt>0)
			{
				echo "duplication";
			}
			else
			{
				/**첨부파일 저장 시작**/
				$file_path="./../../../intranet_file/documents/illtfile";
				
				if($_FILES['fileCont1']['name']<>"" || $_FILES['fileCont1']['size']<>0)
				{
					$file=explode('.',$_FILES['fileCont1']['name']);
					$type=$file[count($file)-1];
					
					if($kind==7)
					{
						$filename=$regnum."_".date("Ymd").".".$type;
					}
					elseif($kind!=7)
					{
						$filename=$regnum.".".$type;
					}
					
					if(is_dir($file_path)){}
					else{mkdir($file_path,0777);}
					
					$vupload=$file_path.'/'.str_replace(" ","",$filename);
					$_FILES['fileCont1']['tmp_name']=iconv("UTF-8","EUC-KR",$_FILES['fileCont1']['tmp_name']);
					move_uploaded_file($_FILES['fileCont1']['tmp_name'],$vupload);
				}
				
				if($_FILES['fileCont2']['name']<>"" || $_FILES['fileCont2']['size']<>0)
				{
					$file=explode('.',$_FILES['fileCont2']['name']);
					$type=$file[count($file)-1];
					
					if($kind==7)
					{
						$imgname=$regnum."_".date("Ymd")."_img.".$type;
					}
					elseif($kind!=7)
					{
						$imgname=$regnum."_img.".$type;
					}
					
					if(is_dir($file_path)){}
					else{mkdir($file_path,0777);}
					
					$vupload=$file_path.'/'.str_replace(" ","",$imgname);
					$_FILES['fileCont2']['tmp_name']=iconv("UTF-8","EUC-KR",$_FILES['fileCont2']['tmp_name']);
					move_uploaded_file($_FILES['fileCont2']['tmp_name'],$vupload);
				}
				
				
				$sql = "select 
							max(techNo) AS no 
						from 
							(
								select 
									MAX(techNo) AS techNo 
								from 
									intellectual_property_tbl
							UNION ALL
								select 
									MAX(techNo) AS techNo 
								from illt_new_tech_tbl
							) A
						"; //techno 최대값 가지고오기
				$re=mysql_query($sql,$db);
				$lastno=mysql_result($re,0,"no");
				$Maxno=$lastno+1;
				
				/**등록종류로 if문 진행**/
				if($kind!=7)
				{
					$sql2="insert into intellectual_property_tbl";
					$sql2.=" (techNo, kind, status, title, regnum, regdate, appnum, appdate, enddate, rightholder, holderetc, inventor_name, country, fileName, imgName, centerYN, summary, mastercompany, UpdateDate, UpdateUser)";
					$sql2.=" values ('$Maxno','$kind','$status','$title','$regnum','$regdate','$appnum','$appdate','$end_date','$rightholder','$holderetc','$inventorname','$country','$filename','$imgname','$centerYN','$summary','$mastercompany', '$now_day','$user_id')"; 
					
					$re2=mysql_query($sql2,$db);
				}
				else
				{
					$sql2="insert into illt_new_tech_tbl";
					$sql2.=" (techNo, kind, status, title, regnum, protectCont, protectStart, protectEnd, techCont, techScope, rightholder, holderetc,  fileName,  imgName, centerYN, mastercompany,  UpdateDate, UpdateUser)";
					$sql2.=" values ('$Maxno','$kind','$status','$title','$regnum','$protectCont','$protectStart','$protectEnd','$techCont','$techScope','$rightholder','$holderetc','$filename','$imgname','$centerYN','$mastercompany','$now_day','$user_id')";
					
					$re2=mysql_query($sql2,$db);
				}
				
				
				if($re2){echo $Maxno;}
				else{echo "Fail";}
			}
		}
	//================================================================================
		// 지식재산권 납입정보 저장
		//================================================================================
		function PaymentSave()
		{
			global $db;
			extract($_REQUEST);
			
			$UpdateDate=date('Y-m-d H:i:s');
			
			if($paymentstatus=="Insert")
			{
				$InQuery="INSERT INTO
							illt_payamt_tbl
							(techNo,
							paymentyear,
							paymentdate,
							paymentamt,
							nextpaymentdate,
							paymentcom,
							remark,
							UpdateDate,
							UpdateUser)
						VALUES
							('$techNo',
							'$paymentyear',
							'$paymentdate',
							'$paymentamt',
							'$nextpaymentdate',
							'$mastercompany',
							'$remark',
							'$UpdateDate',
							'$user_id')";
				
				$InQueryre=mysql_query($InQuery,$db);
				if($InQueryre){$re=1;}
				else{$re='';}
			}
			elseif($paymentstatus=="Update")
			{
				$paymentamt=str_replace(',','',$paymentamt);
				
				$UpQuery="UPDATE 
						illt_payamt_tbl
					SET
						paymentdate='$paymentdate',
						paymentamt='$paymentamt',
						nextpaymentdate='$nextpaymentdate',
						paymentcom='$mastercompany',
						remark='$remark',
						UpdateDate='$UpdateDate',
						UpdateUser='$user_id'
					WHERE
						techNo='$techNo'
					AND paymentyear='$paymentyear'";
				
				$UpQueryre=mysql_query($UpQuery,$db);
				if($UpQueryre){$re=1;}
				else{$re='';}
			}
			elseif($paymentstatus=="")
			{
				$re=1;
			}
			if($re==1){echo $lastData;}else{echo "Fail";}
		}
		//================================================================================
		// 지식재산권 납입정보 삭제
		//================================================================================
		function PaymentDelete()
		{
			global $db;
			extract($_REQUEST);
			
			$DelQuery="DELETE FROM illt_payamt_tbl WHERE techNo='$techNo' AND paymentyear='$paymentyear'";
			$re=mysql_query($DelQuery,$db);
			
			if($re){echo "Success";}
			else{echo "Fail";}
		}
		//================================================================================
		// 지적재산권 읽기 (한맥-전체) logic
		//================================================================================	
		function UpdateReadPage()
		{			
			global $db;
			extract($_REQUEST);
			
			$info_data = array();			
			
			if($kind!=7)
			{
				$sql="select * from intellectual_property_tbl where techNo='$techNo' and kind='$kind'";
			}
			else
			{
				$sql="select * from illt_new_tech_tbl where techNo='$techNo' and kind='$kind'";
			}
			
			$re = mysql_query($sql,$db);
			
			while($re_row = mysql_fetch_array($re))
			{
				if($re_row[status]==1)
				{
					$re_row[statusname]='등록';
					$re_row[statusclass]='t01';
				}
				elseif($re_row[status]==2)
				{
					$re_row[statusname]='출원중';
					$re_row[statusclass]='t02';
				}
				elseif($re_row[status]==3)
				{
					$re_row[statusname]='거절';
					$re_row[statusclass]='t03';
				}
				elseif($re_row[status]==4)
				{
					$re_row[statusname]='포기';
					$re_row[statusclass]='t04';
				}
				elseif($re_row[status]==5)
				{
					$re_row[statusname]='취하';
					$re_row[statusclass]='t05';
				}
				elseif($re_row[status]==6)
				{
					$re_row[statusname]='소멸';
					$re_row[statusclass]='t06';
				}
				elseif($re_row[status]==7)
				{
					$re_row[statusname]='무료';
					$re_row[statusclass]='t07';
				}
				elseif($re_row[status]==8)
				{
					$re_row[statusname]='공개';
					$re_row[statusclass]='t08';
				}
				
				if($re_row[mastercompany]==1)
				{
					$re_row[mastercompanyname]="(주)바론컨설턴트";
				}
				elseif($re_row[mastercompany]==5)
				{
					$re_row[mastercompanyname]="(주)삼안";
				}
				elseif($re_row[mastercompany]==2)
				{
					$re_row[mastercompanyname]="(주)장헌산업";
				}
				elseif($re_row[mastercompany]==3)
				{
					$re_row[mastercompanyname]="(주)피티씨";
				}
				elseif($re_row[mastercompany]==4)
				{
					$re_row[mastercompanyname]="한라산업개발(주)";
				}
				elseif($re_row[mastercompany]==6)
				{
					$re_row[mastercompanyname]="(주)바론컨설턴트";
				}
				array_push($info_data,$re_row);
			}
			
			
			$payment_data=array();
			
			$sql2="select * from illt_payamt_tbl where techNo='$techNo'";
			$re2=mysql_query($sql2,$db);
			
			while($re_row2=mysql_fetch_array($re2))
			{
				if($re_row2[paymentyear]==13)
				{
					$re_row2[paymentyearName]="제 1-3 년분";
				}
				elseif($re_row2[paymentyear]==44)
				{
					$re_row2[paymentyearName]="제 4-4 년분";
				}
				elseif($re_row2[paymentyear]==55)
				{
					$re_row2[paymentyearName]="제 5-5 년분";
				}
				elseif($re_row2[paymentyear]==66)
				{
					$re_row2[paymentyearName]="제 6-6 년분";
				}
				elseif($re_row2[paymentyear]==77)
				{
					$re_row2[paymentyearName]="제 7-7 년분";
				}
				elseif($re_row2[paymentyear]==88)
				{
					$re_row2[paymentyearName]="제 8-8 년분";
				}
				elseif($re_row2[paymentyear]==99)
				{
					$re_row2[paymentyearName]="제 9-9 년분";
				}
				elseif($re_row2[paymentyear]==1010)
				{
					$re_row2[paymentyearName]="제 10-10 년분";
				}
				elseif($re_row2[paymentyear]==1111)
				{
					$re_row2[paymentyearName]="제 11-11 년분";
				}
				elseif($re_row2[paymentyear]==1212)
				{
					$re_row2[paymentyearName]="제 12-12 년분";
				}
				elseif($re_row2[paymentyear]==1313)
				{
					$re_row2[paymentyearName]="제 13-13 년분";
				}
				elseif($re_row2[paymentyear]==1414)
				{
					$re_row2[paymentyearName]="제 14-14 년분";
				}
				elseif($re_row2[paymentyear]==1515)
				{
					$re_row2[paymentyearName]="제 15-15 년분";
				}
				elseif($re_row2[paymentyear]==1616)
				{
					$re_row2[paymentyearName]="제 16-16 년분";
				}
				elseif($re_row2[paymentyear]==1717)
				{
					$re_row2[paymentyearName]="제 17-17 년분";
				}
				elseif($re_row2[paymentyear]==1818)
				{
					$re_row2[paymentyearName]="제 18-18 년분";
				}
				elseif($re_row2[paymentyear]==1919)
				{
					$re_row2[paymentyearName]="제 19-19 년분";
				}
				elseif($re_row2[paymentyear]==2020)
				{
					$re_row2[paymentyearName]="제 20-20 년분";
				}
				elseif($re_row2[paymentyear]==110)
				{
					$re_row2[paymentyearName]="제 1-10 년분";
				}
				
				
				array_push($payment_data,$re_row2);
			}
			
			$this->smarty->assign('mode',$mode);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('kind',$kind);
			$this->smarty->assign('techNo',$techNo);
			
			$this->smarty->assign('companyname',$companyname);
			$this->smarty->assign('auth_patent',$auth_patent);
			$this->smarty->assign('OPENER',$OPENER);
			
			$this->smarty->assign('info_data',$info_data);
			$this->smarty->assign('payment_data',$payment_data);
			
			if($mode=="mod"){
				$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_input_mvc.tpl");
			}
			else{
				if($kind!=7)
				{
					$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_read_mvc.tpl");
				}
				elseif($kind==7)
				{
					$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_read02_mvc.tpl");
				}
			}
		}

		//================================================================================
		// 한맥지적재산권 수정 logic
		//================================================================================	
		function UpdateAction()
		{
			global $db;
			extract($_REQUEST);
			
			$nowdate=date("Y-m-d h:i:s");

			$filepath="./../../../intranet_file/documents/illtfile";
			
			if($ex_fileName!=""){$filename=$ex_fileName;}
			if($ex_imgName!=""){$imgname=$ex_imgName;}
		
			if($_FILES['fileCont1']['name']!="" || $_FILES['fileCont1']['size']<>0)
			{
				if($ex_fileName!="")
				{
					unlink($filepath.'/'.trim($ex_fileName));
				}
				
				$file=explode('.',$_FILES['fileCont1']['name']);
				$type=$file[count($file)-1];
				
				if($ex_kind==7)
				{
					$filename=$regnum."_".date("Ymd").'.'.$type;
				}
				else
				{
					$filename=$regnum.'.'.$type;
				}
				
				if(is_dir($filepath)){}
				else{mkdir($filepath,0777);}
				
				$vupload=$filepath.'/'.str_replace(" ","",$filename);
				$_FILES['fileCont1']['tmp_name']=iconv("UTF-8","EUC-KR",$_FILES['fileCont1']['tmp_name']);
				move_uploaded_file($_FILES['fileCont1']['tmp_name'],$vupload);
			}
			
			
			if($_FILES['fileCont2']['name']!="" || $_FILES['fileCont2']['size']<>0)
			{
				if($ex_imgName!="")
				{
					unlink($filepath.'/'.trim($ex_imgName));
				}
				
				$file=explode('.',$_FILES['fileCont2']['name']);
				$type=$file[count($file)-1];
				
				if($ex_kind==7)
				{
					$imgname=$regnum."_".date("Ymd").'_img.'.$type;
				}
				else
				{
					$imgname=$regnum.'_img.'.$type;
				}
				
				if(is_dir($filepath)){}
				else{mkdir($filepath,0777);}
				
				$vupload=$filepath.'/'.str_replace(" ","",$imgname);
				$_FILES['fileCont2']['tmp_name']=iconv("UTF-8","EUC-KR",$_FILES['fileCont2']['tmp_name']);
				move_uploaded_file($_FILES['fileCont2']['tmp_name'],$vupload);
			}
			
			if($ex_kind!=7)
			{
				$sql="Update 
						intellectual_property_tbl 
					Set
						status='$status',
						title='$title',
						regnum='$regnum',
						regdate='$regdate',
						appnum='$appnum',
						appdate='$appdate',
						enddate='$end_date',
						rightholder='$rightholder',
						holderetc='$holderetc',
						inventor_name='$inventorname',
						country='$country',
						fileName='$filename',
						imgName='$imgname',
						centerYN='$centerYN',
						summary='$summary',
						mastercompany='$mastercompany',
						UpdateDate='$nowdate',
						UpdateUser='$memberID'
					Where 
						techNo='$techNo'
					and kind='$ex_kind'";
			}
			elseif($ex_kind==7)
			{
				$sql="Update 
						illt_new_tech_tbl 
					Set
						status='$status',
						title='$title',
						regnum='$regnum',
						protectCont='$protectCont',
						protectStart='$protectStart',
						protectEnd='$protectEnd',
						techCont='$techCont',
						techScope='$techScope',
						rightholder='$rightholder',
						holderetc='$holderetc',
						fileName='$filename',
						imgName='$imgname',
						centerYN='$centerYN',
						mastercompany='$mastercompany',
						UpdateDate='$nowdate',
						UpdateUser='$memberID'
					Where
						techNo='$techNo'
					and kind='$ex_kind'";
			}
			
			
			$re=mysql_query($sql,$db);
			
			if($re){echo $techNo;}
			else{echo "Fail";}
			
		}
		//================================================================================
		// 한맥지적재산권 삭제 Logic
		//================================================================================	
		function DeleteAction()
		{
			global $db;
			extract($_REQUEST);
			
			$path="./../../../intranet_file/documents/illtfile/";
			$nowdate=date("Y-m-d H:i:s");
		
			if($mode=="del")
			{
				if($ex_fileName!="")
				{
					if(is_file($path.trim($ex_fileName)))
					{
						unlink($path.trim($ex_fileName));
					}
					else
					{
						echo "Fail";
					}
				}
				if($ex_imgName!="")
				{
					if(is_file($path.trim($ex_imgName)))
					{
						unlink($path.trim($ex_imgName));
					}
					else
					{
						echo "Fail";
					}
				}
				
				$sql2="delete from illt_payamt_tbl where techNo='$techNo'";
				mysql_query($sql2,$db);
				
				if($ex_kind!=7)
				{
					$sql="delete from intellectual_property_tbl where kind='$ex_kind' and techNo='$techNo'";
				}
				else
				{
					$sql="delete from illt_new_tech_tbl where kind='$ex_kind' and techNo='$techNo'";
				}
				
				$re=mysql_query($sql,$db);
				
				if($re){echo 'Success';}else{echo "Fail";}
			}
			else
			{
				if($mode=="delfile")
				{
					
					if(is_file($path.trim($ex_fileName)))
					{
						unlink($path.trim($ex_fileName));
						if($ex_kind!=7)
						{
							$sql="Update intellectual_property_tbl Set ";
							$sql.="fileName='', UpdateDate='$nowdate',UpdateUser='$memberID'";
							$sql.=" Where kind='$ex_kind' and techNo='$techNo'";
						}
						elseif($ex_kind==7)
						{
							$sql="Update illt_new_tech_tbl Set ";
							$sql.="fileName='', UpdateDate='$nowdate',UpdateUser='$memberID'";
							$sql.=" Where kind='$ex_kind' and techNo='$techNo'";
						}
						
						$re=mysql_query($sql,$db);
						if($re){echo $techNo;}else{echo "Fail";}
					}
					else
					{
						echo "Fail";
					}
				}
				elseif($mode=="delimg")
				{
					if(is_file($path.trim($ex_imgName)))
					{
						unlink($path.trim($ex_imgName));
						if($ex_kind!=7)
						{
							$sql="Update intellectual_property_tbl Set ";
							$sql.="imgName='', UpdateDate='$nowdate',UpdateUser='$memberID'";
							$sql.=" Where kind='$ex_kind' and techNo='$techNo'";
						}
						elseif($ex_kind==7)
						{
							$sql="Update illt_new_tech_tbl Set ";
							$sql.="imgName='', UpdateDate='$nowdate',UpdateUser='$memberID'";
							$sql.=" Where kind='$ex_kind' and techNo='$techNo'";
						}
						
						$re=mysql_query($sql,$db);
						if($re){echo $techNo;}else{echo "Fail";}
					}
					else
					{
						echo "Fail";
					}
				}
			}
		}
					
		//================================================================================
		// 지적재산권 전체 법인 list logic
		//================================================================================	

		function View()
		{
			global $db;
			extract($_REQUEST);
			if($OPENER=="PLANNING_MNG")
			{
				$certifiQuery="SELECT Certificate FROM member_tbl WHERE MemberNo='$memberID' AND Certificate LIKE '%지적%'";
				$Re_certifiQuery=mysql_query($certifiQuery,$db);
				$certifiNum=mysql_num_rows($Re_certifiQuery);
				
				if($certifiNum>0)
				{
					$_REQUEST["auth_patent"]='1';
				}
			}
			
			if($excel=='excel')
			{	
				if($tab_index==1){$kindName='특허';}
				elseif($tab_index==2){$kindName='디자인';}
				elseif($tab_index==3){$kindName='상표';}
				elseif($tab_index==4){$kindName='실용신안';}
				elseif($tab_index==5){$kindName='프로그램 저작권';}
				elseif($tab_index==6){$kindName='해외특허';}
				elseif($tab_index==7){$kindName='신기술';}
				elseif($tab_index==''){$kindName='전체';}
				
				$this->PrintExcelHeader02("지식재산권 ".$kindName." 리스트",$excel);
				$this->Intellectual_Property_ExcelView();
			}
			elseif($excel!='excel')
			{
				if($tab_index==""){$tab_index=1;}
				if($_SESSION["auth_patent"]==1){$_REQUEST["auth_patent"]=1;}
				if($_REQUEST["companyname"]=="HANMAC"){$company_index="1";}
				elseif($_REQUEST["companyname"]=="JANGHEON"){$company_index="2";}
				elseif($_REQUEST["companyname"]=="PTC"){$company_index="3";}
				elseif($_REQUEST["companyname"]=="HALLA"){$company_index="4";}
				elseif($_REQUEST["companyname"]=="SAMAN"){$company_index="5";}
				elseif($_REQUEST["companyname"]=="BARON"){$company_index="6";}
				elseif($_REQUEST[OPENER]=="PLANNING_MNG"){ $company_index="0";}
				
				$query_data=array();
				
				//신기술이 아닐 때
				if($tab_index!=7){
					$sql="SELECT
							*
						FROM
							intellectual_property_tbl
						WHERE
							kind='$tab_index'";
					if($input_select_01!='')
					{
						$sql.=" AND status like '$input_select_01'";
					}
					if($input_select_02=='Y')
					{
						$sql.=" AND centerYN='$input_select_02'";
					}
					elseif($input_select_02=='N')
					{
						$sql.=" AND (centerYN='$input_select_02' OR centerYN='')";
					}
					if($input_item_01!='')
					{
						$sql.="AND (
								title like '%$input_item_01%'
								OR
								appnum like '%$input_item_01%'
								OR
								regnum like '%$input_item_01%'
							)";
					}
					if($input_select_03!="")
					{
						$sql.=" AND rightholder like '%$input_select_03%'";
					}
					elseif($company_index!='0')
					{
						$sql.=" AND rightholder like '%$company_index%'";
					}
					$sql.=" ORDER BY appdate DESC";
				}
				//신기술 일 때
				else{
					$sql="SELECT
							*
						FROM
							illt_new_tech_tbl
						WHERE
							kind='$tab_index'";
					
					if($input_select_01!=''){
						$sql.=" AND status like '$input_select_01'";
						
					}
					if($input_select_02=='Y'){
						$sql.=" AND centerYN='$input_select_02'";
					}
					elseif($input_select_02=='N'){
						$sql.=" AND (centerYN='$input_select_02' OR centerYN='')";
					}
					if($input_item_01!=''){
						$sql.="AND (
								title like '%$input_item_01%'
								OR
								regnum like '%$input_item_01%'
							)";
					}
					if($input_select_03!=''){
						$sql.=" AND rightholder like '%$input_select_03%'";
					}
					elseif($company_index!='0'){
						$sql.=" AND rightholder like '%$company_index%'";
					}
					$sql.=" ORDER BY protectStart DESC";
				}
				
				
				
				
				$re=mysql_query($sql,$db);
				while($re_row=mysql_fetch_array($re)){
					$re_row[title2]=$re_row[title];
					if(mb_strlen($re_row[title],'utf-8')>27)
					{
						$re_row[title]=mb_substr($re_row[title],0,25,'utf-8').'...';
					}
					
					if($re_row[status]==1)
					{
						$re_row[statusname]='등록';
						$re_row[statusclass]='t01';
					}
					elseif($re_row[status]==2)
					{
						$re_row[statusname]='출원중';
						$re_row[statusclass]='t02';
					}
					elseif($re_row[status]==3)
					{
						$re_row[statusname]='거절';
						$re_row[statusclass]='t03';
					}
					elseif($re_row[status]==4)
					{
						$re_row[statusname]='포기';
						$re_row[statusclass]='t04';
					}
					elseif($re_row[status]==5)
					{
						$re_row[statusname]='취하';
						$re_row[statusclass]='t05';
					}
					elseif($re_row[status]==6)
					{
						$re_row[statusname]='소멸';
						$re_row[statusclass]='t06';
					}
					elseif($re_row[status]==7)
					{
						$re_row[statusname]='무료';
						$re_row[statusclass]='t07';
					}
					elseif($re_row[status]==8)
					{
						$re_row[statusname]='공개';
						$re_row[statusclass]='t08';
					}
					
					array_push($query_data,$re_row);
				}
	
				$tab_Title = array('특허','디자인','상표','실용신안','프로그램 저작권','해외특허','신기술');
				$tab_value = array('1','2','3','4','5','6','7');
				
				$this->smarty->assign('OPENER',$OPENER);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('tab_Title',$tab_Title);
				$this->smarty->assign('tab_value',$tab_value);
				$this->smarty->assign('tab_index',$tab_index);
				$this->smarty->assign('input_item_01',$input_item_01);
				$this->smarty->assign('input_select_01',$input_select_01);
				$this->smarty->assign('input_select_02',$input_select_02);
				$this->smarty->assign('input_select_03',$input_select_03);
				$this->smarty->assign('companyname',$_REQUEST["companyname"]);
				$this->smarty->assign('auth_patent',$_REQUEST["auth_patent"]);
				
				$this->smarty->assign('query_data',$query_data);
				
				$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_contents_mvc.tpl");
			}
		}
		
		//================================================================================
		// 지식재산권 납부정보  Logic
		//================================================================================
		function PaymentPage()
		{
			global $db;
			extract($_REQUEST);
			
			if($start_date==""){$start_date=date('Y-m-d',strtotime("-1 year"));}
			if($end_date==""){$end_date=date('Y-m-d',strtotime("+1 year"));}
			
			$query_data=array();
			if($excel=='excel')
			{
				if($SubAction=="LastData"){$Name="최종";}
				else{$Name="전체";}
				if($displaylist_03=="전체"){$displaylist_03='';}
				$this->PrintExcelHeader02("지식재산권 ".$displaylist_03." ".$Name." 납부 리스트",$excel);
				$this->Payment_Info_ExcelView();
			}
			else
			{
				$sql="SELECT
						A.techNo,
						A.kind,
						A.title,
						A.regnum,
						A.regdate,
						A.enddate,
						B.paymentyear,
						B.paymentdate,
						B.paymentamt,
						B.nextpaymentdate,
						B.paymentcom,
						B.remark
					FROM
						(SELECT
							techNo,
							kind,
							status,
							title,
							regnum,
							regdate,
							enddate
						FROM
							intellectual_property_tbl
						UNION ALL
						SELECT
							techNo,
							kind,
							status,
							title,
							regnum,
							protectStart AS regdate,
							protectEnd AS enddate
						FROM
							illt_new_tech_tbl
						) A
					RIGHT JOIN
						illt_payamt_tbl B
					ON B.techNo=A.techNo
					WHERE
						B.nextpaymentdate BETWEEN '$start_date' AND '$end_date'";
				if($SubAction=='LastData')
				{
					$sql.=" AND B.paymentdate=(SELECT max(paymentdate) FROM illt_payamt_tbl WHERE techNo=B.techNo) ";
					$sql.=" AND A.status = '1'";
					$OrderColumn="B.nextpaymentdate ASC";
				}
				else
				{
					$OrderColumn="B.paymentdate DESC";
				}
				if($input_select_01!='')
				{
					$sql.=" AND A.kind='$input_select_01'";
				}
				if($input_item_01!='')
				{
					$sql.=" AND (A.regnum like '%$input_item_01%' OR A.title like '%$input_item_01%')";
				}
				
				$sql.=" ORDER BY ".$OrderColumn;
				
				$re=mysql_query($sql,$db);
				while($re_row=mysql_fetch_array($re))
				{
					array_push($query_data,$re_row);
				}
				
				$this->smarty->assign('OPENER',$OPENER);
				$this->smarty->assign('SubAction',$SubAction);
				$this->smarty->assign('memberID',$memberID);
				$this->smarty->assign('start_date',$start_date);
				$this->smarty->assign('end_date',$end_date);
				$this->smarty->assign('input_select_01',$input_select_01);
				$this->smarty->assign('input_item_01',$input_item_01);
				
				$this->smarty->assign('query_data',$query_data);
				$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_payment_info_mvc.tpl");
			}
		}
		
		//================================================================================
		// 지식재산권 납부정보 엑셀  Logic
		//================================================================================
		function Payment_Info_ExcelView()
		{
			global $db;
			extract($_REQUEST);
			$query_data=array();
			$sql="SELECT
					A.techNo,
					A.kind,
					A.title,
					A.regnum,
					A.regdate,
					A.enddate,
					B.paymentyear,
					B.paymentdate,
					B.paymentamt,
					B.nextpaymentdate,
					B.paymentcom,
					B.remark
				FROM
					(SELECT
						techNo,
						kind,
						title,
						regnum,
						regdate,
						enddate
					FROM
						intellectual_property_tbl
					UNION ALL
					SELECT
						techNo,
						kind,
						title,
						regnum,
						protectStart AS regdate,
						protectEnd AS enddate
					FROM
						illt_new_tech_tbl
					) A
				RIGHT JOIN
					illt_payamt_tbl B
				ON B.techNo=A.techNo
				WHERE
					B.nextpaymentdate BETWEEN '$start_date' AND '$end_date'";
			if($SubAction=='LastData')
			{
				$sql.=" AND B.paymentdate=(SELECT max(paymentdate) FROM illt_payamt_tbl WHERE techNo=B.techNo)";
				$OrderColumn="B.nextpaymentdate ASC";
			}
			else
			{
				$OrderColumn="B.paymentdate DESC";
			}
			if($input_select_01!='')
			{
				$sql.=" AND A.kind='$input_select_01'";
			}
			if($input_item_01!='')
			{
				$sql.=" AND (A.regnum like '%$input_item_01%' OR A.title like '%$input_item_01%')";
			}
			
			$sql.=" ORDER BY ".$OrderColumn;
			echo $sql;
			$re=mysql_query($sql,$db);
			while($re_row=mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}
			
			if($SubAction=="LastData"){$Name="최종";}
			else{$Name="전체";}
			
			$this->smarty->assign('displaylist_01',$displaylist_01);
			$this->smarty->assign('displaylist_02',$displaylist_02);
			$this->smarty->assign('displaylist_03',$displaylist_03);
			$this->smarty->assign('displaylist_04',$displaylist_04);
			
			$this->smarty->assign('Name',$Name);
			$this->smarty->assign('OPENER',$OPENER);
			$this->smarty->assign("query_data",$query_data);
			$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_payment_info_excel_mvc.tpl");
		}
		
		
		//================================================================================
		// 지적재산권 전체 법인 list logic
		//================================================================================
		
		function Intellectual_Property_ExcelView()
		{
			global $db;
			extract($_REQUEST);
			
			$query_data=array();
			
			if($tab_index!=7)
			{
				$sql="SELECT
						*
					FROM
						intellectual_property_tbl
					WHERE
						kind='$tab_index'";
				if($input_select_01!="")
				{
					$sql.=" AND status='$input_select_01'";
				}
				if($input_select_02!="")
				{
					$sql.=" AND centerYN='$input_select_02'";
				}
				if($input_item_01!="")
				{
					$sql.=" AND (
								regnum like '%$input_item_01%'
								OR
								appnum like '%$input_item_01%'
								OR
								title like '%$input_item_01%'
								)";
				}
				if($input_select_03!="")
				{
					$sql.=" AND rightholder LIKE '%$input_select_03%'";
				}
				$sql.=" ORDER BY appdate DESC";
			}
			elseif($tab_index==7)
			{
				$sql="SELECT
						*
					FROM
						illt_new_tech_tbl
					WHERE
						kind='$tab_index'";
				if($input_select_01!="")
				{
					$sql.=" AND status='$input_select_01'";
				}
				if($input_select_02!="")
				{
					$sql.=" AND centerYN='$input_select_02'";
				}
				if($input_item_01!="")
				{
					$sql.=" AND (
								regnum like '%$input_item_01%'
								OR
								appnum like '%$input_item_01%'
								OR
								title like '%$input_item_01%'
								)";
				}
				if($input_select_03!="")
				{
					$sql.=" AND rightholder LIKE '%$input_select_03%'";
				}
				$sql.="ORDER BY protectStart DESC";
			}
			
			$re=mysql_query($sql);
			while($re_row=mysql_fetch_array($re))
			{
				if($re_row[status]==1)
				{
					$re_row[statusname]='등록';
				}
				elseif($re_row[status]==2)
				{
					$re_row[statusname]='출원중';
				}
				elseif($re_row[status]==3)
				{
					$re_row[statusname]='거절';
				}
				elseif($re_row[status]==4)
				{
					$re_row[statusname]='포기';
				}
				elseif($re_row[status]==5)
				{
					$re_row[statusname]='취하';
				}
				elseif($re_row[status]==6)
				{
					$re_row[statusname]='소멸';
				}
				elseif($re_row[status]==7)
				{
					$re_row[statusname]='무료';
				}
				elseif($re_row[status]==8)
				{
					$re_row[statusname]='공개';
				}
				array_push($query_data,$re_row);
			}
			
			if($tab_index==1){$kindName='특허';}
			elseif($tab_index==2){$kindName='디자인';}
			elseif($tab_index==3){$kindName='상표';}
			elseif($tab_index==4){$kindName='실용신안';}
			elseif($tab_index==5){$kindName='프로그램 저작권';}
			elseif($tab_index==6){$kindName='해외특허';}
			elseif($tab_index==7){$kindName='신기술';}
			elseif($tab_index==''){$kindName='전체';}
			
			$this->smarty->assign('displaylist_01',$displaylist_01);
			$this->smarty->assign('displaylist_02',$displaylist_02);
			$this->smarty->assign('displaylist_03',$displaylist_03);
			$this->smarty->assign('displaylist_04',$displaylist_04);
			
			$this->smarty->assign('OPENER',$OPENER);
			$this->smarty->assign('kindName',$kindName);
			$this->smarty->assign('query_data',$query_data);
			$this->smarty->display("intranet/common_contents/work_intellectual_property/illt_contents_Excel_mvc.tpl");
		}
		
		//============================================================================
		//함수
		//============================================================================
		
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

?>