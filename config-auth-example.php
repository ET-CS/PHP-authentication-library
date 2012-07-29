<?php    
    #create an config-auth.php newfile from this example.

    # mysql connection string
    define("sqlserver", "localhost");
    define("sqluser", "user");
    define("sqlpasswd", "password");
    define("db", "db");
    define("prefix", "");
    
    define("allow_login_by_email","true");
    
    # settings below apply only BEFORE initializing the db. do not change after db have created,
    # or run rebuild() and LOSE all data!;
    
    # MyISAM (default) or innodb.
    # MyISAM - faster, innodb - safer
    define("mysqlengine", "innodb");
    define("admin_login", "admin");
    define("admin_passwd", "password");
    define("admin_email","user@example.com");
    define("max_passwd_length", "20");
?>