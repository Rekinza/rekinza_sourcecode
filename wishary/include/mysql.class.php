<?php
class DB {
		/**
		 * Sington 방식을 사용하기 위해 static 으로 선언한다
		 * @var DB Connect Link
		 */
		var $conn = FALSE;
		
		var $stmt = 0;
		var $abstractData = FALSE;
		var $error = FALSE;
		var $errorqry = FALSE;
		/**
		 * 생성자 : 인스턴스 생성시 자동으로 DB연결을 수행한다.
		 * @return unknown_type
		 */
		function __construct() {
			$this->connect();
		}
		
		/* --------------------------------------------------------------------- */

		function find($tableAssoc, $where=null, $select=null, $order=null) {
			if(!$select) $select = '*';

			if(is_array($where)) {
				$tmpWhere = '';
				foreach($where as $key=>$val) {
					if($tmpWhere) $tmpWhere .= " AND ";
					if(is_numeric($key)) {
						$tmpWhere .= $val;
					}
					else {
						if(substr(strtolower($val),0,8)=='to_date(') {
							$tmpWhere .= $key."=".$val;
						}
						else {
							$tmpWhere .= $key."='".$val."'";
						}
					}
				}
				$where = $tmpWhere;
			}

			$strQuery = "SELECT ".$select." FROM ".$tableAssoc.($where ? " WHERE ".$where : null).($order ? " ORDER BY ".$order : null);
			if(DEBUG!='0' && $this->ChkDebug!='0') $this->DebugQry[$this->queryDebugSID][] = $strQuery;
			$DBresult = $this->parseExec($strQuery);
			$ret = $this->fetchArray($DBresult);
			
			//$this->disconnect();
			return $ret;
		}


		function findCount($tableAssoc, $where=null) {
			if(is_array($where)) {
				$tmpWhere = '';
				foreach($where as $key=>$val) {
					if($tmpWhere) $tmpWhere .= " AND ";
					if(is_numeric($key)) {
						$tmpWhere .= $val;
					}
					else {
						if(substr(strtolower($val),0,8)=='to_date(') {
							$tmpWhere .= $key."=".$val;
						}
						else {
							$tmpWhere .= $key."='".$val."'";
						}
					}
				}
				$where = $tmpWhere;
			}

			$strQuery = "SELECT COUNT(*) AS cnt FROM ".$tableAssoc.($where ? " WHERE ".$where : null);
			if(DEBUG!='0' && $this->ChkDebug!='0') $this->DebugQry[$this->queryDebugSID][] = $strQuery;
			$DBresult = $this->parseExec($strQuery);
			$ret = $this->fetchArray($DBresult);

			//$this->disconnect();
			return $ret['cnt'];
		}



		function findInsertID() {
			$ret['INSERT_ID'] = mysql_insert_id();
			return $ret['INSERT_ID'];
		}
		
		function findAffectedRows() {			
			$ret['AFFECTED_ROWS'] = mysql_affected_rows();
			return $ret['AFFECTED_ROWS'];
		}


		function findAll($tableAssoc, $where=null, $select=null, $order=null, $limit=null, $page=null) {
			if($limit && !$page) $page = 1;
			if(is_array($where)) {
				$tmpWhere = '';
				foreach($where as $key=>$val) {
					if($tmpWhere) $tmpWhere .= " AND ";
					if(is_numeric($key)) {
						$tmpWhere .= $val;
					}
					else {
						if(substr(strtolower($val),0,8)=='to_date(') {
							$tmpWhere .= $key."=".$val;
						}
						else {
							$tmpWhere .= $key."='".$val."'";
						}
					}
				}
				$where = $tmpWhere;
			}

			if(is_array($select)) {
				$select = implode(", ",$select);
			}


			$tmpTable = explode(" JOIN ",str_ireplace(array(' left', ' right', ' inner',' outer', ' cross'),array('',''),$tableAssoc));
			$arrTable = array();
			foreach($tmpTable as $t) {
				$arr_t = explode(" ON",str_replace(' on',' ON',$t));
				$tmp_t = trim($arr_t[0]);
				$alias = trim(strrchr( $tmp_t, ' '));
				if($alias) {
					$arrTable[] = $alias;
				}
				else {
					$arrTable[] = $tmp_t;
				}
			}
			$arrTable = array_unique($arrTable);


			$currentQuery = "SELECT ".($select ? $select : '*')." FROM ".$tableAssoc.($where ? " WHERE ".$where : '').($order ? " ORDER BY ".$order : '').($limit ? " LIMIT ".( ($page-1) * $limit ).",".$limit : '');

			$strQuery = $currentQuery;

			if(DEBUG!='0' && $this->ChkDebug!='0' && $tableAssoc!='CONFIGS') $this->DebugQry[$this->queryDebugSID][] = $strQuery;

			$DBresult = $this->parseExec($strQuery);
			//$ret = $this->fetchArray(&$DBresult);
			$count=0;
			while ($ret = $this->fetchAssoc($DBresult)) {
				$tmp = $ret;
				$result[] = $tmp;
				$count++;
			}
			return $result;
		}


