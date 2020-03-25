<?php
session_start();

//allowed file MIME types
// Choose more from https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Complete_list_of_MIME_types
$arr_file_types = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'application/pdf', 'application/epub+zip', 'audio/mpeg'];
 
if (!(in_array($_FILES['file']['type'], $arr_file_types))) {
    echo "type";
    return;
}

// create the path if not exists
$path= '../files/'.$_SESSION['id'];
if (!file_exists($path)) {
    // recursively create the full path
    // https://stackoverflow.com/a/15012257/2365231
    mkdir($path, 0777, true);
}

//Check if File's already been uploaded
if (file_exists("../files/".$_SESSION['id']."/".($_FILES["file"]["name"]))) {
    echo 'exists';
    return;
// If it's not uploaded , then :
} else {
    //Then Save it that user's user id folder!
    
    move_uploaded_file($_FILES['file']['tmp_name'], $path.'/'. $_FILES['file']['name']);
    
    // echo "Upload: ".$_FILES["file"]["name"]."<br>";
    // echo "Type: ".$_FILES["file"]["type"]."<br>";
    // echo "Size: ".($_FILES["file"]["size"] / 1048576)." MB <br>";
    // echo "Temp file:". $_FILES["file"]["tmp_name"]."<br>";
    echo "Stored in: <a href='files/".$_SESSION['id']."/". rawurlencode($_FILES["file"]["name"])."' target='_blank'>files/".$_SESSION['id'] ."/". $_FILES["file"]["name"]."</a> <input type='hidden' name='files' id='files' value='files/".$_SESSION['id'] ."/". $_FILES["file"]["name"]."'>";
}