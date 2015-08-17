<?php
include 'classes.php';
$file_name = 'cotm';

$index = new Index($file_name);
$metadata = new Metadata($file_name);  
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $metadata->getMetadata('documentTitle'); ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrap">
<header>
  <h1><?php echo $metadata->getMetadata('documentTitle'); ?></h1>
  <h3>by <?php echo $metadata->getMetadata('authorFirstName'); ?> <?php echo $metadata->getMetadata('authorLastName'); ?></h3>
</header>



  <article>
    <ol>
      <?php 
        # Build the list of titles
        foreach($index->getTitles() as $array) {
          echo '<li><a href="view.php?view=' . $array[1] . '">' . $array[0] . '</a></li>';
        }
      ?>
    </ol>
  </article>


</div>

<footer>
  <p>A <?php echo $metadata->getMetadata('documentCategory'); ?> <?php echo $metadata->getMetadata('documentType'); ?>.</p>
</footer>


</body>
</html>