		function query($strQuery) {
			$value = null;
			$DBresult = $this->parseExec($strQuery);
			if(DEBUG!='0' && $this->ChkDebug!='0') $this->DebugQry[$this->queryDebugSID][] = $strQuery;

			if(strpos($strQuery,"INSERT ")!==false || strpos($strQuery,"UPDATE ")!==false || strpos($strQuery,"DELETE ")!==false || strpos($strQuery,"CREATE ")!==false) {
				//OCICommit($this->conn);
				$value = $DBresult;
			}
			
			else {
				if($DBresult) {
					while ($ret = $this->fetchArray($DBresult)) {
							$value[]=$ret;
					}
				}else {
					$value = $DBresult;
				}
			}
			
			//$this->disconnect();
			return $value;
		}

		function queryDebug($errorQry=null) {
			if($errorQry || (isset($this->DebugQry[$this->queryDebugSID]) && !empty($this->DebugQry[$this->queryDebugSID]))) {


				$arrWord1 = array('insert ', 'update ', 'delete ', 'select ','where ', 'order by ', 'group by ', 'join ', 'left ', 'right ', 'outer ', 'cross ', 'as ', 'from ', 'values ', 'into ', 'set ', 'and ', 'or ');
				$arrWord2 = array(' count', ' max');
				$repWord = array();
				foreach($arrWord1 as $w) {
					$repWord[] = "<font color='#666666'>".strtoupper(trim($w))."</font> ";
				}
				foreach($arrWord2 as $w) {
					$repWord[] = " <font color='blue'>".strtoupper(trim($w))."</font>";
				}
				$this->DebugQry[$this->queryDebugSID] = str_ireplace(array_merge($arrWord1,$arrWord2), $repWord, $this->DebugQry[$this->queryDebugSID]);

				echo '<strong>Quries : </strong>';
				echo ' (UID/SID : <strong>' . $this->queryDebugSID . '</strong>)';
				echo "\n<pre style=\"font-family:tahoma;font-size:13px;\">\n";

				if($errorQry) $this->DebugQry[$this->queryDebugSID][] = "<font color='red'>".$errorQry."</font>";

				$var = print_r($this->DebugQry[$this->queryDebugSID], true);
				echo $var . "\n</pre>\n";
				unset($this->DebugQry[$this->queryDebugSID]);
			}
		}

		function debug($type='0') {
			$this->ChkDebug = $type;
		}




		function connect($HOST=DB_HOST,$USERID=DB_USERID,$PASSWD=DB_PASSWD, $DBNAME=DB_NAME){
			if($this->conn) return $this->conn;

			$this->conn = mysql_connect($HOST, $USERID, $PASSWD, true, 131074);
			mysql_select_db($DBNAME, $this->conn);
			mysql_query('set names utf8');

			return $this->conn;
		}

		// OCIParse() 함수는 conn인수를 사용하는 query를 해석한다. 질의(query)가 유효하면 구문(statement)를 리턴한다. 그렇지 않으면 FALSE를 리턴한다. 
		// query인수는 유효한 SQL 구문(statement)이나 PL/SQL블록이 될 수 있다.
		function parseExec ($qry) {
			//$this->stmt = OCIParse($this->conn,$qry);
			//$this->exec(null,$qry);
			$result = mysql_query($qry);
			if ($result){//결과 리턴값이 있을경우
				return $result;
			} else {
				//echo (mysql_error());
			//  DB insert 오류가 났을때 파일로그 남김   
				$file_name = "/home/mnsms/log/DB_ERRLOG_".date("Ymd").".log";
				$handle = @fopen($file_name,'a');

				@chmod($file_name, 0777);
				$str = "|".date("H:i:s")."|".@mysql_errno()."|".@mysql_error()."|".$qry."|\n";

				@fwrite($handle,$str);
				@fclose($handle);
				return $result;
			}
		}


		function fetchArray($result)
		{
			$aResult = @mysql_fetch_array($result);
			return $aResult;
		}
		function fetchAssoc($result)
		{
			$aResult = @mysql_fetch_assoc($result);
			return $aResult;
		}


		//레코드수 알아내기
		function numRows($result) {
			$rownum = @mysql_num_rows($result);
			return $rownum;
		}


		function __destruct()
		{
			$this->disconnect();
		}
		function disconnect()
		{
/* 			$this->queryDebug($errorqry); */
			@mysql_close(DB_SERVERNAME);
		}
}
if(isset($_GET['debug']) && $_GET['debug'] == 'on') {
	define( 'DEBUG', '1' ); //디버그on
}
else {
	define( 'DEBUG', '0' ); //디버그off
}
$DB	= new DB;



?>