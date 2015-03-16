<?php

// increase script timeout value
ini_set('max_execution_time', 5000);

$folder = dirname(__FILE__ );

function show($str){
   echo $str . "<br/>\n";
   flush();
   ob_flush();
}
show($folder);

$date = getdate();
$splitNum = 0;

$archive = $folder."/temporary/backup_" . $date[0];
$currentArchive = $archive . "_" . $splitNum . ".zip";

$zip = new ZipArchive();
if ($zip->open($currentArchive, ZIPARCHIVE::CREATE) !== TRUE) {
    die ("Could not open archive");
}

$numFiles = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("./"));
foreach ($iterator as $key=>$value){
   $numFiles += 1;
}
show( "Will backup $numFiles to $archive.zip" );

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("./"));
$numFiles = 0;
$counter = 0;
$maxFilePerArchive = 500;
foreach ($iterator as $key=>$value){
   $counter += 1;
   if ($counter >= $maxFilePerArchive) {
      $currentArchive = $archive . "_" . $splitNum++ . ".zip";
      show( "Too many files: splitting archive, new archive is $currentArchive" ); 
      $zip->close();
      $zip = new ZipArchive();
      if ($zip->open($currentArchive, ZIPARCHIVE::CREATE) !== TRUE) {
          die ("Could not open archive");
      }
      $counter = 0;
   }
   //$i = $maxFilePerArchive*$splitNum + $counter; 
   if (! preg_match('/temporary\/backup_' . $date[0] . '/', $key)){
      $zip->addFile(realpath($key), $key) or die ("ERROR: Could not add file: $key");
      $numFiles += 1;
      if ($numFiles % 300 == 0) {
         show( "$numFiles" );
      }
   } else {
      show( "Not backuping this file -> $key" );
   }
}
// close and save archive
$zip->close();
show( "Archive created successfully with $numFiles files." );

?>
