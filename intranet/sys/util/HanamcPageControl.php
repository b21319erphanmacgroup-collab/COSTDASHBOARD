<?php
	class PageControl  
	{
		var $smarty;
		var $mTotalRowCount;
		var $mCurrentPage;
		var $mViewCount=15;

		function PageControl($smarty)
		{
			$this->smarty=$smarty;
		}
		function SetMaxRow($TotalRow)
		{
			$this->mTotalRowCount=$TotalRow;
		}
		function SetCurrentPage($CurrentPage)
		{
			if($CurrentPage*$this->mViewCount <= $this->mTotalRowCount)
				$this->mCurrentPage=$CurrentPage;
			else if(($CurrentPage-1)*$this->mViewCount <= $this->mTotalRowCount)
				$this->mCurrentPage=$CurrentPage;
			else
				$this->mCurrentPage=1;
		}
		function SetPageViewCount($ViewCount)
		{
			$this->mViewCount=$ViewCount;
		}

		function GetFirstPage()
		{
			if( $this->mTotalRowCount/$this->mViewCount < 10)
				return false;
			else
				return true;			
		}

		function GetLastPage()
		{
			if( $this->mTotalRowCount/$this->mViewCount < 10) {
				return false;
			}
			else {
				return true;
			}
		}
		function GetCurrentPage()
		{
			return $this->mCurrentPage;
		}

		function GetPreviousPage()
		{
			if($this->mCurrentPage <= 10)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		function GetNextPage()
		{
			$pageno=ceil($this->mTotalRowCount / $this->mViewCount);
			if( $this->mTotalRowCount/$this->mViewCount > 10)
			{
				// if($this->mCurrentPage <= ($pageno -$this->mViewCount))
				if($this->mCurrentPage > ( floor($pageno /10) * 10 ))
					return false;
				else
					return true;
			}
			else
				return false;
		}

		function GetPageList()
		{
			$List="";

			//$StartPage= floor(($this->mCurrentPage-1)/$this->mViewCount)*$this->mViewCount;

			$StartPage=intval(($this->mCurrentPage-1)/10)*10;
			$EndPage=$StartPage+10;			
			if($EndPage > ceil($this->mTotalRowCount/$this->mViewCount))
				$EndPage=ceil($this->mTotalRowCount/$this->mViewCount);
			for($Count=$StartPage+1;$Count<=$EndPage;$Count++)
			{
				if($List =="")
					$List=$Count;
				else
					$List.=",".$Count;
			}
			$query_data = array(); 
			$ListArray = split(",",$List);
			for($i=0;$i< sizeof($ListArray);$i++)
			{
				$re_row[page]=$ListArray[$i];
				array_push($query_data,$re_row);
			}
			return $query_data;

		}

		function PutTamplate()
		{
			$this->smarty->assign("page_first",$this->GetFirstPage());
			$this->smarty->assign("page_previous",$this->GetPreviousPage());
			$this->smarty->assign("page_next",$this->GetNextPage());
			$this->smarty->assign("page_last",$this->GetLastPage());
			$this->smarty->assign("page_list",$this->GetPageList());
			$this->smarty->assign("page_current",$this->GetCurrentPage());
			$this->smarty->assign("last_page", ceil($this->mTotalRowCount / $this->mViewCount));
			$this->smarty->assign("mTotalRowCount", $this->mTotalRowCount );
		}

	}
	
?>
