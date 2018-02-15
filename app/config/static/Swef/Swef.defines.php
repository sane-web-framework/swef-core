<?php

// CONFIGURATION

// Fixed strings - these should be left alone (not "userland")

// Plugin event handler tokens
define ( 'SWEF__ON_PLUGINSSETAFTER',            '_on_pluginsSetAfter'                   );
define ( 'SWEF__ON_PUSHBEFORE',                 '_on_pushBefore'                        );
define ( 'SWEF__ON_HEADERSBEFORE',              '_on_headersBefore'                     );
define ( 'SWEF__ON_PAGEIDENTIFYBEFORE',         '_on_pageIdentifyBefore'                );
define ( 'SWEF__ON_PAGEIDENTIFYAFTER',          '_on_pageIdentifyAfter'                 );
define ( 'SWEF__ON_PAGESCRIPTBEFORE',           '_on_pageScriptBefore'                  );
define ( 'SWEF__ON_PAGETEMPLATEBEFORE',         '_on_pageTemplateBefore'                );
define ( 'SWEF__ON_ENDPOINTIDENTIFYBEFORE',     '_on_endpointIdentifyBefore'            );
define ( 'SWEF__ON_ENDPOINTIDENTIFYAFTER',      '_on_endpointIdentifyAfter'             );
define ( 'SWEF__ON_PULLBEFORE',                 '_on_pullBefore'                        );
define ( 'SWEF__ON_ENDPOINTSCRIPTBEFORE',       '_on_endpointScriptBefore'              );
define ( 'SWEF__ON_ENDPOINTTEMPLATEBEFORE',     '_on_endpointTemplateBefore'            );
define ( 'SWEF__ON_PULLAFTER',                  '_on_pullAfter'                         );
define ( 'SWEF__ON_FLUSHBEFORE',                '_on_flushBefore'                       );
define ( 'SWEF__ON_FLUSHAFTER',                 '_on_flushAfter'                        );
define ( 'SWEF__ON_PUSHAFTER',                  '_on_pushAfter'                         );
define ( 'SWEF__ON_DIAGNOSTICAFTER',            '_on_diagnosticAfter'                   );

// Logical tokens
define ( 'SWEF_BOOL_FALSE',                     false                                   );
define ( 'SWEF_BOOL_TRUE',                      true                                    );

// Stored procedure names
define ( 'SWEF_CALL_APIOPTIONS',                'apiOptions'                            );
define ( 'SWEF_CALL_APISLOAD',                  'swefAPIsLoad'                          );
define ( 'SWEF_CALL_CONTEXTSLOAD',              'swefContextsLoad'                      );
define ( 'SWEF_CALL_INPUTSLOAD',                'swefInputsLoad'                        );
define ( 'SWEF_CALL_MEMBERSHIPSANON',           'swefMembershipsAnon'                   );
define ( 'SWEF_CALL_MEMBERSHIPSLOAD',           'swefMembershipsLoad'                   );
define ( 'SWEF_CALL_PLUGINFETCH',               'swefPluginFetch'                       );
define ( 'SWEF_CALL_PLUGINSLIST',               'swefPluginsList'                       );
define ( 'SWEF_CALL_PLUGINSLOAD',               'swefPluginsLoad'                       );
define ( 'SWEF_CALL_ROUTERSLOAD',               'swefRoutersLoad'                       );
define ( 'SWEF_CALL_SHORTCUTFETCH',             'swefShortcutFetch'                     );
define ( 'SWEF_CALL_SPCODE',                    'swefSPCode'                            );
define ( 'SWEF_CALL_SPSSTATUS',                 'swefSPsStatus'                         );
define ( 'SWEF_CALL_TEMPLATESLOAD',             'swefTemplatesLoad'                     );
define ( 'SWEF_CALL_USERGROUPSLOAD',            'swefUsergroupsLoad'                    );
define ( 'SWEF_CALL_USERAUTHENTICATE',          'swefUserAuthenticate'                  );
define ( 'SWEF_CALL_UUID',                      'swefUUID'                              );

