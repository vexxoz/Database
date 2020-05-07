
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

function deleteExistingService(element, id, serviceId){
  // confirm delete
  if(confirm("Are you sure you want to delete this service? There is no undo! Click OK to delete.")){
    var newCode = '<input type="hidden" name="deletesvc'+id+'" value="'+serviceId+'">'; // new text to be inserted to delete the service
    var parent = element.parentElement; // set parent variable to this buttons the service element
    parent.insertAdjacentHTML('beforeend', newCode); // using insertAdjacentHTML keeps discounts on other services from glitching
    parent.style.display = "none"; // set the service invisible

  var servicePrice = parseFloat(element.parentElement.children[0].value); // get the price of the service thats being removed
  servicePrice = servicePrice * -1; // get the oposite of the value to remove its effect on the totals
  document.getElementById("totalCost").innerHTML = parseFloat(document.getElementById("totalCost").innerHTML) + servicePrice;// update the total cost
  document.getElementById("totalRemaining").innerHTML = parseFloat(document.getElementById("totalRemaining").innerHTML) + servicePrice;// update the total remaining
  // decrement counter
  countId = countId - 1;
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
$('#weddingdate').datepick({dateFormat: 'mm/dd/yyyy'});
$('#predate').datepick({dateFormat: 'mm/dd/yyyy'});

// Timepicker for pretime, start and done times
$('#pretime').timepicker({ 'step': 15 });
$('#starttime').timepicker({ 'step': 15 });
$('#donetime').timepicker({ 'step': 15 });
