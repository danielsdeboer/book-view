<?php

class index {

  protected $file_list = [];
  protected $title_tag = '#TITLE# ';

  # This should be a multidimensional array formatted as
  # [
  #   [title, filename],
  #   [title, filename]
  # ]
  #
  # etc.
  protected $titles = [];

  function __construct ($filename) {
    # get a list of all the files in the current directory
    $files = scandir('.');

    # Loop over the files and unset any values in the array
    # which don't match the string passed to the constructor.
    #
    # We do this because it's possible to have a series of
    # similarly-named files that we'll look at in sequence.
    #
    # For instance if you pass foo, you can have files
    # foo1.txt, foo2.txt, etc.

    foreach($files as $key => $val) {
      
      switch(true) {
        case strpos($val, $filename) === false:
          unset($files[$key]);
          break;
      } #switch

    } #foreach

    # dump the remaining files into the protected variable $file_list
    $this->setFileList($files);

  } #constructor


  # Set the protected variable #file_list.
  # This isn't technically necessary in this instance because
  # this method is protected, but in case this method needs to be
  # public at some point, it's here.

  protected function setFileList($file_list) {

    $this->file_list = $file_list;

  } # $this->file_list setter




  # Set the titles from the file list.

  protected function setTitles($title_list) {

    # Check to make sure $title_list is an array
    # and throw an exception if it isn't.
    switch (is_array($title_list)) {
      
      case true:
        $this->titles = $title_list;
        break;
      
      default:
        throw new Exception("Title list must be an array");
        break;

    }
  }



  # This logic builds an array of titles
  # The presentation logic is kept separately in $this->displayIndex()
  # 

  protected function buildIndex() {

    $title_list = [];

    # iterate over the list of files
    foreach ($this->file_list as $key => $val) {
      
      # Open each file and get the first line only in read-only mode
      $first_line = fgets(fopen($val, 'r'));

      # If a title tag exists, dump the title into an array
      switch(true) {
        # !== false is important here as 0 (the position that $this->title_tag 
        # should appear) evaluates as false when an equality operator (==) is 
        # used instead of the identity operator (===).
        # 
        # The downside of using a switch instead of if/else in this case 
        # is that an if/else statement requires you to explicity set the
        # operator (eg if(something==somethingelse)) whereas a switch defaults
        # to lazy evaluation.
        # 
        # So for instance this switch written optimally like so:
        # switch (strpos(etc, etc) {
        #   case !false:
        #     doStuff();
        #     break;
        # }
        # will evaluate all 0's as false.
        # Which is obviously not what we want here.
        case strpos($first_line, $this->title_tag) !== false:
          $title_list[] = [str_replace($this->title_tag, "", $first_line), $val];
          break;
      } #switch
    } #foreach

    # dump the titles into $this->titles
    $this->setTitles($title_list);

  } #buildIndex


  # This builds the index page.
  public function displayIndex() {

    # First, build the index
    $this->buildIndex();

    # Open an ordered list
    echo '<ol>';

    # Iterate over $this->titles
    foreach ($this->titles as $array) {
      # open a list item for each item
      echo '<li>';

      # build the link
      echo '<a href="view.php?view=' . $array[1] . '">'. $array[0] . '</a>';

      # close the list item
      echo '</li>';

    } #foreach

    # Close the ordered list
    echo '</ol>';
  }
}

class view {

  protected $title = '#TITLE# ';
  protected $chapter = '#CHAPTER#';

  // take the file input and dump it to a protected variable
  // and render the file contents
  function __construct($file) {

    error_reporting(E_ERROR);
    
    // Check and see if the file actually exists. If it doesn't, throw an exception. Otherwise proceed as normal.
    switch(file($file)) {
      case false:
        echo "This file doesn't exist. Try again!";
        exit();
        break;
      default:
        $file_contents = file($file);
        break;
    }

    $title = $this->title;

    $chapter_counter = 0;

    foreach($file_contents as $key => $val) {
      switch(true) {
        case strpos($val, $this->title) !== false:
          echo '<h1>' . "\n" . str_replace($this->title, "", $val) . '</h1>' . "\n";
          break;
        case strpos($val, $this->chapter) !== false:
          $chapter_counter++;
          
          switch(true) {
            // if the chapter marker has trailing text (other than a space), print the chapter header
            case strlen($val) > 11:
              echo '<h2>' . str_replace($this->chapter, "", $val) . "</h2>\n";
              break;
            // otherwise just print a chapter number
            default:
              echo '<h2>' . str_replace($this->chapter, "", (string) $chapter_counter) . '</h2>';
              break;
            } #switch string length
          break;

        default:
          echo '<p>' . "\n" . htmlentities($val, ENT_QUOTES, 'UTF-8') . '</p>' . "\n";
          break;
      } #switch
    } #foreach
  } #constructor
} #class


?>