// Stored procedure output column names
define ( 'SWEF_COL_APIS',                       'apis'                                  );
define ( 'SWEF_COL_ARG',                        'arg'                                   );
define ( 'SWEF_COL_CLASSNAME',                  'classname'                             );
define ( 'SWEF_COL_CONFIGS',                    'configs'                               );
define ( 'SWEF_COL_CONTENTTYPE',                'content_type'                          );
define ( 'SWEF_COL_CONTEXT',                    'context'                               );
define ( 'SWEF_COL_CONTEXT_LIKE',               'context_like'                          );
define ( 'SWEF_COL_CURRENT_CONTEXT',            'current_context'                       );
define ( 'SWEF_COL_CONTEXT_PREG',               'context_preg'                          );
define ( 'SWEF_COL_DASH_ALLOW',                 'dash_allow'                            );
define ( 'SWEF_COL_DASH_USERGROUP_PREG',        'dash_usergroup_preg'                   );
define ( 'SWEF_COL_EMAIL',                      'email'                                 );
define ( 'SWEF_COL_ENABLED',                    'enabled'                               );
define ( 'SWEF_COL_ENDPOINT',                   'endpoint'                              );
define ( 'SWEF_COL_ENDPOINT_PREG',              'endpoint_preg'                         );
define ( 'SWEF_COL_ENDPOINT_URI',               'endpoint_uri'                          );
define ( 'SWEF_COL_FAIL',                       'fail'                                  );
define ( 'SWEF_COL_FILTER_VAR',                 'filter_var'                            );
define ( 'SWEF_COL_FORCE_LOGIN',                'force_login'                           );
define ( 'SWEF_COL_FUNCTION',                   'function'                              );
define ( 'SWEF_COL_HAS_SCRIPT',                 'has_script'                            );
define ( 'SWEF_COL_HOME',                       'home'                                  );
define ( 'SWEF_COL_INPUTS',                     'inputs'                                );
define ( 'SWEF_COL_IS_MEMBER',                  'is_member'                             );
define ( 'SWEF_COL_LANGUAGE',                   'language'                              );
define ( 'SWEF_COL_LANGUAGES',                  'languages'                             );
define ( 'SWEF_COL_LENGTH_MIN',                 'length_min'                            );
define ( 'SWEF_COL_LENGTH_MAX',                 'length_max'                            );
define ( 'SWEF_COL_LOGIN_ALWAYS',               'login_always'                          );
define ( 'SWEF_COL_LOGIN_ON_403',               'login_on_403'                          );
define ( 'SWEF_COL_MUST_BE_VERIFIED',           'must_be_verified'                      );
define ( 'SWEF_COL_NEEDS_SCRIPT',               'needs_script'                          );
define ( 'SWEF_COL_NUM_ARGS',                   'num_args'                              );
define ( 'SWEF_COL_PARAMETER',                  'parameter'                             );
define ( 'SWEF_COL_PASSWORD_HASH',              'password_hash'                         );
define ( 'SWEF_COL_PREG_MATCH',                 'preg_match'                            );
define ( 'SWEF_COL_PROCEDURE',                  'procedure'                             );
define ( 'SWEF_COL_PLUGINS',                    'plugins'                               );
define ( 'SWEF_COL_ROUTERS',                    'routers'                               );
define ( 'SWEF_COL_S_DEFAULT',                  'structure_default'                     );
define ( 'SWEF_COL_S_ERROR',                    'structure_error'                       );
define ( 'SWEF_COL_SCRIPT'     ,                'script'                                );
define ( 'SWEF_COL_SPECIFICITY',                'specificity'                           );
define ( 'SWEF_COL_SERVER_KEY',                 'server_key'                            );
define ( 'SWEF_COL_SERVER_VALUE_PREG',          'server_value_preg'                     );
define ( 'SWEF_COL_SITE_TITLE',                 'site_title'                            );
define ( 'SWEF_COL_STRUCTURE',                  'structure'                             );
define ( 'SWEF_COL_TEMPLATE',                   'template'                              );
define ( 'SWEF_COL_TEMPLATE_BACKREFERENCED',    'template_backreferenced'               );
define ( 'SWEF_COL_TEMPLATES',                  'templates'                             );
define ( 'SWEF_COL_USER_NAME',                  'user_name'                             );
define ( 'SWEF_COL_USERGROUP',                  'usergroup'                             );
define ( 'SWEF_COL_USERGROUPS',                 'usergroups'                            );
define ( 'SWEF_COL_USERGROUP_NAME',             'usergroup_name'                        );
define ( 'SWEF_COL_USERGROUP_PREG',             'usergroup_preg'                        );
define ( 'SWEF_COL_UUID',                       'uuid'                                  );
define ( 'SWEF_COL_VERIFIED',                   'verified'                              );
define ( 'SWEF_COL_VALUE_MIN',                  'value_min'                             );
define ( 'SWEF_COL_VALUE_MAX',                  'value_max'                             );

