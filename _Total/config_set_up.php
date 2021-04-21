<?php if(!isset($ok_come_from_config_script)) die();
$set_config = array (

  // UP stands for: User Permissions
  // Note: With Total.HTM Easy you control UP only by the classes 'protected' and 'private' 
  // Note: With Total.HTM App you have more detailed user-control
  //
  // The following UY exist:
  
  // * 0) Public User or any kind of agent (Unknown)

  // * 1) Minimal  ~ Registered but not confirmed, or low status
  // * 2) User 		 ~ Regular User (Registered, Confirmed, Trusted)
  // * 3) Worker   ~ Privileged User
  // * 4) Office 	 ~ Near the CORE-team
  // * 5) Boss 		 ~ Owner. Highest status for non-tech.
  // * 6) Admin		 ~ Boss with tech-skills, or technician in contract

  // Note: 'public_read' == 0 and can not be changed
  // Note: 'public_write' <= 'protected_read' <= 'protected_write' <= 'private_read' <= 'private_write'
  // Note: Those users who have UY >= 'protected_write' can do file-managent, publish (MAKE)
  // Note: Users can only be created by users >= 'protected_write'. Maximum-UY: creators own UY

  // ---------
  // Set the UY (user type id) that is allowed

  'public_write'      => 2,           // DEFAULT: 2  MIN: 1

  'protected_read'    => 2,           // DEFAULT: 2  MIN: 1
  'protected_write'   => 4,           // DEFAULT: 4  MIN: 2

  'private_read'      => 5,           // DEFAULT: 5  MIN: 3
  'private_write'     => 5,           // DEFAULT: 5  MIN: 3

);

