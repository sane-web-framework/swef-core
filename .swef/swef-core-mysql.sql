
-- SWEF PROCEDURES --

DELIMITER $$

DROP PROCEDURE IF EXISTS `apiOptions`$$
CREATE PROCEDURE `apiOptions`()
BEGIN
  SELECT `api_Procedure` AS `procedure`
        ,`api_Num_Args` AS `num_args`
        ,`api_Usergroup_Preg_Match` AS `usergroup_preg`
        ,`api_Description` AS `description`
  FROM `swef_config_api`
  WHERE `api_Context_Preg_Match` LIKE '%api%'
     OR `api_Context_Preg_Match` LIKE '%www-%'
  ORDER BY `api_Procedure`;
END$$

DROP PROCEDURE IF EXISTS `apiOptionsDashboard`$$
CREATE PROCEDURE `apiOptionsDashboard`()
BEGIN
  SELECT `api_Procedure` AS `procedure`
        ,`api_Num_Args` AS `num_args`
        ,`api_Usergroup_Preg_Match` AS `usergroup_preg`
        ,`api_Description` AS `description`
  FROM `swef_config_api`
  WHERE `api_Context_Preg_Match` LIKE 'dashboard%'
  ORDER BY `api_Procedure`;
END$$

DROP PROCEDURE IF EXISTS `apiPlugins`$$
CREATE PROCEDURE `apiPlugins`()
BEGIN
  SELECT `plugin_Context_LIKE` AS `context_like`
        ,`plugin_Classname` AS `classname`
        ,`plugin_Dash_Usergroup_Preg_Match` AS `dash_usergroup_preg`
        ,`plugin_Enabled` AS `enabled`
        ,`plugin_Handle_Priority` AS `priority`
        ,`plugin_Dash_Allow` AS `dash_allow`
        ,`plugin_Configs` AS `configs`
  FROM `swef_config_plugin`
  ORDER BY `plugin_Handle_Priority`,`plugin_Classname`,`plugin_Context_LIKE`;
END$$

DROP PROCEDURE IF EXISTS `apiShortcuts`$$
CREATE PROCEDURE `apiShortcuts`(IN `sch` VARCHAR(255) CHARSET ascii)
BEGIN
  SELECT `shortcut_Shortcut_URI` AS `shortcut`
        ,`shortcut_Endpoint_URI` AS `endpoint`
  FROM `swef_shortcut`
  WHERE `shortcut_Context_LIKE`='www-%'
    AND `shortcut_Shortcut_URI` LIKE CONCAT('/',sch)
  LIMIT 0,64;
END$$

DROP PROCEDURE IF EXISTS `swefAPIsLoad`$$
CREATE PROCEDURE `swefAPIsLoad`()
BEGIN
  SELECT `api_Context_Preg_Match` AS `context_preg`
        ,`api_Procedure` AS `procedure`
        ,`api_Num_Args` AS `num_args`
        ,`api_Usergroup_Preg_Match` AS `usergroup_preg`
  FROM `swef_config_api`;
END$$

DROP PROCEDURE IF EXISTS `swefContextsLoad`$$
CREATE PROCEDURE `swefContextsLoad`()
BEGIN
  SELECT `context_Language` AS `language`
        ,`context_SERVER_Key` AS `server_key`
        ,`context_Match_Value_Preg` AS `server_value_preg`
        ,`context_Context` AS `context`
        ,`context_Endpoint_Home` AS `home`
        ,`context_Login_Always` AS `login_always`
        ,`context_Login_On_403` AS `login_on_403`
        ,`swef_config_context_property`.*
        ,`context_User_Must_Be_Verified` AS `must_be_verified`
  FROM `swef_config_context`
  LEFT JOIN `swef_config_context_property`
         ON `property_Context`=`context_Context`
  WHERE `context_Enabled`!='0'
  ORDER BY `context_Priority`;
END$$

DROP PROCEDURE IF EXISTS `swefInputsLoad`$$
CREATE PROCEDURE `swefInputsLoad`()
BEGIN
  SELECT `input_Procedure` AS `procedure`
        ,`input_Arg` AS `arg`
        ,`input_Filter_Name` AS `filter_name`
        ,`filter_Filter` AS `filter_var`
        ,`filter_Preg_Match` AS `preg_match`
        ,`filter_Value_Min` AS `value_min`
        ,`filter_Value_Max` AS `value_max`
        ,`filter_Length_Min` AS `length_min`
        ,`filter_Length_Max` AS `length_max`
  FROM `swef_config_input`
  LEFT JOIN `swef_config_filter`
         ON `filter_Name`=`input_Filter_Name`
  ORDER BY `input_Procedure`,`input_Arg`;
