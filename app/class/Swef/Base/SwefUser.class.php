<?php

namespace Swef\Base;

class SwefUser {

    public  $email;                                // Primary identifier
    public  $memberships            = array ();    // Usergroups of which current user is member (in this context)
    private $swef                   = null;
    public  $userName;                             // Display name
    public  $uuid;                                 // UUID
    public  $verified               = 0;           // Registered user has completed verificastion

    public function __construct ($swef) {
        $this->swef                 = $swef;
        $this->sessionLoad ();
    }

    public function __destruct ( ) {
    }

    public function inUsergroup ($usergroups=array()) {
        if (!is_array($usergroups)) {
            $usergroups = array ($usergroups);
        }
        // Is user in any usergroup passed?
        foreach ($this->memberships as $m) {
            foreach ($usergroups as $u) {
                if ($m[SWEF_COL_USERGROUP]==$u) {
                    return $m;
                }
            }
        }
        return SWEF_BOOL_FALSE;
    }

    public function isLoggedIn ( ) {
        return $this->email;
    }

    public function login ($email,$password,$must_verify) {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
        if ($caller[SWEF_STR_CLASS]!=SWEF_CLASS_SWEF || $caller[SWEF_COL_FUNCTION]!=SWEF_FUNCTION_USERLOGIN) {
            $this->swef->diagnosticPush ('May only be called via '.SWEF_CLASS_SWEF.'::'.SWEF_FUNCTION_USERLOGIN.'()');
            return $this->swef->userLogin ($email,$password);
        }
        $u = $this->swef->db->dbCall (SWEF_CALL_USERAUTHENTICATE,$email);
        if (!is_array($u)) {
            $this->swef->diagnosticPush ('ERROR - login query failed');
            return SWEF_BOOL_FALSE;
        }
        if (!count($u)) {
            $this->swef->diagnosticPush ('ERROR - email/password not found');
            return SWEF_BOOL_FALSE;
        }
        if ($u[0][SWEF_COL_VERIFIED] || !$must_verify) {
            if (password_verify($password,$u[0][SWEF_COL_PASSWORD_HASH])) {
                // Complete log-in process
                $this->swef->diagnosticPush ('Login OK');
                $this->verified     = $u[0][SWEF_COL_VERIFIED];
                $this->uuid         = $u[0][SWEF_COL_UUID];
                $this->email        = $email;
                $this->userName     = $u[0][SWEF_COL_USER_NAME];
                $this->redefine ();
            }
        }
        return $email;
    }

    public function logout ( ) {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
        if ($caller[SWEF_STR_CLASS]!=SWEF_CLASS_SWEF || $caller[SWEF_COL_FUNCTION]!=SWEF_FUNCTION_USERLOGOUT) {
            $this->swef->diagnosticPush ('May only be called via '.SWEF_CLASS_SWEF.'::'.SWEF_FUNCTION_USERLOGOUT.'()');
            return $this->swef->userLogout ();
        }
        $this->verified     = 0;
        $this->uuid         = SWEF_STR__EMPTY;
        $this->email        = SWEF_STR__EMPTY;
        $this->userName     = SWEF_STR__EMPTY;
        $this->redefine ();
    }

    public function membershipsDescribe ($force=null) {
        $usergroups         = array ();
        foreach ($this->memberships as $m) {
            foreach ($this->swef->usergroups as $u) {
                if ($u[SWEF_COL_USERGROUP]==$m[SWEF_COL_USERGROUP]) {
                    array_push ($usergroups,$u[SWEF_COL_USERGROUP_NAME]);
                    break;
                }
            }
        }
        return $usergroups;
    }

    public function membershipsLoad ( ) {
        if (strlen($this->email)) {
            $this->memberships  = $this->swef->db->dbCall (SWEF_CALL_MEMBERSHIPSLOAD,$this->email);
        }
        else {
            $this->memberships  = $this->swef->db->dbCall (SWEF_CALL_MEMBERSHIPSANON);
        }
        if (!is_array($this->memberships) || !count($this->memberships)) {
            $this->swef->diagnosticPush ('membershipsLoad(): could not load memberships: '.$this->swef->db->dbErrorLast());
            return SWEF_BOOL_FALSE;
        }
        return SWEF_BOOL_TRUE;
    }

    public function notify ($msg) {
        $this->swef->notify ($msg);
    }

    public function redefine ( ) {
        $this->swef->sessionSet (SWEF_COL_UUID,$this->uuid);
        $this->swef->sessionSet (SWEF_COL_EMAIL,$this->email);
        $this->swef->sessionSet (SWEF_COL_USER_NAME,$this->userName);
        $this->membershipsLoad ();
    }

    public function sessionLoad ( ) {
        $this->email    = $this->swef->sessionGet (SWEF_COL_EMAIL);
        $this->userName = $this->swef->sessionGet (SWEF_COL_USER_NAME);
        $this->uuid     = $this->swef->sessionGet (SWEF_COL_UUID);
    }

}

?>
