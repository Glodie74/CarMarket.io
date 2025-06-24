<!DOCTYPE html>
<html>
<head>
    <title>Save Product</title>
</head>
<body>
    <h2>Save Product</h2>
    <form method="POST" action="Save_product.php">
        <label for="product_name">Product Name:</label><br>
        <input type="text" id="product_name" name="product_name"><br><br>

        <label for="brand">Brand:</label><br>
        <input type="text" id="brand" name="brand"><br><br>

        <label for="price">Price:</label><br>
        <input type="number" id="price" name="price"><br><br>

        <input type="submit" value="Save">
    </form>
</body>
</html>
