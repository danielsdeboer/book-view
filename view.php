<?php
include 'classes.php';
include 'functions.php';

$view = new View($_GET['view']);
$nav = new Nav($_GET['view']);
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

<nav>
  <?php if ($nav->getDocument('-1')) { echo '<a href="view.php?view=' . $nav->getDocument('-1') . '">Previous Document</a>'; } ?>
  
  <a href="index.php">Back to Index</a>

  <?php if ($nav->getDocument('+1')) { echo '<a href="view.php?view=' . $nav->getDocument('+1') . '">Next Document</a>'; } ?>
  

</nav>

<?php
  # Set a word counter to 0
  $word_count = 0;

  # Outer foreach outputs chapters
  foreach($content['bookContents'] as $array) {

    # Check if a chapter number exists and output it
    if (isset($array['chapterNumber'])) {
      echo '<h2>' . $array['chapterNumber'] . '</h2>' . "\n";  
    }
    
    # Inner foreach outputs paragraphs
    foreach($array['chapterContents'] as $key => $val) {
      echo '<p>' . $val['paragraph'] . '</p>' . "\n";

      # add the word count of this paragraph to the cumulative word_count
      $word_count += $val['wordCount'];
    }
  }

  #display the word count
  echo "<h4>Word Count: " . $word_count . "</h4>";
?>



</div>


</body>
</html>