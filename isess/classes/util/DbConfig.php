<?php
include_once("config.php");

define("CONTACT_EMAIL","amebo.info@isessolutions.com");
// SYSYTEM DEFINITIONS - CUSTOMIZE PER CLENT DEPLOYMENT
define("ADMINISTRATOR_EMAIL","amebo.support@isessolutions.com");
define("WEBMASTER_EMAIL","amebo.support@isessolutions.com");
define("KEY","ht56~yh85hgftjm737nshd5j&&%(jdggth");
define("SESSION_KEY","y7i4feo8oeK:}{h66hyk85|jh");
define("LOST_PASSWORD_GEN","7hkjsd9092q&^");
define("MAX_RETRY_EMAIL","10");
define("VALIDATE_EMAIL_GEN","hh78493h#~][1sj$676hde80jk¬jj7798jks");

// USER MESSAGES
define("INFO","Information");
define("ERROR","Error!");
define("SUCCESFULL_REGISTRATION", "Your account has been successfully created! You can now login with your specified username (email address) and password");
define("FAILED_REGISTRATION_USER_EXITS", "Error:Registration failed becuase the user already exists!");
define("MAIL_SENT", "Your message has been sent successfully");
define("USER_LOGIN_FAILED", "Your username and/or password were not found on our database, please check and try again.");
define("USER_NOT_CONFIRMED", "You cannot login as your account has not been confirmed by our administrator.");
define("SESSION_CLOSED","SC");
define("FOUND_USER_PASSWORD","Your password has been sent to your registered email address.");
define("FOUND_NO_PASSWORD","Your username was not found on our database.");
define("INCORRECT_PASSWORD","The password you entered is incorrect, please check and try again.");
define("MAIL_NOT_SENT", "Problem occured while trying to send your message.");
// ADMIN DEFINITIONS
define("ADMIN_PASSWORD_RETREIVAL","ISESS Web Content Mangement Tool Administration Services");
define("ADMIN_CONFIRM_EMAIL_MSG","You can now access the administration tool using the following username and password");
define("MAX_LOGIN_ATTEMPTS",5);
// USER ADMIN MESSAGES
define("ADMINISTRATOR_LOGIN_FAILED", "Your username and/or password were not found on our database, please check and try again.");
define("ADMINISTRATOR_NOT_CONFIRMED", "You need to confirm your registration details (check the email sent to you by Adold Engineering) before you can login.");
define("REGISTERED_ADMINISTRATOR", "Your account has been successfully created.\n\nYou will be sent an email shortly confirmaing your login details.");
define("REGISTRATION_FAILED_ADMINISTRATOR_EXISTS","Your username has already been registered, please select another username and try again");
define("DELETED_ADMINISTRATOR_FAILED_NO_PERMISSIONS","Your AccessLevel does not have the permission to delete other administrators.");
define("DELETED_ADMINISTRATOR_FAILED","Unable to delete the administrator");
define("DELETED_ADMINISTRATOR","Administrator has been deleted");
define("REGISTRATION_FAILED","Unable to register the administrator");
define("UPDATED_ADMINISTRATOR","The details have been succesfully updated");
define("UPDATE_ADMINISTRATOR_FAILED","Unable to update the administration details");
define("REGISTRATION_FAILED_INVALID_ADMINISTRATOR","Unable to update the details,the administrator does not exist on our database");
define("RESTORED_DB_WITH_ERROR","Ecountered some errors restoring the database,please check your database cnfiguration");
define("RESTORED_DB","Successfully restored the database");
define("DB_RESTORE_FAIL","Could not restore the database! Please check your database restore script");
define("NO_ADMIN_PERMISSIONS","Error:You do not have the necessary administrator permissions to visit this page!");
//
define("CONFIRM_EMAIL_HEAD",'<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><title></title></head>');
define("CONFIRM_EMAIL_BODY1","<body><p>You must confirm your email address before you can use the Amebo Application. </p>");
define("CONFIRM_EMAIL_BODY2","<body><p>Please click on the following link to confirm your email address;</p>");
define("CONFIRM_EMAIL_WEB_LINK",'<a href="http://amebo.isessolutions.com/confirmemail.php?ID=');
define("CONFIRM_EMAIL_CLOSE",'" target="_blank">ConfirmEmail</a></p></body></html>');
define("EMAIL_CONFIRMATION","Amebo App Email Confirmation");
define("CONFIRM_EMAIL_MSG","You must confirm your email address before you can run the Amebo App.");
/*
// WCMT REQUEST/RESPONSES MESSAGES
define("ADMINISTRATOR_LOGIN_REQ","LIReq");
define("GET_NEW_PASSWORD_REQ","NPWReq");
define("STARTUP_REQ","SUReq");
define("STARTUP_GALLERY_REQ","SUGReq");
define("UPDATE_MENU_REQ","UMReq");
define("ADMIN_LOGIN_REQ","ALIReq");
define("ADMIN_PAGE_REQ","APReq");
define("GET_ALL_PAGES_REQ","GAPReq");
define("UPDATE_ITEM_REQ","UIReq");
define("DELETE_ITEM_REQ","DIReq");
define("CREATE_ALBUM_REQ","CAReq");
define("RENAME_ALBUM_REQ","RAReq");
define("DELETE_ALBUM_REQ","DAReq");
define("ADD_MEDIA_TO_ALBUM_REQ","AMAReq");
define("REGISTER_ADMINISTRATOR_REQ","RAAReq");
define("DELETE_ADMINISTRATOR_REQ","DAAReq");
define("GET_ADMINISTRATOR_REQ","GAReq");
define("BACKUP_DATABASE_REQ","BDReq");
define("RESTORE_DATABASE_REQ","RDReq");
define("ADMIN_PAGE_LIST_REQ","GPLReq");
define("SERIALISE_FIELDS_REQ","STDReq");
define("ALBUM_SEARCH_REQ","ASReq");
// Responses
define("ADMINISTRATOR_LOGIN_RESP","LIResp");
define("GET_NEW_PASSWORD_RESP","NPWResp");
define("STARTUP_RESP","SUResp");
define("CREATE_ALBUM_RESP","CAResp");
define("RENAME_ALBUM_RESP","RAResp");
define("DELETE_ALBUM_RESP","DAResp");
define("UPDATE_MENU_RESP","UMResp");
define("ADD_MEDIA_RESP","AMAResp");
define("GET_ALL_PAGES_RESP","GAPResp");
define("ADMIN_LOGIN_RESP","ALIResp");
define("ADMIN_PAGE_RESP","APResp");
define("REGISTER_ADMINISTRATOR_RESP","RAAResp");
define("DELETE_ADMINISTRATOR_RESP","DAAResp");
define("GET_ADMINISTRATOR_RESP","GAResp");
define("STARTUP_GALLERY_RESP","SUGResp");
define("BACKUP_DATABASE_RESP","BDResp");
define("CONFIRMATION_BOX_RESP","CBResp");
define("ADMIN_PAGE_LIST_RESP","GPLResp");
define("SERIALISE_FIELDS_RESP","STDResp");
define("ALBUM_SEARCH_RESP","ASResp");
*/
?>