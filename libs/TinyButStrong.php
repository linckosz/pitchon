<?php

/**
 *
 * TinyButStrong - Template Engine for Pro and Beginners
 *
 * @version 3.10.1 for PHP 5
 * @date    2015-12-03
 * @link    http://www.tinybutstrong.com Web site
 * @author  http://www.tinybutstrong.com/onlyyou.html
 * @license http://opensource.org/licenses/LGPL-3.0 LGPL-3.0
 *
 * This library is free software.
 * You can redistribute and modify it even for commercial usage,
 * but you must accept and respect the LPGL License version 3.
 */

// Check PHP version
if (version_compare(PHP_VERSION,'5.0')<0) echo '<br><b>TinyButStrong Error</b> (PHP Version Check) : Your PHP version is '.PHP_VERSION.' while TinyButStrong needs PHP version 5.0 or higher. You should try with TinyButStrong Edition for PHP 4.';
/* COMPAT#1 */

// Render flags
define('TBS_NOTHING', 0);
define('TBS_OUTPUT', 1);
define('TBS_EXIT', 2);

// Plug-ins actions
define('TBS_INSTALL', -1);
define('TBS_ISINSTALLED', -3);

// *********************************************

class clsTbsLocator {
	public $PosBeg = false;
	public $PosEnd = false;
	public $Enlarged = false;
	public $FullName = false;
	public $SubName = '';
	public $SubOk = false;
	public $SubLst = array();
	public $SubNbr = 0;
	public $PrmLst = array();
	public $PrmIfNbr = false;
	public $MagnetId = false;
	public $BlockFound = false;
	public $FirstMerge = true;
	public $ConvProtect = true;
	public $ConvStr = true;
	public $ConvMode = 1; // Normal
	public $ConvBr = true;
}

// *********************************************

class clsTbsDataSource {

public $Type = false;
public $SubType = 0;
public $SrcId = false;
public $Query = '';
public $RecSet = false;
public $RecKey = '';
public $RecNum = 0;
public $RecNumInit = 0;
public $RecSaving = false;
public $RecSaved = false;
public $RecBuffer = false;
public $CurrRec = false;
public $TBS = false;
public $OnDataOk = false;
public $OnDataPrm = false;
public $OnDataPrmDone = array();
public $OnDataPi = false;

public function DataAlert($Msg) {
	if (is_array($this->TBS->_CurrBlock)) {
		return $this->TBS->meth_Misc_Alert('when merging block "'.implode(',',$this->TBS->_CurrBlock).'"',$Msg);
	} else {
		return $this->TBS->meth_Misc_Alert('when merging block '.$this->TBS->_ChrOpen.$this->TBS->_CurrBlock.$this->TBS->_ChrClose,$Msg);
	}
}

public function DataPrepare(&$SrcId,&$TBS) {

	$this->SrcId = &$SrcId;
	$this->TBS = &$TBS;
	$FctInfo = false;
	$FctObj = false;

	if (is_array($SrcId)) {
		$this->Type = 0;
	} elseif (is_resource($SrcId)) {

		$Key = get_resource_type($SrcId);
		switch ($Key) {
		case 'mysql link'            : $this->Type = 6; break;
		case 'mysql link persistent' : $this->Type = 6; break;
		case 'mysql result'          : $this->Type = 6; $this->SubType = 1; break;
		case 'pgsql link'            : $this->Type = 7; break;
		case 'pgsql link persistent' : $this->Type = 7; break;
		case 'pgsql result'          : $this->Type = 7; $this->SubType = 1; break;
		case 'sqlite database'       : $this->Type = 8; break;
		case 'sqlite database (persistent)'	: $this->Type = 8; break;
		case 'sqlite result'         : $this->Type = 8; $this->SubType = 1; break;
		default :
			$FctInfo = $Key;
			$FctCat = 'r';
		}

	} elseif (is_string($SrcId)) {

		switch (strtolower($SrcId)) {
		case 'array' : $this->Type = 0; $this->SubType = 1; break;
		case 'clear' : $this->Type = 0; $this->SubType = 3; break;
		case 'mysql' : $this->Type = 6; $this->SubType = 2; break;
		case 'text'  : $this->Type = 2; break;
		case 'num'   : $this->Type = 1; break;
		default :
			$FctInfo = $SrcId;
			$FctCat = 'k';
		}

	} elseif ($SrcId instanceof Iterator) {
		$this->Type = 9; $this->SubType = 1;
	} elseif ($SrcId instanceof ArrayObject) {
		$this->Type = 9; $this->SubType = 2;
	} elseif ($SrcId instanceof IteratorAggregate) {
		$this->Type = 9; $this->SubType = 3;
	} elseif ($SrcId instanceof MySQLi) {
		$this->Type = 10;
	} elseif ($SrcId instanceof PDO) {
		$this->Type = 11;
	} elseif ($SrcId instanceof Zend_Db_Adapter_Abstract) {
		$this->Type = 12;
	} elseif ($SrcId instanceof SQLite3) {
		$this->Type = 13; $this->SubType = 1;
	} elseif ($SrcId instanceof SQLite3Stmt) {
		$this->Type = 13; $this->SubType = 2;
	} elseif ($SrcId instanceof SQLite3Result) {
		$this->Type = 13; $this->SubType = 3;
	} elseif (is_object($SrcId)) {
		$FctInfo = get_class($SrcId);
		$FctCat = 'o';
		$FctObj = &$SrcId;
		$this->SrcId = &$SrcId;
	} elseif ($SrcId===false) {
		$this->DataAlert('the specified source is set to FALSE. Maybe your connection has failed.');
	} else {
		$this->DataAlert('unsupported variable type : \''.gettype($SrcId).'\'.');
	}

	if ($FctInfo!==false) {
		$ErrMsg = false;
		if ($TBS->meth_Misc_UserFctCheck($FctInfo,$FctCat,$FctObj,$ErrMsg,false)) {
			$this->Type = $FctInfo['type'];
			if ($this->Type!==5) {
				if ($this->Type===4) {
					$this->FctPrm = array(false,0);
					$this->SrcId = &$FctInfo['open'][0];
				}
				$this->FctOpen  = &$FctInfo['open'];
				$this->FctFetch = &$FctInfo['fetch'];
				$this->FctClose = &$FctInfo['close'];
			}
		} else {
			$this->Type = $this->DataAlert($ErrMsg);
		}
	}

	return ($this->Type!==false);

}

public function DataOpen(&$Query,$QryPrms=false) {

	// Init values
	unset($this->CurrRec); $this->CurrRec = true;
	if ($this->RecSaved) {
		$this->FirstRec = true;
		unset($this->RecKey); $this->RecKey = '';
		$this->RecNum = $this->RecNumInit;
		if ($this->OnDataOk) $this->OnDataArgs[1] = &$this->CurrRec;
		return true;
	}
	unset($this->RecSet); $this->RecSet = false;
	$this->RecNumInit = 0;
	$this->RecNum = 0;

	if (isset($this->TBS->_piOnData)) {
		$this->OnDataPi = true;
		$this->OnDataPiRef = &$this->TBS->_piOnData;
		$this->OnDataOk = true;
	}
	if ($this->OnDataOk) {
		$this->OnDataArgs = array();
		$this->OnDataArgs[0] = &$this->TBS->_CurrBlock;
		$this->OnDataArgs[1] = &$this->CurrRec;
		$this->OnDataArgs[2] = &$this->RecNum;
		$this->OnDataArgs[3] = &$this->TBS;
	}

	switch ($this->Type) {
	case 0: // Array
		if (($this->SubType===1) && (is_string($Query))) $this->SubType = 2;
		if ($this->SubType===0) {
			$this->RecSet = &$this->SrcId; /* COMPAT#2 */
		} elseif ($this->SubType===1) {
			if (is_array($Query)) {
				$this->RecSet = &$Query; /* COMPAT#3 */
			} else {
				$this->DataAlert('type \''.gettype($Query).'\' not supported for the Query Parameter going with \'array\' Source Type.');
			}
		} elseif ($this->SubType===2) {
			// TBS query string for array and objects, syntax: "var[item1][item2]->item3[item4]..."
			$x = trim($Query);
			$z = chr(0);
			$x = str_replace(array(']->','][','->','['),$z,$x);
			if (substr($x,strlen($x)-1,1)===']') $x = substr($x,0,strlen($x)-1);
			$ItemLst = explode($z,$x);
			$ItemNbr = count($ItemLst);
			$Item0 = &$ItemLst[0];
			// Check first item
			if ($Item0[0]==='~') {
				$Item0 = substr($Item0,1);
				if ($this->TBS->ObjectRef!==false) {
					$Var = &$this->TBS->ObjectRef;
					$i = 0;
				} else {
					$i = $this->DataAlert('invalid query \''.$Query.'\' because property ObjectRef is not set.');
				}
			} else {
				if (isset($this->TBS->VarRef[$Item0])) {
					$Var = &$this->TBS->VarRef[$Item0]; /* COMPAT#4 */
					$i = 1;
				} else {
					$i = $this->DataAlert('invalid query \''.$Query.'\' because VarRef item \''.$Item0.'\' is not found.');
				}
			}
			// Check sub-items
			$Empty = false;
			while (($i!==false) && ($i<$ItemNbr) && ($Empty===false)) {
				$x = $ItemLst[$i];
				if (is_array($Var)) {
					if (isset($Var[$x])) {
						$Var = &$Var[$x];
					} else {
						$Empty = true;
					}
				} elseif (is_object($Var)) {
					$ArgLst = $this->TBS->f_Misc_CheckArgLst($x);
					if (method_exists($Var,$x)) {
						$f = array(&$Var,$x); unset($Var);
						$Var = call_user_func_array($f,$ArgLst);
					} elseif (property_exists(get_class($Var),$x)) {
						if (isset($Var->$x)) $Var = &$Var->$x;
					} elseif (isset($Var->$x)) {
						$Var = $Var->$x; // useful for overloaded property
					} else {
						$Empty = true;
					}
				} else {
					$i = $this->DataAlert('invalid query \''.$Query.'\' because item \''.$ItemLst[$i].'\' is neither an Array nor an Object. Its type is \''.gettype($Var).'\'.');
				}
				if ($i!==false) $i++;
			}
			// Assign data
			if ($i!==false) {
				if ($Empty) {
					$this->RecSet = array();
				} else {
					$this->RecSet = &$Var;
				}
			}
		} elseif ($this->SubType===3) { // Clear
			$this->RecSet = array();
		}
		// First record
		if ($this->RecSet!==false) {
			$this->RecNbr = $this->RecNumInit + count($this->RecSet);
			$this->FirstRec = true;
			$this->RecSaved = true;
			$this->RecSaving = false;
		}
		break;
	case 6: // MySQL
		switch ($this->SubType) {
		case 0: $this->RecSet = @mysql_query($Query,$this->SrcId); break;
		case 1: $this->RecSet = $this->SrcId; break;
		case 2: $this->RecSet = @mysql_query($Query); break;
		}
		if ($this->RecSet===false) $this->DataAlert('MySql error message when opening the query: '.mysql_error());
		break;
	case 1: // Num
		$this->RecSet = true;
		$this->NumMin = 1;
		$this->NumMax = 1;
		$this->NumStep = 1;
		if (is_array($Query)) {
			if (isset($Query['min'])) $this->NumMin = $Query['min'];
			if (isset($Query['step'])) $this->NumStep = $Query['step'];
			if (isset($Query['max'])) {
				$this->NumMax = $Query['max'];
			} else {
				$this->RecSet = $this->DataAlert('the \'num\' source is an array that has no value for the \'max\' key.');
			}
			if ($this->NumStep==0) $this->RecSet = $this->DataAlert('the \'num\' source is an array that has a step value set to zero.');
		} else {
			$this->NumMax = ceil($Query);
		}
		if ($this->RecSet) {
			if ($this->NumStep>0) {
				$this->NumVal = $this->NumMin;
			} else {
				$this->NumVal = $this->NumMax;
			}
		}
		break;
	case 2: // Text
		if (is_string($Query)) {
			$this->RecSet = &$Query;
		} else {
			$this->RecSet = $this->TBS->meth_Misc_ToStr($Query);
		}
		break;
	case 3: // Custom function
		$FctOpen = $this->FctOpen;
		$this->RecSet = $FctOpen($this->SrcId,$Query,$QryPrms);
		if ($this->RecSet===false) $this->DataAlert('function '.$FctOpen.'() has failed to open query {'.$Query.'}');
		break;
	case 4: // Custom method from ObjectRef
		$this->RecSet = call_user_func_array($this->FctOpen,array(&$this->SrcId,&$Query,&$QryPrms));
		if ($this->RecSet===false) $this->DataAlert('method '.get_class($this->FctOpen[0]).'::'.$this->FctOpen[1].'() has failed to open query {'.$Query.'}');
		break;
	case 5: // Custom method of object
		$this->RecSet = $this->SrcId->tbsdb_open($this->SrcId,$Query,$QryPrms);
		if ($this->RecSet===false) $this->DataAlert('method '.get_class($this->SrcId).'::tbsdb_open() has failed to open query {'.$Query.'}');
		break;
	case 7: // PostgreSQL
		switch ($this->SubType) {
		case 0: $this->RecSet = @pg_query($this->SrcId,$Query); break;
		case 1: $this->RecSet = $this->SrcId; break;
		}
		if ($this->RecSet===false) $this->DataAlert('PostgreSQL error message when opening the query: '.pg_last_error($this->SrcId));
		break;
	case 8: // SQLite
		switch ($this->SubType) {
		case 0: $this->RecSet = @sqlite_query($this->SrcId,$Query); break;
		case 1: $this->RecSet = $this->SrcId; break;
		}
		if ($this->RecSet===false) $this->DataAlert('SQLite error message when opening the query:'.sqlite_error_string(sqlite_last_error($this->SrcId)));
		break;
	case 9: // Iterator
		if ($this->SubType==1) {
			$this->RecSet = $this->SrcId;
		} else { // 2 or 3
			$this->RecSet = $this->SrcId->getIterator();
		}
		$this->RecSet->rewind();
		break;
	case 10: // MySQLi
		$this->RecSet = $this->SrcId->query($Query);
		if ($this->RecSet===false) $this->DataAlert('MySQLi error message when opening the query:'.$this->SrcId->error);
		break;
	case 11: // PDO
		$this->RecSet = $this->SrcId->prepare($Query);
		if ($this->RecSet===false) {
			$ok = false;
		} else {
			if (!is_array($QryPrms)) $QryPrms = array();
			$ok = $this->RecSet->execute($QryPrms);
		}
		if (!$ok) {
			$err = $this->SrcId->errorInfo();
			$this->DataAlert('PDO error message when opening the query:'.$err[2]);
		}
		break;
	case 12: // Zend_DB_Adapter
		try {
			if (!is_array($QryPrms)) $QryPrms = array();
			$this->RecSet = $this->SrcId->query($Query, $QryPrms);
		} catch (Exception $e) {
			$this->DataAlert('Zend_DB_Adapter error message when opening the query: '.$e->getMessage());
		}
		break;
	case 13: // SQLite3
		try {
			if ($this->SubType==3) {
				$this->RecSet = $this->SrcId;
			} elseif (($this->SubType==1) && (!is_array($QryPrms))) {
				// SQL statement without parameters
				$this->RecSet = $this->SrcId->query($Query);
			} else {
				if ($this->SubType==2) {
					$stmt = $this->SrcId;
					$prms = $Query;
				} else {
					// SQL statement with parameters
					$stmt = $this->SrcId->prepare($Query);
					$prms = $QryPrms;
				}
				// bind parameters
				if (is_array($prms)) {
					foreach ($prms as $p => $v) {
						if (is_numeric($p)) {
							$p = $p + 1;
						}
						if (is_array($v)) {
							$stmt->bindValue($p, $v[0], $v[1]);
						} else {
							$stmt->bindValue($p, $v);
						}
					}
				}
				$this->RecSet = $stmt->execute();
			}
		} catch (Exception $e) {
			$this->DataAlert('SQLite3 error message when opening the query: '.$e->getMessage());
		}
		break;
	}

	if (($this->Type===0) || ($this->Type===9)) {
		unset($this->RecKey); $this->RecKey = '';
	} else {
		if ($this->RecSaving) {
			unset($this->RecBuffer); $this->RecBuffer = array();
		}
		$this->RecKey = &$this->RecNum; // Not array: RecKey = RecNum
	}

	return ($this->RecSet!==false);

}

public function DataFetch() {

	if ($this->RecSaved) {
		if ($this->RecNum<$this->RecNbr) {
			if ($this->FirstRec) {
				if ($this->SubType===2) { // From string
					reset($this->RecSet);
					$this->RecKey = key($this->RecSet);
					$this->CurrRec = &$this->RecSet[$this->RecKey];
				} else {
					$this->CurrRec = reset($this->RecSet);
					$this->RecKey = key($this->RecSet);
				}
				$this->FirstRec = false;
			} else {
				if ($this->SubType===2) { // From string
					next($this->RecSet);
					$this->RecKey = key($this->RecSet);
					$this->CurrRec = &$this->RecSet[$this->RecKey];
				} else {
					$this->CurrRec = next($this->RecSet);
					$this->RecKey = key($this->RecSet);
				}
			}
			if ((!is_array($this->CurrRec)) && (!is_object($this->CurrRec))) $this->CurrRec = array('key'=>$this->RecKey, 'val'=>$this->CurrRec);
			$this->RecNum++;
			if ($this->OnDataOk) {
				$this->OnDataArgs[1] = &$this->CurrRec; // Reference has changed if ($this->SubType===2)
				if ($this->OnDataPrm) call_user_func_array($this->OnDataPrmRef,$this->OnDataArgs);
				if ($this->OnDataPi) $this->TBS->meth_PlugIn_RunAll($this->OnDataPiRef,$this->OnDataArgs);
				if ($this->SubType!==2) $this->RecSet[$this->RecKey] = $this->CurrRec; // save modifications because array reading is done without reference :(
			}
		} else {
			unset($this->CurrRec); $this->CurrRec = false;
		}
		return;
	}

	switch ($this->Type) {
	case 6: // MySQL
		$this->CurrRec = mysql_fetch_assoc($this->RecSet);
		break;
	case 1: // Num
		if (($this->NumVal>=$this->NumMin) && ($this->NumVal<=$this->NumMax)) {
			$this->CurrRec = array('val'=>$this->NumVal);
			$this->NumVal += $this->NumStep;
		} else {
			$this->CurrRec = false;
		}
		break;
	case 2: // Text
		if ($this->RecNum===0) {
			if ($this->RecSet==='') {
				$this->CurrRec = false;
			} else {
				$this->CurrRec = &$this->RecSet;
			}
		} else {
			$this->CurrRec = false;
		}
		break;
	case 3: // Custom function
		$FctFetch = $this->FctFetch;
		$this->CurrRec = $FctFetch($this->RecSet,$this->RecNum+1);
		break;
	case 4: // Custom method from ObjectRef
		$this->FctPrm[0] = &$this->RecSet; $this->FctPrm[1] = $this->RecNum+1;
		$this->CurrRec = call_user_func_array($this->FctFetch,$this->FctPrm);
		break;
	case 5: // Custom method of object
		$this->CurrRec = $this->SrcId->tbsdb_fetch($this->RecSet,$this->RecNum+1);
		break;
	case 7: // PostgreSQL
		$this->CurrRec = pg_fetch_assoc($this->RecSet); /* COMPAT#5 */
		break;
	case 8: // SQLite
		$this->CurrRec = sqlite_fetch_array($this->RecSet,SQLITE_ASSOC);
		break;
	case 9: // Iterator
		if ($this->RecSet->valid()) {
			$this->CurrRec = $this->RecSet->current();
			$this->RecKey = $this->RecSet->key();
			$this->RecSet->next();
		} else {
			$this->CurrRec = false;
		}
		break;
	case 10: // MySQLi
		$this->CurrRec = $this->RecSet->fetch_assoc();
		if (is_null($this->CurrRec)) $this->CurrRec = false;
		break;
	case 11: // PDO
		$this->CurrRec = $this->RecSet->fetch(PDO::FETCH_ASSOC);
		break;
	case 12: // Zend_DB_Adapater
		$this->CurrRec = $this->RecSet->fetch(Zend_Db::FETCH_ASSOC);
		break;
	case 13: // SQLite3
		$this->CurrRec = $this->RecSet->fetchArray(SQLITE3_ASSOC);
		break;
	}

	// Set the row count
	if ($this->CurrRec!==false) {
		$this->RecNum++;
		if ($this->OnDataOk) {
			if ($this->OnDataPrm) call_user_func_array($this->OnDataPrmRef,$this->OnDataArgs);
			if ($this->OnDataPi) $this->TBS->meth_PlugIn_RunAll($this->OnDataPiRef,$this->OnDataArgs);
		}
		if ($this->RecSaving) $this->RecBuffer[$this->RecKey] = $this->CurrRec;
	}

}

public function DataClose() {
	$this->OnDataOk = false;
	$this->OnDataPrm = false;
	$this->OnDataPi = false;
	if ($this->RecSaved) return;
	switch ($this->Type) {
	case 6: mysql_free_result($this->RecSet); break;
	case 3: $FctClose=$this->FctClose; $FctClose($this->RecSet); break;
	case 4: call_user_func_array($this->FctClose,array(&$this->RecSet)); break;
	case 5: $this->SrcId->tbsdb_close($this->RecSet); break;
	case 7: pg_free_result($this->RecSet); break;
	case 10: $this->RecSet->free(); break; // MySQLi
	case 13: // SQLite3
		if ($this->SubType!=3) {
			$this->RecSet->finalize();
		}
		break;
	//case 11: $this->RecSet->closeCursor(); break; // PDO
	}
	if ($this->RecSaving) {
		$this->RecSet = &$this->RecBuffer;
		$this->RecNbr = $this->RecNumInit + count($this->RecSet);
		$this->RecSaving = false;
		$this->RecSaved = true;
	}
}

}

// *********************************************

class clsTinyButStrong {

// Public properties
public $Source = '';
public $Render = 3;
public $TplVars = array();
public $ObjectRef = false;
public $NoErr = false;
public $Assigned = array();
public $ExtendedMethods = array();
public $ErrCount = 0;
// Undocumented (can change at any version)
public $Version = '3.10.1';
public $Charset = '';
public $TurboBlock = true;
public $VarPrefix = '';
public $VarRef = null;
public $FctPrefix = '';
public $Protect = true;
public $ErrMsg = '';
public $AttDelim = false;
public $MethodsAllowed = false;
public $OnLoad = true;
public $OnShow = true;
public $IncludePath = array();
public $TplStore = array();
public $OldSubTpl = false;
// Private
public $_ErrMsgName = '';
public $_LastFile = '';
public $_CharsetFct = false;
public $_Mode = 0;
public $_CurrBlock = '';
public $_ChrOpen = '[';
public $_ChrClose = ']';
public $_ChrVal = '[val]';
public $_ChrProtect = '&#91;';
public $_PlugIns = array();
public $_PlugIns_Ok = false;
public $_piOnFrm_Ok = false;

function __construct($Options=null,$VarPrefix='',$FctPrefix='') {

	// Compatibility
	if (is_string($Options)) {
		$Chrs = $Options;
		$Options = array('var_prefix'=>$VarPrefix, 'fct_prefix'=>$FctPrefix);
		if ($Chrs!=='') {
			$Err = true;
			$Len = strlen($Chrs);
			if ($Len===2) { // For compatibility
				$Options['chr_open']  = $Chrs[0];
				$Options['chr_close'] = $Chrs[1];
				$Err = false;
			} else {
				$Pos = strpos($Chrs,',');
				if (($Pos!==false) && ($Pos>0) && ($Pos<$Len-1)) {
					$Options['chr_open']  = substr($Chrs,0,$Pos);
					$Options['chr_close'] = substr($Chrs,$Pos+1);
					$Err = false;
				}
			}
			if ($Err) $this->meth_Misc_Alert('with clsTinyButStrong() function','value \''.$Chrs.'\' is a bad tag delimitor definition.');
		}
	} 

	// Set options
	$this->VarRef =& $GLOBALS;
	if (is_array($Options)) $this->SetOption($Options);

	// Links to global variables (cannot be converted to static yet because of compatibility)
	global $_TBS_FormatLst, $_TBS_UserFctLst, $_TBS_BlockAlias, $_TBS_AutoInstallPlugIns, $_TBS_ParallelLst;
	if (!isset($_TBS_FormatLst))   $_TBS_FormatLst  = array();
	if (!isset($_TBS_UserFctLst))  $_TBS_UserFctLst = array();
	if (!isset($_TBS_BlockAlias))  $_TBS_BlockAlias = array();
	if (!isset($_TBS_ParallelLst)) $_TBS_ParallelLst = array();
	$this->_UserFctLst = &$_TBS_UserFctLst;

	// Auto-installing plug-ins
	if (isset($_TBS_AutoInstallPlugIns)) foreach ($_TBS_AutoInstallPlugIns as $pi) $this->PlugIn(TBS_INSTALL,$pi);

}

function __call($meth, $args) {
	if (isset($this->ExtendedMethods[$meth])) {
		if ( is_array($this->ExtendedMethods[$meth]) || is_string($this->ExtendedMethods[$meth]) ) {
			return call_user_func_array($this->ExtendedMethods[$meth], $args);
		} else {
			return call_user_func_array(array(&$this->ExtendedMethods[$meth], $meth), $args);
		}
	} else {
		$this->meth_Misc_Alert('Method not found','\''.$meth.'\' is neither a native nor an extended method of TinyButStrong.');
	}
}

function SetOption($o, $v=false, $d=false) {
	if (!is_array($o)) $o = array($o=>$v);
	if (isset($o['var_prefix'])) $this->VarPrefix = $o['var_prefix'];
	if (isset($o['fct_prefix'])) $this->FctPrefix = $o['fct_prefix'];
	if (isset($o['noerr'])) $this->NoErr = $o['noerr'];
	if (isset($o['old_subtemplate'])) $this->OldSubTpl = $o['old_subtemplate'];
	if (isset($o['auto_merge'])) {
		$this->OnLoad = $o['auto_merge'];
		$this->OnShow = $o['auto_merge'];
	}
	if (isset($o['onload'])) $this->OnLoad = $o['onload'];
	if (isset($o['onshow'])) $this->OnShow = $o['onshow'];
	if (isset($o['att_delim'])) $this->AttDelim = $o['att_delim'];
	if (isset($o['protect'])) $this->Protect = $o['protect'];
	if (isset($o['turbo_block'])) $this->TurboBlock = $o['turbo_block'];
	if (isset($o['charset'])) $this->meth_Misc_Charset($o['charset']);

	$UpdateChr = false;
	if (isset($o['chr_open'])) {
		$this->_ChrOpen = $o['chr_open'];
		$UpdateChr = true;
	}
	if (isset($o['chr_close'])) {
		$this->_ChrClose = $o['chr_close'];
		$UpdateChr = true;
	}
	if ($UpdateChr) {
		$this->_ChrVal = $this->_ChrOpen.'val'.$this->_ChrClose;
		$this->_ChrProtect = '&#'.ord($this->_ChrOpen[0]).';'.substr($this->_ChrOpen,1);
	}
	if (array_key_exists('tpl_frms',$o)) self::f_Misc_UpdateArray($GLOBALS['_TBS_FormatLst'], 'frm', $o['tpl_frms'], $d);
	if (array_key_exists('block_alias',$o)) self::f_Misc_UpdateArray($GLOBALS['_TBS_BlockAlias'], false, $o['block_alias'], $d);
	if (array_key_exists('parallel_conf',$o)) self::f_Misc_UpdateArray($GLOBALS['_TBS_ParallelLst'], false, $o['parallel_conf'], $d);
	if (array_key_exists('include_path',$o)) self::f_Misc_UpdateArray($this->IncludePath, true, $o['include_path'], $d);
	if (isset($o['render'])) $this->Render = $o['render'];
	if (isset($o['methods_allowed'])) $this->MethodsAllowed = $o['methods_allowed'];
}

function GetOption($o) {
	if ($o==='all') {
		$x = explode(',', 'var_prefix,fct_prefix,noerr,auto_merge,onload,onshow,att_delim,protect,turbo_block,charset,chr_open,chr_close,tpl_frms,block_alias,parallel_conf,include_path,render');
		$r = array();
		foreach ($x as $o) $r[$o] = $this->GetOption($o);
		return $r;
	}
	if ($o==='var_prefix') return $this->VarPrefix;
	if ($o==='fct_prefix') return $this->FctPrefix;
	if ($o==='noerr') return $this->NoErr;
	if ($o==='auto_merge') return ($this->OnLoad && $this->OnShow);
	if ($o==='onload') return $this->OnLoad;
	if ($o==='onshow') return $this->OnShow;
	if ($o==='att_delim') return $this->AttDelim;
	if ($o==='protect') return $this->Protect;
	if ($o==='turbo_block') return $this->TurboBlock;
	if ($o==='charset') return $this->Charset;
	if ($o==='chr_open') return $this->_ChrOpen;
	if ($o==='chr_close') return $this->_ChrClose;
	if ($o==='tpl_frms') {
		// simplify the list of formats
		$x = array();
		foreach ($GLOBALS['_TBS_FormatLst'] as $s=>$i) $x[$s] = $i['Str'];
		return $x;
	}
	if ($o==='include_path') return $this->IncludePath;
	if ($o==='render') return $this->Render;
	if ($o==='methods_allowed') return $this->MethodsAllowed;
	if ($o==='parallel_conf') return $GLOBALS['_TBS_ParallelLst'];
	if ($o==='block_alias') return $GLOBALS['_TBS_BlockAlias'];
	return $this->meth_Misc_Alert('with GetOption() method','option \''.$o.'\' is not supported.');;
}

public function ResetVarRef($ToGlobal) {
	if ($ToGlobal) {
		$this->VarRef = &$GLOBALS;
	} else {
		$x = array();
		$this->VarRef = &$x;
	}
}

// Public methods
public function LoadTemplate($File,$Charset='') {
	if ($File==='') {
		$this->meth_Misc_Charset($Charset);
		return true;
	}
	$Ok = true;
	if ($this->_PlugIns_Ok) {
		if (isset($this->_piBeforeLoadTemplate) || isset($this->_piAfterLoadTemplate)) {
			// Plug-ins
			$ArgLst = func_get_args();
			$ArgLst[0] = &$File;
			$ArgLst[1] = &$Charset;
			if (isset($this->_piBeforeLoadTemplate)) $Ok = $this->meth_PlugIn_RunAll($this->_piBeforeLoadTemplate,$ArgLst);
		}
	}
	// Load the file
	if ($Ok!==false) {
		if (!is_null($File)) {
			$x = '';
			if (!$this->f_Misc_GetFile($x, $File, $this->_LastFile, $this->IncludePath)) return $this->meth_Misc_Alert('with LoadTemplate() method','file \''.$File.'\' is not found or not readable.');
			if ($Charset==='+') {
				$this->Source .= $x;
			} else {
				$this->Source = $x;
			}
		}
		if ($this->meth_Misc_IsMainTpl()) {
			if (!is_null($File)) $this->_LastFile = $File;
			if ($Charset!=='+') $this->TplVars = array();
			$this->meth_Misc_Charset($Charset);
		}
		// Automatic fields and blocks
		if ($this->OnLoad) $this->meth_Merge_AutoOn($this->Source,'onload',true,true);
	}
	// Plug-ins
	if ($this->_PlugIns_Ok && isset($ArgLst) && isset($this->_piAfterLoadTemplate)) $Ok = $this->meth_PlugIn_RunAll($this->_piAfterLoadTemplate,$ArgLst);
	return $Ok;
}

public function GetBlockSource($BlockName,$AsArray=false,$DefTags=true,$ReplaceWith=false) {
	$RetVal = array();
	$Nbr = 0;
	$Pos = 0;
	$FieldOutside = false;
	$P1 = false;
	$Mode = ($DefTags) ? 3 : 2;
	$PosBeg1 = 0;
	while ($Loc = $this->meth_Locator_FindBlockNext($this->Source,$BlockName,$Pos,'.',$Mode,$P1,$FieldOutside)) {
		$Nbr++;
		$Sep = '';
		if ($Nbr==1) {
			$PosBeg1 = $Loc->PosBeg;
		} elseif (!$AsArray) {
			$Sep = substr($this->Source,$PosSep,$Loc->PosBeg-$PosSep); // part of the source between sections
		}
		$RetVal[$Nbr] = $Sep.$Loc->BlockSrc;
		$Pos = $Loc->PosEnd;
		$PosSep = $Loc->PosEnd+1;
		$P1 = false;
	}
	if ($Nbr==0) return false;
	if (!$AsArray) {
		if ($DefTags)  {
			// Return the true part of the template
			$RetVal = substr($this->Source,$PosBeg1,$Pos-$PosBeg1+1);
		} else {
			// Return the concatenated section without def tags
			$RetVal = implode('', $RetVal);
		}
	}
	if ($ReplaceWith!==false) $this->Source = substr($this->Source,0,$PosBeg1).$ReplaceWith.substr($this->Source,$Pos+1);
	return $RetVal;
}

public function MergeBlock($BlockLst,$SrcId='assigned',$Query='',$QryPrms=false) {

	if ($SrcId==='assigned') {
		$Arg = array($BlockLst,&$SrcId,&$Query,&$QryPrms);
		if (!$this->meth_Misc_Assign($BlockLst, $Arg, 'MergeBlock')) return 0;
		$BlockLst = $Arg[0]; $SrcId = &$Arg[1]; $Query = &$Arg[2];
	}

	if (is_string($BlockLst)) $BlockLst = explode(',',$BlockLst);

	if ($SrcId==='cond') {
		$Nbr = 0;
		foreach ($BlockLst as $Block) {
			$Block = trim($Block);
			if ($Block!=='') $Nbr += $this->meth_Merge_AutoOn($this->Source,$Block,true,true);
		}
		return $Nbr;
	} else {
		return $this->meth_Merge_Block($this->Source,$BlockLst,$SrcId,$Query,false,0,$QryPrms);
	}

}

public function MergeField($NameLst,$Value='assigned',$IsUserFct=false,$DefaultPrm=false) {

	$FctCheck = $IsUserFct;
	if ($PlugIn = isset($this->_piOnMergeField)) $ArgPi = array('','',&$Value,0,&$this->Source,0,0);
	$SubStart = 0;
	$Ok = true;
	$Prm = is_array($DefaultPrm);

	if ( ($Value==='assigned') && ($NameLst!=='var') && ($NameLst!=='onshow') && ($NameLst!=='onload') ) {
		$Arg = array($NameLst,&$Value,&$IsUserFct,&$DefaultPrm);
		if (!$this->meth_Misc_Assign($NameLst, $Arg, 'MergeField')) return false;
		$NameLst = $Arg[0]; $Value = &$Arg[1]; $IsUserFct = &$Arg[2]; $DefaultPrm = &$Arg[3];
	}

	$NameLst = explode(',',$NameLst);

	foreach ($NameLst as $Name) {
		$Name = trim($Name);
		$Cont = false;
		switch ($Name) {
		case '': $Cont=true;break;
		case 'onload': $this->meth_Merge_AutoOn($this->Source,'onload',true,true);$Cont=true;break;
		case 'onshow': $this->meth_Merge_AutoOn($this->Source,'onshow',true,true);$Cont=true;break;
		case 'var':	$this->meth_Merge_AutoVar($this->Source,true);$Cont=true;break;
		}
		if ($Cont) continue;
		if ($PlugIn) $ArgPi[0] = $Name;
		$PosBeg = 0;
		// Initilize the user function (only once)
		if ($FctCheck) {
			$FctInfo = $Value;
			$ErrMsg = false;
			if (!$this->meth_Misc_UserFctCheck($FctInfo,'f',$ErrMsg,$ErrMsg,false)) return $this->meth_Misc_Alert('with MergeField() method',$ErrMsg);
			$FctArg = array('','');
			$SubStart = false;
			$FctCheck = false;
		}
		while ($Loc = $this->meth_Locator_FindTbs($this->Source,$Name,$PosBeg,'.')) {
			if ($Prm) $Loc->PrmLst = array_merge($DefaultPrm,$Loc->PrmLst);
			// Apply user function
			if ($IsUserFct) {
				$FctArg[0] = &$Loc->SubName; $FctArg[1] = &$Loc->PrmLst;
				$Value = call_user_func_array($FctInfo,$FctArg);
			}
			// Plug-ins
			if ($PlugIn) {
				$ArgPi[1] = $Loc->SubName; $ArgPi[3] = &$Loc->PrmLst; $ArgPi[5] = &$Loc->PosBeg; $ArgPi[6] = &$Loc->PosEnd;
				$Ok = $this->meth_PlugIn_RunAll($this->_piOnMergeField,$ArgPi);
			}
			// Merge the field
			if ($Ok) {
				$PosBeg = $this->meth_Locator_Replace($this->Source,$Loc,$Value,$SubStart);
			} else {
				$PosBeg = $Loc->PosEnd;
			}
		}
	}
}

public function Show($Render=false) {
	$Ok = true;
	if ($Render===false) $Render = $this->Render;
	if ($this->_PlugIns_Ok) {
		if (isset($this->_piBeforeShow) || isset($this->_piAfterShow)) {
			// Plug-ins
			$ArgLst = func_get_args();
			$ArgLst[0] = &$Render;
			if (isset($this->_piBeforeShow)) $Ok = $this->meth_PlugIn_RunAll($this->_piBeforeShow,$ArgLst);
		}
	}
	if ($Ok!==false) {
		if ($this->OnShow) $this->meth_Merge_AutoOn($this->Source,'onshow',true,true);
		$this->meth_Merge_AutoVar($this->Source,true);
	}
	if ($this->_PlugIns_Ok && isset($ArgLst) && isset($this->_piAfterShow)) $this->meth_PlugIn_RunAll($this->_piAfterShow,$ArgLst);
	if ($this->_ErrMsgName!=='') $this->MergeField($this->_ErrMsgName, $this->ErrMsg);
	if ($this->meth_Misc_IsMainTpl()) {
		if (($Render & TBS_OUTPUT)==TBS_OUTPUT) echo $this->Source;
		if (($Render & TBS_EXIT)==TBS_EXIT) exit;
	} elseif ($this->OldSubTpl) {
		if (($Render & TBS_OUTPUT)==TBS_OUTPUT) echo $this->Source;
	}
	return $Ok;
}

public function PlugIn($Prm1,$Prm2=0) {

	if (is_numeric($Prm1)) {
		switch ($Prm1) {
		case TBS_INSTALL: // Try to install the plug-in
			$PlugInId = $Prm2;
			if (isset($this->_PlugIns[$PlugInId])) {
				return $this->meth_Misc_Alert('with PlugIn() method','plug-in \''.$PlugInId.'\' is already installed.');
			} else {
				$ArgLst = func_get_args();
				array_shift($ArgLst); array_shift($ArgLst);
				return $this->meth_PlugIn_Install($PlugInId,$ArgLst,false);
			}
		case TBS_ISINSTALLED: // Check if the plug-in is installed
			return isset($this->_PlugIns[$Prm2]);
		case -4: // Deactivate special plug-ins
			$this->_PlugIns_Ok_save = $this->_PlugIns_Ok;
			$this->_PlugIns_Ok = false;
			return true;
		case -5: // Deactivate OnFormat
			$this->_piOnFrm_Ok_save = $this->_piOnFrm_Ok;
			$this->_piOnFrm_Ok = false;
			return true;
		case -10:  // Restore
			if (isset($this->_PlugIns_Ok_save)) $this->_PlugIns_Ok = $this->_PlugIns_Ok_save;
			if (isset($this->_piOnFrm_Ok_save)) $this->_piOnFrm_Ok = $this->_piOnFrm_Ok_save;
			return true;
		}

	} elseif (is_string($Prm1)) {
		// Plug-in's command
		$p = strpos($Prm1,'.');
		if ($p===false) {
			$PlugInId = $Prm1;
		} else {
			$PlugInId = substr($Prm1,0,$p); // direct command
		}
		if (!isset($this->_PlugIns[$PlugInId])) {
			if (!$this->meth_PlugIn_Install($PlugInId,array(),true)) return false;
		}
		if (!isset($this->_piOnCommand[$PlugInId])) return $this->meth_Misc_Alert('with PlugIn() method','plug-in \''.$PlugInId.'\' can\'t run any command because the OnCommand event is not defined or activated.');
		$ArgLst = func_get_args();
		if ($p===false) array_shift($ArgLst);
		$Ok = call_user_func_array($this->_piOnCommand[$PlugInId],$ArgLst);
		if (is_null($Ok)) $Ok = true;
		return $Ok;
	}
	return $this->meth_Misc_Alert('with PlugIn() method','\''.$Prm1.'\' is an invalid plug-in key, the type of the value is \''.gettype($Prm1).'\'.');

}

// *-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-

function meth_Locator_FindTbs(&$Txt,$Name,$Pos,$ChrSub) {
// Find a TBS Locator

	$PosEnd = false;
	$PosMax = strlen($Txt) -1;
	$Start = $this->_ChrOpen.$Name;

	do {
		// Search for the opening char
		if ($Pos>$PosMax) return false;
		$Pos = strpos($Txt,$Start,$Pos);

		// If found => next chars are analyzed
		if ($Pos===false) {
			return false;
		} else {
			$Loc = new clsTbsLocator;
			$ReadPrm = false;
			$PosX = $Pos + strlen($Start);
			$x = $Txt[$PosX];

			if ($x===$this->_ChrClose) {
				$PosEnd = $PosX;
			} elseif ($x===$ChrSub) {
				$Loc->SubOk = true; // it is no longer the false value
				$ReadPrm = true;
				$PosX++;
			} elseif (strpos(';',$x)!==false) {
				$ReadPrm = true;
				$PosX++;
			} else {
				$Pos++;
			}

			$Loc->PosBeg = $Pos;
			if ($ReadPrm) {
				self::f_Loc_PrmRead($Txt,$PosX,false,'\'',$this->_ChrOpen,$this->_ChrClose,$Loc,$PosEnd);
				if ($PosEnd===false) {
					$this->meth_Misc_Alert('','can\'t found the end of the tag \''.substr($Txt,$Pos,$PosX-$Pos+10).'...\'.');
					$Pos++;
				}
			}

		}

	} while ($PosEnd===false);

	$Loc->PosEnd = $PosEnd;
	if ($Loc->SubOk) {
		$Loc->FullName = $Name.'.'.$Loc->SubName;
		$Loc->SubLst = explode('.',$Loc->SubName);
		$Loc->SubNbr = count($Loc->SubLst);
	} else {
		$Loc->FullName = $Name;
	}
	if ( $ReadPrm && ( isset($Loc->PrmLst['enlarge']) || isset($Loc->PrmLst['comm']) ) ) {
		$Loc->PosBeg0 = $Loc->PosBeg;
		$Loc->PosEnd0 = $Loc->PosEnd;
		$enlarge = (isset($Loc->PrmLst['enlarge'])) ? $Loc->PrmLst['enlarge'] : $Loc->PrmLst['comm'];
		if (($enlarge===true) || ($enlarge==='')) {
			$Loc->Enlarged = self::f_Loc_EnlargeToStr($Txt,$Loc,'<!--' ,'-->');
		} else {
			$Loc->Enlarged = self::f_Loc_EnlargeToTag($Txt,$Loc,$enlarge,false);
		}
	}

	return $Loc;

}

function &meth_Locator_SectionNewBDef(&$LocR,$BlockName,$Txt,$PrmLst,$Cache) {

	$Chk = true;
	$LocLst = array();
	$Pos = 0;
	$Sort = false;
	
	if ($this->_PlugIns_Ok && isset($this->_piOnCacheField)) {
		$pi = true;
		$ArgLst = array(0=>$BlockName, 1=>false, 2=>&$Txt, 3=>array('att'=>true), 4=>&$LocLst, 5=>&$Pos);
	} else {
		$pi = false;
	}

	// Cache TBS locators
	$Cache = ($Cache && $this->TurboBlock);
	if ($Cache) {

		$Chk = false;
		while ($Loc = $this->meth_Locator_FindTbs($Txt,$BlockName,$Pos,'.')) {

			$LocNbr = 1 + count($LocLst);
			$LocLst[$LocNbr] = &$Loc;
			
			// Next search position : always ("original PosBeg" + 1).
			// Must be done here because loc can be moved by the plug-in.
			if ($Loc->Enlarged) {
				// Enlarged
				$Pos = $Loc->PosBeg0 + 1;
				$Loc->Enlarged = false;
			} else {
				// Normal
				$Pos = $Loc->PosBeg + 1;
			}

			// Note: the plug-in may move, delete and add one or several locs.
			// Move   : backward or forward (will be sorted)
			// Delete : add property DelMe=true
			// Add    : at the end of $LocLst (will be sorted)
			if ($pi) {
				$ArgLst[1] = &$Loc;
				$this->meth_Plugin_RunAll($this->_piOnCacheField,$ArgLst);
			}

			if (($Loc->SubName==='#') || ($Loc->SubName==='$')) {
				$Loc->IsRecInfo = true;
				$Loc->RecInfo = $Loc->SubName;
				$Loc->SubName = '';
			} else {
				$Loc->IsRecInfo = false;
			}
			
			// Process parameter att for new added locators.
			$NewNbr = count($LocLst);
			for ($i=$LocNbr;$i<=$NewNbr;$i++) {
				$li = &$LocLst[$i];
				if (isset($li->PrmLst['att'])) {
					$LocSrc = substr($Txt,$li->PosBeg,$li->PosEnd-$li->PosBeg+1); // for error message
					if ($this->f_Xml_AttFind($Txt,$li,$LocLst,$this->AttDelim)) {
						if (isset($Loc->PrmLst['atttrue'])) {
							$li->PrmLst['magnet'] = '#';
							$li->PrmLst['ope'] = (isset($li->PrmLst['ope'])) ? $li->PrmLst['ope'].',attbool' : 'attbool';
						}
						if ($i==$LocNbr) {
							$Pos = $Loc->DelPos;
						}
					} else {
						$this->meth_Misc_Alert('','TBS is not able to merge the field '.$LocSrc.' because the entity targeted by parameter \'att\' cannot be found.');
					}
				}
			}

			unset($Loc);
			
		}

		// Re-order loc
		$e = self::f_Loc_Sort($LocLst, true, 1);
		$Chk = ($e > 0);
		
	}

	// Create the object
	$o = (object) null;
	$o->Prm = $PrmLst;
	$o->LocLst = $LocLst;
	$o->LocNbr = count($LocLst);
	$o->Name = $BlockName;
	$o->Src = $Txt;
	$o->Chk = $Chk;
	$o->IsSerial = false;
	$o->AutoSub = false;
	$i = 1;
	while (isset($PrmLst['sub'.$i])) {
		$o->AutoSub = $i;
		$i++;
	}

	$LocR->BDefLst[] = &$o; // Can be usefull for plug-in
	return $o;

}

function meth_Locator_SectionAddGrp(&$LocR,$BlockName,&$BDef,$Type,$Field,$Prm) {

	$BDef->PrevValue = false;
	$BDef->Type = $Type;

	// Save sub items in a structure near to Locator.
	$Field0 = $Field;
	if (strpos($Field,$this->_ChrOpen)===false) $Field = $this->_ChrOpen.$BlockName.'.'.$Field.';tbstype='.$Prm.$this->_ChrClose; // tbstype is an internal parameter for catching errors
	$BDef->FDef = &$this->meth_Locator_SectionNewBDef($LocR,$BlockName,$Field,array(),true);
	if ($BDef->FDef->LocNbr==0)	$this->meth_Misc_Alert('Parameter '.$Prm,'The value \''.$Field0.'\' is unvalide for this parameter.');

	if ($Type==='H') {
		if ($LocR->HeaderFound===false) {
			$LocR->HeaderFound = true;
			$LocR->HeaderNbr = 0;
			$LocR->HeaderDef = array(); // 1 to HeaderNbr
		}
		$i = ++$LocR->HeaderNbr;
		$LocR->HeaderDef[$i] = &$BDef;
	} else {
		if ($LocR->FooterFound===false) {
			$LocR->FooterFound = true;
			$LocR->FooterNbr = 0;
			$LocR->FooterDef = array(); // 1 to FooterNbr
		}
		$BDef->AddLastGrp = ($Type==='F');
		$i = ++$LocR->FooterNbr;
		$LocR->FooterDef[$i] = &$BDef;
	}

}

function meth_Locator_Replace(&$Txt,&$Loc,&$Value,$SubStart) {
// This function enables to merge a locator with a text and returns the position just after the replaced block
// This position can be useful because we don't know in advance how $Value will be replaced.

	// Found the value if there is a subname
	if (($SubStart!==false) && $Loc->SubOk) {
		for ($i=$SubStart;$i<$Loc->SubNbr;$i++) {
			$x = $Loc->SubLst[$i]; // &$Loc... brings an error with Event Example, I don't know why.
			if (is_array($Value)) {
				if (isset($Value[$x])) {
					$Value = &$Value[$x];
				} elseif (array_key_exists($x,$Value)) {// can happens when value is NULL
					$Value = &$Value[$x];
				} else {
					if (!isset($Loc->PrmLst['noerr'])) $this->meth_Misc_Alert($Loc,'item \''.$x.'\' is not an existing key in the array.',true);
					unset($Value); $Value = ''; break;
				}
			} elseif (is_object($Value)) {
				$ArgLst = $this->f_Misc_CheckArgLst($x);
				if (method_exists($Value,$x)) {
					if ($this->MethodsAllowed || !in_array(strtok($Loc->FullName,'.'),array('onload','onshow','var')) ) {
						$x = call_user_func_array(array(&$Value,$x),$ArgLst);
					} else {
						if (!isset($Loc->PrmLst['noerr'])) $this->meth_Misc_Alert($Loc,'\''.$x.'\' is a method and the current TBS settings do not allow to call methods on automatic fields.',true);
						$x = '';	
					}
				} elseif (property_exists($Value,$x)) {
					$x = &$Value->$x;
				} elseif (isset($Value->$x)) {
					$x = $Value->$x; // useful for overloaded property
				} else {
					if (!isset($Loc->PrmLst['noerr'])) $this->meth_Misc_Alert($Loc,'item '.$x.'\' is neither a method nor a property in the class \''.get_class($Value).'\'.',true);
					unset($Value); $Value = ''; break;
				}
				$Value = &$x; unset($x); $x = '';
			} else {
				if (!isset($Loc->PrmLst['noerr'])) $this->meth_Misc_Alert($Loc,'item before \''.$x.'\' is neither an object nor an array. Its type is '.gettype($Value).'.',true);
				unset($Value); $Value = ''; break;
			}
		}
	}

	$CurrVal = $Value; // Unlink

	if (isset($Loc->PrmLst['onformat'])) {
		if ($Loc->FirstMerge) {
			$Loc->OnFrmInfo = $Loc->PrmLst['onformat'];
			$Loc->OnFrmArg = array($Loc->FullName,'',&$Loc->PrmLst,&$this);
			$ErrMsg = false;
			if (!$this->meth_Misc_UserFctCheck($Loc->OnFrmInfo,'f',$ErrMsg,$ErrMsg,true)) {
				unset($Loc->PrmLst['onformat']);
				if (!isset($Loc->PrmLst['noerr'])) $this->meth_Misc_Alert($Loc,'(parameter onformat) '.$ErrMsg);
				$Loc->OnFrmInfo = 'pi'; // Execute the function pi() just to avoid extra error messages
			}
		} else {
			$Loc->OnFrmArg[3] = &$this; // bugs.php.net/51174
		}
		$Loc->OnFrmArg[1] = &$CurrVal;
		if (isset($Loc->PrmLst['subtpl'])) {
			$this->meth_Misc_ChangeMode(true,$Loc,$CurrVal);
			call_user_func_array($Loc->OnFrmInfo,$Loc->OnFrmArg);
			$this->meth_Misc_ChangeMode(false,$Loc,$CurrVal);
			$Loc->ConvProtect = false;
			$Loc->ConvStr = false;
		} else {
			call_user_func_array($Loc->OnFrmInfo,$Loc->OnFrmArg);
		}
	}

	if ($Loc->FirstMerge) {
		if (isset($Loc->PrmLst['frm'])) {
			$Loc->ConvMode = 0; // Frm
			$Loc->ConvProtect = false;
		} else {
			// Analyze parameter 'strconv'
			if (isset($Loc->PrmLst['strconv'])) {
				$this->meth_Conv_Prepare($Loc, $Loc->PrmLst['strconv']);
			} elseif (isset($Loc->PrmLst['htmlconv'])) { // compatibility
				$this->meth_Conv_Prepare($Loc, $Loc->PrmLst['htmlconv']);
			} else {
				if ($this->Charset===false) $Loc->ConvStr = false; // No conversion
			}
			// Analyze parameter 'protect'
			if (isset($Loc->PrmLst['protect'])) {
				$x = strtolower($Loc->PrmLst['protect']);
				if ($x==='no') {
					$Loc->ConvProtect = false;
				} elseif ($x==='yes') {
					$Loc->ConvProtect = true;
				}
			} elseif ($this->Protect===false) {
				$Loc->ConvProtect = false;
			}
		}
		if ($Loc->Ope = isset($Loc->PrmLst['ope'])) {
			$OpeLst = explode(',',$Loc->PrmLst['ope']);
			$Loc->OpeAct = array();
			$Loc->OpeArg = array();
			$Loc->OpeUtf8 = false;
			foreach ($OpeLst as $i=>$ope) {
				if ($ope==='list') {
					$Loc->OpeAct[$i] = 1;
					$Loc->OpePrm[$i] = (isset($Loc->PrmLst['valsep'])) ? $Loc->PrmLst['valsep'] : ',';
					if (($Loc->ConvMode===1) && $Loc->ConvStr) $Loc->ConvMode = -1; // special mode for item list conversion
				} elseif ($ope==='minv') {
					$Loc->OpeAct[$i] = 11;
					$Loc->MSave = $Loc->MagnetId;
				} elseif ($ope==='attbool') { // this operation key is set when a loc is cached with paremeter atttrue
					$Loc->OpeAct[$i] = 14;
				} elseif ($ope==='utf8')  { $Loc->OpeUtf8 = true;
				} elseif ($ope==='upper') { $Loc->OpeAct[$i] = 15;
				} elseif ($ope==='lower') { $Loc->OpeAct[$i] = 16;
				} elseif ($ope==='upper1') { $Loc->OpeAct[$i] = 17;
				} elseif ($ope==='upperw') { $Loc->OpeAct[$i] = 18;
				} else {
					$x = substr($ope,0,4);
					if ($x==='max:') {
						$Loc->OpeAct[$i] = (isset($Loc->PrmLst['maxhtml'])) ? 2 : 3;
						if (isset($Loc->PrmLst['maxutf8'])) $Loc->OpeUtf8 = true;
						$Loc->OpePrm[$i] = intval(trim(substr($ope,4)));
						$Loc->OpeEnd = (isset($Loc->PrmLst['maxend'])) ? $Loc->PrmLst['maxend'] : '...';
						if ($Loc->OpePrm[$i]<=0) $Loc->Ope = false;
					} elseif ($x==='mod:') {$Loc->OpeAct[$i] = 5; $Loc->OpePrm[$i] = '0'+trim(substr($ope,4));
					} elseif ($x==='add:') {$Loc->OpeAct[$i] = 6; $Loc->OpePrm[$i] = '0'+trim(substr($ope,4));
					} elseif ($x==='mul:') {$Loc->OpeAct[$i] = 7; $Loc->OpePrm[$i] = '0'+trim(substr($ope,4));
					} elseif ($x==='div:') {$Loc->OpeAct[$i] = 8; $Loc->OpePrm[$i] = '0'+trim(substr($ope,4));
					} elseif ($x==='mok:') {$Loc->OpeAct[$i] = 9; $Loc->OpeMOK[] = trim(substr($ope,4)); $Loc->MSave = $Loc->MagnetId;
					} elseif ($x==='mko:') {$Loc->OpeAct[$i] =10; $Loc->OpeMKO[] = trim(substr($ope,4)); $Loc->MSave = $Loc->MagnetId;
					} elseif ($x==='nif:') {$Loc->OpeAct[$i] =12; $Loc->OpePrm[$i] = trim(substr($ope,4));
					} elseif ($x==='msk:') {$Loc->OpeAct[$i] =13; $Loc->OpePrm[$i] = trim(substr($ope,4));
					} elseif (isset($this->_piOnOperation)) {
						$Loc->OpeAct[$i] = 0;
						$Loc->OpePrm[$i] = $ope;
						$Loc->OpeArg[$i] = array($Loc->FullName,&$CurrVal,&$Loc->PrmLst,&$Txt,$Loc->PosBeg,$Loc->PosEnd,&$Loc);
						$Loc->PrmLst['_ope'] = $Loc->PrmLst['ope'];
					} elseif (!isset($Loc->PrmLst['noerr'])) {
						$this->meth_Misc_Alert($Loc,'parameter ope doesn\'t support value \''.$ope.'\'.',true);
					}
				}
			}
		}
		$Loc->FirstMerge = false;
	}
	$ConvProtect = $Loc->ConvProtect;

	// Plug-in OnFormat
	if ($this->_piOnFrm_Ok) {
		if (isset($Loc->OnFrmArgPi)) {
			$Loc->OnFrmArgPi[1] = &$CurrVal;
			$Loc->OnFrmArgPi[3] = &$this; // bugs.php.net/51174
		} else {
			$Loc->OnFrmArgPi = array($Loc->FullName,&$CurrVal,&$Loc->PrmLst,&$this);
		}
		$this->meth_PlugIn_RunAll($this->_piOnFormat,$Loc->OnFrmArgPi);
	}

	// Operation
	if ($Loc->Ope) {
		foreach ($Loc->OpeAct as $i=>$ope) {
			switch ($ope) {
			case 0:
				$Loc->PrmLst['ope'] = $Loc->OpePrm[$i]; // for compatibility
				$OpeArg = &$Loc->OpeArg[$i];
				$OpeArg[1] = &$CurrVal; $OpeArg[3] = &$Txt;
				if (!$this->meth_PlugIn_RunAll($this->_piOnOperation,$OpeArg)) return $Loc->PosBeg;
				break;
			case  1:
				if ($Loc->ConvMode===-1) {
					if (is_array($CurrVal)) {
						foreach ($CurrVal as $k=>$v) {
							$v = $this->meth_Misc_ToStr($v);
							$this->meth_Conv_Str($v,$Loc->ConvBr);
							$CurrVal[$k] = $v;
						}
						$CurrVal = implode($Loc->OpePrm[$i],$CurrVal);
					} else {
						$CurrVal = $this->meth_Misc_ToStr($CurrVal);
						$this->meth_Conv_Str($CurrVal,$Loc->ConvBr);
					}
				} else {
					if (is_array($CurrVal)) $CurrVal = implode($Loc->OpePrm[$i],$CurrVal);
				}
				break;
			case  2:
				$x = $this->meth_Misc_ToStr($CurrVal);
				if (strlen($x)>$Loc->OpePrm[$i]) {
					$this->f_Xml_Max($x,$Loc->OpePrm[$i],$Loc->OpeEnd);
				}
				break;
			case  3:
				$x = $this->meth_Misc_ToStr($CurrVal);
				if (strlen($x)>$Loc->OpePrm[$i]) {
					if ($Loc->OpeUtf8) {
						$CurrVal = mb_substr($x,0,$Loc->OpePrm[$i],'UTF-8').$Loc->OpeEnd;
					} else {
						$CurrVal = substr($x,0,$Loc->OpePrm[$i]).$Loc->OpeEnd;
					}
				}
				break;
			case  5: $CurrVal = ('0'+$CurrVal) % $Loc->OpePrm[$i]; break;
			case  6: $CurrVal = ('0'+$CurrVal) + $Loc->OpePrm[$i]; break;
			case  7: $CurrVal = ('0'+$CurrVal) * $Loc->OpePrm[$i]; break;
			case  8: $CurrVal = ('0'+$CurrVal) / $Loc->OpePrm[$i]; break;
			case  9; case 10:
				if ($ope===9) {
				 $CurrVal = (in_array($this->meth_Misc_ToStr($CurrVal),$Loc->OpeMOK)) ? ' ' : '';
				} else {
				 $CurrVal = (in_array($this->meth_Misc_ToStr($CurrVal),$Loc->OpeMKO)) ? '' : ' ';
				} // no break here
			case 11:
				if ($this->meth_Misc_ToStr($CurrVal)==='') {
					if ($Loc->MagnetId===0) $Loc->MagnetId = $Loc->MSave;
				} else {
					if ($Loc->MagnetId!==0) {
						$Loc->MSave = $Loc->MagnetId;
						$Loc->MagnetId = 0;
					}
					$CurrVal = '';
				}
				break;
			case 12: if ($this->meth_Misc_ToStr($CurrVal)===$Loc->OpePrm[$i]) $CurrVal = ''; break;
			case 13: $CurrVal = str_replace('*',$CurrVal,$Loc->OpePrm[$i]); break;
			case 14: $CurrVal = self::f_Loc_AttBoolean($CurrVal, $Loc->PrmLst['atttrue'], $Loc->AttName); break;
			case 15: $CurrVal = ($Loc->OpeUtf8) ? mb_convert_case($CurrVal, MB_CASE_UPPER, 'UTF-8') : strtoupper($CurrVal); break;
			case 16: $CurrVal = ($Loc->OpeUtf8) ? mb_convert_case($CurrVal, MB_CASE_LOWER, 'UTF-8') : strtolower($CurrVal); break;
			case 17: $CurrVal = ucfirst($CurrVal); break;
			case 18: $CurrVal = ($Loc->OpeUtf8) ? mb_convert_case($CurrVal, MB_CASE_TITLE, 'UTF-8') : ucwords(strtolower($CurrVal)); break;
			}
		}
	}

	// String conversion or format
	if ($Loc->ConvMode===1) { // Usual string conversion
		$CurrVal = $this->meth_Misc_ToStr($CurrVal);
		if ($Loc->ConvStr) $this->meth_Conv_Str($CurrVal,$Loc->ConvBr);
	} elseif ($Loc->ConvMode===0) { // Format
		$CurrVal = $this->meth_Misc_Format($CurrVal,$Loc->PrmLst);
	} elseif ($Loc->ConvMode===2) { // Special string conversion
		$CurrVal = $this->meth_Misc_ToStr($CurrVal);
		if ($Loc->ConvStr) $this->meth_Conv_Str($CurrVal,$Loc->ConvBr);
		if ($Loc->ConvEsc) $CurrVal = str_replace('\'','\'\'',$CurrVal);
		if ($Loc->ConvWS) {
			$check = '  ';
			$nbsp = '&nbsp;';
			do {
				$pos = strpos($CurrVal,$check);
				if ($pos!==false) $CurrVal = substr_replace($CurrVal,$nbsp,$pos,1);
			} while ($pos!==false);
		}
		if ($Loc->ConvJS) {
			$CurrVal = addslashes($CurrVal); // apply to ('), ("), (\) and (null)
			$CurrVal = str_replace(array("\n","\r","\t"),array('\n','\r','\t'),$CurrVal);
		}
		if ($Loc->ConvUrl) $CurrVal = urlencode($CurrVal);
		if ($Loc->ConvUtf8) $CurrVal = utf8_encode($CurrVal);
	}

	// if/then/else process, there may be several if/then
	if ($Loc->PrmIfNbr) {
		$z = false;
		$i = 1;
		while ($i!==false) {
			if ($Loc->PrmIfVar[$i]) $Loc->PrmIfVar[$i] = $this->meth_Merge_AutoVar($Loc->PrmIf[$i],true);
			$x = str_replace($this->_ChrVal,$CurrVal,$Loc->PrmIf[$i]);
			if ($this->f_Misc_CheckCondition($x)) {
				if (isset($Loc->PrmThen[$i])) {
					if ($Loc->PrmThenVar[$i]) $Loc->PrmThenVar[$i] = $this->meth_Merge_AutoVar($Loc->PrmThen[$i],true);
					$z = $Loc->PrmThen[$i];
				}
				$i = false;
			} else {
				$i++;
				if ($i>$Loc->PrmIfNbr) {
					if (isset($Loc->PrmLst['else'])) {
						if ($Loc->PrmElseVar) $Loc->PrmElseVar = $this->meth_Merge_AutoVar($Loc->PrmLst['else'],true);
						$z =$Loc->PrmLst['else'];
					}
					$i = false;
				}
			}
		}
		if ($z!==false) {
			if ($ConvProtect) {
				$CurrVal = str_replace($this->_ChrOpen,$this->_ChrProtect,$CurrVal); // TBS protection
				$ConvProtect = false;
			}
			$CurrVal = str_replace($this->_ChrVal,$CurrVal,$z);
		}
	}

	if (isset($Loc->PrmLst['file'])) {
		$x = $Loc->PrmLst['file'];
		if ($x===true) $x = $CurrVal;
		$this->meth_Merge_AutoVar($x,false);
		$x = trim(str_replace($this->_ChrVal,$CurrVal,$x));
		$CurrVal = '';
		if ($x!=='') {
			if ($this->f_Misc_GetFile($CurrVal, $x, $this->_LastFile, $this->IncludePath)) {
				$this->meth_Locator_PartAndRename($CurrVal, $Loc->PrmLst);
			} else {
				if (!isset($Loc->PrmLst['noerr'])) $this->meth_Misc_Alert($Loc,'the file \''.$x.'\' given by parameter file is not found or not readable.',true);
			}
			$ConvProtect = false;
		}
	}

	if (isset($Loc->PrmLst['script'])) {// Include external PHP script
		$x = $Loc->PrmLst['script'];
		if ($x===true) $x = $CurrVal;
		$this->meth_Merge_AutoVar($x,false);
		$x = trim(str_replace($this->_ChrVal,$CurrVal,$x));
		if ($x!=='') {
			$this->_Subscript = $x;
			$this->CurrPrm = &$Loc->PrmLst;
			$sub = isset($Loc->PrmLst['subtpl']);
			if ($sub) $this->meth_Misc_ChangeMode(true,$Loc,$CurrVal);
			if ($this->meth_Misc_RunSubscript($CurrVal,$Loc->PrmLst)===false) {
				if (!isset($Loc->PrmLst['noerr'])) $this->meth_Misc_Alert($Loc,'the file \''.$x.'\' given by parameter script is not found or not readable.',true);
			}
			if ($sub) $this->meth_Misc_ChangeMode(false,$Loc,$CurrVal);
			$this->meth_Locator_PartAndRename($CurrVal, $Loc->PrmLst);
			unset($this->CurrPrm);
			$ConvProtect = false;
		}
	}

	if (isset($Loc->PrmLst['att'])) {
		$this->f_Xml_AttFind($Txt,$Loc,true,$this->AttDelim);
		if (isset($Loc->PrmLst['atttrue'])) {
			$CurrVal = self::f_Loc_AttBoolean($CurrVal, $Loc->PrmLst['atttrue'], $Loc->AttName);
			$Loc->PrmLst['magnet'] = '#';
		}
	}

	// Case when it's an empty string
	if ($CurrVal==='') {

		if ($Loc->MagnetId===false) {
			if (isset($Loc->PrmLst['.'])) {
				$Loc->MagnetId = -1;
			} elseif (isset($Loc->PrmLst['ifempty'])) {
				$Loc->MagnetId = -2;
			} elseif (isset($Loc->PrmLst['magnet'])) {
				$Loc->MagnetId = 1;
				$Loc->PosBeg0 = $Loc->PosBeg;
				$Loc->PosEnd0 = $Loc->PosEnd;
				if ($Loc->PrmLst['magnet']==='#') {
					if (!isset($Loc->AttBeg)) {
						$Loc->PrmLst['att'] = '.';
						$this->f_Xml_AttFind($Txt,$Loc,true,$this->AttDelim);
					}
					if (isset($Loc->AttBeg)) {
						$Loc->MagnetId = -3;
					} else {
						$this->meth_Misc_Alert($Loc,'parameter \'magnet=#\' cannot be processed because the corresponding attribute is not found.',true);
					}
				} elseif (isset($Loc->PrmLst['mtype'])) {
					switch ($Loc->PrmLst['mtype']) {
					case 'm+m': $Loc->MagnetId = 2; break;
					case 'm*': $Loc->MagnetId = 3; break;
					case '*m': $Loc->MagnetId = 4; break;
					}
				}
			} elseif (isset($Loc->PrmLst['attadd'])) {
				// In order to delete extra space
				$Loc->PosBeg0 = $Loc->PosBeg;
				$Loc->PosEnd0 = $Loc->PosEnd;
				$Loc->MagnetId = 5;
			} else {
				$Loc->MagnetId = 0;
			}
		}

		switch ($Loc->MagnetId) {
		case 0: break;
		case -1: $CurrVal = '&nbsp;'; break; // Enables to avoid null cells in HTML tables
		case -2: $CurrVal = $Loc->PrmLst['ifempty']; break;
		case -3: $Loc->Enlarged = true; $Loc->PosBeg = $Loc->AttBegM; $Loc->PosEnd = $Loc->AttEnd; break;
		case 1:
			$Loc->Enlarged = true;
			$this->f_Loc_EnlargeToTag($Txt,$Loc,$Loc->PrmLst['magnet'],false);
			break;
		case 2:
			$Loc->Enlarged = true;
			$CurrVal = $this->f_Loc_EnlargeToTag($Txt,$Loc,$Loc->PrmLst['magnet'],true);
			break;
		case 3:
			$Loc->Enlarged = true;
			$Loc2 = $this->f_Xml_FindTag($Txt,$Loc->PrmLst['magnet'],true,$Loc->PosBeg,false,false,false);
			if ($Loc2!==false) {
				$Loc->PosBeg = $Loc2->PosBeg;
				if ($Loc->PosEnd<$Loc2->PosEnd) $Loc->PosEnd = $Loc2->PosEnd;
			}
			break;
		case 4:
			$Loc->Enlarged = true;
			$Loc2 = $this->f_Xml_FindTag($Txt,$Loc->PrmLst['magnet'],true,$Loc->PosBeg,true,false,false);
			if ($Loc2!==false) $Loc->PosEnd = $Loc2->PosEnd;
			break;
		case 5:
			$Loc->Enlarged = true;
			if (substr($Txt,$Loc->PosBeg-1,1)==' ') $Loc->PosBeg--;
			break;
		}
		$NewEnd = $Loc->PosBeg; // Useful when mtype='m+m'
	} else {

		if ($ConvProtect) $CurrVal = str_replace($this->_ChrOpen,$this->_ChrProtect,$CurrVal); // TBS protection
		$NewEnd = $Loc->PosBeg + strlen($CurrVal);

	}

	$Txt = substr_replace($Txt,$CurrVal,$Loc->PosBeg,$Loc->PosEnd-$Loc->PosBeg+1);
	return $NewEnd; // Return the new end position of the field

}

function meth_Locator_FindBlockNext(&$Txt,$BlockName,$PosBeg,$ChrSub,$Mode,&$P1,&$FieldBefore) {
// Return the first block locator just after the PosBeg position
// Mode = 1 : Merge_Auto => doesn't save $Loc->BlockSrc, save the bounds of TBS Def tags instead, return also fields
// Mode = 2 : FindBlockLst or GetBlockSource => save $Loc->BlockSrc without TBS Def tags
// Mode = 3 : GetBlockSource => save $Loc->BlockSrc with TBS Def tags

	$SearchDef = true;
	$FirstField = false;
	// Search for the first tag with parameter "block"
	while ($SearchDef && ($Loc = $this->meth_Locator_FindTbs($Txt,$BlockName,$PosBeg,$ChrSub))) {
		if (isset($Loc->PrmLst['block'])) {
			if (isset($Loc->PrmLst['p1'])) {
				if ($P1) return false;
				$P1 = true;
			}
			$Block = $Loc->PrmLst['block'];
			$SearchDef = false;
		} elseif ($Mode===1) {
			return $Loc;
		} elseif ($FirstField===false) {
			$FirstField = $Loc;
		}
		$PosBeg = $Loc->PosEnd;
	}

	if ($SearchDef) {
		if ($FirstField!==false) $FieldBefore = true;
		return false;
	}

	$Loc->PosDefBeg = -1;

	if ($Block==='begin') { // Block definied using begin/end

		if (($FirstField!==false) && ($FirstField->PosEnd<$Loc->PosBeg)) $FieldBefore = true;

		$Opened = 1;
		while ($Loc2 = $this->meth_Locator_FindTbs($Txt,$BlockName,$PosBeg,$ChrSub)) {
			if (isset($Loc2->PrmLst['block'])) {
				switch ($Loc2->PrmLst['block']) {
				case 'end':   $Opened--; break;
				case 'begin': $Opened++; break;
				}
				if ($Opened==0) {
					if ($Mode===1) {
						$Loc->PosBeg2 = $Loc2->PosBeg;
						$Loc->PosEnd2 = $Loc2->PosEnd;
					} else {
						if ($Mode===2) {
							$Loc->BlockSrc = substr($Txt,$Loc->PosEnd+1,$Loc2->PosBeg-$Loc->PosEnd-1);
						} else {
							$Loc->BlockSrc = substr($Txt,$Loc->PosBeg,$Loc2->PosEnd-$Loc->PosBeg+1);
						}
						$Loc->PosEnd = $Loc2->PosEnd;
					}
					$Loc->BlockFound = true;
					return $Loc;
				}
			}
			$PosBeg = $Loc2->PosEnd;
		}

		return $this->meth_Misc_Alert($Loc,'a least one tag with parameter \'block=end\' is missing.',false,'in block\'s definition');

	}

	if ($Mode===1) {
		$Loc->PosBeg2 = false;
	} else {
		$beg = $Loc->PosBeg;
		$end = $Loc->PosEnd;
		if ($this->f_Loc_EnlargeToTag($Txt,$Loc,$Block,false)===false) return $this->meth_Misc_Alert($Loc,'at least one tag corresponding to '.$Loc->PrmLst['block'].' is not found. Check opening tags, closing tags and embedding levels.',false,'in block\'s definition');
		if ($Loc->SubOk || ($Mode===3)) {
			$Loc->BlockSrc = substr($Txt,$Loc->PosBeg,$Loc->PosEnd-$Loc->PosBeg+1);
			$Loc->PosDefBeg = $beg - $Loc->PosBeg;
			$Loc->PosDefEnd = $end - $Loc->PosBeg;
		} else {
			$Loc->BlockSrc = substr($Txt,$Loc->PosBeg,$beg-$Loc->PosBeg).substr($Txt,$end+1,$Loc->PosEnd-$end);
		}
	}

	$Loc->BlockFound = true;
	if (($FirstField!==false) && ($FirstField->PosEnd<$Loc->PosBeg)) $FieldBefore = true;
	return $Loc; // methods return by ref by default

}

function meth_Locator_PartAndRename(&$CurrVal, &$PrmLst) {

	// Store part
	if (isset($PrmLst['store'])) {
		$storename = (isset($PrmLst['storename'])) ? $PrmLst['storename'] : 'default';
		if (!isset($this->TplStore[$storename])) $this->TplStore[$storename] = '';
		$this->TplStore[$storename] .= $this->f_Xml_GetPart($CurrVal, $PrmLst['store'], false);
	}

	// Get part
	if (isset($PrmLst['getpart'])) {
		$part = $PrmLst['getpart'];
	} elseif (isset($PrmLst['getbody'])) {
		$part = $PrmLst['getbody'];
	} else {
		$part = false;
	}
	if ($part!=false) {
		$CurrVal = $this->f_Xml_GetPart($CurrVal, $part, true);
	}

	// Rename or delete TBS tags names
	if (isset($PrmLst['rename'])) {
	
		$Replace = $PrmLst['rename'];

		if (is_string($Replace)) $Replace = explode(',',$Replace);
		foreach ($Replace as $x) {
			if (is_string($x)) $x = explode('=', $x);
			if (count($x)==2) {
				$old = trim($x[0]);
				$new = trim($x[1]);
				if ($old!=='') {
					if ($new==='') {
						$q = false;
						$s = 'clear';
						$this->meth_Merge_Block($CurrVal, $old, $s, $q, false, false, false);
					} else {
						$old = $this->_ChrOpen.$old;
						$old = array($old.'.', $old.' ', $old.';', $old.$this->_ChrClose);
						$new = $this->_ChrOpen.$new;
						$new = array($new.'.', $new.' ', $new.';', $new.$this->_ChrClose);
						$CurrVal = str_replace($old,$new,$CurrVal);
					}
				}
			}
		} 

	}

}

function meth_Locator_FindBlockLst(&$Txt,$BlockName,$Pos,$SpePrm) {
// Return a locator object covering all block definitions, even if there is no block definition found.

	$LocR = new clsTbsLocator;
	$LocR->P1 = false;
	$LocR->FieldOutside = false;
	$LocR->FOStop = false;
	$LocR->BDefLst = array();

	$LocR->NoData = false;
	$LocR->Special = false;
	$LocR->HeaderFound = false;
	$LocR->FooterFound = false;
	$LocR->SerialEmpty = false;
	$LocR->GrpBreak = false; // Only for plug-ins

	$LocR->WhenFound = false;
	$LocR->WhenDefault = false;

	$LocR->SectionNbr = 0;       // Normal sections
	$LocR->SectionLst = array(); // 1 to SectionNbr

	$BDef = false;
	$ParentLst = array();
	$Pid = 0;

	do {

		if ($BlockName==='') {
			$Loc = false;
		} else {
			$Loc = $this->meth_Locator_FindBlockNext($Txt,$BlockName,$Pos,'.',2,$LocR->P1,$LocR->FieldOutside);
		}

		if ($Loc===false) {

			if ($Pid>0) { // parentgrp mode => disconnect $Txt from the source
				$Parent = &$ParentLst[$Pid];
				$Src = $Txt;
				$Txt = &$Parent->Txt;
				if ($LocR->BlockFound) {
					// Redefine the Header block
					$Parent->Src = substr($Src,0,$LocR->PosBeg);
					// Add a Footer block
					$BDef = &$this->meth_Locator_SectionNewBDef($LocR,$BlockName,substr($Src,$LocR->PosEnd+1),$Parent->Prm,true);
					$this->meth_Locator_SectionAddGrp($LocR,$BlockName,$BDef,'F',$Parent->Fld,'parentgrp');
				}
				// Now go down to previous level
				$Pos = $Parent->Pos;
				$LocR->PosBeg = $Parent->Beg;
				$LocR->PosEnd = $Parent->End;
				$LocR->BlockFound = true;
				unset($Parent);
				unset($ParentLst[$Pid]);
				$Pid--;
				$Loc = true;
			}

		} else {

			$Pos = $Loc->PosEnd;

			// Define the block limits
			if ($LocR->BlockFound) {
				if ( $LocR->PosBeg > $Loc->PosBeg ) $LocR->PosBeg = $Loc->PosBeg;
				if ( $LocR->PosEnd < $Loc->PosEnd ) $LocR->PosEnd = $Loc->PosEnd;
			} else {
				$LocR->BlockFound = true;
				$LocR->PosBeg = $Loc->PosBeg;
				$LocR->PosEnd = $Loc->PosEnd;
			}

			// Merge block parameters
			if (count($Loc->PrmLst)>0) $LocR->PrmLst = array_merge($LocR->PrmLst,$Loc->PrmLst);

			// Force dynamic parameter to be cachable
			if ($Loc->PosDefBeg>=0) {
				$dynprm = array('when','headergrp','footergrp','parentgrp');
				foreach($dynprm as $dp) {
					$n = 0;
					if ((isset($Loc->PrmLst[$dp])) && (strpos($Loc->PrmLst[$dp],$this->_ChrOpen.$BlockName)!==false)) {
						$n++;
						if ($n==1) {
							$len = $Loc->PosDefEnd - $Loc->PosDefBeg + 1;
							$x = substr($Loc->BlockSrc,$Loc->PosDefBeg,$len);
						}
						$x = str_replace($Loc->PrmLst[$dp],'',$x);
					}
					if ($n>0) $Loc->BlockSrc = substr_replace($Loc->BlockSrc,$x,$Loc->PosDefBeg,$len);
				}
			}
			// Save the block and cache its tags
			$IsParentGrp = isset($Loc->PrmLst['parentgrp']);
			$BDef = &$this->meth_Locator_SectionNewBDef($LocR,$BlockName,$Loc->BlockSrc,$Loc->PrmLst,!$IsParentGrp);

			// Add the text in the list of blocks
			if (isset($Loc->PrmLst['nodata'])) { // Nodata section
				$LocR->NoData = &$BDef;
			} elseif (($SpePrm!==false) && isset($Loc->PrmLst[$SpePrm])) { // Special section (used for navigation bar)
				$LocR->Special = &$BDef;
			} elseif (isset($Loc->PrmLst['when'])) {
				if ($LocR->WhenFound===false) {
					$LocR->WhenFound = true;
					$LocR->WhenSeveral = false;
					$LocR->WhenNbr = 0;
					$LocR->WhenLst = array();
				}
				$this->meth_Merge_AutoVar($Loc->PrmLst['when'],false);
				$BDef->WhenCond = &$this->meth_Locator_SectionNewBDef($LocR,$BlockName,$Loc->PrmLst['when'],array(),true);
				$BDef->WhenBeforeNS = ($LocR->SectionNbr===0);
				$i = ++$LocR->WhenNbr;
				$LocR->WhenLst[$i] = &$BDef;
				if (isset($Loc->PrmLst['several'])) $LocR->WhenSeveral = true;
			} elseif (isset($Loc->PrmLst['default'])) {
				$LocR->WhenDefault = &$BDef;
				$LocR->WhenDefaultBeforeNS = ($LocR->SectionNbr===0);
			} elseif (isset($Loc->PrmLst['headergrp'])) {
				$this->meth_Locator_SectionAddGrp($LocR,$BlockName,$BDef,'H',$Loc->PrmLst['headergrp'],'headergrp');
			} elseif (isset($Loc->PrmLst['footergrp'])) {
				$this->meth_Locator_SectionAddGrp($LocR,$BlockName,$BDef,'F',$Loc->PrmLst['footergrp'],'footergrp');
			} elseif (isset($Loc->PrmLst['splittergrp'])) {
				$this->meth_Locator_SectionAddGrp($LocR,$BlockName,$BDef,'S',$Loc->PrmLst['splittergrp'],'splittergrp');
			} elseif ($IsParentGrp) {
				$this->meth_Locator_SectionAddGrp($LocR,$BlockName,$BDef,'H',$Loc->PrmLst['parentgrp'],'parentgrp');
				$BDef->Fld = $Loc->PrmLst['parentgrp'];
				$BDef->Txt = &$Txt;
				$BDef->Pos = $Pos;
				$BDef->Beg = $LocR->PosBeg;
				$BDef->End = $LocR->PosEnd;
				$Pid++;
				$ParentLst[$Pid] = &$BDef;
				$Txt = &$BDef->Src;
				$Pos = $Loc->PosDefBeg + 1;
				$LocR->BlockFound = false;
				$LocR->PosBeg = false;
				$LocR->PosEnd = false;
			} elseif (isset($Loc->PrmLst['serial'])) {
				// Section	with serial subsections
				$SrSrc = &$BDef->Src;
				// Search the empty item
				if ($LocR->SerialEmpty===false) {
					$SrName = $BlockName.'_0';
					$x = false;
					$SrLoc = $this->meth_Locator_FindBlockNext($SrSrc,$SrName,0,'.',2,$x,$x);
					if ($SrLoc!==false) {
						$LocR->SerialEmpty = $SrLoc->BlockSrc;
						$SrSrc = substr_replace($SrSrc,'',$SrLoc->PosBeg,$SrLoc->PosEnd-$SrLoc->PosBeg+1);
					}
				}
				$SrName = $BlockName.'_1';
				$x = false;
				$SrLoc = $this->meth_Locator_FindBlockNext($SrSrc,$SrName,0,'.',2,$x,$x);
				if ($SrLoc!==false) {
					$SrId = 1;
					do {
						// Save previous subsection
						$SrBDef = &$this->meth_Locator_SectionNewBDef($LocR,$SrName,$SrLoc->BlockSrc,$SrLoc->PrmLst,true);
						$SrBDef->SrBeg = $SrLoc->PosBeg;
						$SrBDef->SrLen = $SrLoc->PosEnd - $SrLoc->PosBeg + 1;
						$SrBDef->SrTxt = false;
						$BDef->SrBDefLst[$SrId] = &$SrBDef;
						// Put in order
						$BDef->SrBDefOrdered[$SrId] = &$SrBDef;
						$i = $SrId;
						while (($i>1) && ($SrBDef->SrBeg<$BDef->SrBDefOrdered[$SrId-1]->SrBeg)) {
							$BDef->SrBDefOrdered[$i] = &$BDef->SrBDefOrdered[$i-1];
							$BDef->SrBDefOrdered[$i-1] = &$SrBDef;
							$i--;
						}
						// Search next subsection
						$SrId++;
						$SrName = $BlockName.'_'.$SrId;
						$x = false;
						$SrLoc = $this->meth_Locator_FindBlockNext($SrSrc,$SrName,0,'.',2,$x,$x);
					} while ($SrLoc!==false);
					$BDef->SrBDefNbr = $SrId-1;
					$BDef->IsSerial = true;
					$i = ++$LocR->SectionNbr;
					$LocR->SectionLst[$i] = &$BDef;
				}
			} elseif (isset($Loc->PrmLst['parallel'])) {
				$BlockLst = $this->meth_Locator_FindParallel($Txt, $Loc->PosBeg, $Loc->PosEnd, $Loc->PrmLst['parallel']);
				if ($BlockLst) {
					// Store BDefs
					foreach ($BlockLst as $i => $Blk) {
						if ($Blk['IsRef']) {
							$PrBDef = &$BDef;
						} else {
							$PrBDef = &$this->meth_Locator_SectionNewBDef($LocR,$BlockName,$Blk['Src'],array(),true);
						}
						$PrBDef->PosBeg = $Blk['PosBeg'];
						$PrBDef->PosEnd = $Blk['PosEnd'];
						$i = ++$LocR->SectionNbr;
						$LocR->SectionLst[$i] = &$PrBDef;
					}
					$LocR->PosBeg = $BlockLst[0]['PosBeg'];
					$LocR->PosEnd = $BlockLst[$LocR->SectionNbr-1]['PosEnd'];
				}
			} else {
				// Normal section
				$i = ++$LocR->SectionNbr;
				$LocR->SectionLst[$i] = &$BDef;
			}

		}

	} while ($Loc!==false);

	if ($LocR->WhenFound && ($LocR->SectionNbr===0)) {
		// Add a blank section if When is used without a normal section
		$BDef = &$this->meth_Locator_SectionNewBDef($LocR,$BlockName,'',array(),false);
		$LocR->SectionNbr = 1;
		$LocR->SectionLst[1] = &$BDef;
	}

	return $LocR; // methods return by ref by default

}

function meth_Locator_FindParallel(&$Txt, $ZoneBeg, $ZoneEnd, $ConfId) {

	// Define configurations
	global $_TBS_ParallelLst;

	if ( ($ConfId=='tbs:table')  && (!isset($_TBS_ParallelLst['tbs:table'])) ) {
		$_TBS_ParallelLst['tbs:table'] = array(
			'parent' => 'table',
			'ignore' => array('!--', 'caption', 'thead', 'tbody', 'tfoot'),
			'cols' => array(),
			'rows' => array('tr', 'colgroup'),
			'cells' => array('td'=>'colspan', 'th'=>'colspan', 'col'=>'span'),
		);
	}

	if (!isset($_TBS_ParallelLst[$ConfId])) return $this->meth_Misc_Alert("Parallel", "The configuration '$ConfId' is not found.");

	$conf = $_TBS_ParallelLst[$ConfId];

	$Parent = $conf['parent'];

	// Search parent bounds
	$par_o = self::f_Xml_FindTag($Txt,$Parent,true ,$ZoneBeg,false,1,false);
	if ($par_o===false) return $this->meth_Misc_Alert("Parallel", "The opening tag '$Parent' is not found.");

	$par_c = self::f_Xml_FindTag($Txt,$Parent,false,$ZoneBeg,true,-1,false);
	if ($par_c===false) return $this->meth_Misc_Alert("Parallel", "The closing tag '$Parent' is not found.");

	$SrcPOffset = $par_o->PosEnd + 1;
	$SrcP = substr($Txt, $SrcPOffset, $par_c->PosBeg - $SrcPOffset);

	// temporary variables
	$tagR = '';
	$tagC = '';
	$z = '';
	$pRO  = false;
	$pROe = false;
	$pCO  = false;
	$pCOe = false;
	$p = false;
	$Loc = new clsTbsLocator;

	$Rows  = array();
	$RowIdx = 0;
	$RefRow = false;
	$RefCellB= false;
	$RefCellE = false;
	
	$RowType = array();

	// Loop on entities inside the parent entity
	$PosR = 0;

	$mode_column = true;
	$Cells = array();
	$ColNum = 1;
	$IsRef = false;
	
	// Search for the next Row Opening tag
	while (self::f_Xml_GetNextEntityName($SrcP, $PosR, $tagR, $pRO, $p)) {

		$pROe = strpos($SrcP, '>', $p) + 1;
		$singleR = ($SrcP[$pROe-2] === '/');

		// If the tag is not a closing, a self-closing and has a name
		if ($tagR!=='') {

			if (in_array($tagR, $conf['ignore'])) {
				// This tag must be ignored
				$PosR = $p;
			} elseif (isset($conf['cols'][$tagR])) {
				// Column definition that must be merged as a cell
				if ($mode_column === false)  return $this->meth_Misc_Alert("Parallel", "There is a column definition ($tagR) after a row (".$Rows[$RowIdx-1]['tag'].").");
				if (isset($RowType['_column'])) {
					$RowType['_column']++;
				} else {
					$RowType['_column'] = 1;
				}
				$att = $conf['cols'][$tagR];
				$this->meth_Locator_FindParallelCol($SrcP, $PosR, $tagR, $pRO, $p, $SrcPOffset, $RowIdx, $ZoneBeg, $ZoneEnd, $att, $Loc, $Cells, $ColNum, $IsRef, $RefCellB, $RefCellE, $RefRow);

			} elseif (!$singleR) {

				// Search the Row Closing tag
				$locRE = self::f_Xml_FindTag($SrcP, $tagR, false, $pROe, true, -1, false);
				if ($locRE===false) return $this->meth_Misc_Alert("Parallel", "The row closing tag is not found. (tagR=$tagR, p=$p, pROe=$pROe)");

				// Inner source
				$SrcR = substr($SrcP, $pROe, $locRE->PosBeg - $pROe);
				$SrcROffset = $SrcPOffset + $pROe;

				if (in_array($tagR, $conf['rows'])) {

					if ( $mode_column && isset($RowType['_column']) ) {
						$Rows[$RowIdx] = array('tag'=>'_column', 'cells' => $Cells, 'isref' => $IsRef, 'count' => $RowType['_column']);
						$RowIdx++;
					}

					$mode_column = false;

					if (isset($RowType[$tagR])) {
						$RowType[$tagR]++;
					} else {
						$RowType[$tagR] = 1;
					}

					// Now we've got the row entity, we search for cell entities
					$Cells = array();
					$ColNum = 1;
					$PosC = 0;
					$IsRef = false;

					// Loop on Cell Opening tags
					while (self::f_Xml_GetNextEntityName($SrcR, $PosC, $tagC, $pCO, $p)) {
						if (isset($conf['cells'][$tagC]) ) {
							$att = $conf['cells'][$tagC];
							$this->meth_Locator_FindParallelCol($SrcR, $PosC, $tagC, $pCO, $p, $SrcROffset, $RowIdx, $ZoneBeg, $ZoneEnd, $att, $Loc, $Cells, $ColNum, $IsRef, $RefCellB, $RefCellE, $RefRow);
						} else {
							$PosC = $p;
						}
					}

					$Rows[$RowIdx] = array('tag'=>$tagR, 'cells' => $Cells, 'isref' => $IsRef, 'count' => $RowType[$tagR]);
					$RowIdx++;

				}

				$PosR = $locRE->PosEnd; 

			} else {
				$PosR = $pROe;
			}
		} else {
			$PosR = $pROe;
		}
	}

	//return $Rows;

	$Blocks = array();
	$rMax = count($Rows) -1;
	foreach ($Rows as $r=>$Row) {
		$Cells = $Row['cells'];
		if (isset($Cells[$RefCellB]) && $Cells[$RefCellB]['IsBegin']) {
			if ( isset($Cells[$RefCellE]) &&  $Cells[$RefCellE]['IsEnd'] ) {
				$PosBeg = $Cells[$RefCellB]['PosBeg'];
				$PosEnd = $Cells[$RefCellE]['PosEnd'];
				$Blocks[$r] = array(
					'PosBeg' => $PosBeg,
					'PosEnd' => $PosEnd,
					'IsRef'  => $Row['isref'],
					'Src' => substr($Txt, $PosBeg, $PosEnd - $PosBeg + 1),
				);
			} else {
				return $this->meth_Misc_Alert("Parallel", "At row ".$Row['count']." having entity [".$Row['tag']."], the column $RefCellE is missing or is not the last in a set of spanned columns. (The block is defined from column $RefCellB to $RefCellE)");
			}
		} else {
			return $this->meth_Misc_Alert("Parallel", "At row ".$Row['count']." having entity [".$Row['tag']."],the column $RefCellB is missing or is not the first in a set of spanned columns. (The block is defined from column $RefCellB to $RefCellE)");
		}
	}

	return $Blocks;

}

function meth_Locator_FindParallelCol($SrcR, &$PosC, $tagC, $pCO, $p, $SrcROffset, $RowIdx, $ZoneBeg, $ZoneEnd, &$att, &$Loc, &$Cells, &$ColNum, &$IsRef, &$RefCellB, &$RefCellE, &$RefRow) {

	$pCOe = false;

	// Read parameters
	$Loc->PrmLst = array();
	self::f_Loc_PrmRead($SrcR,$p,true,'\'"','<','>',$Loc,$pCOe,true);

	$singleC = ($SrcR[$pCOe-1] === '/');
	if ($singleC) {
		$pCEe = $pCOe;
	} else {
		// Find the Cell Closing tag
		$locCE = self::f_Xml_FindTag($SrcR, $tagC, false, $pCOe, true, -1, false);
		if ($locCE===false) return $this->meth_Misc_Alert("Parallel", "The cell closing tag is not found. (pCOe=$pCOe)");
		$pCEe = $locCE->PosEnd;
	}
	
	// Check the cell of reference
	$Width = (isset($Loc->PrmLst[$att])) ? intval($Loc->PrmLst[$att]) : 1;
	$ColNumE = $ColNum + $Width -1; // Ending Cell
	$PosBeg = $SrcROffset + $pCO;
	$PosEnd = $SrcROffset + $pCEe;
	$OnZone = false;
	if ( ($PosBeg <= $ZoneBeg) && ($ZoneBeg <= $PosEnd) && ($RefRow===false) ) {
		$RefRow = $RowIdx;
		$RefCellB = $ColNum;
		$OnZone = true;
		$IsRef = true;
	}
	if ( ($PosBeg <= $ZoneEnd) && ($ZoneEnd <= $PosEnd) ) {
		$RefCellE = $ColNum;
		$OnZone = true;
	}
	
	// Save info
	$Cell = array(
		//'_tagR' => $tagR, '_tagC' => $tagC, '_att' => $att, '_OnZone' => $OnZone, '_PrmLst' => $Loc->PrmLst, '_Offset' => $SrcROffset, '_Src' => substr($SrcR, $pCO, $locCE->PosEnd - $pCO + 1),
		'PosBeg' => $PosBeg,
		'PosEnd' => $PosEnd,
		'ColNum' => $ColNum,
		'Width' => $Width,
		'IsBegin' => true,
		'IsEnd' => false,
	);
	$Cells[$ColNum] = $Cell;
	
	// add a virtual column to say if its a ending
	if (!isset($Cells[$ColNumE])) $Cells[$ColNumE] = array('IsBegin' => false);
	
	$Cells[$ColNumE]['IsEnd'] = true;
	$Cells[$ColNumE]['PosEnd'] = $Cells[$ColNum]['PosEnd'];
	
	$PosC = $pCEe;
	$ColNum += $Width;

}

function meth_Merge_Block(&$Txt,$BlockLst,&$SrcId,&$Query,$SpePrm,$SpeRecNum,$QryPrms=false) {

	$BlockSave = $this->_CurrBlock;
	$this->_CurrBlock = $BlockLst;

	// Get source type and info
	$Src = new clsTbsDataSource;
	if (!$Src->DataPrepare($SrcId,$this)) {
		$this->_CurrBlock = $BlockSave;
		return 0;
	}

	if (is_string($BlockLst)) $BlockLst = explode(',', $BlockLst);
	$BlockNbr = count($BlockLst);
	$BlockId = 0;
	$WasP1 = false;
	$NbrRecTot = 0;
	$QueryZ = &$Query;
	$ReturnData = false;

	while ($BlockId<$BlockNbr) {

		$RecSpe = 0;  // Row with a special block's definition (used for the navigation bar)
		$QueryOk = true;
		$this->_CurrBlock = trim($BlockLst[$BlockId]);
		if ($this->_CurrBlock==='*') {
			$ReturnData = true;
			if ($Src->RecSaved===false) $Src->RecSaving = true;
			$this->_CurrBlock = '';
		}

		// Search the block
		$LocR = $this->meth_Locator_FindBlockLst($Txt,$this->_CurrBlock,0,$SpePrm);

		if ($LocR->BlockFound) {

			if ($LocR->Special!==false) $RecSpe = $SpeRecNum;
			// OnData
			if ($Src->OnDataPrm = isset($LocR->PrmLst['ondata'])) {
				$Src->OnDataPrmRef = $LocR->PrmLst['ondata'];
				if (isset($Src->OnDataPrmDone[$Src->OnDataPrmRef])) {
					$Src->OnDataPrm = false;
				} else {
					$ErrMsg = false;
					if ($this->meth_Misc_UserFctCheck($Src->OnDataPrmRef,'f',$ErrMsg,$ErrMsg,true)) {
						$Src->OnDataOk = true;
					} else {
						$LocR->FullName = $this->_CurrBlock;
						$Src->OnDataPrm = $this->meth_Misc_Alert($LocR,'(parameter ondata) '.$ErrMsg,false,'block');
					}
				}
			}
			// Dynamic query
			if ($LocR->P1) {
				if ( ($LocR->PrmLst['p1']===true) && ((!is_string($Query)) || (strpos($Query,'%p1%')===false)) ) { // p1 with no value is a trick to perform new block with same name
					if ($Src->RecSaved===false) $Src->RecSaving = true;
				} elseif (is_string($Query)) {
					$Src->RecSaved = false;
					unset($QueryZ); $QueryZ = ''.$Query;
					$i = 1;
					do {
						$x = 'p'.$i;
						if (isset($LocR->PrmLst[$x])) {
							$QueryZ = str_replace('%p'.$i.'%',$LocR->PrmLst[$x],$QueryZ);
							$i++;
						} else {
							$i = false;
						}
					} while ($i!==false);
				}
				$WasP1 = true;
			} elseif (($Src->RecSaved===false) && ($BlockNbr-$BlockId>1)) {
				$Src->RecSaving = true;
			}
		} elseif ($WasP1) {
			$QueryOk = false;
			$WasP1 = false;
		}

		// Open the recordset
		if ($QueryOk) {
			if ((!$LocR->BlockFound) && (!$LocR->FieldOutside)) {
				// Special case: return data without any block to merge
				$QueryOk = false;
				if ($ReturnData && (!$Src->RecSaved)) {
					if ($Src->DataOpen($QueryZ,$QryPrms)) {
						do {$Src->DataFetch();} while ($Src->CurrRec!==false);
						$Src->DataClose();
					}
				}
			}	else {
				$QueryOk = $Src->DataOpen($QueryZ,$QryPrms);
				if (!$QueryOk) {
					if ($WasP1) {	$WasP1 = false;} else {$LocR->FieldOutside = false;} // prevent from infinit loop
				}
			}
		}

		// Merge sections
		if ($QueryOk) {
			if ($Src->Type===2) { // Special for Text merge
				if ($LocR->BlockFound) {
					$Txt = substr_replace($Txt,$Src->RecSet,$LocR->PosBeg,$LocR->PosEnd-$LocR->PosBeg+1);
					$Src->DataFetch(); // store data, may be needed for multiple blocks
					$Src->RecNum = 1;
					$Src->CurrRec = false;
				} else {
					$Src->DataAlert('can\'t merge the block with a text value because the block definition is not found.');
				}
			} elseif ($LocR->BlockFound===false) {
				$Src->DataFetch(); // Merge first record only
			} elseif (isset($LocR->PrmLst['parallel'])) {
				$this->meth_Merge_BlockParallel($Txt,$LocR,$Src);
			} else {
				$this->meth_Merge_BlockSections($Txt,$LocR,$Src,$RecSpe);
			}
			$Src->DataClose(); // Close the resource
		}

		if (!$WasP1) {
			$NbrRecTot += $Src->RecNum;
			$BlockId++;
		}
		if ($LocR->FieldOutside) $this->meth_Merge_FieldOutside($Txt,$Src->CurrRec,$Src->RecNum,$LocR->FOStop);

	}

	// End of the merge
	unset($LocR);
	$this->_CurrBlock = $BlockSave;
	if ($ReturnData) {
		return $Src->RecSet;
	} else {
		unset($Src);
		return $NbrRecTot;
	}

}

function meth_Merge_BlockParallel(&$Txt,&$LocR,&$Src) {

	// Main loop
	$Src->DataFetch();

	$FirstRec = true;
	
	// Prepare sources
	$BlockRes = array();
	for ($i=1 ; $i<=$LocR->SectionNbr ; $i++) {
		if ($i>1) {
			// Add txt source between the BDefs
			$BlockRes[$i] = substr($Txt, $LocR->SectionLst[$i-1]->PosEnd + 1, $LocR->SectionLst[$i]->PosBeg - $LocR->SectionLst[$i-1]->PosEnd -1); 
		} else {
			$BlockRes[$i] = '';
		}
	}
	
	while($Src->CurrRec!==false) {
		// Merge the current record with all sections
		for ($i=1 ; $i<=$LocR->SectionNbr ; $i++) {
			$SecDef = &$LocR->SectionLst[$i];
			$SecSrc = $this->meth_Merge_SectionNormal($SecDef,$Src);
			$BlockRes[$i] .= $SecSrc;
		}
		// Next row
		$Src->DataFetch();
	}
	
	$BlockRes = implode('', $BlockRes);
	$Txt = substr_replace($Txt,$BlockRes,$LocR->PosBeg,$LocR->PosEnd-$LocR->PosBeg+1);

}

function meth_Merge_BlockSections(&$Txt,&$LocR,&$Src,&$RecSpe) {

	// Initialise
	$SecId = 0;
	$SecOk = ($LocR->SectionNbr>0);
	$SecSrc = '';
	$BlockRes = ''; // The result of the chained merged blocks
	$IsSerial = false;
	$SrId = 0;
	$SrNbr = 0;
	$GrpFound = false;
	if ($LocR->HeaderFound || $LocR->FooterFound) {
		$GrpFound = true;
		$piOMG = false;
		if ($LocR->FooterFound) $Src->PrevRec = (object) null;
	}
	// Plug-ins
	$piOMS = false;
	if ($this->_PlugIns_Ok) {
		if (isset($this->_piBeforeMergeBlock)) {
			$ArgLst = array(&$Txt,&$LocR->PosBeg,&$LocR->PosEnd,$LocR->PrmLst,&$Src,&$LocR);
			$this->meth_Plugin_RunAll($this->_piBeforeMergeBlock,$ArgLst);
		}
		if (isset($this->_piOnMergeSection)) {
			$ArgLst = array(&$BlockRes,&$SecSrc);
			$piOMS = true;
		}
		if ($GrpFound && isset($this->_piOnMergeGroup)) {
			$ArgLst2 = array(0,0,&$Src,&$LocR);
			$piOMG = true;
		}
	}

	// Main loop
	$Src->DataFetch();

	while($Src->CurrRec!==false) {

		// Headers and Footers
		if ($GrpFound) {
			$brk_any = false;
			$brk_src = '';
			if ($LocR->FooterFound) {
				$brk = false;
				for ($i=$LocR->FooterNbr;$i>=1;$i--) {
					$GrpDef = &$LocR->FooterDef[$i];
					$x = $this->meth_Merge_SectionNormal($GrpDef->FDef,$Src);
					if ($Src->RecNum===1) {
						$GrpDef->PrevValue = $x;
						$brk_i = false;
					} else {
						if ($GrpDef->AddLastGrp) {
							$brk_i = &$brk;
						} else {
							unset($brk_i); $brk_i = false;
						}
						if (!$brk_i) $brk_i = !($GrpDef->PrevValue===$x);
						if ($brk_i) {
							$brk_any = true;
							$ok = true;
							if ($piOMG) {$ArgLst2[0]=&$Src->PrevRec; $ArgLst2[1]=&$GrpDef; $ok = $this->meth_PlugIn_RunAll($this->_piOnMergeGroup,$ArgLst2);}
							if ($ok!==false) $brk_src = $this->meth_Merge_SectionNormal($GrpDef,$Src->PrevRec).$brk_src;
							$GrpDef->PrevValue = $x;
						}
					}
				}
				$Src->PrevRec->CurrRec = $Src->CurrRec;
				$Src->PrevRec->RecNum = $Src->RecNum;
				$Src->PrevRec->RecKey = $Src->RecKey;
			}
			if ($LocR->HeaderFound) {
				$brk = ($Src->RecNum===1);
				for ($i=1;$i<=$LocR->HeaderNbr;$i++) {
					$GrpDef = &$LocR->HeaderDef[$i];
					$x = $this->meth_Merge_SectionNormal($GrpDef->FDef,$Src);
					if (!$brk) $brk = !($GrpDef->PrevValue===$x);
					if ($brk) {
						$ok = true;
						if ($piOMG) {$ArgLst2[0]=&$Src; $ArgLst2[1]=&$GrpDef; $ok = $this->meth_PlugIn_RunAll($this->_piOnMergeGroup,$ArgLst2);}
						if ($ok!==false) $brk_src .= $this->meth_Merge_SectionNormal($GrpDef,$Src);
						$GrpDef->PrevValue = $x;
					}
				}
				$brk_any = ($brk_any || $brk);
			}
			if ($brk_any) {
				if ($IsSerial) {
					$BlockRes .= $this->meth_Merge_SectionSerial($SecDef,$SrId,$LocR);
					$IsSerial = false;
				}
				$BlockRes .= $brk_src;
			}
		} // end of header and footer

		// Increment Section
		if (($IsSerial===false) && $SecOk) {
			$SecId++;
			if ($SecId>$LocR->SectionNbr) $SecId = 1;
			$SecDef = &$LocR->SectionLst[$SecId];
			$IsSerial = $SecDef->IsSerial;
			if ($IsSerial) {
				$SrId = 0;
				$SrNbr = $SecDef->SrBDefNbr;
			}
		}

		// Serial Mode Activation
		if ($IsSerial) { // Serial Merge
			$SrId++;
			$SrBDef = &$SecDef->SrBDefLst[$SrId];
			$SrBDef->SrTxt = $this->meth_Merge_SectionNormal($SrBDef,$Src);
			if ($SrId>=$SrNbr) {
				$SecSrc = $this->meth_Merge_SectionSerial($SecDef,$SrId,$LocR);
				$BlockRes .= $SecSrc;
				$IsSerial = false;
			}
		} else { // Classic merge
			if ($SecOk) {
				if ($Src->RecNum===$RecSpe) $SecDef = &$LocR->Special;
				$SecSrc = $this->meth_Merge_SectionNormal($SecDef,$Src);
			} else {
				$SecSrc = '';
			}
			if ($LocR->WhenFound) { // With conditional blocks
				$found = false;
				$continue = true;
				$i = 1;
				do {
					$WhenBDef = &$LocR->WhenLst[$i];
					$cond = $this->meth_Merge_SectionNormal($WhenBDef->WhenCond,$Src);
					if ($this->f_Misc_CheckCondition($cond)) {
						$x_when = $this->meth_Merge_SectionNormal($WhenBDef,$Src);
						if ($WhenBDef->WhenBeforeNS) {$SecSrc = $x_when.$SecSrc;} else {$SecSrc = $SecSrc.$x_when;}
						$found = true;
						if ($LocR->WhenSeveral===false) $continue = false;
					}
					$i++;
					if ($i>$LocR->WhenNbr) $continue = false;
				} while ($continue);
				if (($found===false) && ($LocR->WhenDefault!==false)) {
					$x_when = $this->meth_Merge_SectionNormal($LocR->WhenDefault,$Src);
					if ($LocR->WhenDefaultBeforeNS) {$SecSrc = $x_when.$SecSrc;} else {$SecSrc = $SecSrc.$x_when;}
				}
			}
			if ($piOMS) $this->meth_PlugIn_RunAll($this->_piOnMergeSection,$ArgLst);
			$BlockRes .= $SecSrc;
		}

		// Next row
		$Src->DataFetch();

	} //--> while($CurrRec!==false) {

	$SecSrc = '';

	// Serial: merge the extra the sub-blocks
	if ($IsSerial) $SecSrc .= $this->meth_Merge_SectionSerial($SecDef,$SrId,$LocR);

	// Footer
	if ($LocR->FooterFound) {
		if ($Src->RecNum>0) {
			for ($i=1;$i<=$LocR->FooterNbr;$i++) {
				$GrpDef = &$LocR->FooterDef[$i];
				if ($GrpDef->AddLastGrp) {
					$ok = true;
					if ($piOMG) {$ArgLst2[0]=&$Src->PrevRec; $ArgLst2[1]=&$GrpDef; $ok = $this->meth_PlugIn_RunAll($this->_piOnMergeGroup,$ArgLst2);}
					if ($ok!==false) $SecSrc .= $this->meth_Merge_SectionNormal($GrpDef,$Src->PrevRec);
				}
			}
		}
	}

	// NoData
	if ($Src->RecNum===0) {
		if ($LocR->NoData!==false) {
			$SecSrc = $LocR->NoData->Src;
		} elseif(isset($LocR->PrmLst['bmagnet'])) {
			$this->f_Loc_EnlargeToTag($Txt,$LocR,$LocR->PrmLst['bmagnet'],false);
		}
	}

	// Plug-ins
	if ($piOMS && ($SecSrc!=='')) $this->meth_PlugIn_RunAll($this->_piOnMergeSection,$ArgLst);

	$BlockRes .= $SecSrc;

	// Plug-ins
	if ($this->_PlugIns_Ok && isset($ArgLst) && isset($this->_piAfterMergeBlock)) {
		$ArgLst = array(&$BlockRes,&$Src,&$LocR);
		$this->meth_PlugIn_RunAll($this->_piAfterMergeBlock,$ArgLst);
	}

	// Merge the result
	$Txt = substr_replace($Txt,$BlockRes,$LocR->PosBeg,$LocR->PosEnd-$LocR->PosBeg+1);
	if ($LocR->P1) $LocR->FOStop = $LocR->PosBeg + strlen($BlockRes) -1;

}

function meth_Merge_AutoVar(&$Txt,$ConvStr,$Id='var') {
// Merge automatic fields with VarRef

	$Pref = &$this->VarPrefix;
	$PrefL = strlen($Pref);
	$PrefOk = ($PrefL>0);

	if ($ConvStr===false) {
		$Charset = $this->Charset;
		$this->Charset = false;
	}

	// Then we scann all fields in the model
	$x = '';
	$Pos = 0;
	while ($Loc = $this->meth_Locator_FindTbs($Txt,$Id,$Pos,'.')) {
		if ($Loc->SubNbr==0) $Loc->SubLst[0]=''; // In order to force error message
		if ($Loc->SubLst[0]==='') {
			$Pos = $this->meth_Merge_AutoSpe($Txt,$Loc);
		} elseif ($Loc->SubLst[0][0]==='~') {
			if (!isset($ObjOk)) $ObjOk = (is_object($this->ObjectRef) || is_array($this->ObjectRef));
			if ($ObjOk) {
				$Loc->SubLst[0] = substr($Loc->SubLst[0],1);
				$Pos = $this->meth_Locator_Replace($Txt,$Loc,$this->ObjectRef,0);
			} elseif (isset($Loc->PrmLst['noerr'])) {
				$Pos = $this->meth_Locator_Replace($Txt,$Loc,$x,false);
			} else {
				$this->meth_Misc_Alert($Loc,'property ObjectRef is neither an object nor an array. Its type is \''.gettype($this->ObjectRef).'\'.',true);
				$Pos = $Loc->PosEnd + 1;
			}
		} elseif ($PrefOk && (substr($Loc->SubLst[0],0,$PrefL)!==$Pref)) {
			if (isset($Loc->PrmLst['noerr'])) {
				$Pos = $this->meth_Locator_Replace($Txt,$Loc,$x,false);
			} else {
				$this->meth_Misc_Alert($Loc,'does not match the allowed prefix.',true);
				$Pos = $Loc->PosEnd + 1;
			}
		} elseif (isset($this->VarRef[$Loc->SubLst[0]])) {
			$Pos = $this->meth_Locator_Replace($Txt,$Loc,$this->VarRef[$Loc->SubLst[0]],1);
		} else {
			if (isset($Loc->PrmLst['noerr'])) {
				$Pos = $this->meth_Locator_Replace($Txt,$Loc,$x,false);
			} else {
				$Pos = $Loc->PosEnd + 1;
				$msg = (isset($this->VarRef['GLOBALS'])) ? 'VarRef seems refers to $GLOBALS' : 'VarRef seems refers to a custom array of values';
				$this->meth_Misc_Alert($Loc,'the key \''.$Loc->SubLst[0].'\' does not exist or is not set in VarRef. ('.$msg.')',true);
			}
		}
	}

	if ($ConvStr===false) $this->Charset = $Charset;

	return false; // Useful for properties PrmIfVar & PrmThenVar

}

function meth_Merge_AutoSpe(&$Txt,&$Loc) {
// Merge Special Var Fields ([var..*])

	$ErrMsg = false;
	$SubStart = false;
	if (isset($Loc->SubLst[1])) {
		switch ($Loc->SubLst[1]) {
		case 'now': $x = time(); break;
		case 'version': $x = $this->Version; break;
		case 'script_name': $x = basename(((isset($_SERVER)) ? $_SERVER['PHP_SELF'] : $GLOBALS['HTTP_SERVER_VARS']['PHP_SELF'] )); break;
		case 'template_name': $x = $this->_LastFile; break;
		case 'template_date': $x = ''; if ($this->f_Misc_GetFile($x,$this->_LastFile,'',array(),false)) $x = $x['mtime']; break;
		case 'template_path': $x = dirname($this->_LastFile).'/'; break;
		case 'name': $x = 'TinyButStrong'; break;
		case 'logo': $x = '**TinyButStrong**'; break;
		case 'charset': $x = $this->Charset; break;
		case 'error_msg': $this->_ErrMsgName = $Loc->FullName; return $Loc->PosEnd;	break;
		case '': $ErrMsg = 'it doesn\'t have any keyword.'; break;
		case 'tplvars':
			if ($Loc->SubNbr==2) {
				$SubStart = 2;
				$x = implode(',',array_keys($this->TplVars)); // list of all template variables
			} else {
				if (isset($this->TplVars[$Loc->SubLst[2]])) {
					$SubStart = 3;
					$x = &$this->TplVars[$Loc->SubLst[2]];
				} else {
					$ErrMsg = 'property TplVars doesn\'t have any item named \''.$Loc->SubLst[2].'\'.';
				}
			}
			break;
		case 'store':
			if ($Loc->SubNbr==2) {
				$SubStart = 2;
				$x = implode('',$this->TplStore); // concatenation of all stores
			} else {
				if (isset($this->TplStore[$Loc->SubLst[2]])) {
					$SubStart = 3;
					$x = &$this->TplStore[$Loc->SubLst[2]];
				} else {
					$ErrMsg = 'Store named \''.$Loc->SubLst[2].'\' is not defined yet.';
				}
			}
			if (!isset($Loc->PrmLst['strconv'])) {$Loc->PrmLst['strconv'] = 'no'; $Loc->PrmLst['protect'] = 'no';}
			break;
		case 'cst': $x = @constant($Loc->SubLst[2]); break;
		case 'tbs_info':
			$x = 'TinyButStrong version '.$this->Version.' for PHP 5';
			$x .= "\r\nInstalled plug-ins: ".count($this->_PlugIns);
			foreach (array_keys($this->_PlugIns) as $pi) {
				$o = &$this->_PlugIns[$pi];
				$x .= "\r\n- plug-in [".(isset($o->Name) ? $o->Name : $pi ).'] version '.(isset($o->Version) ? $o->Version : '?' );
			}
			break;
		case 'php_info':
			ob_start();
			phpinfo();
			$x = ob_get_contents();
			ob_end_clean();
			$x = self::f_Xml_GetPart($x, '(style)+body', false);
			if (!isset($Loc->PrmLst['strconv'])) {$Loc->PrmLst['strconv'] = 'no'; $Loc->PrmLst['protect'] = 'no';}
			break;
		default:
			$IsSupported = false;
			if (isset($this->_piOnSpecialVar)) {
				$x = '';
				$ArgLst = array(substr($Loc->SubName,1),&$IsSupported ,&$x, &$Loc->PrmLst,&$Txt,&$Loc->PosBeg,&$Loc->PosEnd,&$Loc);
				$this->meth_PlugIn_RunAll($this->_piOnSpecialVar,$ArgLst);
			}
			if (!$IsSupported) $ErrMsg = '\''.$Loc->SubLst[1].'\' is an unsupported keyword.';
		}
	} else {
		$ErrMsg = 'it doesn\'t have any subname.';
	}
	if ($ErrMsg!==false) {
		$this->meth_Misc_Alert($Loc,$ErrMsg);
		$x = '';
	}
	if ($Loc->PosBeg===false) {
		return $Loc->PosEnd;
	} else {
		return $this->meth_Locator_Replace($Txt,$Loc,$x,$SubStart);
	}
}

function meth_Merge_FieldOutside(&$Txt, &$CurrRec, $RecNum, $PosMax) {
	$Pos = 0;
	$SubStart = ($CurrRec===false) ? false : 0;
	do {
		$Loc = $this->meth_Locator_FindTbs($Txt,$this->_CurrBlock,$Pos,'.');
		if ($Loc!==false) {
			if (($PosMax!==false) && ($Loc->PosEnd>$PosMax)) return;
			if ($Loc->SubName==='#') {
				$NewEnd = $this->meth_Locator_Replace($Txt,$Loc,$RecNum,false);
			} else {
				$NewEnd = $this->meth_Locator_Replace($Txt,$Loc,$CurrRec,$SubStart);
			}
			if ($PosMax!==false) $PosMax += $NewEnd - $Loc->PosEnd;
			$Pos = $NewEnd;
		}
	} while ($Loc!==false);
}

function meth_Merge_SectionNormal(&$BDef,&$Src) {

	$Txt = $BDef->Src;
	$LocLst = &$BDef->LocLst;
	$iMax = $BDef->LocNbr;
	$PosMax = strlen($Txt);

	if ($Src===false) { // Erase all fields

		$x = '';

		// Chached locators
		for ($i=$iMax;$i>0;$i--) {
			if ($LocLst[$i]->PosBeg<$PosMax) {
				$this->meth_Locator_Replace($Txt,$LocLst[$i],$x,false);
				if ($LocLst[$i]->Enlarged) {
					$PosMax = $LocLst[$i]->PosBeg;
					$LocLst[$i]->PosBeg = $LocLst[$i]->PosBeg0;
					$LocLst[$i]->PosEnd = $LocLst[$i]->PosEnd0;
					$LocLst[$i]->Enlarged = false;
				}
			}
		}

		// Uncached locators
		if ($BDef->Chk) {
			$BlockName = &$BDef->Name;
			$Pos = 0;
			while ($Loc = $this->meth_Locator_FindTbs($Txt,$BlockName,$Pos,'.')) $Pos = $this->meth_Locator_Replace($Txt,$Loc,$x,false);
		}

	} else {

		// Cached locators
		for ($i=$iMax;$i>0;$i--) {
			if ($LocLst[$i]->PosBeg<$PosMax) {
				if ($LocLst[$i]->IsRecInfo) {
					if ($LocLst[$i]->RecInfo==='#') {
						$this->meth_Locator_Replace($Txt,$LocLst[$i],$Src->RecNum,false);
					} else {
						$this->meth_Locator_Replace($Txt,$LocLst[$i],$Src->RecKey,false);
					}
				} else {
					$this->meth_Locator_Replace($Txt,$LocLst[$i],$Src->CurrRec,0);
				}
				if ($LocLst[$i]->Enlarged) {
					$PosMax = $LocLst[$i]->PosBeg;
					$LocLst[$i]->PosBeg = $LocLst[$i]->PosBeg0;
					$LocLst[$i]->PosEnd = $LocLst[$i]->PosEnd0;
					$LocLst[$i]->Enlarged = false;
				}
			}
		}

		// Unchached locators
		if ($BDef->Chk) {
			$BlockName = &$BDef->Name;
			foreach ($Src->CurrRec as $key => $val) {
				$Pos = 0;
				$Name = $BlockName.'.'.$key;
				while ($Loc = $this->meth_Locator_FindTbs($Txt,$Name,$Pos,'.')) $Pos = $this->meth_Locator_Replace($Txt,$Loc,$val,0);
			}
			$Pos = 0;
			$Name = $BlockName.'.#';
			while ($Loc = $this->meth_Locator_FindTbs($Txt,$Name,$Pos,'.')) $Pos = $this->meth_Locator_Replace($Txt,$Loc,$Src->RecNum,0);
			$Pos = 0;
			$Name = $BlockName.'.$';
			while ($Loc = $this->meth_Locator_FindTbs($Txt,$Name,$Pos,'.')) $Pos = $this->meth_Locator_Replace($Txt,$Loc,$Src->RecKey,0);
		}

	}

	// Automatic sub-blocks
	if (isset($BDef->AutoSub)) {
		for ($i=1;$i<=$BDef->AutoSub;$i++) {
			$name = $BDef->Name.'_sub'.$i;
			$query = '';
			$col = $BDef->Prm['sub'.$i];
			if ($col===true) $col = '';
			$col_opt = (substr($col,0,1)==='(') && (substr($col,-1,1)===')');
			if ($col_opt) $col = substr($col,1,strlen($col)-2);
			if ($col==='') {
				// $col_opt cannot be used here because values which are not array nore object are reformated by $Src into an array with keys 'key' and 'val'
				$data = &$Src->CurrRec;
			} elseif (is_object($Src->CurrRec)) {
				$data = &$Src->CurrRec->$col;
			} else {
				if (array_key_exists($col, $Src->CurrRec)) {
					$data = &$Src->CurrRec[$col];
				} else {
					if (!$col_opt) $this->meth_Misc_Alert('for merging the automatic sub-block ['.$name.']','key \''.$col.'\' is not found in record #'.$Src->RecNum.' of block ['.$BDef->Name.']. This key can become optional if you designate it with parenthesis in the main block, i.e.: sub'.$i.'=('.$col.')');
					unset($data); $data = array();
				}
			}
			if (is_string($data)) {
				$data = explode(',',$data);
			} elseif (is_null($data) || ($data===false)) {
				$data = array();
			}
			$this->meth_Merge_Block($Txt, $name, $data, $query, false, 0, false);
		}
	}

	return $Txt;

}

function meth_Merge_SectionSerial(&$BDef,&$SrId,&$LocR) {

	$Txt = $BDef->Src;
	$SrBDefOrdered = &$BDef->SrBDefOrdered;
	$Empty = &$LocR->SerialEmpty;

	// All Items
	$F = false;
	for ($i=$BDef->SrBDefNbr;$i>0;$i--) {
		$SrBDef = &$SrBDefOrdered[$i];
		if ($SrBDef->SrTxt===false) { // Subsection not merged with a record
			if ($Empty===false) {
				$SrBDef->SrTxt = $this->meth_Merge_SectionNormal($SrBDef,$F);
			} else {
				$SrBDef->SrTxt = $Empty;
			}
		}
		$Txt = substr_replace($Txt,$SrBDef->SrTxt,$SrBDef->SrBeg,$SrBDef->SrLen);
		$SrBDef->SrTxt = false;
	}

	$SrId = 0;
	return $Txt;

}

function meth_Merge_AutoOn(&$Txt,$Name,$TplVar,$MergeVar) {
// Merge [onload] or [onshow] fields and blocks

	$GrpDisplayed = array();
	$GrpExclusive = array();
	$P1 = false;
	$FieldBefore = false;
	$Pos = 0;

	while ($LocA=$this->meth_Locator_FindBlockNext($Txt,$Name,$Pos,'_',1,$P1,$FieldBefore)) {

		if ($LocA->BlockFound) {

			if (!isset($GrpDisplayed[$LocA->SubName])) {
				$GrpDisplayed[$LocA->SubName] = false;
				$GrpExclusive[$LocA->SubName] = ($LocA->SubName!=='');
			}
			$Displayed = &$GrpDisplayed[$LocA->SubName];
			$Exclusive = &$GrpExclusive[$LocA->SubName];

			$DelBlock = false;
			$DelField = false;
			if ($Displayed && $Exclusive) {
				$DelBlock = true;
			} else {
				if (isset($LocA->PrmLst['when'])) {
					if (isset($LocA->PrmLst['several'])) $Exclusive=false;
					$x = $LocA->PrmLst['when'];
					$this->meth_Merge_AutoVar($x,false);
					if ($this->f_Misc_CheckCondition($x)) {
						$DelField = true;
						$Displayed = true;
					} else {
						$DelBlock = true;
					}
				} elseif(isset($LocA->PrmLst['default'])) {
					if ($Displayed) {
						$DelBlock = true;
					} else {
						$Displayed = true;
						$DelField = true;
					}
					$Exclusive = true; // No more block displayed for the group after
				}
			}

			// Del parts
			if ($DelField) {
				if ($LocA->PosBeg2!==false) $Txt = substr_replace($Txt,'',$LocA->PosBeg2,$LocA->PosEnd2-$LocA->PosBeg2+1);
				$Txt = substr_replace($Txt,'',$LocA->PosBeg,$LocA->PosEnd-$LocA->PosBeg+1);
				$Pos = $LocA->PosBeg;
			} else {
				$FldPos = $LocA->PosBeg;
				$FldLen = $LocA->PosEnd - $LocA->PosBeg + 1;
				if ($LocA->PosBeg2===false) {
					if ($this->f_Loc_EnlargeToTag($Txt,$LocA,$LocA->PrmLst['block'],false)===false) $this->meth_Misc_Alert($LocA,'at least one tag corresponding to '.$LocA->PrmLst['block'].' is not found. Check opening tags, closing tags and embedding levels.',false,'in block\'s definition');
				} else {
					$LocA->PosEnd = $LocA->PosEnd2;
				}
				if ($DelBlock) {
					$parallel = false;
					if (isset($LocA->PrmLst['parallel'])) {
						// may return false if error
						$parallel = $this->meth_Locator_FindParallel($Txt, $LocA->PosBeg, $LocA->PosEnd, $LocA->PrmLst['parallel']);
						if ($parallel===false) {
							$Txt = substr_replace($Txt,'',$FldPos,$FldLen);
						} else {
							// delete in reverse order
							for ($r = count($parallel)-1 ; $r >= 0 ; $r--) {
								$p = $parallel[$r];
								$Txt = substr_replace($Txt,'',$p['PosBeg'],$p['PosEnd']-$p['PosBeg']+1);
							}
						}
					} else {
						$Txt = substr_replace($Txt,'',$LocA->PosBeg,$LocA->PosEnd-$LocA->PosBeg+1);
					}
				} else {
					// Merge the block as if it was a field
					$x = '';
					$this->meth_Locator_Replace($Txt,$LocA,$x,false);
				}
				$Pos = $LocA->PosBeg;
			}

		} else { // Field (has no subname at this point)

			// Check for Template Var
			if ($TplVar) {
				if (isset($LocA->PrmLst['tplvars']) || isset($LocA->PrmLst['tplfrms'])) {
					$Scan = '';
					foreach ($LocA->PrmLst as $Key => $Val) {
						if ($Scan=='v') {
							$this->TplVars[$Key] = $Val;
						} elseif ($Scan=='f') {
							self::f_Misc_FormatSave($Val,$Key);
						} elseif ($Key==='tplvars') {
							$Scan = 'v';
						} elseif ($Key==='tplfrms') {
							$Scan = 'f';
						}
					}
				}
			}

			$x = '';
			$Pos = $this->meth_Locator_Replace($Txt,$LocA,$x,false);
			$Pos = $LocA->PosBeg;

		}

	}

	if ($MergeVar) $this->meth_Merge_AutoVar($this->Source,true,$Name); // merge other fields (must have subnames)

	foreach ($this->Assigned as $n=>$a) {
		if (isset($a['auto']) && ($a['auto']===$Name)) {
			$x = array();
			$this->meth_Misc_Assign($n,$x,false);
		}
	}

}

// Prepare the strconv parameter
function meth_Conv_Prepare(&$Loc, $StrConv) {
	$x = strtolower($StrConv);
	$x = '+'.str_replace(' ','',$x).'+';
	if (strpos($x,'+esc+')!==false)  {$this->f_Misc_ConvSpe($Loc); $Loc->ConvStr = false; $Loc->ConvEsc = true; }
	if (strpos($x,'+wsp+')!==false)  {$this->f_Misc_ConvSpe($Loc); $Loc->ConvWS = true; }
	if (strpos($x,'+js+')!==false)   {$this->f_Misc_ConvSpe($Loc); $Loc->ConvStr = false; $Loc->ConvJS = true; }
	if (strpos($x,'+url+')!==false)  {$this->f_Misc_ConvSpe($Loc); $Loc->ConvStr = false; $Loc->ConvUrl = true; }
	if (strpos($x,'+utf8+')!==false)  {$this->f_Misc_ConvSpe($Loc); $Loc->ConvStr = false; $Loc->ConvUtf8 = true; }
	if (strpos($x,'+no+')!==false)   $Loc->ConvStr = false;
	if (strpos($x,'+yes+')!==false)  $Loc->ConvStr = true;
	if (strpos($x,'+nobr+')!==false) {$Loc->ConvStr = true; $Loc->ConvBr = false; }
}

// Convert a string with charset or custom function
function meth_Conv_Str(&$Txt,$ConvBr=true) {
	if ($this->Charset==='') { // Html by default
		$Txt = htmlspecialchars($Txt);
		if ($ConvBr) $Txt = nl2br($Txt);
	} elseif ($this->_CharsetFct) {
		$Txt = call_user_func($this->Charset,$Txt,$ConvBr);
	} else {
		$Txt = htmlspecialchars($Txt,ENT_COMPAT,$this->Charset);
		if ($ConvBr) $Txt = nl2br($Txt);
	}
}

// Standard alert message provided by TinyButStrong, return False is the message is cancelled.
function meth_Misc_Alert($Src,$Msg,$NoErrMsg=false,$SrcType=false) {
	$this->ErrCount++;
	if ($this->NoErr || (PHP_SAPI==='cli') ) {
		$t = array('','','','','');
	} else {
		$t = array('<br /><b>','</b>','<em>','</em>','<br />');
		$Msg = htmlentities($Msg);
	}
	if (!is_string($Src)) {
		if ($SrcType===false) $SrcType='in field';
		if (isset($Src->PrmLst['tbstype'])) {
			$Msg = 'Column \''.$Src->SubName.'\' is expected but missing in the current record.';
			$Src = 'Parameter \''.$Src->PrmLst['tbstype'].'='.$Src->SubName.'\'';
			$NoErrMsg = false;
		} else {
			$Src = $SrcType.' '.$this->_ChrOpen.$Src->FullName.'...'.$this->_ChrClose;
		}
	}
	$x = $t[0].'TinyButStrong Error'.$t[1].' '.$Src.': '.$Msg;
	if ($NoErrMsg) $x = $x.' '.$t[2].'This message can be cancelled using parameter \'noerr\'.'.$t[3];
	$x = $x.$t[4]."\n";
	if ($this->NoErr) {
		$this->ErrMsg .= $x;
	} else {
		if (PHP_SAPI!=='cli') {
			$x = str_replace($this->_ChrOpen,$this->_ChrProtect,$x);
		}
		echo $x;
	}
	return false;
}

function meth_Misc_Assign($Name,&$ArgLst,$CallingMeth) {
// $ArgLst must be by reference in order to have its inner items by reference too.

	if (!isset($this->Assigned[$Name])) {
		if ($CallingMeth===false) return true;
		return $this->meth_Misc_Alert('with '.$CallingMeth.'() method','key \''.$Name.'\' is not defined in property Assigned.');
	}

	$a = &$this->Assigned[$Name];
	$meth = (isset($a['type'])) ? $a['type'] : 'MergeBlock';
	if (($CallingMeth!==false) && (strcasecmp($CallingMeth,$meth)!=0)) return $this->meth_Misc_Alert('with '.$CallingMeth.'() method','the assigned key \''.$Name.'\' cannot be used with method '.$CallingMeth.' because it is defined to run with '.$meth.'.');

	$n = count($a);
	for ($i=0;$i<$n;$i++) {
		if (isset($a[$i])) $ArgLst[$i] = &$a[$i];
	}

	if ($CallingMeth===false) {
		if (in_array(strtolower($meth),array('mergeblock','mergefield'))) {
			call_user_func_array(array(&$this,$meth), $ArgLst);
		} else {
			return $this->meth_Misc_Alert('The assigned field \''.$Name.'\'. cannot be merged because its type \''.$a[0].'\' is not supported.');
		}
	}
	if (!isset($a['merged'])) $a['merged'] = 0;
	$a['merged']++;
	return true;
}

function meth_Misc_IsMainTpl() {
	return ($this->_Mode==0);
}

function meth_Misc_ChangeMode($Init,&$Loc,&$CurrVal) {
	if ($Init) {
		// Save contents configuration
		$Loc->SaveSrc = &$this->Source;
		$Loc->SaveMode = $this->_Mode;
		$Loc->SaveVarRef = &$this->VarRef;
		unset($this->Source); $this->Source = '';
		$this->_Mode++; // Mode>0 means subtemplate mode
		if ($this->OldSubTpl) {
			ob_start(); // Start buffuring output
			$Loc->SaveRender = $this->Render;
		}
		$this->Render = TBS_OUTPUT;
	} else {
		// Restore contents configuration
		if ($this->OldSubTpl) {
			$CurrVal = ob_get_contents();
			ob_end_clean();
			$this->Render = $Loc->SaveRender;
		} else {
			$CurrVal = $this->Source;
		}
		$this->Source = &$Loc->SaveSrc;
		$this->_Mode = $Loc->SaveMode;
		$this->VarRef = &$Loc->SaveVarRef;
	}
}

function meth_Misc_UserFctCheck(&$FctInfo,$FctCat,&$FctObj,&$ErrMsg,$FctCheck=false) {

	$FctId = $FctCat.':'.$FctInfo;
	if (isset($this->_UserFctLst[$FctId])) {
		$FctInfo = $this->_UserFctLst[$FctId];
		return true;
	}

	// Check and put in cache
	$FctStr = $FctInfo;
	$IsData = ($FctCat!=='f');
	$Save = true;
	if ($FctStr[0]==='~') {
		$ObjRef = &$this->ObjectRef;
		$Lst = explode('.',substr($FctStr,1));
		$iMax = count($Lst) - 1;
		$Suff = 'tbsdb';
		$iMax0 = $iMax;
		if ($IsData) {
			$Suff = $Lst[$iMax];
			$iMax--;
		}
		// Reading sub items
		for ($i=0;$i<=$iMax;$i++) {
			$x = &$Lst[$i];
			if (is_object($ObjRef)) {
				$ArgLst = $this->f_Misc_CheckArgLst($x);
				if (method_exists($ObjRef,$x)) {
					if ($i<$iMax) {
						$f = array(&$ObjRef,$x); unset($ObjRef);
						$ObjRef = call_user_func_array($f,$ArgLst);
					}
				} elseif ($i===$iMax0) {
					$ErrMsg = 'Expression \''.$FctStr.'\' is invalid because \''.$x.'\' is not a method in the class \''.get_class($ObjRef).'\'.';
					return false;
				} elseif (isset($ObjRef->$x)) {
					$ObjRef = &$ObjRef->$x;
				} else {
					$ErrMsg = 'Expression \''.$FctStr.'\' is invalid because sub-item \''.$x.'\' is neither a method nor a property in the class \''.get_class($ObjRef).'\'.';
					return false;
				}
			} elseif (($i<$iMax0) && is_array($ObjRef)) {
				if (isset($ObjRef[$x])) {
					$ObjRef = &$ObjRef[$x];
				} else {
					$ErrMsg = 'Expression \''.$FctStr.'\' is invalid because sub-item \''.$x.'\' is not a existing key in the array.';
					return false;
				}
			} else {
				$ErrMsg = 'Expression \''.$FctStr.'\' is invalid because '.(($i===0)?'property ObjectRef':'sub-item \''.$x.'\'').' is not an object'.(($i<$iMax)?' or an array.':'.');
				return false;
			}
		}
		// Referencing last item
		if ($IsData) {
			$FctInfo = array('open'=>'','fetch'=>'','close'=>'');
			foreach ($FctInfo as $act=>$x) {
				$FctName = $Suff.'_'.$act;
				if (method_exists($ObjRef,$FctName)) {
					$FctInfo[$act] = array(&$ObjRef,$FctName);
				} else {
					$ErrMsg = 'Expression \''.$FctStr.'\' is invalid because method '.$FctName.' is not found.';
					return false;
				}
			}
			$FctInfo['type'] = 4;
			if (isset($this->RecheckObj) && $this->RecheckObj) $Save = false;
		} else {
			$FctInfo = array(&$ObjRef,$x);
		}
	} elseif ($IsData) {

		$IsObj = ($FctCat==='o');

		if ($IsObj && method_exists($FctObj,'tbsdb_open') && (!method_exists($FctObj,'+'))) { // '+' avoid a bug in PHP 5

			if (!method_exists($FctObj,'tbsdb_fetch')) {
				$ErrMsg = 'the expected method \'tbsdb_fetch\' is not found for the class '.$Cls.'.';
				return false;
			}
			if (!method_exists($FctObj,'tbsdb_close')) {
				$ErrMsg = 'the expected method \'tbsdb_close\' is not found for the class '.$Cls.'.';
				return false;
			}
			$FctInfo = array('type'=>5);

		}	else {

			if ($FctCat==='r') { // Resource
				$x = strtolower($FctStr);
				$x = str_replace('-','_',$x);
				$Key = '';
				$i = 0;
				$iMax = strlen($x);
				while ($i<$iMax) {
					if (($x[$i]==='_') || (($x[$i]>='a') && ($x[$i]<='z')) || (($x[$i]>='0') && ($x[$i]<='9'))) {
						$Key .= $x[$i];
						$i++;
					} else {
						$i = $iMax;
					}
				}
			} else {
				$Key = $FctStr;
			}

			$FctInfo = array('open'=>'','fetch'=>'','close'=>'');
			foreach ($FctInfo as $act=>$x) {
				$FctName = 'tbsdb_'.$Key.'_'.$act;
				if (function_exists($FctName)) {
					$FctInfo[$act] = $FctName;
				} else {
					$err = true;
					if ($act==='open') { // Try simplified key
						$p = strpos($Key,'_');
						if ($p!==false) {
							$Key2 = substr($Key,0,$p);
							$FctName2  = 'tbsdb_'.$Key2.'_'.$act;
							if (function_exists($FctName2)) {
								$err = false;
								$Key = $Key2;
								$FctInfo[$act] = $FctName2;
							}
						}
					}
					if ($err) {
						$ErrMsg = 'Data source Id \''.$FctStr.'\' is unsupported because function \''.$FctName.'\' is not found.';
						return false;
					}
				}
			}

			$FctInfo['type'] = 3;

		}

	} else {
		if ( $FctCheck && ($this->FctPrefix!=='') && (strncmp($this->FctPrefix,$FctStr,strlen($this->FctPrefix))!==0) ) {
			$ErrMsg = 'user function \''.$FctStr.'\' does not match the allowed prefix.'; return false;
		} else if (!function_exists($FctStr)) {
			$x = explode('.',$FctStr);
			if (count($x)==2) {
				if (class_exists($x[0])) {
					$FctInfo = $x;
				} else {
					$ErrMsg = 'user function \''.$FctStr.'\' is not correct because \''.$x[0].'\' is not a class name.'; return false;
				}
			} else {
				$ErrMsg = 'user function \''.$FctStr.'\' is not found.'; return false;
			}
		}
	}

	if ($Save) $this->_UserFctLst[$FctId] = $FctInfo;
	return true;

}

function meth_Misc_RunSubscript(&$CurrVal,$CurrPrm) {
// Run a subscript without any local variable damage
	return @include($this->_Subscript);
}

function meth_Misc_Charset($Charset) {
	if ($Charset==='+') return;
	$this->_CharsetFct = false;
	if (is_string($Charset)) {
		if (($Charset!=='') && ($Charset[0]==='=')) {
			$ErrMsg = false;
			$Charset = substr($Charset,1);
			if ($this->meth_Misc_UserFctCheck($Charset,'f',$ErrMsg,$ErrMsg,false)) {
				$this->_CharsetFct = true;
			} else {
				$this->meth_Misc_Alert('with charset option',$ErrMsg);
				$Charset = '';
			}
		}
	} elseif (is_array($Charset)) {
		$this->_CharsetFct = true;
	} elseif ($Charset===false) {
		$this->Protect = false;
	} else {
		$this->meth_Misc_Alert('with charset option','the option value is not a string nor an array.');
		$Charset = '';
	}
	$this->Charset = $Charset;
}

function meth_PlugIn_RunAll(&$FctBank,&$ArgLst) {
	$OkAll = true;
	foreach ($FctBank as $FctInfo) {
		$Ok = call_user_func_array($FctInfo,$ArgLst);
		if (!is_null($Ok)) $OkAll = ($OkAll && $Ok);
	}
	return $OkAll;
}

function meth_PlugIn_Install($PlugInId,$ArgLst,$Auto) {

	$ErrMsg = 'with plug-in \''.$PlugInId.'\'';

	if (class_exists($PlugInId)) {
		// Create an instance
		$IsObj = true;
		$PiRef = new $PlugInId;
		$PiRef->TBS = &$this;
		if (!method_exists($PiRef,'OnInstall')) return $this->meth_Misc_Alert($ErrMsg,'OnInstall() method is not found.');
		$FctRef = array(&$PiRef,'OnInstall');
	} else {
		$FctRef = 'tbspi_'.$PlugInId.'_OnInstall';
		if(function_exists($FctRef)) {
			$IsObj = false;
			$PiRef = true;
		} else {
			return $this->meth_Misc_Alert($ErrMsg,'no class named \''.$PlugInId.'\' is found, and no function named \''.$FctRef.'\' is found.');
		}
	}

	$this->_PlugIns[$PlugInId] = &$PiRef;

	$EventLst = call_user_func_array($FctRef,$ArgLst);
	if (is_string($EventLst)) $EventLst = explode(',',$EventLst);
	if (!is_array($EventLst)) return $this->meth_Misc_Alert($ErrMsg,'OnInstall() method does not return an array.');

	// Add activated methods
	foreach ($EventLst as $Event) {
		$Event = trim($Event);
		if (!$this->meth_PlugIn_SetEvent($PlugInId, $Event)) return false;
	}

	return true;

}

function meth_PlugIn_SetEvent($PlugInId, $Event, $NewRef='') {
// Enable or disable a plug-in event. It can be called by a plug-in, even during the OnInstall event. $NewRef can be used to change the method associated to the event.

	// Check the event's name
	if (strpos(',OnCommand,BeforeLoadTemplate,AfterLoadTemplate,BeforeShow,AfterShow,OnData,OnFormat,OnOperation,BeforeMergeBlock,OnMergeSection,OnMergeGroup,AfterMergeBlock,OnSpecialVar,OnMergeField,OnCacheField,', ','.$Event.',')===false) return $this->meth_Misc_Alert('with plug-in \''.$PlugInId.'\'','The plug-in event named \''.$Event.'\' is not supported by TinyButStrong (case-sensitive). This event may come from the OnInstall() method.');

	$PropName = '_pi'.$Event;

	if ($NewRef===false) {
		// Disable the event
		if (!isset($this->$PropName)) return false;
		$PropRef = &$this->$PropName;
		unset($PropRef[$PlugInId]);
		return true;
	}
	
	// Prepare the reference to be called
	$PiRef = &$this->_PlugIns[$PlugInId];
	if (is_object($PiRef)) {
		if ($NewRef==='') $NewRef = $Event;
		if (!method_exists($PiRef, $NewRef)) return $this->meth_Misc_Alert('with plug-in \''.$PlugInId.'\'','The plug-in event named \''.$Event.'\' is declared but its corresponding method \''.$NewRef.'\' is found.');
		$FctRef = array(&$PiRef, $NewRef);
	} else {
		$FctRef = ($NewRef==='') ? 'tbspi_'.$PlugInId.'_'.$Event : $NewRef;
		if (!function_exists($FctRef)) return $this->meth_Misc_Alert('with plug-in \''.$PlugInId.'\'','The expected function \''.$FctRef.'\' is not found.');
	}

	// Save information into the corresponding property
	if (!isset($this->$PropName)) $this->$PropName = array();
	$PropRef = &$this->$PropName;
	$PropRef[$PlugInId] = $FctRef;

	// Flags saying if a plugin is installed
	switch ($Event) {
	case 'OnCommand': break;
	case 'OnSpecialVar': break;
	case 'OnOperation': break;
	case 'OnFormat': $this->_piOnFrm_Ok = true; break;
	default: $this->_PlugIns_Ok = true; break;
	}
		
	return true;

}

static function meth_Misc_ToStr($Value) {
	if (is_string($Value)) {
		return $Value;
	} elseif(is_object($Value)) {
		if (method_exists($Value,'__toString')) {
			return $Value->__toString();
		} elseif (is_a($Value, 'DateTime')) {
			return $Value->format('c');
		}
	}
	return @(string)$Value; // (string) is faster than strval() and settype()
}

function meth_Misc_Format(&$Value,&$PrmLst) {
// This function return the formated representation of a Date/Time or numeric variable using a 'VB like' format syntax instead of the PHP syntax.

	$FrmStr = $PrmLst['frm'];
	$CheckNumeric = true;
	if (is_string($Value)) $Value = trim($Value);

	if ($FrmStr==='') return '';
	$Frm = self::f_Misc_FormatSave($FrmStr);

	// Manage Multi format strings
	if ($Frm['type']=='multi') {

		// Select the format
		if (is_numeric($Value)) {
			if (is_string($Value)) $Value = 0.0 + $Value;
			if ($Value>0) {
				$FrmStr = &$Frm[0];
			} elseif ($Value<0) {
				$FrmStr = &$Frm[1];
				if ($Frm['abs']) $Value = abs($Value);
			} else { // zero
				$FrmStr = &$Frm[2];
				$Minus = '';
			}
			$CheckNumeric = false;
		} else {
			$Value = $this->meth_Misc_ToStr($Value);
			if ($Value==='') {
				return $Frm[3]; // Null value
			} else {
				$t = strtotime($Value); // We look if it's a date
				if (($t===-1) || ($t===false)) { // Date not recognized
					return $Frm[1];
				} elseif ($t===943916400) { // Date to zero
					return $Frm[2];
				} else { // It's a date
					$Value = $t;
					$FrmStr = &$Frm[0];
				}
			}
		}

		// Retrieve the correct simple format
		if ($FrmStr==='') return '';
		$Frm = self::f_Misc_FormatSave($FrmStr);

	}

	switch ($Frm['type']) {
	case 'num' :
		// NUMERIC
		if ($CheckNumeric) {
			if (is_numeric($Value)) {
				if (is_string($Value)) $Value = 0.0 + $Value;
			} else {
				return $this->meth_Misc_ToStr($Value);
			}
		}
		if ($Frm['PerCent']) $Value = $Value * 100;
		$Value = number_format($Value,$Frm['DecNbr'],$Frm['DecSep'],$Frm['ThsSep']);
		if ($Frm['Pad']!==false) $Value = str_pad($Value, $Frm['Pad'], '0', STR_PAD_LEFT);
		if ($Frm['ThsRpl']!==false) $Value = str_replace($Frm['ThsSep'], $Frm['ThsRpl'], $Value);
		$Value = substr_replace($Frm['Str'],$Value,$Frm['Pos'],$Frm['Len']);
		return $Value;
		break;
	case 'date' :
		// DATE
		if (is_object($Value)) {
			$Value = $this->meth_Misc_ToStr($Value);
		}
		if (is_string($Value)) {
			if ($Value==='') return '';
			$x = strtotime($Value);
			if (($x===-1) || ($x===false)) {
				if (!is_numeric($Value)) $Value = 0;
			} else {
				$Value = &$x;
			}
		} else {
			if (!is_numeric($Value)) return $this->meth_Misc_ToStr($Value);
		}
		if ($Frm['loc'] || isset($PrmLst['locale'])) {
			$x = strftime($Frm['str_loc'],$Value);
			$this->meth_Conv_Str($x,false); // may have accent
			return $x;
		} else {
			return date($Frm['str_us'],$Value);
		}
		break;
	default:
		return $Frm['string'];
		break;
	}

}

// Simply update an array
static function f_Misc_UpdateArray(&$array, $numerical, $v, $d) {
	if (!is_array($v)) {
		if (is_null($v)) {
			$array = array();
			return;
		} else {
			$v = array($v=>$d);
		}
	}
	foreach ($v as $p=>$a) {
		if ($numerical===true) { // numerical keys
			if (is_string($p)) {
				// syntax: item => true/false
				$i = array_search($p, $array, true);
				if ($i===false) {
					if (!is_null($a)) $array[] = $p;
				} else {
					if (is_null($a)) array_splice($array, $i, 1);
				}
			} else {
				// syntax: i => item
				$i = array_search($a, $array, true);
				if ($i==false) $array[] = $a;
			}
		} else { // string keys
			if (is_null($a)) {
				unset($array[$p]);
			} elseif ($numerical==='frm') {
				self::f_Misc_FormatSave($a, $p);
			} else {
				$array[$p] = $a;
			}
		}
	}
}

static function f_Misc_FormatSave(&$FrmStr,$Alias='') {

	$FormatLst = &$GLOBALS['_TBS_FormatLst'];

	if (isset($FormatLst[$FrmStr])) {
		if ($Alias!='') $FormatLst[$Alias] = &$FormatLst[$FrmStr];
		return $FormatLst[$FrmStr];
	}

	if (strpos($FrmStr,'|')!==false) {

		// Multi format
		$Frm = explode('|',$FrmStr); // syntax: Postive|Negative|Zero|Null
		$FrmNbr = count($Frm);
		$Frm['abs'] = ($FrmNbr>1);
		if ($FrmNbr<3) $Frm[2] = &$Frm[0]; // zero
		if ($FrmNbr<4) $Frm[3] = ''; // null
		$Frm['type'] = 'multi';
		$FormatLst[$FrmStr] = $Frm;

	} elseif (($nPosEnd = strrpos($FrmStr,'0'))!==false) {

		// Numeric format
		$nDecSep = '.';
		$nDecNbr = 0;
		$nDecOk = true;
		$nPad = false;
		$nPadZ = 0;

		if (substr($FrmStr,$nPosEnd+1,1)==='.') {
			$nPosEnd++;
			$nPos = $nPosEnd;
			$nPadZ = 1;
		} else {
			$nPos = $nPosEnd - 1;
			while (($nPos>=0) && ($FrmStr[$nPos]==='0')) {
				$nPos--;
			}
			if (($nPos>=1) && ($FrmStr[$nPos-1]==='0')) {
				$nDecSep = $FrmStr[$nPos];
				$nDecNbr = $nPosEnd - $nPos;
			} else {
				$nDecOk = false;
			}
		}

		// Thousand separator
		$nThsSep = '';
		$nThsRpl = false;
		if (($nDecOk) && ($nPos>=5)) {
			if ((substr($FrmStr,$nPos-3,3)==='000') && ($FrmStr[$nPos-4]!=='0')) {
				$p = strrpos(substr($FrmStr,0,$nPos-4), '0');
				if ($p!==false) {
					$len = $nPos-4-$p;
					$x = substr($FrmStr, $p+1, $len);
					if ($len>1) {
						// for compatibility for number_format() with PHP < 5.4.0
						$nThsSep = ($nDecSep=='*') ? '.' : '*';
						$nThsRpl = $x;
					} else {
						$nThsSep = $x;
					}
					$nPos = $p+1;
				}
			}
		}

		// Pass next zero
		if ($nDecOk) $nPos--;
		while (($nPos>=0) && ($FrmStr[$nPos]==='0')) {
			$nPos--;
		}

		$nLen = $nPosEnd-$nPos;
		if ( ($nThsSep==='') && ($nLen>($nDecNbr+$nPadZ+1)) )	$nPad = $nLen - $nPadZ;

		// Percent
		$nPerCent = (strpos($FrmStr,'%')===false) ? false : true;

		$FormatLst[$FrmStr] = array('type'=>'num','Str'=>$FrmStr,'Pos'=>($nPos+1),'Len'=>$nLen,'ThsSep'=>$nThsSep,'ThsRpl'=>$nThsRpl,'DecSep'=>$nDecSep,'DecNbr'=>$nDecNbr,'PerCent'=>$nPerCent,'Pad'=>$nPad);

	} else {

		// Date format
		$x = $FrmStr;
		$FrmPHP = '';
		$FrmLOC = '';
		$StrIn = false;
		$Cnt = 0;
		$i = strpos($FrmStr,'(locale)');
		$Locale = ($i!==false);
		if ($Locale) $x = substr_replace($x,'',$i,8);

		$iEnd = strlen($x);
		for ($i=0;$i<$iEnd;$i++) {

			if ($StrIn) {
				// We are in a string part
				if ($x[$i]==='"') {
					if (substr($x,$i+1,1)==='"') {
						$FrmPHP .= '\\"'; // protected char
						$FrmLOC .= $x[$i];
						$i++;
					} else {
						$StrIn = false;
					}
				} else {
					$FrmPHP .= '\\'.$x[$i]; // protected char
					$FrmLOC .= $x[$i];
				}
			} else {
				if ($x[$i]==='"') {
					$StrIn = true;
				} else {
					$Cnt++;
					if     (strcasecmp(substr($x,$i,2),'hh'  )===0) { $FrmPHP .= 'H'; $FrmLOC .= '%H'; $i += 1;}
					elseif (strcasecmp(substr($x,$i,2),'hm'  )===0) { $FrmPHP .= 'h'; $FrmLOC .= '%I'; $i += 1;} // for compatibility
					elseif (strcasecmp(substr($x,$i,1),'h'   )===0) { $FrmPHP .= 'G'; $FrmLOC .= '%H';}
					elseif (strcasecmp(substr($x,$i,2),'rr'  )===0) { $FrmPHP .= 'h'; $FrmLOC .= '%I'; $i += 1;}
					elseif (strcasecmp(substr($x,$i,1),'r'   )===0) { $FrmPHP .= 'g'; $FrmLOC .= '%I';}
					elseif (strcasecmp(substr($x,$i,4),'ampm')===0) { $FrmPHP .= substr($x,$i,1); $FrmLOC .= '%p'; $i += 3;} // $Fmp = 'A' or 'a'
					elseif (strcasecmp(substr($x,$i,2),'nn'  )===0) { $FrmPHP .= 'i'; $FrmLOC .= '%M'; $i += 1;}
					elseif (strcasecmp(substr($x,$i,2),'ss'  )===0) { $FrmPHP .= 's'; $FrmLOC .= '%S'; $i += 1;}
					elseif (strcasecmp(substr($x,$i,2),'xx'  )===0) { $FrmPHP .= 'S'; $FrmLOC .= ''  ; $i += 1;}
					elseif (strcasecmp(substr($x,$i,4),'yyyy')===0) { $FrmPHP .= 'Y'; $FrmLOC .= '%Y'; $i += 3;}
					elseif (strcasecmp(substr($x,$i,2),'yy'  )===0) { $FrmPHP .= 'y'; $FrmLOC .= '%y'; $i += 1;}
					elseif (strcasecmp(substr($x,$i,4),'mmmm')===0) { $FrmPHP .= 'F'; $FrmLOC .= '%B'; $i += 3;}
					elseif (strcasecmp(substr($x,$i,3),'mmm' )===0) { $FrmPHP .= 'M'; $FrmLOC .= '%b'; $i += 2;}
					elseif (strcasecmp(substr($x,$i,2),'mm'  )===0) { $FrmPHP .= 'm'; $FrmLOC .= '%m'; $i += 1;}
					elseif (strcasecmp(substr($x,$i,1),'m'   )===0) { $FrmPHP .= 'n'; $FrmLOC .= '%m';}
					elseif (strcasecmp(substr($x,$i,4),'wwww')===0) { $FrmPHP .= 'l'; $FrmLOC .= '%A'; $i += 3;}
					elseif (strcasecmp(substr($x,$i,3),'www' )===0) { $FrmPHP .= 'D'; $FrmLOC .= '%a'; $i += 2;}
					elseif (strcasecmp(substr($x,$i,1),'w'   )===0) { $FrmPHP .= 'w'; $FrmLOC .= '%u';}
					elseif (strcasecmp(substr($x,$i,4),'dddd')===0) { $FrmPHP .= 'l'; $FrmLOC .= '%A'; $i += 3;}
					elseif (strcasecmp(substr($x,$i,3),'ddd' )===0) { $FrmPHP .= 'D'; $FrmLOC .= '%a'; $i += 2;}
					elseif (strcasecmp(substr($x,$i,2),'dd'  )===0) { $FrmPHP .= 'd'; $FrmLOC .= '%d'; $i += 1;}
					elseif (strcasecmp(substr($x,$i,1),'d'   )===0) { $FrmPHP .= 'j'; $FrmLOC .= '%d';}
					else {
						$FrmPHP .= '\\'.$x[$i]; // protected char
						$FrmLOC .= $x[$i]; // protected char
						$Cnt--;
					}
				}
			}

		}

		if ($Cnt>0) {
			$FormatLst[$FrmStr] = array('type'=>'date','str_us'=>$FrmPHP,'str_loc'=>$FrmLOC,'loc'=>$Locale);
		} else {
			$FormatLst[$FrmStr] = array('type'=>'else','string'=>$FrmStr);
		}

	}

	if ($Alias!='') $FormatLst[$Alias] = &$FormatLst[$FrmStr];

	return $FormatLst[$FrmStr];

}

static function f_Misc_ConvSpe(&$Loc) {
	if ($Loc->ConvMode!==2) {
		$Loc->ConvMode = 2;
		$Loc->ConvEsc = false;
		$Loc->ConvWS = false;
		$Loc->ConvJS = false;
		$Loc->ConvUrl = false;
		$Loc->ConvUtf8 = false;
	}
}

static function f_Misc_CheckArgLst(&$Str) {
	$ArgLst = array();
	if (substr($Str,-1,1)===')') {
		$pos = strpos($Str,'(');
		if ($pos!==false) {
			$ArgLst = explode(',',substr($Str,$pos+1,strlen($Str)-$pos-2));
			$Str = substr($Str,0,$pos);
		}
	}
	return $ArgLst;
}

static function f_Misc_CheckCondition($Str) {
// Check if an expression like "exrp1=expr2" is true or false.

	$StrZ = $Str; // same string but without protected data
	$Max = strlen($Str)-1;
	$p = strpos($Str,'\'');
	if ($Esc=($p!==false)) {
		$In = true;
		for ($p=$p+1;$p<=$Max;$p++) {
			if ($StrZ[$p]==='\'') {
				$In = !$In;
			} elseif ($In) {
				$StrZ[$p] = 'z';
			}
		}
	}

	// Find operator and position
	$Ope = '=';
	$Len = 1;
	$p = strpos($StrZ,$Ope);
	if ($p===false) {
		$Ope = '+';
		$p = strpos($StrZ,$Ope);
		if ($p===false) return false;
		if (($p>0) && ($StrZ[$p-1]==='-')) {
			$Ope = '-+'; $p--; $Len=2;
		} elseif (($p<$Max) && ($StrZ[$p+1]==='-')) {
			$Ope = '+-'; $Len=2;
		} else {
			return false;
		}
	} else {
		if ($p>0) {
			$x = $StrZ[$p-1];
			if ($x==='!') {
				$Ope = '!='; $p--; $Len=2;
			} elseif ($x==='~') {
				$Ope = '~='; $p--; $Len=2;
			} elseif ($p<$Max) {
				$y = $StrZ[$p+1];
				if ($y==='=') {
					$Len=2;
				} elseif (($x==='+') && ($y==='-')) {
					$Ope = '+=-'; $p--; $Len=3;
				} elseif (($x==='-') && ($y==='+')) {
					$Ope = '-=+'; $p--; $Len=3;
				}
			} else {
			}
		}
	}

	// Read values
	$Val1  = trim(substr($Str,0,$p));
	$Val2  = trim(substr($Str,$p+$Len));
	if ($Esc) {
		$Nude1 = self::f_Misc_DelDelimiter($Val1,'\'');
		$Nude2 = self::f_Misc_DelDelimiter($Val2,'\'');
	} else {
		$Nude1 = $Nude2 = false;
	}

	// Compare values
	if ($Ope==='=') {
		return (strcasecmp($Val1,$Val2)==0);
	} elseif ($Ope==='!=') {
		return (strcasecmp($Val1,$Val2)!=0);
	} elseif ($Ope==='~=') {
		return (preg_match($Val2,$Val1)>0);
	} else {
		if ($Nude1) $Val1='0'+$Val1;
		if ($Nude2) $Val2='0'+$Val2;
		if ($Ope==='+-') {
			return ($Val1>$Val2);
		} elseif ($Ope==='-+') {
			return ($Val1 < $Val2);
		} elseif ($Ope==='+=-') {
			return ($Val1 >= $Val2);
		} elseif ($Ope==='-=+') {
			return ($Val1<=$Val2);
		} else {
			return false;
		}
	}

}

static function f_Misc_DelDelimiter(&$Txt,$Delim) {
// Delete the string delimiters
	$len = strlen($Txt);
	if (($len>1) && ($Txt[0]===$Delim)) {
		if ($Txt[$len-1]===$Delim) $Txt = substr($Txt,1,$len-2);
		return false;
	} else {
		return true;
	}
}

static function f_Misc_GetFile(&$Res, &$File, $LastFile='', $IncludePath=false, $Contents=true) {
// Load the content of a file into the text variable.

	$Res = '';
	$fd = self::f_Misc_TryFile($File, false); 
	if ($fd===false) {
		if (is_array($IncludePath)) {
			foreach ($IncludePath as $d) {
				$fd = self::f_Misc_TryFile($File, $d);
				if ($fd!==false) break;
			}
		}
		if (($fd===false) && ($LastFile!='')) $fd = self::f_Misc_TryFile($File, dirname($LastFile));
		if ($fd===false) return false;
	}

	$fs = fstat($fd);
	if ($Contents) {
		// Return contents
		if (isset($fs['size'])) {
			if ($fs['size']>0) $Res = fread($fd,$fs['size']);
		} else {
			while (!feof($fd)) $Res .= fread($fd,4096);
		}
	} else {
		// Return stats
		$Res = $fs;
	}

	fclose($fd);
	return true;

}

static function f_Misc_TryFile(&$File, $Dir) {
	if ($Dir==='') return false;
	$FileSearch = ($Dir===false) ? $File : $Dir.'/'.$File;
	// 'rb' if binary for some OS. fopen() uses include_path and search on the __FILE__ directory while file_exists() doesn't.
	$f = @fopen($FileSearch, 'r', true);
	if ($f!==false) $File = $FileSearch;
	return $f;
}

static function f_Loc_PrmRead(&$Txt,$Pos,$XmlTag,$DelimChrs,$BegStr,$EndStr,&$Loc,&$PosEnd,$WithPos=false) {

	$BegLen = strlen($BegStr);
	$BegChr = $BegStr[0];
	$BegIs1 = ($BegLen===1);

	$DelimIdx = false;
	$DelimCnt = 0;
	$DelimChr = '';
	$BegCnt = 0;
	$SubName = $Loc->SubOk;

	$Status = 0; // 0: name not started, 1: name started, 2: name ended, 3: equal found, 4: value started
	$PosName = 0;
	$PosNend = 0;
	$PosVal = 0;

	// Variables for checking the loop
	$PosEnd = strpos($Txt,$EndStr,$Pos);
	if ($PosEnd===false) return;
	$Continue = ($Pos<$PosEnd);

	while ($Continue) {

		$Chr = $Txt[$Pos];

		if ($DelimIdx) { // Reading in the string

			if ($Chr===$DelimChr) { // Quote found
				if ($Chr===$Txt[$Pos+1]) { // Double Quote => the string continue with un-double the quote
					$Pos++;
				} else { // Simple Quote => end of string
					$DelimIdx = false;
				}
			}

		} else { // Reading outside the string

			if ($BegCnt===0) {

				// Analyzing parameters
				$CheckChr = false;
				if (($Chr===' ') || ($Chr==="\r") || ($Chr==="\n")) {
					if ($Status===1) {
						if ($SubName && ($XmlTag===false)) {
							// Accept spaces in TBS subname.
						} else {
							$Status = 2;
							$PosNend = $Pos;
						}
					} elseif ($XmlTag && ($Status===4)) {
						self::f_Loc_PrmCompute($Txt,$Loc,$SubName,$Status,$XmlTag,$DelimChr,$DelimCnt,$PosName,$PosNend,$PosVal,$Pos,$WithPos);
						$Status = 0;
					}
				} elseif (($XmlTag===false) && ($Chr===';')) {
					self::f_Loc_PrmCompute($Txt,$Loc,$SubName,$Status,$XmlTag,$DelimChr,$DelimCnt,$PosName,$PosNend,$PosVal,$Pos,$WithPos);
					$Status = 0;
				} elseif ($Status===4) {
					$CheckChr = true;
				} elseif ($Status===3) {
					$Status = 4;
					$DelimCnt = 0;
					$PosVal = $Pos;
					$CheckChr = true;
				} elseif ($Status===2) {
					if ($Chr==='=') {
						$Status = 3;
					} elseif ($XmlTag) {
						self::f_Loc_PrmCompute($Txt,$Loc,$SubName,$Status,$XmlTag,$DelimChr,$DelimCnt,$PosName,$PosNend,$PosVal,$Pos,$WithPos);
						$Status = 1;
						$PosName = $Pos;
						$CheckChr = true;
					} else {
						$Status = 4;
						$DelimCnt = 0;
						$PosVal = $Pos;
						$CheckChr = true;
					}
				} elseif ($Status===1) {
					if ($Chr==='=') {
						$Status = 3;
						$PosNend = $Pos;
					} else {
						$CheckChr = true;
					}
				} else {
					$Status = 1;
					$PosName = $Pos;
					$CheckChr = true;
				}

				if ($CheckChr) {
					$DelimIdx = strpos($DelimChrs,$Chr);
					if ($DelimIdx===false) {
						if ($Chr===$BegChr) {
							if ($BegIs1) {
								$BegCnt++;
							} elseif(substr($Txt,$Pos,$BegLen)===$BegStr) {
								$BegCnt++;
							}
						}
					} else {
						$DelimChr = $DelimChrs[$DelimIdx];
						$DelimCnt++;
						$DelimIdx = true;
					}
				}

			} else {
				if ($Chr===$BegChr) {
					if ($BegIs1) {
						$BegCnt++;
					} elseif(substr($Txt,$Pos,$BegLen)===$BegStr) {
						$BegCnt++;
					}
				}
			}

		}

		// Next char
		$Pos++;

		// We check if it's the end
		if ($Pos===$PosEnd) {
			if ($XmlTag) {
				$Continue = false;
			} elseif ($DelimIdx===false) {
				if ($BegCnt>0) {
					$BegCnt--;
				} else {
					$Continue = false;
				}
			}
			if ($Continue) {
				$PosEnd = strpos($Txt,$EndStr,$PosEnd+1);
				if ($PosEnd===false) return;
			} else {
				if ($XmlTag && ($Txt[$Pos-1]==='/')) $Pos--; // In case last attribute is stuck to "/>"
				self::f_Loc_PrmCompute($Txt,$Loc,$SubName,$Status,$XmlTag,$DelimChr,$DelimCnt,$PosName,$PosNend,$PosVal,$Pos,$WithPos);
			}
		}

	}

	$PosEnd = $PosEnd + (strlen($EndStr)-1);

}

static function f_Loc_PrmCompute(&$Txt,&$Loc,&$SubName,$Status,$XmlTag,$DelimChr,$DelimCnt,$PosName,$PosNend,$PosVal,$Pos,$WithPos) {

	if ($Status===0) {
		$SubName = false;
	} else {
		if ($Status===1) {
			$x = substr($Txt,$PosName,$Pos-$PosName);
		} else {
			$x = substr($Txt,$PosName,$PosNend-$PosName);
		}
		if ($XmlTag) $x = strtolower($x);
		if ($SubName) {
			$Loc->SubName = trim($x);
			$SubName = false;
		} else {
			if ($Status===4) {
				$v = trim(substr($Txt,$PosVal,$Pos-$PosVal));
				if ($DelimCnt===1) { // Delete quotes inside the value
					if ($v[0]===$DelimChr) {
						$len = strlen($v);
						if ($v[$len-1]===$DelimChr) {
							$v = substr($v,1,$len-2);
							$v = str_replace($DelimChr.$DelimChr,$DelimChr,$v);
						}
					}
				}
			} else {
				$v = true;
			}
			if ($x==='if') {
				self::f_Loc_PrmIfThen($Loc,true,$v);
			} elseif ($x==='then') {
				self::f_Loc_PrmIfThen($Loc,false,$v);
			} else {
				$Loc->PrmLst[$x] = $v;
				if ($WithPos) $Loc->PrmPos[$x] = array($PosName,$PosNend,$PosVal,$Pos,$DelimChr,$DelimCnt);
			}
		}
	}

}

static function f_Loc_PrmIfThen(&$Loc,$IsIf,$Val) {
	$nbr = &$Loc->PrmIfNbr;
	if ($nbr===false) {
		$nbr = 0;
		$Loc->PrmIf = array();
		$Loc->PrmIfVar = array();
		$Loc->PrmThen = array();
		$Loc->PrmThenVar = array();
		$Loc->PrmElseVar = true;
	}
	if ($IsIf) {
		$nbr++;
		$Loc->PrmIf[$nbr] = $Val;
		$Loc->PrmIfVar[$nbr] = true;
	} else {
		$nbr2 = $nbr;
		if ($nbr2===false) $nbr2 = 1; // Only the first 'then' can be placed before its 'if'. This is for compatibility.
		$Loc->PrmThen[$nbr2] = $Val;
		$Loc->PrmThenVar[$nbr2] = true;
	}
}

static function f_Loc_EnlargeToStr(&$Txt,&$Loc,$StrBeg,$StrEnd) {
/*
This function enables to enlarge the pos limits of the Locator.
If the search result is not correct, $PosBeg must not change its value, and $PosEnd must be False.
This is because of the calling function.
*/

	// Search for the begining string
	$Pos = $Loc->PosBeg;
	$Ok = false;
	do {
		$Pos = strrpos(substr($Txt,0,$Pos),$StrBeg[0]);
		if ($Pos!==false) {
			if (substr($Txt,$Pos,strlen($StrBeg))===$StrBeg) $Ok = true;
		}
	} while ( (!$Ok) && ($Pos!==false) );

	if ($Ok) {
		$PosEnd = strpos($Txt,$StrEnd,$Loc->PosEnd + 1);
		if ($PosEnd===false) {
			$Ok = false;
		} else {
			$Loc->PosBeg = $Pos;
			$Loc->PosEnd = $PosEnd + strlen($StrEnd) - 1;
		}
	}

	return $Ok;

}

static function f_Loc_EnlargeToTag(&$Txt,&$Loc,$TagStr,$RetInnerSrc) {
//Modify $Loc, return false if tags not found, returns the inner source of tag if $RetInnerSrc=true

	$AliasLst = &$GLOBALS['_TBS_BlockAlias'];

	// Analyze string
	$Ref = 0;
	$LevelStop = 0;
	$i = 0;
	$TagFct = array();
	$TagLst = array();
	$TagBnd = array();
	while ($TagStr!=='') {
		// get next tag
		$p = strpos($TagStr, '+');
		if ($p===false) {
			$t = $TagStr;
			$TagStr = '';
		} else {
			$t = substr($TagStr,0,$p);
			$TagStr = substr($TagStr,$p+1);
		}
		// Check parentheses, relative position and single tag
 		do {
 			$t = trim($t);
	 		$e = strlen($t) - 1; // pos of last char
	 		if (($e>1) && ($t[0]==='(') && ($t[$e]===')')) {
	 			if ($Ref===0) $Ref = $i;
	 			if ($Ref===$i) $LevelStop++;
	 			$t = substr($t,1,$e-1);
	 		} else {
	 			if (($e>=0) && ($t[$e]==='/')) $t = substr($t,0,$e); // for compatibilty
	 			$e = false;
	 		}
 		} while ($e!==false);
		// Check for multiples
		$p = strpos($t, '*');
		if ($p!==false) {
			$n = intval(substr($t, 0, $p));
			$t = substr($t, $p + 1);
			$n = max($n ,1); // prevent for error: minimum valu is 1
			$TagStr = str_repeat($t . '+', $n-1) . $TagStr;
		}
		// Reference
		if (($t==='.') && ($Ref===0)) $Ref = $i;
		// Take of the (!) prefix
		$b = '';
		if (($t!=='') && ($t[0]==='!')) {
			$t = substr($t, 1);
			$b = '!';
		}
		// Alias
		$a = false;
		if (isset($AliasLst[$t])) {
			$a = $AliasLst[$t]; // a string or a function
			if (is_string($a)) {
				if ($i>999) return false; // prevent from circular alias
				$TagStr = $b . $a . (($TagStr==='') ? '' : '+') . $TagStr;
				$t = false;
			}
		}
		if ($t!==false) {
			$TagLst[$i] = $t; // with prefix ! if specified
			$TagFct[$i] = $a;
			$TagBnd[$i] = ($b==='');
			$i++;
		}
	}
	
	$TagMax = $i-1;

	// Find tags that embeds the locator
	if ($LevelStop===0) $LevelStop = 1;

	// First tag of reference
	if ($TagLst[$Ref] === '.') {
		$TagO = new clsTbsLocator;
		$TagO->PosBeg = $Loc->PosBeg;
		$TagO->PosEnd = $Loc->PosEnd;
		$PosBeg = $Loc->PosBeg;
		$PosEnd = $Loc->PosEnd;
	} else {
		$TagO = self::f_Loc_Enlarge_Find($Txt,$TagLst[$Ref],$TagFct[$Ref],$Loc->PosBeg-1,false,$LevelStop);
		if ($TagO===false) return false;
		$PosBeg = $TagO->PosBeg;
		$LevelStop += -$TagO->RightLevel; // RightLevel=1 only if the tag is single and embeds $Loc, otherwise it is 0 
		if ($LevelStop>0) {
			$TagC = self::f_Loc_Enlarge_Find($Txt,$TagLst[$Ref],$TagFct[$Ref],$Loc->PosEnd+1,true,-$LevelStop);
			if ($TagC==false) return false;
			$PosEnd = $TagC->PosEnd;
			$InnerLim = $TagC->PosBeg;
			if ((!$TagBnd[$Ref]) && ($TagMax==0)) {
				$PosBeg = $TagO->PosEnd + 1;
				$PosEnd = $TagC->PosBeg - 1;
			}
		} else {
			$PosEnd = $TagO->PosEnd;
			$InnerLim = $PosEnd + 1;
		}
	}

	$RetVal = true;
	if ($RetInnerSrc) {
		$RetVal = '';
		if ($Loc->PosBeg>$TagO->PosEnd) $RetVal .= substr($Txt,$TagO->PosEnd+1,min($Loc->PosBeg,$InnerLim)-$TagO->PosEnd-1);
		if ($Loc->PosEnd<$InnerLim) $RetVal .= substr($Txt,max($Loc->PosEnd,$TagO->PosEnd)+1,$InnerLim-max($Loc->PosEnd,$TagO->PosEnd)-1);
	}

	// Other tags forward
	$TagC = true;
	for ($i=$Ref+1;$i<=$TagMax;$i++) {
		$x = $TagLst[$i];
		if (($x!=='') && ($TagC!==false)) {
			$level = ($TagBnd[$i]) ? 0 : 1;
			$TagC = self::f_Loc_Enlarge_Find($Txt,$x,$TagFct[$i],$PosEnd+1,true,$level);
			if ($TagC!==false) {
				$PosEnd = ($TagBnd[$i]) ? $TagC->PosEnd : $TagC->PosBeg -1 ;
			}
		}
	}

	// Other tags backward
	$TagO = true;
	for ($i=$Ref-1;$i>=0;$i--) {
		$x = $TagLst[$i];
		if (($x!=='') && ($TagO!==false)) {
			$level = ($TagBnd[$i]) ? 0 : -1;
			$TagO = self::f_Loc_Enlarge_Find($Txt,$x,$TagFct[$i],$PosBeg-1,false,$level);
			if ($TagO!==false) {
				$PosBeg = ($TagBnd[$i]) ? $TagO->PosBeg : $TagO->PosEnd + 1;
			}
		}
	}

	$Loc->PosBeg = $PosBeg;
	$Loc->PosEnd = $PosEnd;
	return $RetVal;

}

static function f_Loc_Enlarge_Find($Txt, $Tag, $Fct, $Pos, $Forward, $LevelStop) {
	if ($Fct===false) {
		return self::f_Xml_FindTag($Txt,$Tag,(!$Forward),$Pos,$Forward,$LevelStop,false);
	} else {
		$p = call_user_func_array($Fct,array($Tag,$Txt,$Pos,$Forward,$LevelStop));
		if ($p===false) {
			return false;
		} else {
			return (object) array('PosBeg'=>$p, 'PosEnd'=>$p, 'RightLevel'=> 0); // it's a trick
		}	
	}
}

static function f_Loc_AttBoolean($CurrVal, $AttTrue, $AttName) {

// Return the good value for a boolean attribute
	if ($AttTrue===true) {
		if (self::meth_Misc_ToStr($CurrVal)==='') {
			return '';
		} else {
			return $AttName;
		}
	} elseif (self::meth_Misc_ToStr($CurrVal)===$AttTrue) {
		return $AttName;
	} else {
		return '';
	}
}

/**
 * Affects the positions of a list of locators regarding to a specific moving locator.
 */
static function f_Loc_Moving(&$LocM, &$LocLst) {
	foreach ($LocLst as &$Loc) {
		if ($Loc !== $LocM) {
			if ($Loc->PosBeg >= $LocM->InsPos) {
				$Loc->PosBeg += $LocM->InsLen;
				$Loc->PosEnd += $LocM->InsLen;
			}
			if ($Loc->PosBeg > $LocM->DelPos) {
				$Loc->PosBeg -= $LocM->DelLen;
				$Loc->PosEnd -= $LocM->DelLen;
			}
		}
	}
	return true;
}

/**
 * Sort the locators in the list. Apply the bubble algorithm.
 * Deleted locators maked with DelMe.
 * @param array   $LocLst An array of locators.
 * @param boolean $DelEmbd True to deleted locators that embded other ones.
 * @param boolean $iFirst Index of the first item.
 * @return integer Return the number of met embedding locators.
 */
static function f_Loc_Sort(&$LocLst, $DelEmbd, $iFirst = 0) {

	$iLast = $iFirst + count($LocLst) - 1;
	$embd = 0;
	
	for ($i = $iLast ; $i>=$iFirst ; $i--) {
		$Loc = $LocLst[$i];
		$d = (isset($Loc->DelMe) && $Loc->DelMe);
		$b = $Loc->PosBeg;
		$e = $Loc->PosEnd;
		for ($j=$i+1; $j<=$iLast ; $j++) {
			// If DelMe, then the loc will be put at the end and deleted
			$jb = $LocLst[$j]->PosBeg;
			if ($d || ($b > $jb)) {
				$LocLst[$j-1] = $LocLst[$j];
				$LocLst[$j] = $Loc;
			} elseif ($e > $jb) {
				$embd++;
				if ($DelEmbd) {
					$d = true;
					$j--; // replay the current position
				} else {
					$j = $iLast; // quit the loop
				}
			} else {
				$j = $iLast; // quit the loop
			}
		}
		if ($d) {
			unset($LocLst[$iLast]);
			$iLast--;
		}
	}
	
	return $embd;
}

/**
 * Prepare all informations to move a locator according to parameter "att".
 * @param mixed $MoveLocLst true to simple move the loc, or an array of loc to rearrange the list after the move.
 *              Note: rearrange doest not work with PHP4.
 */
static function f_Xml_AttFind(&$Txt,&$Loc,$MoveLocLst=false,$AttDelim=false,$LocLst=false) {
// att=div#class ; att=((div))#class ; att=+((div))#class

	$Att = $Loc->PrmLst['att'];
	unset($Loc->PrmLst['att']); // prevent from processing the field twice
	$Loc->PrmLst['att;'] = $Att; // for debug

	$p = strrpos($Att,'#');
	if ($p===false) {
		$TagLst = '';
	} else {
		$TagLst = substr($Att,0,$p);
		$Att = substr($Att,$p+1);
	}

	$Forward = (substr($TagLst,0,1)==='+');
	if ($Forward) $TagLst = substr($TagLst,1);
	$TagLst = explode('+',$TagLst);

	$iMax = count($TagLst)-1;
	$WithPrm = false;
	$LocO = &$Loc;
	foreach ($TagLst as $i=>$Tag) {
		$LevelStop = false;
		while ((strlen($Tag)>1) && (substr($Tag,0,1)==='(') && (substr($Tag,-1,1)===')')) {
			if ($LevelStop===false) $LevelStop = 0;
			$LevelStop++;
			$Tag = trim(substr($Tag,1,strlen($Tag)-2));
		}
		if ($i==$iMax) $WithPrm = true;
		$Pos = ($Forward) ? $LocO->PosEnd+1 : $LocO->PosBeg-1;
		unset($LocO);
		$LocO = self::f_Xml_FindTag($Txt,$Tag,true,$Pos,$Forward,$LevelStop,$WithPrm,$WithPrm);
		if ($LocO===false) return false;
	}

	$Loc->AttForward = $Forward;
	$Loc->AttTagBeg = $LocO->PosBeg;
	$Loc->AttTagEnd = $LocO->PosEnd;
	$Loc->AttDelimChr = false;

	if ($Att==='.') {
		// this indicates that the TBS field is supposed to be inside an attribute's value
		foreach ($LocO->PrmPos as $a=>$p ) {
			if ( ($p[0]<$Loc->PosBeg) && ($Loc->PosEnd<$p[3]) ) $Att = $a;
		}
		if ($Att==='.') return false;
	}
	$Loc->AttName = $Att;
	
	$AttLC = strtolower($Att);
	if (isset($LocO->PrmLst[$AttLC])) {
		// The attribute is existing
		$p = $LocO->PrmPos[$AttLC];
		$Loc->AttBeg = $p[0];
		$p[3]--; while ($Txt[$p[3]]===' ') $p[3]--; // external end of the attribute, may has an extra spaces
		$Loc->AttEnd = $p[3];
		$Loc->AttDelimCnt = $p[5];
		$Loc->AttDelimChr = $p[4];
		if (($p[1]>$p[0]) && ($p[2]>$p[1])) {
			//$Loc->AttNameEnd =  $p[1];
			$Loc->AttValBeg = $p[2];
		} else { // attribute without value
			//$Loc->AttNameEnd =  $p[3];
			$Loc->AttValBeg = false;
		}
	} else {
		// The attribute is not yet existing
		$Loc->AttDelimCnt = 0;
		$Loc->AttBeg = false;
	}
	
	// Search for a delimitor
	if (($Loc->AttDelimCnt==0) && (isset($LocO->PrmPos))) {
		foreach ($LocO->PrmPos as $p) {
			if ($p[5]>0) $Loc->AttDelimChr = $p[4];
		}
	}

	if ($MoveLocLst) return self::f_Xml_AttMove($Txt,$Loc,$AttDelim,$MoveLocLst);

	return true;

}

static function f_Xml_AttMove(&$Txt, &$Loc, $AttDelim, &$MoveLocLst) {

	if ($AttDelim===false) $AttDelim = $Loc->AttDelimChr;
	if ($AttDelim===false) $AttDelim = '"';

	$DelPos = $Loc->PosBeg;
	$DelLen = $Loc->PosEnd - $Loc->PosBeg + 1;
	$Txt = substr_replace($Txt,'',$DelPos,$DelLen); // delete the current locator
	if ($Loc->AttForward) {
		$Loc->AttTagBeg += -$DelLen;
		$Loc->AttTagEnd += -$DelLen;
	} elseif ($Loc->PosBeg<$Loc->AttTagEnd) {
		$Loc->AttTagEnd += -$DelLen;
	}

	$InsPos = false;
	if ($Loc->AttBeg===false) {
		$InsPos = $Loc->AttTagEnd;
		if ($Txt[$InsPos-1]==='/') $InsPos--;
		if ($Txt[$InsPos-1]===' ') $InsPos--;
		$Ins1 = ' '.$Loc->AttName.'='.$AttDelim;
		$Ins2 = $AttDelim;
		$Loc->AttBeg = $InsPos + 1;
		$Loc->AttValBeg = $InsPos + strlen($Ins1) - 1;
	} else {
		if ($Loc->PosEnd<$Loc->AttBeg) $Loc->AttBeg += -$DelLen;
		if ($Loc->PosEnd<$Loc->AttEnd) $Loc->AttEnd += -$DelLen;
		if ($Loc->AttValBeg===false) {
			$InsPos = $Loc->AttEnd+1;
			$Ins1 = '='.$AttDelim;
			$Ins2 = $AttDelim;
			$Loc->AttValBeg = $InsPos+1;
		} elseif (isset($Loc->PrmLst['attadd'])) {
			$InsPos = $Loc->AttEnd;
			$Ins1 = ' ';
			$Ins2 = '';
		} else {
			// value already existing
			if ($Loc->PosEnd<$Loc->AttValBeg) $Loc->AttValBeg += -$DelLen;
			$PosBeg = $Loc->AttValBeg;
			$PosEnd = $Loc->AttEnd;
			if ($Loc->AttDelimCnt>0) {$PosBeg++; $PosEnd--;}
		}
	}

	if ($InsPos===false) {
		$InsLen = 0;
	} else {
		$InsTxt = $Ins1.'[]'.$Ins2;
		$InsLen = strlen($InsTxt);
		$PosBeg = $InsPos + strlen($Ins1);
		$PosEnd = $PosBeg + 1;
		$Txt = substr_replace($Txt,$InsTxt,$InsPos,0);
		$Loc->AttEnd = $InsPos + $InsLen - 1;
		$Loc->AttTagEnd += $InsLen;
	}

	$Loc->PrevPosBeg = $Loc->PosBeg;
	$Loc->PrevPosEnd = $Loc->PosEnd;
	$Loc->PosBeg = $PosBeg;
	$Loc->PosEnd = $PosEnd;
	$Loc->AttBegM = ($Txt[$Loc->AttBeg-1]===' ') ? $Loc->AttBeg-1 : $Loc->AttBeg; // for magnet=#

	// for CacheField
	if (is_array($MoveLocLst)) {
		$Loc->InsPos = $InsPos;
		$Loc->InsLen = $InsLen;
		$Loc->DelPos = $DelPos;
		if ($Loc->InsPos < $Loc->DelPos) $Loc->DelPos += $InsLen;
		$Loc->DelLen = $DelLen;
		self::f_Loc_Moving($Loc, $MoveLocLst);
	}
	
	return true;

}

static function f_Xml_Max(&$Txt,&$Nbr,$MaxEnd) {
// Limit the number of HTML chars

	$pMax =  strlen($Txt)-1;
	$p=0;
	$n=0;
	$in = false;
	$ok = true;

	while ($ok) {
		if ($in) {
			if ($Txt[$p]===';') {
				$in = false;
				$n++;
			}
		} else {
			if ($Txt[$p]==='&') {
				$in = true;
			} else {
				$n++;
			}
		}
		if (($n>=$Nbr) || ($p>=$pMax)) {
			$ok = false;
		} else {
			$p++;
		}
	}

	if (($n>=$Nbr) && ($p<$pMax)) $Txt = substr($Txt,0,$p).$MaxEnd;

}

static function f_Xml_GetPart(&$Txt, $TagLst, $AllIfNothing=false) {
// Returns parts of the XML/HTML content, default is BODY.

	if (($TagLst===true) || ($TagLst==='')) $TagLst = 'body';

	$x = '';
	$nothing = true;
	$TagLst = explode('+',$TagLst);

	// Build a clean list of tags
	foreach ($TagLst as $i=>$t) {
		if ((substr($t,0,1)=='(') && (substr($t,-1,1)==')')) {
			$t = substr($t,1,strlen($t)-2);
			$Keep = true;
		} else {
			$Keep = false;
		}
		$TagLst[$i] = array('t'=>$t, 'k'=>$Keep, 'b'=>-1, 'e'=>-1, 's'=>false);
	}

	$PosOut = strlen($Txt);
	$Pos = 0;
	
	// Optimized search for all tag types
	do {

		// Search next positions of each tag type
		$TagMin = false;   // idx of the tag at first position
		$PosMin = $PosOut; // pos of the tag at first position
		foreach ($TagLst as $i=>$Tag) {
			if ($Tag['b']<$Pos) {
				$Loc = self::f_Xml_FindTag($Txt,$Tag['t'],true,$Pos,true,false,false);
				if ($Loc===false) {
					$Tag['b'] = $PosOut; // tag not found, no more search on this tag
				} else {
					$Tag['b'] = $Loc->PosBeg;
					$Tag['e'] = $Loc->PosEnd;
					$Tag['s'] = (substr($Txt,$Loc->PosEnd-1,1)==='/'); // true if it's a single tag
				}
				$TagLst[$i] = $Tag; // update
			}
			if ($Tag['b']<$PosMin) {
				$TagMin = $i;
				$PosMin = $Tag['b'];
			}
		}

		// Add the part of tag types
		if ($TagMin!==false) {
			$Tag = &$TagLst[$TagMin];
			$Pos = $Tag['e']+1;
			if ($Tag['s']) {
				// single tag
				if ($Tag['k']) $x .= substr($Txt,$Tag['b']  ,$Tag['e'] - $Tag['b'] + 1);
			} else {
				// search the closing tag
				$Loc = self::f_Xml_FindTag($Txt,$Tag['t'],false,$Pos,true,false,false);
				if ($Loc===false) {
					$Tag['b'] = $PosOut; // closing tag not found, no more search on this tag
				} else {
					$nothing = false;
					if ($Tag['k']) {
						$x .= substr($Txt,$Tag['b']  ,$Loc->PosEnd - $Tag['b'] + 1);
					} else {
						$x .= substr($Txt,$Tag['e']+1,$Loc->PosBeg - $Tag['e'] - 1);
					}
					$Pos = $Loc->PosEnd + 1;
				}
			}
		}

	} while ($TagMin!==false);
	
	if ($AllIfNothing && $nothing) return $Txt;
	return $x;

}

/**
 * Find the start of an XML tag. Used by OpenTBS.
 * $Case=false can be useful for HTML.
 * $Tag='' should work and found the start of the first tag.
 * $Tag='/' should work and found the start of the first closing tag.
 * Encapsulation levels are not featured yet.
 */
static function f_Xml_FindTagStart(&$Txt,$Tag,$Opening,$PosBeg,$Forward,$Case=true) {

	if ($Txt==='') return false;

	$x = '<'.(($Opening) ? '' : '/').$Tag;
	$xl = strlen($x);

	$p = $PosBeg - (($Forward) ? 1 : -1);

	if ($Case) {
		do {
			if ($Forward) $p = strpos($Txt,$x,$p+1);  else $p = strrpos(substr($Txt,0,$p+1),$x);
			if ($p===false) return false;
			/* COMPAT#6 */
			$z = substr($Txt,$p+$xl,1);
		} while ( ($z!==' ') && ($z!=="\r") && ($z!=="\n") && ($z!=='>') && ($z!=='/') && ($Tag!=='/') && ($Tag!=='') );
	} else {
		do {
			if ($Forward) $p = stripos($Txt,$x,$p+1);  else $p = strripos(substr($Txt,0,$p+1),$x);
			if ($p===false) return false;
			/* COMPAT#7 */
			$z = substr($Txt,$p+$xl,1);
		} while ( ($z!==' ') && ($z!=="\r") && ($z!=="\n") && ($z!=='>') && ($z!=='/') && ($Tag!=='/') && ($Tag!=='') );
	}

	return $p;

}

/**
 * This function is a smart solution to find an XML tag.
 * It allows to ignore full opening/closing couple of tags that could be inserted before the searched tag.
 * It allows also to pass a number of encapsulations.
 * To ignore encapsulation and opengin/closing just set $LevelStop=false.
 * $Opening is used only when $LevelStop=false.
 */
static function f_Xml_FindTag(&$Txt,$Tag,$Opening,$PosBeg,$Forward,$LevelStop,$WithPrm,$WithPos=false) {

	if ($Tag==='_') { // New line
		$p = self::f_Xml_FindNewLine($Txt,$PosBeg,$Forward,($LevelStop!==0));
		$Loc = new clsTbsLocator;
		$Loc->PosBeg = ($Forward) ? $PosBeg : $p;
		$Loc->PosEnd = ($Forward) ? $p : $PosBeg;
		$Loc->RightLevel = 0;
		return $Loc;
	}

	$Pos = $PosBeg + (($Forward) ? -1 : +1);
	$TagIsOpening = false;
	$TagClosing = '/'.$Tag;
	$LevelNum = 0;
	$TagOk = false;
	$PosEnd = false;
	$TagL = strlen($Tag);
	$TagClosingL = strlen($TagClosing);
	$RightLevel = 0;
	
	do {

		// Look for the next tag def
		if ($Forward) {
			$Pos = strpos($Txt,'<',$Pos+1);
		} else {
			if ($Pos<=0) {
				$Pos = false;
			} else {
				$Pos = strrpos(substr($Txt,0,$Pos - 1),'<'); // strrpos() syntax compatible with PHP 4
			}
		}

		if ($Pos!==false) {

			// Check the name of the tag
			if (strcasecmp(substr($Txt,$Pos+1,$TagL),$Tag)==0) {
				// It's an opening tag
				$PosX = $Pos + 1 + $TagL; // The next char
				$TagOk = true;
				$TagIsOpening = true;
			} elseif (strcasecmp(substr($Txt,$Pos+1,$TagClosingL),$TagClosing)==0) {
				// It's a closing tag
				$PosX = $Pos + 1 + $TagClosingL; // The next char
				$TagOk = true;
				$TagIsOpening = false;
			}

			if ($TagOk) {
				// Check the next char
				$x = $Txt[$PosX];
				if (($x===' ') || ($x==="\r") || ($x==="\n") || ($x==='>') || ($x==='/') || ($Tag==='/') || ($Tag==='')) {
					// Check the encapsulation count
					if ($LevelStop===false) { // No encapsulation check
						if ($TagIsOpening!==$Opening) $TagOk = false;
					} else { // Count the number of level
						if ($TagIsOpening) {
							$PosEnd = strpos($Txt,'>',$PosX);
							if ($PosEnd!==false) {
								if ($Txt[$PosEnd-1]==='/') {
									if (($Pos<$PosBeg) && ($PosEnd>$PosBeg)) {$RightLevel=1; $LevelNum++;}
								} else {
									$LevelNum++;
								}
							}
						} else {
							$LevelNum--;
						}
						// Check if it's the expected level
						if ($LevelNum!=$LevelStop) {
							$TagOk = false;
							$PosEnd = false;
						}
					}
				} else {
					$TagOk = false;
				}
			} //--> if ($TagOk)

		}
	} while (($Pos!==false) && ($TagOk===false));

	// Search for the end of the tag
	if ($TagOk) {
		$Loc = new clsTbsLocator;
		if ($WithPrm) {
			self::f_Loc_PrmRead($Txt,$PosX,true,'\'"','<','>',$Loc,$PosEnd,$WithPos);
		} elseif ($PosEnd===false) {
			$PosEnd = strpos($Txt,'>',$PosX);
			if ($PosEnd===false) {
				$TagOk = false;
			}
		}
	}

	// Result
	if ($TagOk) {
		$Loc->PosBeg = $Pos;
		$Loc->PosEnd = $PosEnd;
		$Loc->RightLevel = $RightLevel;
		return $Loc;
	} else {
		return false;
	}

}

static function f_Xml_FindNewLine(&$Txt,$PosBeg,$Forward,$IsRef) {

	$p = $PosBeg;
	if ($Forward) {
		$Inc = 1;
		$Inf = &$p;
		$Sup = strlen($Txt)-1;
	} else {
		$Inc = -1;
		$Inf = 0;
		$Sup = &$p;
	}

	do {
		if ($Inf>$Sup) return max($Sup,0);
		$x = $Txt[$p];
		if (($x==="\r") || ($x==="\n")) {
			$x2 = ($x==="\n") ? "\r" : "\n";
			$p0 = $p;
			if (($Inf<$Sup) && ($Txt[$p+$Inc]===$x2)) $p += $Inc; // Newline char can have two chars.
			if ($Forward) return $p; // Forward => return pos including newline char.
			if ($IsRef || ($p0!=$PosBeg)) return $p0+1; // Backwars => return pos without newline char. Ignore newline if it is the very first char of the search.
		}
		$p += $Inc;
	} while (true);

}

static function f_Xml_GetNextEntityName($Txt, $Pos, &$tag, &$PosBeg, &$p) {
/* 
 $tag : tag name
 $PosBeg : position of the tag
 $p   : position where the read has stop
 $z   : first char after the name
*/

	$tag = '';
	$PosBeg = strpos($Txt, '<', $Pos);
	
	if ($PosBeg===false) return false;
	
	// Read the name of the tag
	$go = true;
	$p = $PosBeg;
	while ($go) {
		$p++;
		$z = $Txt[$p];
		if ($go = ($z!==' ') && ($z!=="\r") && ($z!=="\n") && ($z!=='>') && ($z!=='/') ) {
			$tag .= $z;
		}
	}
	
	return true;
	
}

}


/**
 * @file
 * OpenTBS
 *
 * This TBS plug-in can open a zip file, read the central directory,
 * and retrieve the content of a zipped file which is not compressed.
 *
 * @version 1.9.10
 * @date 2017-07-05
 * @see     http://www.tinybutstrong.com/plugins.php
 * @author  Skrol29 http://www.tinybutstrong.com/onlyyou.html
 * @license LGPL-3.0
 */

/**
 * Constants to drive the plugin.
 */
define('OPENTBS_PLUGIN','clsOpenTBS');
define('OPENTBS_DOWNLOAD',1);   // download (default) = TBS_OUTPUT
define('OPENTBS_NOHEADER',4);   // option to use with DOWNLOAD: no header is sent
define('OPENTBS_FILE',8);       // output to file   = TBSZIP_FILE
define('OPENTBS_DEBUG_XML',16); // display the result of the current subfile
define('OPENTBS_STRING',32);    // output to string = TBSZIP_STRING
define('OPENTBS_DEBUG_AVOIDAUTOFIELDS',64); // avoit auto field merging during the Show() method
define('OPENTBS_INFO','clsOpenTBS.Info');       // command to display the archive info
define('OPENTBS_RESET','clsOpenTBS.Reset');      // command to reset the changes in the current archive
define('OPENTBS_ADDFILE','clsOpenTBS.AddFile');    // command to add a new file in the archive
define('OPENTBS_DELETEFILE','clsOpenTBS.DeleteFile'); // command to delete a file in the archive
define('OPENTBS_REPLACEFILE','clsOpenTBS.ReplaceFile'); // command to replace a file in the archive
define('OPENTBS_EDIT_ENTITY','clsOpenTBS.EditEntity'); // command to force an attribute
define('OPENTBS_FILEEXISTS','clsOpenTBS.FileExists');
define('OPENTBS_GET_FILES','clsOpenTBS.GetFiles');
define('OPENTBS_CHART','clsOpenTBS.Chart');
define('OPENTBS_CHART_INFO','clsOpenTBS.ChartInfo');
define('OPENTBS_DEFAULT','');   // Charset
define('OPENTBS_ALREADY_XML',false);
define('OPENTBS_ALREADY_UTF8','already_utf8');
define('OPENTBS_DEBUG_XML_SHOW','clsOpenTBS.DebugXmlShow');
define('OPENTBS_DEBUG_XML_CURRENT','clsOpenTBS.DebugXmlCurrent');
define('OPENTBS_DEBUG_INFO','clsOpenTBS.DebugInfo');
define('OPENTBS_DEBUG_CHART_LIST','clsOpenTBS.DebugInfo'); // deprecated
define('OPENTBS_FORCE_DOCTYPE','clsOpenTBS.ForceDocType');
define('OPENTBS_DELETE_ELEMENTS','clsOpenTBS.DeleteElements');
define('OPENTBS_SELECT_SHEET','clsOpenTBS.SelectSheet');
define('OPENTBS_SELECT_SLIDE','clsOpenTBS.SelectSlide');
define('OPENTBS_SELECT_MAIN','clsOpenTBS.SelectMain');
define('OPENTBS_DISPLAY_SHEETS','clsOpenTBS.DisplaySheets');
define('OPENTBS_DELETE_SHEETS','clsOpenTBS.DeleteSheets');
define('OPENTBS_DELETE_COMMENTS','clsOpenTBS.DeleteComments');
define('OPENTBS_MERGE_SPECIAL_ITEMS','clsOpenTBS.MergeSpecialItems');
define('OPENTBS_CHANGE_PICTURE','clsOpenTBS.ChangePicture');
define('OPENTBS_COUNT_SLIDES','clsOpenTBS.CountSlides');
define('OPENTBS_COUNT_SHEETS','clsOpenTBS.CountSheets');
define('OPENTBS_SEARCH_IN_SLIDES','clsOpenTBS.SearchInSlides');
define('OPENTBS_DISPLAY_SLIDES','clsOpenTBS.DisplaySlides');
define('OPENTBS_DELETE_SLIDES','clsOpenTBS.DeleteSlides');
define('OPENTBS_SELECT_FILE','clsOpenTBS.SelectFile');
define('OPENTBS_ADD_CREDIT','clsOpenTBS.AddCredit');
define('OPENTBS_SYSTEM_CREDIT','clsOpenTBS.SystemCredit');
define('OPENTBS_RELATIVE_CELLS','clsOpenTBS.RelativeCells');
define('OPENTBS_MAKE_OPTIMIZED_TEMPLATE','clsOpenTBS.MakeOptimizedTemplate');
define('OPENTBS_FIRST',1); // 
define('OPENTBS_GO',2);    // = TBS_GO
define('OPENTBS_ALL',4);   // = TBS_ALL
// Types of file to select
define('OPENTBS_GET_HEADERS_FOOTERS','clsOpenTBS.SelectHeaderFooter');
define('OPENTBS_SELECT_HEADER','clsOpenTBS.SelectHeader');
define('OPENTBS_SELECT_FOOTER','clsOpenTBS.SelectFooter');
// Sub-types of file
define('OPENTBS_EVEN',128);

/**
 * Main class which is a TinyButStrong plug-in.
 * It is also a extension of clsTbsZip so it can directly manage the archive underlying the template.
 */
class clsOpenTBS extends clsTbsZip {

	function OnInstall() {
		$TBS =& $this->TBS;

		if (!isset($TBS->OtbsAutoLoad))             $TBS->OtbsAutoLoad = true; // TBS will load the subfile regarding to the extension of the archive
		if (!isset($TBS->OtbsConvBr))               $TBS->OtbsConvBr = false;  // string for NewLine conversion
		if (!isset($TBS->OtbsAutoUncompress))       $TBS->OtbsAutoUncompress = $this->Meth8Ok;
		if (!isset($TBS->OtbsConvertApostrophes))   $TBS->OtbsConvertApostrophes = true;
		if (!isset($TBS->OtbsSpacePreserve))        $TBS->OtbsSpacePreserve = true;
		if (!isset($TBS->OtbsClearWriter))          $TBS->OtbsClearWriter = true;
		if (!isset($TBS->OtbsClearMsWord))          $TBS->OtbsClearMsWord = true;
		if (!isset($TBS->OtbsDeleteObsoleteChartData))    $TBS->OtbsDeleteObsoleteChartData = true;
		if (!isset($TBS->OtbsMsExcelConsistent))    $TBS->OtbsMsExcelConsistent = true;
		if (!isset($TBS->OtbsMsExcelExplicitRef))   $TBS->OtbsMsExcelExplicitRef = true;
		if (!isset($TBS->OtbsClearMsPowerpoint))    $TBS->OtbsClearMsPowerpoint = true;
		if (!isset($TBS->OtbsGarbageCollector))     $TBS->OtbsGarbageCollector = true;
		if (!isset($TBS->OtbsMsExcelCompatibility)) $TBS->OtbsMsExcelCompatibility = true;
		$this->Version = '1.9.10';
		$this->DebugLst = false; // deactivate the debug mode
		$this->ExtInfo = false;
		$TBS->TbsZip = &$this; // a shortcut
		return array('BeforeLoadTemplate','BeforeShow', 'OnCommand', 'OnOperation', 'OnCacheField');
	}

	function BeforeLoadTemplate(&$File,&$Charset) {

		$TBS =& $this->TBS;
		if ($TBS->_Mode!=0) return; // If we are in subtemplate mode, the we use the TBS default process

		if ($File === false) {
			// Close the current template if any
			@$this->Close();
			// Save memory space
			$this->TbsInitArchive();
			return false;
		}
		
		// Decompose the file path. The syntaxe is 'Archive.ext#subfile', or 'Archive.ext', or '#subfile'
		$FilePath = $File;
		$SubFileLst = false;
		if (is_string($File)) {
			$p = strpos($File, '#');
			if ($p!==false) {
				$FilePath = substr($File,0,$p);
				$SubFileLst = substr($File,$p+1);
			}
		}

		// Open the archive
		if ($FilePath!=='') {
			$ok = @$this->Open($FilePath);  // Open the archive
			if (!$ok) {
				if ($this->ArchHnd===false) {
					return $this->RaiseError("The template '".$this->ArchFile."' cannot be found.");
				} else {
					return false;
				}
			}
			$this->TbsInitArchive(); // Initialize other archive informations
			if ($TBS->OtbsAutoLoad && ($this->ExtInfo!==false) && ($SubFileLst===false)) {
				// auto load files from the archive
				$SubFileLst = $this->ExtInfo['load'];
				$TBS->OtbsConvBr = $this->ExtInfo['br'];
			}
			$TBS->OtbsSubFileLst = $SubFileLst;
		} elseif ($this->ArchFile==='') {
			$this->RaiseError('Cannot read file(s) "'.$SubFileLst.'" because no archive is opened.');
		}

		// Change the Charset if a new archive is opended, or if LoadTemplate is called explicitely for that
		if (($FilePath!=='') || ($File==='')) {
			if ($Charset===OPENTBS_ALREADY_XML) {
				$TBS->LoadTemplate('', false);                       // Define the function for string conversion
			} elseif ($Charset===OPENTBS_ALREADY_UTF8) {
				$TBS->LoadTemplate('', array(&$this,'ConvXmlOnly')); // Define the function for string conversion
			} else {
				$TBS->LoadTemplate('', array(&$this,'ConvXmlUtf8')); // Define the function for string conversion
			}
		}

		// Load the subfile(s)
		if (($SubFileLst!=='') && ($SubFileLst!==false)) {
			if (is_string($SubFileLst)) $SubFileLst = explode(';',$SubFileLst);
			$this->TbsLoadSubFileAsTemplate($SubFileLst);
		}

		if ($FilePath!=='') $TBS->_LastFile = $this->ArchFile;

		return false; // default LoadTemplate() process is not executed

	}

	function BeforeShow(&$Render, $File='') {

		$TBS =& $this->TBS;

		if ($this->ArchFile==='') {
			return $this->RaiseError('Command Show() cannot be processed because no archive is opened.');
		}

		if ($TBS->_Mode!=0) return; // If we are in subtemplate mode, the we use the TBS default process

		if ($this->TbsSystemCredits) {
			$this->Misc_EditCredits("OpenTBS " . $this->Version, true, true);
		}
		
		$this->TbsStorePark(); // Save the current loaded subfile if any

		$TBS->Plugin(-4); // deactivate other plugins

		$Debug = (($Render & OPENTBS_DEBUG_XML)==OPENTBS_DEBUG_XML);
		if ($Debug) $this->DebugLst = array();

		$TbsShow = (($Render & OPENTBS_DEBUG_AVOIDAUTOFIELDS)!=OPENTBS_DEBUG_AVOIDAUTOFIELDS);

		switch ($this->ExtEquiv) {
			case 'ods':  $this->OpenDoc_SheetSlides_DeleteAndDisplay(true); break;
			case 'odp':  $this->OpenDoc_SheetSlides_DeleteAndDisplay(false); break;
			case 'xlsx': $this->MsExcel_SheetDeleteAndDisplay(); break;
			case 'pptx': $this->MsPowerpoint_SlideDelete(); break;
		}
		
		$explicitRef = ($TBS->OtbsMsExcelExplicitRef && ($this->ExtEquiv==='xlsx'));
		
		// Merges all modified subfiles
		$idx_lst = array_keys($this->TbsStoreLst);
		foreach ($idx_lst as $idx) {
			$TBS->Source = $this->TbsStoreLst[$idx]['src'];
			$onshow = $this->TbsStoreLst[$idx]['onshow'];
			unset($this->TbsStoreLst[$idx]); // save memory space
			$TBS->OtbsCurrFile = $this->TbsGetFileName($idx); // usefull for TbsPicAdd()
			$this->TbsCurrIdx = $idx; // usefull for debug mode
			if ($TbsShow && $onshow) $TBS->Show(TBS_NOTHING);
			if ($this->ExtEquiv == 'docx') {
				$this->MsWord_RenumDocPr($TBS->Source);
			}
			if ($explicitRef && (!isset($this->MsExcel_KeepRelative[$idx])) ) {
				$this->MsExcel_ConvertToExplicit($TBS->Source);
			}
			if ($Debug) $this->DebugLst[$this->TbsGetFileName($idx)] = $TBS->Source;
			$this->FileReplace($idx, $TBS->Source, TBSZIP_STRING, $TBS->OtbsAutoUncompress);
		}
		$TBS->Plugin(-10); // reactivate other plugins
		$this->TbsCurrIdx = false;

		if ($this->OpenXmlCTypes!==false) $this->OpenXML_CTypesCommit($Debug);    // Commit special OpenXML features if any
		if ($this->OpenDocManif!==false)  $this->OpenDoc_ManifestCommit($Debug);  // Commit special OpenDocument features if any
		if ($this->OpenXmlRid!==false) $this->OpenXML_Rels_CommitNewRids($Debug); // Must be done also after the loop because some Rid can be added with [onshow]
		
		if ($TBS->OtbsGarbageCollector) {
			if ($this->ExtType=='openxml') $this->OpenMXL_GarbageCollector();
		}

		if ( ($TBS->ErrCount>0) && (!$TBS->NoErr) && (!$Debug)) {
			$TBS->meth_Misc_Alert('Show() Method', 'The output is cancelled by the OpenTBS plugin because at least one error has occured.');
			exit;
		}

		if ($Debug) {
			// Do the debug even if other options are used
			$this->TbsDebug_Merge(true, false);
		} elseif (($Render & TBS_OUTPUT)==TBS_OUTPUT) { // notice that TBS_OUTPUT = OPENTBS_DOWNLOAD
			// download
			$ContentType = (isset($this->ExtInfo['ctype'])) ? $this->ExtInfo['ctype'] : '';
			$this->Flush($Render, $File, $ContentType); // $Render is used because it can contain options OPENTBS_DOWNLOAD and OPENTBS_NOHEADER.
			$Render = $Render - TBS_OUTPUT; //prevent TBS from an extra output.
		} elseif(($Render & OPENTBS_FILE)==OPENTBS_FILE) {
			// to file
			$this->Flush(TBSZIP_FILE, $File);
		} elseif(($Render & OPENTBS_STRING)==OPENTBS_STRING) {
			// to string
			$this->Flush(TBSZIP_STRING);
			$TBS->Source = $this->OutputSrc;
			$this->OutputSrc = '';
		}

		if (($Render & TBS_EXIT)==TBS_EXIT) {
			$this->Close();
			exit;
		}

		return false; // cancel the default Show() process

	}

	function OnCacheField($BlockName,&$Loc,&$Txt,$PrmProc) {

		if (isset($Loc->PrmLst['ope'])) {

			$ope_lst = explode(',', $Loc->PrmLst['ope']); // in this event, ope is not exploded

			// Prepare to change picture
			if (in_array('changepic', $ope_lst)) {
				$this->TbsPicPrepare($Txt, $Loc, true); // add parameter "att" which will be processed just after this event, when the field is cached
			} elseif (in_array('mergecell', $ope_lst)) {
				$this->TbsPrepareMergeCell($Txt, $Loc);
			}

			// Change cell type
			foreach($ope_lst as $ope) {
				$x = substr($ope,0,4);
				if( ($x==='tbs:') || ($x==='xlsx') || (substr($ope,0,3)==='ods') ) {
					if ($this->ExtEquiv==='ods') {
						$z = '';
						$this->OpenDoc_ChangeCellType($Txt, $Loc, $ope, false, $z);
					} elseif ($this->ExtEquiv==='xlsx') {
						$this->MsExcel_ChangeCellType($Txt, $Loc, $ope);
					}
					return; // do only one change
				}
			}

		}

	}

	function OnOperation($FieldName,&$Value,&$PrmLst,&$Txt,$PosBeg,$PosEnd,&$Loc) {
	// in this event, ope is exploded, there is one function call for each ope command
		$ope = $PrmLst['ope'];
		if ($ope==='addpic') {
			// for compatibility
			$this->TbsPicAdd($Value, $PrmLst, $Txt, $Loc, 'ope=addpic');
		} elseif ($ope==='changepic') {
			$this->TbsPicPrepare($Txt, $Loc, false);
			$this->TbsPicAdd($Value, $PrmLst, $Txt, $Loc, 'ope=changepic');
		} elseif ($ope==='delcol') {
			// Delete the TBS field otherwise ? return false ? will produce a TBS error ? doesn't have any subname ? with [onload] fields.
			$Txt = substr_replace($Txt, '', $PosBeg, $PosEnd - $PosBeg + 1);
			$this->TbsDeleteColumns($Txt, $Value, $PrmLst, $PosBeg);
			return false; // prevent TBS from actually merging the field
		} elseif ($ope==='mergecell') {
			if (isset($this->PrevVals[$Loc->FullName])) {
				if ($Value==$this->PrevVals[$Loc->FullName]) {
					$Value = '<w:vMerge w:val="continue"/>';
				} else {
					$this->PrevVals[$Loc->FullName] = $Value;
					$Value = '<w:vMerge w:val="restart"/>';
				}
			}
		} else {
			$x = substr($ope,0,4);
			if( ($x==='tbs:') || ($x==='xlsx') || (substr($ope,0,3)==='ods') ) {
				if ($this->ExtEquiv==='ods') {
					if (!isset($Loc->PrmLst['cellok'])) $this->OpenDoc_ChangeCellType($Txt, $Loc, $ope, true, $Value);
				} elseif ($this->ExtEquiv==='xlsx') {
					if (!isset($Loc->PrmLst['cellok'])) $this->MsExcel_ChangeCellType($Txt, $Loc, $ope);
					$this->MsExcel_ChangeCellValue($Loc, $Value);
				}
			}
		}
	}

	function OnCommand($Cmd, $x1=null, $x2=null, $x3=null, $x4=null, $x5=null) {

		if ($Cmd==OPENTBS_INFO) {
			// Display debug information
			echo "<strong>OpenTBS plugin Information</strong><br>\r\n";
			return $this->Debug();
		} elseif ( ($Cmd==OPENTBS_DEBUG_INFO) || ($Cmd==OPENTBS_DEBUG_CHART_LIST) ) {
			if (is_null($x1)) $x1 = true;
			$this->TbsDebug_Info($x1);
			return true;
		}

		if($Cmd==OPENTBS_MAKE_OPTIMIZED_TEMPLATE) {
				
			// save options
			$s_onload = $this->TBS->GetOption('onload');
			$s_onshow = $this->TBS->GetOption('onshow');
			
			// change options
			$this->TBS->SetOption('onload', false);
			$this->TBS->SetOption('onshow', false);
			
			// load the template
			$this->TBS->LoadTemplate($x1);
			
			if ($this->ExtEquiv == 'xlsx') {
				// load all sheets
				$this->MsExcel_SheetInit();
				foreach($this->MsExcel_Sheets as $o) {
					$this->TbsLoadSubFileAsTemplate('xl/'.$o->file);
				}
			} elseif ($this->ExtEquiv == 'pptx') {
				// load all slides
				$this->MsPowerpoint_InitSlideLst();
				foreach ($this->OpenXmlSlideLst as $s) {
					$this->TbsLoadSubFileAsTemplate($s['file']);
				}
			}

			// save the result
			$this->TBS->Show(OPENTBS_FILE + OPENTBS_DEBUG_AVOIDAUTOFIELDS, $x2);
			
			// restore options
			$this->TBS->SetOption('onload', $s_onload);
			$this->TBS->SetOption('onshow', $s_onshow);
			
			return true;
			
		}
		
		// Check that a template is loaded
		if ($this->ExtInfo===false) {
			$this->RaiseError("Cannot execute the plug-in commande because no template is loaded.");
			return true;
		}
		
		if ($Cmd==OPENTBS_RESET) {

			// Reset all mergings
			$this->ArchCancelModif();
			$this->TbsStoreLst = array();
			$TBS =& $this->TBS;
			$TBS->Source = '';
			$TBS->OtbsCurrFile = false;
			if (is_string($TBS->OtbsSubFileLst)) {
				$f = '#'.$TBS->OtbsSubFileLst;
				$h = '';
				$this->BeforeLoadTemplate($f,$h);
			}
			return true;

		} elseif ($Cmd==OPENTBS_SELECT_FILE) {
		
			// Raise an error is the file is not found
			return $this->TbsLoadSubFileAsTemplate($x1);
		
		} elseif ( ($Cmd==OPENTBS_ADDFILE) || ($Cmd==OPENTBS_REPLACEFILE) ) {

			// Add a new file or cancel a previous add
			$Name = (is_null($x1)) ? false : $x1;
			$Data = (is_null($x2)) ? false : $x2;
			$DataType = (is_null($x3)) ? TBSZIP_STRING : $x3;
			$Compress = (is_null($x4)) ? true : $x4;

			if ($Cmd==OPENTBS_ADDFILE) {
				return $this->FileAdd($Name, $Data, $DataType, $Compress);
			} else {
				return $this->FileReplace($Name, $Data, $DataType, $Compress);
			}

		} elseif ($Cmd==OPENTBS_DELETEFILE) {

			// Delete an existing file in the archive
			$Name = (is_null($x1)) ? false : $x1;
			$this->FileCancelModif($Name, false);    // cancel added files
			return $this->FileReplace($Name, false); // mark the file as to be deleted

		} elseif ($Cmd==OPENTBS_FILEEXISTS) {

			return $this->FileExists($x1);

		} elseif ($Cmd==OPENTBS_CHART) {

			$ChartRef = $x1;
			$SeriesNameOrNum = $x2;
			$NewValues = (is_null($x3)) ? false : $x3;
			$NewLegend = (is_null($x4)) ? false : $x4;

			if ($this->ExtType=='odf') {
				return $this->OpenDoc_ChartChangeSeries($ChartRef, $SeriesNameOrNum, $NewValues, $NewLegend);
			} else {
				return $this->OpenXML_ChartChangeSeries($ChartRef, $SeriesNameOrNum, $NewValues, $NewLegend);
			}
		} elseif ($Cmd==OPENTBS_CHART_INFO) {

			$ChartRef = $x1;
			$Complete = $x2;
			
			if ($this->ExtType=='odf') {
				return $this->OpenDoc_ChartReadSeries($ChartRef, $Complete);
			} else {
				return $this->OpenXML_ChartReadSeries($ChartRef, $Complete);
			}
			
			
		} elseif ($Cmd==OPENTBS_DEBUG_XML_SHOW) {

			$this->TBS->Show(OPENTBS_DEBUG_XML);

		} elseif ($Cmd==OPENTBS_DEBUG_XML_CURRENT) {

			$this->TbsStorePark();
			$this->DebugLst = array();
			foreach ($this->TbsStoreLst as $idx=>$park) $this->DebugLst[$this->TbsGetFileName($idx)] = $park['src'];
			$this->TbsDebug_Merge(true, true);

			if (is_null($x1) || $x1) exit();

		} elseif($Cmd==OPENTBS_FORCE_DOCTYPE) {

			return $this->Ext_PrepareInfo($x1);

		} elseif ($Cmd==OPENTBS_DELETE_ELEMENTS) {

			if (is_string($x1)) $x1 = explode(',', $x1);
			if (is_null($x2)) $x2 = false; // OnlyInner
			return $this->XML_DeleteElements($this->TBS->Source, $x1, $x2);

		} elseif ($Cmd==OPENTBS_SELECT_MAIN) {

			if ( ($this->ExtInfo!==false) && isset($this->ExtInfo['main']) ) {
				$this->TbsLoadSubFileAsTemplate($this->ExtInfo['main']);
				return true;
			} else {
				return false;
			}

		} elseif ($Cmd==OPENTBS_SELECT_SHEET) {

			if ($this->ExtEquiv=='ods') {
				$this->TbsLoadSubFileAsTemplate($this->ExtInfo['main']);
				return true;
			}

			// Only XLSX files have sheets in separated subfiles.
			if ($this->ExtEquiv==='xlsx') {
				$o = $this->MsExcel_SheetGet($x1, $x2);
				if ($o===false) return;
				if ($o->file===false) return $this->RaiseError("($Cmd) Error with sheet '$x1'. The corresponding XML subfile is not referenced.");
				return $this->TbsLoadSubFileAsTemplate('xl/'.$o->file);
			}
			
			return false;

		} elseif ( ($Cmd==OPENTBS_DELETE_SHEETS) || ($Cmd==OPENTBS_DISPLAY_SHEETS) || ($Cmd==OPENTBS_DELETE_SLIDES) || ($Cmd==OPENTBS_DISPLAY_SLIDES) ) {

			$delete = ( ($Cmd==OPENTBS_DELETE_SHEETS) || ($Cmd==OPENTBS_DELETE_SLIDES) ) ;
			$this->TbsSheetSlide_DeleteDisplay($x1, $x2, $delete);

		} elseif ($Cmd==OPENTBS_MERGE_SPECIAL_ITEMS) {

			if ($this->ExtEquiv!='xlsx') return 0;
			$lst = $this->MsExcel_GetDrawingLst();
			$this->TbsQuickLoad($lst);

		} elseif ($Cmd==OPENTBS_SELECT_SLIDE) {

			if ($this->ExtEquiv=='odp') {
				$this->TbsLoadSubFileAsTemplate($this->ExtInfo['main']);
				return true;
			}
			
			if ($this->ExtEquiv!='pptx') return false;

			$master  = (is_null($x2)) ? false : $x2;
			$slide = ($master) ? 'slide master' : 'slide';
			$RefLst = $this->MsPowerpoint_InitSlideLst($master);

			$s = intval($x1)-1;
			if (isset($RefLst[$s])) {
				$this->TbsLoadSubFileAsTemplate($RefLst[$s]['idx']);
				return true;
			} else {
				return $this->RaiseError("($Cmd) $slide number $x1 is not found inside the Presentation.");
			}

		} elseif ($Cmd==OPENTBS_DELETE_COMMENTS) {

			// Default values
			$MainTags = false;
			$CommFiles = false;
			$CommTags = false;
			$Inner = false;

			if ($this->ExtType=='odf') {
				$MainTags = array('office:annotation', 'officeooo:annotation'); // officeooo:annotation is used in ODP Presentations
			} else {
				switch ($this->ExtEquiv) {
				case 'docx':
					$MainTags = array('w:commentRangeStart', 'w:commentRangeEnd', 'w:commentReference');
					$CommFiles = array('wordprocessingml.comments+xml');
					$CommTags = array('w:comment');
					$Inner = true;
					break;
				case 'xlsx':
					$CommFiles = array('spreadsheetml.comments+xml');
					$CommTags = array('comment', 'author');
					break;
				case 'pptx':
					$CommFiles = array('presentationml.comments+xml');
					$CommTags = array('p:cm');
					break;
				default:
					return 0;
				}
			}

			return $this->TbsDeleteComments($MainTags, $CommFiles, $CommTags, $Inner);

		} elseif ($Cmd==OPENTBS_CHANGE_PICTURE) {

			static $UniqueId = 0;

			$code = $x1;
			$file = $x2;
			$prms = array('default'=>'current', 'adjust' => 'inside');
			if (is_array($x3)) {
				$prms = array_merge($prms, $x3);
			} else {
				// Compatibility v <= 1.9.0
				if (!is_null($x3)) $prms['default'] = $x3;
				if (!is_null($x4)) $prms['adjust'] = $x4;
			}
			$prms_flat = array();
			foreach($prms as $p => $v) $prms_flat[] = $p.'='.$v;
			$prms_flat = implode(';', $prms_flat);
			
			$UniqueId++;
			$name = 'OpenTBS_Change_Picture_'.$UniqueId;
			$tag = "[$name;ope=changepic;tagpos=inside;$prms_flat]";

			$nbr = false;
			$TBS =& $this->TBS; 
			$TBS->Source = str_replace($code, $tag, $TBS->Source, $nbr); // argument $nbr supported buy PHP >= 5
			if ($nbr!==0) $TBS->MergeField($name, $file);

			return $nbr;

		} elseif ($Cmd==OPENTBS_COUNT_SLIDES) {

			$master  = (is_null($x1)) ? false : $x1;
			
			if ($this->ExtEquiv=='pptx') {
				$RefLst = $this->MsPowerpoint_InitSlideLst($master);
				return count($RefLst);
			} elseif ($this->ExtEquiv=='odp') {
				$idx = $this->Ext_GetMainIdx();
				$txt = $this->TbsStoreGet($idx, "Command OPENTBS_COUNT_SLIDES");
				return substr_count($txt, '</draw:page>');
			} else {
				return 0;
			}
			
		} elseif ($Cmd==OPENTBS_COUNT_SHEETS) {

			if ($this->ExtEquiv=='xlsx') {
				$this->MsExcel_SheetInit();
				return count($this->MsExcel_Sheets);
			} elseif ($this->ExtEquiv=='ods') {
				$idx = $this->Ext_GetMainIdx();
				$txt = $this->TbsStoreGet($idx, "Command OPENTBS_COUNT_SHEETS");
				return substr_count($txt, '</table:table>');
			} else {
				return 0;
			}
			
		} elseif ($Cmd==OPENTBS_SEARCH_IN_SLIDES) {

			if ($this->ExtEquiv=='pptx') {
				$option = (is_null($x2)) ? OPENTBS_FIRST : $x2;
				$returnFirstFound = (($option & OPENTBS_ALL)!=OPENTBS_ALL);
				$find = $this->MsPowerpoint_SearchInSlides($x1, $returnFirstFound);
				if ($returnFirstFound) {
					$slide = $find['key'];
					if ( ($slide!==false) && (($option & OPENTBS_GO)==OPENTBS_GO) ) $this->OnCommand(OPENTBS_SELECT_SLIDE, $slide);
					return ($slide);
				} else {
					$res = array();
					foreach($find as $f) $res[] = $f['key'];
					return $res;
				}
			} elseif ($this->ExtEquiv=='odp') {
				// Only for compatibility
				$p = instr($TBS->Source, $str);
				return ($p===false) ? false : 1;
			} else {
				return false;
			}
			
		} elseif ( ($Cmd==OPENTBS_SELECT_HEADER) || ($Cmd==OPENTBS_SELECT_FOOTER) ) {
		
			$file = false;

			switch ($this->ExtEquiv) {
			case 'docx':
				$x2 = intval($x2); // 0 by default
				$file = $this->MsWord_GetHeaderFooterFile($Cmd, $x1, $x2);
				break;
			case 'odt': case 'ods': case 'odp':
				$file = $this->ExtInfo['main'];
			case 'xlsx': case 'pptx': 
				return false;
				break;
			}
			
			return $this->TbsLoadSubFileAsTemplate($file);
		
		} elseif ($Cmd==OPENTBS_GET_HEADERS_FOOTERS) {

			$res = array();
		
			switch ($this->ExtEquiv) {
			case 'docx':
				$this->MsWord_InitHeaderFooter();
				foreach ($this->MsWord_HeaderFooter as $info) {
					$res[] = $info['file'];
				}				
				break;
			case 'odt': case 'ods': case 'odp':
				// Headers and footers are in the main file.
				// Handout headers and footers for presentations (PPTX & ODP) are not supported for now.
				if (isset($this->ExtInfo['main'])) $res[] = $this->ExtInfo['main'];
			case 'xlsx':
				$FileName = $this->CdFileLst[$this->TbsCurrIdx];
				if ($this->MsExcel_SheetIsIt($FileName) ) $res[] = $FileName;
				break;
			case 'pptx': 
				// Headers and footers are in the selected sheet or slide.
				$FileName = $this->CdFileLst[$this->TbsCurrIdx];
				if ($this->MsPowerpoint_SlideIsIt($FileName) ) $res[] = $FileName;
				break;
			}
			
			return $res;
		
		} elseif ($Cmd==OPENTBS_SYSTEM_CREDIT) {

			$x1 = (boolean) $x1;
			$this->TbsSystemCredits = $x1;
			return $x1;

		} elseif ($Cmd==OPENTBS_ADD_CREDIT) {

			return $this->Misc_EditCredits($x1, true, false, $x2);
			
		} elseif ($Cmd==OPENTBS_RELATIVE_CELLS) {

			$KeepRelative = (boolean) $x1;
			if ($x2 == OPENTBS_ALL) {
				// Al$ sheets
				$this->TBS->OtbsMsExcelExplicitRef = (!$KeepRelative);
			} else {
				// Current sheet
				if ($KeepRelative) {
					$this->MsExcel_KeepRelative[$this->TbsCurrIdx] = true;
				} else {
					unset($this->MsExcel_KeepRelative[$this->TbsCurrIdx]);
				}
			}
			return $KeepRelative;
			
		} elseif ($Cmd==OPENTBS_EDIT_ENTITY) {
			
			$AddElIfMissing = (boolean) $x5;
			return $this->XML_ForceAtt($x1, $x2, $x3, $x4, $AddElIfMissing);
			
		} elseif ($Cmd==OPENTBS_GET_FILES) {
	
			$files = array();
			// All files in the archive
			foreach ($this->CdFileLst as $f) {
				$files[] = $f['v_name'];
			}
			return $files;
	
		}

	}

	// Initialize template information
	function TbsInitArchive() {

		$TBS =& $this->TBS;

		$TBS->OtbsCurrFile = false;

		$this->TbsStoreLst = array();
		$this->TbsCurrIdx = false;
		$this->TbsSystemCredits = true;
		$this->TbsNoField = array(); // idx of sub-file having no TBS fields
		$this->IdxToCheck = array(); // index of files to check
		$this->PrevVals = array(); // Previous values for 'mergecell' operator

		$this->ImageIndex = 1;          // Serial for inserted images
		$this->ImageInternal = array(); // Internal names of inserted image

		$this->ExtEquiv = false;
		$this->ExtType = false;
		
		$this->OtbsSheetSlidesDelete = array();
		$this->OtbsSheetSlidesVisible = array();

		$this->OpenDocCharts = false;
		$this->OpenDocManif = false;
		$this->OpenDoc_SheetSlides = false;
		$this->OpenDoc_Styles = false;

		$this->OpenXmlRid = false;
		$this->OpenXmlCTypes = false;
		$this->OpenXmlCharts = false;
		$this->OpenXmlSharedStr = false;
		$this->OpenXmlSlideLst = false;
		$this->OpenXmlSlideMasterLst = false;
		$this->MsExcel_Sheets = false;
		$this->MsExcel_NoTBS = array(); // shared string containing no TBS field
		$this->MsExcel_KeepRelative = array();
		$this->MsWord_HeaderFooter = false;
		$this->MsWord_DocPrId = 0;

		$this->Ext_PrepareInfo(); // Set extension information

	}

	/**
	 * Load one or several sub-files of the archive as the current template.
	 * If a sub-template is loaded for the first time, then automatic merges and clean-up are performed.
	 * Return true if the file is correctly loaded.
	 * @param $SubFileLst Can be an index or a name or a file, or an array of such values.
	 */
	function TbsLoadSubFileAsTemplate($SubFileLst) {

		if (!is_array($SubFileLst)) $SubFileLst = array($SubFileLst);

		$ok = true;
		$TBS = false;

		foreach ($SubFileLst as $SubFile) {

			$idx = $this->FileGetIdx($SubFile);
			if ($idx===false) {
				$ok = $this->RaiseError('Cannot load "'.$SubFile.'". The file is not found in the archive "'.$this->ArchFile.'".');
			} elseif ($idx!==$this->TbsCurrIdx) {
				// Save the current loaded subfile if any
				$this->TbsStorePark();
				// Load the subfile
				if (!is_string($SubFile)) $SubFile = $this->TbsGetFileName($idx);
				$this->TbsStoreLoad($idx, $SubFile);
				if ($this->LastReadNotStored) {
					// Loaded for the first time
					if ($TBS===false) {
						$this->TbsSwitchMode(true); // Configuration which prevents from other plug-ins when calling LoadTemplate()
						$MergeAutoFields = $this->TbsMergeAutoFields();
						$TBS =& $this->TBS;
					}
					if ($this->LastReadComp<=0) { // the contents is not compressed
						if ($this->ExtInfo!==false) {
							$i = $this->ExtInfo;
							$e = $this->ExtEquiv;
							if ($this->TbsApplyOptim($TBS->Source, true)) {
								if (isset($i['rpl_what'])) {
									// auto replace strings in the loaded file
									$TBS->Source = str_replace($i['rpl_what'], $i['rpl_with'], $TBS->Source);
								}
								if (($e==='odt') && $TBS->OtbsClearWriter) {
									$this->OpenDoc_CleanRsID($TBS->Source);
								}
								if (($e==='ods') && $TBS->OtbsMsExcelCompatibility) {
									$this->OpenDoc_atExcelCompatibility($TBS->Source);
								}
								if ($e==='docx') {
									if ($TBS->OtbsSpacePreserve) $this->MsWord_CleanSpacePreserve($TBS->Source);
									if ($TBS->OtbsClearMsWord) $this->MsWord_Clean($TBS->Source);
								}
								if (($e==='pptx') && $TBS->OtbsClearMsPowerpoint) {
									$this->MsPowerpoint_Clean($TBS->Source);
								}
								if (($e==='xlsx') && $TBS->OtbsMsExcelConsistent) {
									$this->MsExcel_DeleteFormulaResults($TBS->Source);
									$this->MsExcel_ConvertToRelative($TBS->Source);
								}
							}
						}
						// apply default TBS behaviors on the uncompressed content: other plug-ins + [onload] fields
						if ($MergeAutoFields) $TBS->LoadTemplate(null,'+');
					}
				}
			}

		}

		if ($TBS!==false) $this->TbsSwitchMode(false); // Reactivate default configuration
		
		return $ok;
		
	}

	// Return true if automatic fields must be merged
	function TbsMergeAutoFields() {
		return (($this->TBS->Render & OPENTBS_DEBUG_AVOIDAUTOFIELDS)!=OPENTBS_DEBUG_AVOIDAUTOFIELDS);
	}

	function TbsSwitchMode($PluginMode) {
		$TBS = &$this->TBS;
		if ($PluginMode) {
			$this->_ModeSave = $TBS->_Mode;
			$TBS->_Mode++;    // deactivate TplVars[] reset and Charset reset.
			$TBS->Plugin(-4); // deactivate other plugins
		} else {
			// Reactivate default configuration
			$TBS->_Mode = $this->_ModeSave;
			$TBS->Plugin(-10); // reactivate other plugins
		}
	}

	// Save the last opened subfile into the store, and close the subfile
	function TbsStorePark() {
		if ($this->TbsCurrIdx!==false) {
			$this->TbsStoreLst[$this->TbsCurrIdx] = array('src'=>$this->TBS->Source, 'onshow'=>true);
			$this->TBS->Source = '';
			$this->TbsCurrIdx = false;
		}
	}

	// Load a subfile from the store to be the current subfile
	function TbsStoreLoad($idx, $file=false) {
		$this->TBS->Source = $this->TbsStoreGet($idx, false);
		$this->TbsCurrIdx = $idx;
		if ($file===false) $file = $this->TbsGetFileName($idx);
		$this->TBS->OtbsCurrFile = $file;
	}

	/**
	 * Save a given source in the store.
	 * $onshow=true means [onshow] are merged before the output. 
	 * If $onshow is null, then the 'onshow' option stays unchanged.
	 */
	function TbsStorePut($idx, $src, $onshow = null) {
		if ($idx===$this->TbsCurrIdx) {
			$this->TBS->Source = $src;
		} else {
			if (is_null($onshow)) {
				if (isset($this->TbsStoreLst[$idx])) {
					$onshow = $this->TbsStoreLst[$idx]['onshow'];
				} else {
					$onshow = false;
				}
			}
			$this->TbsStoreLst[$idx] = array('src'=>$src, 'onshow'=>$onshow);
		}
	}

	/**
	 * Return a source from the current merging, the store, or the archive.
	 * Take care that if the source it taken from the archive, then it is not saved in the store.
	 * @param {integer} $idx The index of the file to read.
	 * @param {string|false} $caller A text describing the calling function, for error reporting purpose. If caller=false it means TbsStoreLoad().
	 */
	function TbsStoreGet($idx, $caller) {
		$this->LastReadNotStored = false;
		if ($idx===$this->TbsCurrIdx) {
			return $this->TBS->Source;
		} elseif (isset($this->TbsStoreLst[$idx])) {
			$txt = $this->TbsStoreLst[$idx]['src'];
			if ($caller===false) $this->TbsStoreLst[$idx]['src'] = ''; // save memory space
			return $txt;
		} else {
			$this->LastReadNotStored = true;
			$txt = $this->FileRead($idx, true);
			if ($this->LastReadComp>0) {
				if ($caller===false) {
					return $txt; // return the uncompressed contents
				} else {
					return $this->RaiseError("(".$caller.") unable to uncompress '".$this->TbsGetFileName($idx)."'.");
				}
			} else {
				return $txt;
			}
		}
	}

	// Load a list of sub-files, but only if they have TBS fields.
	// This is in order to merge automatic fields in special XML sub-files that are not usually loaded manually.
	function TbsQuickLoad($NameLst) {

		if (!is_array($NameLst)) $NameLst = array($NameLst);
		$nbr = 0;
		$TBS = &$this->TBS;

		foreach ($NameLst as $FileName) {
			$idx = $this->FileGetIdx($FileName);
			if ( (!isset($this->TbsStoreLst[$idx])) && (!isset($this->TbsNoField[$idx])) ) {
				$txt = $this->FileRead($idx, true);
				if (strpos($txt, $TBS->_ChrOpen)!==false) {
					// merge
					$nbr++;
					if ($nbr==1) {
						$MergeAutoFields = $this->TbsMergeAutoFields();
						$SaveIdx = $this->TbsCurrIdx; // save the index of sub-file before the QuickLoad
						$SaveName = $TBS->OtbsCurrFile;
						$this->TbsSwitchMode(true);
					}
					$this->TbsStorePark(); // save the current file in the store
					$TBS->Source = $txt;
					unset($txt);
					$TBS->OtbsCurrFile = $FileName; // may be needed for [onload] parameters
					$this->TbsCurrIdx = $idx;
					if ($MergeAutoFields) $TBS->LoadTemplate(null,'+');
				} else {
					$this->TbsNoField[$idx] = true;
				}
			}
		}

		if ($nbr>0) {
			$this->TbsSwitchMode(false);
			$this->TbsStorePark(); // save the current file in the store
			$this->TbsStoreLoad($SaveIdx, $SaveName); // restore the sub-file as before the QuickLoad
		}

		return $nbr;

	}

	function TbsGetFileName($idx) {
		if (isset($this->CdFileLst[$idx])) {
			return $this->CdFileLst[$idx]['v_name'];
		} else {
			return '(id='.$idx.')';
		}
	}

	/**
	 * Tells if optimisation marker is prensent in the current source, eventually add it if it is not.
	 * The optimization marker is a simple space (' ') before the closing chars of the "<? ?>" element.
	 * @param  string  $Txt  The text source to check
	 * @param  boolean $mark Set to true to mark the source as done if it is not the case.
	 * @return boolean True if the current source has just been marked done. Null if it is not possible to telle if it is done or note. Fasle if is is done before.
	 */
	function TbsApplyOptim(&$Txt, $mark) {
		if (substr($Txt, 0, 2) === '<?') {
			$p = strpos($Txt, '?>');
			if (substr($Txt, $p-1, 1) === ' ') {
				return false;
			} else {
				if ($mark) {
					$Txt = substr_replace($Txt, ' ', $p, 0);
				}
				return true;
			}
		} else {
			return null;
		}
	}
	
	/**
	 * Display the header of the debug mode (only once)
	 */
	function TbsDebug_Init(&$nl, &$sep, &$bull, $type) {

		static $DebugInit = false;

		if ($DebugInit) return;
		$DebugInit = true;

		$nl = "\n";
		$sep = str_repeat('-',30);
		$bull = $nl.'  - ';


		if (!headers_sent()) header('Content-Type: text/plain; charset="UTF-8"');

		echo "* OPENTBS DEBUG MODE: if the star, (*) on the left before the word OPENTBS, is not the very first character of this page, then your
merged Document will be corrupted when you use the OPENTBS_DOWNLOAD option. If there is a PHP error message, then you have to fix it.
If they are blank spaces, line beaks, or other unexpected characters, then you have to check your code in order to avoid them.";
		echo $nl;
		echo $nl.$sep.$nl.'INFORMATION'.$nl.$sep;
		echo $nl.'* Debug command: '.$type;
		echo $nl.'* OpenTBS version: '.$this->Version;
		echo $nl.'* TinyButStrong version: '.$this->TBS->Version;
		echo $nl.'* PHP version: '.PHP_VERSION;
		echo $nl.'* Zlib enabled: '.($this->Meth8Ok) ? 'YES' : 'NO (it should be enabled)';
		echo $nl.'* Opened document: '.(($this->ArchFile==='') ? '(none)' : $this->ArchFile);
		echo $nl.'* Activated features for document type: '.(($this->ExtInfo===false) ? '(none)' : $this->ExtType.'/'.$this->ExtEquiv);

	}

	function TbsDebug_Info($Exit) {

		$this->TbsDebug_Init($nl, $sep, $bull, 'OPENTBS_DEBUG_INFO');

		if ($this->ExtInfo !== false) {
		
			switch ($this->ExtEquiv) {
			case 'docx': $this->MsWord_DocDebug($nl, $sep, $bull); break;
			case 'xlsx': $this->MsExcel_SheetDebug($nl, $sep, $bull); break;
			case 'pptx': $this->MsPowerpoint_SlideDebug($nl, $sep, $bull); break;
			case 'ods' : $this->OpenDoc_SheetSlides_Debug(true, $nl, $sep, $bull); break;
			case 'odp' : $this->OpenDoc_SheetSlides_Debug(false, $nl, $sep, $bull); break;
			}

			switch ($this->ExtType) {
			case 'openxml': $this->OpenXML_ChartDebug($nl, $sep, $bull); break;
			case 'odf':     $this->OpenDoc_ChartDebug($nl, $sep, $bull); break;
			}
			
		}

		if ($Exit) exit;

	}

	function TbsDebug_Merge($XmlFormat = true, $Current) {
	// display modified and added files

		$this->TbsDebug_Init($nl, $sep, $bull, ($Current ? 'OPENTBS_DEBUG_XML_CURRENT' :'OPENTBS_DEBUG_XML_SHOW'));

		// scann files for collecting information
		$mod_lst = ''; // id of modified files
		$del_lst = ''; // id of deleted  files
		$add_lst = ''; // id of added    files

		// files marked as replaced in TbsZip
		$idx_lst = array_keys($this->ReplInfo);
		foreach ($idx_lst as $idx) {
			$name = $this->TbsGetFileName($idx);
			if ($this->ReplInfo[$idx]===false) {
				$del_lst .= $bull.$name;
			} else {
				$mod_lst .= $bull.$name;
			}
		}

		// files marked as modified in the Park
		$idx_lst = array_keys($this->TbsStoreLst);
		foreach ($idx_lst as $idx) {
			if (!isset($this->ReplInfo[$idx])) {
				$mod_lst .= $bull.$this->TbsGetFileName($idx);
			}
		}

		// files marked as added in TbsZip
		$idx_lst = array_keys($this->AddInfo);
		foreach ($idx_lst as $idx) {
			$name = $this->AddInfo[$idx]['name'];
			$add_lst .= $bull.$name;
		}

		if ($mod_lst==='')  $mod_lst = ' none';
		if ($del_lst==='')  $del_lst = ' none';
		if ($add_lst==='')  $add_lst = ' none';

		echo $nl.'* Deleted files in the archive:'.$del_lst;
		echo $nl.'* Added files in the archive:'.$add_lst;
		echo $nl.'* Modified files in the archive:'.$mod_lst;
		echo $nl;

		// display contents merged with OpenTBS
		foreach ($this->DebugLst as $name=>$src) {
			$x = trim($src);
			$info = '';
			$xml = ((strlen($x)>0) && $x[0]==='<');
			if ($XmlFormat && $xml) {
				$info = ' (XML reformated for debuging only)';
				$src = $this->XmlFormat($src);
			}
			echo $nl.$sep;
			echo $nl.'File merged with OpenTBS'.$info.': '.$name;
			echo $nl.$sep;
			echo $nl.$src;
		}

	}

	function ConvXmlOnly($Txt, $ConvBr) {
	// Used by TBS to convert special chars and new lines.
		$x = htmlspecialchars($Txt);
		if ($ConvBr) $this->ConvBr($x);
		return $x;
	}

	function ConvXmlUtf8($Txt, $ConvBr) {
	// Used by TBS to convert special chars and new lines.
		$x = htmlspecialchars(utf8_encode($Txt));
		if ($ConvBr) $this->ConvBr($x);
		return $x;
	}

	function ConvBr(&$x) {
		$z = $this->TBS->OtbsConvBr;
		if ($z===false) return;
		$x = nl2br($x); // Convert any type of line break
		$x = str_replace("\r", '' ,$x);
		$x = str_replace("\n", '' ,$x);
		$x = str_replace('<br />',$z ,$x);

	}

	function XmlFormat($Txt) {
	// format an XML source the be nicely aligned

		// delete line breaks
		$Txt = str_replace("\r",'',$Txt);
		$Txt = str_replace("\n",'',$Txt);

		// init values
		$p = 0;
		$lev = 0;
		$Res = '';

		$to = true;
		while ($to!==false) {
			$to = strpos($Txt,'<',$p);
			if ($to!==false) {
				$tc = strpos($Txt,'>',$to);
				if ($to===false) {
					$to = false; // anomaly
				} else {
					// get text between the tags
					$x = trim(substr($Txt, $p, $to-$p),' ');
					if ($x!=='') $Res .= "\n".str_repeat(' ',max($lev,0)).$x;
					// get the tag
					$x = substr($Txt, $to, $tc-$to+1);
					if ($Txt[$to+1]==='/') $lev--;
					$Res .= "\n".str_repeat(' ',max($lev,0)).$x;
					// change the level
					if (($Txt[$to+1]!=='?') && ($Txt[$to+1]!=='/') && ($Txt[$tc-1]!=='/')) $lev++;
					// next position
					$p = $tc + 1;
				}
			}
		}

		$Res = substr($Res, 1); // delete the first line break
		if ($p<strlen($Txt)) $Res .= trim(substr($Txt, $p), ' '); // complete the end

		return $Res;

	}

	function RaiseError($Msg, $NoErrMsg=false) {
		// Overwrite the parent RaiseError() method.
		$exit = (!$this->TBS->NoErr);
		if ($exit) $Msg .= ' The process is ending, unless you set NoErr property to true.';
		$this->TBS->meth_Misc_Alert('OpenTBS Plugin', $Msg, $NoErrMsg);
		if ($exit) {
			if ($this->DebugLst!==false) {
				if ($this->TbsCurrIdx!==false) $this->DebugLst[$this->TbsGetFileName($this->TbsCurrIdx)] = $this->TBS->Source;
				$this->TbsDebug_Merge(true, false);
			}
			exit;
		}
		return false;
	}

	/**
	 * Return the item of an array if exits, or the default value.
	 */
	function getItem($array, $item, $default) {
		if (isset($array[$item])) {
			return $array[$item];
		} else {
			return $default;
		}
	}
	
	// Found the relevant attribute for the image source, and then add parameter 'att' to the TBS locator.
	function TbsPicPrepare(&$Txt, &$Loc, $IsCaching) {

		if (isset($Loc->PrmLst['pic_prepared'])) {
			return true;
		}
	
		if (isset($Loc->PrmLst['att'])) {
			return $this->RaiseError('Parameter att is used with parameter ope=changepic in the field ['.$Loc->FullName.']. changepic will be ignored');
		}
		
		$backward = true;

		if (isset($Loc->PrmLst['tagpos'])) {
			$s = $Loc->PrmLst['tagpos'];
			if ($s=='before') {
				$backward = false;
			} elseif ($s=='inside') {
				if ($this->ExtType=='openxml') $backward = false;
			}
		}
		
		// Find the target attribute
		$att = false;
		if ($this->ExtType==='odf') {
			$att = 'draw:image#xlink:href';
		} elseif ($this->ExtType==='openxml') {
			$att = $this->OpenXML_FirstPicAtt($Txt, $Loc->PosBeg, $backward);
			if ($att===false) return $this->RaiseError('Parameter ope=changepic used in the field ['.$Loc->FullName.'] has failed to found the picture.');
		} else {
			return $this->RaiseError('Parameter ope=changepic used in the field ['.$Loc->FullName.'] is not supported with the current document type.');
		}
				
		// Move the field to the attribute
		// This technical works with cached fields because already cached fields are placed before the picture.
		$prefix = ($backward) ? '' : '+';
		$Loc->PrmLst['att'] = $prefix.$att;
		clsTinyButStrong::f_Xml_AttFind($Txt,$Loc,true);

		// Delete parameter att to prevent TBS from another processing
		unset($Loc->PrmLst['att']);
	   
		// Get picture dimension information
		if (isset($Loc->PrmLst['adjust'])) {
			$FieldLen = 0;
			if ($this->ExtType==='odf') {
				$Loc->otbsDim = $this->TbsPicGetDim_ODF($Txt, $Loc->PosBeg, false, $Loc->PosBeg, $FieldLen);
			} else {
				if (strpos($att,'v:imagedata')!==false) { 
					$Loc->otbsDim = $this->TbsPicGetDim_OpenXML_vml($Txt, $Loc->PosBeg, false, $Loc->PosBeg, $FieldLen);
				} else {
					$Loc->otbsDim = $this->TbsPicGetDim_OpenXML_dml($Txt, $Loc->PosBeg, false, $Loc->PosBeg, $FieldLen);
				}
			}
		}
		
		// Set the original picture to empty
		if ( isset($Loc->PrmLst['unique']) && $Loc->PrmLst['unique'] ) {

			// Get the value in the template
			$Value = substr($Txt, $Loc->PosBeg, $Loc->PosEnd -  $Loc->PosBeg +1);

			if ($this->ExtType==='odf') {
				$InternalPicPath = $Value;
			} elseif ($this->ExtType==='openxml') {
				$InternalPicPath = $this->OpenXML_GetInternalPicPath($Value);
				if ($InternalPicPath === false) {
					$this->RaiseError('The picture to merge with field ['.$Loc->FullName.'] cannot be found. Value=' . $Value);
				}
			}

			// Set the picture file to empty
			$this->FileReplace($InternalPicPath, '', TBSZIP_STRING, false);		

		}
		
		$Loc->PrmLst['pic_prepared'] = true;
		return true;

	}

	function TbsPicGetDim_ODF($Txt, $Pos, $Forward, $FieldPos, $FieldLen) {
	// Found the attributes for the image dimensions, in an ODF file
		// unit (can be: mm, cm, in, pi, pt)
		$Offset = 0;
		$dim = $this->TbsPicGetDim_Any($Txt, $Pos, $Forward, $FieldPos, $FieldLen, $Offset, 'draw:frame', 'svg:width="', 'svg:height="', 3, false, false);
		return array($dim);
	}

	function TbsPicGetDim_OpenXML_vml($Txt, $Pos, $Forward, $FieldPos, $FieldLen) {
		$Offset = 0;
		$dim = $this->TbsPicGetDim_Any($Txt, $Pos, $Forward, $FieldPos, $FieldLen, $Offset, 'v:shape', 'width:', 'height:', 2, false, false);
		return array($dim);
	}

	function TbsPicGetDim_OpenXML_dml($Txt, $Pos, $Forward, $FieldPos, $FieldLen) {

		$Offset = 0;

		// Try to find the drawing element
		if (isset($this->ExtInfo['pic_entity'])) {
			$tag = $this->ExtInfo['pic_entity'];
			$Loc = clsTbsXmlLoc::FindElement($Txt, $this->ExtInfo['pic_entity'], $Pos, false);
			if ($Loc) {
				$Txt = $Loc->GetSrc();
				$Pos = 0;
				$Forward = true;
				$Offset = $Loc->PosBeg;
			}
		}

		$dim_shape = $this->TbsPicGetDim_Any($Txt, $Pos, $Forward, $FieldPos, $FieldLen, $Offset, 'wp:extent', 'cx="', 'cy="', 0, 12700, false);
		$dim_inner = $this->TbsPicGetDim_Any($Txt, $Pos, $Forward, $FieldPos, $FieldLen, $Offset, 'a:ext'    , 'cx="', 'cy="', 0, 12700, 'uri="');
		$dim_drawing = $this->TbsPicGetDim_Drawings($Txt, $Pos, $FieldPos, $FieldLen, $Offset, $dim_inner); // check for XLSX

		// dims must be sorted in reverse order of location
		$result = array();
		if ($dim_shape!==false)   $result[$dim_shape['wb']] = $dim_shape;
		if ($dim_inner!==false)   $result[$dim_inner['wb']] = $dim_inner;
		if ($dim_drawing!==false) $result[$dim_drawing['wb']] = $dim_drawing;
		krsort($result);
		
		return $result;
		
	}

	// Found the attributes for the image dimensions, in an ODF file
	function TbsPicGetDim_Any($Txt, $Pos, $Forward, $FieldPos, $FieldLen, $Offset, $Element, $AttW, $AttH, $AllowedDec, $CoefToPt, $IgnoreIfAtt) {

		while (true) {

			$p = clsTinyButStrong::f_Xml_FindTagStart($Txt, $Element, true, $Pos, $Forward, true);
			if ($p===false) return false;

			$pe = strpos($Txt, '>', $p);
			if ($pe===false) return false;

			$x = substr($Txt, $p, $pe -$p);

			if ( ($IgnoreIfAtt===false) || (strpos($x, $IgnoreIfAtt)===false) ) {

				$att_lst = array('w'=>$AttW, 'h'=>$AttH);
				$res_lst = array();

				foreach ($att_lst as $i=>$att) {
						$l = strlen($att);
						$b = strpos($x, $att);
						if ($b===false) return false;
						$b = $b + $l;
						$e = strpos($x, '"', $b);
						$e2 = strpos($x, ';', $b); // in case of VML format, width and height are styles separted by ;
						if ($e2!==false) $e = min($e, $e2);
						if ($e===false) return false;
						$lt = $e - $b;
						$t = substr($x, $b, $lt);
						$pu = $lt; // unit first char
						while ( ($pu>1) && (!is_numeric($t[$pu-1])) ) $pu--;
						$u = ($pu>=$lt) ? '' : substr($t, $pu);
						$v = floatval(substr($t, 0, $pu));
						$beg = $Offset+$p+$b;
						if ($beg>$FieldPos) $beg = $beg - $FieldLen;
						$res_lst[$i.'b'] = $beg; // start position in the main string
						$res_lst[$i.'l'] = $lt; // length of the text
						$res_lst[$i.'u'] = $u; // unit
						$res_lst[$i.'v'] = $v; // value
						$res_lst[$i.'t'] = $t; // text
						$res_lst[$i.'o'] = 0; // offset
				}

				$res_lst['r'] = ($res_lst['hv']==0) ? 0.0 : $res_lst['wv']/$res_lst['hv']; // ratio W/H
				$res_lst['dec'] = $AllowedDec; // save the allowed decimal for this attribute
				$res_lst['cpt'] = $CoefToPt;
				return $res_lst;

			} else {

				// Next try
				$Pos = $p + (($Forward) ? +1 : -1);

			}

		}

	}

	// Get Dim in an OpenXML Drawing (pictures in an XLSX)
	function TbsPicGetDim_Drawings($Txt, $Pos, $FieldPos, $FieldLen, $Offset, $dim_inner) {

		// The <a:ext> coordinates must have been found previously.
		if ($dim_inner===false) return false;
		// The current file must be an XLSX drawing sub-file.
		if (strpos($this->TBS->OtbsCurrFile, 'xl/drawings/')!==0) return false;
		
		if ($Pos==0) {
			// The parent element has already been found
			$PosEl = 0;
		} else {
			// Found  parent element
			$loc = clsTbsXmlLoc::FindStartTag($Txt, 'xdr:twoCellAnchor', $Pos, false);
			if ($loc===false) return false;
			$PosEl = $loc->PosBeg;
		}
		
		$loc = clsTbsXmlLoc::FindStartTag($Txt, 'xdr:to', $PosEl, true);
		if ($loc===false) return false;
		$p = $loc->PosBeg;

		$res = array();

		$el_lst = array('w'=>'xdr:colOff', 'h'=>'xdr:rowOff');
		foreach ($el_lst as $i=>$el) {
			$loc = clsTbsXmlLoc::FindElement($Txt, $el, $p, true);
			if ($loc===false) return false;
			$beg =  $Offset + $loc->GetInnerStart();
			if ($beg>$FieldPos) $beg = $beg - $FieldLen;
			$val = $dim_inner[$i.'v'];
			$tval = $loc->GetInnerSrc();
			$res[$i.'b'] = $beg;
			$res[$i.'l'] = $loc->GetInnerLen();
			$res[$i.'u'] = '';
			$res[$i.'v'] = $val;
			$res[$i.'t'] = $tval;
			$res[$i.'o'] = intval($tval) - $val;
		}

		$res['r'] = ($res['hv']==0) ? 0.0 : $res['wv']/$res['hv']; // ratio W/H;
		$res['dec'] = 0;
		$res['cpt'] = 12700;

		return $res;

	}

	/**
	 * Return the path of the image on the server corresponding the current field being merged.
	 */
	function TbsPicExternalPath(&$Value, &$PrmLst) {
	
		$TBS = &$this->TBS;
	
		// set the path where files should be taken
		if (isset($PrmLst['from'])) {
			if (!isset($PrmLst['pic_prepared'])) $TBS->meth_Merge_AutoVar($PrmLst['from'],true); // merge automatic TBS fields in the path
			$FullPath = str_replace($TBS->_ChrVal,$Value,$PrmLst['from']); // merge [val] fields in the path
		} else {
			$FullPath = $Value;
		}
		if ( (!isset($PrmLst['pic_prepared'])) && isset($PrmLst['default']) ) $TBS->meth_Merge_AutoVar($PrmLst['default'],true); // merge automatic TBS fields in the path

		// check if the picture exists, and eventually use the default picture
		if (!file_exists($FullPath)) {
			if (isset($PrmLst['default'])) {
				$x = $PrmLst['default'];
				if ($x==='current') {
					return false;
				} elseif (file_exists($x)) {
					$FullPath = $x;
				} else {
					return $this->RaiseError('The default picture "'.$x.'" defined by parameter "default" of the field ['.$Loc->FullName.'] is not found.');
				}
			} else {
				return false;
			}
		}

		return $FullPath;
		
	}
	
	/**
	 * Add a picture inside the archive, use parameters 'from' and 'as'.
	 * Argument $Prm is only used for error messages.
	 */
	function TbsPicAdd(&$Value, &$PrmLst, &$Txt, &$Loc, $Prm) {
		
		$TBS = &$this->TBS;

		$PrmLst['pic_prepared'] = true; // mark the locator as Picture prepared
		
		$ExternalPath = $this->TbsPicExternalPath($Value, $PrmLst);
		
		if ($ExternalPath === false) {
			if (isset($PrmLst['att'])) {
				// can happen when using MergeField()
				unset($PrmLst['att']);
				$Value = '';
			} else {
				// parameter att already applied during Field caching
				$Value = substr($Txt, $Loc->PosBeg, $Loc->PosEnd - $Loc->PosBeg + 1);
			}
			return false;
		}

		// set the name of the internal file
		if (isset($PrmLst['as'])) {
			if (!isset($PrmLst['pic_prepared'])) $TBS->meth_Merge_AutoVar($PrmLst['as'],true); // merge automatic TBS fields in the path
			$InternalPath = str_replace($TBS->_ChrVal,$Value,$PrmLst['as']); // merge [val] fields in the path
		} else {
			// uniqueness by the name of the file, not its full path, this is a weakness
			// OpenXML does not support spaces and accents in internal file names.
			$x = basename($ExternalPath);
			if (!isset($this->ImageInternal[$x])) {
				$ext = $this->Misc_FileExt(basename($ExternalPath));
				$this->ImageInternal[$x] = 'opentbs_added_' . $this->ImageIndex . '.' . $ext;
				$this->ImageIndex++;
			}
			$InternalPath = $this->ImageInternal[$x];
		}

		// the value of the current TBS field becomes the full internal path
		if (isset($this->ExtInfo['pic_path'])) $InternalPath = $this->ExtInfo['pic_path'].$InternalPath;

		// actually add the picture inside the archive
		if ($this->FileGetIdxAdd($InternalPath)===false) $this->FileAdd($InternalPath, $ExternalPath, TBSZIP_FILE, true);

		// preparation for others file in the archive
		$Rid = false;
		if ($this->ExtType==='odf') {
			// OpenOffice document
			$this->OpenDoc_ManifestChange($InternalPath,'');
		} elseif ($this->ExtType==='openxml') {
			// Microsoft Office document
			$this->OpenXML_CTypesPrepareExt($InternalPath, '');
			$BackNbr = max(substr_count($TBS->OtbsCurrFile, '/') - 1, 0); // docx=>"media/img.png", xlsx & pptx=>"../media/img.png"
			$TargetDir = str_repeat('../', $BackNbr).'media/';
			$FileName = basename($InternalPath);
			$Rid = $this->OpenXML_Rels_AddNewRid($TBS->OtbsCurrFile, $TargetDir, $FileName);
		}

		// change the value of the field for the merging process
		if ($Rid===false) {
			$Value = $InternalPath;
		} else {
			$Value = $Rid; // the Rid is used instead of the file name for the merging
		}

		// Change the dimensions of the picture
		if (isset($Loc->otbsDim)) {
			$this->TbsPicAdjust($Txt, $Loc, $ExternalPath);
		}

		return true;

	}

	// Adjust the dimensions if the picture
	function TbsPicAdjust(&$Txt, &$Loc, &$File) {

		$fDim = @getimagesize($File); // file dimensions
		if (!is_array($fDim)) return;
		$w = (float) $fDim[0];
		$h = (float) $fDim[1];
		$r = ($w/$h);
		$delta = 0;
		$adjust = $Loc->PrmLst['adjust'];
		if ( (!is_string($adjust)) || ($adjust=='') ) $adjust = 'inside';
		if (strpos($adjust, '%')!==false) {
			$adjust_coef = floatval(str_replace('%','',$adjust))/100.0;
			$adjust = '%';
		}

		// Save position of the locator before dims are modified
		if (!isset($Loc->svPosBeg)) {
			$Loc->svPosBeg = $Loc->PosBeg;
			$Loc->svPosEnd = $Loc->PosEnd;
		}

		foreach ($Loc->otbsDim as $tDim) { // template dimensions. They must be sorted in reverse order of location
			if ($tDim!==false) {
				// find what dimensions should be edited
				if ($adjust=='%') {
					if ($tDim['wb']>$tDim['hb']) { // the last attribute must be processed first
						$edit_lst = array('w' =>  $adjust_coef * $w, 'h' =>  $adjust_coef * $h );
					} else {
						$edit_lst = array('h' =>  $adjust_coef * $h, 'w' =>  $adjust_coef * $w );
					}
				} elseif ($adjust=='samewidth') {
					$edit_lst = array('h' => $tDim['wv'] * $h / $w );
				} elseif ($adjust=='sameheight') {
					$edit_lst = array('w' =>  $r * $tDim['hv'] );
				} else { // default value
					if ($tDim['r']>=$r) {
						$edit_lst = array('w' =>  $r * $tDim['hv'] ); // adjust width
					} else {
						$edit_lst = array('h' => $tDim['wv'] * $h / $w ); // adjust height
					}
				}
				// edit dimensions
				foreach ($edit_lst as $what=>$new) {
					$beg  = $tDim[$what.'b'];
					$len  = $tDim[$what.'l'];
					$unit = $tDim[$what.'u'];
					if ($adjust=='%') {
						if ($tDim['cpt']!==false) $new = $new * $tDim['cpt']; // apply the coef to Point conversion if any
						if ($unit!=='') { // force unit to pt, if units are allowed
							$unit = 'pt';
						}
					}
					$new = $new + $tDim[$what.'o']; // add the offset (xlsx only)
					$new = number_format($new, $tDim['dec'], '.', '').$unit;
					$Txt = substr_replace($Txt, $new, $beg, $len);
					if ($Loc->PosBeg>$beg) $delta = $delta + strlen($new) - $len;
				}
			}
		}

		// Update the position
		$Loc->PosBeg = $Loc->svPosBeg + $delta;
		$Loc->PosEnd = $Loc->svPosEnd + $delta;

	}
	
	/**
	 * Search a string in a list if several sub-file in the archive.
	 * @param $files An associated array of sub-files to scann. Structure: $key => IdxOrName
	 * @param $str   The string to search.
	 * @param $returnFirstFind  true to return only the first record fund.
	 * @return a single record or a recordset structured like: array('key'=>, 'idx'=>, 'src'=>, 'pos'=>, 'curr'=>)
	 */
	function TbsSearchInFiles($files, $str, $returnFirstFound = true) {

		$keys_ok = array();

		// transform the list of files into a list of available idx
		$keys_todo = array();
		$idx_keys = array();
		foreach($files as $k=>$f) {
			$idx = $this->FileGetIdx($f);
			if ($idx!==false) {
				$keys_todo[$k] = $idx;
				$idx_keys[$idx] = $k;
			}
		}

		// Search in the current sub-file
		if ( ($this->TbsCurrIdx!==false) && isset($idx_keys[$this->TbsCurrIdx]) ) {
			$key = $idx_keys[$this->TbsCurrIdx];
			$p = strpos($this->TBS->Source, $str);
			if ($p!==false) {
				$keys_ok[] = array('key' => $key, 'idx' => $this->TbsCurrIdx, 'src' => &$this->TBS->Source, 'pos' => $p, 'curr'=>true);
				if ($returnFirstFound) return $keys_ok[0];
			}
			unset($keys_todo[$key]);
		}

		// Search in the store
		foreach($this->TbsStoreLst as $idx => $s) {
			if ( ($idx!==$this->TbsCurrIdx) && isset($idx_keys[$idx]) ) {
				$key = $idx_keys[$idx];
				$p = strpos($s['src'], $str);
				if ($p!==false) {
					$keys_ok[] = array('key' => $key, 'idx' => $idx, 'src' => &$s['src'], 'pos' => $p, 'curr'=>false);
					if ($returnFirstFound) return $keys_ok[0];
				}
				unset($keys_todo[$key]);
			}
		}

		// Search in other sub-files (never opened)
		foreach ($keys_todo as $key => $idx) {
			$txt = $this->FileRead($idx);
			$p = strpos($txt, $str);
			if ($p!==false) {
				$keys_ok[] = array('key' => $key, 'idx' => $idx, 'src' => $txt, 'pos' => $p, 'curr'=>false);
				if ($returnFirstFound) return $keys_ok[0];
			}
		}

		if ($returnFirstFound) {
			return  array('key'=>false, 'idx'=>false, 'src'=>false, 'pos'=>false, 'curr'=>false);
		} else {
			return $keys_ok;
		}

	}

	// Check after the sheet process
	function TbsSheetCheck() {
		if (count($this->OtbsSheetSlidesDelete)>0) $this->RaiseError("Unable to delete the following sheets because they are not found in the workbook: ".(str_replace(array('i:','n:'),'',implode(', ',$this->OtbsSheetSlidesDelete))).'.');
		if (count($this->OtbsSheetSlidesVisible)>0) $this->RaiseError("Unable to change visibility of the following sheets because they are not found in the workbook: ".(str_replace(array('i:','n:'),'',implode(', ',array_keys($this->OtbsSheetSlidesVisible)))).'.');
	}

	function TbsDeleteComments($MainTags, $CommFiles, $CommTags, $Inner) {

		$nbr = 0;

		// Retrieve the Comment sub-file (OpenXML only)
		if ($CommFiles!==false) {
			$Files = $this->OpenXML_MapGetFiles($CommFiles);
			foreach ($Files as $file) {
				$idx = $this->FileGetIdx($file);
				if ($idx!==false) {
					// Delete inner text of the comments to be sure that contents is deleted
					// we only empty the comment elements in case some comments are referenced in other special part of the document
					$Txt = $this->TbsStoreGet($idx, "Delete Comments");
					$nbr = $nbr + $this->XML_DeleteElements($Txt, $CommTags, $Inner);
					$this->TbsStorePut($idx, $Txt);
				}
			}
		}

		// Retrieve the Main sub-file
		if ($MainTags!==false) {
			$idx = $this->Ext_GetMainIdx();
			if ($idx===false) return false;
			// Delete Comment locators
			$Txt = $this->TbsStoreGet($idx, "Delete Comments");
			$nbr2 = $this->XML_DeleteElements($Txt, $MainTags);
			$this->TbsStorePut($idx, $Txt);
			if ($CommFiles===false) $nbr = $nbr2;
		}

		return $nbr;

	}

	/**
	 * Replace var fields in a Parameter of a block.
	 * @param  string $PrmVal The parameter value.
	 * @param  string $FldVal The value of the field that holds the value.
	 * @return string The merged value of the parameter.
	 */
	function TbsMergeVarFields($PrmVal, $FldVal) {
        if ($PrmVal === true) $PrmVal = ''; // TBS set the value to true if no value set, but it is converted into '1'.
		$this->TBS->meth_Merge_AutoVar($PrmVal, true);
		$PrmVal = str_replace($this->TBS->_ChrVal, $FldVal, $PrmVal);
		return $PrmVal;
	}

	function TbsDeleteColumns(&$Txt, $Value, $PrmLst, $PosBeg) {

		$ext = $this->ExtEquiv;
		if ($ext==='docx') {
			$el_table = 'w:tbl';
			$el_delete = array(
				array('p'=>'w:tblGrid', 'c'=>'w:gridCol', 's'=>false),
				array('p'=>'w:tr', 'c'=>'w:tc', 's'=>false),
			);
		} elseif ($ext==='odt') {
			$el_table = 'table:table';
			$el_delete = array(
				array('p'=>false, 'c'=>'table:table-column', 's'=>'table:number-columns-repeated'),
				array('p'=>'table:table-row', 'c'=>'table:table-cell', 's'=>false),
			);
		} else {
			return false;
		}
		
		if (is_array($Value)) $Value = implode(',', $Value);

		// Retreive the list of columns id to delete
		$col_lst = $this->TbsMergeVarFields($PrmLst['colnum'], $Value); // prm equal to true if value is not given
		$col_lst = str_replace(' ', '', $col_lst);
		if ( ($col_lst=='') || ($col_lst=='0') ) return false; // there is nothing to do
		$col_lst = explode(',', $col_lst);
		$col_nbr = count($col_lst);
		for ($c=0; $c<$col_nbr; $c++) $col_lst[$c] = intval($col_lst[$c]); // Conversion into numerical
		
		// Add columns by shifting
		if (isset($PrmLst['colshift'])) {
			$col_shift = intval($this->TbsMergeVarFields($PrmLst['colshift'], $Value));
			if ($col_shift<>0) {
				$step = ($col_shift>0) ? -1 : +1;
				for ($s = $col_shift; $s<>0; $s = $s + $step) {
					for ($c=0; $c<$col_nbr; $c++) $col_lst[] = $col_lst[$c] + $s;
				}
			}
		}

		// prepare column info
		$col_lst = array_unique($col_lst, SORT_NUMERIC); // Delete duplicated columns
		sort($col_lst, SORT_NUMERIC); // Sort colmun id in order
		$col_max = $col_lst[(count($col_lst)-1)]; // Last column to delete
        
        // Delete impossible col num (like zero)
        while ( (count($col_lst) > 0) && ($col_lst[0] <= 0) ) {
            array_shift($col_lst);
        }
        if (count($col_lst) == 0) return false;
        
		// Look for the source of the table
		$Loc = clsTbsXmlLoc::FindElement($Txt, $el_table, $PosBeg, false);
		if ($Loc===false) return false;

		$Src = $Loc->GetSrc();

		foreach ($el_delete as $info) {
			if ($info['p']===false) {
				$this->XML_DeleteColumnElements($Src, $info['c'], $info['s'], $col_lst, $col_max);
			} else {
				$ParentPos = 0;
				while ($ParentLoc = clsTbsXmlLoc::FindElement($Src, $info['p'], $ParentPos, true)) {
					$ParentSrc = $ParentLoc->GetSrc();
					$ModifNbr = $this->XML_DeleteColumnElements($ParentSrc, $info['c'], $info['s'], $col_lst, $col_max);
					if ($ModifNbr>0) $ParentLoc->ReplaceSrc($ParentSrc);
					$ParentPos = $ParentLoc->PosEnd + 1;
				}
			}
		}

		$Loc->ReplaceSrc($Src);

	}

	/**
	 * Delete or Display a Sheet or a Slide according to its numbre or its name
	 * @param $id_or_name Id or Name of the Sheet/Slide
	 * @param $ok         true to Keep or Display, false to Delete or Hide
	 * @param $delete     true to Delete/Keep, false to Display/Hide
	 */
	function TbsSheetSlide_DeleteDisplay($id_or_name, $ok, $delete) {

		if (is_null($ok)) $ok = true; // default value


		$ext = $this->ExtEquiv;

		$ok = (boolean) $ok;
		if (!is_array($id_or_name)) $id_or_name = array($id_or_name);

		foreach ($id_or_name as $item=>$action) {
			if (!is_bool($action)) {
				$item = $action;
				$action = $ok;
			}
			$item_ref = (is_string($item)) ? 'n:'.htmlspecialchars($item) : 'i:'.$item; // help to make the difference beetween id and name
			if ($delete) {
				if ($ok) {
					$this->OtbsSheetSlidesDelete[$item_ref] = $item;
				} else {
					unset($this->OtbsSheetSlidesVisible[$item_ref]);
				}
			} else {
				$this->OtbsSheetSlidesVisible[$item_ref] = $ok;
			}
		}

	}

	/**
	 * Prepare the locator for merging cells.
	 */
	function TbsPrepareMergeCell(&$Txt, &$Loc) {
		if ($this->ExtEquiv=='docx') {
			// Move the locator just inside the <w:tcPr> element.
			// See OnOperation() for other process
			$xml = clsTbsXmlLoc::FindStartTag($Txt, 'w:tcPr', $Loc->PosBeg, false);
			if ($xml) {
				$Txt = substr_replace($Txt, '', $Loc->PosBeg, $Loc->PosEnd - $Loc->PosBeg + 1);
				$Loc->PosBeg = $xml->PosEnd+1;
				$Loc->PosEnd = $xml->PosEnd;
				$this->PrevVals[$Loc->FullName] = ''; // the previous value is saved in property because they can be several sections, and thus several Loc for the same column.
				//$Loc->Prms['strconv']='no'; // should work
				$Loc->ConvStr=false;
			}
		}
	}


	
	/**
	 * Actualize property ExtInfo (Extension Info).
	 * ExtInfo will be an array with keys 'load', 'br', 'ctype' and 'pic_path'. Keys 'rpl_what' and 'rpl_with' are optional.
	 *  load:     files in the archive to be automatically loaded by OpenTBS when the archive is loaded. Separate files with comma ';'.
	 *  br:       string that replace break-lines in the values merged by TBS, set to false if no conversion.
	 *  frm:      format of the file ('odf' or 'openxml'), for now it is used only to activate a special feature for openxml files
	 *  ctype:    (optional) the Content-Type header name that should be use for HTTP download. Omit or set to '' if not specified.
	 *  pic_path: (optional) the folder nale in the archive where to place pictures
	 *  rpl_what: (optional) string to replace automatically in the files when they are loaded. Can be a string or an array.
	 *  rpl_with: (optional) to be used with 'rpl_what',  Can be a string or an array.
	 * User can define his own Extension Information, they are taken in acount if saved int the global variable $_OPENTBS_AutoExt.
	 */
	function Ext_PrepareInfo($Ext=false) {

		$this->ExtEquiv = false;
		$this->ExtType = false;
	
		if ($Ext===false) {
			// Get the extension of the current archive
			if ($this->ArchIsStream) {
				$Ext = '';
			} else {
				$Ext = basename($this->ArchFile);
				$p = strrpos($Ext, '.');
				$Ext = ($p===false) ? '' : strtolower(substr($Ext, $p + 1));
			}
			$Frm = $this->Ext_DeductFormat($Ext, true); // may change $Ext
			// Rename the name of the phantom file if it is a stream
			if ( $this->ArchIsStream && (strlen($Ext)>2) ) $this->ArchFile = str_replace('.zip', '.'.$Ext, $this->ArchFile);
		} else {
			// The extension is forced
			$Frm = $this->Ext_DeductFormat($Ext, false); // may change $Ext
		}

		$TBS = &$this->TBS;
		$set_option = method_exists($TBS, 'SetOption');
		
		$i = false;
		$block_alias = false;
		
		if (isset($GLOBAL['_OPENTBS_AutoExt'][$Ext])) {
			// User defined information
			$i = $GLOBAL['_OPENTBS_AutoExt'][$Ext];
			if (isset($i['equiv'])) $this->ExtEquiv = $i['equiv'];
			if (isset($i['frm'])) $this->ExtType = $i['frm'];
		} elseif ($Frm==='odf') {
			// OpenOffice & LibreOffice documents
			$i = array('main' => 'content.xml', 'br' => '<text:line-break/>', 'ctype' => 'application/vnd.oasis.opendocument.', 'pic_path' => 'Pictures/', 'rpl_what' => '&apos;', 'rpl_with' => '\'');
			if ($this->FileExists('styles.xml')) $i['load'] = array('styles.xml'); // styles.xml may contain header/footer contents
			if ($Ext==='odf') $i['br'] = false;
			if ($Ext==='odm') $this->ExtEquiv = 'odt';
			if ($Ext==='ots') $this->ExtEquiv = 'ods';
			$this->ExtType = 'odf';
			$ctype = array('t' => 'text', 's' => 'spreadsheet', 'g' => 'graphics', 'f' => 'formula', 'p' => 'presentation', 'm' => 'text-master');
			$i['ctype'] .= $ctype[($Ext[2])];
			$i['pic_ext'] = array('png' => 'png', 'bmp' => 'bmp', 'gif' => 'gif', 'jpg' => 'jpeg', 'jpeg' => 'jpeg', 'jpe' => 'jpeg', 'jfif' => 'jpeg', 'tif' => 'tiff', 'tiff' => 'tiff');
			$block_alias = array(
				'tbs:p' => 'text:p',              // ODT+ODP
				'tbs:title' => 'text:h',          // ODT+ODP
				'tbs:section' => 'text:section',  // ODT
				'tbs:table' => 'table:table',     // ODT (sheet for ODS)
				'tbs:row' => 'table:table-row',   // ODT+ODS
				'tbs:cell' => 'table:table-cell', // ODT+ODS
				'tbs:comment' => 'office:annotation',
				'tbs:page' => array(&$this, 'OpenDoc_GetPage'), // ODT
				'tbs:slide' => 'draw:page',       // ODP
				'tbs:sheet' => 'table:table',     // ODS (table for ODT)
				'tbs:draw' => array(&$this, 'OpenDoc_GetDraw'),
				'tbs:drawgroup' => 'draw:g',
				'tbs:drawitem' => array(&$this, 'OpenDoc_GetDraw'),
				'tbs:listitem' => 'text:list-item', // ODT+ODP
			);
			if ($set_option) {
				$TBS->SetOption('parallel_conf', 'tbs:table', 
					array(
						'parent' => 'table:table',
						'ignore' => array('table:covered-table-cell', 'table:table-header-rows'),
						'cols' => array('table:table-column' => 'table:number-columns-repeated'),
						'rows' => array('table:table-row'),
						'cells' => array('table:table-cell' => 'table:number-columns-spanned'),
					)
				);
			}
		} elseif ($Frm==='openxml') {
			// Microsoft Office documents
			$this->OpenXML_MapInit();
			if ($TBS->OtbsConvertApostrophes) {
				$x = array(chr(226) . chr(128) . chr(152), chr(226) . chr(128) . chr(153));
			} else {
				$x = null;
			}
			$ctype = 'application/vnd.openxmlformats-officedocument.';
			if ( ($Ext==='docx') || ($Ext==='docm') ) {
				// Notes: (1) '<w:br/>' works but '</w:t><w:br/><w:t>' enforce compatibility with Libre Office. (2) Line-breaks merged in attributes will corrupt the DOCX anyway.
				$i = array('br' => '</w:t><w:br/><w:t>', 'ctype' => $ctype . 'wordprocessingml.document', 'pic_path' => 'word/media/', 'rpl_what' => $x, 'rpl_with' => '\'', 'pic_entity'=>'w:drawing');
				if ($Ext==='docm') $i['ctype'] = 'application/vnd.ms-word.document.macroEnabled.12';
				$i['main'] = $this->OpenXML_MapGetMain('wordprocessingml.document.main+xml', 'word/document.xml');
				$i['load'] = $this->OpenXML_MapGetFiles(array('wordprocessingml.header+xml', 'wordprocessingml.footer+xml'));
				$this->ExtEquiv = 'docx';
				$block_alias = array(
					'tbs:p' => 'w:p',
					'tbs:title' => 'w:p',
					'tbs:section' => array(&$this, 'MsWord_GetSection'),
					'tbs:table' => 'w:tbl',
					'tbs:row' => 'w:tr',
					'tbs:cell' => 'w:tc',
					'tbs:page' => array(&$this, 'MsWord_GetPage'),
					'tbs:draw' => 'mc:AlternateContent',
					'tbs:drawgroup' => 'mc:AlternateContent',
					'tbs:drawitem' => 'wps:wsp',
					'tbs:listitem' => 'w:p',
				);  
				if ($set_option) {
					$TBS->SetOption('parallel_conf', 'tbs:table', 
						array(
							'parent' => 'w:tbl',
							'ignore' => array('w:tblPr', 'w:tblGrid'),
							'cols' => array('w:gridCol' => ''),
							'rows' => array('w:tr'),
							'cells' => array('w:tc' => ''), // <w:gridSpan w:val="2"/>
						)
					);
				}
			} elseif ( ($Ext==='xlsx') || ($Ext==='xlsm')) {
				$this->MsExcel_DeleteCalcChain();
				$i = array('br' => false, 'ctype' => $ctype . 'spreadsheetml.sheet', 'pic_path' => 'xl/media/', 'pic_entity'=>'xdr:twoCellAnchor');
				if ($Ext==='xlsm') $i['ctype'] = 'application/vnd.ms-excel.sheet.macroEnabled.12';
				$i['main'] = $this->OpenXML_MapGetMain('spreadsheetml.worksheet+xml', 'xl/worksheets/sheet1.xml');
				$this->ExtEquiv = 'xlsx';
				$block_alias = array(
					'tbs:row' => 'row',
					'tbs:cell' => 'c',
					'tbs:draw' => 'xdr:twoCellAnchor',
					'tbs:drawgroup' => 'xdr:twoCellAnchor',
					'tbs:drawitem' => 'xdr:sp',
				);
			} elseif ( ($Ext==='pptx') || ($Ext==='pptm') ){
				$i = array('br' => false, 'ctype' => $ctype . 'presentationml.presentation', 'pic_path' => 'ppt/media/', 'rpl_what' => $x, 'rpl_with' => '\'', 'pic_entity'=>'p:pic');
				if ($Ext==='pptm') $i['ctype'] = 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
				$this->MsPowerpoint_InitSlideLst();
				$i['main'] = (isset($this->OpenXmlSlideLst[0])) ? $this->OpenXmlSlideLst[0]['file'] : 'ppt/slides/slide1.xml';
				$i['load'] = $this->OpenXML_MapGetFiles(array('presentationml.notesSlide+xml')); // auto-load comments
				$this->ExtEquiv = 'pptx';
				$block_alias = array(
					'tbs:p' => 'a:p',
					'tbs:title' => 'a:p',
					'tbs:table' => 'a:tbl',
					'tbs:row' => 'a:tr',
					'tbs:cell' => 'a:tc',
					'tbs:draw' => 'p:sp',
					'tbs:drawgroup' => 'p:grpSp',
					'tbs:drawitem' => 'p:sp',
					'tbs:listitem' => 'a:p',
				);
			}
			$i['pic_ext'] = array('png' => 'png', 'bmp' => 'bmp', 'gif' => 'gif', 'jpg' => 'jpeg', 'jpeg' => 'jpeg', 'jpe' => 'jpeg', 'tif' => 'tiff', 'tiff' => 'tiff', 'ico' => 'x-icon', 'svg' => 'svg+xml');
		}

		if ($i!==false) {
			$i['ext'] = $Ext;
			if (!isset($i['load'])) $i['load'] = array();
			$i['load'][] = $i['main']; // add to main file at the end of the files to load
		}
		  
		if ($set_option && ($block_alias!==false)) $TBS->SetOption('block_alias', $block_alias);

		$this->ExtInfo = $i;
		if ($this->ExtEquiv===false) $this->ExtEquiv = $Ext;
		if ($this->ExtType===false)  $this->ExtType = $Frm;
		return (is_array($i)); // return true if the extension is supported
	}

	// Return the type of document corresponding to the given extension.
	function Ext_DeductFormat(&$Ext, $Search) {
		if (strpos(',odt,ods,odg,odf,odp,odm,ott,ots,otg,otp,', ',' . $Ext . ',') !== false) return 'odf';
		if (strpos(',docx,docm,xlsx,xlsm,pptx,pptm,', ',' . $Ext . ',') !== false) return 'openxml';
		if (!$Search) return false;
		if ($this->FileExists('content.xml')) {
			// OpenOffice documents
			if ($this->FileExists('META-INF/manifest.xml')) {
				$Ext = '?'; // not needed for processing OpenOffice documents
				return 'odf';
			}
		} elseif ($this->FileExists('[Content_Types].xml')) {
			// Ms Office documents
			if ($this->FileExists('word/document.xml')) {
				$Ext = 'docx';
				return 'openxml';
			} elseif ($this->FileExists('xl/workbook.xml')) {
				$Ext = 'xlsx';
				return 'openxml';
			} elseif ($this->FileExists('ppt/presentation.xml')) {
				$Ext = 'pptx';
				return 'openxml';
			}
		}
		return false;
	}

	// Return the idx of the main document, if any.
	function Ext_GetMainIdx() {
		if ( ($this->ExtInfo!==false) && isset($this->ExtInfo['main']) ) {
			return $this->FileGetIdx($this->ExtInfo['main']);
		} else {
			return false;
		}
	}

	function XML_FoundTagStart($Txt, $Tag, $PosBeg) {
	// Found the next tag of the asked type. (Not specific to MsWord, works for any XML)
	// Tag must be prefixed with '<' or '</'.
		$len = strlen($Tag);
		$p = $PosBeg;
		while ($p!==false) {
			$p = strpos($Txt, $Tag, $p);
			if ($p===false) return false;
			$x = substr($Txt, $p+$len, 1);
			if (($x===' ') || ($x==='/') || ($x==='>') ) {
				return $p;
			} else {
				$p = $p+$len;
			}
		}
		return false;
	}
	
	/**
	 * Delete all tags of the types given in the list.
	 * @param {string} $Txt The text content to search into.
	 * @param {array} $TagLst List of tag names to delete.
	 * @param {boolean} $OnlyInner Set to true to keep the content inside the element. Set to false to delete the entire element. Default is false.
	 */
	function XML_DeleteElements(&$Txt, $TagLst, $OnlyInner=false) {
		$nb = 0;
		$Content = !$OnlyInner;
		foreach ($TagLst as $tag) {
			$p = 0;
			while ($x = clsTbsXmlLoc::FindElement($Txt, $tag, $p)) {
				$x->Delete($Content);
				$p = $x->PosBeg;
				$nb++;
			}
		}
		return $nb;
	}

	/**
	 * Delete all column elements  according to their position.
	 * Return the number of deleted elements.
	 */
	function XML_DeleteColumnElements(&$Txt, $Tag, $SpanAtt, $ColLst, $ColMax) {

		$ColNum = 0;
		$ColPos = 0;
		$ColQty = 1;
		$Continue = true;
		$ModifNbr = 0;

		while ($Continue && ($Loc = clsTbsXmlLoc::FindElement($Txt, $Tag, $ColPos, true)) ) {

			// get colmun quantity covered by the element (1 by default)
			if ($SpanAtt!==false) {
				$ColQty = $Loc->GetAttLazy($SpanAtt);
				$ColQty = ($ColQty===false) ? 1 : intval($ColQty);
			}
			// count column to keep
			$KeepQty = 0;
			for ($i=1; $i<=$ColQty ;$i++) {
				if (array_search($ColNum+$i, $ColLst)===false) $KeepQty++;
			}
			if ($KeepQty==0) {
				// delete the tag
				$Loc->ReplaceSrc('');
				$ModifNbr++;
			} else {
				if ($KeepQty!=$ColQty) {
					// edit the attribute
					$Loc->ReplaceAtt($SpanAtt, $KeepQty);
					$ModifNbr++;
				}
				$ColPos = $Loc->PosEnd + 1;
			}

			$ColNum += $ColQty;
			if ($ColNum>$ColMax) $Continue = false;
		}

		return $ModifNbr;

	}

	/**
	 * Change an attribute's value or an entity's value in the first element in a given sub-file.
	 * @param {mixed}  $SubFile : the name or the index of the sub-file. Use value false to get the current sub-file.
	 * @param {string} $ElPath  : path of the element. For example : 'w:document/w:body/w:p'.
	 * @param {string|boolean} $Att    : the attribute, or false to replace the entity's value.
	 * @param {string|boolean} $NewVal : the new value, or false to delete the attribute.
	 * @return {boolean} True if the attribute is found and processed. False otherwise.
	 */
	function XML_ForceAtt($SubFile, $ElPath, $Att, $NewVal, $AddElIfMissing = false) {
	
		// Find the file
		$idx = $this->FileGetIdx($SubFile);
		if ($idx === false) return false;
		$Txt = $this->TbsStoreGet($idx, 'XML_ForceAtt');
	
		// Find the element
		$el_lst = explode('/', $ElPath);
		$p = 0;
		$el_idx = 0;
		$el_nb = count($el_lst);
		$end = $el_nb;
		$loc = false;
		$loc_prev = false;
		while ($el_idx < $end) {
			$loc_prev = $loc;
			$loc = clsTbsXmlLoc::FindStartTag($Txt, $el_lst[$el_idx], $p);
			if ($loc === false) {
				if ($AddElIfMissing) {
					// stop the loop
					$end = $el_idx;
				} else {
					return false;
				}
			} else {
				$p = $loc->PosEnd;
				$el_idx++;
			}
		}

		if (($loc === false) && ($loc_prev === false)) return false;

		$save = true;
		if ($el_idx < $el_nb) {
			// One of the entities is not found => create entities
			if ($NewVal === false) {
				// Nothing to do
				$save = false;
			} else {
				$before = '';
				$after = '';
				$i_end = ($end - 1);
				for ($i = $el_idx ; $i < $i_end ; $i++) {
					$before .= '<' .  $el_lst[$i] . '>';
					$after = '</' .  $el_lst[$i] . '>' . $after;
				}
				if ($Att === false) {
					$x = $before . '<' . $el_lst[$i] . '>' . $NewVal . '</' . $el_lst[$i] . '>' . $after;
				} else {
					$x = $before . '<' . $el_lst[$i] . ' ' . $Att . '="' . $NewVal . '" />' . $after;
				}
				$loc_prev->FindEndTag();
				if ($loc_prev->pET_PosBeg === false) {
					return $this->RaiseError("Cannot apply attribute because entity '" . $loc_prev->FindName() . "' has no ending tag in file [$SubFile].");
				}
				$Txt = substr_replace($Txt, $x, $loc_prev->pET_PosBeg, 0);
			}
		} else {
			// The last entity is found => force the attribute
			if ($NewVal === false) {
				if ($Att === false) {
					// delete the entity
					$loc->Delete();
				} else {
					// delete the attribute
					$loc->DeleteAtt($Att);
				}
			} else {
				if ($Att === false) {
					// change the entity's value
					$loc->FindEndTag();
					$loc->ReplaceInnerSrc($NewVal);
				} else {
					// change the attribute's value
					$loc->ReplaceAtt($Att, $NewVal, true);
				}
			}
		}

		// Save the file
		if ($save) {
			$this->TbsStorePut($idx, $Txt);
		}

		return true;
		
	}
	
	/**
	 * Function used by Block Alias
	 * The first start tag on the left is supposed to be the good one.
	 * Note: encapsulation is not yet supported in this version.
	 */
	function XML_BlockAlias_Prefix($TagPrefix, $Txt, $PosBeg, $Forward, $LevelStop) {

		$loc = clsTbsXmlLoc::FindStartTagByPrefix($Txt, $TagPrefix, $PosBeg, false);

		if ($Forward) {
			$loc->FindEndTag();
			return $loc->PosEnd;
		} else {
			return $loc->PosBeg;
		}

	}

	/**
	 * Return the column number from a cell reference like "B3".
	 */
	function Misc_ColNum($ColRef, $IsODF) {

		if ($IsODF) {
			$p = strpos($ColRef, '.');
			if ($p!==false) $ColRef = substr($ColRef, $p); // delete the table name wich is in prefix
			$ColRef = str_replace( array('.','$'), '', $ColRef);
			$ColRef = explode(':', $ColRef);
			$ColRef = $ColRef[0];
		}

		$num = 0;
		$rank = 0;
		for ($i=strlen($ColRef)-1;$i>=0;$i--) {
			$l = $ColRef[$i];
			if (!is_numeric($l)) {
				$l = ord(strtoupper($l)) -64;
				if ($l>0 && $l<27) {
					$num = $num + $l*pow(26,$rank);
				} else {
					return $this->RaiseError('(Sheet) Reference of cell \''.$ColRef.'\' cannot be recognized.');
				}
				$rank++;
			}
		}

		return $num;

	}

	/**
	 * Return the reference of the cell, such as 'A10'.
	 */
	function Misc_CellRef($Col, $Row) {
		$r = '';
		$x = $Col;
		do {
			$x = $x - 1;
			$c = ($x % 26);
			$x = ($x - $c)/26;
			$r = chr(65 + $c) . $r; // chr(65)='A'
		} while ($x>0);
		return $r.$Row;
	}
	
	/**
	 * Return the extension of the file, lower case and without the dot. Example: 'png'.
	 */
	function Misc_FileExt($FileOrExt) {
		$p = strrpos($FileOrExt, '.');
		$ext = ($p===false) ? $FileOrExt : substr($FileOrExt, $p+1);
		$ext = strtolower($ext);
		return $ext;
	}

	/**
	 * Add or replace a credit information in the appropriate property of the document.
	 * Return the new credit text if succeed.
	 * Return false if the expected file is not found.
	 * @param string  $NewCredit  The text to set.
	 * @param boolean $Add        Add the item.
	 * @param boolean $System     Automatic system information.
	 * @param string  $Type       (optional) type of the item to add.
	 */
	function Misc_EditCredits($NewCredit, $Add, $System, $Type = null) {
	
		if ($this->ExtType=='odf') {
			$File = 'meta.xml';
			$Tag = 'meta:user-defined';
			if (is_string($Type)) {
				$n = $Type;
			} else {
				$n = ($System) ? 'Producer' : 'Creator';
			}
			$Att = 'meta:name="' . $n .'"';
			$Parent = 'office:meta';
		} elseif ($this->ExtType=='openxml') {
			$File = 'docProps/core.xml';
			$Tag = (is_string($Type)) ? $Type : 'dc:creator';
			$Att = false;
			$Parent = 'cp:coreProperties';
		} else {
			return false;
		}
	
		$idx = $this->FileGetIdx($File);
		if ($idx===false) return false;
		
		// prevent from XML injection
		$NewCredit = htmlspecialchars($NewCredit, ENT_NOQUOTES); // ENT_NOQUOTES because target is an element's content
		
		$Txt = $this->TbsStoreGet($idx, "EditCredits");
		
		if ($Att) {
			$loc = clsTbsXmlLoc::FindElementHavingAtt($Txt, $Att, 0);
			$TagOpen = $Tag.' '.$Att;
		} else {
			$loc = clsTbsXmlLoc::FindElement($Txt, $Tag, 0);
			$TagOpen = $Tag;
		}
		
		// On both OpenXML and ODF, the item must be unique.
		if ($loc===false) {
			$p = strpos($Txt, '</'.$Parent.'>');
			if ($p===false) return $p;
			$Txt = substr_replace($Txt, '<'.$TagOpen.'>'.$NewCredit.'</'.$Tag.'>', $p, 0);
		} else {
			if ($Add) {
				$NewCredit = $loc->GetInnerSrc().';'.$NewCredit;
			}
			$loc->ReplaceInnerSrc($NewCredit);
		}
		
		$this->TbsStorePut($idx, $Txt);
		
		return $NewCredit;
		
	}
	
	/**
	 * Return the path of file $FullPath relatively to the path of file $RelativeTo.
	 * For example:
	 * 'dir1/dir2/file_a.xml' relatively to 'dir1/dir2/file_b.xml' is 'file_a.xml'
	 * 'dir1/file_a.xml' relatively to 'dir1/dir2/file_b.xml' is '../file_a.xml'
	 */
	function OpenXML_GetRelativePath($FullPath, $RelativeTo) {
		
		$fp = explode('/', $FullPath);
		$fp_file = array_pop($fp);
		$fp_max = count($fp)-1;
		
		$rt = explode('/', $RelativeTo);
		$rt_file = array_pop($rt);
		$rt_max = count($rt)-1;
		
		// First different item
		$min = min($fp_max, $rt_max);
		while( ($min>=0) && ($fp[0]==$rt[0])  ) {
			$min--;
			array_shift($fp);
			array_shift($rt);
		}

		$path  = str_repeat('../', count($rt));
		$path .= implode('/', $fp);
		if (count($fp)>0) $path .= '/';
		$path .= $fp_file;
		
		return $path;
		
	}

	/**
	 * Return the absolute path of file $RelativePath which is relative to the full path $RelativeTo.
	 * For example:
	 * '../file_a.xml' relatively to 'dir1/dir2/file_b.xml' is 'dir1/file_a.xml'
	 */    
	function OpenXML_GetAbsolutePath($RelativePath, $RelativeTo) {
		
		// May be reltaive to the root
		if (substr($RelativePath, 0, 1) == '/') {
			return substr($RelativePath, 1);
		}

		$rp = explode('/', $RelativePath);
		$rt = explode('/', $RelativeTo);
		
		// Get off the file name;
		array_pop($rt);
		
		while ($rp[0] == '..') {
			array_pop($rt);
			array_shift($rp);
		}
		
		while ($rp[0] == '.') {
			array_shift($rp);
		}
		
		$path = array_merge($rt, $rp);
		$path = implode('/', $path);
		
		return $path;
		
	}
	
	function OpenXML_GetMediaRelativeToCurrent() {
		$file = $this->TBS->OtbsCurrFile;
		$x = explode('/', $file);
		$dir = $x[0] . '/media';
		return $this->OpenXML_GetRelativePath($dir, $file);
	}

	/**
	 * Return the absolute internal path of a target for a given Rid used in the current file.
	 */
	function OpenXML_GetInternalPicPath($Rid) {
		// $this->OpenXML_CTypesPrepareExt($InternalPicPath, '');
		$TargetDir = $this->OpenXML_GetMediaRelativeToCurrent();
		$o = $this->OpenXML_Rels_GetObj($this->TBS->OtbsCurrFile, $TargetDir);
		if (isset($o->TargetLst[$Rid])) {
			$x = $o->TargetLst[$Rid]; // relative path
			return $this->OpenXML_GetAbsolutePath($x, $this->TBS->OtbsCurrFile);
		} else {
			return false;
		}
	}
	
	/**
	 * Delete an XML file in the OpenXML archive.
	 * The file is delete from the declaration file [Content_Types].xml and from the relationships of the specified files.
	 * @param {string} $FullPath The full path of the file to delete.
	 * @param {array}  $RelatedTo List of the the full paths of the files than may have relationship with the file to delete.
	 * @return {mixed} False if it is not possible to delete the file, or the number of modifier relations ship in case of success (may be 0). 
	 */
	function OpenXML_DeleteFile($FullPath, $RelatedTo) {

		// Delete the file in the archive
		$idx = $this->FileGetIdx($FullPath);
		if ($idx==false) return false;
		$this->FileReplace($idx, false);

		// Delete the declaration of the file
		$this->OpenXML_CTypesDeletePart('/' . $FullPath);
		 
		// Delete the relationships
		$nb = 0;
		foreach ($RelatedTo as $file) {
			$target = $this->OpenXML_GetRelativePath($FullPath, $file);
			$att = 'Target="' . $target . '"';
			if ($this->OpenXML_Rels_DeleteRel($file, $att)) {
				$nb++;
			}
		}
		
		return $nb;
		
	}

	/**
	 * Return the path of the Rel file in the archive for a given XML document.
	 * @param $DocPath      Full path of the sub-file in the archive
	 */
	function OpenXML_Rels_GetPath($DocPath) {
		$DocName = basename($DocPath);
		return str_replace($DocName,'_rels/'.$DocName.'.rels',$DocPath);
	}

	/**
	 * Delete an element in a Rels file.
	 * Take car that there is another technic for listing and adding targets wish is working with a persistent object which is commit at the end of the merge..
	 * @param string $DocPath   The fullpath of the document file.
	 * @param string $AttExpr   The target att expression to find.
	 * @param string|boolean $ReturnAttLst The list of att values to return.
	 * @return mixed $ReturnAttVal (or True) if the change is applied.
	 */
	function OpenXML_Rels_DeleteRel($DocPath, $AttExpr, $ReturnAttLst = false) {
	
		$RelsPath = $this->OpenXML_Rels_GetPath($DocPath);
		$idx = $this->FileGetIdx($RelsPath);
		if ($idx===false) $this->RaiseError("Cannot edit target in '$RelsPath' because the file is not found.");
		$txt = $this->TbsStoreGet($idx, 'Replace target in rels file');
		
		$loc = clsTbsXmlLoc::FindElementHavingAtt($txt, $AttExpr, 0);
		if ($loc) {
			$ret = true;
			if (is_array($ReturnAttLst)) {
				$ret = array();
				foreach ($ReturnAttLst as $att) {
					$ret[$att] = $loc->GetAttLazy($att);
				}
			}
			$loc->Delete();
			$this->TbsStorePut($idx, $txt);
			return $ret;
		} else {
			return false;
		}
	
	}

	/**
	 * Return an object that represents the informations of an .rels file, but for optimization, targets are scanned only for asked directories.
	 * The result is stored in a cache so that a second call will not compute again.
	 * The function stores Rids of files existing in a the $TargetPrefix directory of the archive (image, ...).
	 * @param $DocPath      Full path of the sub-file in the archive
	 * @param $TargetPrefix Prefix of the 'Target' attribute. For example $TargetPrefix='../drawings/'
	 */
	function OpenXML_Rels_GetObj($DocPath, $TargetPrefix) {

		if ($this->OpenXmlRid===false) $this->OpenXmlRid = array();

		// Create the object if it does not exist yet
		if (!isset($this->OpenXmlRid[$DocPath])) {

			$o = (object) null;
			$o->RidLst = array();    // Current Rids in the template ($Target=>$Rid)
			$o->TargetLst = array(); // Current Targets in the template ($Rid=>$Target)
			$o->RidNew = array();    // New Rids to add at the end of the merge
			$o->DirLst = array();    // Processed target dir
			$o->ChartLst = false;    // Chart list, computed in another method

			$o->FicPath = $this->OpenXML_Rels_GetPath($DocPath);

			$FicIdx = $this->FileGetIdx($o->FicPath);
			if ($FicIdx===false) {
				$o->FicType = 1;
				$Txt = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';
			} else {
				$o->FicIdx = $FicIdx;
				$o->FicType = 0;
				$Txt = $this->FileRead($FicIdx, true);
			}
			$o->FicTxt = $Txt;
			$o->ParentIdx = $this->FileGetIdx($DocPath);

			$this->OpenXmlRid[$DocPath] = &$o;

		} else {

			$o = &$this->OpenXmlRid[$DocPath];
			$Txt = &$o->FicTxt;

		}

		// Feed the Rid and Target lists for the asked directory
		if (!isset($o->DirLst[$TargetPrefix])) {

			$o->DirLst[$TargetPrefix] = true;

			// read existing Rid in the file
			$zTarget = ' Target="'.$TargetPrefix;
			$zId  = ' Id="';
			$p = -1;
			while (($p = strpos($Txt, $zTarget, $p+1))!==false) {
				// Get the target name
				$p1 = $p + strlen($zTarget);
				$p2 = strpos($Txt, '"', $p1);
				if ($p2===false) return $this->RaiseError("(OpenXML) end of attribute Target not found in position ".$p1." of sub-file ".$o->FicPath);
				$TargetEnd = substr($Txt, $p1, $p2 -$p1);
				$Target = $TargetPrefix.$TargetEnd;
				// Get the Id
				$p1 = strrpos(substr($Txt,0,$p), '<');
				if ($p1===false) return $this->RaiseError("(OpenXML) beginning of tag not found in position ".$p." of sub-file ".$o->FicPath);
				$p1 = strpos($Txt, $zId, $p1);
				if ($p1!==false) {
					$p1 = $p1 + strlen($zId);
					$p2 = strpos($Txt, '"', $p1);
					if ($p2===false) return $this->RaiseError("(OpenXML) end of attribute Id not found in position ".$p1." of sub-file ".$o->FicPath);
					$Rid = substr($Txt, $p1, $p2 - $p1);
					$o->RidLst[$Target] = $Rid;
					$o->TargetLst[$Rid] = $Target;
				}
			}

		}

		return $o;

	}

	/* 
	* Add a new Rid in the file in the Rels file. Return the Rid.
	* Rels files are attached to XML files and are listing, and gives all rids and their corresponding targets used in the XML file.
	*/
	function OpenXML_Rels_AddNewRid($DocPath, $TargetDir, $FileName) {

		$o = $this->OpenXML_Rels_GetObj($DocPath, $TargetDir);

		$Target = $TargetDir.$FileName;

		if (isset($o->RidLst[$Target])) return $o->RidLst[$Target];

		// Add the Rid in the information
		$NewRid = 'opentbs'.(1+count($o->RidNew));
		$o->RidLst[$Target] = $NewRid;
		$o->RidNew[$Target] = $NewRid;

		$this->IdxToCheck[$o->ParentIdx] = $o->FicIdx;

		return $NewRid;

	}

	// Save the changes in the rels files (works only for images for now)
	function OpenXML_Rels_CommitNewRids ($Debug) {

		foreach ($this->OpenXmlRid as $doc => $o) {

			if (count($o->RidNew)>0) {

				// search position for insertion
				$p = strpos($o->FicTxt, '</Relationships>');
				if ($p===false) return $this->RaiseError("(OpenXML) closing tag </Relationships> not found in subfile ".$o->FicPath);

				// build the string to insert
				$x = '';
				foreach ($o->RidNew as $target=>$rid) {
					$x .= '<Relationship Id="'.$rid.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="'.$target.'"/>';
				}

				// insert
				$o->FicTxt = substr_replace($o->FicTxt, $x, $p, 0);

				// save
				if ($o->FicType==1) {
					$this->FileAdd($o->FicPath, $o->FicTxt);
				} else {
					$this->FileReplace($o->FicIdx, $o->FicTxt);
				}

				// debug mode
				if ($Debug) $this->DebugLst[$o->FicPath] = $o->FicTxt;
				
				$this->OpenXmlRid[$doc]->RidNew = array(); // Erase the Rid done because there can be another commit

			}
		}

	}

	/**
	 * Initialize modifications in '[Content_Types].xml'.
	 */
	function OpenXML_CTypesInit() {
		if ($this->OpenXmlCTypes===false){
			$this->OpenXmlCTypes = array(
				'Extension'=>array(),
				'PartName'=>array()
			);
		}
	}

	/**
	 * Prepare information for adding a content type for an extension.
	 * It needs to be completed when a new picture file extension is added in the document.
	 */
	function OpenXML_CTypesPrepareExt($FileOrExt, $ct='') {

		$ext = $this->Misc_FileExt($FileOrExt);

		$this->OpenXML_CTypesInit();
		
		$lst =& $this->OpenXmlCTypes['Extension'];
		if (isset($lst[$ext]) && ($lst[$ext]!=='') ) return;

		if (($ct==='') && isset($this->ExtInfo['pic_ext'][$ext])) $ct = 'image/'.$this->ExtInfo['pic_ext'][$ext];

		$lst[$ext] = $ct;

	}

	/**
	 * Delete a file in the declaration file.
	 * @param $PartName : path of the file to delete
	 */
	function OpenXML_CTypesDeletePart($PartName) {
		$this->OpenXML_CTypesInit();
		$this->OpenXmlCTypes['PartName'][$PartName] = false;
	}
	
	function OpenXML_CTypesCommit($Debug) {

		$file = '[Content_Types].xml';
		$idx = $this->FileGetIdx($file);
		if ($idx===false) {
			$Txt = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>';
		} else {
			$Txt = $this->FileRead($idx, true);
		}

		$ok = false;
		
		// Delete PartNames
		foreach ($this->OpenXmlCTypes['PartName'] as $part=>$val) {
			if ($val===false) {
				$loc = clsTbsXmlLoc::FindElementHavingAtt($Txt, 'PartName="'.$part.'"', 0);
				if ($loc!==false) {
					$loc->ReplaceSrc('');
					$ok = true;
				}
			}
		}		

		// Add missing extensions
		$x = '';
		foreach ($this->OpenXmlCTypes['Extension'] as $ext=>$ct) {
			$p = strpos($Txt, ' Extension="'.$ext.'"');
			if ($p===false) {
				if ($ct==='') {
					$this->RaiseError("(OpenXML) '".$ext."' is not an picture's extension recognize by OpenTBS.");
				} else {
					$x .= '<Default Extension="'.$ext.'" ContentType="'.$ct.'"/>';
				}
			}
		}
		if ($x!=='') {
			$p = strpos($Txt, '</Types>'); // search position for insertion
			if ($p===false) return $this->RaiseError("(OpenXML) closing tag </Types> not found in subfile ".$file);
			$Txt = substr_replace($Txt, $x, $p ,0);
			$ok = true;
		}


		if ($ok) {
			// debug mode
			if ($Debug) $this->DebugLst[$file] = $Txt;

			if ($idx===false) {
				$this->FileAdd($file, $Txt);
			} else {
				$this->FileReplace($idx, $Txt);
			}

		}

	}

	function OpenXML_FirstPicAtt($Txt, $Pos, $Backward) {
	// search the first image element in the given direction. Two types of image can be found. Return the value required for "att" parameter.
		$TypeVml = '<v:imagedata ';
		$TypeDml = '<a:blip ';

		if ($Backward) {
			// search the last image position this code is compatible with PHP 4
			$p = -1;
			$pMax = -1;
			$t_curr = $TypeVml;
			$t = '';
			do {
				$p = strpos($Txt, $t_curr, $p+1);
				if ( ($p===false) || ($p>=$Pos) ) {
					if ($t_curr===$TypeVml) {
						// we take a new search for the next type of image
						$t_curr = $TypeDml;
						$p = -1;
					} else {
						$p = false;
					}
				} elseif ($p>$pMax) {
					$pMax = $p;
					$t = $t_curr;
				}
			} while ($p!==false);
		} else {
			$p1 = strpos($Txt, $TypeVml, $Pos);
			$p2 = strpos($Txt, $TypeDml, $Pos);
			if (($p1===false) && ($p2===false)) {
				$t = '';
			} elseif ($p1===false) {
				$t = $TypeDml;
			} elseif ($p2===false) {
				$t = $TypeVml;
			} else {
				$t = ($p1<$p2) ? $TypeVml : $TypeDml;
			}
		}

		if ($t===$TypeVml) {
			return 'v:imagedata#r:id';
		} elseif ($t===$TypeDml) {
			return 'a:blip#r:embed';
		} else {
			return false;
		}

	}

	function OpenXML_MapInit() {
	// read the Content_Type XML file and save a sumup in the OpenXmlMap property.

		$this->OpenXmlMap = array();
		$Map =& $this->OpenXmlMap;

		$file = '[Content_Types].xml';
		$idx = $this->FileGetIdx($file);
		if ($idx===false) return;

		$Txt = $this->FileRead($idx, true);

		$type = ' ContentType="application/vnd.openxmlformats-officedocument.';
		$type_l = strlen($type);
		$name = ' PartName="';
		$name_l = strlen($name);

		$p = -1;
		while ( ($p=strpos($Txt, '<', $p+1))!==false) {
			$pe = strpos($Txt, '>', $p);
			if ($pe===false) return; // syntax error in the XML
			$x = substr($Txt, $p+1, $pe-$p-1);
			$pi = strpos($x, $type);
			if ($pi!==false) {
				$pi = $pi + $type_l;
				$pc = strpos($x, '"', $pi);
				if ($pc===false) return; // syntax error in the XML
				$ShortType = substr($x, $pi, $pc-$pi); // content type's short value
				$pi = strpos($x, $name);
				if ($pi!==false) {
					$pi = $pi + $name_l;
					$pc = strpos($x, '"', $pi);
					if ($pc===false) return; // syntax error in the XML
					$Name = substr($x, $pi, $pc-$pi); // name
					if ($Name[0]=='/') $Name = substr($Name,1); // fix the file path
					if (!isset($Map[$ShortType])) $Map[$ShortType] = array();
					$Map[$ShortType][] = $Name;
				}
			}
			$p = $pe;
		}

	}

	function OpenXML_MapGetFiles($ShortTypes) {
	// Return all values for a given type (or array of types) in the map.
		if (is_string($ShortTypes)) $ShortTypes = array($ShortTypes);
		$res = array();
		foreach ($ShortTypes as $type) {
			if (isset($this->OpenXmlMap[$type])) {
				$val = $this->OpenXmlMap[$type];
				foreach ($val as $file) $res[] = $file;
			}
		}
		return $res;
	}

	function OpenXML_MapGetMain($ShortType, $Default) {
	// Return all values for a given type (or array of types) in the map.
		if (isset($this->OpenXmlMap[$ShortType])) {
			return $this->OpenXmlMap[$ShortType][0];
		} else {
			return $Default;
		}
	}

	/**
	 * Build the list of chart files.
	 */
	function OpenXML_ChartInit() {

		$this->OpenXmlCharts = array();

		foreach ($this->CdFileByName as $f => $i) {
			// Note : some of liste files are style or color files, not chart.
			if (strpos($f, '/charts/')!==false) {
				$x = explode('/',$f);
				$n = count($x) -1;
				if ( ($n>=2) && ($x[$n-1]==='charts') ) {
					$x = $x[$n]; // name of the xml file
					if (substr($x,-4)==='.xml') {
						$x = substr($x,0,strlen($x)-4);
						$this->OpenXmlCharts[$x] = array('idx'=>$i, 'clean'=>false, 'series'=>false);
					}
				}
			}
		}

	}

	function OpenXML_ChartDebug($nl, $sep, $bull) {

		if ($this->OpenXmlCharts===false) $this->OpenXML_ChartInit();

		echo $nl;
		echo $nl."Charts technically stored in the document: (use command OPENTBS_CHART_INFO to get series's names and data)";
		echo $nl."------------------------------------------";

		// list of supported charts
		$nbr = 0;
		foreach ($this->OpenXmlCharts as $key => $info) {
			$ok = true;
			if (!isset($info['series_nbr'])) {
				$txt = $this->FileRead($info['idx'], true);
				$info['series_nbr'] = substr_count($txt, '<c:ser>');
				$ok = (strpos($txt, '<c:chart>')!==false);
			}
			if ($ok) {
				$nbr++;
				echo $bull."name: '".$key."' , number of series: ".$info['series_nbr'];
			}
		}

		if ($this->TbsCurrIdx===false) {
			echo $bull."(unable to scann more because no subfile is loaded)";
		} else {
			$x = ' ProgID="MSGraph.Chart.';
			$x_len = strlen($x);
			$p = 0;
			$txt = $this->TBS->Source;
			while (($p=strpos($txt, $x, $p))!==false) {
				// check that the text is inside an xml tag
				$p = $p + $x_len;
				$p1 = strpos($txt, '>', $p);
				$p2 = strpos($txt, '<', $p);
				if ( ($p1!==false) && ($p2!==false) && ($p1<$p2) ) {
					$nbr++;
					$p1 = strpos($txt, '"', $p);
					$z = substr($txt, $p, $p1-$p);
					echo $bull."1 chart created using MsChart version ".$z." (series can't be merged with OpenTBS)";
				}
			}
		}

		if ($nbr==0) echo $bull."(none)";

	}

	/**
	 * Search for the series in the chart definition
	 * @return mixed An Array if success, or a string if error.
	 */
	function OpenXML_ChartSeriesFound(&$Txt, $SeriesNameOrNum, $OnlyBounds=false) {

		$IsNum = is_numeric($SeriesNameOrNum);
		if ($IsNum) {
			$p = strpos($Txt, '<c:order val="'.($SeriesNameOrNum-1).'"/>');
			if ($p===false) return "Number of the series not found.";
		} else {
			$SeriesNameOrNum = htmlspecialchars($SeriesNameOrNum, ENT_NOQUOTES); // ENT_NOQUOTES because target is an element's content
			$p = strpos($Txt, '>'.$SeriesNameOrNum.'<');
			if ($p===false) return "Name of the series not found.";
			$p++;
		}

		$res = array('p'=>$p);

		if ($OnlyBounds) {
			if ($loc = clsTbsXmlLoc::FindElement($Txt, 'c:ser', $p, false)) {
				$res['p'] = $loc->PosBeg;
				$res['l'] = $loc->PosEnd - $loc->PosBeg + 1;
				return $res;
			} else {
				return "XML entity not found.";
			}
		}

		// faster than clsTbsXmlLoc::FindElement
		$end_tag = '</c:ser>';
		$end = strpos($Txt, $end_tag, $p);
		$len = $end + strlen($end_tag) - $p;
		$res['l'] = $len;

		$x = substr($Txt, $p, $len);

		// Legend, may be absent
		$p = 0;
		if ($IsNum) {
			$p1 = strpos($x, '<c:tx>');
			if ($p1>0) {
				$p2 = strpos($x, '</c:tx>', $p1);
				$tag = '<c:v>';
				$p1 = strpos($x, $tag, $p1);
				if ( ($p1!==false) && ($p1<$p2) ) {
					$p1 = $p1 + strlen($tag);
					$p2 = strpos($x, '<', $p1);
					$res['leg_p'] = $p1;
					$res['leg_l'] = $p2 - $p1;
					$p = $p2;
				} 
			}
		} else {
			$res['leg_p'] = 0;
			$res['leg_l'] = strlen($SeriesNameOrNum);
		}

		// Data X & Y, we assume that (X or Category) are always first and (Y or Value) are always second
		// Some charts may not have categories, they cannot be merged :-(
		for ($i=1; $i<=2; $i++) {
			$p1 = strpos($x, '<c:ptCount ', $p);
			if ($p1===false) return ($i==1) ? "categories or values not found." : "categories not found, check the chart to add categories.";
			$p2 = strpos($x, 'Cache>', $p1); // the closing tag can be </c:numCache> or </c:strCache>
			if ($p2===false) return "Cached data not found for categories or values.";
			$p2 = $p2 - 7;
			$res['point'.$i.'_p'] = $p1;
			$res['point'.$i.'_l'] = $p2 - $p1;
			$p = $p2;
		}

		return $res;

	}

	/**
	 * Find a chart in the template by its reference.
	 * Returns the OpenTBS's internal chart ref if found.
	 */
	function OpenXML_ChartFind($ChartRef, $ErrTitle) {
		
		if ($this->OpenXmlCharts===false) $this->OpenXML_ChartInit();
		
 		$ref = ''.$ChartRef;
		// try with $ChartRef as number
		if (!isset($this->OpenXmlCharts[$ref])) {
			$ref = 'chart'.$ref;
		}
		// try with $ChartRef as name of the file
		if (!isset($this->OpenXmlCharts[$ref])) {
			$charts = array();
			$idx = false;
			if ($this->ExtEquiv=='pptx') {
				// search in slides
				$find = $this->MsPowerpoint_SearchInSlides(' title="'.$ChartRef.'"');
				$idx = $find['idx'];
			} else {
				$idx =$this->Ext_GetMainIdx();
			}
			if ($idx !== false) {
				$charts = $this->OpenXML_ChartGetInfoFromFile($idx);
			}
			// Search the chart having the title
			foreach($charts as $c) {
				if ($c['title']===$ChartRef) $ref = $c['name'];
			}
			if (isset($this->OpenXmlCharts[$ref])) {
				$chart = &$this->OpenXmlCharts[$ref];
				$this->OpenXmlCharts[$ChartRef] = &$chart; 
				// For debug
				$chart['parent_idx'] = $idx;
			} else {
				return $this->RaiseError("($ErrTitle) : unable to found the chart corresponding to '".$ChartRef."'.");
			}
		}
		
		return $ref;
		
	}
	
	function OpenXML_ChartChangeSeries($ChartRef, $SeriesNameOrNum, $NewValues, $NewLegend=false) {
		
		// Search the chart
		$ref = $this->OpenXML_ChartFind($ChartRef, 'ChartChangeSeries');
		if ($ref===false) return false;

		// Open the chart doc
		$chart =& $this->OpenXmlCharts[$ref];
		$Txt = $this->TbsStoreGet($chart['idx'], 'ChartChangeSeries');
		if ($Txt===false) return false;

		if (!$chart['clean']) {
			$this->OpenXML_ChartUnlinklDataSheet($chart['idx'], $Txt, $this->TBS->OtbsDeleteObsoleteChartData);
			$chart['nbr'] = substr_count($Txt, '<c:ser>');
			$chart['clean'] = true;
		}

		$Delete = ($NewValues===false);
		if (is_array($SeriesNameOrNum)) return $this->RaiseError("(ChartChangeSeries) '$ChartRef' : The series reference is an array, a string or a number is expected. ".$ChartRef."'."); // usual mistake in arguments
		$ser = $this->OpenXML_ChartSeriesFound($Txt, $SeriesNameOrNum, $Delete);
		if (!is_array($ser)) return $this->RaiseError("(ChartChangeSeries) '$ChartRef' : unable change series '".$SeriesNameOrNum."' in the chart '".$ref."' : ".$ser);

		if ($Delete) {

			$Txt = substr_replace($Txt, '', $ser['p'], $ser['l']);

		} else {


			$point1 = ''; // category
			$point2 = ''; // value
			$i = 0;
			$v = reset($NewValues);
			if (is_array($v)) {
				// syntax 2: $NewValues = array( array('cat1','cat2',...), array(val1,val2,...) );
				$k = key($NewValues);
				$key_lst = &$NewValues[$k];
				$val_lst = &$NewValues[1];
				$simple = false;
			} else {
				// syntax 1: $NewValues = array('cat1'=>val1, 'cat2'=>val2, ...);
				$key_lst = &$NewValues;
				$val_lst = &$NewValues;
				$simple = true;
			}
			foreach ($key_lst as $k=>$v) {
				if ($simple) {
					$x = $k;
					$y = $v;
				} else {
					$x = $v;
					$y = isset($val_lst[$k]) ? $val_lst[$k] : null;
				}
				// a category should not be missing otherwise it caption may not be display if the series is the first one
				$point1 .= '<c:pt idx="'.$i.'"><c:v>'.$x.'</c:v></c:pt>';
				// a missing value is possible
				if ( (!is_null($y)) && ($y!==false) && ($y!=='') && ($y!=='NULL') ) {
					$point2 .= '<c:pt idx="'.$i.'"><c:v>'.$y.'</c:v></c:pt>';
				}
				$i++;
			} 
			$point1 = '<c:ptCount val="'.$i.'"/>'.$point1;
			$point2 = '<c:ptCount val="'.$i.'"/>'.$point2; // yes, the count is the same as point1 whenever missing values

			// change info in reverse order of placement in order to avoid exention problems
			$p = $ser['p'];
			$Txt = substr_replace($Txt, $point2, $p+$ser['point2_p'], $ser['point2_l']);
			$Txt = substr_replace($Txt, $point1, $p+$ser['point1_p'], $ser['point1_l']);
			if ( (is_string($NewLegend)) && isset($ser['leg_p']) && ($ser['leg_p']<$ser['point1_p']) ) {
				$NewLegend = htmlspecialchars($NewLegend, ENT_NOQUOTES); // ENT_NOQUOTES because target is an element's content
				$Txt = substr_replace($Txt, $NewLegend, $p+$ser['leg_p'], $ser['leg_l']);
			}

		}

		$this->TbsStorePut($chart['idx'], $Txt, true);

		return true;

	}

	/**
	 * Return the list of all charts in the current sub-file, with title and description if any.
	 */
	function OpenXML_ChartGetInfoFromFile($idx, $Txt=false) {

		if ($idx===false) return false;

		$file = $this->CdFileLst[$idx]['v_name'];
		$relative = (substr_count($file, '/')==1) ? '' : '../';
		$o = $this->OpenXML_Rels_GetObj($file, $relative.'charts/');

		if ($o->ChartLst===false) {

			if ($Txt===false) $Txt = $this->TbsStoreGet($idx, 'OpenXML_ChartGetInfoFromFile');

			$o->ChartLst = array();

			$p = 0;
			while ($t = clsTbsXmlLoc::FindStartTag($Txt, 'c:chart', $p)) {
				$rid = $t->GetAttLazy('r:id');
				$name = false;
				$title = false;
				$descr = false;
				$parent = clsTbsXmlLoc::FindStartTag($Txt, 'w:drawing', $t->PosBeg, false); // DOCX <w:drawing> can embeds <wp:inline> if inline with text, or <wp:anchor> otherwise
				if ($parent===false) $parent = clsTbsXmlLoc::FindStartTag($Txt, 'p:nvGraphicFramePr', $t->PosBeg, false); // PPTX
				if ($parent!==false) {
					$parent->FindEndTag();
					$src = $parent->GetInnerSrc();
					$el = clsTbsXmlLoc::FindStartTagHavingAtt($src, 'title', 0);
					if ($el!==false) $title = $el->GetAttLazy('title');
					$el = clsTbsXmlLoc::FindStartTagHavingAtt($src, 'descr', 0);
					if ($el!==false) $descr = $el->GetAttLazy('descr');
				}

				if (isset($o->TargetLst[$rid])) {
					$name = basename($o->TargetLst[$rid]);
					if (substr($name,-4)==='.xml') $name = substr($name,0,strlen($name)-4);
				}
				$o->ChartLst[] = array('rid'=>$rid, 'title'=>$title, 'descr'=>$descr, 'name'=>$name);
				$p = $t->PosEnd;
			}

		}

		return $o->ChartLst;

	}

	/**
	 * Unlink and eventually delete the data sheet from the chart.
	 * Each chart can have only 1 linked data sheet. It may be external or internal.
	 * Each internal data sheet can be linked to only 1 chart. So it is safe to delete the internal data sheet.
	 * If the chart stay linked to the old data sheet afert the merge, then the chart is automatically updated when the user attempt to edit it. This is not good.
	 * If the data sheet is simply unlinked, the user can open the data sheet from Word of Powerpoint. But that will not change the chart.
	 * If the data sheet is delete, the user cannot open the data sheet and cannot add a new data sheet. Data of the chart stay uneditable.
	 */
	function OpenXML_ChartUnlinklDataSheet($idx, &$Txt, $Delete) {

		if ($Delete) {
			if ($loc = clsTbsXmlLoc::FindElement($Txt, 'c:externalData', 0)) {
				// Delete the relationship
				$rid = $loc->GetAttLazy('r:id');
				if ($rid) {
					$doc = $this->TbsGetFileName($idx);
					$att = 'Id="' . $rid . '"';
					$res = $this->OpenXML_Rels_DeleteRel($doc, $att, array('Target', 'TargetMode'));
					// Delete the target file if embedded
					if ($res && ($res['TargetMode'] != 'External')) {
						$file = $this->OpenXML_GetAbsolutePath($res['Target'], $doc);
						$this->FileReplace($file, false);
					}
				}
				// Delete the element
				$loc->Delete();
			}
		}

		// Unlink the data sheet by deleting references
		$this->XML_DeleteElements($Txt, array('c:f'));
	}

	/**
	 * Return information and adata about all series in the chart.
	 */
	function OpenXML_ChartReadSeries($ChartRef, $Complete) {
		
		// Search the chart
		$ref = $this->OpenXML_ChartFind($ChartRef, 'ChartReadSerials');
		if ($ref===false) return false;

		// Open the chart doc
		$chart =& $this->OpenXmlCharts[$ref];

		$Txt = $this->TbsStoreGet($chart['idx'], 'ChartReadSerials');
		if ($Txt===false) return false;

		// Prepare loops
		$serials = array();
		
		$loop_conf = array(
			'names' => array('parent' => 'c:tx',  'format' => false),
			'cat'   => array('parent' => 'c:cat', 'format' => 'c:formatCode'),
			'val'   => array('parent' => 'c:val', 'format' => 'c:formatCode'),
		);

		// Loop
		$loop_res = array();
		$ser_p = 0;
		while ($ser_loc = clsTbsXmlLoc::FindElement($Txt, 'c:ser', $ser_p)) {
			$res = array();
			foreach ($loop_conf as $key => $conf) {
				if ($loc_parent = clsTbsXmlLoc::FindElement($ser_loc, $conf['parent'], 0)) {
					// Search format
					$format = false;
					if ($conf['format']) {
						if ($loc = clsTbsXmlLoc::FindElement($loc_parent, $conf['format'], 0)) {
							$format = $loc->GetInnerSrc();
							$res[$key . '_format'] = $format;
						}
					}
					// Search items
					// It is possible that a val item is missing for a cat idx
					$items = array();
					$loc_p = 0;
					while ($loc_pt = clsTbsXmlLoc::FindElement($loc_parent, 'c:pt', $loc_p)) {
						$idx = $loc_pt->GetAttLazy('idx');
						$loc = clsTbsXmlLoc::FindElement($loc_pt, 'c:v', 0);
						$items[$idx] = $loc->GetInnerSrc();
						$loc_p = $loc_pt->PosEnd;
					}
					$res[$key] = $items;
				} else {
					$res[$key] = false;
				}
			}
			
			// simplify name info
			$names = $res['names'];
			if (is_array($names) && isset($res['names'][0])) {
				$res['name'] = $res['names'][0];
			} else {
				$res['name'] = false;
			}
			if (is_array($names)) {
				if (count($names) > 0) {
					unset($res['names']);
				}
			} else {
				unset($res['names']);
			}

			$loop_res[] = $res;
			$ser_p = $ser_loc->PosEnd;
		}
		
		if ($Complete) {
			return array(
				'file_idx' => $chart['idx'],
				'file_name' => $this->TbsGetFileName($chart['idx']),
				'parent_idx' => $chart['parent_idx'],
				'parent_name' => $this->TbsGetFileName($chart['parent_idx']),
				'series' => $loop_res,
			);
		} else {
			$series = array();
			foreach ($loop_res as $res) {
				$series[$res['name']] = array($res['cat'], $res['val']);
			}
			return $series;
		}
		
		return $loop_res;

	}
	
	function OpenXML_SharedStrings_Prepare() {

		$file = 'xl/sharedStrings.xml';
		$idx = $this->FileGetIdx($file);
		if ($idx===false) return;

		$Txt = $this->TbsStoreGet($idx, 'Excel SharedStrings');
		if ($Txt===false) return false;
		$this->TbsStorePut($idx, $Txt); // save for any further usage

		$this->OpenXmlSharedStr = array();
		$this->OpenXmlSharedSrc =& $this->TbsStoreLst[$idx]['src'];

	}

	function OpenXML_SharedStrings_GetVal($id) {
	// this function return the XML content of the string and put previous met values in cache
		if ($this->OpenXmlSharedStr===false) $this->OpenXML_SharedStrings_Prepare();

		$Txt =& $this->OpenXmlSharedSrc;

		if (!isset($this->OpenXmlSharedStr[$id])) {
			$last_id = count($this->OpenXmlSharedStr) - 1; // last id in the cache
			if ($last_id<0) {
				$p2 = 0; // no items found yet
			} else {
				$p2 = $this->OpenXmlSharedStr[$last_id]['end'];
			}
			$x1 = '<si'; // SharedString Item
			$x1_len = strlen($x1);
			$x2 = '</si>';
			while ($last_id<$id) {
				$last_id++;
				$p1 = strpos($Txt, $x1, $p2+1);
				if ($p1===false) return $this->RaiseError("(Excel SharedStrings) id $id is searched but id $last_id is not found.");
				$p1 = strpos($Txt, '>', $p1+$x1_len)+1;
				$p2 = strpos($Txt, $x2, $p1);
				if ($p2===false) return $this->RaiseError("(Excel SharedStrings) id $id is searched but no closing tag found for id $last_id.");
				$this->OpenXmlSharedStr[$last_id] = array('beg'=>$p1, 'end'=>$p2, 'len'=>($p2-$p1));
			}
		}

		$str =& $this->OpenXmlSharedStr[$id];

		return substr($Txt, $str['beg'], $str['len']);

	}

	// Delete unreferenced images
	function OpenMXL_GarbageCollector() {

		if ( (count($this->IdxToCheck)==0) && (count($this->OtbsSheetSlidesDelete)==0) ) return;

		// Key for Pictures
		$pic_path = $this->ExtInfo['pic_path'];
		$pic_path_len = strlen($pic_path);

		// Key for Rels
		$rels_ext = '.rels';
		$rels_ext_len = strlen($rels_ext);

		// List all Pictures and Rels files
		$pictures = array();
		$rels = array();
		foreach ($this->CdFileLst as $idx=>$f) {
			$n = $f['v_name'];
			if (substr($n, 0, $pic_path_len)==$pic_path) {
				$short = basename($pic_path).'/'.basename($n);
				$pictures[] = array('name'=>$n, 'idx'=>$idx, 'nbr'=>0, 'short'=>$short);
			} elseif (substr($n, -$rels_ext_len)==$rels_ext) {
				if ($this->FileGetState($idx)!='d') $rels[$n] = $idx;
			}
		}

		// Read contents or Rels files
		foreach ($rels as $n=>$idx) {
			$txt = $this->TbsStoreGet($idx, 'GarbageCollector');
			foreach ($pictures as $i=>$info) {
				if (strpos($txt, $info['short'].'"')!==false) $pictures[$i]['nbr']++;
			}
		}

		// Delete unused Picture files
		foreach ($pictures as $info) {
			if ($info['nbr']==0) $this->FileReplace($info['idx'], false);
		}

		
	}

	function MsExcel_ConvertToRelative(&$Txt) {
		// <row r="10" ...> attribute "r" is optional since missing row are added using <row />
		// <c r="D10" ...> attribute "r" is optional since missing cells are added using <c />
		$Loc = new clsTbsLocator;
		$this->MsExcel_ConvertToRelative_Item($Txt, $Loc, 'row', 'r', true);
	}

	function MsExcel_ConvertToRelative_Item(&$Txt, &$Loc, $Tag, $Att, $IsRow) {
	// convert tags $Tag which have a position (defined with attribute $Att) into relatives tags without attribute $Att. Missing tags are added as empty tags.
		$item_num = 0;
		$tag_len = strlen($Tag);
		$missing = '<'.$Tag.'/>';
		$closing = '</'.$Tag.'>';
		$p = 0;
		$compat_limit_miss = 1000;
		$compat_limit_num = 1048576 - 10000;
		while (($p=clsTinyButStrong::f_Xml_FindTagStart($Txt, $Tag, true, $p, true, true))!==false) {

			$Loc->PrmPos = array();
			$Loc->PrmLst = array();
			$p2 = $p + $tag_len + 2; // count the char '<' before and the char ' ' after
			$PosEnd = strpos($Txt, '>', $p2);
			clsTinyButStrong::f_Loc_PrmRead($Txt,$p2,true,'\'"','<','>',$Loc, $PosEnd, true); // read parameters
			$Delete = false;
			if (isset($Loc->PrmPos[$Att])) {
				// attribute found
				$r = $Loc->PrmLst[$Att];
				if ($IsRow) {
					$r = intval($r);
				} else {
					$r = $this->Misc_ColNum($r, false);
				}
				$missing_nbr = $r - $item_num -1;
				if ($missing_nbr<0) {
					return $this->RaiseError('(Excel Consistency) error in counting items <'.$Tag.'>, found number '.$r.', previous was '.$item_num);
				} elseif($IsRow && ($missing_nbr > $compat_limit_miss) && ($r >= $compat_limit_num)) { // Excel limit is 1048576
					// Useless final rows: LibreOffice add several final useless rows in the sheet when saving as XLSX.
					$Delete = true;
					$item_num++;
				} else {
					// delete the $Att attribute
					$pp = $Loc->PrmPos[$Att];
					$pp[3]--; //while ($Txt[$pp[3]]===' ') $pp[3]--; // external end of the attribute, may has an extra spaces
					$x_p = $pp[0]-1; // we take out the space
					$x_len = $pp[3] - $x_p +1;
					$Txt = substr_replace($Txt, '', $x_p, $x_len);
					$PosEnd = $PosEnd - $x_len;
					// If it's a cell, we look if it's a good idea to replace the shared string
					if ( (!$IsRow) && isset($Loc->PrmPos['t']) && ($Loc->PrmLst['t']==='s') ) $this->MsExcel_ReplaceString($Txt, $p, $PosEnd);
					// add missing items before the current item
					if ($missing_nbr>0) {
						$x = str_repeat($missing, $missing_nbr);
						$x_len = strlen($x);
						$Txt = substr_replace($Txt, $x, $p, 0);
						$PosEnd = $PosEnd + $x_len;
						$x = ''; // empty the memory
					}
					$item_num = $r;
				}
			} else {
				// nothing to change the item is already relative
				$item_num++;
			}
			if ($Delete) {
				if (($Txt[$PosEnd-1]!=='/')) {
					$x_p = strpos($Txt, $closing, $PosEnd);
					if ($x_p===false) return $this->RaiseError('(Excel Consistency) closing row tag is not found.');
					$PosEnd = $x_p + strlen($closing) - 1;
				}
				$Txt = substr_replace($Txt, '', $p, $PosEnd - $p + 1);
			} elseif ($IsRow && ($Txt[$PosEnd-1]!=='/')) {
				// It's a row item that may contain columns
				$x_p = strpos($Txt, $closing, $PosEnd);
				if ($x_p===false) return $this->RaiseError('(Excel Consistency) closing row tag is not found.');
				$x_len0 = $x_p - $PosEnd -1;
				$x = substr($Txt, $PosEnd+1, $x_len0);
				$this->MsExcel_ConvertToRelative_Item($x, $Loc, 'c', 'r', false);
				$Txt = substr_replace($Txt, $x, $PosEnd+1, $x_len0);
				$x_len = strlen($x);
				$p = $x_p + $x_len - $x_len0;
			} else {
				$p = $PosEnd;
			}
		}
	
	}

	/**
	 * Add the attribute in all <row> and <c> items, and delete empty items.
	 */
	function MsExcel_ConvertToExplicit(&$Txt) {
		if (strpos($Txt, '<sheetData>')===false) return;
		$this->MsExcel_ConvertToExplicit_Item($Txt, 'row', 'r', false);
	}

	/**
	 * Add the attribute that gives the reference of the item.
	 * Return the number of inserted attributes.
	 * Note: substr() and strpos() function's execution time are geometrically increasing with then string length.
	 *       So it is for this function. converting a sheet with 5.000 rows may have a duration of 15 sec.
	 */
	function MsExcel_ConvertToExplicit_Item(&$Txt, $Tag, $Att, $ParentRowNum) {

		$tag_pc = strlen($Tag) + 1;
		$rpl = '<'.$Tag.' '.$Att.'="';
		$rpl_len = strlen($rpl);
		$rpl_nbr = 0;
		$item_num = 0;

		$p = clsTinyButStrong::f_Xml_FindTagStart($Txt, $Tag, true, 0, true, true);
		if ($p === false) return;

		if ($p === 0) {
			$Txt_Done = '';
		} else {
			$Txt_Done = substr($Txt, 0, $p);
			$Txt = substr($Txt, $p);
		}
		
		do {

			// Next item
			$p_next = clsTinyButStrong::f_Xml_FindTagStart($Txt, $Tag, true, 0 + $tag_pc, true, true);
			
			// Small text containing the current item
			if ($p_next === false) {
				$Txt_Curr = $Txt;
				$Txt = '';
			} else {
				$Txt_Curr = substr($Txt, 0, $p_next);
				$Txt = substr($Txt, $p_next);
			}

			$item_num++;
			
			if (substr($Txt_Curr, 0 + $tag_pc, 1) == '/') {

				// It's an empty item => Delete the item
				$Txt_Done .= substr($Txt_Curr, 0 + $tag_pc + 2); // +2 is for the tail '/>'

			} else {

				// The item is not empty => replace attribute and delete the previous empty item in the same time
				$ref = ($ParentRowNum===false) ? $item_num : $this->Misc_CellRef($item_num, $ParentRowNum);
				$Txt_Curr = $rpl . $ref . '"' . substr($Txt_Curr, 0 + $tag_pc);
				$rpl_nbr++;

				// If it's a row => search for cells
				if ($ParentRowNum===false) {
					$nbr = $this->MsExcel_ConvertToExplicit_Item($Txt_Curr, 'c', 'r', $item_num);
				}
				
				$Txt_Done .= $Txt_Curr;
			
			}

		} while ($p_next !== false);
		
		$Txt = $Txt_Done . $Txt;
		
		return $rpl_nbr;

	}
	
	function MsExcel_DeleteFormulaResults(&$Txt) {
	// In order to refresh the formula results when the merged XLSX is opened, then we delete all <v> elements having a formula.
		$c_close = '</c>';
		$p = 0;
		while (($p=clsTinyButStrong::f_Xml_FindTagStart($Txt, 'f', true, $p, true, true))!==false) {
			$c_p = strpos($Txt, $c_close, $p);
			if ($c_p===false) return false; // error in the XML
			$x_len0 = $c_p - $p;
			$x = substr($Txt, $p, $x_len0);
			$this->XML_DeleteElements($x, array('v'));
			$Txt = substr_replace($Txt, $x, $p, $x_len0);
			$p = $p + strlen($x);
		}
	}

	/**
	 * XLSX has a file that refers to formulas in the entire workbook in order to schedule the calculations. 
	 * The cells references in this file mey become erroneous since cell has been deleted or added in some sheets.
	 * Hopefully this file is optional. We have to deleted it.
	 */
	function MsExcel_DeleteCalcChain() {
		return $this->OpenXML_DeleteFile('xl/calcChain.xml', array('xl/workbook.xml'));
	}
	
	function MsExcel_ReplaceString(&$Txt, $p, &$PosEnd) {
	// replace a SharedString into an InlineStr only if the string contains a TBS field
		static $c = '</c>';
		static $v1 = '<v>';
		static $v1_len = 3;
		static $v2 = '</v>';
		static $v2_len = 4;

		// found position of the <c> element, and extract its contents
		$p_close = strpos($Txt, $c, $PosEnd);
		if ($p_close===false) return;
		$x_len = $p_close - $p;
		$x = substr($Txt, $p, $x_len); // [<c ...> ... ]</c>

		// found position of the <v> element, and extract its contents
		$v1_p = strpos($x, $v1);
		if ($v1_p==false) return false;
		$v2_p = strpos($x, $v2, $v1_p);
		if ($v2_p==false) return false;
		$vt = substr($x, $v1_p+$v1_len, $v2_p - $v1_p - $v1_len);

		// extract the SharedString id, and retrieve the corresponding text
		$v = intval($vt);
		if (($v==0) && ($vt!='0')) return false;
		if (isset($this->MsExcel_NoTBS[$v])) return true;
		$s = $this->OpenXML_SharedStrings_GetVal($v);

		// if the SharedSring has no TBS field, then we save the id in a list of known id, and we leave the function
		if (strpos($s, $this->TBS->_ChrOpen)===false) {
			$this->MsExcel_NoTBS[$v] = true;
			return true;
		}

		// prepare the replace
		$x1 = substr($x, 0, $v1_p);
		$x3 = substr($x, $v2_p + $v2_len);
		$x2 = '<is>'.$s.'</is>';
		$x = str_replace(' t="s"', ' t="inlineStr"', $x1).$x2.$x3;

		$Txt = substr_replace($Txt, $x, $p, $x_len);

		$PosEnd = $p + strlen($x); // $PosEnd is used to search the next item, so we update it

	}

	function MsExcel_ChangeCellType(&$Txt, &$Loc, $Ope) {
	// change the type of a cell in an XLSX file

		$Loc->PrmLst['cellok'] = $Ope; // avoid the field to be processed twice

		if ( ($Ope==='xlsxString') || ($Ope==='tbs:string')) return true;

		static $OpeLst = array(
			'tbs:bool'=>' t="b"',
			'xlsxBool'=>' t="b"',
			'xlsxDate'=>'',
			'xlsxNum'=>'',
			'tbs:date'=>'',
			'tbs:num'=>'',
			// compatibility with ODF format
			'tbs:time'=>'',
			'tbs:percent'=>'',
			'tbs:curr'=>'',
		);

		if (!isset($OpeLst[$Ope])) return false;

		$t0 = clsTinyButStrong::f_Xml_FindTagStart($Txt, 'c', true, $Loc->PosBeg, false, true);
		if ($t0===false) return false; // error in the XML structure

		$te = strpos($Txt, '>', $t0);
		if ( ($te===false) || ($te>$Loc->PosBeg) ) return false; // error in the XML structure

		$len = $te - $t0 + 1;
		$c_open = substr($Txt, $t0, $len); // '<c ...>'
		$c_open = str_replace(' t="inlineStr"', $OpeLst[$Ope], $c_open);

		$t1 = strpos($Txt, '</c>', $te);
		if ($t1===false) return false; // error in the XML structure

		$p_is1 = strpos($Txt, '<is>', $te);
		if (($p_is1===false) || ($p_is1>$t1) ) return false; // error in the XML structure

		$is2 = '</is>';
		$p_is2 = strpos($Txt, $is2, $p_is1);
		if (($p_is2===false) || ($p_is2>$t1) ) return false; // error in the XML structure
		$p_is2 = $p_is2 + strlen($is2); // move to end the of the tag

		$middle_len = $p_is1 - $te - 1;
		$middle = substr($Txt, $te + 1, $middle_len); // text bewteen <c...> and <is>

		// new tag to replace <is>...</is>
		static $v = '<v>[]</v>';
		$v_len = strlen($v);
		$v_pos = strpos($v, '[]');

		$x = $c_open.$middle.$v;

		$Txt = substr_replace($Txt, $x, $t0, $p_is2 - $t0);

		// move the TBS field
		$p_fld = $t0 + strlen($c_open) + $middle_len + $v_pos;
		$Loc->PosBeg = $p_fld;
		$Loc->PosEnd = $p_fld +1;

	}
	
	function MsExcel_ChangeCellValue(&$Loc, &$Value) {
	
		switch ($Loc->PrmLst['cellok']) {
		case 'tbs:num': 
		case 'tbs:curr': 
		case 'tbs:percent': 
		case 'xlsxNum':
			if (is_numeric($Value)) {
				// we have to check contents in order to avoid Excel errors. Note that value '0.00000000000000' makes an Excel error.
				if (strpos($Value,'e')!==false) { // exponential representation
					$Value = var_export((float) $Value, true); // this string conversion is not affected by the decimal separator given by the locale setting
				} elseif (strpos($Value,'x')!==false) { // hexa representation
					$Value = '' . hexdec($Value);
				} elseif (strpos($Value,'.')===false) {
					// it is better to not convert because of big numbers
					// intval(7580563123) returns -1009371469 in 32bits
				} else {
					$Value = var_export((float) $Value, true);
				}
			} else {
				$Value = '';
			}
			break;
		case 'tbs:bool':
		case 'xlsxBool':
			$Value = ($Value) ? 1 : 0;
			break;
		case 'tbs:date':
		case 'tbs:time':
		case 'xlsxDate':
			if (is_string($Value)) {
				$t = strtotime($Value); // We look if it's a date
			} else {
				$t = $Value;
			}
			if (($t===-1) || ($t===false)|| ($t===null)) { // Date not recognized
				$Value = '';
			} elseif ($t===943916400) { // Date to zero
				$Value = '';
			} else { // It's a date
				$Value = ($t/86400.00)+25569; // unix: 1 means 01/01/1970, xlsx: 1 means 01/01/1900
			}
			break;
		default:
			// do nothing
		}
	}

	function MsExcel_SheetInit() {

		if ($this->MsExcel_Sheets!==false) return;

		$this->MsExcel_Sheets = array();   // sheet info sorted by location

		$idx = $this->FileGetIdx('xl/workbook.xml');
		$this->MsExcel_Sheets_WkbIdx = $idx;
		if ($idx===false) return;

		$Txt = $this->TbsStoreGet($idx, 'SheetInfo'); // use the store, so the file will be available for editing if needed
		if ($Txt===false) return false;
		$this->TbsStorePut($idx, $Txt);

		// scann sheet list
		$p = 0;
		$i = 0;
		$rels = array();
		while ($loc=clsTbsXmlLoc::FindStartTag($Txt, 'sheet', $p, true) ) {
			$o = (object) null;
			$o->num = $i + 1;
			// SheetId is not the numbered sheet in the workbook. It may have a missing sheet id.
			$o->sheetId   = $loc->GetAttLazy('sheetId');
			$o->rid   = $loc->GetAttLazy('r:id');
			$o->name  = $loc->GetAttLazy('name');
			$o->state = $loc->GetAttLazy('state');
			$o->stateR = ($o->state===false) ? 'visible' : $o->state;
			$o->file  = false;
			$this->MsExcel_Sheets[$i] = $o;
			$rels[$o->rid] =& $this->MsExcel_Sheets[$i]; 
			$i++;
			$p = $loc->PosEnd;
		}

		// Retrieve Sheet files
		$idx = $this->FileGetIdx('xl/_rels/workbook.xml.rels');
		$Txt = $this->FileRead($idx);
		if ($Txt===false) return false;

		$p = 0;
		while ($loc=clsTbsXmlLoc::FindStartTag($Txt, 'Relationship', $p, true) ) {
			$rid = $loc->GetAttLazy('Id');
			if (isset($rels[$rid])) $rels[$rid]->file =  $loc->GetAttLazy('Target');
			$p = $loc->PosEnd;
		}

	}

	function MsExcel_SheetGet($IdOrName, $bySheetId = false) {
		$this->MsExcel_SheetInit();
		foreach($this->MsExcel_Sheets as $o) {
			if ($o->name==$IdOrName) return $o;
			if ($bySheetId) {
				if ($o->sheetId==$IdOrName) return $o;
			} else {
				if ($o->num==$IdOrName) return $o;
			}
		}
		return $this->RaiseError("(MsExcel_SheetInit) The sheet '$IdOrName' is not found inside the Workbook. Try command OPENTBS_DEBUG_INFO to check all sheets inside the current Workbook.");
	}

	/**
	 * Check if the file name is a subfile corresponding to a sheet.
	 */
	function MsExcel_SheetIsIt($FileName) {
		$this->MsExcel_SheetInit();
		foreach($this->MsExcel_Sheets as $o) {
			if ($FileName=='xl/'.$o->file) return true;
		}
		return false;
	}
	
	function MsExcel_SheetDebug($nl, $sep, $bull) {

		$this->MsExcel_SheetInit();

		echo $nl;
		echo $nl."Sheets in the Workbook:";
		echo $nl."-----------------------";
		foreach ($this->MsExcel_Sheets as $o) {
			$name = str_replace(array('&amp;','&quot;','&lt;','&gt;'), array('&','"','<','>'), $o->name);
			echo $bull."num: ".$o->num.", id: ".$o->sheetId.", name: [".$name."], state: ".$o->stateR.", file: xl/".$o->file;
		}

	}

	// Actually delete, display of hide sheet marked for this operations.
	function MsExcel_SheetDeleteAndDisplay() {

		if ( (count($this->OtbsSheetSlidesDelete)==0) && (count($this->OtbsSheetSlidesVisible)==0) ) return;

		$this->MsExcel_SheetInit();
		
		$WkbTxt = $this->TbsStoreGet($this->MsExcel_Sheets_WkbIdx, 'Sheet Delete and Display');
		$nothing = false;
		
		$change = false;
		$refToDel = array();

		// process sheet in reverse order of their positions
		foreach ($this->MsExcel_Sheets as $o) {
			$zid = 'i:'.$o->num;
			$zname = 'n:'.$o->name; // the value in the name attribute is XML protected
			if ( isset($this->OtbsSheetSlidesDelete[$zname]) || isset($this->OtbsSheetSlidesDelete[$zid]) ) {
				// Delete the sheet
				$this->MsExcel_DeleteSheetFile($o->file, $o->rid, $WkbTxt);
				$change = true;
				$ref1 = str_replace(array('&quot;','\''), array('"','\'\''), $o->name);
				$ref2 = "'".$ref1."'";
				$refToDel[] = $ref1;
				$refToDel[] = $ref2;
				unset($this->OtbsSheetSlidesDelete[$zname]);
				unset($this->OtbsSheetSlidesDelete[$zid]);
				unset($this->OtbsSheetSlidesVisible[$zname]);
				unset($this->OtbsSheetSlidesVisible[$zid]);
			} elseif ( isset($this->OtbsSheetSlidesVisible[$zname]) || isset($this->OtbsSheetSlidesVisible[$zid]) ) {
				// Hide or display the sheet
				$visible = (isset($this->OtbsSheetSlidesVisible[$zname])) ? $this->OtbsSheetSlidesVisible[$zname] : $this->OtbsSheetSlidesVisible[$zid];
				$state = ($visible) ? 'visible' : 'hidden';
				if ($o->stateR!=$state) {
					if (!$visible) $change = true;
					$loc = clsTbsXmlLoc::FindStartTagHavingAtt($WkbTxt, 'r:id="'.$o->rid.'"', 0);
					if ($loc!==false) $loc->ReplaceAtt('state', $state, true);
				}
				unset($this->OtbsSheetSlidesVisible[$zname]);
				unset($this->OtbsSheetSlidesVisible[$zid]);
			}
		}

		// If they are deleted or hidden sheet, then it could be the active sheet, so we delete the active tab information
		// Note: activeTab attribute seems to not be a sheet id, but rather a tab id.
		if ($change) {
			$loc = clsTbsXmlLoc::FindStartTag($WkbTxt, 'workbookView', 0);
			if ($loc!==false) $loc->DeleteAtt('activeTab');
		}

		// Delete name of cells (<definedName>) that refer to a deleted sheet
		foreach ($refToDel as $ref) {
			// The name of the sheets is used in the reference, but with small changes
			$p = 0;
			while ( ($p = strpos($WkbTxt, '>'.$ref.'!', $p)) !==false ) {
				$p2 = strpos($WkbTxt, '>', $p+1);
				$p1 = strrpos(substr($WkbTxt, 0, $p), '<');
				if ( ($p1!==false) && ($p2!==false) ) {
					$WkbTxt = substr_replace($WkbTxt, '', $p1, $p2 - $p1 +1);
				} else {
					$p++;
				}
			}
			//<pivotCaches><pivotCache cacheId="1" r:id="rId5"/></pivotCaches>
		}
		
		// can make Excel error, no problem with <definedNames>
		$WkbTxt = str_replace('<pivotCaches></pivotCaches>', '', $WkbTxt);
	
		// store the result
		$this->TbsStorePut($this->MsExcel_Sheets_WkbIdx, $WkbTxt);

		$this->TbsSheetCheck();

	}

	function MsExcel_DeleteSheetFile($file, $rid, &$WkbTxt) {

		$this->OpenXML_DeleteFile('xl/' . $file, array('xl/workbook.xml'));

		// Delete in workbook.xml
		if ($rid!=false) {
			$loc = clsTbsXmlLoc::FindElementHavingAtt($WkbTxt, 'r:id="'.$rid.'"', 0);
			if ($loc!==false) $loc->ReplaceSrc('');
		}
		
	}
	
	// Return the list of images in the current sheet
	function MsExcel_GetDrawingLst() {

		$lst = array();

		$dir = '../drawings/';
		$dir_len = strlen($dir);
		$o = $this->OpenXML_Rels_GetObj($this->TBS->OtbsCurrFile, $dir);
		foreach($o->TargetLst as $t) {
			if ( (substr($t, 0, $dir_len)===$dir) && (substr($t, -4)==='.xml') ) $lst[] = 'xl/drawings/'.substr($t, $dir_len);
		}

		return $lst;
	}

	/**
	 * Return the array of the cells
	 * Problem to solve: the results of formulas are deleted because of OtbsMsExcelConsistent
	 */
	function MsExcel_AsArray($Txt, $options = array()) {
	
		$rBeg = $this->getItem($options, 'row_beg', 1);
		$rEnd = $this->getItem($options, 'row_end', 0);
		$cBeg = $this->getItem($options, 'col_beg', 1);
		$cEnd = $this->getItem($options, 'col_end', 0);
		$formulas = $this->getItem($options, 'formulas', false);
		$fill = $this->getItem($options, 'fill', true);
		
		$result = array();
	
		$rp = 0;
		$rn = -1;
		while ($re=clsTbsXmlLoc::FindElement($Txt, 'row', $rp, true) ) {
			$rn++;
			$row = array();
			if ($re->GetInnerStart() !== false) {
				$cn = -1;
				$cp = 0;
				while ($ce=clsTbsXmlLoc::FindElement($re, 'c', $cp, true) ) {
					$cn++;
					$x = null;
					if ($ce->GetInnerStart() !== false) {
						$type = $ce->GetAttLazy('t');
						$vtag = ($type === 'inlineStr') ? 't' : 'v';
						$ve = clsTbsXmlLoc::FindElement($ce, $vtag, 0, true);
						if ($ve === false) {
							$x = "(tag $vtag not found)";
						} else {
							$v = $ve->GetInnerSrc();
							switch ($type) {
							case 'b': // boolean: 0=false
								$x = (boolean) $v; break;
							case 's': // shared string
								$x = $this->OpenXML_SharedStrings_GetVal($v); break;
							case 'inlineStr': // inline string
								$x = $v;
							case 'str': // formula returning a string				
								$x = $v;
							case 'd': // date
							    $t = ($v-25569.0) * 86400.0; // unix: 1 means 01/01/1970, xlsx: 1 means 01/01/1900
								$x = date('Y-m-d h:i:s', $t);
							case 'e': // error, example of value: #DIV/0!
								$x = $v;
							default: // false or 'n' : number
								$x = $v;
							}
						}
					}
					$row[] = $x;
					$cp = $ce->PosEnd;
				}
			}
			$result[]= $row;
			$rp = $re->PosEnd;
		}

		return $result;
		
	}
	
	/**
	 * Return the list of slides in the Ms Powerpoint presentation.
	 * @param {boolean} $Master Trye to operate on master slides.
	 * @return {array} The list of the slides, of false if an error occurs.
	 */
	function MsPowerpoint_InitSlideLst($Master = false) {

		if ($Master) {
			$RefLst = &$this->OpenXmlSlideMasterLst;
		} else {
			$RefLst = &$this->OpenXmlSlideLst;
		}
		
		if ($RefLst!==false) return $RefLst;

		$PresFile = 'ppt/presentation.xml';

		$prefix = ($Master) ? 'slideMasters/' : 'slides/';
		$o = $this->OpenXML_Rels_GetObj('ppt/presentation.xml', $prefix);

		$Txt = $this->FileRead($PresFile);
		if ($Txt===false) return false;

		$p = 0;
		$i = 0;
		$lst = array();
		$tag = ($Master) ? 'p:sldMasterId' : 'p:sldId';
		while ($loc = clsTbsXmlLoc::FindStartTag($Txt, $tag, $p)) {
			$i++;
			$rid = $loc->GetAttLazy('r:id');
			if ($rid===false) {
				$this->RaiseError("(Init Slide List) attribute 'r:id' is missing for slide #$i in '$PresFile'.");
			} elseif (isset($o->TargetLst[$rid])) {
				$f = 'ppt/'.$o->TargetLst[$rid];
				$lst[] = array('file' => $f, 'idx' => $this->FileGetIdx($f), 'rid' => $rid);
			} else {
				$this->RaiseError("(Init Slide List) Slide corresponding to rid=$rid is not found in the Rels file of '$PresFile'.");
			}
			$p = $loc->PosEnd;
		}

		$RefLst = $lst;
		return $RefLst;

	}

	// Clean tags in an Ms Powerpoint slide
	function MsPowerpoint_Clean(&$Txt) {

		$this->MsPowerpoint_CleanRpr($Txt, 'a:rPr');
		$Txt = str_replace('<a:rPr/>', '', $Txt);

		$this->MsPowerpoint_CleanRpr($Txt, 'a:endParaRPr');
		$Txt = str_replace('<a:endParaRPr/>', '', $Txt); // do not delete, can change layout

		// Join split elements
		$Txt = str_replace('</a:t><a:t>', '', $Txt);
		$Txt = str_replace('</a:t></a:r><a:r><a:t>', '', $Txt); // this join TBS split tags

		// Delete empty elements
		// An <a:r> must contain at least one <a:t>. An empty <a:t> may exist after several merges or an OpenTBS cleans.
		$Txt = str_replace('<a:r><a:t></a:t></a:r>', '', $Txt);

	}

	function MsPowerpoint_CleanRpr(&$Txt, $elem) {
		$p = 0;
		while ($x = clsTbsXmlLoc::FindStartTag($Txt, $elem, $p)) {
			$x->DeleteAtt('noProof');
			$x->DeleteAtt('lang');
			$x->DeleteAtt('err');
			$x->DeleteAtt('smtClean');
			$x->DeleteAtt('dirty');
			$p = $x->PosEnd;
		}
	}

	/**
	 * Search a string in all slides of the Presentation.
	 */
	function MsPowerpoint_SearchInSlides($str, $returnFirstFound = true) {

		// init the list of slides
		$this->MsPowerpoint_InitSlideLst(); // List of slides

		// build the list of files in the expected structure
		$files = array();
		foreach($this->OpenXmlSlideLst as $i=>$s) $files[$i+1] = $s['idx'];

		// search
		$find = $this->TbsSearchInFiles($files, $str, $returnFirstFound);

		return $find;

	}

	function MsPowerpoint_SlideDebug($nl, $sep, $bull) {

		$this->MsPowerpoint_InitSlideLst(); // List of slides

		echo $nl;
		echo $nl.count($this->OpenXmlSlideLst)." slide(s) in the Presentation:";
		echo $nl."-------------------------------";
		foreach ($this->OpenXmlSlideLst as $i => $s) {
			echo $bull."#".($i+1).": ".basename($s['file']).", file: " . $s['file'];
		}
		if (count($this->OpenXmlSlideLst)==0) echo $bull."(none)";

		// List of charts
		echo $nl;
		echo $nl."Charts found in slides:";
		echo $nl."-------------------------";

		$nbr = 0;
		for ($s=1; $s <= count($this->OpenXmlSlideLst); $s++) {
			$this->OnCommand(OPENTBS_SELECT_SLIDE, $s);
			$ChartLst = $this->OpenXML_ChartGetInfoFromFile($this->TbsCurrIdx);
			foreach ($ChartLst as $i=>$c) {
				$name = ($c['name']===false) ? '(not found)' : $c['name'];
				$title = ($c['title']===false) ? '(not found)' : var_export($c['title'], true);
				echo $bull."slide: $s, chart name: '$name', title: $title";
				if ($c['descr']!==false) echo ", description: ".$c['descr'];
				$nbr++;
			}
		}
		if ($nbr==0) echo $bull."(none)";

	}

	// Actually delete slides in the Presentation
	function MsPowerpoint_SlideDelete() {

		if ( (count($this->OtbsSheetSlidesDelete)==0) && (count($this->OtbsSheetSlidesVisible)==0) ) return;

		$this->MsPowerpoint_InitSlideLst();

		// Edit both XML and REL of file 'presentation.xml'

		$xml_file = 'ppt/presentation.xml';
		$xml_idx = $this->FileGetIdx($xml_file);
		$rel_idx = $this->FileGetIdx($this->OpenXML_Rels_GetPath($xml_file));

		$xml_txt = $this->TbsStoreGet($xml_idx, 'Slide Delete and Display / XML');
		$rel_txt = $this->TbsStoreGet($rel_idx, 'Slide Delete and Display / REL');

		$del_lst = array();
		$del_lst2 = array();
		$first_kept = false; // Name of the first slide, to be kept 
		foreach ($this->OpenXmlSlideLst as $i=>$s) {
			$ref = 'i:'.($i+1);
			if (isset($this->OtbsSheetSlidesDelete[$ref]) && $this->OtbsSheetSlidesDelete[$ref] ) {

				// the rid may be used several time in the fiel. i.e.: in <p:sldIdLst><p:sldIdLst>, but also in <p:custShow><p:sldLst>
				while ( ($x = clsTbsXmlLoc::FindElementHavingAtt($xml_txt, 'r:id="'.$s['rid'].'"', 0))!==false ) {
					$x->ReplaceSrc(''); // delete the element
				}

				$x = clsTbsXmlLoc::FindElementHavingAtt($rel_txt, 'Id="'.$s['rid'].'"', 0);
				if ($x!==false) $x->ReplaceSrc(''); // delete the element

				$del_lst[] = $s['file'];
				$del_lst[] = $this->OpenXML_Rels_GetPath($s['file']);
				$del_lst2[] = basename($s['file']);

			} else {
				$first_kept = basename($s['file']);
			}
		}

		$this->TbsStorePut($xml_idx, $xml_txt);
		$this->TbsStorePut($rel_idx, $rel_txt);
		unset($xml_txt, $rel_txt);

		// Delete references in '[Content_Types].xml'
		foreach ($del_lst as $f) {
			$this->OpenXML_CTypesDeletePart('/'.$f);
		}

		// Change references in 'viewProps.xml.rels'
		$idx = $this->FileGetIdx('ppt/_rels/viewProps.xml.rels');
		$txt = $this->TbsStoreGet($idx, 'Slide Delete and Display / viewProps');
		$ok = false;
		foreach ($del_lst2 as $f) {
			$z = 'Target="slides/'.$f.'"';
			if (strpos($txt, $z)) {
				if ($first_kept===false) return $this->RaiseError("(Slide Delete and Display) : no slide left to replace the default slide in 'viewProps.xml.rels'.");
				$ok = true;
				$txt = str_replace($z, 'Target="slides/'.$first_kept.'"' , $txt);
			}
		}
		if ($ok) $this->TbsStorePut($idx, $txt);

		// Actually delete the slide files
		foreach ($del_lst as $f) {
			$idx = $this->FileGetIdx($f);
			unset($this->TbsStoreLst[$idx]); // delete the slide from the merging if any
			$this->FileReplace($idx, false);
		}

	}

	/**
	 * Return true if the file name is a slide.
	 */
	function MsPowerpoint_SlideIsIt($FileName) {
		$this->MsPowerpoint_InitSlideLst();
		foreach ($this->OpenXmlSlideLst as $i => $s) {
			if ($FileName==$s['file']) return true;
		}
		return false;
	}
	
	// Cleaning tags in MsWord
	function MsWord_Clean(&$Txt) {
		$Txt = str_replace('<w:lastRenderedPageBreak/>', '', $Txt); // faster
		//$this->MsWord_CleanFallbacks($Txt);
		$this->XML_DeleteElements($Txt, array('w:proofErr', 'w:noProof', 'w:lang', 'w:lastRenderedPageBreak'));
		$this->MsWord_CleanSystemBookmarks($Txt);
		$this->MsWord_CleanRsID($Txt);
		$this->MsWord_CleanDuplicatedLayout($Txt);
	}
	
	/**
	 * <mc:Fallback> entities may contains duplicated TBS fields and this may corrupt the merging.
	 * This function delete such entities if they seems to contain TBS fields. This make the DOCX content less compatible with previous Word versions.
	 * https://wiki.openoffice.org/wiki/OOXML/Markup_Compatibility_and_Extensibility
	 */ 
	function MsWord_CleanFallbacks(&$Txt) {
		
		$p = 0;
		$nb = 0;
		while ( ($loc = clsTbsXmlLoc::FindElement($Txt,'mc:Fallback',$p))!==false ) {
			if (strpos($loc->GetSrc(), $this->TBS->_ChrOpen) !== false ) {
				$loc->Delete();
				$nb++;
			}
			$p = $loc->PosEnd;
		}

	}
	
	function MsWord_CleanSystemBookmarks(&$Txt) {
	// Delete GoBack hidden bookmarks that appear since Office 2010. Example: <w:bookmarkStart w:id="0" w:name="_GoBack"/><w:bookmarkEnd w:id="0"/>

		$x = ' w:name="_GoBack"/><w:bookmarkEnd ';
		$x_len = strlen($x);

		$b = '<w:bookmarkStart ';
		$b_len = strlen($b);

		$nbr_del = 0;

		$p = 0;
		while ( ($p=strpos($Txt, $x, $p))!==false ) {
			$pe = strpos($Txt, '>', $p + $x_len);
			if ($pe===false) return false;
			$pb = strrpos(substr($Txt,0,$p) , '<');
			if ($pb===false) return false;
			if (substr($Txt, $pb, $b_len)===$b) {
				$Txt = substr_replace($Txt, '', $pb, $pe - $pb + 1); 
				$p = $pb;
				$nbr_del++;
			} else {
				$p = $pe +1;
			}
		}

		return $nbr_del;

	}

	function MsWord_CleanRsID(&$Txt) {
	/* Delete XML attributes relative to log of user modifications. Returns the number of deleted attributes.
	In order to insert such information, MsWord does split TBS tags with XML elements.
	After such attributes are deleted, we can concatenate duplicated XML elements. */

		$rs_lst = array('w:rsidR', 'w:rsidRPr');

		$nbr_del = 0;
		foreach ($rs_lst as $rs) {

			$rs_att = ' '.$rs.'="';
			$rs_len = strlen($rs_att);

			$p = 0;
			while ($p!==false) {
				// search the attribute
				$ok = false;
				$p = strpos($Txt, $rs_att, $p);
				if ($p!==false) {
					// attribute found, now seach tag bounds
					$po = strpos($Txt, '<', $p);
					$pc = strpos($Txt, '>', $p);
					if ( ($pc!==false) && ($po!==false) && ($pc<$po) ) { // means that the attribute is actually inside a tag
						$p2 = strpos($Txt, '"', $p+$rs_len); // position of the delimiter that closes the attribute's value
						if ( ($p2!==false) && ($p2<$pc) ) {
							// delete the attribute
							$Txt = substr_replace($Txt, '', $p, $p2 -$p +1);
							$ok = true;
							$nbr_del++;
						}
					}
					if (!$ok) $p = $p + $rs_len;
				}
			}

		}

		// delete empty tags
		$Txt = str_replace('<w:rPr></w:rPr>', '', $Txt);
		$Txt = str_replace('<w:pPr></w:pPr>', '', $Txt);

		return $nbr_del;

	}

	/**
	 * MsWord cut the source of the text when a modification is done. This is splitting TBS tags.
	 * This function repare the split text by searching and delete duplicated layout.
	 * Return the number of deleted dublicates.
	 */
	function MsWord_CleanDuplicatedLayout(&$Txt) {

		$wro = '<w:r';
		$wro_len = strlen($wro);

		$wrc = '</w:r';
		$wrc_len = strlen($wrc);

		$wto = '<w:t';
		$wto_len = strlen($wto);

		$wtc = '</w:t';
		$wtc_len = strlen($wtc);

		$preserve = 'xml:space="preserve"';

		$nbr = 0;
		$wro_p = 0;
		while ( ($wro_p=$this->XML_FoundTagStart($Txt,$wro,$wro_p))!==false ) { // next <w:r> tag
			$wto_p = $this->XML_FoundTagStart($Txt,$wto,$wro_p); // next <w:t> tag
			if ($wto_p===false) return false; // error in the structure of the <w:r> element
			$first = true;
			$last_att = '';
			$first_att = '';
			do {
				$ok = false;
				$wtc_p = $this->XML_FoundTagStart($Txt,$wtc,$wto_p); // next </w:t> tag
				if ($wtc_p===false) return false;
				$wrc_p = $this->XML_FoundTagStart($Txt,$wrc,$wro_p); // next </w:r> tag (only to check inclusion)
				if ($wrc_p===false) return false;
				if ( ($wto_p<$wrc_p) && ($wtc_p<$wrc_p) ) { // if the <w:t> is actually included in the <w:r> element
					if ($first) {
						// text that is concatenated and can be simplified
						$superfluous = '</w:t></w:r>'.substr($Txt, $wro_p, ($wto_p+$wto_len)-$wro_p); // without the last symbol, like: '</w:t></w:r><w:r>....<w:t'
						$superfluous = str_replace('<w:tab/>', '', $superfluous); // tabs must not be deleted between parts => they nt be in the superfluous string
						$superfluous_len = strlen($superfluous);
						$first = false;
						$p_first_att = $wto_p+$wto_len;
						$p =  strpos($Txt, '>', $wto_p);
						if ($p!==false) $first_att = substr($Txt, $p_first_att, $p-$p_first_att);
					}
					// if the <w:r> layout is the same than the next <w:r>, then we join them
					$p_att = $wtc_p + $superfluous_len;
					$x = substr($Txt, $p_att, 1); // must be ' ' or '>' if the string is the superfluous AND the <w:t> tag has or not attributes
					if ( (($x===' ') || ($x==='>')) && (substr($Txt, $wtc_p, $superfluous_len)===$superfluous) ) {
						$p_end = strpos($Txt, '>', $wtc_p+$superfluous_len); //
						if ($p_end===false) return false; // error in the structure of the <w:t> tag
						$last_att = substr($Txt,$p_att,$p_end-$p_att);
						$Txt = substr_replace($Txt, '', $wtc_p, $p_end-$wtc_p+1); // delete superfluous part + <w:t> attributes
						$nbr++;
						$ok = true;
					}
				}
			} while ($ok);

			// Recover the 'preserve' attribute if the last join element was having it. We check also the first one because the attribute must not be twice.
			if ( ($last_att!=='') && (strpos($first_att, $preserve)===false)  && (strpos($last_att, $preserve)!==false) ) {
				$Txt = substr_replace($Txt, ' '.$preserve, $p_first_att, 0);
			}

			$wro_p = $wro_p + $wro_len;

		}

		return $nbr; // number of replacements

	}

	/**
	 * Prevent from the problem of missing spaces when calling ->MsWord_CleanRsID() or under certain merging circumstances.
	 * Replace attribute xml:space="preserve" used in <w:t>, with the same attribute in <w:document>.
	 * This trick works for MsWord 2007, 2010 but is undocumented. It may be desabled by default in a next version.
	 * LibreOffice does ignore this attribute in both <w:t> and <w:document>.
	 */
	function MsWord_CleanSpacePreserve(&$Txt) {
		$XmlLoc = clsTbsXmlLoc::FindStartTag($Txt, 'w:document', 0);
		if ($XmlLoc===false) return;
		if ($XmlLoc->GetAttLazy('xml:space') === 'preserve') return;
		
		$Txt = str_replace(' xml:space="preserve"', '', $Txt); // not mendatory but cleanner and save space
		$XmlLoc->ReplaceAtt('xml:space', 'preserve', true);

	}

	/**
	 * Renumber attribute "id " of elements <wp:docPr> in order to ensure unicity.
	 * Such elements are used in objects.
	 */
	function MsWord_RenumDocPr(&$Txt) {

		$this->MsWord_DocPrId;
	
		$el = '<wp:docPr ';
		$el_len = strlen($el);

		$id = ' id="';
		$id_len = strlen($id);

		$nbr = 0;

		$p = 0;
		while ($p!==false) {
			// search the element
			$p = strpos($Txt, $el, $p);
			if ($p!==false) {
				// attribute found, now seach tag bounds
				$p = $p + $el_len -1; // don't take the space, it is used for the next search
				$pc = strpos($Txt, '>', $p);
				if ($pc!==false) {
					$x = substr($Txt, $p, $pc - $p);
					$pi = strpos($x, $id);
					if ($pi!==false) {
						$pi = $pi + $id_len;
						$pq = strpos($x, '"', $pi);
						if ($pq!==false) {
							$i_len = $pq - $pi;
							$i = intval(substr($x, $pi, $i_len));
							if ($i>0) { // id="0" is erroneous
								if ($i > $this->MsWord_DocPrId) {
									$this->MsWord_DocPrId = $i; // nothing else to do
								} else {
									$this->MsWord_DocPrId++;
									$id_txt = '' . $this->MsWord_DocPrId;
									$Txt = substr_replace($Txt, $id_txt, $p + $pi, $i_len);
									$nbr++;
								}
							}
						}
					}
				}
			}
		}

		return $nbr;

	}

	// Alias of block: 'tbs:page'
	function MsWord_GetPage($Tag, $Txt, $Pos, $Forward, $LevelStop) {

		// Search the two possible tags for having a page-break
		$loc1 = clsTbsXmlLoc::FindStartTagHavingAtt($Txt, 'w:type="page"', $Pos, $Forward);
		$loc2 = clsTbsXmlLoc::FindStartTag($Txt, 'w:pageBreakBefore', $Pos, $Forward);

		// Define the position of start for the corresponding paragraph 
		if ( ($loc1===false) && ($loc2===false) ) {
			if ($Forward) {
				// End of the last paragraph of the document.
				// The <w:p> elements can be embeded, and it can be a single tag if it cnotains no text.
				$loc = clsTbsXmlLoc::FindElement($Txt, 'w:p', strlen($Txt), false);
				if ($loc===false) return false;
				return $loc->PosEnd;
			} else {
				// start of the first paragraph of the document
				$loc = clsTbsXmlLoc::FindStartTag($Txt, 'w:p', 0, true);
				if ($loc===false) return false;
				return $loc->PosBeg;
			}
		}

		// Take care that <w:p> elements can be sef-embeded.
		// 	That's why we assume that there is no page-break in an embeded paragraph while it is useless but possible.
		if ($loc1===false) {
			$s = $loc2->PosBeg;
		} elseif($loc2===false) {
			$s = $loc1->PosBeg;
		} else {
			if ($Forward) {
				$s = ($loc1->PosBeg < $loc2->PosBeg) ? $loc1->PosBeg : $loc2->PosBeg;
			} else {
				$s = ($loc1->PosBeg > $loc2->PosBeg) ? $loc1->PosBeg : $loc2->PosBeg;
			}
		}
		$loc = clsTbsXmlLoc::FindStartTag($Txt, 'w:p', $s, false);

		$p = $loc->PosBeg;
		if ($Forward) $p--; // if it's forward, we stop the block before the paragraph with page-break
		return $p;
		
	}

	/**
	 * Alias of block: 'tbs:section'
	 * In Docx, section-breaks <w:sectPr> can be saved in the last <w:p> of the section, or just after the last <w:p> of the section.
	 * In practice, there is always at least one sectin-break and only the last section-break is saved outside the <w:p>.
	 */ 
	function MsWord_GetSection($Tag, $Txt, $Pos, $Forward, $LevelStop) {

		// First we check if the TBS tag is inside a <w:p> and if this <w:p> has a <w:sectPr>
		$case = false;
		$locP = clsTbsXmlLoc::FindStartTag($Txt, 'w:p', $Pos, false);
		if ($locP!==false) {
			$locP->FindEndTag(true);
			if ($locP->PosEnd>$Pos) {
				$src = $locP->GetSrc();
				$loc = clsTbsXmlLoc::FindStartTag($src, 'w:sectPr', 0, true);
				if ($loc!==false) $case = true;
			}
		}

		if ($case && $Forward) return $locP->PosEnd;

		// Look for the next section-break
		$p = ($Forward) ? $locP->PosEnd : $locP->PosBeg;
		$locS = clsTbsXmlLoc::FindStartTag($Txt, 'w:sectPr', $p, $Forward);

		if ($locS===false) {
			if ($Forward) {
				// end of the body
				$p = strpos($Txt, '</w:body>', $Pos);
				return ($p===false) ? false : $p - 1;
			} else {
				// start of the body
				$loc2 = clsTbsXmlLoc::FindStartTag($Txt, 'w:body', 0, true);
				return ($loc2===false) ? false : $loc2->PosEnd + 1;
			}
		}

		// is <w:sectPr> inside a <w:p> ?
		$ewp = '</w:p>';
		$inside = false;
		$p = strpos($Txt, $ewp, $locS->PosBeg);
		if ($p!==false) {
			$loc2 = clsTbsXmlLoc::FindStartTag($Txt, 'w:p', $locS->PosBeg, true);
			if ( ($loc2===false) || ($loc2->PosBeg>$p) ) $inside = true;
		}

		$offset = ($Forward) ? 0 : 1;
		if ($inside) {
			return $p + strlen($ewp) - 1 + $offset;
		} else {
			// not inside
			$locS->FindEndTag();
			return $locS->PosEnd + $offset;
		}

	}

	/**
	 * Initialize information about header and footer files
	 */
	function MsWord_InitHeaderFooter() {
	
		if ($this->MsWord_HeaderFooter!==false) return;

		$types_ok = array('default' => true, 'first' => false, 'even' => false);
		
		// Is there a different header/footer for odd an even pages ?
		$idx = $this->FileGetIdx('word/settings.xml');
		if ($idx!==false) {		
			$Txt = $this->TbsStoreGet($idx, 'GetHeaderFooterFile');
			$types_ok['even'] = (strpos($Txt, '<w:evenAndOddHeaders/>')!==false);
			unset($Txt);
		}

		// Is there a different header/footer for the first page ?
		$idx = $this->FileGetIdx('word/document.xml');
		if ($idx===false) return false;
		$Txt = $this->TbsStoreGet($idx, 'GetHeaderFooterFile');
		$types_ok['first'] = (strpos($Txt, '<w:titlePg/>')!==false);

		$places = array('header', 'footer');
		$files = array();
		$rels = $this->OpenXML_Rels_GetObj('word/document.xml', '');
		
		foreach ($places as $place) {
			$p = 0;
			$entity = 'w:' . $place . 'Reference';
			while ($loc = clsTbsXmlLoc::FindStartTag($Txt, $entity, $p)) {
				$p = $loc->PosEnd;
				$type = $loc->GetAttLazy('w:type');
				if (isset($types_ok[$type]) && $types_ok[$type]) {
					$rid = $loc->GetAttLazy('r:id');
					if (isset($rels->TargetLst[$rid])) {
						$target = $rels->TargetLst[$rid];
						$files[] = array('file' => ('word/'.$target), 'type' => $type, 'place' => $place);
					}
				}
			}
		}

		$this->MsWord_HeaderFooter = $files;
	
	}
	
	/**
	 * Retrieve the header/footer sub-file.
	 * @param mixed $TbsCmd  OPENTBS_SELECT_HEADER or OPENTBS_SELECT_FOOTER.
	 * @param mixed $TbsType OPENTBS_DEFAULT, OPENTBS_FIRST or OPENTBS_EVEN. 
	 * @param int [$Offset] Since a DCX can have several sections, and each section can have its own header/footer, this options 
	 * @return mixed The name of the file of false if no file is found. 
	 */
	function MsWord_GetHeaderFooterFile($TbsCmd, $TbsType, $Offset = 0) {

		$this->MsWord_InitHeaderFooter();

		$Place = 'header';
		if ($TbsCmd==OPENTBS_SELECT_FOOTER) {
			$Place = 'footer';
		}

		$Type = 'default';
		if ($TbsType==OPENTBS_FIRST) {
			$Type = 'first';
		} elseif ($TbsType==OPENTBS_EVEN) {
			$Type = 'even';
		}

		$nb = 0;
		foreach($this->MsWord_HeaderFooter as $info) {
			if ( ($info['type']==$Type) && ($info['place']==$Place) ) {
				if ($nb==$Offset) {
					return $info['file'];
				} else {
					$nb++;
				}
			}
		}
		
		return false;
		
	}
	
	function MsWord_DocDebug($nl, $sep, $bull) {

		$ChartLst = $this->OpenXML_ChartGetInfoFromFile($this->Ext_GetMainIdx());

		echo $nl;
		echo $nl."Charts found in the body:";
		echo $nl."-------------------------";
		foreach ($ChartLst as $i=>$c) {
			$name = ($c['name']===false) ? '(not found)' : $c['name'];
			$title = ($c['title']===false) ? '(not found)' : var_export($c['title'], true);
			echo $bull."name: '$name', title: $title";
			if ($c['descr']!==false) echo ", description: ".$c['descr'];
		}

	}

	// OpenOffice documents

	function OpenDoc_CleanRsID(&$Txt) {
	
		// Get all style names about RSID for <span> elements
		$styles = array();
		$p = 0;
		while ( ($el = clsTbsXmlLoc::FindStartTagHavingAtt($Txt, 'officeooo:rsid', $p)) !== false) {
			// If the <style:text-properties> element has only this attribute then its length is 50.
			if ($el->GetLen() < 60) {
				if ($par = clsTbsXmlLoc::FindStartTag($Txt, 'style:style', $el->PosBeg, false)) {
					if ($name = $par->GetAttLazy('style:name')) {
						$styles[] = $name;
					}
				}
			}
			$p = $el->PosEnd;
		}
		
		// Delete <text:span> elements
		$xe = '</text:span>';
		$xe_len = strlen($xe);
		foreach ($styles as $name) {
			$p = 0;
			$x = '<text:span text:style-name="' . $name . '">';
			$x_len = strlen($x);
			while ( ($p = strpos($Txt, $x, $p)) !== false) {
				$pe = strpos($Txt, $xe, $p);
				$src = substr($Txt, $p + $x_len, $pe - $p - $x_len);
				$Txt = substr_replace($Txt, $src, $p, $pe + $xe_len - $p);
				$p = $p + strlen($src);
			}
		}
	
	}
	
	function OpenDoc_ManifestChange($Path, $Type) {
	// Set $Type=false in order to mark the the manifest entry to be deleted.
	// Video and sound files are not to be registered in the manifest since the contents is not saved in the document.

		// Initialization
		if ($this->OpenDocManif===false) $this->OpenDocManif = array();

		// We try to found the type of image
		if (($Type==='') && (substr($Path,0,9)==='Pictures/')) {
			$ext = basename($Path);
			$p = strrpos($ext, '.');
			if ($p!==false) {
				$ext = strtolower(substr($ext,$p+1));
				if (isset($this->ExtInfo['pic_ext'][$ext])) $Type = 'image/'.$this->ExtInfo['pic_ext'][$ext];
			}
		}

		$this->OpenDocManif[$Path] = $Type;

	}

	function OpenDoc_ManifestCommit($Debug) {

		// Retrieve the content of the manifest
		$name = 'META-INF/manifest.xml';
		$idx = $this->FileGetIdx($name);
		if ($idx===false) return;

		$Txt = $this->TbsStoreGet($idx, 'OpenDocumentFormat');
		if ($Txt===false) return false;

		// Perform all changes
		foreach ($this->OpenDocManif as $Path => $Type) {
			$x = 'manifest:full-path="'.$Path.'"';
			$p = strpos($Txt,$x);
			if ($Type===false) {
				// the entry should be deleted
				if ($p!==false) {
					$p1 = strrpos(substr($Txt,0,$p), '<');
					$p2 = strpos($Txt,'>',$p);
					if (($p1!==false) && ($p2!==false)) $Txt = substr($Txt,0,$p1).substr($Txt,$p2+1);
				}
			} else {
				// the entry should be added
				if ($p===false) {
					$p = strpos($Txt,'</manifest:manifest>');
					if ($p!==false) {
						$x = ' <manifest:file-entry manifest:media-type="'.$Type.'" '.$x.'/>'."\n";
						$Txt = substr_replace($Txt, $x, $p, 0);
					}
				}
			}
		}

		// Save changes (no need to save it in the park because this fct is called after merging)
		$this->FileReplace($idx, $Txt);

		if ($Debug) $this->DebugLst[$name] = $Txt;

	}

	function OpenDoc_ChangeCellType(&$Txt, &$Loc, $Ope, $IsMerging, &$Value) {
	// change the type of a cell in an ODS file

		$Loc->PrmLst['cellok'] = true; // avoid the field to be processed twice

		if ($Ope==='odsStr') return true;

		static $OpeLst = array(
			'tbs:num'=>'float',
			'tbs:percent'=>'percentage',
			'tbs:curr'=>'currency',
			'tbs:bool'=>'boolean',
			'tbs:date'=>'date',
			'tbs:time'=>'time',
			// for compatibility
			'odsNum'=>'float',
			'odsPercent'=>'percentage',
			'odsCurr'=>'currency',
			'odsBool'=>'boolean',
			'odsDate'=>'date',
			'odsTime'=>'time',
		);
		
		static $TypeLst = array(
			'float' => array('attval' => 'office:value'),
			'percentage' => array('attval' => 'office:value'),
			'currency' => array('attval' => 'office:value', 'attcurr' => 'office:currency'),
			'boolean' => array('attval' => 'office:boolean-value'),
			'date' => array('attval' => 'office:date-value', 'frm' => 'yyyy-mm-ddThh:nn:ss'),
			'time' => array('attval' => 'office:time-value', 'frm' => '"PT"hh"H"nn"M"ss"S"'),
		);

		if (!isset($OpeLst[$Ope])) return false;

		$new_type = $OpeLst[$Ope];
		$new_atts = $TypeLst[$new_type];
		
		$xLoc = clsTbsXmlLoc::FindStartTag($Txt, 'table:table-cell', $Loc->PosBeg, false);
		if ($xLoc===false) return false; // error in the XML structure
		
		// Replace the current TBS field with blank chars
		// This prevent from cases when the TBS field is not inside the cell (is this even possible ?)
		$len = $Loc->PosEnd - $Loc->PosBeg + 1;
		$Txt = substr_replace($Txt, str_repeat(' ',$len), $Loc->PosBeg, $len);
		
		$xLoc->switchToRelative();
		
		// Update attributes
		$xLoc->DeleteAtt('calcext:value-type'); // new attribute in LibreOffice 4
		$xLoc->ReplaceAtt('office:value-type', $new_type, true);
		$xLoc->ReplaceAtt($new_atts['attval'], '[]', true); // [] are the new bounds of the TBS field
		if (isset($new_atts['attcurr']) && isset($Loc->PrmLst['currency'])) $xLoc->ReplaceAtt('office:currency', $Loc->PrmLst['currency'], true);

		// Delete contents
		$xLocP = clsTbsXmlLoc::FindElement($xLoc, 'text:p', 0);
		if ($xLocP!==false) {
			$xLocP->Delete();
			$xLocP->UpdateParent();
		}
		
		// move the TBS field
		$p_fld = strpos($xLoc->Txt, '[', 0); // new position of the fields in $Txt

		$xLoc->switchToNormal();
		
		$Loc->PosBeg = $xLoc->PosBeg + $p_fld;
		$Loc->PosEnd = $xLoc->PosBeg + $p_fld +1;
		
		if ($IsMerging) {
			// the field is currently being merged
			if ($new_type==='boolean') {
				if ($Value) {
					$Value = 'true';
				} else {
					$Value = 'false';
				}
			} elseif (isset($new_atts['frm'])) {
				$prm = array('frm'=>$new_atts['frm']);
				$Value = $this->TBS->meth_Misc_Format($Value,$prm);
			}
			$Loc->ConvStr = false;
			$Loc->ConvProtect = false;
		} else {
			if (isset($new_atts['frm'])) $Loc->PrmLst['frm'] = $new_atts['frm'];
		}

	}

	function OpenDoc_SheetSlides_Init($sheet, $force = false) {

		if (($this->OpenDoc_SheetSlides!==false) && (!$force) ) return;

		$this->OpenDoc_SheetSlides = array();     // sheet/slide info sorted by location

		$idx = $this->Ext_GetMainIdx();
		if ($idx===false) return;
		$Txt = $this->TbsStoreGet($idx, 'Sheet/Slide Info');
		if ($Txt===false) return false;
		if ($this->LastReadNotStored) $this->TbsStorePut($idx, $Txt);
		$this->OpenDoc_SheetSlides_FileId = $idx;

		$tag = ($sheet) ? 'table:table' : 'draw:page';

		// scann sheet/slide list
		$p = 0;
		$idx = 0;
		while ($loc=clsTinyButStrong::f_Xml_FindTag($Txt, $tag, true, $p, true, false, true, true) ) {
			$this->OpenDoc_SheetSlides[$idx] = $loc;
			$idx++;
			$p = $loc->PosEnd;
		}

	}

	// Actally delete hide or display Sheets and Slides in a ODS or ODP
	function OpenDoc_SheetSlides_DeleteAndDisplay($sheet) {

		if ( (count($this->OtbsSheetSlidesDelete)==0) && (count($this->OtbsSheetSlidesVisible)==0) ) return;

		$this->OpenDoc_SheetSlides_Init($sheet, true);
		$Txt = $this->TbsStoreGet($this->OpenDoc_SheetSlides_FileId, 'Sheet Delete and Display');

		if ($sheet) {
			// Sheet
			$tag_close = '</table:table>';
			$att_name = 'table:name';
			$att_style = 'table:style-name';
			$att_display = 'table:display';
			$yes_display = 'true';
			$not_display = 'false';
			$tag_property = 'style:table-properties';
		} else {
			// Slide
			$tag_close = '</draw:page>';
			$att_name = 'draw:name';
			$att_style = 'draw:style-name';
			$att_display = 'presentation:visibility';
			$yes_display = 'visible';
			$not_display = 'hidden';
			$tag_property = 'style:drawing-page-properties';
		}
		$tag_close_len = strlen($tag_close);

		$styles_to_edit = array();
		// process sheet in rever order of their positions
		for ($idx = count($this->OpenDoc_SheetSlides) - 1; $idx>=0; $idx--) {
			$loc = $this->OpenDoc_SheetSlides[$idx];
			$id = 'i:'.($idx + 1);
			$name = 'n:'.$loc->PrmLst[$att_name];
			if ( isset($this->OtbsSheetSlidesDelete[$name]) || isset($this->OtbsSheetSlidesDelete[$id]) ) {
				// Delete the sheet
				$p = strpos($Txt, $tag_close, $loc->PosEnd);
				if ($p===false) return; // XML error
				$Txt = substr_replace($Txt, '', $loc->PosBeg, $p + $tag_close_len - $loc->PosBeg);
				unset($this->OtbsSheetSlidesDelete[$name]);
				unset($this->OtbsSheetSlidesDelete[$id]);
				unset($this->OtbsSheetSlidesVisible[$name]);
				unset($this->OtbsSheetSlidesVisible[$id]);
			} elseif ( isset($this->OtbsSheetSlidesVisible[$name]) || isset($this->OtbsSheetSlidesVisible[$id]) ) {
				// Hide or dispay the sheet
				$visible = (isset($this->OtbsSheetSlidesVisible[$name])) ? $this->OtbsSheetSlidesVisible[$name] : $this->OtbsSheetSlidesVisible[$id];
				$visible = ($visible) ? $yes_display : $not_display;
				if (isset($loc->PrmLst[$att_style])) {
					$style = $loc->PrmLst[$att_style];
					$new = $style.'_tbs_'.$visible;
					if (!isset($styles_to_edit[$style])) $styles_to_edit[$style] = array();
					$styles_to_edit[$style][$visible] = $new; // mark the style to be edited
					$pi = $loc->PrmPos[$att_style];
					$Txt = substr_replace($Txt, $pi[4].$new.$pi[4], $pi[2], $pi[3]-$pi[2]);
				}
				unset($this->OtbsSheetSlidesVisible[$name]);
				unset($this->OtbsSheetSlidesVisible[$id]);
			}
		}

		// process styles to edit
		if (count($styles_to_edit)>0) {
			$tag_close = '</style:style>';
			$tag_close_len = strlen($tag_close);
			$p = 0;
			while ($loc=clsTinyButStrong::f_Xml_FindTag($Txt, 'style:style', true, $p, true, false, true, false) ) {
				$p = $loc->PosEnd;
				if (isset($loc->PrmLst['style:name'])) {
					$name = $loc->PrmLst['style:name'];
					if (isset($styles_to_edit[$name])) {
						// retrieve the full source of the <style:style> element
						$p = strpos($Txt, $tag_close, $p);
						if ($p===false) return; // bug in the XML contents
						$p = $p + $tag_close_len;
						$src = substr($Txt, $loc->PosBeg, $p - $loc->PosBeg);
						// add the attribute, if missing
						if (strpos($src, ' '.$att_display.'="')===false)  $src = str_replace('<'.$tag_property.' ', '<'.$tag_property.' '.$att_display.'="'.$yes_display.'" ', $src);
						// add new styles
						foreach ($styles_to_edit[$name] as $visible => $newName) {
							$not = ($visible===$not_display) ? $yes_display : $not_display;
							$src2 = str_replace(' style:name="'.$name.'"', ' style:name="'.$newName.'"', $src);
							$src2 = str_replace(' '.$att_display.'="'.$not.'"', ' '.$att_display.'="'.$visible.'"', $src2);
							$Txt = substr_replace($Txt, $src2, $loc->PosBeg, 0);
							$p = $p + strlen($src2);
						}
					}
				}
			}

		}

		// store the result
		$this->TbsStorePut($this->OpenDoc_SheetSlides_FileId, $Txt);

		$this->TbsSheetCheck();

	}

	function OpenDoc_SheetSlides_Debug($sheet, $nl, $sep, $bull) {

		$this->OpenDoc_SheetSlides_Init($sheet);

		$text = ($sheet) ? "Sheets in the Workbook" : "Slides in the Presentation";
		$att = ($sheet) ? 'table:name' : 'draw:name';

		echo $nl;
		echo $nl.$text.":";
		echo $nl."-----------------------";
		foreach ($this->OpenDoc_SheetSlides as $idx => $loc) {
			$name = str_replace(array('&amp;','&quot;','&lt;','&gt;'), array('&','"','<','>'), $loc->PrmLst[$att]);
			echo $bull."id: ".($idx+1).", name: [".$name."]";
		}

	}

	function OpenDoc_StylesInit() {

		if ($this->OpenDoc_Styles!==false) return;

		$this->OpenDoc_Styles = array();     // sheet info sorted by location

		$Styles = array();

		// Read styles in 'styles.xml'
		$idx = $this->FileGetIdx('styles.xml');
		if ($idx!==false) {
			$Txt = $this->TbsStoreGet($idx, 'Style Init styles.xml');
			if ($Txt==!false) $this->OpenDoc_StylesFeed($Styles, $Txt);
		}

		// Read styles in 'content.xml'
		$idx = $this->FileGetIdx('content.xml');
		if ($idx!==false){
			$Txt = $this->TbsStoreGet($idx, 'Style Init content.xml');
			if ($Txt!==false) $this->OpenDoc_StylesFeed($Styles, $Txt);
		}

		// define childs
		foreach($Styles as $n => $s) {
			if ( ($s->parentName!==false) && isset($Styles[$s->parentName]) ) $Styles[$s->parentName]->childs[$s->name] = &$s;
		}

		// propagate page-break property to alla childs
		$this->OpenDoc_StylesPropagate($Styles);

		$this->OpenDoc_Styles = $Styles;

	}

	// Feed $Styles with styles found in $Txt
	function OpenDoc_StylesFeed(&$Styles, $Txt) {
		$p = 0;
		while ($loc = clsTbsXmlLoc::FindElement($Txt, 'style:style', $p)) {
			unset($o);
			$o = (object) null;
			$o->name = $loc->GetAttLazy('style:name');
			$o->parentName = $loc->GetAttLazy('style:parent-style-name');
			$o->childs = array();
			$o->pbreak = false;
			$o->ctrl = false;
			$src = $loc->GetSrc();
			if (strpos($src, ' fo:break-before="page"')!==false) $o->pbreak = 'before';
			if (strpos($src, ' fo:break-after="page"')!==false) $o->pbreak = 'after';
			if ($o->name!==false) $Styles[$o->name] = $o;
			$p = $loc->PosEnd;
		}
	}

	function OpenDoc_StylesPropagate(&$Styles) {
		foreach ($Styles as $i => $s) {
			if (!$s->ctrl) {
				$s->ctrl = true; // avoid circular reference
				if ($s->pbreak!==false) {
					foreach ($s->childs as $j => $c) {
						if ($c->pbreak!==false) $c->pbreak = $s->pbreak;
						$this->OpenDoc_StylesPropagate($c);
					}
				}
				$s->childs = false;
			}
		}
	}

	// TBS Block Alias for pages
	function OpenDoc_GetPage($Tag, $Txt, $Pos, $Forward, $LevelStop) {

		$this->OpenDoc_StylesInit();

		$p = $Pos;

		while (	($loc = clsTbsXmlLoc::FindStartTagHavingAtt($Txt, 'text:style-name', $p, $Forward))!==false) {

			$style = $loc->GetAttLazy('text:style-name');

			if ( ($style!==false) && isset($this->OpenDoc_Styles[$style]) ) {
				$pbreak = $this->OpenDoc_Styles[$style]->pbreak;
				if ($pbreak!==false) {
					if ($Forward) {
						// Forward
						if ($pbreak==='before') {
							return $loc->PosBeg -1; // note that the page-break is not in the block
						} else {
							$loc->FindEndTag();
							return $loc->PosEnd;
						}
					} else {
						// Backward
						if ($pbreak==='before') {
							return $loc->PosBeg;
						} else {
							$loc->FindEndTag();
							return $loc->PosEnd+1; // note that the page-break is not in the block
						}
					}
				}
			}

			$p = ($Forward) ? $loc->PosEnd : $loc->PosBeg; 

		}

		// If we are here, then no tag is found, we return the boud of the main element
		if ($Forward) {
			$p = strpos($Txt, '</office:text');
			if ($p===false) return false;
			return $p-1;
		} else {
			$loc = clsTbsXmlLoc::FindStartTag($Txt, 'office:text', $Pos, false);
			if ($loc===false) return false;
			return $loc->PosEnd + 1;
		}

	}

	// TBS Block Alias for draws
	function OpenDoc_GetDraw($Tag, $Txt, $Pos, $Forward, $LevelStop) {
		return $this->XML_BlockAlias_Prefix('draw:', $Txt, $Pos, $Forward, $LevelStop);
	}

	/**
	 * Find a chart in the template by its reference.
	 * Return an array of technical information about the sub-file.
	 */
	function OpenDoc_ChartFind($ChartRef, &$Txt, $ErrTitle) {
		
		if ($this->OpenDocCharts===false) $this->OpenDoc_ChartInit();

		// Find the chart
		if (is_numeric($ChartRef)) {
			$ChartCaption = 'number '.$ChartRef;
			$idx = intval($ChartRef) -1;
			if (!isset($this->OpenDocCharts[$idx])) return $this->RaiseError("($ErrTitle) : unable to found the chart $ChartCaption.");
		} else {
			$ChartCaption = 'with title "'.$ChartRef.'"';
			$idx = false;
			$x = htmlspecialchars($ChartRef, ENT_NOQUOTES); // ENT_NOQUOTES because target is an element's content
			foreach($this->OpenDocCharts as $i=>$c) {
				if ($c['title']==$x) $idx = $i;
			}
			if ($idx===false) return $this->RaiseError("($ErrTitle) : unable to found the chart $ChartCaption.");
		}
		$this->_ChartCaption = $ChartCaption; // for error messages

		// Retrieve chart information
		$chart = &$this->OpenDocCharts[$idx];
		if ($chart['to_clear']) $this->OpenDoc_ChartClear($chart);

		// Retrieve the XML of the data
		$file_name = $chart['href'] . '/content.xml';
		$file_idx = $this->FileGetIdx($file_name);
		if ($file_idx===false) return $this->RaiseError("($ErrTitle) : unable to found the data in the chart $ChartCaption.");
		$chart['file_name'] = $file_name;
		$chart['file_idx'] = $file_idx;

		$Txt = $this->TbsStoreGet($file_idx, 'OpenDoc_ChartChangeSeries');

		// Found all chart series
		if (!isset($chart['series'])) {
			$ok = $this->OpenDoc_ChartFindSeries($chart, $Txt);
			if (!$ok) return false;
		}
		
		return $chart;
		
	}
	
	function OpenDoc_ChartChangeSeries($ChartRef, $SeriesNameOrNum, $NewValues, $NewLegend=false) {

		$Txt = false;
		$chart = $this->OpenDoc_ChartFind($ChartRef, $Txt, 'ChartChangeSeries');
		if ($chart === false) return;
		
		$series = &$chart['series'];

		// Found the asked series
		$s_info = false;
		if (is_numeric($SeriesNameOrNum)) {
			$s_caption = 'number '.$SeriesNameOrNum;
			$idx = $SeriesNameOrNum -1;
			if (isset($series[$idx])) $s_info = &$series[$idx];
		} else {
			$s_caption = '"'.$SeriesNameOrNum.'"';
			foreach($series as $idx => $s) {
				if ( ($s_info===false) && ($s['name']==$SeriesNameOrNum) ) $s_info = &$series[$idx];
			}
		}
		if ($s_info===false) return $this->RaiseError("(ChartChangeSeries) : unable to found the series $s_caption in the chart ".$this->_ChartCaption.".");

		if ($NewLegend!==false) $this->OpenDoc_ChartRenameSeries($Txt, $s_info, $NewLegend);

		// simplified variables
		$col_cat  = $chart['col_cat']; // column Category (always 1)
		$col_nbr  = $chart['col_nbr']; // number of columns
		$s_col    = $s_info['cols'][0];  // first column of the series
		$s_col_nbr = count($s_info['cols']);
		$s_colend  = $s_col + $s_col_nbr - 1;  // last column of the series
		$s_use_cat = (count($s_info['cols'])==1); // true is the series uses the column Category

		// Force syntax of data
		if (!is_array($NewValues)) {
			$data = array();
			if ($NewValues===false) $this->OpenDoc_ChartDelSeries($Txt, $s_info);
		} elseif ( $s_use_cat && isset($NewValues[0]) && isset($NewValues[1]) && is_array($NewValues[0]) && is_array($NewValues[1]) ) {
			// syntax 2: $NewValues = array( array('cat1','cat2',...), array(val1,val2,...) );		
			$k = $NewValues[0];
			$v = $NewValues[1];
			$data = array();
			foreach($k as $i=>$x) $data[$x] = isset($v[$i]) ? $v[$i] : false;
			unset($k, $v);
		} else {
			// syntax 1: $NewValues = array( 'cat1'=>val1, 'cat2'=>val2, ... );		
			$data = $NewValues;
		}
		unset($NewValues);

		// Scann all rows for changing cells
		$elData = clsTbsXmlLoc::FindElement($Txt, 'table:table-rows', 0);
		$p_row = 0;
		while (($elRow=clsTbsXmlLoc::FindElement($elData, 'table:table-row', $p_row))!==false) {
			$p_cell = 0;
			$category = false;
			$data_r = false;
			for ($i=1; $i<=$s_colend; $i++) {
				if ($elCell = clsTbsXmlLoc::FindElement($elRow, 'table:table-cell', $p_cell)) {
					if ($i==$col_cat) {
						// Category
						if ($elP = clsTbsXmlLoc::FindElement($elCell, 'text:p', 0)) {
							$category = $elP->GetInnerSrc();
						}
					} elseif ($i>=$s_col) {
						// Change the value
						$x = 'NaN'; // default value
						if ($s_use_cat) {
							if ( ($category!==false) && isset($data[$category]) ) {
								$x = $data[$category];
								unset($data[$category]); // delete the category in order to keep only unused
							}
						} else {
							$val_idx = $i - $s_col;
							if ($data_r===false) $data_r = array_shift($data); // (may return null) delete the row in order to keep only unused
							if ( (!is_null($data_r)) && isset($data_r[$val_idx])) $x = $data_r[$val_idx];
						}
						if ( ($x===false) || is_null($x) ) $x = 'NaN';
						$elCell->ReplaceAtt('office:value', $x);
						// Delete the cached legend
						if ($elP = clsTbsXmlLoc::FindElement($elCell, 'text:p', 0)) {
							$elP->ReplaceSrc('');
							$elP->UpdateParent(); // update $elCell source
						}
						$elCell->UpdateParent(); // update $elRow source
					}
					$p_cell = $elCell->PosEnd;
				} else {
					$i = $s_colend+1; // ends the loops
				}
			}
			$elRow->UpdateParent(); // update $elData source
			$p_row = $elRow->PosEnd;
		}

		// Add unused data
		$x = '';
		$x_nan = '<table:table-cell office:value-type="float" office:value="NaN"></table:table-cell>';
		foreach ($data as $cat=>$val) {
			$x .= '<table:table-row>';
			if ($s_use_cat) $val = array($val);
			for ($i=1; $i<=$col_nbr; $i++) {
				if ( ($s_col<=$i) && ($i<=$s_colend) ) {
					$val_idx = $i - $s_col;
					if (isset($val[$val_idx])) {
						$x .= '<table:table-cell office:value-type="float" office:value="'.$val[$val_idx].'"></table:table-cell>';
					} else {
						$x .= $x_nan;
					}
				} else {
					if ($s_use_cat && ($i==$col_cat) ) {
						// ENT_NOQUOTES because target is an element's content
						$x .= '<table:table-cell office:value-type="string"><text:p>'.htmlspecialchars($cat, ENT_NOQUOTES).'</text:p></table:table-cell>';
					} else {
						$x .= $x_nan;
					}
				}
			}
			$x .= '</table:table-row>';
		}
		$p = strpos($Txt, '</table:table-rows>', $elData->PosBeg);
		if ($x!=='') $Txt = substr_replace($Txt, $x, $p, 0);

		// Save the result
		$this->TbsStorePut($chart['file_idx'], $Txt);

	}

	/**
	 * Look for all chart in the document, and store information.
	 */
	function OpenDoc_ChartInit() {

		$this->OpenDocCharts = array();

		$idx = $this->Ext_GetMainIdx();
		$Txt = $this->TbsStoreGet($idx, 'OpenDoc_ChartInit');

		$p = 0;
		while($drEl = clsTbsXmlLoc::FindElement($Txt, 'draw:frame', $p)) {

			$src = $drEl->GetInnerSrc();
			$objEl = clsTbsXmlLoc::FindStartTag($src, 'draw:object', 0);

			if ($objEl) { // Picture have <draw:frame> without <draw:object>
				$href = $objEl->GetAttLazy('xlink:href'); // example "./Object 1"
				if ($href) {

					$imgEl = clsTbsXmlLoc::FindElement($src, 'draw:image', 0);
					$img_href = ($imgEl) ? $imgEl->GetAttLazy('xlink:href') : false; // "./ObjectReplacements/Object 1"
					$img_src = ($imgEl) ? $imgEl->GetSrc('xlink:href') : false;

					$titEl = clsTbsXmlLoc::FindElement($src, 'svg:title', 0);
					$title = ($titEl) ? $titEl->GetInnerSrc() : '';

					if (substr($href,0,2)=='./') $href = substr($href, 2);
					if ( is_string($img_href) && (substr($img_href,0,2)=='./') ) $img_href = substr($img_href, 2);
					$this->OpenDocCharts[] = array('href'=>$href, 'title'=>$title, 'img_href'=>$img_href, 'img_src'=>$img_src, 'to_clear'=> ($img_href!==false) );

				}
			}
			$p = $drEl->PosEnd;
		}

		
	}

	function OpenDoc_ChartClear(&$chart) {

		$chart['to_clear'] = false;

		// Delete the file in the archive
		$this->FileReplace($chart['img_href'], false);

		// Delete the element in the main file
		$main = $this->Ext_GetMainIdx();
		$Txt = $this->TbsStoreGet($main, 'OpenDoc_ChartClear');
		$Txt = str_replace($chart['img_src'], '', $Txt);
		$this->TbsStorePut($main, $Txt);

		// Delete the element in the Manifest file
		$manifest = $this->FileGetIdx('META-INF/manifest.xml');
		if ($manifest!==false) {
			$Txt = $this->TbsStoreGet($manifest, 'OpenDoc_ChartClear');
			$el = clsTbsXmlLoc::FindStartTagHavingAtt($Txt, 'manifest:full-path="'.$chart['img_href']."'", 0);
			if ($el) {
				$el->ReplaceSrc('');
				$this->TbsStorePut($manifest, $Txt);
			}
		}

	}

	/**
	 * Find and save informations abouts all series in the chart.
	 */
	function OpenDoc_ChartFindSeries(&$chart, $Txt) {

		// Find series declarations
		$p = 0;
		$s_idx = 0;
		$series = array();
		$cols = array(); // all columns attached to a series
		$cols_name = array();
		while($elSeries = clsTbsXmlLoc::FindElement($Txt, 'chart:series', $p)) {
			$s_cols = array();
			// Column of main value
			$col = $this->OpenDoc_ChartFindCol($cols, $elSeries, 'chart:values-cell-range-address', $s_idx);
			$s_cols[$col] = true;
			// Column's num that contains the name of the series
			$col_name = $this->OpenDoc_ChartFindCol($cols, $elSeries, 'chart:label-cell-address', $s_idx);
			// List of column's nums for other values
			$src = $elSeries->GetInnerSrc();
			$p2 = 0;
			while($elDom = clsTbsXmlLoc::FindStartTag($src, 'chart:domain', $p2)) {
				$col = $this->OpenDoc_ChartFindCol($cols, $elDom, 'table:cell-range-address', $s_idx);
				$s_cols[$col] = true;
				$p2 = $elDom->PosEnd;
			}
			// rearrange col numbers
			ksort($s_cols);
			$s_cols = array_keys($s_cols); // nedded for having first col on index 0
			// Attribute to re-find the series
			$ref = $elSeries->GetAttLazy('chart:label-cell-address');
			// Add the series
			$series[$s_idx] = array(
				'name' => false, // name of the series
				'col_name' => $col_name,
				'cols' => $s_cols,
				'ref' => $ref,
			);
			$cols_name[$col_name] = $s_idx;
			$p = $elSeries->PosEnd;
			$s_idx++;
		}
		$chart['cols'] = $cols;

		// Column of categories
		$col_cat = false;
		$elCat = clsTbsXmlLoc::FindStartTag($Txt, 'chart:categories', 0);
		if ($elCat!==false) {
			$att = $elCat->GetAttLazy('table:cell-range-address');
			$col_cat = $this->Misc_ColNum($att, true); // the column of categories is always #1
		}
		$chart['col_cat'] = $col_cat;


		// Brows headers columns
		$elHeaders = clsTbsXmlLoc::FindElement($Txt, 'table:table-header-rows', 0);
		if ($elHeaders===false) return $this->RaiseError("(ChartFindSeries) : unable to found the series names in the chart ".$this->_ChartCaption.".");
		$p = 0;
		$col_num = 0;
		while (($elCell=clsTbsXmlLoc::FindElement($elHeaders, 'table:table-cell', $p))!==false) {
			$col_num++;
			if (isset($cols_name[$col_num])) {
				$elP = clsTbsXmlLoc::FindElement($elCell, 'text:p', 0);
				$name = ($elP===false) ? '' : $elP->GetInnerSrc();
				$s_idx = $cols_name[$col_num];
				$series[$s_idx]['name'] = $name;
			}
			$p = $elCell->PosEnd;
		}
		$chart['series'] = $series;
		$chart['col_nbr'] = $col_num;

		return true;

	}

	function OpenDoc_ChartFindCol(&$cols, &$el, $att, $s_idx) {
		$x = $el->GetAttLazy($att);
		if ($x===false) return $this->RaiseError("(ChartFindCol) : unable to find cell references for series number #".($idx+1)." in the chart ".$this->_ChartCaption.".");
		$c = $this->Misc_ColNum($x, true);
		if ($s_idx!==false) $cols[$c] = $s_idx;
		return $c;
	}

	function OpenDoc_ChartDelSeries(&$Txt, &$series) {

		$att = 'chart:label-cell-address="'.$series['ref'].'"';
		$elSeries = clsTbsXmlLoc::FindElementHavingAtt($Txt, $att, 0);

		if ($elSeries!==false) $elSeries->ReplaceSrc('');

	}

	function OpenDoc_ChartRenameSeries(&$Txt, &$series, $NewName) {

		$NewName = htmlspecialchars($NewName, ENT_NOQUOTES); // ENT_NOQUOTES because target is an element's content
		$col_name = $series['col_name'];

		$el = clsTbsXmlLoc::FindStartTag($Txt, 'table:table-header-rows', 0);
		$el = clsTbsXmlLoc::FindStartTag($Txt, 'table:table-row', $el->PosEnd);
		for ($i=1; $i<$col_name; $i++) $el = clsTbsXmlLoc::FindStartTag($Txt, 'table:table-cell', $el->PosEnd);
		$elCell = clsTbsXmlLoc::FindElement($Txt, 'table:table-cell', $el->PosEnd);

		$elP = clsTbsXmlLoc::FindElement($elCell, 'text:p', 0);
		if ($elP===false) {
			$elCell->ReplaceInnerSrc($elCell->GetInnerSrc().'<text:p>'.$NewName.'</text:p>');
		} else {
			if($elP->SelfClosing) {
				$elP->ReplaceSrc('<text:p>'.$NewName.'</text:p>');
			} else {
				$elP->ReplaceInnerSrc($NewName);
			}
			$elP->UpdateParent();
		}

	}

	/**
	 * Return information and data about all series in the chart.
	 */
	function OpenDoc_ChartReadSeries($ChartRef, $Complete) {
		
		$Txt = false;
		$chart = $this->OpenDoc_ChartFind($ChartRef, $Txt, 'ChartReadSeries');
		if ($chart === false) return;

		// Read the data table
		$table = array();
		$rows = clsTbsXmlLoc::FindElement($Txt, 'table:table-rows', 0);
		$pr = 0;
		while ($r = clsTbsXmlLoc::FindElement($rows, 'table:table-row', $pr)) {
			$pr = $r->PosEnd;
			$pc = 0;
			$row = array();
			while ($c = clsTbsXmlLoc::FindElement($r, 'table:table-cell', $pc)) {
				$pc = $c->PosEnd;
				$val = $c->getAttLazy('office:value');
				if ($val == 'NaN') { // Not a Number, happens when the cell is empty
					$val = false;
					$txt = '';
				} else {
					if ($x = clsTbsXmlLoc::FindElement($c, 'text:p', 0)) {
						$txt = $x->GetInnerSrc();
					} else {
						$txt = false;
					};
				}
				$row[] = array('val' => $val, 'txt' => $txt);
			}
			$table[] = $row;
		}
		
		// Format series information
		$series = array();
		$cat_idx = $chart['col_cat'] - 1;
		foreach ($chart['series'] as $idx => $info) {
			$cat = array();
			$val = array();
			$col_idx = $info['cols'][0] - 1;
			foreach ($table as $row) {
				$val[] = $row[$col_idx]['val'];
				$cat[] = $row[$cat_idx]['txt'];
			}
			$series[] = array(
				'name' => $info['name'],
				'cat' => $cat,
				'val' => $val,
			);
		}
		
		if ($Complete) {
			// Complete information about the chart
			$main_idx = $this->Ext_GetMainIdx();
			return array(
				'file_idx' => $chart['file_idx'],
				'file_name' => $chart['file_name'],
				'parent_idx' => $main_idx,
				'parent_name' => $this->TbsGetFileName($main_idx),
				'series' => $series,
			);
		} else {
			// Simple information about data
			$simple = array();
			foreach ($series as $s) {
				$name = $s['name'];
				$simple[$name] = array($s['cat'], $s['val']);
			}
			return $simple;
		}
		
	}
	
	function OpenDoc_ChartDebug($nl, $sep, $bull) {

		if ($this->OpenDocCharts===false) $this->OpenDoc_ChartInit();

		$ChartLst = $this->OpenDocCharts;

		echo $nl;
		echo $nl."Charts found in the contents: (use command OPENTBS_CHART_INFO to get series's names and data)";
		echo $nl."-----------------------------";
		foreach ($ChartLst as $i=>$c) {
			$title = ($c['title']===false) ? '(not found)' : var_export($c['title'], true);
			echo $bull."title: $title";
		}
		if (count($ChartLst)==0) echo $bull."(none)";

	}

	/**
	 * Fixes the problem of ODS files built with LibreOffice >= 4 and merged with OpenTBS and opened with Ms Excel.
	 * The virtual number of row can exeed the maximum supported, then Excem raises an error when opening the file.
	 * LibreOffice does not.
	 */
	function OpenDoc_atExcelCompatibility(&$Txt) {
		
		$el_tbl  = 'table:table';
		$el_col  = 'table:table-column'; // Column definition
		$el_row  = 'table:table-row';
		$el_cell = 'table:table-cell';
		$att_rep_col = 'table:number-columns-repeated';
		$att_rep_row = 'table:number-rows-repeated';
		
		$loop = array($att_rep_col, $att_rep_row);
		
		// Loop for deleting useless repeated columns
		foreach ($loop as $att_rep) {
		
			$p = 0;
			while ( $xml = clsTbsXmlLoc::FindElementHavingAtt($Txt, $att_rep, $p) ) {
			
				$xml->FindName();
				$p = $xml->PosEnd;
				
				// Next tag (opening or closing)
				$next = clsTbsXmlLoc::FindStartTagByPrefix($Txt, '', $p);
				$next_name = $next->Name;
				if ($next_name == '') {
					$next_name = $next->GetSrc();
					$next_name = substr($next_name, 1, strlen($next_name) -2);
				};

				$z_src = $next->GetSrc();
				
				//echo " * name=" . $xml->Name . ", suiv_name=$next_name, suiv_src=$z_src\n";

				$delete = false;
				
				if ( ($xml->Name == $el_col) && ($xml->SelfClosing) ) {
					if ( ($next_name == $el_row) || ($next_name == '/' . $el_tbl) ) {
						$delete = true;
					}
				} elseif ( ($xml->Name == $el_cell) && ($xml->SelfClosing) ) {
					if ( $next_name == '/' . $el_row ) {
						$delete = true;
					}
				} elseif ($xml->Name == $el_row) {
					if ( $next_name == '/' . $el_tbl ) {
						$inner_src = '' . $xml->GetInnerSrc();
						if (strpos($inner_src, '<') === false) {
							$delete = true;
						}
					}
				}
				
				if ($delete) {
					//echo " * SUPPRIME " . $xml->Name . " : " . $xml->GetSrc() . "\n";
					$p = $xml->PosBeg;
					$xml->Delete();
				}
				
			}

		}
		
	}
	
}

/**
 * clsTbsXmlLoc
 * Wrapper to search and replace in XML entities.
 * The object represents only the opening tag until method FindEndTag() is called.
 * Then is represents the complete entity.
 */
class clsTbsXmlLoc {

	var $PosBeg;
	var $PosEnd;
	var $SelfClosing;
	var $Txt;
	var $Name = ''; 

	var $pST_PosEnd = false; // start tag: position of the end
	var $pST_Src = false;    // start tag: source
	var $pET_PosBeg = false; // end tag: position of the beginning

	var $Parent = false; // parent object

	// For relative mode
	var $rel_Txt = false;
	var $rel_PosBeg = false;
	var $rel_Len = false;
	
	// Create an instance with the given parameters
	function __construct(&$Txt, $Name, $PosBeg, $SelfClosing = null, $Parent=false) {
	
		$this->PosEnd = strpos($Txt, '>', $PosBeg);
		if ($this->PosEnd===false) $this->PosEnd = strlen($Txt)-1; // should no happen but avoid errors
	
		$this->Txt = &$Txt;
		$this->Name = $Name;
		$this->PosBeg = $PosBeg;
		$this->pST_PosEnd = $this->PosEnd;
		$this->SelfClosing = $SelfClosing;
		$this->Parent = $Parent;
	}

	// Return an array of (val_pos, val_len, very_sart, very_len) of the attribute. Return false if the attribute is not found.
	// Positions are relative to $this->PosBeg.
	// This method is lazy because it assumes the attribute is separated by a space and its value is delimited by double-quote.
	function _GetAttValPos($Att) {
		if ($this->pST_Src===false) $this->pST_Src = substr($this->Txt, $this->PosBeg, $this->pST_PosEnd - $this->PosBeg + 1 );
		$a = ' '.$Att.'="';
		$p0 = strpos($this->pST_Src, $a);
		if ($p0!==false) {
			$p1 = $p0 + strlen($a);
			$p2 = strpos($this->pST_Src, '"', $p1);
			if ($p2!==false) return array($p1, $p2-$p1, $p0, $p2-$p0+1);
		}
		return false;
	}
	
	// Update positions when attributes of the start tag has been upated.
	function _ApplyDiffFromStart($Diff) {
		$this->pST_PosEnd += $Diff;
		$this->pST_Src = false;
		if ($this->pET_PosBeg!==false) $this->pET_PosBeg += $Diff;
		$this->PosEnd += $Diff;
	}
	
	// Update all positions.
	function _ApplyDiffToAll($Diff) {
		$this->PosBeg += $Diff;
		$this->PosEnd += $Diff;
		$this->pST_PosEnd += $Diff;
		if ($this->pET_PosBeg!==false) $this->pET_PosBeg += $Diff;
	}

	// Return true is the ending position is a self-closing.
	function _SelfClosing($PosEnd) {
		return (substr($this->Txt, $PosEnd-1, 1)=='/');
	}
	
	// Return the outer len of the locator.
	function GetLen() {
		return $this->PosEnd - $this->PosBeg + 1;
	}

	// Return the outer source of the locator.
	function GetSrc() {
		return substr($this->Txt, $this->PosBeg, $this->GetLen() );
	}

	// Replace the source of the locator in the TXT contents.
	// Update the locator's ending position.
	// Too complicated to update other information, given that it can be deleted.
	function ReplaceSrc($new) {
		$len = $this->GetLen(); // avoid PHP error : Strict Standards: Only variables should be passed by reference
		$this->Txt = substr_replace($this->Txt, $new, $this->PosBeg, $len);
		$diff = strlen($new) - $len;
		$this->PosEnd += $diff;
		$this->pST_Src = false;
		if ($new==='') {
			$this->pST_PosBeg = false;
			$this->pST_PosEnd = false;
			$this->pET_PosBeg = false;
		} else {
			$this->pST_PosEnd += $diff; // CAUTION: may be wrong if attributes has changed
			if ($this->pET_PosBeg!==false) $this->pET_PosBeg += $diff; // CAUTION: right only if the tag name is the same
		}
	}

	// Return the start of the inner content, or false if it's a self-closing tag 
	// Return false if SelfClosing.
	function GetInnerStart() {
		return ($this->pST_PosEnd===false) ? false : $this->pST_PosEnd + 1;
	}

	// Return the length of the inner content, or false if it's a self-closing tag
	// Assume FindEndTag() is previously called.
	// Return false if SelfClosing.
	function GetInnerLen() {
		return ($this->pET_PosBeg===false) ? false : $this->pET_PosBeg - $this->pST_PosEnd - 1;
	}

	// Return the length of the inner content, or false if it's a self-closing tag 
	// Assume FindEndTag() is previously called.
	// Return false if SelfClosing.
	function GetInnerSrc() {
		return ($this->pET_PosBeg===false) ? false : substr($this->Txt, $this->pST_PosEnd + 1, $this->pET_PosBeg - $this->pST_PosEnd - 1 );
	}

	// Replace the inner source of the locator in the TXT contents. Update the locator's positions.
	// Assume FindEndTag() is previously called.
	// Convert a self-closing entity to a start+end entity if needed.
	function ReplaceInnerSrc($new) {
		if ($this->SelfClosing) {
			$end = '>' . $new . '</' . $this->FindName() . '>';
			$this->Txt = substr_replace($this->Txt, $end, $this->PosEnd - 1, 2);
			$this->SelfClosing = false;
			$this->pST_PosEnd = $this->PosEnd - 1;
			$this->pET_PosBeg = $this->pST_PosEnd + strlen($new) + 1;
			$this->PosEnd = $this->pST_PosEnd + strlen($end) - 1;
		} else {
			$len = $this->GetInnerLen();
			if ($len===false) return false;
			$this->Txt = substr_replace($this->Txt, $new, $this->pST_PosEnd + 1, $len);
			$this->PosEnd += strlen($new) - $len;
			$this->pET_PosBeg += strlen($new) - $len;
		}
	}

	// Update the parent object, if any.
	function UpdateParent($Cascading=false) {
		if ($this->Parent) {
			$this->Parent->ReplaceSrc($this->Txt);
			if ($Cascading) $this->Parent->UpdateParent($Cascading);
		}
	}

	// Get an attribute's value. Or false if the attribute is not found.
	// It's a lazy way because the attribute is searched with the patern {attribute="value" }
	function GetAttLazy($Att) {
		$z = $this->_GetAttValPos($Att);
		if ($z===false) return false;
		return substr($this->pST_Src, $z[0], $z[1]);
	}

	function ReplaceAtt($Att, $Value, $AddIfMissing = false) {

		$Value = ''.$Value;

		$z = $this->_GetAttValPos($Att);
		if ($z===false) {
			if ($AddIfMissing) {
				// Add the attribute
				$Value = ' '.$Att.'="'.$Value.'"';
				$pi = $this->pST_PosEnd;
				if ($this->_SelfClosing($pi)) $pi--;
				$z = array($pi - $this->PosBeg, 0);
			} else {
				return false;
			}
		}

		$this->Txt = substr_replace($this->Txt, $Value, $this->PosBeg + $z[0], $z[1]);

		// update info
		$this->_ApplyDiffFromStart(strlen($Value) - $z[1]);

		return true;

	}
	
	// Delete the element with or without the content.
	function Delete($Contents=true) {
		$this->FindEndTag();
		if ($Contents || $this->SelfClosing) {
			$this->ReplaceSrc('');
		} else {
			$inner = $this->GetInnerSrc();
			$this->ReplaceSrc($inner);
		}
	}
	
	/**
	 * Return true if the attribute existed and is deleted, otherwise return false.
	 */
	function DeleteAtt($Att) {
		$z = $this->_GetAttValPos($Att);
		if ($z===false) return false;
		$this->Txt = substr_replace($this->Txt, '', $this->PosBeg + $z[2], $z[3]);
		$this->_ApplyDiffFromStart( - $z[3]);
		return true;
	}

	// Find the name of the element
	function FindName() {
		if ($this->Name==='') {
			$p = $this->PosBeg;
			do {
				$p++;
				$z = $this->Txt[$p];
			} while ( ($z!==' ') && ($z!=="\r") && ($z!=="\n") && ($z!=='>') && ($z!=='/') );
			$this->Name = substr($this->Txt, $this->PosBeg + 1, $p - $this->PosBeg - 1);
		}
		return $this->Name;
	}

	// Find the ending tag of the object
	// Use $Encaps=true if the element can be self encapsulated (like <div>).
	// Return true if the end is funf
	function FindEndTag($Encaps=false) {
		if (is_null($this->SelfClosing)) {
			$pe = $this->PosEnd;
			$SelfClosing = $this->_SelfClosing($pe);
			if (!$SelfClosing) {
				if ($Encaps) {
					$loc = clsTinyButStrong::f_Xml_FindTag($this->Txt , $this->FindName(), null, $pe, true, -1, false, false);
					if ($loc===false) return false;
					$this->pET_PosBeg = $loc->PosBeg;
					$this->PosEnd = $loc->PosEnd;
				} else {
					$pe = clsTinyButStrong::f_Xml_FindTagStart($this->Txt, $this->FindName(), false, $pe, true , true);
					if ($pe===false) return false;
					$this->pET_PosBeg = $pe;
					$pe = strpos($this->Txt, '>', $pe);
					if ($pe===false) return false;
					$this->PosEnd = $pe;
				}
			}
			$this->SelfClosing = $SelfClosing;
		}
		return true;
	}

	// Swith the locator to a realtive one that has no XML contents before and no XML contents after.
	// Useful to save time in search and replace.
	function switchToRelative() {
		$this->FindEndTag();
		// Save info
		$this->rel_Txt = &$this->Txt;
		$this->rel_PosBeg = $this->PosBeg;
		$this->rel_Len = $this->GetLen();
		// Change the univers
		$src = $this->GetSrc();
		$this->Txt = &$src;
		// Change positions
		$this->_ApplyDiffToAll(-$this->PosBeg);
	}

	// To use after switchToRelative(): save modificatin to the normal contents and update positions.
	function switchToNormal() {
		// Save info
		$src = $this->GetSrc();
		$this->Txt = &$this->rel_Txt;
		$x = false;
		$this->rel_Txt = &$x;
		$this->Txt = substr_replace($this->Txt, $src, $this->rel_PosBeg, $this->rel_Len);
		$this->_ApplyDiffToAll(+$this->rel_PosBeg);
		$this->rel_PosBeg = false;
		$this->rel_Len = false;
	}
	
	/**
	 * Search a start tag of an element in the TXT contents, and return an object if it is found.
	 * Instead of a TXT content, it can be an object of the class. Thus, the object is linked to a copy
	 *  of the source of the parent element. The parent element can receive the changes of the object using method UpdateParent().
	 */
	static function FindStartTag(&$TxtOrObj, $Tag, $PosBeg, $Forward=true) {

		if (is_object($TxtOrObj)) {
			$TxtOrObj->FindEndTag();
			$Txt = $TxtOrObj->GetSrc();
			if ($Txt===false) return false;
			$Parent = &$TxtOrObj;
		} else {
			$Txt = &$TxtOrObj;
			$Parent = false;
		}

		$PosBeg = clsTinyButStrong::f_Xml_FindTagStart($Txt, $Tag, true , $PosBeg, $Forward, true);
		if ($PosBeg===false) return false;

		return new clsTbsXmlLoc($Txt, $Tag, $PosBeg, null, $Parent);

	}

	// Search a start tag by the prefix of the element
	static function FindStartTagByPrefix(&$Txt, $TagPrefix, $PosBeg, $Forward=true) {

		$x = '<'.$TagPrefix;
		$xl = strlen($x);

		if ($Forward) {
			$PosBeg = strpos($Txt, $x, $PosBeg);
		} else {
			$PosBeg = strrpos(substr($Txt, 0, $PosBeg+2), $x);
		}
		if ($PosBeg===false) return false;

		// Read the actual tag name
		$Tag = $TagPrefix;
		$p = $PosBeg + $xl;
		do {
			$z = substr($Txt,$p,1);
			if ( ($z!==' ') && ($z!=="\r") && ($z!=="\n") && ($z!=='>') && ($z!=='/') ) {
				$Tag .= $z;
				$p++;
			} else {
				$p = false;
			}
		} while ($p!==false);

		return new clsTbsXmlLoc($Txt, $Tag, $PosBeg);

	}

	// Search an element in the TXT contents, and return an object if it's found.
	static function FindElement(&$TxtOrObj, $Tag, $PosBeg, $Forward=true) {

		$XmlLoc = clsTbsXmlLoc::FindStartTag($TxtOrObj, $Tag, $PosBeg, $Forward);
		if ($XmlLoc===false) return false;

		$XmlLoc->FindEndTag();
		return $XmlLoc;

	}

	// Search an element in the TXT contents which has the asked attribute, and return an object if it is found.
	// Note that the element found has an unknown name until FindEndTag() is called.
	// The given attribute can be with or without a specific value. Example: 'visible' or 'visible="1"'
	static function FindStartTagHavingAtt(&$Txt, $Att, $PosBeg, $Forward=true) {

		$p = $PosBeg - (($Forward) ? 1 : -1);
		$x = (strpos($Att, '=')===false) ? (' '.$Att.'="') : (' '.$Att); // get the item more precise if not yet done
		$search = true;

		do {
			if ($Forward) {
				$p = strpos($Txt, $x, $p+1);
			} else {
				$p = strrpos(substr($Txt, 0, $p+1), $x);
			}
			if ($p===false) return false;
			do {
			  $p = $p - 1;
			  if ($p<0) return false;
			  $z = $Txt[$p];
			} while ( ($z!=='<') && ($z!=='>') );
			if ($z==='<') $search = false;
		} while ($search);

		return new clsTbsXmlLoc($Txt, '', $p);

	}

	static function FindElementHavingAtt(&$Txt, $Att, $PosBeg, $Forward=true) {

		$XmlLoc = clsTbsXmlLoc::FindStartTagHavingAtt($Txt, $Att, $PosBeg, $Forward);
		if ($XmlLoc===false) return false;

		$XmlLoc->FindEndTag();

		return $XmlLoc;

	}

}

/*
TbsZip version 2.16
Date    : 2014-04-08
Author  : Skrol29 (email: http://www.tinybutstrong.com/onlyyou.html)
Licence : LGPL
This class is independent from any other classes and has been originally created for the OpenTbs plug-in
for TinyButStrong Template Engine (TBS). OpenTbs makes TBS able to merge OpenOffice and Ms Office documents.
Visit http://www.tinybutstrong.com
*/

define('TBSZIP_DOWNLOAD',1);   // download (default)
define('TBSZIP_NOHEADER',4);   // option to use with DOWNLOAD: no header is sent
define('TBSZIP_FILE',8);       // output to file  , or add from file
define('TBSZIP_STRING',32);    // output to string, or add from string

class clsTbsZip {

	function __construct() {
		$this->Meth8Ok = extension_loaded('zlib'); // check if Zlib extension is available. This is need for compress and uncompress with method 8.
		$this->DisplayError = true;
		$this->ArchFile = '';
		$this->Error = false;
	}

	function CreateNew($ArchName='new.zip') {
	// Create a new virtual empty archive, the name will be the default name when the archive is flushed.
		if (!isset($this->Meth8Ok)) $this->__construct();  // for PHP 4 compatibility
		$this->Close(); // note that $this->ArchHnd is set to false here
		$this->Error = false;
		$this->ArchFile = $ArchName;
		$this->ArchIsNew = true;
		$bin = 'PK'.chr(05).chr(06).str_repeat(chr(0), 18);
		$this->CdEndPos = strlen($bin) - 4;
		$this->CdInfo = array('disk_num_curr'=>0, 'disk_num_cd'=>0, 'file_nbr_curr'=>0, 'file_nbr_tot'=>0, 'l_cd'=>0, 'p_cd'=>0, 'l_comm'=>0, 'v_comm'=>'', 'bin'=>$bin);
		$this->CdPos = $this->CdInfo['p_cd'];
	}

	function Open($ArchFile, $UseIncludePath=false) {
	// Open the zip archive
		if (!isset($this->Meth8Ok)) $this->__construct();  // for PHP 4 compatibility
		$this->Close(); // close handle and init info
		$this->Error = false;
		$this->ArchIsNew = false;
		$this->ArchIsStream = (is_resource($ArchFile) && (get_resource_type($ArchFile)=='stream'));
		if ($this->ArchIsStream) {
			$this->ArchFile = 'from_stream.zip';
			$this->ArchHnd = $ArchFile;
		} else {
			// open the file
			$this->ArchFile = $ArchFile;
			$this->ArchHnd = fopen($ArchFile, 'rb', $UseIncludePath);
		}
		$ok = !($this->ArchHnd===false);
		if ($ok) $ok = $this->CentralDirRead();
		return $ok;
	}

	function Close() {
		if (isset($this->ArchHnd) and ($this->ArchHnd!==false)) fclose($this->ArchHnd);
		$this->ArchFile = '';
		$this->ArchHnd = false;
		$this->CdInfo = array();
		$this->CdFileLst = array();
		$this->CdFileNbr = 0;
		$this->CdFileByName = array();
		$this->VisFileLst = array();
		$this->ArchCancelModif();
	}

	function ArchCancelModif() {
		$this->LastReadComp = false; // compression of the last read file (1=compressed, 0=stored not compressed, -1= stored compressed but read uncompressed)
		$this->LastReadIdx = false;  // index of the last file read
		$this->ReplInfo = array();
		$this->ReplByPos = array();
		$this->AddInfo = array();
	}

	function FileAdd($Name, $Data, $DataType=TBSZIP_STRING, $Compress=true) {

		if ($Data===false) return $this->FileCancelModif($Name, false); // Cancel a previously added file

		// Save information for adding a new file into the archive
		$Diff = 30 + 46 + 2*strlen($Name); // size of the header + cd info
		$Ref = $this->_DataCreateNewRef($Data, $DataType, $Compress, $Diff, $Name);
		if ($Ref===false) return false;
		$Ref['name'] = $Name;
		$this->AddInfo[] = $Ref;
		return $Ref['res'];

	}

	function CentralDirRead() {
		$cd_info = 'PK'.chr(05).chr(06); // signature of the Central Directory
		$cd_pos = -22;
		$this->_MoveTo($cd_pos, SEEK_END);
		$b = $this->_ReadData(4);
		if ($b===$cd_info) {
			$this->CdEndPos = ftell($this->ArchHnd) - 4;
		} else {
			$p = $this->_FindCDEnd($cd_info);
			//echo 'p='.var_export($p,true); exit;
			if ($p===false) {
				return $this->RaiseError('The End of Central Directory Record is not found.');
			} else {
				$this->CdEndPos = $p;
				$this->_MoveTo($p+4);
			}
		}
		$this->CdInfo = $this->CentralDirRead_End($cd_info);
		$this->CdFileLst = array();
		$this->CdFileNbr = $this->CdInfo['file_nbr_curr'];
		$this->CdPos = $this->CdInfo['p_cd'];

		if ($this->CdFileNbr<=0) return $this->RaiseError('No header found in the Central Directory.');
		if ($this->CdPos<=0) return $this->RaiseError('No position found for the Central Directory.');

		$this->_MoveTo($this->CdPos);
		for ($i=0;$i<$this->CdFileNbr;$i++) {
			$x = $this->CentralDirRead_File($i);
			if ($x!==false) {
				$this->CdFileLst[$i] = $x;
				$this->CdFileByName[$x['v_name']] = $i;
			}
		}
		return true;
	}

	function CentralDirRead_End($cd_info) {
		$b = $cd_info.$this->_ReadData(18);
		$x = array();
		$x['disk_num_curr'] = $this->_GetDec($b,4,2);  // number of this disk
		$x['disk_num_cd'] = $this->_GetDec($b,6,2);    // number of the disk with the start of the central directory
		$x['file_nbr_curr'] = $this->_GetDec($b,8,2);  // total number of entries in the central directory on this disk
		$x['file_nbr_tot'] = $this->_GetDec($b,10,2);  // total number of entries in the central directory
		$x['l_cd'] = $this->_GetDec($b,12,4);          // size of the central directory
		$x['p_cd'] = $this->_GetDec($b,16,4);          // position of start of central directory with respect to the starting disk number
		$x['l_comm'] = $this->_GetDec($b,20,2);        // .ZIP file comment length
		$x['v_comm'] = $this->_ReadData($x['l_comm']); // .ZIP file comment
		$x['bin'] = $b.$x['v_comm'];
		return $x;
	}

	function CentralDirRead_File($idx) {

		$b = $this->_ReadData(46);

		$x = $this->_GetHex($b,0,4);
		if ($x!=='h:02014b50') return $this->RaiseError("Signature of Central Directory Header #".$idx." (file information) expected but not found at position ".$this->_TxtPos(ftell($this->ArchHnd) - 46).".");

		$x = array();
		$x['vers_used'] = $this->_GetDec($b,4,2);
		$x['vers_necess'] = $this->_GetDec($b,6,2);
		$x['purp'] = $this->_GetBin($b,8,2);
		$x['meth'] = $this->_GetDec($b,10,2);
		$x['time'] = $this->_GetDec($b,12,2);
		$x['date'] = $this->_GetDec($b,14,2);
		$x['crc32'] = $this->_GetDec($b,16,4);
		$x['l_data_c'] = $this->_GetDec($b,20,4);
		$x['l_data_u'] = $this->_GetDec($b,24,4);
		$x['l_name'] = $this->_GetDec($b,28,2);
		$x['l_fields'] = $this->_GetDec($b,30,2);
		$x['l_comm'] = $this->_GetDec($b,32,2);
		$x['disk_num'] = $this->_GetDec($b,34,2);
		$x['int_file_att'] = $this->_GetDec($b,36,2);
		$x['ext_file_att'] = $this->_GetDec($b,38,4);
		$x['p_loc'] = $this->_GetDec($b,42,4);
		$x['v_name'] = $this->_ReadData($x['l_name']);
		$x['v_fields'] = $this->_ReadData($x['l_fields']);
		$x['v_comm'] = $this->_ReadData($x['l_comm']);

		$x['bin'] = $b.$x['v_name'].$x['v_fields'].$x['v_comm'];

		return $x;
	}

	function RaiseError($Msg) {
		if ($this->DisplayError) {
			if (PHP_SAPI==='cli') {
				echo get_class($this).' ERROR with the zip archive: '.$Msg."\r\n";
			} else {
				echo '<strong>'.get_class($this).' ERROR with the zip archive:</strong> '.$Msg.'<br>'."\r\n";
			}
		}
		$this->Error = $Msg;
		return false;
	}

	function Debug($FileHeaders=false) {

		$this->DisplayError = true;

		if ($FileHeaders) {
			// Calculations first in order to have error messages before other information
			$idx = 0;
			$pos = 0;
			$pos_stop = $this->CdInfo['p_cd'];
			$this->_MoveTo($pos);
			while ( ($pos<$pos_stop) && ($ok = $this->_ReadFile($idx,false)) ) {
				$this->VisFileLst[$idx]['p_this_header (debug_mode only)'] = $pos;
				$pos = ftell($this->ArchHnd);
				$idx++;
			}
		}

		$nl = "\r\n";
		echo "<pre>";

		echo "-------------------------------".$nl;
		echo "End of Central Directory record".$nl;
		echo "-------------------------------".$nl;
		print_r($this->DebugArray($this->CdInfo));

		echo $nl;
		echo "-------------------------".$nl;
		echo "Central Directory headers".$nl;
		echo "-------------------------".$nl;
		print_r($this->DebugArray($this->CdFileLst));

		if ($FileHeaders) {
			echo $nl;
			echo "------------------".$nl;
			echo "Local File headers".$nl;
			echo "------------------".$nl;
			print_r($this->DebugArray($this->VisFileLst));
		}

		echo "</pre>";

	}

	function DebugArray($arr) {
		foreach ($arr as $k=>$v) {
			if (is_array($v)) {
				$arr[$k] = $this->DebugArray($v);
			} elseif (substr($k,0,2)=='p_') {
				$arr[$k] = $this->_TxtPos($v);
			}
		}
		return $arr;
	}

	function FileExists($NameOrIdx) {
		return ($this->FileGetIdx($NameOrIdx)!==false);
	}

	function FileGetIdx($NameOrIdx) {
	// Check if a file name, or a file index exists in the Central Directory, and return its index
		if (is_string($NameOrIdx)) {
			if (isset($this->CdFileByName[$NameOrIdx])) {
				return $this->CdFileByName[$NameOrIdx];
			} else {
				return false;
			}
		} else {
			if (isset($this->CdFileLst[$NameOrIdx])) {
				return $NameOrIdx;
			} else {
				return false;
			}
		}
	}

	function FileGetIdxAdd($Name) {
	// Check if a file name exists in the list of file to add, and return its index
		if (!is_string($Name)) return false;
		$idx_lst = array_keys($this->AddInfo);
		foreach ($idx_lst as $idx) {
			if ($this->AddInfo[$idx]['name']===$Name) return $idx;
		}
		return false;
	}

	function FileRead($NameOrIdx, $Uncompress=true) {

		$this->LastReadComp = false; // means the file is not found
		$this->LastReadIdx = false;

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) return $this->RaiseError('File "'.$NameOrIdx.'" is not found in the Central Directory.');

		$pos = $this->CdFileLst[$idx]['p_loc'];
		$this->_MoveTo($pos);

		$this->LastReadIdx = $idx; // Can be usefull to get the idx

		$Data = $this->_ReadFile($idx, true);

		// Manage uncompression
		$Comp = 1; // means the contents stays compressed
		$meth = $this->CdFileLst[$idx]['meth'];
		if ($meth==8) {
			if ($Uncompress) {
				if ($this->Meth8Ok) {
					$Data = gzinflate($Data);
					$Comp = -1; // means uncompressed
				} else {
					$this->RaiseError('Unable to uncompress file "'.$NameOrIdx.'" because extension Zlib is not installed.');
				}
			}
		} elseif($meth==0) {
			$Comp = 0; // means stored without compression
		} else {
			if ($Uncompress) $this->RaiseError('Unable to uncompress file "'.$NameOrIdx.'" because it is compressed with method '.$meth.'.');
		}
		$this->LastReadComp = $Comp;

		return $Data;

	}

	function _ReadFile($idx, $ReadData) {
	// read the file header (and maybe the data ) in the archive, assuming the cursor in at a new file position

		$b = $this->_ReadData(30);

		$x = $this->_GetHex($b,0,4);
		if ($x!=='h:04034b50') return $this->RaiseError("Signature of Local File Header #".$idx." (data section) expected but not found at position ".$this->_TxtPos(ftell($this->ArchHnd)-30).".");

		$x = array();
		$x['vers'] = $this->_GetDec($b,4,2);
		$x['purp'] = $this->_GetBin($b,6,2);
		$x['meth'] = $this->_GetDec($b,8,2);
		$x['time'] = $this->_GetDec($b,10,2);
		$x['date'] = $this->_GetDec($b,12,2);
		$x['crc32'] = $this->_GetDec($b,14,4);
		$x['l_data_c'] = $this->_GetDec($b,18,4);
		$x['l_data_u'] = $this->_GetDec($b,22,4);
		$x['l_name'] = $this->_GetDec($b,26,2);
		$x['l_fields'] = $this->_GetDec($b,28,2);
		$x['v_name'] = $this->_ReadData($x['l_name']);
		$x['v_fields'] = $this->_ReadData($x['l_fields']);

		$x['bin'] = $b.$x['v_name'].$x['v_fields'];

		// Read Data
		if (isset($this->CdFileLst[$idx])) {
			$len_cd = $this->CdFileLst[$idx]['l_data_c'];
			if ($x['l_data_c']==0) {
				// Sometimes, the size is not specified in the local information.
				$len = $len_cd;
			} else {
				$len = $x['l_data_c'];
				if ($len!=$len_cd) {
					//echo "TbsZip Warning: Local information for file #".$idx." says len=".$len.", while Central Directory says len=".$len_cd.".";
				}
			}
		} else {
			$len = $x['l_data_c'];
			if ($len==0) $this->RaiseError("File Data #".$idx." cannt be read because no length is specified in the Local File Header and its Central Directory information has not been found.");
		}

		if ($ReadData) {
			$Data = $this->_ReadData($len);
		} else {
			$this->_MoveTo($len, SEEK_CUR);
		}

		// Description information
		$desc_ok = ($x['purp'][2+3]=='1');
		if ($desc_ok) {
			$b = $this->_ReadData(12);
			$s = $this->_GetHex($b,0,4);
			$d = 0;
			// the specification says the signature may or may not be present
			if ($s=='h:08074b50') {
				$b .= $this->_ReadData(4); 
				$d = 4;
				$x['desc_bin'] = $b;
				$x['desc_sign'] = $s;
			} else {
				$x['desc_bin'] = $b;
			}
			$x['desc_crc32']    = $this->_GetDec($b,0+$d,4);
			$x['desc_l_data_c'] = $this->_GetDec($b,4+$d,4);
			$x['desc_l_data_u'] = $this->_GetDec($b,8+$d,4);
		}

		// Save file info without the data
		$this->VisFileLst[$idx] = $x;

		// Return the info
		if ($ReadData) {
			return $Data;
		} else {
			return true;
		}

	}

	function FileReplace($NameOrIdx, $Data, $DataType=TBSZIP_STRING, $Compress=true) {
	// Store replacement information.

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) return $this->RaiseError('File "'.$NameOrIdx.'" is not found in the Central Directory.');

		$pos = $this->CdFileLst[$idx]['p_loc'];

		if ($Data===false) {
			// file to delete
			$this->ReplInfo[$idx] = false;
			$Result = true;
		} else {
			// file to replace
			$Diff = - $this->CdFileLst[$idx]['l_data_c'];
			$Ref = $this->_DataCreateNewRef($Data, $DataType, $Compress, $Diff, $NameOrIdx);
			if ($Ref===false) return false;
			$this->ReplInfo[$idx] = $Ref;
			$Result = $Ref['res'];
		}

		$this->ReplByPos[$pos] = $idx;

		return $Result;

	}

	/**
	 * Return the state of the file.
	 * @return {string} 'u'=unchanged, 'm'=modified, 'd'=deleted, 'a'=added, false=unknown
	 */
	function FileGetState($NameOrIdx) {

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) {
			$idx = $this->FileGetIdxAdd($NameOrIdx);
			if ($idx===false) {
				return false;
			} else {
				return 'a';
			}
		} elseif (isset($this->ReplInfo[$idx])) {
			if ($this->ReplInfo[$idx]===false) {
				return 'd';
			} else {
				return 'm';
			}
		} else {
			return 'u';
		}

	}

	function FileCancelModif($NameOrIdx, $ReplacedAndDeleted=true) {
	// cancel added, modified or deleted modifications on a file in the archive
	// return the number of cancels

		$nbr = 0;

		if ($ReplacedAndDeleted) {
			// replaced or deleted files
			$idx = $this->FileGetIdx($NameOrIdx);
			if ($idx!==false) {
				if (isset($this->ReplInfo[$idx])) {
					$pos = $this->CdFileLst[$idx]['p_loc'];
					unset($this->ReplByPos[$pos]);
					unset($this->ReplInfo[$idx]);
					$nbr++;
				}
			}
		}

		// added files
		$idx = $this->FileGetIdxAdd($NameOrIdx);
		if ($idx!==false) {
			unset($this->AddInfo[$idx]);
			$nbr++;
		}

		return $nbr;

	}

	function Flush($Render=TBSZIP_DOWNLOAD, $File='', $ContentType='') {

		if ( ($File!=='') && ($this->ArchFile===$File) && ($Render==TBSZIP_FILE) ) {
			$this->RaiseError('Method Flush() cannot overwrite the current opened archive: \''.$File.'\''); // this makes corrupted zip archives without PHP error.
			return false;
		}

		$ArchPos = 0;
		$Delta = 0;
		$FicNewPos = array();
		$DelLst = array(); // idx of deleted files
		$DeltaCdLen = 0; // delta of the CD's size

		$now = time();
		$date  = $this->_MsDos_Date($now);
		$time  = $this->_MsDos_Time($now);

		if (!$this->OutputOpen($Render, $File, $ContentType)) return false;

		// output modified zipped files and unmodified zipped files that are beetween them
		ksort($this->ReplByPos);
		foreach ($this->ReplByPos as $ReplPos => $ReplIdx) {
			// output data from the zip archive which is before the data to replace
			$this->OutputFromArch($ArchPos, $ReplPos);
			// get current file information
			if (!isset($this->VisFileLst[$ReplIdx])) $this->_ReadFile($ReplIdx, false);
			$FileInfo =& $this->VisFileLst[$ReplIdx];
			$b1 = $FileInfo['bin'];
			if (isset($FileInfo['desc_bin'])) {
				$b2 = $FileInfo['desc_bin'];
			} else {
				$b2 = '';
			}
			$info_old_len = strlen($b1) + $this->CdFileLst[$ReplIdx]['l_data_c'] + strlen($b2); // $FileInfo['l_data_c'] may have a 0 value in some archives
			// get replacement information
			$ReplInfo =& $this->ReplInfo[$ReplIdx];
			if ($ReplInfo===false) {
				// The file is to be deleted
				$Delta = $Delta - $info_old_len; // headers and footers are also deleted
				$DelLst[$ReplIdx] = true;
			} else {
				// prepare the header of the current file
				$this->_DataPrepare($ReplInfo); // get data from external file if necessary
				$this->_PutDec($b1, $time, 10, 2); // time
				$this->_PutDec($b1, $date, 12, 2); // date
				$this->_PutDec($b1, $ReplInfo['crc32'], 14, 4); // crc32
				$this->_PutDec($b1, $ReplInfo['len_c'], 18, 4); // l_data_c
				$this->_PutDec($b1, $ReplInfo['len_u'], 22, 4); // l_data_u
				if ($ReplInfo['meth']!==false) $this->_PutDec($b1, $ReplInfo['meth'], 8, 2); // meth
				// prepare the bottom description if the zipped file, if any
				if ($b2!=='') {
					$d = (strlen($b2)==16) ? 4 : 0; // offset because of the signature if any
					$this->_PutDec($b2, $ReplInfo['crc32'], $d+0, 4); // crc32
					$this->_PutDec($b2, $ReplInfo['len_c'], $d+4, 4); // l_data_c
					$this->_PutDec($b2, $ReplInfo['len_u'], $d+8, 4); // l_data_u
				}
				// output data
				$this->OutputFromString($b1.$ReplInfo['data'].$b2);
				unset($ReplInfo['data']); // save PHP memory
				$Delta = $Delta + $ReplInfo['diff'] + $ReplInfo['len_c'];
			}
			// Update the delta of positions for zipped files which are physically after the currently replaced one
			for ($i=0;$i<$this->CdFileNbr;$i++) {
				if ($this->CdFileLst[$i]['p_loc']>$ReplPos) {
					$FicNewPos[$i] = $this->CdFileLst[$i]['p_loc'] + $Delta;
				}
			}
			// Update the current pos in the archive
			$ArchPos = $ReplPos + $info_old_len;
		}

		// Ouput all the zipped files that remain before the Central Directory listing
		if ($this->ArchHnd!==false) $this->OutputFromArch($ArchPos, $this->CdPos); // ArchHnd is false if CreateNew() has been called
		$ArchPos = $this->CdPos;

		// Output file to add
		$AddNbr = count($this->AddInfo);
		$AddDataLen = 0; // total len of added data (inlcuding file headers)
		if ($AddNbr>0) {
			$AddPos = $ArchPos + $Delta; // position of the start
			$AddLst = array_keys($this->AddInfo);
			foreach ($AddLst as $idx) {
				$n = $this->_DataOuputAddedFile($idx, $AddPos);
				$AddPos += $n;
				$AddDataLen += $n;
			}
		}

		// Modifiy file information in the Central Directory for replaced files
		$b2 = '';
		$old_cd_len = 0;
		for ($i=0;$i<$this->CdFileNbr;$i++) {
			$b1 = $this->CdFileLst[$i]['bin'];
			$old_cd_len += strlen($b1);
			if (!isset($DelLst[$i])) {
				if (isset($FicNewPos[$i])) $this->_PutDec($b1, $FicNewPos[$i], 42, 4);   // p_loc
				if (isset($this->ReplInfo[$i])) {
					$ReplInfo =& $this->ReplInfo[$i];
					$this->_PutDec($b1, $time, 12, 2); // time
					$this->_PutDec($b1, $date, 14, 2); // date
					$this->_PutDec($b1, $ReplInfo['crc32'], 16, 4); // crc32
					$this->_PutDec($b1, $ReplInfo['len_c'], 20, 4); // l_data_c
					$this->_PutDec($b1, $ReplInfo['len_u'], 24, 4); // l_data_u
					if ($ReplInfo['meth']!==false) $this->_PutDec($b1, $ReplInfo['meth'], 10, 2); // meth
				}
				$b2 .= $b1;
			}
		}
		$this->OutputFromString($b2);
		$ArchPos += $old_cd_len;
 		$DeltaCdLen =  $DeltaCdLen + strlen($b2) - $old_cd_len;
 
		// Output until "end of central directory record"
		if ($this->ArchHnd!==false) $this->OutputFromArch($ArchPos, $this->CdEndPos); // ArchHnd is false if CreateNew() has been called

		// Output file information of the Central Directory for added files
		if ($AddNbr>0) {
			$b2 = '';
			foreach ($AddLst as $idx) {
				$b2 .= $this->AddInfo[$idx]['bin'];
			}
			$this->OutputFromString($b2);
			$DeltaCdLen += strlen($b2);
		}

		// Output "end of central directory record"
		$b2 = $this->CdInfo['bin'];
		$DelNbr = count($DelLst);
		if ( ($AddNbr>0) || ($DelNbr>0) ) {
			// total number of entries in the central directory on this disk
			$n = $this->_GetDec($b2, 8, 2);
			$this->_PutDec($b2, $n + $AddNbr - $DelNbr,  8, 2);
			// total number of entries in the central directory
			$n = $this->_GetDec($b2, 10, 2);
			$this->_PutDec($b2, $n + $AddNbr - $DelNbr, 10, 2);
			// size of the central directory
			$n = $this->_GetDec($b2, 12, 4);
			$this->_PutDec($b2, $n + $DeltaCdLen, 12, 4);
			$Delta = $Delta + $AddDataLen;
		}
		$this->_PutDec($b2, $this->CdPos+$Delta , 16, 4); // p_cd  (offset of start of central directory with respect to the starting disk number)
		$this->OutputFromString($b2);

		$this->OutputClose();

		return true;

	}

	// ----------------
	// output functions
	// ----------------

	function OutputOpen($Render, $File, $ContentType) {

		if (($Render & TBSZIP_FILE)==TBSZIP_FILE) {
			$this->OutputMode = TBSZIP_FILE;
			if (''.$File=='') $File = basename($this->ArchFile).'.zip';
			$this->OutputHandle = @fopen($File, 'w');
			if ($this->OutputHandle===false) {
				return $this->RaiseError('Method Flush() cannot overwrite the target file \''.$File.'\'. This may not be a valid file path or the file may be locked by another process or because of a denied permission.');
			}
		} elseif (($Render & TBSZIP_STRING)==TBSZIP_STRING) {
			$this->OutputMode = TBSZIP_STRING;
			$this->OutputSrc = '';
		} elseif (($Render & TBSZIP_DOWNLOAD)==TBSZIP_DOWNLOAD) {
			$this->OutputMode = TBSZIP_DOWNLOAD;
			// Output the file
			if (''.$File=='') $File = basename($this->ArchFile);
			if (($Render & TBSZIP_NOHEADER)==TBSZIP_NOHEADER) {
			} else {
				header ('Pragma: no-cache');
				if ($ContentType!='') header ('Content-Type: '.$ContentType);
				header('Content-Disposition: attachment; filename="'.$File.'"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: public');
				header('Content-Description: File Transfer'); 
				header('Content-Transfer-Encoding: binary');
				$Len = $this->_EstimateNewArchSize();
				if ($Len!==false) header('Content-Length: '.$Len); 
			}
		} else {
			return $this->RaiseError('Method Flush is called with a unsupported render option.');
		}

		return true;

	}

	function OutputFromArch($pos, $pos_stop) {
		$len = $pos_stop - $pos;
		if ($len<0) return;
		$this->_MoveTo($pos);
		$block = 1024;
		while ($len>0) {
			$l = min($len, $block);
			$x = $this->_ReadData($l);
			$this->OutputFromString($x);
			$len = $len - $l;
		}
		unset($x);
	}

	function OutputFromString($data) {
		if ($this->OutputMode===TBSZIP_DOWNLOAD) {
			echo $data; // donwload
		} elseif ($this->OutputMode===TBSZIP_STRING) {
			$this->OutputSrc .= $data; // to string
		} elseif (TBSZIP_FILE) {
			fwrite($this->OutputHandle, $data); // to file
		}
	}

	function OutputClose() {
		if ( ($this->OutputMode===TBSZIP_FILE) && ($this->OutputHandle!==false) ) {
			fclose($this->OutputHandle);
			$this->OutputHandle = false;
		}
	}

	// ----------------
	// Reading functions
	// ----------------

	function _MoveTo($pos, $relative = SEEK_SET) {
		fseek($this->ArchHnd, $pos, $relative);
	}

	function _ReadData($len) {
		if ($len>0) {
			$x = fread($this->ArchHnd, $len);
			return $x;
		} else {
			return '';
		}
	}

	// ----------------
	// Take info from binary data
	// ----------------

	function _GetDec($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		$z = 0;
		for ($i=0;$i<$len;$i++) {
			$asc = ord($x[$i]);
			if ($asc>0) $z = $z + $asc*pow(256,$i);
		}
		return $z;
	}

	function _GetHex($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		return 'h:'.bin2hex(strrev($x));
	}

	function _GetBin($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		$z = '';
		for ($i=0;$i<$len;$i++) {
			$asc = ord($x[$i]);
			if (isset($x[$i])) {
				for ($j=0;$j<8;$j++) {
					$z .= ($asc & pow(2,$j)) ? '1' : '0';
				}
			} else {
				$z .= '00000000';
			}
		}
		return 'b:'.$z;
	}

	// ----------------
	// Put info into binary data
	// ----------------

	function _PutDec(&$txt, $val, $pos, $len) {
		$x = '';
		for ($i=0;$i<$len;$i++) {
			if ($val==0) {
				$z = 0;
			} else {
				$z = intval($val % 256);
				if (($val<0) && ($z!=0)) { // ($z!=0) is very important, example: val=-420085702
					// special opration for negative value. If the number id too big, PHP stores it into a signed integer. For example: crc32('coucou') => -256185401 instead of  4038781895. NegVal = BigVal - (MaxVal+1) = BigVal - 256^4
					$val = ($val - $z)/256 -1;
					$z = 256 + $z;
				} else {
					$val = ($val - $z)/256;
				}
			}
			$x .= chr($z);
		}
		$txt = substr_replace($txt, $x, $pos, $len);
	}

	function _MsDos_Date($Timestamp = false) {
		// convert a date-time timstamp into the MS-Dos format
		$d = ($Timestamp===false) ? getdate() : getdate($Timestamp);
		return (($d['year']-1980)*512) + ($d['mon']*32) + $d['mday'];
	}
	function _MsDos_Time($Timestamp = false) {
		// convert a date-time timstamp into the MS-Dos format
		$d = ($Timestamp===false) ? getdate() : getdate($Timestamp);
		return ($d['hours']*2048) + ($d['minutes']*32) + intval($d['seconds']/2); // seconds are rounded to an even number in order to save 1 bit
	}

	function _MsDos_Debug($date, $time) {
		// Display the formated date and time. Just for debug purpose.
		// date end time are encoded on 16 bits (2 bytes) : date = yyyyyyymmmmddddd , time = hhhhhnnnnnssssss
		$y = ($date & 65024)/512 + 1980;
		$m = ($date & 480)/32;
		$d = ($date & 31);
		$h = ($time & 63488)/2048;
		$i = ($time & 1984)/32;
		$s = ($time & 31) * 2; // seconds have been rounded to an even number in order to save 1 bit
		return $y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT).' '.str_pad($h,2,'0',STR_PAD_LEFT).':'.str_pad($i,2,'0',STR_PAD_LEFT).':'.str_pad($s,2,'0',STR_PAD_LEFT);
	}

	function _TxtPos($pos) {
		// Return the human readable position in both decimal and hexa
		return $pos." (h:".dechex($pos).")";
	}

	/**
	 * Search the record of end of the Central Directory.
	 * Return the position of the record in the file.
	 * Return false if the record is not found. The comment cannot exceed 65335 bytes (=FFFF).
	 * The method is read backwards a block of 256 bytes and search the key in this block.
	 */
	function _FindCDEnd($cd_info) {
		$nbr = 1;
		$p = false;
		$pos = ftell($this->ArchHnd) - 4 - 256;
		while ( ($p===false) && ($nbr<256) ) {
			if ($pos<=0) {
				$pos = 0;
				$nbr = 256; // in order to make this a last check
			}
			$this->_MoveTo($pos);
			$x = $this->_ReadData(256);
			$p = strpos($x, $cd_info);
			if ($p===false) {
				$nbr++;
				$pos = $pos - 256 - 256;
			} else {
				return $pos + $p;
			}
		}
		return false;
	}
	
	function _DataOuputAddedFile($Idx, $PosLoc) {

		$Ref =& $this->AddInfo[$Idx];
		$this->_DataPrepare($Ref); // get data from external file if necessary

		// Other info
		$now = time();
		$date  = $this->_MsDos_Date($now);
		$time  = $this->_MsDos_Time($now);
		$len_n = strlen($Ref['name']);
		$purp  = 2048 ; // purpose // +8 to indicates that there is an extended local header 

		// Header for file in the data section 
		$b = 'PK'.chr(03).chr(04).str_repeat(' ',26); // signature
		$this->_PutDec($b,20,4,2); //vers = 20
		$this->_PutDec($b,$purp,6,2); // purp
		$this->_PutDec($b,$Ref['meth'],8,2);  // meth
		$this->_PutDec($b,$time,10,2); // time
		$this->_PutDec($b,$date,12,2); // date
		$this->_PutDec($b,$Ref['crc32'],14,4); // crc32
		$this->_PutDec($b,$Ref['len_c'],18,4); // l_data_c
		$this->_PutDec($b,$Ref['len_u'],22,4); // l_data_u
		$this->_PutDec($b,$len_n,26,2); // l_name
		$this->_PutDec($b,0,28,2); // l_fields
		$b .= $Ref['name']; // name
		$b .= ''; // fields

		// Output the data
		$this->OutputFromString($b.$Ref['data']);
		$OutputLen = strlen($b) + $Ref['len_c']; // new position of the cursor
		unset($Ref['data']); // save PHP memory

		// Information for file in the Central Directory
		$b = 'PK'.chr(01).chr(02).str_repeat(' ',42); // signature
		$this->_PutDec($b,20,4,2);  // vers_used = 20
		$this->_PutDec($b,20,6,2);  // vers_necess = 20
		$this->_PutDec($b,$purp,8,2);  // purp
		$this->_PutDec($b,$Ref['meth'],10,2); // meth
		$this->_PutDec($b,$time,12,2); // time
		$this->_PutDec($b,$date,14,2); // date
		$this->_PutDec($b,$Ref['crc32'],16,4); // crc32
		$this->_PutDec($b,$Ref['len_c'],20,4); // l_data_c
		$this->_PutDec($b,$Ref['len_u'],24,4); // l_data_u
		$this->_PutDec($b,$len_n,28,2); // l_name
		$this->_PutDec($b,0,30,2); // l_fields
		$this->_PutDec($b,0,32,2); // l_comm
		$this->_PutDec($b,0,34,2); // disk_num
		$this->_PutDec($b,0,36,2); // int_file_att
		$this->_PutDec($b,0,38,4); // ext_file_att
		$this->_PutDec($b,$PosLoc,42,4); // p_loc
		$b .= $Ref['name']; // v_name
		$b .= ''; // v_fields
		$b .= ''; // v_comm

		$Ref['bin'] = $b;

		return $OutputLen;

	}

	function _DataCreateNewRef($Data, $DataType, $Compress, $Diff, $NameOrIdx) {

		if (is_array($Compress)) {
			$result = 2;
			$meth = $Compress['meth'];
			$len_u = $Compress['len_u'];
			$crc32 = $Compress['crc32'];
			$Compress = false;
		} elseif ($Compress and ($this->Meth8Ok)) {
			$result = 1;
			$meth = 8;
			$len_u = false; // means unknown
			$crc32 = false;
		} else {
			$result = ($Compress) ? -1 : 0;
			$meth = 0;
			$len_u = false;
			$crc32 = false;
			$Compress = false;
		}

		if ($DataType==TBSZIP_STRING) {
			$path = false;
			if ($Compress) {
				// we compress now in order to save PHP memory
				$len_u = strlen($Data);
				$crc32 = crc32($Data);
				$Data = gzdeflate($Data);
				$len_c = strlen($Data);
			} else {
				$len_c = strlen($Data);
				if ($len_u===false) {
					$len_u = $len_c;
					$crc32 = crc32($Data);
				}
			}
		} else {
			$path = $Data;
			$Data = false;
			if (file_exists($path)) {
				$fz = filesize($path);
				if ($len_u===false) $len_u = $fz;
				$len_c = ($Compress) ? false : $fz;
			} else {
				return $this->RaiseError("Cannot add the file '".$path."' because it is not found.");
			}
		}

		// at this step $Data and $crc32 can be false only in case of external file, and $len_c is false only in case of external file to compress
		return array('data'=>$Data, 'path'=>$path, 'meth'=>$meth, 'len_u'=>$len_u, 'len_c'=>$len_c, 'crc32'=>$crc32, 'diff'=>$Diff, 'res'=>$result);

	}

	function _DataPrepare(&$Ref) {
	// returns the real size of data
		if ($Ref['path']!==false) {
			$Ref['data'] = file_get_contents($Ref['path']);
			if ($Ref['crc32']===false) $Ref['crc32'] = crc32($Ref['data']);
			if ($Ref['len_c']===false) {
				// means the data must be compressed
				$Ref['data'] = gzdeflate($Ref['data']);
				$Ref['len_c'] = strlen($Ref['data']);
			}
		}
	}

	function _EstimateNewArchSize($Optim=true) {
	// Return the size of the new archive, or false if it cannot be calculated (because of external file that must be compressed before to be insered)

		if ($this->ArchIsNew) {
			$Len = strlen($this->CdInfo['bin']);
		} elseif ($this->ArchIsStream) {
			$x = fstat($this->ArchHnd);
			$Len = $x['size'];
		} else {
			$Len = filesize($this->ArchFile);
		}

		// files to replace or delete
		foreach ($this->ReplByPos as $i) {
			$Ref =& $this->ReplInfo[$i];
			if ($Ref===false) {
				// file to delete
				$Info =& $this->CdFileLst[$i];
				if (!isset($this->VisFileLst[$i])) {
					if ($Optim) return false; // if $Optimization is set to true, then we d'ont rewind to read information
					$this->_MoveTo($Info['p_loc']);
					$this->_ReadFile($i, false);
				}
				$Vis =& $this->VisFileLst[$i];
				$Len += -strlen($Vis['bin']) -strlen($Info['bin']) - $Info['l_data_c'];
				if (isset($Vis['desc_bin'])) $Len += -strlen($Vis['desc_bin']);
			} elseif ($Ref['len_c']===false) {
				return false; // information not yet known
			} else {
				// file to replace
				$Len += $Ref['len_c'] + $Ref['diff'];
			}
		}

		// files to add
		$i_lst = array_keys($this->AddInfo);
		foreach ($i_lst as $i) {
			$Ref =& $this->AddInfo[$i];
			if ($Ref['len_c']===false) {
				return false; // information not yet known
			} else {
				$Len += $Ref['len_c'] + $Ref['diff'];
			}
		}

		return $Len;

	}

}