END$$

DROP PROCEDURE IF EXISTS `swefMembershipsAnon`$$
CREATE PROCEDURE `swefMembershipsAnon`()
BEGIN
  SELECT `membership_Usergroup` AS `usergroup`
        ,'usergroup,explain' AS `explain`
  FROM `swef_user`
  LEFT JOIN `swef_membership`
         ON `swef_membership`.`membership_UUID`=`swef_user`.`user_UUID`
  WHERE `user_Email`='';
END$$

DROP PROCEDURE IF EXISTS `swefMembershipsLoad`$$
CREATE PROCEDURE `swefMembershipsLoad`(IN `eml` VARCHAR(255) CHARSET ascii)
BEGIN
  SELECT `membership_Usergroup` AS `usergroup`
        ,'usergroup,explain' AS `explain`
  FROM `swef_user`
  LEFT JOIN `swef_membership`
         ON `swef_membership`.`membership_UUID`=`swef_user`.`user_UUID`
  WHERE `user_Email`=eml;
END$$

DROP PROCEDURE IF EXISTS `swefPluginFetch`$$
CREATE PROCEDURE `swefPluginFetch`(IN `cln` VARCHAR(64) CHARSET ascii)
BEGIN
  SELECT `plugin_Context_LIKE` AS `context_like`
        ,`plugin_Classname` AS `classname`
        ,`plugin_Dash_Usergroup_Preg_Match` AS `dash_usergroup_preg`
        ,`plugin_Enabled` AS `enabled`
        ,`plugin_Handle_Priority` AS `priority`
        ,`plugin_Dash_Allow` AS `dash_allow`
        ,`plugin_Configs` AS `configs`
  FROM `swef_config_plugin`
  WHERE `plugin_Classname`=cln;
END$$

DROP PROCEDURE IF EXISTS `swefPluginsList`$$
CREATE PROCEDURE `swefPluginsList`(IN `ctx` VARCHAR(64) CHARSET ascii)
BEGIN
  SELECT ctx LIKE `plugin_Context_LIKE` AS `current_context`
        ,`plugin_Context_LIKE` AS `context_like`
        ,`plugin_Classname` AS `classname`
        ,`plugin_Dash_Usergroup_Preg_Match` AS `dash_usergroup_preg`
        ,`plugin_Enabled` AS `enabled`
        ,`plugin_Handle_Priority` AS `priority`
        ,`plugin_Dash_Allow` AS `dash_allow`
        ,`plugin_Configs` AS `configs`
  FROM `swef_config_plugin`
  ORDER BY `plugin_Handle_Priority`;
END$$

DROP PROCEDURE IF EXISTS `swefPluginsLoad`$$
CREATE PROCEDURE `swefPluginsLoad`(IN `ctx` VARCHAR(64) CHARSET ascii)
BEGIN
  SELECT `plugin_Context_LIKE` AS `context_like`
        ,`plugin_Classname` AS `classname`
        ,`plugin_Dash_Usergroup_Preg_Match` AS `dash_usergroup_preg`
        ,`plugin_Enabled` AS `enabled`
        ,`plugin_Handle_Priority` AS `priority`
        ,`plugin_Dash_Allow` AS `dash_allow`
        ,`plugin_Configs` AS `configs`
  FROM `swef_config_plugin`
WHERE ctx LIKE `plugin_Context_LIKE`
  ORDER BY `plugin_Handle_Priority`,`plugin_Classname`;
END$$

DROP PROCEDURE IF EXISTS `swefRoutersLoad`$$
CREATE PROCEDURE `swefRoutersLoad`(IN `ctx` VARCHAR(64) CHARSET ascii)
BEGIN
  SELECT `router_Context_LIKE` AS `context_like`
        ,`router_Endpoint_Preg_Match` AS `endpoint_preg`
        ,`router_Usergroup_Preg_Match` AS `usergroup_preg`
  FROM `swef_config_router`
  WHERE ctx LIKE `router_Context_LIKE`
  ORDER BY `router_Context_LIKE`='%',`router_Context_LIKE`;
