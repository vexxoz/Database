<?php
require_once "dbfunctions.php";

session_start();
// get bid from url
// Check that bid is what we want/safe and the user is logged in with the correct permissions
if((isset($_GET['bid']) && $_GET['bid'] != "" && $_GET['bid'] != NULL && is_numeric($_GET['bid'])) && (isset($_SESSION['cid']) && $_SESSION['cid'] > 0 && $_SESSION['isAdmin'] >= 1)){
  // make sure bid is non negative
  $bid = abs($_GET['bid']);
  if($bid==0){
    $isedit = 0;
  }else{
    $isedit = 1;
  }
}else{
  header("Location: newbride-new.php");
}

// if editing get existing info from bride
if($isedit == 1){
  // get assoc array returned from the DB
  $brideInfo = json_decode(listBrideInfo($bid, '0'));
  $brideServices = json_decode(listBrideServices($bid, '0'));
  $bridePayments = json_decode(listPayments($bid, '0'));
  $referredList = json_decode(listReferralSources());
  $plannerList = json_decode(listPlanners());
  $photoList = json_decode(listPhotographers());
  $consList = json_decode(listConsultants(1));
  $brideConsList = json_decode(listDayOfConsultants($bid));

  $contractId = $brideInfo[0]->Contract_ID;
}else{ // adding a new bride
  $contractId = '';
  for($i = 0; $i < 9; $i++) { $contractId .= mt_rand(0, 9); }
}

