<?php
session_start();

// Check that bid is what we want/safe and the user is logged in with the correct permissions
if((isset($_SESSION['cid']) && $_SESSION['cid'] > 0 && $_SESSION['isAdmin'] >= 1)){
  $bid = $_SESSION['cid'];
}else{
  header("Location: newbride-new.php");
}

if(isset($_POST['submit']) && isset($_POST['report'])){
  // the message
  // use wordwrap() if lines are longer than 70 characters
  $msg = wordwrap($_POST['report'],70);

  // send email
  mail("blakecaldwell123@gmail.com","Bug Report",$msg);
  $reportCode = urlencode("Bug Report Sent!");
  header("Location: newbride-new.php?msg=" . $reportCode . "&color=green");
}

?>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
      <div class="row">
        <h1>Report Bugs</h1>
        <h3>If the database doesnt work as it should, please write as detailed as possible what is wrong with it.</h3>
      </div>
      <div class="row" >
        <form style="width: 100%;" action="bugreport.php" method="post">
          <textarea name="report" style="width: 100%; height: 400px;"></textarea>
          <button style="width: 100%; font-size: 25px;" name="submit" type="submit">Submit</button>
        </form>
      </div>
    </div>
  </body>
</html>