END$$

DROP PROCEDURE IF EXISTS `swefShortcutFetch`$$
CREATE PROCEDURE `swefShortcutFetch`(IN `ctx` VARCHAR(64) CHARSET ascii, IN `sct` VARCHAR(255) CHARSET ascii)
BEGIN
  SELECT `shortcut_Is_System` AS `is_system`
        ,`shortcut_Endpoint_URI` AS `endpoint_uri`
  FROM `swef_shortcut`
  WHERE ctx LIKE `shortcut_Context_LIKE`
    AND `shortcut_Shortcut_URI`=sct
  LIMIT 0,1;
END$$

DROP PROCEDURE IF EXISTS `swefSPCode`$$
CREATE PROCEDURE `swefSPCode`(IN `spn` VARCHAR(64) CHARSET ascii)
BEGIN
  SHOW PROCEDURE CODE spn;
END$$

DROP PROCEDURE IF EXISTS `swefSPsStatus`$$
CREATE PROCEDURE `swefSPsStatus`(IN `dbn` VARCHAR(64) CHARSET ascii)
BEGIN
  SHOW PROCEDURE STATUS
  WHERE `Db`=dbn
  AND `Type`='PROCEDURE';
END$$

DROP PROCEDURE IF EXISTS `swefTemplatesLoad`$$
CREATE PROCEDURE `swefTemplatesLoad`(IN `ctx` VARCHAR(64) CHARSET ascii)
BEGIN
  SELECT `template_Endpoint_Preg_Match` AS `endpoint_preg`
        ,`template_Needs_Script` AS `needs_script`
        ,`template_Content_Type` AS `content_type`
        ,`template_Template_Backreferenced` AS `template_backreferenced`
  FROM `swef_config_template`
  WHERE ctx LIKE `template_Context_LIKE`
  ORDER BY `template_Priority`;
END$$

DROP PROCEDURE IF EXISTS `swefUserAuthenticate`$$
CREATE PROCEDURE `swefUserAuthenticate`(IN `eml` VARCHAR(255) CHARSET ascii)
BEGIN
  SELECT `user_Verified` AS `verified`
        ,`user_UUID` AS `uuid`
        ,`user_Password_Hash` AS `password_hash`
        ,`user_Name_Display` AS `user_name`
  FROM `swef_user`
  WHERE `user_Email`=eml
  LIMIT 0,1;
END$$

DROP PROCEDURE IF EXISTS `swefUsergroupsLoad`$$
CREATE PROCEDURE `swefUsergroupsLoad`()
BEGIN
  SELECT `usergroup_Usergroup` AS `usergroup`
        ,`usergroup_Db_User` AS `db_user`
        ,`usergroup_Name_Display` AS `usergroup_name`
  FROM `swef_config_usergroup`
  ORDER BY `usergroup_Priority`;
END$$

DROP PROCEDURE IF EXISTS `swefUUID`$$
CREATE PROCEDURE `swefUUID`()
BEGIN
  SELECT UUID() AS `uuid`;
END$$

DELIMITER ;


-- SWEF CORE TABLES --

