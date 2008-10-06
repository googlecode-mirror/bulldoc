<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id$
***********************************************************************************/

require_once (colesoLibrarian::lib_lname("db"));

class colesoUserLogin
{
	var $login;
	var $uid;
	var $sessID;

	var $passwdType; //Password storage Type encrypted | full
	var $dbConn;
	var $tPrefix;
	var $sessionControl;
	var $ACL;

	function colesoUserLogin()
  {
		$this->dbConn=colesoDB::getConnection();
		$this->passwdType=colesoApplication::getConfigVal('/system/loginPasswordType');
		$this->tPrefix=colesoApplication::getConfigVal('/system/db/tablePrefix');
		$this->sessionControl=new colesoControlLoginSession();
		$this->ACL=new colesoACL();
  }
//- - - - - - - - - - - - - - - - -  - - - - - - - - - -
	function check_passwd($login,$passwd)
  {
    $esclogin=$this->dbConn->escapeString($login);
    $sql_auth="SELECT passwd, UID FROM ".$this->tPrefix."users WHERE login='$esclogin' AND locked!=1";
    $this->dbConn->perform_query($sql_auth);
    if ($this->dbConn->num_rows!=1) return false;
    $row=$this->dbConn->fetch_row();
    $crypted_pass=$row[0];
    if ($this->passwdType=='encrypted') {
      $checkPass=md5($passwd);
    } else {
      $checkPass=$passwd;
    }
    if ($checkPass==$crypted_pass) {
      $this->login=$login;
      $this->uid=$row[1];
      return true;
    } else{
      return false;
    }
  }
//---------------------------------------------------------
  function loadData2class()
  {
		$this->login=$this->sessionControl->login;
		$this->uid=$this->sessionControl->uid;
		$this->sessID=$this->sessionControl->sessid;
  }
//---------------------------------------------------------
  function getLogin()
  {
    return $this->login;
  }
//---------------------------------------------------------
  function getUID()
  {
    return $this->uid;
  }
//---------------------------------------------------------
  function performLogin ($login,$passwd)
	{
	  if ($this->check_passwd($login,$passwd)) {
		  $this->sessID=$this->sessionControl->makeNewSessn($this->login,$this->uid,$passwd);
		  return true;
    } else {
      return false;
    }
	}
//---------------------------------------------------------
  function manage_session()
	{
	  $res=$this->sessionControl->manageSession();
	  if ($res) $this->loadData2class();
	  return $res;
	}
//---------------------------------------------------------
  function close_sessn()
	{
    $this->sessionControl->closeSessn($this->sessID);
	}
//---------------------------------------------------------
  function setKeepSessn()
  {
    $this->sessionControl->setKeepSessn();
  }
//---------------------------------------------------------
}	//class


//=============================================================================
class colesoACL
{

	var $passwdType; //Password storage Type encrypted | full
	var $dbConn;
	var $tPrefix;

