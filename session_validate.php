<?php
if(!isset($_SESSION)){
  session_start();
}

class  Session_Validater{

  // set the inital time
  public function __construct(){
    if(!isset($_SESSION['Last_Activity']) || !isset($_SESSION['Last_Regen']) ){
      $_SESSION['Last_Activity'] = time();
      $_SESSION['Last_Regen'] = time();
    }
  }

  public function check(){
    // check if inactive for 60 mins
    if(isset($_SESSION['Last_Activity']) && (time() - $_SESSION['Last_Activity'] >= 3600)){// if last activity is greater than or equal to 60 exit session
      session_unset();     // unset $_SESSION variable for the run-time
      session_destroy();   // destroy session data in storage
      header("Location: ../calendar/index.php");
    }

    // regenerate session id after 10 minutes
    if (isset($_SESSION['Last_Regen']) && (time() - $_SESSION['Last_Regen'] >= 600)){// if last activity is greater than or equal to 10 mins refresh id
      session_regenerate_id(true);// generate a new session id and deactivate the old one to minimize session hijacking
      $_SESSION['Last_Regen'] = time(); // update regen time
    }

    // update activity time stamp
    $_SESSION['Last_Activity'] = time();
  }

}
?>