CREATE TABLE IF NOT EXISTS `swef_config_api` (
  `api_Procedure` varchar(64) CHARACTER SET ascii NOT NULL,
  `api_Context_Preg_Match` varchar(64) CHARACTER SET ascii NOT NULL,
  `api_Num_Args` int(1) unsigned NOT NULL,
  `api_Usergroup_Preg_Match` varchar(64) CHARACTER SET armscii8 NOT NULL,
  `api_Description` varchar(255) NOT NULL,
  PRIMARY KEY (`api_Procedure`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_context` (
  `context_Enabled` int(1) unsigned NOT NULL,
  `context_Priority` int(1) unsigned NOT NULL,
  `context_Language` varchar(64) CHARACTER SET ascii NOT NULL DEFAULT 'en-gb',
  `context_Context` varchar(64) CHARACTER SET ascii NOT NULL,
  `context_SERVER_key` varchar(64) CHARACTER SET ascii NOT NULL,
  `context_Match_Value_Preg` varchar(255) CHARACTER SET ascii NOT NULL,
  `context_Endpoint_Home` varchar(64) CHARACTER SET ascii NOT NULL,
  `context_Login_Always` int(1) unsigned NOT NULL,
  `context_Login_On_403` int(1) unsigned NOT NULL,
  `context_User_Must_Be_Verified` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`context_Context`,`context_SERVER_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_context_property` (
  `property_Context` varchar(64) CHARACTER SET ascii NOT NULL,
  `property_Set_Password_Preg_Match` varchar(255) NOT NULL,
  PRIMARY KEY (`property_Context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_filter` (
  `filter_Name` varchar(64) CHARACTER SET ascii NOT NULL,
  `filter_Filter` varchar(64) CHARACTER SET ascii NOT NULL,
  `filter_Preg_Match` varchar(64) CHARACTER SET ascii NOT NULL,
  `filter_Value_Min` varchar(64) CHARACTER SET ascii NOT NULL,
  `filter_Value_Max` varchar(64) NOT NULL,
  `filter_Length_Min` int(11) unsigned NOT NULL,
  `filter_Length_Max` int(11) unsigned NOT NULL,
  PRIMARY KEY (`filter_Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_input` (
  `input_Procedure` varchar(64) CHARACTER SET ascii NOT NULL,
  `input_Arg` int(11) unsigned NOT NULL,
  `input_Filter_Name` varchar(64) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`input_Procedure`,`input_Arg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_plugin` (
  `plugin_Dash_Allow` int(1) unsigned NOT NULL,
  `plugin_Dash_Usergroup_Preg_Match` varchar(64) CHARACTER SET ascii NOT NULL,
  `plugin_Enabled` int(1) unsigned NOT NULL,
  `plugin_Context_LIKE` varchar(64) CHARACTER SET ascii NOT NULL,
  `plugin_Classname` varchar(64) CHARACTER SET ascii NOT NULL,
  `plugin_Handle_Priority` int(1) NOT NULL,
  `plugin_Configs` text CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`plugin_Context_LIKE`,`plugin_Classname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_router` (
  `router_Context_LIKE` varchar(64) CHARACTER SET ascii NOT NULL,
  `router_Endpoint_Preg_Match` varchar(64) CHARACTER SET ascii NOT NULL,
  `router_Usergroup_Preg_Match` varchar(64) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`router_Context_LIKE`,`router_Endpoint_Preg_Match`,`router_Usergroup_Preg_Match`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_template` (
  `template_Priority` int(11) unsigned NOT NULL,
  `template_Context_LIKE` varchar(64) CHARACTER SET ascii NOT NULL,
  `template_Endpoint_Preg_Match` varchar(64) CHARACTER SET ascii NOT NULL,
  `template_Needs_Script` int(1) unsigned NOT NULL,
  `template_Content_Type` varchar(64) CHARACTER SET ascii NOT NULL,
  `template_Template_Backreferenced` varchar(255) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`template_Context_LIKE`,`template_Endpoint_Preg_Match`,`template_Template_Backreferenced`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_config_usergroup` (
  `usergroup_Usergroup` varchar(64) CHARACTER SET ascii NOT NULL DEFAULT '',
  `usergroup_Priority` int(11) unsigned NOT NULL,
  `usergroup_Db_User` varchar(64) CHARACTER SET ascii NOT NULL,
  `usergroup_Name_Display` varchar(64) NOT NULL,
  PRIMARY KEY (`usergroup_Usergroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_membership` (
  `membership_UUID` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `membership_Usergroup` varchar(64) CHARACTER SET ascii NOT NULL DEFAULT '0',
  PRIMARY KEY (`membership_UUID`,`membership_Usergroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_shortcut` (
  `shortcut_Is_System` int(1) unsigned NOT NULL,
  `shortcut_Context_LIKE` varchar(64) CHARACTER SET ascii NOT NULL,
  `shortcut_Shortcut_URI` varchar(255) CHARACTER SET ascii NOT NULL,
  `shortcut_Endpoint_URI` varchar(255) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`shortcut_Context_LIKE`,`shortcut_Shortcut_URI`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `swef_user` (
  `user_Verified` int(1) unsigned NOT NULL,
  `user_UUID` varchar(255) CHARACTER SET ascii NOT NULL,
  `user_Email` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `user_Password_Hash` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `user_Name_Display` varchar(64) NOT NULL,
  PRIMARY KEY (`user_UUID`),
  UNIQUE KEY `user_Email` (`user_Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