?>
<html>
  <head>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <!-- Datepicker Files -->
    <link rel="stylesheet" type="text/css" href="css/jquery.datepick.css">
    <script type="text/javascript" src="js/jquery.plugin.min.js"></script>
    <script type="text/javascript" src="js/jquery.datepick.min.js"></script>
    <!-- Timepicker Files -->
    <link rel="stylesheet" type="text/css" href="css/jquery.timepicker.min.css">
    <script type="text/javascript" src="js/jquery.timepicker.min.js"></script>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
    #brideInfo .row div, #inHouseNotes .row div, #serviceInfo .row div{
      margin-top: 15px;
    }
    input{
      width: 100%;
    }
    select{
      font-size: 19px;
      width: 100%;
    }
    #title{
      border-bottom: 2px #f499b8 solid;
    }
    .section{
      margin-top: 25px;
    }
    textarea{
      width: 100%;
      height: 165px;
      font-size: 15px;
    }

    /* Service styling */
    #serviceDate{
      width: 60px;
      font-size: 10px;
    }
    #serviceQuantity{
      font-size: 10px;
      width: 18px;
      text-align: center;
    }
    #serviceName{
      font-size: 12px;
      margin-left: 5px;
    }
    #serviceDiscount{
      width: 50px;
      font-size: 10px;
      text-align: center;
      float: right;
    }
    #deleteService{
      font-size: 10px;
      float: right;
    }

    /* Macro Styling */
    #menuButton{
      width: 100%;
      font-weight: 700;
      font-size: 20px;
    }
    </style>
  </head>
  <body>
    <div class="container">
      <!-- First Row -->
      <form autocomplete="off" method="post" action="addbride.php">
        <!-- Top row -->
        <div class="row">

          <!-- Left Column -->
          <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
            <!-- Brides Info -->
            <div class="col-md-12 col-sm-12 col-xs-12 section" id="brideInfo">
              <!-- Title -->
              <div id="title">
                <h3>Bride Info</h3>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12"><label>First Name</label><br><input required name="firstname" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Client_FirstName;} ?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Last Name</label><br><input required name="lastname" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Client_LastName;} ?>"></div>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Email</label><br><input name="email" type="email" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Client_Email;} ?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Phone</label><br><input name="phone" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Client_Phone;} ?>"></div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12"><label>Bride's Address</label><br><input name="address" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Client_Address;} ?>"></div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12"><label>Pre Address</label><br><input name="preaddress" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Pre_Address;} ?>"></div>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Pre Date</label><br><input id="predate" name="predate" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Pre_DateTime); echo $predateTime[0];}?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Pre Time</label><br><input id="pretime" name="prestarttime" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Pre_DateTime); echo $predateTime[1];}?>"></div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <label>Referred By</label>
                  <br>
                  <select name="referral">
                    <?php
                    $referredHTML = "";
                    foreach ($referredList as $ref) {
                      if(($isedit == 1) && ($ref->ID == $brideInfo[0]->Client_ReferredBy)){
                        $referredHTML = $referredHTML . "<option selected value='".$ref->ID."'>".$ref->Name."</option>";
                      }else{
                        $referredHTML = $referredHTML . "<option value='".$ref->ID."'>".$ref->Name."</option>";
                      }
                    }

                    echo $referredHTML;
                    ?>
                  </select>
                </div>
              </div>

            </div>

            <!-- Brides Services -->
            <div class="col-md-12 col-sm-12 col-xs-12 section" id="services">
              <div id="title">
                <h3>Services</h3>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top:15px;">
                  <label>All Services</label><br>
                  <input value="" style="width: 69%;">
                  <button>Add</button>
                  <button>Remove</button>
                </div>
              </div>

              <!-- List of current services -->
              <div class="row" id="currentListOfServices" style="margin-top:5px;">

                <?php
                $serviceHTML = "";
                $totalCost = 0;
                $paymentsHTML = "";
                $totalPaid = 0;
                if($isedit==0){}else{
                // for each bride service
                foreach ($brideServices as $service) {
                  // get date the service was added
                  $serviceDate = explode(" ", $service->Date_Added);
                  $serviceDate = $serviceDate[0];
                  //get description
                  if($service->Quantity > 1){
                    $serviceDescription = $service->Plural;
                  }else{
                    $serviceDescription = $service->Description;
                  }
                  // add HTML to the variable to display
                  $serviceHTML = $serviceHTML . '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><input disabled id="serviceDate" value="'.$serviceDate.'"><input disabled id="serviceQuantity" value="'.$service->Quantity.'">';

                  // check if service was a removal or not
                  if($service->IsCancelled == 1){
                    //decrease Cost
                    $totalCost = $totalCost - $service->Service_Price;
                    // set color of text to be red
                    $serviceHTML = $serviceHTML . '<label id="serviceName" style="color: red;">'.$serviceDescription.'</label>';
                  }else{
                    // increase cost
                    $totalCost = $totalCost + $service->Service_Price;
                    $serviceHTML = $serviceHTML . '<label id="serviceName">'.$serviceDescription.'</label>';
                  }
                  // add final code to the variable to be displayed
                  $serviceHTML = $serviceHTML . '<input disabled id="serviceDiscount" value="'.$service->Discount_ID.'"></div>';
                }

                // echo the html of the bride's services
                echo $serviceHTML;

                // add tip to the total
                $totalCost = $totalCost*1.15;

                // echo "Total Cost of Services: $" . $totalCost . "<br>";


                ///// PAYMENTS
                foreach ($bridePayments as $payment) {
                  $date = explode(" ", $payment->Date);
                  $date = $date[0];
                  if($payment->isCredit == 1){
                    $paymentsHTML = $paymentsHTML . '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><input disabled id="serviceDate" value="'.$date.'"><label id="serviceName">Credit of: $'.$payment->Amount.'</label></div>';

                  }else{
                    $paymentsHTML = $paymentsHTML . '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><input disabled id="serviceDate" value="'.$date.'"><label id="serviceName">Payment of: $'.$payment->Amount.'</label></div>';
                  }

                  $totalPaid = $totalPaid + $payment->Amount;
                }
              }
                ?>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 12px; border-bottom: 1px solid pink;">
                  <label>Total Cost of Services: $<?php echo ($totalCost); ?></label>
                </div>
                <!-- Echo the list of payments -->
                <div>
                  <?php echo $paymentsHTML; ?>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 12px; border-top: 1px solid pink;">
                  <label>Total Remaining: $
                    <?php if(($totalCost-$totalPaid)<0.01){echo 0;}else{echo ($totalCost-$totalPaid); }
                  ?></label>
                </div>

                <!-- <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem">
                  <input id="serviceDate" value="1/18/2020">
                  <input id="serviceQuantity" value="0">
                  <label id="serviceName">NAME</label>
                  <button id=deleteService onclick="delete(id)">Delete</button>
                  <input id="serviceDiscount" value="0">
                </div> -->
              </div>
            </div>

          </div>

          <!-- Right Column -->
          <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
            <!-- All wedding info and payment/service information -->
            <div class="col-md-12 col-sm-12 col-xs-12 section" id="serviceInfo">
              <div id="title">
                <h3>Wedding Info</h3>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Wedding Date</label><br><input required id="weddingdate" name="weddingdate" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Event_Date); echo $predateTime[0];}?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>ROTD</label><br><input name="readyontheday" value="<?php if($isedit==0){}else{echo $brideInfo[0]->rotd;}?>"></div>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Start Time</label><br><input required id="starttime" name="starttime" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Start_Time); echo $predateTime[1];}?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Done Time</label><br><input required id="donetime" name="donetime" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Done_Time); echo $predateTime[1];}?>"></div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12"><label>Wedding Day Address</label><br><input name="dayofaddress" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Dayof_Address; }?>"></div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <label>Planner</label>
                  <br>
                  <select name="planner">
                    <?php
                    $plannerHTML = "";
                    foreach ($plannerList as $plannerItem) {
                      if(($isedit == 1) && ($plannerItem->ID == $brideInfo[0]->Client_Planner_ID)){
                        $plannerHTML = $plannerHTML . "<option selected value='".$plannerItem->ID."'>".$plannerItem->Name."</option>";
                      }else{
                        $plannerHTML = $plannerHTML . "<option value='".$plannerItem->ID."'>".$plannerItem->Name."</option>";
                      }
                    }

                    echo $plannerHTML;
                    ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <label>Photographer</label>
                  <br>
                  <select name="photographer">
                    <?php
                    $photoHTML = "";
                    foreach ($photoList as $photoItem) {
                      if(($isedit == 1) && ($photoItem->ID == $brideInfo[0]->Client_Photographer_ID)){
                        $photoHTML = $photoHTML . "<option selected value='".$photoItem->ID."'>".$photoItem->Name."</option>";
                      }else{
                        $photoHTML = $photoHTML . "<option value='".$photoItem->ID."'>".$photoItem->Name."</option>";
                      }
                    }

                    echo $photoHTML;
                    ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <label>Lead</label>
                  <br>
                  <select name="lead">
                    <?php
                    $leadHTML = "";
                    foreach ($consList as $consultant) {
                      if(($isedit == 1) && ($consultant->ID == $brideConsList[0]->Consultant_ID)){
                        $leadHTML = $leadHTML . "<option selected value='".$consultant->ID."'>".$consultant->Consultant_Name."</option>";
                      }else{
                        $leadHTML = $leadHTML . "<option value='".$consultant->ID."'>".$consultant->Consultant_Name."</option>";
                      }
                    }

                    echo $leadHTML;
                    ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <label>Assisting</label>
                  <br>
                  <select multiple name="consultantslist[]">
                    <?php
                    $assistingHTML = "";
                    $print = 0;
                    foreach ($consList as $consultant) {
                      for($i = 1; $i < count($brideConsList); $i++){
                        if(($isedit == 1) && ($consultant->ID == $brideConsList[$i]->Consultant_ID)){
                          $print = 1;
                          break;
                        }
                      }
                      if($print == 0){
                        $assistingHTML = $assistingHTML . "<option value='".$consultant->ID."'>".$consultant->Consultant_Name."</option>";
                      }else{
                        $assistingHTML = $assistingHTML . "<option selected value='".$consultant->ID."'>".$consultant->Consultant_Name."</option>";
                      }
                      $print = 0;
                    }

                    echo $assistingHTML;
                    ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12"><label>Driving Fee City</label><br><input name="drivingcity" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Driving_City; }?>"></div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12"><label>Notes On Contract</label><br><input name="drivingnote" value="<?php if($isedit==0){}else{echo $brideInfo[0]->Driving_Note; }?>"></div>
              </div>

            </div>
          </div>

        </div><!--End Row-->

        <!-- Second Row -->
        <div class="row" style="margin-bottom: 30px;">
          <div class="col-md-12 col-sm-12 col-xs-12 section" id="inHouseNotes">
            <!-- Title -->
            <div id="title">
              <h3>In-House Notes</h3>
            </div>
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div><label>Pre-Session Notes</label><br><textarea name="notes"><?php if($isedit==0){}else{echo $brideInfo[0]->Notes;} ?></textarea></div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div><label>Wedding Day Notes</label><br><textarea name="cbd_notes"><?php if($isedit==0){}else{echo $brideInfo[0]->cbd_notes; }?></textarea></div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div><label>Private Notes</label><br><textarea name="pri_notes"><?php if($isedit==0){}else{echo $brideInfo[0]->pri_notes; }?></textarea></div>
              </div>
            </div>
          </div>
        </div><!-- End second row -->
        <input name="fromValue" value="1" type="hidden">
        <input name="brideid" value="<?php echo $bid; ?>" type="hidden">
        <input name="contractid" value="<?php echo $contractId; ?>" type="hidden">
        <button id="menuButtom" type="submit" name="isedit" value="<?php echo $isedit; ?>">Save</button>
        <button id="menuButtom" type="reset">Undo Changes</Button>
      </form><!-- End form -->
      <button id="menuButtom" onclick="goBack()">Cancel</button>
    </div><!-- End container row -->
  </body>
  <script>
    // datepicker for wedding and pre date
    $('#weddingdate').datepick({dateFormat: 'yyyy-mm-dd'});
    $('#predate').datepick({dateFormat: 'yyyy-mm-dd'});
    // Timepicker for pretime, start and done times
    $('#pretime').timepicker({ 'step': 15 });
    $('#starttime').timepicker({ 'step': 15 });
    $('#donetime').timepicker({ 'step': 15 });

    function goBack(){
      window.location.assign("http://office.salonmaison.net/contracts/newbride-new.php");
    }
  </script>
</html>
