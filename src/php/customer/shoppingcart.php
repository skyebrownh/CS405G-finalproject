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
$cartIsEmpty = TRUE;

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
else {
    // validate user login by querying form value
    $query = "SELECT * FROM (SELECT * FROM ShoppingBasket) AS ShoppingCart JOIN 
        (SELECT * FROM Products) AS AllProducts ON ShoppingCart.prodID = 
        AllProducts.productID WHERE userID='$userID'";
    $result = $mysqli->query($query);
    if (!$result) {
        echo "<script> alert(\"Query failed: " . $mysqli->error . ". ";
        echo "Please try again later. Click 'OK' to go back.\"); </script>";
        exit();
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
        <a href="./products.php">Products</a>
        <a href="./orders.php">Orders</a>
        <a href="./shoppingcart.php" class="active">Shopping Cart</a>
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
</script>

<div class="imgcontainer">
    <h1>Shopping Cart</h1>
    <img src="../../assets/ShoppingCartLogo.png" alt="Avatar" class="avatar">
</div>

<div>
<?php
    // Create table with items in users shopping cart
    echo '<table>';
    echo '<tr>';
    echo '<th> Actions </th>';
    echo '<th> Product Name </th>';
    echo '<th> Category </th>';
    echo '<th> Quantity </th>';
    echo '<th> Each </th>';
    echo '<th> Discount </th>';
    echo '<th> Total </th>';
    echo '</tr>';
    // Display products with information on row
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $cartIsEmpty = FALSE;
        echo '<tr>';
        // button to allow removal of product from cart
        echo '<form action="./removefromcart.php" method="POST"><td><center>'; 
        echo '<button name="id" value="' . $row["productID"] .'" type="submit" ';
        echo 'onclick="removeFromCartAlert()"> Remove from Cart </button>';
        echo '</center></td></form>';
        echo '<td>' . $row["name"] . '</td>';
        echo '<td>' . $row["category"] . '</td>';
        // update quantity of a product in cart
        echo '<form action="./updatequantity.php" method="POST"><input ';
        echo 'type=hidden name="productID" value ="' . $row["productID"] . '" ';
        echo 'style="display:none"></input><td><center><input type=number min="1" ';
        echo 'name="quantity" value="' . $row["quantity"] .'" style="width:3em; ';
        echo 'text-align:center" onchange=this.form.submit()></input></center>';
        echo '</td></form>';
        echo '<td>$' . $row["price"] . '</td>';
		echo '<td style="color:red">-$' . number_format($row["price"]*($row["promotions"] / 100)*$row["quantity"], 2, '.', '') . '</td>';
        echo '<td>$' . number_format(($row["price"]-($row["price"]*($row["promotions"] / 100)))*$row["quantity"], 2, '.', '') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
?>
</div>

<script>
function removeFromCartAlert() {
    alert("Item removed successfully from cart!");
}
</script>

<?php
    // Get price and quantity for each product in shopping cart
    $query = "SELECT price, quantity, promotions FROM (SELECT * FROM ShoppingBasket) 
        AS ShoppingCart JOIN (SELECT * FROM Products) AS AllProducts ON 
        ShoppingCart.prodID = AllProducts.productID WHERE userID='$userID'";
    $result = $mysqli->query($query);
    if (!$result) {
        echo "<script> alert(\"Query failed: " . $mysqli->error . ". ";
        echo "Please try again later. Click 'OK' to go back.\"); </script>";
        exit;
    }
    // Calculate total price, tax, and subtotal for products in shopping cart
    else {
        $true_prices = [];
        $prices = [];
        $quantities = [];
        $promotions = [];
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $prices[] = $row["price"];
            $quantities[] = $row["quantity"];
            $promotions[] = $row["promotions"];
        }
        $true_total = 0;
        for ($i = 0; $i <= sizeOf($prices); $i++) {
            $true_prices[] = ($prices[$i] - ($prices[$i]*($promotions[$i] / 100))) * $quantities[$i];
            $true_total += $true_prices[$i];
        }
        // Display total, tax, and subtotal
        $total = number_format($true_total, 2, '.', '');
        $tax = number_format($true_total * 0.06, 2, '.', '');
        $subtotal = number_format($true_total + $true_total * 0.06, 2, '.', '');
        echo '<div style="float:right; margin-right:10px; text-align:right">';  
        echo '<div style="float:left; margin-top:0px">';
        echo '<p style="float:right"><b> Total:&nbsp <br>Sales Tax:&nbsp <br>Subtotal:&nbsp </b></p>';
        echo '</div>';
        echo '<div style="float:right; margin-top:0px">';
        echo '<p style="float:right"><b>$' . $total . '<br>';
        echo '$' . $tax . '<br>$' . $subtotal . '</b></p>';
        echo '</div>';
        echo '</div>';
    }
    $mysqli->close();
?>

<br><br><br><br><br>

<center><button type="button" onclick="addToOrder()" class="primarybtn"> Click Here to Order! </button></center>

<script>
function addToOrder() {
    var cartIsEmpty = "<?php echo $cartIsEmpty; ?>";
    if (cartIsEmpty) {
        alert("Your shopping cart is empty!");
    }
    else if (confirm("Are you sure you want to place this order?")) {
        window.location="./addtoorder.php";
    }
}
function logout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location="../logout.php";
    }
}
</script>
</body>
</html>
