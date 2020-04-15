<?php
session_start();
if(isset($_SESSION['cid']) && $_SESSION['cid'] > 0 && $_SESSION['isAdmin'] >= 1){

}else{
	header("Location: ../calendar/index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Salon Maison Confidential</title>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="newBrideStyle.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <div class="row">
    <div class="col-md-12 col-sm-12 col-lg-12" id="alert" name="alert">

    </div>
  </div>
	<button class="col-md-1 col-sm-2 col-xs-2 col-lg-1" onclick="edit(0)">New Bride</button>
  <input class="col-md-5 col-sm-8 col-xs-8 col-lg-5 offset-md-2 offset-lg-2" type="text" id="search" name="search" placeholder="Type to start search" autocomplete="off">
  <select class="col-md-1 col-sm-2 col-xs-2 col-lg-1" type="text" id="year" name="year" required="">
    <option value="ALL">ALL</option>
		<option value="2025">2025</option>
    <option value="2024">2024</option>
    <option value="2023">2023</option>
    <option value="2022">2022</option>
    <option value="2021">2021</option>
    <option value="2020" selected>2020</option>
    <option value="2019">2019</option>
    <option value="2018">2018</option>
    <option value="2017">2017</option>
    <option value="2016">2016</option>
    <option value="2015">2015</option>
    <option value="2014">2014</option>
    <option value="2013">2013</option>
    <option value="2012">2012</option>
    <option value="2011">2011</option>
    <option value="2010">2010</option>
    <option value="2009">2009</option>
    <option value="2008">2008</option>

  </select>
  <div class="container" id="resultsBox" name="resultsBox">
    <div class="row" name="results" id="results">

    </div>
    <!-- <ul id="results" name="results">
      No results found yet!
    </ul> -->
  </div>
</body>
<script type="text/javascript">

    $('#search').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
			var value = $('#search').val();
			var inputYear = $('#year').val();
			$.post("clientSearch.php",
			{
				type: 'search',
				name: value,
				year: inputYear
			},
			function(data,status){
				//alert("Data: " + data + "\nStatus: " + status);
				$("#results").html(data);
			});
        }
    });

  function edit(id){
	  window.location = 'editBride.php?bid=' + id;
  }

  function pdf(id){
	  window.location = 'createpdf.php?bid=' + id;
  }
</script>
</html>
