<?php

class Index {

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
      
      # Open each file and get the first line only in read-only mode.
      # Also strip out any newlines which are annoying if present.
      $first_line = str_replace(["\r", "\n"], "", fgets(fopen($val, 'r')));

      # If a title tag exists, dump the title into an array.
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
  public function getTitles() {

    # First, build the index
    $this->buildIndex();

    # Return an array of titles
    return $this->titles;
  }
}



class Nav extends Index {

  # Holds the current document (filename with extension)
  protected $current_document;
  # Holds a list of titles generated from the parent class
  protected $title_list;
  # Holds a sanitized file list stripped of any metadata documents
  protected $sanitized_file_list;

  

  public function __construct($filename) {

    # Bump the input into the current_document var. This will be
    # used to determine if there is a next document or not.
    $this->current_document = $filename;

    # Get rid of any numbers in the filename
    $filename = preg_replace('/\d/', "", $filename);

    # Split the string using . as the delimiter
    $filename = explode(".", $filename);

    # Get the first value in the returned array
    $filename = $filename[0];

    # Call the parent constructor with the filtered filename
    parent::__construct($filename);

    $this->setSanitizedFileList();
    $this->title_list = $this->getTitles();
  }



  protected function setSanitizedFileList() {
    $file_list = $this->file_list;

    # If the array value contains a json file, unset that key.
    # We don't want users to navigate to the metadata file.
    foreach($file_list as $key => $val) {
      if (strpos($val, '.json') !== false) {
        unset($file_list[$key]);
      }
    }

    $this->sanitized_file_list = $file_list;
  }



  public function getDocumentOrTitle($number, $type) {
    $current_document = $this->current_document;
    $file_list = $this->sanitized_file_list;
    $title_list = $this->title_list;

    # Switch over the $type variable
    switch(true) {
     
      # If the type variable is 'document' or 'doc', get the
      # document index specified by $number. This can be a positive
      # or negative number, or even zero. So it's possible to even skip
      # forward or backward by several documents by specifying '-2'
      # for instance.
      case $type === 'document' || $type === 'doc':

        # Search the $file_list array for the current document.
        # This returns false if not found, which shouldn't happen,
        # but even if it does, false + 0 = 0 (etc) in PHP.
        $key = array_search($current_document, $file_list);

        # Increment the key by whatever number was passed to the
        # method. This number does not have to be positive or even
        # non-zero.
        $key = $key + (int) $number;
  
        # If the index at that key is set, return the value
        if (isset($file_list[$key])) {
          return $file_list[$key];
        }
        break;

      # If the type variable is 'title', get the title. Same
      # caveat as 'document' above.
      case $type === 'title':
        
        # Because the $title_list array structure is a little different
        # we do a foreach to search the multidimentional array and
        # discover why variables should not be named 'key'.
        foreach($title_list as $arraykey => $val) {

          # If we find anything, set the key
          if (array_search($current_document, $val)) {
            $key = $arraykey;
          }
        }

        # Increment the key as we did above
        $key = $key + (int) $number;

        # If the index can exist (eg it's greater than zero), return
        # the value of that index.
        # Note that in this array we search for $current_document which
        # lives at $title_list[$key][1], but we actually return 
        # $title_list[0] (which contains the title, not the filename).
        if ($key >= 0) {
          return $title_list[$key][0];
        }

        break;

    } #switch type
  } #getDocumentOrTitle
} #class Nav



class View {

  protected $filename;
  protected $title = '#TITLE#';
  protected $chapter = '#CHAPTER#';

  public function checkFile($filename) {
    if (!file($filename)) {
      throw new Exception("The file does not exist.");
    }
  }

  protected function pr($thing_to_check) {
    echo '<pre>'; print_r($thing_to_check); echo '</pre>';
  }

  # Take the passed filename and check if exists. If it does,
  # dump its bits and pieces into an array.
  public function __construct($filename_input) {

    # Check to see if the file actually exists. If it doesn't,
    # throw an exception and redirect to the 404 with the 
    # message attached.
    try {
      $this->checkFile($filename_input);
    } catch (Exception $e) {
      header('Location: 404.php?e="' . $e->getMessage() . '"');
      die();
    }

    # if everything goes ok, load the filename_input into a protected variable
    $this->filename = $filename_input;

  } #constructor


  public function getFilename() {
    return $this->filename;
  }



  public function buildJson() {
    
    # Get the array
    $array = $this->buildArray();

    # Return the array as a json encoded object.
    return json_encode($array);

  } # buildjson()



  /*
   *
   * Here we can build the array without having to worry about output;
   * if we want json there's another method for that.
   *
   */