	function colesoACL()
  {
		$this->dbConn=colesoDB::getConnection();
		$this->passwdType=colesoApplication::getConfigVal('/system/loginPasswordType');
		$this->tPrefix=colesoApplication::getConfigVal('/system/db/tablePrefix');
  }
//-------------------------------------------------------------------------------
	function addUser($login,$passwd,$email='',$desc='')
  {
		if ($this->passwdType=='encrypted') $passwdcrypt=md5($passwd);
		else $passwdcrypt=$passwd;
		if ($this->userExistsL($login)) return -1;
    $sqlins="INSERT INTO ".$this->tPrefix."users (login,passwd,email,descr,regdate) ".
      "VALUES ('$login','$passwdcrypt','$email','$desc',NOW())";
    $this->dbConn->perform_query ($sqlins,'insert');
    $UID=$this->dbConn->getInsertID();
    return $UID;
  }
//-------------------------------------------------------------------------------
	function updateUser ($uid,$email,$desc='')
	{
		if (!$this->userExistsU($uid)) die ('the user doesn\'t exists');
    $sqlupd="UPDATE ".$this->tPrefix."users SET email='$email'";
    if ($desc) $sqlupd.=",descr='$desc'";
    $sqlupd.=" WHERE UID=$uid";
    $this->dbConn->perform_query ($sqlupd,'update');
  }
//-------------------------------------------------------------------------------
	function chpass ($uid,$passwd)
	{
		if (!$this->userExistsU($uid)) die ('the user doesn\'t exists');
		if ($this->passwdType=='encrypted') $passwdcrypt=md5($passwd);
		else $passwdcrypt=$passwd;
    $sqlupd="UPDATE ".$this->tPrefix."users SET passwd='$passwdcrypt' WHERE UID=$uid";
    $this->dbConn->perform_query ($sqlupd,'update');
  }
//- - - - - - - - - - - - - - - - -  - - - - - - - - - -
	function userUpdateGroupList ($uid, $groupsList)
	{
    if (!$this->userExistsU($uid)) die ('User doesnt\'t exist: '.$uid);
    $this->dbConn->perform_query ('LOCK TABLES '.$this->tPrefix.'group_members WRITE, '.
      $this->tPrefix.'users WRITE,'.
      $this->tPrefix.'groups WRITE','plain');
    $this->userDelFromAllGroup ($uid);
    foreach ($groupsList as $group) {
      if (!$this->groupExists($group)) die ('Group doesnt\'t exist:'.$group);
      $sqlins="INSERT INTO ".$this->tPrefix."group_members (GID,UID) VALUES ($group,$uid)";
      $this->dbConn->perform_query ($sqlins,'insert');
    }
	  $this->dbConn->perform_query ('UNLOCK TABLES','plain');
  }
//- - - - - - - - - - - - - - - - -  - - - - - - - - - -
	function groupUpdateUserList ($gid, $usersList)
	{
    if (!$this->groupExists($gid)) die ('Group doesnt\'t exist: '.$gid);
    $this->dbConn->perform_query ('LOCK TABLES '.$this->tPrefix.'group_members WRITE, '.
      $this->tPrefix.'users WRITE,'.
      $this->tPrefix.'groups WRITE','plain');
    $this->groupDelFromAllUsers ($gid);
    foreach ($usersList as $user) {
      if (!$this->userExistsU($user)) die ('User doesnt\'t exist:'.$group);
      $sqlins="INSERT INTO ".$this->tPrefix."group_members (GID,UID) VALUES ($gid,$user)";
      $this->dbConn->perform_query ($sqlins,'insert');
    }
	  $this->dbConn->perform_query ('UNLOCK TABLES','plain');
  }
//- - - - - - - - - - - - - - - - -  - - - - - - - - - -
	function userDelFromAllGroup ($uid)
	{
    if (!$this->userExistsU($uid)) die ('User doesnt\'t exist: '.$uid);
    $sqldel="DELETE FROM ".$this->tPrefix."group_members WHERE UID='$uid'";
    $this->dbConn->perform_query ($sqldel,'delete');
  }
//- - - - - - - - - - - - - - - - -  - - - - - - - - - -
	function groupDelFromAllUsers ($gid)
	{
    $sqldel="DELETE FROM ".$this->tPrefix."group_members WHERE GID='$gid'";
    $this->dbConn->perform_query ($sqldel,'delete');
  }
//---------------------------------------------------------
  function getLogin($uid)
  {
    if (!$this->userExistsU ($uid)) die ('user doesn\'t exist: '.$uid);
    $sql="SELECT ".$this->tPrefix."users.login FROM ".$this->tPrefix."users WHERE ".$this->tPrefix."users.UID=".$uid;
      $this->dbConn->perform_query ($sql,'select');
      $c=$this->dbConn->fetch_row();
      $login=$c[0];
      return $login;
  }
//---------------------------------------------------------
  function getLoginExt($uid)
  {
    if (!$this->userExistsU ($uid)) die ('user doesn\'t exist: '.$uid);
    $sql="SELECT * FROM ".$this->tPrefix."users WHERE ".$this->tPrefix."users.UID=".$uid;
    $this->dbConn->perform_query ($sql,'select');
    $row=$this->dbConn->fetch_row_assoc();
    return $row;
  }
//---------------------------------------------------------
  function userExistsL ($login)
  {
    $sqlchk="SELECT uid FROM ".$this->tPrefix."users WHERE login='$login'";
    $this->dbConn->perform_query ($sqlchk);
    return ($this->dbConn->getNumRows() == 1);
  }
//---------------------------------------------------------
  function userExistsU ($uid)
  {
    $sqlchk="SELECT uid FROM ".$this->tPrefix."users WHERE UID='$uid'";
    $this->dbConn->perform_query ($sqlchk);
    return ($this->dbConn->getNumRows() == 1);
  }
//---------------------------------------------------------
  function groupExists($gid)
  {
    $sqlchk="SELECT gid FROM ".$this->tPrefix."groups WHERE GID='$gid'";
    $this->dbConn->perform_query ($sqlchk);
    return ($this->dbConn->getNumRows() == 1);
  }
//---------------------------------------------------------
  function groupMmbr($gid,$uid)
  {
    $sqlchk="SELECT uid FROM ".$this->tPrefix."group_members WHERE UID='$uid' AND GID='$gid'";
    $this->dbConn->perform_query ($sqlchk);
    return ($this->dbConn->getNumRows() == 1);
  }
//---------------------------------------------------------
  function getUserMembership($uid)
  {
    $sql='SELECT * FROM '.$this->tPrefix.'group_members WHERE `UID`='.$uid;
    $this->dbConn->perform_query ($sql);
    $result=array();
    while ($row=$this->dbConn->fetch_row_assoc()){
      $result[]=$row['GID'];
    }
    return $result;
  }
//---------------------------------------------------------
  function lockUser($uid)
  {
    $sqlLock='UPDATE '.$this->tPrefix.'users SET locked=1  WHERE UID='.$uid;
    $this->dbConn->perform_query ($sqlLock,'update');
  }
//---------------------------------------------------------
  function unLockUser($uid)
  {
    $sqlLock='UPDATE '.$this->tPrefix.'users SET locked=0 WHERE UID='.$uid;
    $this->dbConn->perform_query ($sqlLock,'update');
  }
}

