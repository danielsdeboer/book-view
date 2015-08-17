<?php
include 'classes.php';
function fileName() {
  return 'cotm';
}

$index = new Index(fileName());

function say($something) {

  // "cache" the object - probably a better way of doing this?
  static $metadata;

  switch (is_null($metadata)) {
    case true:
      $metadata = new Metadata(fileName());  
      break;
  }

  echo $metadata->getMetadata($something);
}

?>
<!DOCTYPE html>
<html>
<head>
  <title><?php say('documentTitle')?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrap">
<header>
  <h1 class="text-uppercase text-center"><?php say('documentTitle'); ?></h1>
  <h3 class="text-center">by <?php say('authorFirstName'); ?> <?php say('authorLastName'); ?></h3>
</header>



  <article>
    <?php $index->displayIndex(); ?>
  </article>


</div>

<footer>
  <p>A <?php say('documentCategory'); ?> <?php say('documentType'); ?>.</p>
</footer>


</body>
</html>