  public function buildArray() {
    # Since the program didn't die() in the constructor, we can safely (?) continue 
    # doing things like loading the actual file contents into an array. Note the 
    # use of FILE_IGNORE_NEW_LINES, so we don't have to use rtrim() or whatnot
    # somewhere down the line (or in the view file).
    $file_contents = file($this->filename, FILE_IGNORE_NEW_LINES);



    # Set up a chapter counter; we use this if chapter names aren't defined,
    # in which case we just use numbers.
    $chapter_counter = 0;
    $paragraph_counter = 0;



    # We're going to use a new array for this, as the structure will be 
    # slightly different to make json encoding easier, like so:
    #
    # Title [
    #   Chapter [
    #     Paragraph [ ... ]
    #   ]
    # ]
    #
    # and so forth.
    $file_contents_formatted = [];



    # Since file() dumped the file as an array, we can iterate over it and
    # do different things for different bits of the array
    foreach($file_contents as $key => $val) {
      
      # Switch over each $val and do different things depending on
      # what they are (for instance title and chapter headings)
      switch(true) {

        # Check for a title tag.
        case strpos($val, $this->title) !== false:
          # Remove the title tag and trim any extra spaces
          $file_contents_formatted = ['bookTitle' => utf8_encode(trim(str_replace($this->title, "", $val))), 'bookContents' => []];
          break;

        # Check for chapter.
        case strpos($val, $this->chapter) !== false:
          
          # Increment the chapter number each time (before the rest of logic, as we don't
          # want a Chapter 0).
          $chapter_counter++;

          # If a chapter title exists, $chapter will be a string which can be echoed 
          # as a chapter title in the view. If not it will simply be an empty field
          # in the array in which case the view can just output the chapterNumber.
          $chapter = utf8_encode(trim(str_replace($this->chapter, "", $val)));

          # Dump the chapter number and title into an array
          $file_contents_formatted['bookContents'][] = 
            [
              'chapterNumber' => $chapter_counter, 
              'chapterName' => $chapter,
              'chapterContents' => [],
            ];
          // $file_contents_formatted['title'] = array("chapter" => );

          # Reset the paragraph counter 0 every time a chapter heading is found.
          # We want to count the paragraphs per chapter, not overall.
          $paragraph_counter = 0;

          break;

        # Otherwise assume we have a paragraph.
        default:

          # We also want to increment the paragraph counter; not certain how this 
          # would be useful, but it's there.
            $paragraph_counter++;

          # Paragraphs don't have any tags, so we just trim() and move on.
          # We also use htmlspecialchars() so our browsers don't freak out
          # and interpret things as tags and whatnot.
          $paragraph = utf8_encode(htmlspecialchars(trim($val)));

          # Dumping the paragraph into the array isn't as easy as it is
          # for titles and chapters. Here we have to worry about where the
          # paragraph goes, so we slot it in under $chapter_counter - 1,
          # which handily happens to be the array position of each chapter
          # 
          # The ternary expression following $chapter_counter is useful when
          # there are no chapters; otherwise the array's index is -1.
          $file_contents_formatted['bookContents'][$chapter_counter > 0 ? $chapter_counter - 1 : 0]['chapterContents'][] = 
            [
              'belongsToChapter' => $chapter_counter,
              'paragraphNumber' => $paragraph_counter, 
              'paragraph' => $paragraph,
              'wordCount' => str_word_count($paragraph),
            ];

      }
    } #foreach

    # Return the array as... an array
    return $file_contents_formatted;

  } #buildarray

} #class View


class Metadata {

  protected $metadata = [];

  function __construct ($filename) {
    # Grab the contents of the json file as a php array.
    # The second parameter "true" on json_decode does this.
    $metadata_from_json = json_decode(file_get_contents($filename . '.json'), true);

    # dump the remaining files into the protected variable $file_list
    $this->setMetadata($metadata_from_json);

  } #constructor

  # Setter for $this->metadata
  # Again, this is protected as it doesn't need to be accessed
  # from outside of this class, unlike the getter below.
  protected function setMetadata($metadata) {
    
    # check if metadata is an array, we don't really want to pass an object
    switch(is_array($metadata)) {
      case true:
        $this->metadata = $metadata;
        break;

    } #switch
  } #setMetadata


  # getter for metadata bits and bobs
  public function getMetadata($search_string) {
    
    # Loop through the first array level [metadata]
    foreach($this->metadata['metadata'] as $array) {

      # Loop through the second metadata's children
      foreach($array as $key => $val) {

        # Check for the $search_string key and return its value
        switch($key === $search_string) {
          case true:
            return $val;
            break;

        } #switch
      } #foreach2
    } #foreach1
  } #getMetadata

} #class Metadata


?>