// PDO error mode is not user configurable as SWEF database methods rely upon it
define ( 'SWEF_DB_PDO_E_MODE',                  PDO::ERRMODE_EXCEPTION                  );
define ( 'SWEF_STR_PDO_DBNAME',                 'dbname'                                );

// HTTP related
define ( 'SWEF_GET_CLASSNAME',                  'c'                                     );
define ( 'SWEF_GET_OPTION',                     'o'                                     );
define ( 'SWEF_HTTP_HEADER_CONTENTTYPE',        'text/html; charset=UTF-8'              );
define ( 'SWEF_HTTP_STATUS_CODE_200',           '200'                                   );
define ( 'SWEF_HTTP_STATUS_MSG_200',            '200 OK'                                );
define ( 'SWEF_HTTP_STATUS_CODE_403',           '403'                                   );
define ( 'SWEF_HTTP_STATUS_MSG_403',            '403 Unauthorised'                      );
define ( 'SWEF_HTTP_STATUS_CODE_404',           '404'                                   );
define ( 'SWEF_HTTP_STATUS_MSG_404',            '404 Not found'                         );
define ( 'SWEF_HTTP_STATUS_CODE_444',           '444'                                   );
define ( 'SWEF_HTTP_STATUS_MSG_444',            '444 Client sent illegal input(s)'      );
define ( 'SWEF_HTTP_STATUS_CODE_555',           '555'                                   );
define ( 'SWEF_HTTP_STATUS_MSG_555',            '555 Web application error'             );

// Integer tokens
define ( 'SWEF_INT_0',                          0                                       );
define ( 'SWEF_INT_1',                          1                                       );
define ( 'SWEF_INT_2',                          2                                       );
define ( 'SWEF_INT_3',                          3                                       );
define ( 'SWEF_INT_TZO_MAX',                    720                                     );
define ( 'SWEF_INT_TZO_MIN',                    -720                                    );

// Language
define ( 'SWEF_LANG_SOURCE',                    'en-gb'                                 );
define ( 'SWEF_LANG_TAG_CLOSE',                 '</t>'                                  );
define ( 'SWEF_LANG_TAG_OPEN',                  '<t '                                   );

// SWEF modes
define ( 'SWEF_MODE_SWEF',                      1                                       );
define ( 'SWEF_MODE_BESPOKE',                   2                                       );
define ( 'SWEF_MODE_PRODUCTION',                3                                       );
define ( 'SWEF_MODE_DEFAULT',                   SWEF_MODE_PRODUCTION                    );

// Popular PHP error levels
define ( 'SWEF_PHP_ERROR_LEVEL_OFF',            0                                       );
define ( 'SWEF_PHP_ERROR_LEVEL_ERR',            E_ALL & ~E_NOTICE                       );
define ( 'SWEF_PHP_ERROR_LEVEL_ALL',            -1                                      );

// Sorting
define ( 'SWEF_SORT_ASC',                       true                                    );
define ( 'SWEF_SORT_DESC',                      false                                   );
define ( 'SWEF_SORT_NATURAL',                   true                                    );

