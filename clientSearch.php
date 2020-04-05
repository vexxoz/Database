<?php
session_start();
if ((isset($_SESSION['cid']) && $_SESSION['cid'] > 0 && $_SESSION['isAdmin'] >= 1)) {
	// connect to database to get info
	// ini_set('include_path','/var/www/html/contracts/lib/');
	// include("auth2.php");
	// $log = new logmein();     //Instentiate the class
	// $mainConnection = $log->dbconnect();
	require_once "dbfunctions.php";
	$mainConnection = dbConnect();

	// make sure the type is set and is set to edit and there is a valid id being send
	if(isset($_POST['type']) && $_POST['type'] == 'edit' && isset($_POST['bid'])){
		//get edit info

		// get post variables
		$bid = $_POST['bid'];

		// escape input and redifine variables
		$bid = $mainConnection->escape_string($bid);
		$list = "";

		// get bride info from bid
		$sql = "SELECT pri_notes FROM clients WHERE ID='$bid'";
		$result = $mainConnection->query($sql) or die("cannot get info");
		while($row = $result->fetch_assoc()){
			$list .= $row['pri_notes'];
		}

		if($list == "" || $list == null){
			$list = "test";
		}
		echo $list;

	// check that type is search and a name is given
	}elseif (isset($_POST['type']) && $_POST['type'] == 'search' && isset($_POST['name']) && $_POST['name'] != "" && $_POST['name'] != null) {
		// get search feedback


		// get post variables
		$search = $_POST['name'];
		$year = $_POST['year'];

		// make sure request is not empty
		if($search != "" || $search != " " || $search != null){

			// escape input and define variables
			$search = $mainConnection->escape_string($search);
			$year = $mainConnection->escape_string($year);
			$list = "";

			// changing sql statements dependent on the typed input
			if(stripos($search, " ") > -1){
				// gets exactly first name and part of last name
				$search = explode(" ", $search);
				// gets any part of last or first name but not both at same time
				// checks if a year is specified
				if($year == "ALL"){
					$sql = "SELECT clients.Client_FirstName, clients.Client_LastName, clients.ID, clients.Event_Date, clients.Start_Time, clients.Done_Time, clients.Dayof_Address, clients.Client_Photographer_ID, clients.Client_Planner_ID FROM clients WHERE Client_LastName LIKE CONCAT('%', '$search[1]', '%') AND Client_FirstName='$search[0]'";
				}else{
					$sql = "SELECT clients.Client_FirstName, clients.Client_LastName, clients.ID, clients.Event_Date, clients.Start_Time, clients.Done_Time, clients.Dayof_Address, clients.Client_Photographer_ID, clients.Client_Planner_ID FROM clients WHERE Client_LastName LIKE CONCAT('%', '$search[1]', '%') AND Client_FirstName='$search[0]' AND Event_Date LIKE CONCAT('%', '$year', '%')";

				}
			}else{
				// checks if a year is specified
				if($year == "ALL"){
					$sql = "SELECT clients.Client_FirstName, clients.Client_LastName, clients.ID, clients.Event_Date, clients.Start_Time, clients.Done_Time, clients.Dayof_Address, clients.Client_Photographer_ID, clients.Client_Planner_ID FROM clients WHERE Client_LastName LIKE CONCAT('%', '$search', '%') OR Client_FirstName LIKE CONCAT('%', '$search', '%')";
				}else{
					// gets any part of last or first name but not both at same time
					$sql = "SELECT clients.Client_FirstName, clients.Client_LastName, clients.ID, clients.Event_Date, clients.Start_Time, clients.Done_Time, clients.Dayof_Address, clients.Client_Photographer_ID, clients.Client_Planner_ID FROM clients WHERE Client_LastName LIKE CONCAT('%', '$search', '%') OR Client_FirstName LIKE CONCAT('%', '$search', '%') AND Event_Date LIKE CONCAT('%', '$year', '%')";
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
				$total_cost_sql = "SELECT services.Service_Price FROM services WHERE Client_ID='$bid'";
				$total_cost_sql_return = $mainConnection->query($total_cost_sql) or die("cannot get info on total cost.");
				while($total_cost_row = $total_cost_sql_return->fetch_assoc()){
					$totalCost += $total_cost_row['Service_Price'];
				}
				$totalCost = round($totalCost*1.15, 2);  // rounds to nearest cent

				// get total of payments
				$totalPaid = 0;
				$total_paid_sql = "SELECT payments.Amount FROM payments WHERE Client_ID='$bid'";
				$total_paid_sql_return = $mainConnection->query($total_paid_sql) or die("cannot get info on total paid.");
				while($total_paid_row = $total_paid_sql_return->fetch_assoc()){
					$totalPaid += $total_paid_row['Amount'];
				}

				$totalOwe = $totalCost-$totalPaid;

				// add the styled clients into variable to respond
				$list = "<div class='card col-xs-12 col-sm-12 col-md-4 col-lg-4' id='card'><h3 id='cardName'>". $firstName . " " . $lastName ."</h3><span class='cardText' id='cardDate'>" . $eventDate . "</span><span class='cardText' id='cardTime'>" . $startTime . "-" . $doneTime . "</span><span class='cardText' id='cardPlace'>".$dayof."</span><span class='cardText' id='cardOwe'><b>Payment: </b>$". $totalPaid ."/$". $totalCost ."(Owing: $". $totalOwe .")</span><span class='cardText' id='cardPhoto'><b>Photographer: </b>".$photo_name."</span><span class='cardText' id='cardPlanner'><b>Planner: </b>".$planner_name."</span><button id='edit' onclick='edit(".$bid.")'>Edit</button><button id='pdf' onclick='pdf(".$bid.")'>Create PDF</button></div>" . $list;

				// $list .= "<li id='entry'><button id='edit' onclick='edit(".$bid.")'>Edit</button><span id='text'>".$firstName . " " . $lastName . " Event on: " . $eventDate . " " . $startTime . "-" . $doneTime . "</span></li>";//" . $eventDate . " " . $startTme . " " . $doneTime . " " . $photographer . " " . $dayof . "
			}
			// if no results
			if($list == "" || $list == null || $list == " "){
				echo "Results not yet found";
			}else{
				echo $list;
			}
		}else{
			echo "Results not yet found";
		}
	}else{
		echo "No name given please type a name and hit enter for results";
	}
}else{
	echo "You do not have access to view this page";
	header("Location: ../calendar/index.php");
}

?>
