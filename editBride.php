'<?php
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
  $reportCode = urlencode("No Bride Selected!");
  header("Location: newbride-new.php?msg=" . $reportCode . "&color=red");
}

// Get general information
$referredList = json_decode(listReferralSources());
$plannerList = json_decode(listPlanners());
$photoList = json_decode(listPhotographers());
$consList = json_decode(listConsultants(1));
$servicesList = json_decode(listServices());
$discountList = json_decode(listDiscounts());

// if editing get existing info from bride
if($isedit == 1){
  // get brides personal info if editing
  $brideInfo = json_decode(listBrideInfo($bid, '0'));
  $brideServices = json_decode(listBrideServices($bid, '0'));
  $bridePayments = json_decode(listPayments($bid, '0'));
  $brideConsList = json_decode(listDayOfConsultants($bid));

  // get contract id
  $contractId = $brideInfo[0]->Contract_ID;

  // get contract date
  $contractdate = $brideInfo[0]->Contract_Date;
}else{ // adding a new bride
  // create a 9 digit number for a contract id
  $contractId = '';
  for($i = 0; $i < 9; $i++) { $contractId .= mt_rand(0, 9); }

  // get the date for the contract date
  $contractdate = date('Y-m-d');
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

    <meta name="viewport" content="width=device-width, initial-scale=1">

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

    #inputQty{
      padding: 0;
    }
    /* Service styling */
    .hidden{
      display: none;
    }
    #serviceDate{
      width: 60px;
      font-size: 10px;
    }
    #gratuity{
      width: 60px;
      font-size: 10px;
    }
    #serviceQuantity{
      font-size: 12px;
      width: 19px;
      text-align: center;
      background: none;
      border: none;
    }
    #serviceName{
      font-size: 12px;
      margin-left: 5px;
    }
    #removedServiceName{
      font-size: 12px;
      margin-left: 5px;
      color: red;
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
      width: auto;
    }

    /* payment styling */
    .paymentInput{
      max-width: 25%;
      min-width: 71px;
      margin-top: 15px;
      margin-bottom: 15px;
    }
    #paymentValue{
      max-width: 50px;
      border: none;
      background: none;
    }

    /* Macro Styling */
    #menuButton{
      width: 100%;
      font-weight: 700;
      font-size: 20px;
    }
    #checkbox{
      width: auto;
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
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Pre Date (yyyy-mm-dd)</label><br><input id="predate" name="predate" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Pre_DateTime); echo $predateTime[0];}?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Pre Time</label><br><input id="pretime" name="prestarttime" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Pre_DateTime); echo date('h:ia', strtotime($predateTime[1]));}?>"></div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <label>Referred By</label>
                  <br>
                  <select name="referral">
                    <?php
                    $referredHTML = "";
                    foreach ($referredList as $ref) {
                      if( (($isedit == 1) && ($ref->ID == $brideInfo[0]->Client_ReferredBy)) || (($isedit == 0) && ($ref->ID == 1)) ){
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
                  <label>All Services (yyyy-mm-dd)</label><br>
                  <input class="col-md-1 col-sm-12 col-xs-12 col-lg-1" type="number" id="inputQty" value="1">
                  <select class="col-md-6 col-sm-12 col-xs-12 col-lg-6" id="serviceList">
                    <?php
                    $servicesListHTML = "";
                    foreach($servicesList as $serviceItem){
                      $servicesListHTML = $servicesListHTML . "<option selected value='".$serviceItem->ID."' price='".$serviceItem->Price."'>".$serviceItem->Description."</option>";
                    }
                    echo $servicesListHTML;
                    ?>
                  </select>
                  <input class="col-md-2 col-sm-12 col-xs-12 col-lg-2" onclick="addService(false)" type="button" value="Add">
                  <input class="col-md-2 col-sm-12 col-xs-12 col-lg-2" onclick="addService(true)" id="removeService" type="button" value="Remove">
                </div>
              </div>

              <!-- List of current services -->
              <div class="row" id="currentListOfServices" style="margin-top:5px;">

                <?php
                $serviceHTML = "";
                $totalCost = 0;
                $paymentsHTML = "";
                $totalPaid = 0;
                $countId = 1;
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

                  // get discount descriptions
                  foreach($discountList as $discountItem){ // for each discountId check if it matches the one on the service
                    if($discountItem->ID == $service->Discount_ID){ // if so then get the description
                      $discountId = $discountItem->Description;
                    }
                  }

                  $tip = "";
                  $servicePrice = 0;
                  if($service->noGratuity == 1){
                    $servicePrice = $service->Service_Price*1.15;
                    $tip = '<option value="on">No tip</option><option value="off" selected>Tip</option>';
                  }else{
                    $servicePrice = $service->Service_Price;
                    $tip = '<option value="on" selected >No tip</option><option value="off">Tip</option>';
                  }

                  // add HTML to the variable to display
                  $serviceHTML = $serviceHTML . '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><select disable id="gratuity" name="nogratuity'.$countId.'">'.$tip.'</select><input name="svc'.$countId.'" value="'.$service->Service_ID.'" class="hidden"><input disabled name="dateadded'.$countId.'" id="serviceDate" value="'.$serviceDate.'"><input disabled name="qty'.$countId.'" id="serviceQuantity" value="'.$service->Quantity.'">';

                  // check if service was a removal or not
                  if($service->IsCancelled == 1){
                    //decrease Cost
                    $totalCost = $totalCost - $servicePrice;
                    // set color of text to be red
                    $serviceHTML = $serviceHTML . '<label id="removedServiceName">'.$serviceDescription.'</label>';
                  }else{
                    // increase cost
                    $totalCost = $totalCost + $servicePrice;
                    $serviceHTML = $serviceHTML . '<label id="serviceName">'.$serviceDescription.'</label>';
                  }
                  // add final code to the variable to be displayed
                  $serviceHTML = $serviceHTML . '<input disabled name="disc'.$countId.'" id="serviceDiscount" value="'.$discountId.'"></div>';

                  // move id up 1
                  $countId++;
                }

                // echo the html of the bride's services
                echo $serviceHTML;


                ///// PAYMENTS
                foreach ($bridePayments as $payment) {
                  $date = explode(" ", $payment->Date);
                  $date = $date[0];
                  if($payment->isCredit == 1){
                    $paymentsHTML = $paymentsHTML . '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><input disabled id="serviceDate" value="'.$date.'"><label id="serviceName">Credit of: $'.$payment->Amount.'</label></div>';
                    $totalPaid = $totalPaid - $payment->Amount;

                  }else{
                    $paymentsHTML = $paymentsHTML . '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><input disabled id="serviceDate" value="'.$date.'"><label id="serviceName">Payment of: $'.$payment->Amount.'</label></div>';
                    $totalPaid = $totalPaid + $payment->Amount;
                  }


                }
              }
                ?>


                <!-- <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem">
                  <input id="serviceDate" value="1/18/2020">
                  <input id="serviceQuantity" value="0">
                  <label id="serviceName">NAME</label>
                  <button id=deleteService onclick="delete(id)">Delete</button>
                  <input id="serviceDiscount" value="0">
                </div> -->
              </div>
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 12px; border-bottom: 1px solid pink;">
                <label>Total Cost of Services: $<span id="totalCost"><?php echo ($totalCost); ?></span></label>

              </div>
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <input class="paymentInput" step="0.01" id="paymentAmount" type="number">
                <input class="paymentInput" style="padding: initial;" type="button" onclick="addPayment(false)" value="Payment">
                <input class="paymentInput" type="button" onclick="addPayment(true)" value="Credit">
              </div>
              <!-- Echo the list of payments -->
              <div id="paymentList" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <label>
                  Payments:
                </label>
                <?php echo $paymentsHTML; ?>
              </div>
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 12px; border-top: 1px solid pink;">
                <label>Total Remaining: $<span id="totalRemaining">
                  <?php echo $totalCost-$totalPaid;
                ?></span></label>
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
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Wedding Date (yyyy-mm-dd)</label><br><input required id="weddingdate" name="weddingdate" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Event_Date); echo $predateTime[0];}?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>ROTD</label><br><input name="readyontheday" value="<?php if($isedit==0){}else{echo $brideInfo[0]->rotd;}?>"></div>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Start Time</label><br><input required id="starttime" name="starttime" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Start_Time); echo date('h:ia', strtotime($predateTime[1]));}?>"></div>
                <div class="col-md-6 col-sm-12 col-xs-12"><label>Done Time</label><br><input required id="donetime" name="donetime" value="<?php if($isedit==0){}else{$predateTime = explode(" ", $brideInfo[0]->Done_Time); echo date('h:ia', strtotime($predateTime[1]));}?>"></div>
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
                      if((($isedit == 1) && ($plannerItem->ID == $brideInfo[0]->Client_Planner_ID)) || (($isedit == 0) && ($plannerItem->ID == 1)) ){
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
                      if( (($isedit == 1) && ($photoItem->ID == $brideInfo[0]->Client_Photographer_ID)) || (($isedit == 0) && ($photoItem->ID == 1)) ){
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

              <div class="row">
                <div class="col-md-4 col-sm-4 col-xs-4"><input id="checkbox" <?php if($isedit==0){}else{if($brideInfo[0]->caBride == 1){echo "checked";}} ?> type="checkbox" name="caBride" value="1"><label>CA Bride</label></div>
                <div class="col-md-4 col-sm-4 col-xs-4"><input id="checkbox" <?php if($isedit==0){}else{if($brideInfo[0]->specialEvent == 1){echo "checked";}} ?> type="checkbox" name="specialEvent" value="1"><label>Special Event</label></div>
                <div class="col-md-4 col-sm-4 col-xs-4"><input id="checkbox" <?php if($isedit==0){}else{if($brideInfo[0]->totalCancel == 1){echo "checked";}} ?> type="checkbox" name="totalCancel" value="1"><label>Booking Cancelled</label></div>
                <div class="col-md-4 col-sm-4 col-xs-4"><input id="checkbox" <?php if($isedit==0){}else{if($brideInfo[0]->IsEmailed == 1){echo "checked";}} ?> type="checkbox" name="ce" value="1"><label>Contract Emailed</label></div>
                <div class="col-md-4 col-sm-4 col-xs-4"><input id="checkbox" <?php if($isedit==0){}else{if($brideInfo[0]->IsContractSigned == 1){echo "checked";}} ?> type="checkbox" name="cs" value="1"><label>Contract Signed</label></div>
                <div class="col-md-4 col-sm-4 col-xs-4"><input id="checkbox" <?php if($isedit==0){}else{if($brideInfo[0]->hadPresession == 1){echo "checked";}} ?> type="checkbox" name="pd" value="1"><label>Pre-Session Done</label></div>
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
        <input id="countId" name="countId" value="0" type="hidden">
        <input name="fromValue" value="1" type="hidden">
        <input name="contractdate" value="<?php echo $contractdate; ?>" type="hidden">
        <input name="brideid" value="<?php echo $bid; ?>" type="hidden">
        <input name="contractid" value="<?php echo $contractId; ?>" type="hidden">
        <button id="menuButtom" type="submit" name="isedit" value="<?php echo $isedit; ?>">Save</button>
        <button id="menuButtom" type="reset">Undo Changes</Button>
      </form><!-- End form -->
      <button id="menuButtom" onclick="goBack()" style="margin-bottom: 35px;">Cancel</button>
    </div><!-- End container row -->
  </body>
  <script>
    // all global variables needed for adding services
    var countId = <?php echo $countId;  ?>; // plus 1 so there is no 0
    var today = new Date();
    var currentDate = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
    var discountList = <?php echo json_encode($discountList); ?>;

    function goBack(){
      window.location.assign("http://office.salonmaison.net/contracts/newbride-new.php");
    }

    function createDiscountDropdown(){ // funtion to create a dropdown for the discounts
      code = "<select name='new_disc"+countId+"' onchange='updateDiscount(this)' id='serviceDiscount'>";
      for (var i = 0; i < discountList.length; i++){
        code = code + "<option value='"+discountList[i].ID+"' isPercent='"+discountList[i].isPercentage+"' Discount='"+discountList[i].Discount+"'>"+discountList[i].Description+"</option>";
      }
      code = code + "</select>";
      return code;
    }

    function addPayment(isCredit){
      var amount = parseFloat(document.getElementById("paymentAmount").value);
        if (!isNaN(amount) && amount > 0){

        $paymentsHTML = "";

        if(isCredit == true){
          $paymentsHTML = $paymentsHTML + '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><input readonly class="hidden" name="newcr_pmtforpre'+countId+'"><input readonly id="serviceDate" name="newcr_pmtdate'+countId+'" value="'+currentDate+'"><label id="serviceName"><input disabled value="1" class="hidden">Credit of: $<input readonly name="newcr_pmtv'+countId+'" id="paymentValue" value="'+amount+'"></label><input id="deleteService" type="button" onclick="deletePayment(this)" value="Delete"></div>';
          // negate amount since its refund
          amount = amount*-1;
        }else{
          $paymentsHTML = $paymentsHTML + '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" id="serviceItem"><input readonly class="hidden" name="newpm_pmtforpre'+countId+'"><input readonly id="serviceDate" name="newpm_pmtdate'+countId+'" value="'+currentDate+'"><label id="serviceName"><input disabled value="0" class="hidden">Payment of: $<input readonly name="newpm_pmtv'+countId+'" id="paymentValue" value="'+amount+'"></label><input id="deleteService" type="button" onclick="deletePayment(this)" value="Delete"></div>';
        }

        // add the new payment item to the list of payments
        document.getElementById("paymentList").insertAdjacentHTML('beforeend', $paymentsHTML); // using insertAdjacentHTML keeps discounts on other services from glitching
        // subtract the amount of the payment from the total remaining
        document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) - amount;// update the total remaining

        // Increment counter
        countId = countId + 1;
      }else{
        alert("Please type in a value greater than 0!");
      }
    }

    function deletePayment(element){
      var paymentAmount = parseFloat(element.parentElement.children[2].children[1].value);
      console.log("paymentAmount=" + paymentAmount);
      var isCredit = parseFloat(element.parentElement.children[2].children[0].value);
      console.log("isCredit=" + isCredit);

      if(isCredit == 1){
        // negate amount since refund
        paymentAmount = paymentAmount*-1;
      }
      paymentAmount = Math.ceil(paymentAmount * 100) / 100; // round decimal up to nearest penny

      document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + paymentAmount;// update the total remaining

      element.parentElement.remove();
      // decrement counter
      countId = countId - 1;
    }

    function updateDiscount(element){
      var initialPrice = parseFloat(element.parentElement.children[0].value); //get intial price of all the services
      // console.log("intialprice:" + initialPrice);
      var currentPrice = parseFloat(element.parentElement.children[1].value); // get the curren price with discounts
      // console.log("currentPrice:" + currentPrice);
      var quantity = parseFloat(element.parentElement.children[5].value); // get quantity of service
      // console.log("quantity:" + quantity);
      var isPercent = parseFloat(element.options[element.selectedIndex].getAttribute("isPercent")); // get boolean percent
      // console.log("isPercent:" + isPercent);
      var discount = parseFloat(element.options[element.selectedIndex].getAttribute("Discount"));  // get
      // console.log("discount:" + discount);

      var totalPrice = parseFloat(document.getElementById("totalCost").innerHTML);
      var totalRemaining = parseFloat(document.getElementById("totalRemaining").innerHTML);

      // negate the effect of the current price by * -1 so get price without service included
      document.getElementById("totalCost").innerHTML = parseFloat(document.getElementById("totalCost").innerHTML) + (currentPrice*-1);// update the total cost
      document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + (currentPrice*-1);// update the total remaining

      if(isPercent == 1){ // discount is percent
        currentPrice = initialPrice*(1-(discount/100));// dont need to get tip or quantity since the total includes tip + price of servide + quantities
      }else{ // discount is $ amount
        if(currentPrice < 0){// if price is negative (removed service)
          currentPrice = initialPrice + (discount*1.15*quantity); // negative with discount = less negative
        }else{
          currentPrice = initialPrice - (discount*1.15*quantity); // positive with discount = more negative
        }
      }
      currentPrice = Math.ceil(currentPrice * 100) / 100; // round decimal up to nearest penny
      // update the new currentPrice
      element.parentElement.children[1].value = currentPrice;

      // add the new price of the service to the total
      document.getElementById("totalCost").innerHTML = parseFloat(document.getElementById("totalCost").innerHTML) + currentPrice;// update the total cost
      document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + currentPrice;// update the total remaining
    }

    function addService(remove){ // add a service item to the list of services
      var quantity = document.getElementById("inputQty").value; // get the value of how many of service to add
      if(quantity < 100 && quantity > 0){

        // get info needed for the service item
        var serviceId = document.getElementById("serviceList").value; // get the service id value
        var serviceDropdown = document.getElementById("serviceList"); // select the dropdown element
        var serviceDesc = (serviceDropdown.options[serviceDropdown.selectedIndex].text); // get the description of the service

        // update cost of servces
        var servicePrice = parseFloat(serviceDropdown.options[serviceDropdown.selectedIndex].getAttribute("price")) * quantity; // get the price of the service and multiple it by the quantity
        servicePrice = servicePrice * 1.15// add the mandatory tip to the cost
        servicePrice = Math.ceil(servicePrice * 100) / 100; // round decimal up to nearest penny
        if(remove === true){ // if removed service
          servicePrice = servicePrice * -1; // set the price to negative so when it is added to the total prices it subtracts
        }

        if(remove === true){ // remove service
          // create the new removed service item
          newCode = "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12' id='serviceItem'><input disabled class='hidden' id='initialPrice' value='"+servicePrice+"'><input disabled class='hidden' id='price' value='"+servicePrice+"'><select id='gratuity' name='del_nogratuity"+countId+"' onchange='updateTip(this)' value='off'><option value='on'>No tip</option><option value='off' selected>Tip</option></select><input name='del_svc"+countId+"' value='"+serviceId+"' class='hidden'><input readonly name='del_dateadded"+countId+"' id='serviceDate' value='"+currentDate+"'><input readonly name='del_qty"+countId+"' id='serviceQuantity' value='"+quantity+"'><label id='removedServiceName'>"+serviceDesc+"</label>"+createDiscountDropdown()+"<input id='deleteService' type='button' onclick='deleteElement(this)' value='Delete'></div>";
        }else{ // add service
          // create the new service item
          newCode = "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12' id='serviceItem'><input disabled class='hidden' id='initialPrice' value='"+servicePrice+"'><input disabled class='hidden' id='price' value='"+servicePrice+"'><select id='gratuity' name='new_nogratuity"+countId+"' onchange='updateTip(this)' value='off'><option value='on'>No tip</option><option value='off' selected>Tip</option></select><input name='new_svc"+countId+"' value='"+serviceId+"' class='hidden'><input readonly name='new_dateadded"+countId+"' id='serviceDate' value='"+currentDate+"'><input readonly name='new_qty"+countId+"' id='serviceQuantity' value='"+quantity+"'><label id='serviceName'>"+serviceDesc+"</label>"+createDiscountDropdown()+"<input id='deleteService' type='button' onclick='deleteElement(this)' value='Delete'></div>";
        }//<input name='new_disc"+countId+"' id='serviceDiscount' value='1'>

        // add the new service item to the list of services
        document.getElementById("currentListOfServices").insertAdjacentHTML('beforeend', newCode); // using insertAdjacentHTML keeps discounts on other services from glitching

        document.getElementById("totalCost").innerHTML = parseFloat(document.getElementById("totalCost").innerHTML) + servicePrice;// update the total cost
        document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + servicePrice;// update the total remaining
        // Increment counter
        countId = countId + 1;
      }else{
        alert("Invalid Quantity! Must be within 1 to 99");
      }
    }

    function updateTip(element){
      var currentPrice = parseFloat(element.parentElement.children[1].value); // get the curren price with discounts

      var noGratuity = element.value;


      // negate the effect of the current price by * -1 so get price without service included
      document.getElementById("totalCost").innerHTML = parseFloat(document.getElementById("totalCost").innerHTML) + (currentPrice*-1);// update the total cost
      document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + (currentPrice*-1);// update the total remaining

      var newPrice = 0;

      if(noGratuity == "on"){
        newPrice = parseInt(currentPrice /1.15);
      }else{
        newPrice = (currentPrice *1.15);
      }

      newPrice = Math.ceil(newPrice * 100) / 100; // round decimal up to nearest penny
      // update the new currentPrice
      element.parentElement.children[1].value = newPrice;

      document.getElementById("totalCost").innerHTML = parseFloat(document.getElementById("totalCost").innerHTML) + newPrice;// update the total cost
      document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + newPrice;// update the total remaining
    }


    function deleteElement(element){
      var servicePrice = parseFloat(element.parentElement.children[1].value); // get the price of the service thats being removed
      servicePrice = servicePrice * -1; // get the oposite of the value to remove its effect on the totals

      document.getElementById("totalCost").innerHTML = parseFloat(document.getElementById("totalCost").innerHTML) + servicePrice;// update the total cost
      document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + servicePrice;// update the total remaining

      element.parentElement.remove();
      // decrement counter
      countId = countId - 1;
    }

    // datepicker for wedding and pre date
    $('#weddingdate').datepick({dateFormat: 'yyyy-mm-dd'});
    $('#predate').datepick({dateFormat: 'yyyy-mm-dd'});

    // Timepicker for pretime, start and done times
    $('#pretime').timepicker({ 'step': 15 });
    $('#starttime').timepicker({ 'step': 15 });
    $('#donetime').timepicker({ 'step': 15 });
  </script>
</html>