// Database connection driver tokens
define ( 'SWEF_SUPPORTED_DATABASE_MARIADB',     'mysql'                                 );
define ( 'SWEF_SUPPORTED_DATABASE_MSSQL',       'dblib'                                 );
define ( 'SWEF_SUPPORTED_DATABASE_MYSQL',       'mysql'                                 );
define ( 'SWEF_SUPPORTED_DATABASE_POSTGRESQL',  'pgsql'                                 );
define ( 'SWEF_SUPPORTED_DATABASE_SYBASE',      'dblib'                                 );
define ( 'SWEF_SUPPORTED_DATABASE_DRIVERS',     'dblib,mysql,pgsql'                     );

// Character tokens
define ( 'SWEF_STR__BSLASH',                    "\\"                                    );
define ( 'SWEF_STR__COLON',                     ':'                                     );
define ( 'SWEF_STR__COMMA',                     ','                                     );
define ( 'SWEF_STR__CR',                        "\r"                                    );
define ( 'SWEF_STR__CRLF',                      "\r\n"                                  );
define ( 'SWEF_STR__DASH',                      '-'                                     );
define ( 'SWEF_STR__DBRACKET',                  '()'                                    );
define ( 'SWEF_STR__DCOLON',                    '::'                                    );
define ( 'SWEF_STR__DQUOTE',                    '"'                                     );
define ( 'SWEF_STR__DOLLAR',                    '$'                                     );
define ( 'SWEF_STR__DOT',                       '.'                                     );
define ( 'SWEF_STR__EMPTY',                     ''                                      );
define ( 'SWEF_STR__EQUALS',                    '='                                     );
define ( 'SWEF_STR__FSLASH',                    '/'                                     );
define ( 'SWEF_STR__GT',                        '>'                                     );
define ( 'SWEF_STR__LF',                        "\n"                                    );
define ( 'SWEF_STR__LT',                        '<'                                     );
define ( 'SWEF_STR__QMARK',                     '?'                                     );
define ( 'SWEF_STR__SEMICOLON',                 ';'                                     );
define ( 'SWEF_STR__SPACE',                     ' '                                     );
define ( 'SWEF_STR__SQUOTE',                    "'"                                     );
define ( 'SWEF_STR__SQUOTE_CSV',                "','"                                   );
define ( 'SWEF_STR__UNDERSCORE',                "_"                                     );

