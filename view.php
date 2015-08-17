<?php
include 'classes.php';
include 'functions.php';

$view = new View($_GET['view']);
$content = $view->buildArray();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Children of the Moon</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrap">
<h1><?php echo $content['bookTitle']; ?></h1>

<?php
  # Outer foreach outputs chapters
  foreach($content['bookContents'] as $array) {
    echo '<h2>' . $array['chapterNumber'] . '</h2>' . "\n";

    # Inner foreach outputs paragraphs
    foreach($array['chapterContents'] as $key => $val) {
      echo '<p>' . $val['paragraph'] . '</p>' . "\n";
    }
  }

pr($content);

?>



</div>


</body>
</html>