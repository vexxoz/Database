<?php
session_start();
require_once "session_validate.php";
if(isset($_SESSION['cid']) && $_SESSION['cid'] > 0 && $_SESSION['isAdmin'] >= 1){// check if logged in with correct permissions
		$session_validate = new Session_Validater();
		$session_validate->check();
}else{
	header("Location: ../calendar/index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Salon Maison Confidential</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="newBrideStyle.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <div class="row">

			<?php
				if(isset($_GET['msg'])){
					if(isset($_GET['color']) && $_GET['color'] == "green"){
						$color = "alert-success";
					}else if(isset($_GET['color']) && $_GET['color'] == "red"){
						$color = "alert-danger";
					}else{
						$color = "alert-info";
					}

					echo '<div class="col-md-12 col-sm-12 col-lg-12 '.$color.'" id="alert" name="alert">';
					echo $_GET['msg'] . "</br>";
					echo "</div>";
				}
			 ?>

		<div class="col-md-12 col-sm-12 col-lg-12" style="text-align: center;">
			<span style="font-size: 20px;">Any Bugs? Report them <u><a style="color:black;" href="./bugreport.php">HERE</a></u></span>
		</div>
  </div>
	<div class="row">
		<div class="col-md-12 col-sm-12 col-lg-12" style="text-align: center;">
			<button onclick="listView(this)">List View: Not Active</button>
		</div>
	</div>
	<div class="row">
		<button class="col-md-1 col-sm-2 col-xs-2 col-lg-1 menubar" onclick="back()">Menu</button>
		<button class="col-md-1 col-sm-2 col-xs-2 col-lg-1 menubar offset-md-1 offset-lg-1" onclick="edit(0)">New Bride</button>
	  <input class="col-md-5 col-sm-8 col-xs-8 col-lg-5" type="text" id="search" name="search" placeholder="Type to start search" autocomplete="off">
	  <select class="col-md-1 col-sm-2 col-xs-2 col-lg-1 menubar" type="text" id="year" name="year" required="">
			<option value="ALL">ALL</option>
			<?php
				$currentYear = (int)date("Y");
				$endYear = 2009;
				// dynamically keep the dates current
				for($i=$currentYear+4;$i>=$endYear;$i--){
					if($i == $currentYear){
						echo "<option value='".$i."' selected>".$i."</option>";
					}else{
						echo "<option value='".$i."'>".$i."</option>";
					}
				}

			 ?>
	  </select>
		<div class="dropdown col-md-1 col-sm-2 col-xs-2 col-lg-1">
		  <button class="btn btn-secondary dropdown-toggle" type="button" id="cbdButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		    CBD
		  </button>
		  <div class="dropdown-menu" aria-labelledby="cbdButton">
				<?php
				// dynamically keep the dates current
				for($i=$currentYear;$i>=$endYear;$i--){
					echo "<a class='dropdown-item' href='opencbd.php?year=".$i."'>".$i."</a>";
				}

				?>
		  </div>
		</div>
	</div>
  <div class="container" id="resultsBox" name="resultsBox">
    <div class="row" name="results" id="results">

    </div>
    <!-- <ul id="results" name="results">
      No results found yet!
    </ul> -->
  </div>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script type="text/javascript">
		<?php

		if(isset($_SESSION['lastSearch']) && isset($_SESSION['lastYear'])){

			echo "var lastSearch = '".$_SESSION['lastSearch']."';";
			echo "var lastYear = '".$_SESSION['lastYear']."';";

			unset ($_SESSION['lastSearch']);
			unset ($_SESSION['lastYear']);
		}else{
			echo "var lastSearch = '';";
			echo "var lastYear = '';";
		}

		?>

		var list = false;
		var headNode;

    $('#search').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
					var value = $('#search').val();
					var inputYear = $('#year').val();
					search(value, inputYear);
        }
    });

	function search(value, inputYear){
		$.post("clientSearch.php",
		{
			type: 'search',
			name: value,
			year: inputYear
		},
		function(data,status){
			//alert("Data: " + data + "\nStatus: " + status);
			$("#results").html(data);
			if(list === true){
				for(var i=0;i<document.getElementById("results").childElementCount;i++){
					// add classes back into elements
					document.getElementById("results").children[i].classList.remove("col-xs-12");
					document.getElementById("results").children[i].classList.remove("col-sm-12");
					document.getElementById("results").children[i].classList.remove("col-md-4");
					document.getElementById("results").children[i].classList.remove("col-lg-4");
				}
			}
		});
	}

  function edit(id){
	  window.location = 'editBride.php?bid=' + id;
  }
	function back(){
		window.location = '../calendar/selector.php';
	}
  function pdf(id){
	  window.location = 'createpdf.php?bid=' + id;
  }
	function oldpdf(id){
		window.location = 'createpdf.php?bid=' + id+'&oldTerms=1';
	}

	function listView(element){
		headNode = document.getElementsByTagName("head");
		if(list === true){ // remove list mode
			listDectivate();
			element.innerHTML = "List View: Not Active";
		}else{ // activate list mode
			listActivate();
			element.innerHTML = "List View: Active";
		}
	}

	function listDectivate(){
		list = false;
		headNode[0].children[headNode[0].childElementCount-1].remove(); // remove last element
		for(var i=0;i<document.getElementById("results").childElementCount;i++){
			// add classes back into elements
			document.getElementById("results").children[i].classList.add("col-xs-12");
			document.getElementById("results").children[i].classList.add("col-sm-12");
			document.getElementById("results").children[i].classList.add("col-md-4");
			document.getElementById("results").children[i].classList.add("col-lg-4");
		}
	}

	function listActivate(){
		list = true;
		var newCode = '<link rel="stylesheet" type="text/css" href="newBrideStyleList.css">';
		headNode[0].insertAdjacentHTML('beforeend', newCode);
		for(var i=0;i<document.getElementById("results").childElementCount;i++){
			// add classes back into elements
			document.getElementById("results").children[i].classList.remove("col-xs-12");
			document.getElementById("results").children[i].classList.remove("col-sm-12");
			document.getElementById("results").children[i].classList.remove("col-md-4");
			document.getElementById("results").children[i].classList.remove("col-lg-4");
		}
	}

	// if the search was saved
	if(lastSearch != '' && lastYear != ''){
		// put search into search bar
		document.getElementById('search').value = lastSearch;
		// set the date to previous search date
		document.getElementById('year').value = lastYear;
		// run the search
		search(lastSearch, lastYear);
	}
</script>
</html>
