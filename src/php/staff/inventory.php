<?php
// start session
session_start();
if(!isset($_SESSION['username'])) {
    // not logged in
    header('Location: ../../../index.html');
    exit();
}

// get user ID, username, and password from session
$userID = $_SESSION['userID'];
$username = $_SESSION['username'];
$password = $_SESSION['password'];

// connect to mysql
$host = "localhost";
$mysqlUser = "root";
$mysqlPassword = "pwd";
$mysqldb = "ecommerce";
$mysqli = new mysqli($host, $mysqlUser, $mysqlPassword, $mysqldb);

// check connection
if ($mysqli->connect_errno) {
    echo "<script> alert(\"Could not connect to database";
    echo "Error: " . $mysqli->connect_error . ". ";
    echo "Please try again another time. Click 'OK' to go back.\"); ";
    echo "window.location.href='./homepage.php'; </script>"; 
    exit();
}
// get user firstname and lastname and store in current session
else {
    // Get all products to display
    $query = "SELECT * FROM Products ORDER BY category;";
    $result = $mysqli->query($query);
    if (!$result) {
        echo "<script> alert(\"Query failed: " . $mysqli->error . ". ";
        echo "Please try again later. Click 'OK' to go back.\"); "; 
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/main.css">
</head>
<body>

<div class="topnav" id="myTopnav">
    <div style="float:left">  
        <p style="float:left; color:#f2f2f2; text-align: center; text-decoration: none; 
        font-size: 15px; margin-left:10px; margin-bottom:0px"> ToyzRUs </p>
    </div>
    <div style="float:right">
        <a href="./homepage.php">Home</a>
        <a href="./inventory.php" class="active">Inventory</a>
        <a href="./orders.php">Orders</a>
        <a href="javascript:void(0);" onclick="logout()">Logout</a>
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
</div>

<script>
function myFunction() {
    var x = document.getElementById("myTopnav");
    if (x.className === "topnav") {
        x.className += " responsive";
    } 
    else {
        x.className = "topnav";
    }
}
function logout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location="../logout.php";
    }
}
</script>

<div class="imgcontainer">
    <h1>Manage Inventory</h1>
    <img src="../../assets/staffinventorylogo.png" alt="Avatar" class="avatar">
	<br>
</div>

<center><button type="button" onclick="addInventory()" class="primarybtn"> Add New Product </button></center>

<br>

<script>
function addInventory() {
	window.location="./addinventoryform.php";
}
</script>

<div>
<?php
    // Display table header
    echo '<table>';
    echo '<tr>';
    echo '<th> Product Name </th>';      
    echo '<th> Category </th>';
    echo '<th> Price </th>';
    echo '<th> Stock </th>';
    echo '<th> Quantity </th>';
	//echo '<th> Actions </th>';
    echo '</tr>';
    // Add products into table
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row["name"] . '</td>';
        echo '<td>' . $row["category"] . '</td>';
        echo '<td>$' . $row["price"] . '</td>';
        // Out of stock product, disable 'Add to Cart' button
        if ($row["inventory"] == 0) {
            echo '<td><center><mark style="background-color:#F44336">';
            echo 'Out of Stock</mark></center></td>';
        }
        // Low stock
        else if ($row["inventory"] <= 5) {
            echo '<td><center><mark style="background-color:#FFE158">';
            echo 'Low Stock</mark></center></td>';
        }
        // In stock
        else {
            echo '<td><center><mark style="background-color:#4F7FE4">';
            echo 'In Stock</mark></center></td>';
        }
        // update inventory for a particular product
        echo '<form action="./updateinventory.php" method="POST"><input ';
        echo 'type=hidden name="productID" value ="' . $row["productID"] . '" ';
        echo 'style="display:none"></input><td><center><input type=number ';
        echo 'name="quantity" value="' . $row["inventory"] .'" style="width:3em; ';
        echo 'text-align:center" onchange=this.form.submit()></input></center>';
        echo '</td></form>';
        // button to allow removal of product from cart
        /*echo '<form action="./removefrominventory.php" method="POST"><td><center>'; 
        echo '<button name="id" value="' . $row["productID"] .'" type="submit" ';
        echo 'onclick="return removeFromInventoryAlert()"> Remove </button>';
        echo '</center></td></form>';
		echo '</tr>';*/
    }
    echo '</table>';
    $mysqli->close();
?>
</div>

<script>
function removeFromCartAlert() {
    if (confirm("Are you sure you want to remove this item from inventory?")) {
        return true;
    }
    else {
        return false;
    }
}
</script>

</body>
</html>
