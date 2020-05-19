<?php
session_start();
if ((isset($_SESSION['cid']) && $_SESSION['cid'] > 0 && $_SESSION['isAdmin'] >= 1)) {//check if logged in
	// check that session is valid
	require_once "session_validate.php";
	$session_validate = new Session_Validater();
	$session_validate->check();
	// connect to database to get info
	// ini_set('include_path','/var/www/html/contracts/lib/');
	// include("auth2.php");
	// $log = new logmein();     //Instentiate the class
	// $mainConnection = $log->dbconnect();
	require_once "dbfunctions.php";
	$mainConnection = dbConnect();

if (isset($_POST['type']) && $_POST['type'] == 'search' && isset($_POST['name'])) {



		// get post variables
		$search = $_POST['name'];
		$year = $_POST['year'];

		// save the search so if a user clicks on edit they can go back to their previous search
		$_SESSION['lastSearch'] = $search;
		$_SESSION['lastYear'] = $year;

		// make sure request is not empty
		if($search != "" || $search != " " || $search != null){

			// escape input and define variables
			$search = $mainConnection->escape_string($search);
			$year = $mainConnection->escape_string($year);

			$list = "";
			$isString = 0;

			// if is date with
			if(($timestamp = strtotime($search)) === false){
				$isString = 1;
			}else{
				$timestamp =  date('m-d', $timestamp);
				if($year == "ALL"){
					$year = "%";
				}else {
					$year = $year . "-";
				}
				$sql = "SELECT clients.Client_FirstName, clients.Client_LastName, clients.ID, clients.Event_Date, clients.Start_Time, clients.Done_Time, clients.Dayof_Address, clients.Client_Photographer_ID, clients.Client_Planner_ID FROM clients WHERE Event_Date LIKE CONCAT('$year', '$timestamp', '%')";
			}

			// if is not a date
			if($isString === 1){
				// changing sql statements dependent on the space input
				if(stripos($search, " ") > -1){
					// gets exactly first name and part of last name
					$search = explode(" ", $search);
					// gets first name exact and any last name starting with whats input
						$sql = "SELECT clients.Client_FirstName, clients.Client_LastName, clients.ID, clients.Event_Date, clients.Start_Time, clients.Done_Time, clients.Dayof_Address, clients.Client_Photographer_ID, clients.Client_Planner_ID FROM clients WHERE (Client_FirstName='$search[0]' AND Client_LastName LIKE CONCAT('$search[1]', '%'))";
				}else{

						$sql = "SELECT clients.Client_FirstName, clients.Client_LastName, clients.ID, clients.Event_Date, clients.Start_Time, clients.Done_Time, clients.Dayof_Address, clients.Client_Photographer_ID, clients.Client_Planner_ID FROM clients WHERE (Client_FirstName LIKE CONCAT('%', '$search', '%') OR Client_LastName LIKE CONCAT('%', '$search', '%'))";
				}

				// if year is specified
				if($year != "ALL"){
					$sql .= " AND YEAR(Event_Date)='$year'";
				}

			}
			// run the sql statement
			$result = $mainConnection->query($sql) or die("cannot get info");

			// get the results
			while($row = $result->fetch_assoc()) {
				$bid = $row["ID"];
				$firstName = $row["Client_FirstName"];
				$lastName = $row["Client_LastName"];

				$startTimeGet = $row["Start_Time"];
				$d = strtotime($startTimeGet);
				$startTime = date("h:i:sa", $d);

				$doneTimeGet = $row["Done_Time"];
				$d = strtotime($doneTimeGet);
				$doneTime = date("h:i:sa", $d);

				$eventDateGet = $row["Event_Date"];
				$d = strtotime($eventDateGet);
				$eventDate = date("m/d/Y", $d);

				$dayof = $row['Dayof_Address'];

				$photographer = $row['Client_Photographer_ID'];
				$planner = $row['Client_Planner_ID'];


				//get planner name
				$planner_sql = "SELECT planners.Name FROM planners WHERE ID='$planner'";
				$planner_sql_return = $mainConnection->query($planner_sql) or die("cannot get info on planners.");
				while($planner_row = $planner_sql_return->fetch_assoc()){
					$planner_name = $planner_row['Name'];
				}

				// get photo name
				$photo_sql = "SELECT planners.Name FROM planners WHERE ID='$planner'";
				$photo_sql_return = $mainConnection->query($photo_sql) or die("cannot get info on photo.");
				while($photo_row = $photo_sql_return->fetch_assoc()){
					$photo_name = $photo_row['Name'];
				}

				// get total cost of services
				$totalCost = 0;
				$total_cost_sql = "SELECT services.Service_Price, services.IsCancelled, services.noGratuity FROM services WHERE Client_ID='$bid'";
				$total_cost_sql_return = $mainConnection->query($total_cost_sql) or die("cannot get info on total cost.");
				while($total_cost_row = $total_cost_sql_return->fetch_assoc()){
					if($total_cost_row['IsCancelled'] == 1){ // removed service
						if($total_cost_row['noGratuity'] == 1){//tip
							$totalCost -= round($total_cost_row['Service_Price']*1.15, 2);  // rounds to nearest cent
						}else{//no tip
							$totalCost -= $total_cost_row['Service_Price'];
						}
					}else{ // normal service
						if($total_cost_row['noGratuity'] == 1){ //tip
							$totalCost += round($total_cost_row['Service_Price']*1.15, 2);  // rounds to nearest cent
						}else{ //no tip
							$totalCost += $total_cost_row['Service_Price'];
						}
					}
				}

				// get total of payments
				$totalPaid = 0;
				$total_paid_sql = "SELECT payments.Amount, payments.isCredit FROM payments WHERE Client_ID='$bid'";
				$total_paid_sql_return = $mainConnection->query($total_paid_sql) or die("cannot get info on total paid.");
				while($total_paid_row = $total_paid_sql_return->fetch_assoc()){
					if($total_paid_row['isCredit'] == 1){// if credit
						$totalPaid -= $total_paid_row['Amount'];
					}else{
						$totalPaid += $total_paid_row['Amount'];
					}
				}

				$totalOwe = $totalCost-$totalPaid;

				// get lead
				$leadId = 0;
				$leadName = "No Lead";
				$brideConsList = json_decode(listDayOfConsultants($bid));
				for($i = 0; $i < count($brideConsList); $i++){
					if($brideConsList[$i]->IsLead == '1'){
						$leadId = $brideConsList[$i]->Consultant_ID;
						break;
					}
				}
				if($leadId != 0){
					$consList = json_decode(listConsultants(1));
					foreach ($consList as $consultant) {
						if($consultant->ID == $leadId){
							$leadName = $consultant->Consultant_Name;
						}
					}
				}

				// add the styled clients into variable to respond
				$list = "<div class='card col-xs-12 col-sm-12 col-md-4 col-lg-4' id='card'><h3 id='cardName'>". $firstName . " " . $lastName ."</h3><span class='cardText' id='cardDate'>" . $eventDate . "</span><span class='cardText' id='cardTime'>" . $startTime . "-" . $doneTime . "</span><span class='cardText' id='cardPlace'>".$dayof."</span><span class='cardText' id='cardOwe'><b>Payment: </b>$". $totalPaid ."/$". $totalCost ."(Owing: $". $totalOwe .")</span><span class='cardText' id='cardPhoto'><b>Photographer: </b>".$photo_name."</span><span class='cardText' id='cardLead'><b>Lead: </b>".$leadName."</span><span class='cardText' id='cardPlanner'><b>Planner: </b>".$planner_name."</span><button id='edit' onclick='edit(".$bid.")'>Edit</button><button id='pdf' onclick='pdf(".$bid.")'>Create PDF</button><button id='pdf' onclick='oldpdf(".$bid.")'>Create PDF W/ Old Terms</button></div>" . $list;

				// $list .= "<li id='entry'><button id='edit' onclick='edit(".$bid.")'>Edit</button><span id='text'>".$firstName . " " . $lastName . " Event on: " . $eventDate . " " . $startTime . "-" . $doneTime . "</span></li>";//" . $eventDate . " " . $startTme . " " . $doneTime . " " . $photographer . " " . $dayof . "
			}
			// if no results
			if($list == "" || $list == null || $list == " "){
				if($isString == 1){
					echo "No brides found by that name";
				}else{
					echo "No Brides with that date have been found!";
				}
			}else{
				echo $list;
			}
		}else{
			echo "No name given please type a name and hit enter for results";
		}
	}else{
		echo "No name given please type a name and hit enter for results";
	}
}else{
	echo "You do not have access to view this page";
	header("Location: ../calendar/index.php");
}

?>
