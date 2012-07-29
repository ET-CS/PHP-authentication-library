PHP-authentication-library
==========================

One file, light-weight and easy PHP authentication library


how to use
==========

copy the manager.php and create a config-auth.php from the config-auth-example.php

* user can have multiple emails.
* currently login by email and password

inside your php page:
 include 'manager.php';

function
========
* accManager::connect(); - connect database.
* accManager::disconnect(); - disconnect database.
* accManager::accounts_count(); - returns number of accounts in db.
* accManager::emails_count(); - returns number of emails in db.
* accManager::validated_emails_count(); - returns number of vaildated emails in db.
* accManager::lock($id); - lock account by id
* accManager::unlock($id); - unlock account by id
* accManager::isLocked($id); - check if account is locked
* accManager::validate_email($email_id); - validate an email by id
* accManager::set_default_email($id, $email_id); - set default email to user - NOT IMPLEMENTED YET.
* accManager::add_email($id, $email); - add email to user by user id
* accManager::signin($email, $passwd); - sign in with email/password (new account)
* accManager::login($email, $passwd); - try to login by email/password
* accManager::delete_account_perm ($id); - delete account with all related data
* accManager::update_password($id, $passwd); - update password for user id

function for debugging
======================
* accManager::test($id); - check db and authentication engine with output
* accManager::rebuild(); - rebuild database tables using config-auth.php preferences
* accManager::clearDB(); - clear all data in db!!!
 