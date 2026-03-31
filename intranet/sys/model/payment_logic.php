<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

	/***************************************
	* 출금전표
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/ 

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/approval_function.php";


	extract($_GET);
	class PaymentLogic {
		var $smarty;
		function PaymentLogic($smarty)
		{
			$this->smarty=$smarty;
		}

	
		//================================================================================
		// 출금전표 작성 Logic
		//================================================================================	
		function InsertPage()
		{

			global $db;
			global $auth,$memberID,$mode,$kind,$PRINT;

				$RG_Date=date("Y-m-d");
				$GroupName=MemberNo2GroupName($memberID);
				$GroupCode=MemberNo2GroupCode($memberID); 
				$makerKor=MemberNo2Name($memberID);
				$doc_status = $DOC_STATUS_CREATE ;


				if($kind==""){$kind=1;}
				if($billdate==""){$billdate=date("Y-m-d");}
				if($claimdate==""){$claimdate=date("Y-m-d");}
				if($expense_duedate==""){$expense_duedate=date("Y-m-d", strtotime(date("Y-m-d")." + 7day"));}
				
				$claim_date=explode("-",$claimdate);

				$this->smarty->assign('memberID',$memberID);	
				$this->smarty->assign('mode',$mode);	
				$this->smarty->assign('kind',$kind);	
				$this->smarty->assign('RG_Date',$RG_Date);	
				$this->smarty->assign('GroupName',$GroupName);	
				$this->smarty->assign('GroupCode',$GroupCode);	
				$this->smarty->assign('makerKor',$makerKor);
				$this->smarty->assign('billdate',$billdate);	
				$this->smarty->assign('expense_duedate',$expense_duedate);	
				$this->smarty->assign('claimdate',$claimdate);	
				$this->smarty->assign('claim_date',$claim_date);	
				$this->smarty->assign('doc_status',$doc_status);	
				$this->smarty->assign('PRINT',$PRINT);	

				$this->smarty->assign("page_action","payment_controller.php");

				$this->smarty->display("intranet/common_contents/work_documents/payment_input_mvc.tpl");

				//프린트 페이지 자동 미리보기 시간 설정===============================
				if($PRINT=="YES")
				{
					$this->smarty->assign('mode',"print");
					$this->smarty->display("intranet/js_page.tpl");
				}
				//프린트 페이지 자동 미리보기 시간 설정 끝=============================
		}

	
		//================================================================================
		// 출금전표 Insert Logic
		//================================================================================	
		function InsertAction()
		{

			global $db;
			global $auth,$memberID;
			global $claimdate,$billdate,$ProjectCode,$BridgeNo;
			global $ProjectNickName,$GroupCode,$GroupName,$Team;
			global $DepartAccCode,$DepartAccName,$VendorType,$VendorCode,$VendorName;
			global $AccSubstance,$buy_money,$buy_vat,$buy_amount,$today,$expense_duedate;

			global $kind,$DepartAccMenu,$Substance_detail;
			global $etc1,$etc2,$etc3,$etc4,$proof_doc;
			global $maker,$makerKor,$buy_amountKor;

				$today=date("Y-m-d H:i:s");

				if($CompanyKind=="JANG")				
				{
					$ProjectNickName=ProjectCode2Bridge($ProjectCode,$BridgeNo);
				}

				if($maker==""){$maker=$memberID;}

				$buy_amount=str_replace(",","",$buy_amount);
				$buy_money=str_replace(",","",$buy_money);
				$buy_vat=str_replace(",","",$buy_vat);

				if($buy_money=="" && $buy_vat=="")
				{
					$buy_money=$buy_amount;
				}

					if($CompanyKind=="JANG")				
					{
						$sql2 = "insert into project_account_tbl(claimdate, billdate, ProjectCode,BridgeNo, ProjectNickName, GroupCode, GroupName,Team, AccCode, AccName, VendorType, VendorCode, VendorName, AccSubstance, buy_money, buy_vat, buy_amount, UpdateDate, UpdateUser,expense_duedate,expensedate)
						values('$claimdate', '$billdate', '$ProjectCode','$BridgeNo', '$ProjectNickName', '$GroupCode', '$GroupName','$Team','$DepartAccCode', '$DepartAccName',  '$VendorType', '$VendorCode', '$VendorName', '$AccSubstance', '$buy_money', '$buy_vat', '$buy_amount', '$today', '$memberID','$expense_duedate','')";
					}
					else if($CompanyKind=="PILE")				
					{
						$sql2 = "insert into project_account_tbl(claimdate, billdate, ProjectCode, ProjectNickName, GroupCode, GroupName, AccCode, AccName, VendorType, VendorCode, VendorName, AccSubstance, buy_money, buy_vat, buy_amount, UpdateDate, UpdateUser,expense_duedate,expensedate)
						values('$claimdate', '$billdate', '$ProjectCode', '$ProjectNickName', '$GroupCode', '$GroupName','$DepartAccCode', '$DepartAccName',  '$VendorType', '$VendorCode', '$VendorName', '$AccSubstance', '$buy_money', '$buy_vat', '$buy_amount', '$today', '$memberID','$expense_duedate','')";
					}
						mysql_query($sql2,$db);

				if($billdate <>""){
					if($CompanyKind=="JANG")				
					{
						$sql3="select max(account_no) account_no from project_account_tbl where ProjectCode='$ProjectCode' and BridgeNo='$BridgeNo' and billdate='$billdate' and AccName='$DepartAccName' and claimdate='$claimdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount'";
					}
					else if($CompanyKind=="PILE")				
					{
						$sql3="select max(account_no) account_no from project_account_tbl where ProjectCode='$ProjectCode' and billdate='$billdate' and AccName='$DepartAccName' and claimdate='$claimdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount'";
					}
				}else
				{
					if($CompanyKind=="JANG")				
					{
						$sql3="select max(account_no) account_no from project_account_tbl where ProjectCode='$ProjectCode' and BridgeNo='$BridgeNo' and AccName='$DepartAccName' and claimdate='$claimdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount'";
					}
					else if($CompanyKind=="PILE")				
					{
						$sql3="select max(account_no) account_no from project_account_tbl where ProjectCode='$ProjectCode' and AccName='$DepartAccName' and claimdate='$claimdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount'";
					}
				}

				
				$re3=mysql_query($sql3,$db);
				$maxaccount_no=mysql_result($re3,0,"account_no");

				for($i=0; $i<16; $i++) {
					$pdoc= $pdoc . $proof_doc[$i]."/n";
				}

					if($CompanyKind=="JANG")				
					{
						$sql = "insert into payment_voucher_tbl (kind,ProjectCode,ProjectName, AccSubstance, DepartAccMenu, DepartAccCode, DepartAccName, VendorType, VendorCode, VendorName, Substance_detail, etc1, etc2, etc3, etc4, claimdate, billdate, GroupCode, GroupName,Team, maker, makerKor, UpdateDate, UpdateUser, buy_money, buy_vat, buy_amount, buy_amountKor, proof_doc,account_no,expense_duedate) 
						values('$kind','$ProjectCode','$ProjectNickName', '$AccSubstance', '$DepartAccMenu', '$DepartAccCode', '$DepartAccName', '$VendorType', '$VendorCode', '$VendorName', '$Substance_detail', '$etc1', '$etc2', '$etc3', '$etc4', '$claimdate', '$billdate', '$GroupCode', '$GroupName','$Team','$maker', '$makerKor', '$today', '$memberID', '$buy_money', '$buy_vat', '$buy_amount', '$buy_amountKor', '$pdoc','$maxaccount_no','$expense_duedate')";
					}
					else if($CompanyKind=="PILE")				
					{
						$sql = "insert into payment_voucher_tbl (kind,ProjectCode,ProjectName, AccSubstance, DepartAccMenu, DepartAccCode, DepartAccName, VendorType, VendorCode, VendorName, Substance_detail, etc1, etc2, etc3, etc4, claimdate, billdate, GroupCode, GroupName, maker, makerKor, UpdateDate, UpdateUser, buy_money, buy_vat, buy_amount, buy_amountKor, proof_doc,account_no,expense_duedate) 
						values('$kind','$ProjectCode','$ProjectNickName', '$AccSubstance', '$DepartAccMenu', '$DepartAccCode', '$DepartAccName', '$VendorType', '$VendorCode', '$VendorName', '$Substance_detail', '$etc1', '$etc2', '$etc3', '$etc4', '$claimdate', '$billdate', '$GroupCode', '$GroupName','$maker', '$makerKor', '$today', '$memberID', '$buy_money', '$buy_vat', '$buy_amount', '$buy_amountKor', '$pdoc','$maxaccount_no','$expense_duedate')";
					}
						mysql_query($sql,$db);

				if($billdate <>""){
					if($CompanyKind=="JANG")				
					{
						$sql4="select max(id) id from payment_voucher_tbl where kind='$kind' and ProjectCode='$ProjectCode' and BridgeNo='$BridgeNo' and claimdate='$claimdate' and billdate='$billdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount' and Substance_detail='$Substance_detail'";
					}
					else if($CompanyKind=="PILE")				
					{
						$sql4="select max(id) id from payment_voucher_tbl where kind='$kind' and ProjectCode='$ProjectCode' and claimdate='$claimdate' and billdate='$billdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount' and Substance_detail='$Substance_detail'";
					}
				}else{
					if($CompanyKind=="JANG")				
					{
						$sql4="select max(id) id from payment_voucher_tbl where kind='$kind' and ProjectCode='$ProjectCode' and BridgeNo='$BridgeNo' and claimdate='$claimdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount' and Substance_detail='$Substance_detail'";
					}
					else if($CompanyKind=="PILE")				
					{
						$sql4="select max(id) id from payment_voucher_tbl where kind='$kind' and ProjectCode='$ProjectCode' and claimdate='$claimdate' and AccSubstance ='$AccSubstance' and buy_amount='$buy_amount' and Substance_detail='$Substance_detail'";
					}
				}


				$re4=mysql_query($sql4,$db);
				$maxid=mysql_result($re4,0,"id");

				$this->smarty->assign('claimdate',$claimdate);
				$this->smarty->assign('claim_date',$claim_date);

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"payment_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");
		}

	
		//================================================================================
		// 출금전표 읽기 Logic
		//================================================================================	
		function UpdateReadPage()
		{

			global $db;
			global $auth,$id,$memberID,$mode,$kind,$PRINT;

			if($mode=="print"){$PRINT="YES";}

				$RG_Date=date("Y-m-d");
				$GroupName=MemberNo2GroupName($memberID);
				$GroupCode=MemberNo2GroupCode($memberID); 
				$makerKor=MemberNo2Name($memberID);
				$doc_status = $DOC_STATUS_CREATE ;


				if($kind==""){$kind=1;}
				if($billdate==""){$billdate=date("Y-m-d");}
				if($claimdate==""){$claimdate=date("Y-m-d");}
				if($expense_duedate==""){$expense_duedate=date("Y-m-d", strtotime(date("Y-m-d")." + 7day"));}
				
				$claim_date=explode("-",$claimdate);
				$sql7="select * from payment_voucher_tbl where id = $id";

				$re7 = mysql_query($sql7,$db);
				while($re_row7 = mysql_fetch_array($re7))
				{

					$id =$re_row7[id] ;//id
					$kind =$re_row7[kind] ;//kind
					$ProjectCode =$re_row7[ProjectCode] ;//프로젝트코드
					$ProjectName =$re_row7[ProjectName] ;//프로젝트네임	
					$AccSubstance =$re_row7[AccSubstance] ;//내역
					$DepartAccMenu =$re_row7[DepartAccMenu] ;//부서계정과목대분류
					$DepartAccCode =$re_row7[DepartAccCode] ;//부서계정과목코드
					$DepartAccName =$re_row7[DepartAccName] ;//부서계정과목명칭
					$VendorType =$re_row7[VendorType] ;//거래처 타입
					$VendorCode =$re_row7[VendorCode] ;//거래처 코드
					$VendorName =$re_row7[VendorName] ;//거래처 상호
					$claimdate =$re_row7[claimdate] ;//청구일
					
					$claim_date=explode("-",$claimdate);

					$billdate =$re_row7[billdate] ;//계산서 발행일
					$Substance_detail =$re_row7[Substance_detail] ;//상세내역
					$etc1 =$re_row7[etc1] ;//특기사항1
					$etc2 =$re_row7[etc2] ;//특기사항2
					$etc3 =$re_row7[etc3] ;//특기사항3
					$etc4 =$re_row7[etc4] ;//특기사항4
					$GroupCode =$re_row7[GroupCode] ;//부서코드
					$GroupName =$re_row7[GroupName] ;//부서명
					$maker =$re_row7[maker] ;//작성자 코드
					
					if($GroupCode=="07")
						{ //현장소장
						$makerKor=MemberNo2SiteName($maker);
						}
					else
						{
						$makerKor=MemberNo2Name($maker);
						}	
					
					$UpdateDate =$re_row7[UpdateDate] ;//업데이트 날짜
					$UpdateUser =$re_row7[UpdateUser] ;//업데이트자 코드 
					$buy_money =$re_row7[buy_money] ;//공급가액
					$buy_vat =$re_row7[buy_vat] ;//부가세
					$buy_amount =$re_row7[buy_amount] ;//합계금액
					$buy_amountKor =$re_row7[buy_amountKor] ;//합계금액 한글표기
					$proof_doc =$re_row7[proof_doc] ;//
					$account_no =$re_row7[account_no] ;//
					$expense_duedate =$re_row7[expense_duedate] ;//
					$BridgeNo =$re_row7[BridgeNo] ;//
							
				}
						
				$proof_doc=split("/n",$proof_doc); //증빙등

				if($kind==""){$kind=1;}
				if($billdate==""){$billdate=date("Y-m-d");}
				if($expense_duedate==""){$expense_duedate=date("Y-m-d", strtotime(date("Y-m-d")." + 7day"));}
				if($claimdate==""){$claim_date=explode("-",date("Y-m-d"));}


				$this->smarty->assign('memberID',$memberID);	
				$this->smarty->assign('mode',$mode);	
				$this->smarty->assign('kind',$kind);	
				$this->smarty->assign('RG_Date',$RG_Date);	
				$this->smarty->assign('GroupName',$GroupName);	
				$this->smarty->assign('GroupCode',$GroupCode);	
				$this->smarty->assign('makerKor',$makerKor);
				$this->smarty->assign('billdate',$billdate);	
				$this->smarty->assign('expense_duedate',$expense_duedate);	
				$this->smarty->assign('claimdate',$claimdate);	
				$this->smarty->assign('claim_date',$claim_date);	
				$this->smarty->assign('doc_status',$doc_status);	
				$this->smarty->assign('PRINT',$PRINT);	

				$this->smarty->assign('id',$id);
				$this->smarty->assign('ProjectCode',$ProjectCode);
				$this->smarty->assign('ProjectName',$ProjectName);	
				$this->smarty->assign('AccSubstance',$AccSubstance);	
				$this->smarty->assign('DepartAccMenu',$DepartAccMenu);	
				$this->smarty->assign('DepartAccCode',$DepartAccCode);	
				$this->smarty->assign('DepartAccName',$DepartAccName);	
				$this->smarty->assign('VendorType',$VendorType);	
				$this->smarty->assign('VendorCode',$VendorCode);	
				$this->smarty->assign('VendorName',$VendorName);	
				$this->smarty->assign('Substance_detail',$Substance_detail);	
				$this->smarty->assign('RG_Date',$RG_Date);	
				$this->smarty->assign('etc1',$etc1);	
				$this->smarty->assign('etc2',$etc2);	
				$this->smarty->assign('etc3',$etc3);	
				$this->smarty->assign('etc4',$etc4);	
				$this->smarty->assign('maker',$maker);	
				$this->smarty->assign('UpdateDate',$UpdateDate);	
				$this->smarty->assign('UpdateUser',$UpdateUser);	
				$this->smarty->assign('buy_money',$buy_money);	
				$this->smarty->assign('buy_vat',$buy_vat);	
				$this->smarty->assign('buy_amount',$buy_amount);	
				$this->smarty->assign('buy_amountKor',$buy_amountKor);	
				$this->smarty->assign('proof_doc',$proof_doc);	
				$this->smarty->assign('account_no',$account_no);	
				$this->smarty->assign('BridgeNo',$BridgeNo);	
				$this->smarty->assign('proof_doc',$proof_doc);	

				$this->smarty->assign("page_action","payment_controller.php");
				$this->smarty->display("intranet/common_contents/work_documents/payment_input_mvc.tpl");
	
				if($PRINT=="YES")
				{	$this->smarty->assign('mode',"print");
					$this->smarty->display("intranet/js_page.tpl");
				}


		}

	
		//================================================================================
		// 출금전표 Update Logic
		//================================================================================	
		function UpdateAction()
		{

			global $db;
			global $auth,$memberID,$id,$account_no;
			global $claimdate,$billdate,$ProjectCode,$BridgeNo;
			global $ProjectNickName,$GroupCode,$GroupName,$Team;
			global $DepartAccCode,$DepartAccName,$VendorType,$VendorCode,$VendorName;
			global $AccSubstance,$buy_money,$buy_vat,$buy_amount,$today,$expense_duedate;

			global $kind,$DepartAccMenu,$Substance_detail;
			global $etc1,$etc2,$etc3,$etc4,$proof_doc;
			global $maker,$makerKor,$buy_amountKor;

			$today=date("Y-m-d H:i:s");
			$ProjectNickName=ProjectCode2Bridge($ProjectCode,$BridgeNo);
			if($maker==""){$maker=$memberID;}

			$buy_amount=str_replace(",","",$buy_amount);
			$buy_money=str_replace(",","",$buy_money);
			$buy_vat=str_replace(",","",$buy_vat);

			if($buy_money=="" && $buy_vat=="")
			{
				$buy_money=$buy_amount;
			}

				if($CompanyKind=="JANG")				
				{
					$sql="update project_account_tbl set claimdate='$claimdate',ProjectCode='$ProjectCode',BridgeNo='$BridgeNo',ProjectNickName='$ProjectNickName',GroupCode='$GroupCode',GroupName='$GroupName',Team='$Team',AccCode='$DepartAccCode',AccName='$DepartAccName',VendorType='$VendorType',VendorCode='$VendorCode',VendorName='$VendorName',AccSubstance='$AccSubstance',buy_money='$buy_money',buy_vat='$buy_vat',buy_amount='$buy_amount',UpdateDate='$today',UpdateUser='$memberID',expense_duedate='$expense_duedate'";
					$sql.=" where account_no='$account_no'";
				}
				else if($CompanyKind=="PILE")				
				{
					$sql="update project_account_tbl set claimdate='$claimdate',ProjectCode='$ProjectCode',ProjectNickName='$ProjectNickName',GroupCode='$GroupCode',GroupName='$GroupName',AccCode='$DepartAccCode',AccName='$DepartAccName',VendorType='$VendorType',VendorCode='$VendorCode',VendorName='$VendorName',AccSubstance='$AccSubstance',buy_money='$buy_money',buy_vat='$buy_vat',buy_amount='$buy_amount',UpdateDate='$today',UpdateUser='$memberID',expense_duedate='$expense_duedate'";
					$sql.=" where account_no='$account_no'";
				}
					mysql_query($sql,$db);

		
			for($i=0; $i<16; $i++) {
				$pdoc= $pdoc . $proof_doc[$i]."/n";
			}

				if($CompanyKind=="JANG")				
				{
					$sql2="update payment_voucher_tbl set ProjectCode='$ProjectCode',BridgeNo='$BridgeNo',ProjectName='$ProjectNickName',AccSubstance='$AccSubstance',DepartAccMenu='$DepartAccMenu',DepartAccCode='$DepartAccCode',DepartAccName='$DepartAccName',VendorType='$VendorType',VendorCode='$VendorCode',VendorName='$VendorName',Substance_detail='$Substance_detail',etc1='$etc1',etc2='$etc2',etc3='$etc3',claimdate='$claimdate',billdate='$billdate',GroupCode='$GroupCode',GroupName='$GroupName',Team='$Team',maker='$maker', makerKor='$makerKor',UpdateDate='$today',UpdateUser='$memberID',buy_money='$buy_money',buy_vat='$buy_vat',buy_amount='$buy_amount',buy_amountKor='$buy_amountKor',proof_doc='$pdoc',expense_duedate='$expense_duedate'";
					$sql2.=" where id='$id'";
				}
				else if($CompanyKind=="PILE")				
				{
					$sql2="update payment_voucher_tbl set ProjectCode='$ProjectCode',ProjectName='$ProjectNickName',AccSubstance='$AccSubstance',DepartAccMenu='$DepartAccMenu',DepartAccCode='$DepartAccCode',DepartAccName='$DepartAccName',VendorType='$VendorType',VendorCode='$VendorCode',VendorName='$VendorName',Substance_detail='$Substance_detail',etc1='$etc1',etc2='$etc2',etc3='$etc3',claimdate='$claimdate',billdate='$billdate',GroupCode='$GroupCode',GroupName='$GroupName',maker='$maker', makerKor='$makerKor',UpdateDate='$today',UpdateUser='$memberID',buy_money='$buy_money',buy_vat='$buy_vat',buy_amount='$buy_amount',buy_amountKor='$buy_amountKor',proof_doc='$pdoc',expense_duedate='$expense_duedate'";
					$sql2.=" where id='$id'";
				}
					mysql_query($sql2,$db);

			$maxid=$id;

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"payment_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");

		}

	
		//================================================================================
		// 출금전표 Delete Logic
		//================================================================================	
		function DeleteAction()
		{
			global $db;
			global $auth,$memberID,$id,$account_no,$mode2;

			if($mode2=="del1" or $mode2=="del2") { //삭제
				$sql="delete from project_account_tbl where account_no='$account_no'";	
				mysql_query($sql,$db);

				$sql2="delete from payment_voucher_tbl where id='$id'";
				mysql_query($sql2,$db);
			}

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"payment_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");

		}

		
		//================================================================================
		// 출금전표 List Logic
		//================================================================================	
		function View()
		{
			global $db;
			global $auth,$tab_index,$memberID,$sub_index;
			global $Start,$page,$currentPage,$last_page,$tab_index;

			$page=15;
	
			if($currentPage=="")
				$Start = 0;
			else
				$Start=$page*($currentPage-1);

			if($tab_index == "") $tab_index = 0;

				$PaymentList = array(); 
		
			if($tab_index==0)
			{ //지출일없음
				$sql_num = "select * from payment_voucher_tbl where UpdateUser='$memberID' and (input_id='' or IsNull(input_id)) order by account_no desc";
				$sql = $sql_num." limit $Start, $page";
			}
			else
			{
				$sql_num = "select a11.*,a12.AmountDate  from ((select * from payment_voucher_tbl where UpdateUser='$memberID' and input_id !='')a11 left join (select AmountDate,id as input_id from accounting_input_tbl)a12 on a11.input_id=a12.input_id)";
				$sql = $sql_num." limit $Start, $page";
			}
		

			$sql_num = mysql_query($sql_num,$db);
			$TotalRow = mysql_num_rows($sql_num);//총 개수 저장
			$last_start = ceil($TotalRow/10)*10+1;
			$last_page=ceil($TotalRow/$page);

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				array_push($PaymentList,$re_row);
			}


			if($currentPage == "") $currentPage = 1; 
		

			$PageHandler =new PageControl($this->smarty);
			$PageHandler->SetMaxRow($TotalRow);

			$PageHandler->SetCurrentPage($currentPage);
			$PageHandler->PutTamplate();


			$this->smarty->assign('memberID',$memberID);	
			$this->smarty->assign('tab_index',$tab_index);				
			$this->smarty->assign('PaymentList',$PaymentList);
			$this->smarty->assign("page_action","payment_controller.php");

			$this->smarty->display("intranet/common_contents/work_documents/paymentlist_contents_mvc.tpl");
		}


		//================================================================================
		// ProjectCode 검색 Logic
		//================================================================================	
		function ProjectCodeSearch()
		{
			global $db;
			global $auth,$SelectedMenu,$no,$kind,$PName,$FindKey,$CK_CompanyKind;

			if($PName=="") $PName="시공";

			$ProjectTitleList = array(); 

			$sql="select * from systemconfig_tbl where SysKey = 'ProjectCode' and Code in('11','12','13','14','15','16','17','20') order by Code";

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{ 
				$Note=$re_row[Note];
				$Name=$re_row[Name];
				$Code = $re_row[Code];
				$Code = sprintf('%02d',$Code);
				
				array_push($ProjectTitleList,$re_row);
			}

			$ProjectCodeList = array(); 

			if($FindKey <> "") 
			{
				if($CompanyKind=="JANG")				
				{
					$azSQL = "select * from project_tbl A, project_cs_summary_tbl B where (A.ProjectNickname like '%$FindKey%' or A.OrgProjectNickname like '%$FindKey%' or B.Item like '%$FindKey%') and (A.ProjectCode like '%-시공-%') and A.ProjectCode=B.ProjectCode";
				}
				else if($CompanyKind=="PILE")
				{
					$azSQL = "select * from project_tbl where (ProjectNickname like '%$FindKey%' or OrgProjectNickname like '%$FindKey%') and ProjectCode like '%-시공-%'";
				}
			}
			else 
			{
				$azSQL = "select * from project_tbl where ProjectCode like '%-$PName-%' and (ProjectCode like '0%' or ProjectCode like 'X%' or ProjectCode like '1%' or ProjectCode like '2%') order by ProjectCode desc";
			}

			$re = mysql_query($azSQL,$db);   
			$i="0";
			while($re_row = mysql_fetch_array($re)) 
			{ 

				$ProjectCode = $re_row[ProjectCode];
				$ProjectCode = change_code($ProjectCode);
				$re_row[ProjectCode]=$ProjectCode;
				$OrgProjectNickname=$re_row[OrgProjectNickname];
				$ProjectNickname = $re_row[ProjectNickname];					

				array_push($ProjectCodeList,$re_row);

				if($CompanyKind=="JANG")				
				{
					if($PName=="시공" || $PName=="설계" )
					{	
						if($PName=="시공")
						{
							$tmpProjectNickname[$i]=$OrgProjectNickname;
						}else
						{
							$tmpProjectNickname[$i]=$ProjectNickname;
						}

							if($old_ProjectCode <> $ProjectCode)
							{
								$old_ProjectCode=$ProjectCode;

								$j="0";

								$sql2 = "select * from project_cs_summary_tbl where ProjectCode='$ProjectCode' order by no asc";	
								
								$re2 = mysql_query($sql2,$db);
								$rowspan[$i] = mysql_num_rows($re2);
								while($re_row2 = mysql_fetch_array($re2)) 
								{
									$Item[$i][$j]=$re_row2[Item];
									$id[$i][$j]=$re_row2[no];

									if($j=="0"){$first[$i][$j]=true;}
									else{$first[$i][$j]=false;}
									
									$j++;
								}							
							}
						}
					}
				$i++;
			}

			$this->smarty->assign('no',$no);
			$this->smarty->assign('id',$id);
			$this->smarty->assign('kind',$kind);	
			$this->smarty->assign('PName',$PName);	
			$this->smarty->assign('CoName',$CoName);	
			$this->smarty->assign('rowspan',$rowspan);
			$this->smarty->assign('first',$first);
			$this->smarty->assign('Item',$Item);
			$this->smarty->assign('tmpProjectNickname',$tmpProjectNickname);
			$this->smarty->assign('ProjectTitleList',$ProjectTitleList);
			$this->smarty->assign('ProjectCodeList',$ProjectCodeList);

			$this->smarty->display("intranet/common_contents/work_documents/payment_projectcode_mvc.tpl");

		}


		//================================================================================
		// 계정과목 검색 Logic/
		//================================================================================	
		function AccountTitleSearch()
		{
			global $db;
			global $auth,$SelectedMenu,$no,$tab_index,$FindKey;

			$AccountList = array(); 

			$sql="select DISTINCT Fmenu from accounting_code_tbl order by Fcode asc";
			$re = mysql_query($sql);
			while($re_row = mysql_fetch_array($re))
			{ 
				$Fmenu = $re_row[Fmenu];
				$CoName=$re_row[Name];

				array_push($AccountList,$re_row);

					if($SelectedMenu == "") $SelectedMenu = $Fmenu;
			}

			$TitleList = array(); 			

			if($SelectedMenu == "FIND") {
				$azSQL = "select * from accounting_code_tbl where Fcode like '$FindKey%' or Fcategory like '%$FindKey%' order by Fcode asc";
			} else {
				$azSQL = "select * from accounting_code_tbl where Fmenu='$SelectedMenu' order by Fcode asc";
			}

			$res_fcode = mysql_query($azSQL);   
			$fcode_no = mysql_num_rows($res_fcode);
			while($rec_fcode = mysql_fetch_array($res_fcode)) 
			{ 
				array_push($TitleList,$rec_fcode);
			}

			$this->smarty->assign('no',$no);	
			$this->smarty->assign('SelectedMenu',$SelectedMenu);	
			$this->smarty->assign('CoName',$CoName);				
			$this->smarty->assign('AccountList',$AccountList);
			$this->smarty->assign('TitleList',$TitleList);

			$this->smarty->display("intranet/common_contents/work_documents/payment_accountcode_mvc.tpl");
		}


		//================================================================================
		// 거래처 검색 Logic
		//================================================================================	
		function VenderCodeSearch()
		{
			global $db;
			global $auth,$SelectedMenu,$no;
			global $tab_index,$tab_index,$Part;
			global $Company, $Member,$GroupCode;

			if($tab_index == "") $tab_index="1";
			if($tab_index=="1")
			{
				if ($Part=="") $Part="건설하도급";
			}
			else
			{
				if ($GroupCode=="") $GroupCode="01";
			}
			if($SelectedMenu == "") $SelectedMenu = $Fmenu;
			$tab_Titel = array('거래처','사원명');
			$tab_value = array('1','2');
			

			if($tab_index == "1")  //거래처 
			{
				$VenderKindList = array();
				
				$sql="select * from systemconfig_tbl where SysKey = 'OutsidePartCode' order by binary Name asc";
				$re = mysql_query($sql);
				while($re_row = mysql_fetch_array($re))
				{ 
					$CoCode=$re_row[Code];
					$CoCode = sprintf('%02d',$CoCode);
					$CoName=$re_row[Name];;

					array_push($VenderKindList,$re_row);
				}
				

				$VenderList = array();

				if($Company != "") {//거래처
					 $sql2 = "select * from outside_cooperation_tbl where Company like '%$Company%' or P_Name like '%$Company%' order by binary(CompanyNicName)";	     
				} elseif ($Part == "all") {
					 $sql2 = "select * from outside_cooperation_tbl order by binary(CompanyNicName)";
				} else {
					 $sql2 = "select * from outside_cooperation_tbl where Part = '$Part' order by binary(CompanyNicName)";	     
				}

				$re2 = mysql_query($sql2,$db);
				$re_count=mysql_num_rows($re2);

				while($re_row2 = mysql_fetch_array($re2)) 
				{ 
					array_push($VenderList,$re_row2);
				}

			}
			else  //내부직원
			{
				$MemberKindList = array();

				$sql="select * from systemconfig_tbl where SysKey='GroupCode' and Code<>'99' order by Code";
				$re = mysql_query($sql,$db);
				while($re_row = mysql_fetch_array($re)) 
				{ 
					$Code=$re_row[Code];
					$Code = sprintf('%02d',$Code);
					$GroupName=$re_row[Name];

					array_push($MemberKindList,$re_row);
				}


				$MemberList = array();

				if($Member <> "") {
					$sql2="select * from member_tbl where (Korname like '%$Member%' or MemberNo like '%$Member%') and WorkPosition<'8' and WorkPosition<>'2'  order by RankCode";
				} else {
					$sql2="select * from member_tbl where GroupCode = '$GroupCode' and WorkPosition<'8' and WorkPosition<>'2'  order by RankCode";
				}

				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2))
				{ 
					array_push($MemberList,$re_row2);
				}

			}


			$this->smarty->assign('no',$no);
			$this->smarty->assign('Part',$Part);		
			$this->smarty->assign('GroupCode',$GroupCode);		
			
			$this->smarty->assign('tab_Titel',$tab_Titel);			
			$this->smarty->assign('tab_value',$tab_value);
			$this->smarty->assign('tab_index',$tab_index);			
			$this->smarty->assign('CoCode',$CoCode);

			$this->smarty->assign('VenderKindList',$VenderKindList);
			$this->smarty->assign('VenderList',$VenderList);	
			$this->smarty->assign('MemberKindList',$MemberKindList);
			$this->smarty->assign('MemberList',$MemberList);

			$this->smarty->display("intranet/common_contents/work_documents/payment_vendercode_mvc.tpl");
		}


		//================================================================================
		// 청구자 검색 Logic
		//================================================================================	
		function MemberCodeSearch()
		{
			global $db;
			global $auth,$SelectedMenu,$no;
			global $tab_index,$tab_index,$Part;
			global $Company, $Member,$GroupCode,$Gcode,$Description,$Team;

			if($Gcode==""){$Gcode="01";}
			if($Description==""){$Description="임원";}
			if($SelectedMenu == "") $SelectedMenu = $Fmenu;

			$MemberKindList = array();
			
			$sql="select * from systemconfig_tbl where SysKey='GroupCode' and Code <> '06'  and Code <> '99'  order by orderno";

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{ 
				$GroupCode=$re_row[Code];
				$GroupCode = sprintf('%02d',$GroupCode);
				$GroupName=$re_row[Name];
					
				array_push($MemberKindList,$re_row);
					
			}
		

			$MemberList = array();
			$i="0";
			if($Gcode=="07"){
				$sql3="select * from systemconfig_tbl where SysKey='TeamLeader' order by Code";
			}else{
				$sql3="select * from member_tbl where WorkPosition<'8' and WorkPosition<>'2' and GroupCode='$Gcode' order by RankCode";
			}
			
			$res_fcode = mysql_query($sql3,$db);   
			while($rec_fcode = mysql_fetch_array($res_fcode)) 
			{ 
				if($Gcode=="07"){
					$SCode=$rec_fcode[Code];
					$SName=$rec_fcode[Name];
					$Team=$SCode;
				}else
				{
					$SCode = $rec_fcode[MemberNo];
					$SName = $rec_fcode[korName];
					$approval_rank[$i]=MemberNo2Rank($SCode);
				}
				array_push($MemberList,$rec_fcode);								
				$i++;
			}


			$this->smarty->assign('no',$no);

			$this->smarty->assign('CoCode',$CoCode);
			$this->smarty->assign('Gcode',$Gcode);
			$this->smarty->assign('approval_rank',$approval_rank);
			$this->smarty->assign('Description',$Description);

			$this->smarty->assign('MemberKindList',$MemberKindList);
			$this->smarty->assign('MemberList',$MemberList);

			$this->smarty->display("intranet/common_contents/work_documents/payment_membercode_mvc.tpl");
		}


		//================================================================================
		// 거래처 추가 Logic
		//================================================================================		
		function EditVenderLogic()
		{
			global $db;
			global $auth,$kind,$no, $mode;

				if($mode=="mod")
				{

					$sql="select * from outside_cooperation_tbl where no='$no'";
					$re = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($re)) 
					{
						$no=$re_row[no];
						$Company=$re_row[Company];
						$CompanyNicName=$re_row[CompanyNicName];
						$Part=$re_row[Part];
						$DetailPart=$re_row[DetailPart];
						$Phone=$re_row[Phone];
						$Fax=$re_row[Fax];
						$Address=$re_row[Address];
						$P_Name=$re_row[P_Name];
						$ProviderNo=$re_row[ProviderNo];
						$RegisterDate=$re_row[RegisterDate];
						$ListDisplay=$re_row[ListDisplay];
						$Bank_name=$re_row[Bank_name];
						$Account_number=$re_row[Account_number];
						$Account_holder=$re_row[Account_holder];
					}
				}
				else
				{	
					$RegisterDate=date("Y-m-d");
				}
					
				$this->smarty->assign('kind',$kind);
				$this->smarty->assign('mode',$mode);
				$this->smarty->assign('no',$no);
				$this->smarty->assign('Company',$Company);
				$this->smarty->assign('CompanyNicName',$CompanyNicName);
				$this->smarty->assign('Part',$Part);
				$this->smarty->assign('DetailPart',$DetailPart);
				$this->smarty->assign('Phone',$Phone);
				$this->smarty->assign('Fax',$Fax);
				$this->smarty->assign('Address',$Address);
				$this->smarty->assign('P_Name',$P_Name);
				$this->smarty->assign('ProviderNo',$ProviderNo);
				$this->smarty->assign('RegisterDate',$RegisterDate);
				$this->smarty->assign('ListDisplay',$ListDisplay);
				$this->smarty->assign('Bank_name',$Bank_name);
				$this->smarty->assign('Account_number',$Account_number);
				$this->smarty->assign('Account_holder',$Account_holder);

				$this->smarty->display("intranet/common_contents/work_documents/payment_venderedit_mvc.tpl");
		}


		//================================================================================
		// 거래처 저장/수정 Logic
		//================================================================================	
		function SaveVenderLogic()
		{
			global $db;
			global $auth,$kind,$no, $mode;
			global $ListDisplay,$Company,$CompanyNicName, $Part;
			global $DetailPart,$Phone,$Fax, $Address, $P_Name, $ProviderNo;
			global $RegisterDate,$Bank_name,$Account_number, $Account_holder;


			if($ListDisplay=="0"){ //안보이게 
				$ListDisplay2=1;
			}else
			{	
				$ListDisplay2=0;
			}

			if($mode=="mod")
			{  
				$sql="update outside_cooperation_tbl set Company='$Company', CompanyNicName='$CompanyNicName', Part='$Part', DetailPart='$DetailPart',Phone='$Phone',Fax='$Fax',Address='$Address', P_Name='$P_Name', ProviderNo='$ProviderNo', RegisterDate='$RegisterDate', ListDisplay='$ListDisplay2', Bank_name='$Bank_name', Account_number='$Account_number', Account_holder='$Account_holder' where no='$no'";
				
		   
			 }else
			 {

				$azSQL = "select * from outside_cooperation_tbl where Company='$Company' and Part='$Part'";
				$result00 = mysql_query($azSQL,$db);
				if(mysql_num_rows($result00) <= 0) 
				{ //추가
	
					$sql="insert into outside_cooperation_tbl (Company, CompanyNicName, Part, DetailPart, Phone, Fax, Address, P_Name, ProviderNo, RegisterDate, ListDisplay, Bank_name, Account_number, Account_holder) values ('$Company', '$CompanyNicName', '$Part','$DetailPart', '$Phone', '$Fax', '$Address', '$P_Name', '$ProviderNo', '$RegisterDate', '$ListDisplay2','$Bank_name', '$Account_number', '$Account_holder')";	
				}else
				{
					
					$this->smarty->assign('mode',"alert");
					$this->smarty->assign('msg',"중복된 업체명이 있습니다.확인후 다시 등록해 주십시요");
					$this->smarty->display("intranet/js_page.tpl");
				}
			 }

			mysql_query($sql,$db);

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"payment_controller.php?ActionMode=vender_search&tab_index=$kind&Part=$Part");
			$this->smarty->display("intranet/move_page.tpl");
		}
}
?>