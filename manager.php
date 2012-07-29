<?php
include 'config-auth.php';

define("tbl_accounts", prefix."accounts");
    define("tbl_accounts_id", "id");
    define("tbl_accounts_passwd", "passwd");
    define("tbl_accounts_lock", "locked");
define("tbl_emails", prefix."emails");
    define("tbl_emails_id", "id");
    define("tbl_emails_account", "account");
    define("tbl_emails_mail", "email");
    define("tbl_emails_validated", "validated");        

define("tbl_accounts_script", 
    "CREATE TABLE ".tbl_accounts."
    (
    ".tbl_accounts_id." int NOT NULL AUTO_INCREMENT, 
    PRIMARY KEY(".tbl_accounts_id."),        
    ".tbl_accounts_passwd." varchar(".max_passwd_length."),
    ".tbl_accounts_lock." BOOL
    ) type='".mysqlengine."';");

define("tbl_emails_script", "CREATE TABLE ".tbl_emails."
    (
    ".tbl_emails_id." int NOT NULL AUTO_INCREMENT, 
    PRIMARY KEY(id),
    ".tbl_emails_account." int,
    ".tbl_emails_mail." varchar(35),
    ".tbl_emails_validated." BOOL,
    FOREIGN KEY (".tbl_emails_account.") REFERENCES ".tbl_accounts."(".tbl_accounts_id.") ON DELETE CASCADE 
    ) type='".mysqlengine."';");

class accManager {

    private static $conStatic;
    private $con;
    
    public function __call($name, $arguments)
    {
        #echo "Calling object method '$name' " . implode(', ', $arguments). "<br/>";
        switch ($name) {
            case "connect":
                $this->con = self::connect();
                break;
            case "disconnect":
                self::disconnect($this->con);
                $this->con = NULL;
                break;
        }
    }

    /**  As of PHP 5.3.0  */
    public static function __callStatic($name, $arguments)
    {        
        #echo "Calling static method '$name' " . implode(', ', $arguments). "\n";
        switch ($name) {
            case "connect":
                self::$conStatic = self::connect();
                break;
            case "disconnect":
                self::disconnect(self::$conStatic);
                self::$conStatic = NULL;
                break;
        }
    }
    
    /**
     * connect to the accounts sql db
     * @return resource a MySQL link identifier on success or false on failure.
     */
    private static function connect() {
        $con = mysql_connect(sqlserver,sqluser,sqlpasswd);
        if ($con) {
            if (!(mysql_select_db(db, $con))) {
                # TODO: Send me a mail with the errono() and mysql_error();
            }
            return $con;
        } else {
            # TODO: Send me a mail with the errono() and mysql_error();
        }        
    }

    private static function disconnect($con) {
        # check ended. close db.
        mysql_close($con);        
    }
    
    public function accounts_count() {
        $result = mysql_query("SELECT * FROM ".tbl_accounts) or die(mysql_error());
        return mysql_num_rows($result);
    }
    
    public function emails_count() {
        $result = mysql_query("SELECT * FROM ".tbl_emails) or die(mysql_error());
        return mysql_num_rows($result);
    }
    
    public function validated_emails_count() {
        $result = mysql_query("SELECT * FROM ".tbl_emails." WHERE ".tbl_emails_validated."=true") or die(mysql_error());
        return mysql_num_rows($result);
    }
    
    public function lock($id) {
        $result = mysql_query("UPDATE ".tbl_accounts." SET ".tbl_accounts_lock."=true WHERE ".tbl_accounts_id." = ".$id) or die(mysql_error());        
        return $result;
    }

    public function unlock($id) {
        $result = mysql_query("UPDATE ".tbl_accounts." SET ".tbl_accounts_lock."=false WHERE ".tbl_accounts_id." = ".$id) or die(mysql_error());
        return $result;
    }    
    
    public function isLocked($id) {
        $result = mysql_query("SELECT ".tbl_accounts_lock." 
                               FROM ".tbl_accounts." 
                               WHERE ".tbl_accounts_id."='".$id."' AND ".tbl_accounts_lock."=true") 
                        or die(mysql_error());
        $rows = mysql_num_rows($result);        
        if($rows == 0) return false;            
        if($rows == 1) return true;        
        return false;
    }
    
    public function validate_email($email_id) {
        $result = mysql_query("UPDATE ".tbl_emails." SET ".tbl_emails_validated."=true WHERE ".tbl_emails_id." = ".$email_id) or die(mysql_error());
        return $result;
    }
    
    public function is_email_validated($email_id) {
        $result = mysql_query("SELECT ".tbl_emails.".".tbl_emails_validated." 
                               FROM ".tbl_emails." 
                               WHERE ".tbl_emails_id."='".$email_id."' AND ".tbl_emails_validated."=true") 
                        or die(mysql_error());
        $rows = mysql_num_rows($result);        
        if($rows == 0) return false;            
        if($rows == 1) return true;        
        return false;
    }
    
    public function set_default_email($id, $email_id) {
        
    }
    
    public function add_email($id, $email) {
        mysql_query("INSERT INTO ".tbl_emails." (account, email) VALUES ($id, '".$email."')");
    }
    
    public function signin($email, $passwd) {
        mysql_query("INSERT INTO ".tbl_accounts." (".tbl_accounts_passwd.") VALUES ('".$passwd."')");
        $id = mysql_insert_id();
        mysql_query("INSERT INTO ".tbl_emails." (".tbl_emails_account.", ".tbl_emails_mail.", ".tbl_emails_validated.") VALUES ('".$id."', '".$email."', false)");
        return $id;
    }
    
    public function login($email, $passwd) {        
        if (allow_login_by_email=="true") {
            $result = mysql_query("SELECT accounts.id FROM emails LEFT JOIN accounts ON accounts.id = emails.account
            WHERE emails.email = '".$email."' AND accounts.passwd = '".$passwd."'") or die(mysql_error());
            $rows = mysql_num_rows($result);        
            if($rows == 0) return false;
            while($row = mysql_fetch_array($result)) return $row['id'];            
        }
        return false;
    }
    
    public function delete_account_perm ($id) {
        mysql_query("DELETE FROM ".tbl_accounts." WHERE ".tbl_accounts_id." = ".$id);
        if (mysqlengine!="innodb") {
            // inno-db takes care of the following auto by relations:
            mysql_query("DELETE FROM ".tbl_emails." WHERE ".tbl_emails_account." = ".$id);        
        }        
    }
    
    public function update_password($id, $passwd) {        
        $result = mysql_query("UPDATE accounts SET passwd='".$passwd."' WHERE id = ".$id) or die(mysql_error());        
        return $result;
    }
    
    # check DB
    public function check() {        
        #connection mysql
        @$con = self::connect();
        if ($con) {            
            # check ended. close db.
            mysql_close($con);
        } else {
            # TODO: Send me a mail with the errono() and mysql_error();
        }        
        return $con;        
    }

    private function showerror(  )
    {
        die("Error " . mysql_errno(  ) . " : " . mysql_error(  ));
    }
    
    private function test_table($con, $table) {
        $this->test_echo("checking for table '".$table."'... ");
        if (!($result = @ mysql_query ("SELECT * FROM ".$table, $con))) 
                if (mysql_errno()==1146) {
                    $this->test_echo("warning! table missing! (you should run 'accmgr->rebuild()')");
                } else { $this->showerror(); }
        echo '<br />';
    }
    
    private function test_echo($msg) {
        echo $msg;
    }
    
    public function test() {
        $this->test_echo("Testing accounts manager:<br/>");
        #connection mysql        
        $this->test_echo("Connecting mysql... ");
        @$con = $this->connect();
        if (!$con) {
            $this->showerror();
        }
        $this->test_echo("mysql connected.<br/>");
        $this->test_echo("Selecting db... ");
        # connecting database
        if (!(mysql_select_db("accounts", $con))) {
            # database missing?!
            $this->showerror();
        } else {
            $this->test_echo("accounts database found.<br/>");
        }
        $this->test_table($con, tbl_accounts);
        $this->test_table($con, tbl_emails);
                
        # check ended. close db.
        mysql_close($con);
        echo "everything ok :)<br>   ";
    }
    
    public function rebuild() {
        $this->clearDB();
        $this->test_echo("Rebuilding accounts manager db:<br/>");
        #connection mysql        
        $this->test_echo("Connecting mysql... ");
        @$con = $this->connect();
        if (!$con) {
            $this->showerror();
        }
        $this->test_echo("mysql connected.<br/>");
        $this->test_echo("Selecting db... ");
        # connecting database
        if (!(mysql_select_db("accounts", $con))) {
            # database missing?!
            $this->showerror();
        } else {
            $this->test_echo("accounts database found.<br/>");
        }        
        
        $this->test_echo("rebuilding '".tbl_accounts."' table...<br/>");                
        mysql_query(tbl_accounts_script,$con) or die(mysql_error());
        
        $this->test_echo("rebuilding '".tbl_emails."' table...<br/>");        
        mysql_query(tbl_emails_script,$con) or die(mysql_error());

        $this->test_echo("adding root...<br/>");
        # add root with two mails: "root", "root@root"
        if ($this->accounts_count()!=0) die("no accounts should've been there!?");
        if ($this->emails_count()!=0) die("no emails should've been there!?");
        $this->add_email($this->signin(admin_login, admin_passwd), admin_email);
        if ($this->accounts_count()!=1) die("1 account should've been there!?");
        if ($this->emails_count()!=2) die("2 emails should've been there!?");
        
        $this->test_echo("checking root... <br/>");
        $this->login(admin_login, admin_passwd);
        
        #testing passwords system
        $this->test_echo("testing passwords system... <br/>");
        $this->update_password(1, "");
        if (!$this->login(admin_login,"")) die('error login');
        $this->update_password(1, "1");
        if (!$this->login(admin_login,"1")) die('error login');
        
        #test random max password
        $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length=max_passwd_length;
        $str = '';
        $count = strlen($charset);
        while ($length--) { $str .= $charset[mt_rand(0, $count-1)]; }
        $this->update_password(1, $str);
        if (!$this->login(admin_login,$str)) die('error login');
        #add one to fail
        $str.="1";
        $this->update_password(1, $str);
        if ($this->login(admin_login,$str)) die('login shouldve failed due to overlengthed password');
        
        $this->update_password(1, admin_passwd);
        if (!$this->login(admin_login,admin_passwd)) die('error login');
        
        #try activation system
        $this->test_echo("testing email activation system... <br/>");
        if ($this->validated_emails_count()!=0) die("error. no emails should've been validated.");
        $this->validate_email(1);
        if (!$this->is_email_validated(1)) die('error. email 1 should be activated.');
        if ($this->is_email_validated(2)) die("error. email 2 shouldn't be activated.");
        if ($this->validated_emails_count()!=1) die("error. 1 emails should've been validated.");
        $this->validate_email(2);
        if (!$this->is_email_validated(2)) die("error. email 2 should be activated.");
        if ($this->validated_emails_count()!=2) die("error. 2 emails should've been validated.");

        #try locking system        
        $this->test_echo("testing locking system... <br/>");
        $this->lock(1);
        if (!$this->isLocked(1)) die("error. root should be locked.");
        $this->unlock(1);        
        if ($this->isLocked(1)) die("error. root shouldn't be locked.");

        #try the delete perm for root only in dev
        #$this->delete_account_perm(1);
        #if ($this->accounts_count()!=0) die("no accounts should've been there!?");
        #if ($this->emails_count()!=0) die("no emails should've been there!?");
        
        # check ended. close db.
        mysql_close($con);
        echo "everything ok :)<br>   ";
    }
    
    public function clearDB() {
        $this->test_echo("WARNING! Erasing ALL DB, include tables!!!.<br/>");        
        #connection mysql        
        $this->test_echo("Connecting mysql... ");
        @$con = $this->connect();
        if (!$con) {
            $this->showerror();
        }
        $this->test_echo("mysql connected.<br/>");
        $this->test_echo("Selecting db... ");
        # connecting database
        if (!(mysql_select_db("accounts", $con))) {
            # database missing?!
            $this->showerror();
        } else {
            $this->test_echo("accounts database found.<br/>");
        }        
        
        $this->test_echo("deleting tables...<br/>");        
        // Execute query
        mysql_query("DROP TABLE ".tbl_accounts,$con);
        mysql_query("DROP TABLE ".tbl_emails,$con);
        
        # check ended. close db.
        mysql_close($con);
        echo "everything deleted.<br>   ";        
    }
    
}
?>