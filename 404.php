<!DOCTYPE html>
<html>
<head>
  <title>404</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <h1>Sorry. Something's off the rails.</h1>
  <?php switch(isset($_GET['e'])) {
    case true:
      echo '<h3>More specifically: ' . ( (string) str_replace('"', '', $_GET['e']) ) . '</h3>';
      break;
  }
  ?>
  
</div>
</body>
</html>