// All other literal tokens
define ( 'SWEF_CLASS_SWEF',                     'Swef\Base\SwefSwef'                    );
define ( 'SWEF_F_APPEND',                       'a'                                     );
define ( 'SWEF_F_READ',                         'r'                                     );
define ( 'SWEF_F_WRITE',                        'w'                                     );
define ( 'SWEF_FILE_VERSION',                   'version.txt'                           );
define ( 'SWEF_FORMAT_8601',                    'c'                                     );
define ( 'SWEF_FORMAT_8601_ZONELESS',           'Y-m-d\TH:i:s'                          );
define ( 'SWEF_FORMAT_TZ',                      'Z'                                     );
define ( 'SWEF_FUNCTION_USERLOGIN',             'userLogin'                             );
define ( 'SWEF_FUNCTION_USERLOGOUT',            'userLogout'                            );
define ( 'SWEF_STR_ACCESS',                     'access'                                );
define ( 'SWEF_STR_ARGS',                       'args'                                  );
define ( 'SWEF_STR_BOOLEAN',                    'boolean'                               );
define ( 'SWEF_STR_CLASS',                      'class'                                 );
define ( 'SWEF_STR_CONFIG',                     'configuration'                         );
define ( 'SWEF_STR_CONSTANT',                   'constant'                              );
define ( 'SWEF_STR_CONTENTTYPE',                'Content-Type: '                        );
define ( 'SWEF_STR_CONTEXTS',                   'contexts'                              );
define ( 'SWEF_STR_DASHBOARD',                  'dashboard'                             );
define ( 'SWEF_STR_DATA',                       'data'                                  );
define ( 'SWEF_STR_DIAGNOSTIC',                 'diagnostic'                            );
define ( 'SWEF_STR_DIAGNOSTIC_HR',              '--------'                              );
define ( 'SWEF_STR_DEFINE',                     'define'                                );
define ( 'SWEF_STR_EMAIL',                      'email'                                 );
define ( 'SWEF_STR_ERROR',                      'error'                                 );
define ( 'SWEF_STR_EXT_LOG',                    '.log'                                  );
define ( 'SWEF_STR_EXT_PHP',                    '.php'                                  );
define ( 'SWEF_STR_EXT_VAR',                    '.var'                                  );
define ( 'SWEF_STR_FREQUENCY',                  'frequency'                             );
define ( 'SWEF_STR_INIT',                       'init'                                  );
define ( 'SWEF_STR_X_POWERED_BY',               'X-Powered-By: '                        );
define ( 'SWEF_STR_HELP',                       'help'                                  );
define ( 'SWEF_STR_LOGIN',                      'swef_login'                            );
define ( 'SWEF_STR_LOGIN_ATTEMPT',              'login_attempt'                         );
define ( 'SWEF_STR_LOGOUT',                     'logout'                                );
define ( 'SWEF_STR_MEMBERSHIPS',                'memberships'                           );
define ( 'SWEF_STR_NAME',                       'name'                                  );
define ( 'SWEF_STR_NOTICE',                     'notice'                                );
define ( 'SWEF_STR_NOTIFICATIONS',              'notifications'                         );
define ( 'SWEF_STR_PDO_DBNAME_PREG',            '<(:|;)dbname=([^;]*).*$>'              );
define ( 'SWEF_STR_PHP_SELF',                   'PHP_SELF'                              );
define ( 'SWEF_STR_PHRASES',                    'phrases'                               );
define ( 'SWEF_STR_PLUGINS_CONFIG_SEP_I',       '::'                                    );
define ( 'SWEF_STR_PLUGINS_CONFIG_SEP_O',       ';;'                                    );
define ( 'SWEF_STR_PLUGINS_LIST',               'plugins_list'                          );
define ( 'SWEF_PREG_CONSTANT_FIXED',            '/^\s*define\s*\(\s*([^,]+)\s*,/'       );
define ( 'SWEF_PREG_CONSTANT_VAR',              "/\s*\'([^']+)\'\s*=\s*>\s*\'/"         );
define ( 'SWEF_STR_PROCEDURE',                  'procedure'                             );
define ( 'SWEF_STR_PROCEDURES',                 'procedures'                            );
define ( 'SWEF_STR_PROPERTY_PREFIX',            'property_'                             );
define ( 'SWEF_STR_PASSWORD',                   'password'                              );
define ( 'SWEF_STR_REQUEST_URI',                'REQUEST_URI'                           );
define ( 'SWEF_STR_REQUESTS',                   'requests'                              );
define ( 'SWEF_STR_RESULTS',                    'results'                               );
define ( 'SWEF_STR_START',                      'start'                                 );
define ( 'SWEF_STR_STATUS',                     'status'                                );
define ( 'SWEF_STR_UNTITLED',                   'Untitled'                              );
define ( 'SWEF_STR_USER',                       'user'                                  );
define ( 'SWEF_STR_UTF8',                       'UTF-8'                                 );
define ( 'SWEF_STR_UUIDS',                      'uuids'                                 );
define ( 'SWEF_STR_WHITESPACE_PREG',            '<\s+>'                                 );
define ( 'SWEF_STR_WILDCARD',                   '*'                                     );
define ( 'SWEF_XML_ATTR_FROM',                  'from'                                  );
define ( 'SWEF_XML_ATTR_TO',                    'to'                                    );
define ( 'SWEF_XML_TAG_PHRASE',                 'phrase'                                );
define ( 'SWEF_XML_TAG_ROOT_OPEN',              '<swef>'                                );
define ( 'SWEF_XML_TAG_ROOT_CLOSE',             '</swef>'                               );
define ( 'SWEF_XML_TAG_VERSION',                '<?xml version="1.0"?>'                 );

?>