//=============================================================================
class colesoControlLoginSession
{
	var $login;
	var $uid;
	var $sessid;
	var $sessnTimeoutMin=60;  //move this to configuration file later
	var $sessnClearDays=7;
	var $sessnKeepDays=30;

	var $_db_conn;
	var $tPrefix;
	var $environment;

	function colesoControlLoginSession()
	{
		$this->dbConn=colesoDB::getConnection();
		$this->tPrefix=colesoApplication::getConfigVal('/system/db/tablePrefix');
		$this->environment = colesoApplication::getEnvironment(); //obsolete ref
	}
//-----------------------------------------------------------------------------
  function clearSessions()
  {
    $t=time()-$this->sessnClearDays*86400;
	  $sql="DELETE FROM ".$this->tPrefix."sessions WHERE sess_last_req < $t OR locked=1";
	  $this->dbConn->perform_query ($sql,'delete');
  }
//---------------------------------------------------------
  function loadData2class($fields,$id)
  {
		$this->login=$fields['login'];
		$this->uid=$fields['UID'];
		$this->sessid=$id;
  }
//---------------------------------------------------------
  function checkSessnEx ($id,$pass)
	{
    $this->clearSessions();
  	$t=time()-$this->sessnTimeoutMin*60;
  	$escid=$this->dbConn->escapeString($id);
  	$escpass=$this->dbConn->escapeString($pass);
  	$sql="SELECT login,UID FROM ".$this->tPrefix."sessions ".
    	  "WHERE SESSID='$escid' AND sesspasswd='$escpass' AND (sess_last_req > $t OR keepSession=1)";
  	$this->dbConn->perform_query ($sql);
   	if ($this->dbConn->getNumRows()==1) {
      $fields=$this->dbConn->fetch_row_assoc();	//get values
	    $sql="UPDATE ".$this->tPrefix."sessions SET sess_last_req=".time()." WHERE SESSID=$id";
      $this->dbConn->perform_query ($sql,'update');	//refresh the sessn record
      $this->loadData2class($fields,$id);
	    return true;	//success load
	  }else {
		  return false;
		}
	}
//---------------------------------------------------------
  function manageSession()
	{
	  $sid=$this->environment->getCookieVar("ht_sessid");
	  if ($sid) {
		  $spwd=$this->environment->getCookieVar("ht_spwd");
		  return $this->checkSessnEx($sid,$spwd);
		} else {
		  return false;
		}
	}
//---------------------------------------------------------
  function makeNewSessn($login,$uid,$passw)
	{
  	//$passw is needed just to seed the session passwd generation
	  $spasswd=md5(uniqid($login.$passw.$uid));
  	$t=time();
	  $sql="INSERT INTO ".$this->tPrefix."sessions (login,UID,sesspasswd,sess_start,sess_last_req)
					VALUES ('$login','$uid','$spasswd',NOW(),$t)";
	  $this->dbConn->perform_query ($sql,'insert');
	  $ret_id=$this->dbConn->getInsertID();
	  $this->environment->setCookieVar("ht_sessid",$ret_id, time()+60*60*24*$this->sessnKeepDays);
	  $this->environment->setCookieVar("ht_spwd",$spasswd, time()+60*60*24*$this->sessnKeepDays);
	  return $ret_id;
	}
//---------------------------------------------------------
  function closeSessn($id)
	{
  	$sql="DELETE FROM ".$this->tPrefix."sessions WHERE sessid=$id";
	  $this->dbConn->perform_query ($sql,'delete');
	  $this->environment->setCookieVar("ht_sessid",'',time()- 3600);
	  $this->environment->setCookieVar("ht_spwd",'',time()- 3600);
	}
//---------------------------------------------------------
  function setKeepSessn()
  {
    $sid=$this->environment->getCookieVar("ht_sessid");
    $sql="UPDATE ".$this->tPrefix."sessions SET keepSession=1 WHERE sessid=$sid";
	  $this->_db_conn->perform_query ($sql,'update');	//keep sessn srecord
  }
}
?>
