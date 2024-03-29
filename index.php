<?php 
ini_set('display_errors', 1);
set_time_limit(100000);
ini_set('default_socket_timeout', 3);
require("config.php");

$serviceArray = array();
$bouquets = array();
$ftp_login = false;

function clean($string) {
    $string = strtolower($string);
    $search = array("+","*","&","ä","Ä","ö","Ö","ü","Ü", "ß", "《", "》", ":", "-", " ");
    $replace = array("plus","star","and","a","A","o","O","u","U","","","","", "", "");
    $string = str_replace($search, $replace, $string);
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
 }

 function resizePicon($originalImage){
    
    $suffix = strrchr($originalImage, ".");

    if($suffix == ".jpg" || $suffix == ".jpeg" || $suffix == ".webp"){    
        if($suffix == ".jpg" || $suffix == ".jpeg"){
            $image = imagecreatefromjpeg($originalImage);
        }else if($suffix == ".webp"){
            $image = imagecreatefromwebp($originalImage);
        }
        imagepng($image, rtrim($originalImage, $suffix) . ".png");
        unlink($originalImage);
        $originalImage = rtrim($originalImage, $suffix) . ".png";
    }


    $uncroppedImage = imagecreatefrompng($originalImage);
    
    $croppedImage = imagecropauto($uncroppedImage, IMG_CROP_DEFAULT);
    if($croppedImage!=false){
        imagepng($croppedImage, $originalImage,0);
    }
        
    $blankImage = imagecreatetruecolor(220, 132);
    imagesavealpha($blankImage, true);
    $color = imagecolorallocatealpha($blankImage, 0, 0, 0, 127);
    imagefill($blankImage, 0, 0, $color);

    list($width, $height, $type) = getimagesize($originalImage);
    $r_xpicon = 220/132;
    $r = $width / $height;
    $y_offset = 0;
    $x_offset = 0;
    if($r >= $r_xpicon){
        $new_width = 220;
        $new_height = 220/$r;
        $y_offset = (132-$new_height)/2;
    }else{
        $new_width = 132*$r;
        $new_height = 132;
        $x_offset = (222-$new_width)/2;
    }
    $newImage = imagecreatefrompng($originalImage);
    imagecopyresized($blankImage, $newImage, $x_offset, $y_offset, 0, 0, $new_width, $new_height, $width, $height);
    imagepng($blankImage, $originalImage);
    
 }

function connectFTP($server, $user, $pass, &$ftp_login){
    $ftp_conn = ftp_connect($server);
    if($ftp_conn == false ) {
        return;
    }

    if(ftp_login($ftp_conn, $user, $pass)){
        $ftp_login = true;
    };

    ftp_pasv($ftp_conn, true);
    ftp_set_option($ftp_conn, FTP_TIMEOUT_SEC, 100000);

    return $ftp_conn;
}


if(isset($_POST["clearPicons"])){
    $clearPiconsAtStart = true;
}else{
    $clearPiconsAtStart = false;
}
if(isset($_POST["uploadFTP"])){
    $uploadFTP = true;
}else{
    $uploadFTP = false;
}


$ftp_conn = connectFTP($ftp_server,$ftp_user, $ftp_pass, $ftp_login);

if($clearPiconsAtStart){
    //echo "Deleting all IPTV-Picons in /usr/share/enigma2/picon/...<br>";
    $files = ftp_nlist($ftp_conn, "/usr/share/enigma2/picon/");
    foreach ($files as $file)
    {
        if(strpos($file, "1_") == false) {
            ftp_delete($ftp_conn, $file);
        }
    
    }  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
  
    <style>
    
        .bg-primary{
            background-color: #4D1491 !important;
        }

        .btn-primary{
            background-color: #4D1491 !important;
            border-color: #4D1491 !important; 
        }

        .alert-console{
            background-color: #0c0c0c !important;
            font-size: .9em;
            min-height: 600px;
            max-height: 600px;

        }

        .form-check-input:checked{
            background-color: #4D1491 !important;
            border-color: #4D1491 !important; 
        }

        .list-group{
            max-height: 400px;
        }
    </style>
    <script>
        var checked = true;
        function toggleSelect(){
            listNodes = document.getElementsByName("live_categories[]");
            checked = !checked;

            listNodes.forEach(
                function(node, index){
                    node.checked = checked;
                }
            );
            
        }
    
    </script>
    <title>E2 Xtream Picons</title>
</head>
<body>
    <div class="container-fluid p-0 bg-light vh-100">

        <div class="container-fluid text-center p-2 bg-primary text-white">

            <h3>E2 ❌TREAM PICONS</h3>
            <p>
            <?php
                echo "<hr><p>";
                if($ftp_login){
                    $e2_info =  json_decode(file_get_contents("http://$ftp_server/api/about"), true);
                    $hardware = $e2_info['info']['brand'] . $e2_info['info']['model'];

                }

                if($ftp_login){
                    echo "FTP: $ftp_user@$ftp_server";
                    echo  " [$hardware] <br> ";

                }else{
                    echo '<span class="badge rounded-pill bg-danger">No FTP-Connection</span><br>';                

                }

                $xtream_info = json_decode(file_get_contents("$dns/player_api.php?username=$user&password=$pass", false), true);
                $account = "Xtream Account: "  . $xtream_info["user_info"]["status"] . " | Expires: " . date('d.m.Y', $xtream_info["user_info"]["exp_date"]);
                echo $account;
            ?>

            </p>
        </div>
        <div class="container bg-light py-2 px-3">

        <?php
        if($ftp_login){

            $userBouquets =  json_decode(file_get_contents("http://$ftp_server/api/bouquets"), true);
            
            foreach($userBouquets as $key => $value){
                foreach($value as $key2 => $value2){
                    $bouquets[] = $value2[1];
                }        
            }
        }
        if(!isset($_POST["generate_picons"]) || (isset($_POST["generate_picons"]) && !isset($_POST["live_categories"]))){
            $liveStreamCategories = json_decode(file_get_contents("$dns/player_api.php?username=$user&password=$pass&action=get_live_categories", false), true);
            echo '<h6 class="my-3">Select categories to generate picons for. Userbouquets from receiver are pre-selected.</h6>';
            echo '<button type="button" class="btn btn-primary btn-sm rounded-0" onclick="toggleSelect()">Select/Deselect All</button>';

            echo '<form action="index.php" method="post">';
            echo '<div class="list-group overflow-auto rounded-0">';
            //echo '<select class="form-control" name="live_categories[]" Size="20" multiple="multiple">';
            foreach($liveStreamCategories as $key => $value){
                if(array_search($value['category_name'], $bouquets) != false){
                    $checked = "checked";
                }else{
                    $checked = "";
                }
                //echo "<option " . $selected . " value=" . $value['category_id'] .">" . $value['category_name']. "</option>";
                echo '<label class="list-group-item">
                    <input ' . $checked . ' class="form-check-input me-1" type="checkbox" name="live_categories[]" value=' . $value['category_id'] .'>
                    '. $value['category_name'].'
                    </label>';
            }
            //echo "</select>";
            echo '
            </div>
            <div class="form-check mt-3">
            <label class="form-check-label">';
                    
                if($ftp_login){
                    $disabled = "";
                }else{
                    $disabled = "disabled";
                }
            echo '<input ' . $disabled . ' id="checkFTP" class="form-check-input" type="checkbox" name="uploadFTP" onclick="toggleClearCheckbox()"> Upload via FTP. If unchecked, Picons will be created in subfolder /picon            
            </label>
            </div>
            <div class="form-check mx-4 mb-3">
            <label class="form-check-label">
                <input id="checkClearPicons" class="form-check-input" type="checkbox" name="clearPicons" disabled> Clear all IPTV-Picons on receiver before uploading via FTP
            </label>

            </div>';
            echo '<input class="btn btn-primary rounded-0" name="generate_picons" value="Generate Picons" type="submit" formmethod="post">';
            echo '</form>';

        }else if(isset($_POST["generate_picons"]) && isset($_POST["live_categories"])){
            echo '<a id="loading_button" class="btn btn-primary rounded-0 disabled" href="index.php">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Generating...this may take some time...
          </a>';


            ob_flush();
            flush();
            $liveStreams = array();
            echo '<div class="mt-2"><p class="text-monospace alert alert-console text-white overflow-auto text-nowrap">';
            echo 'Log output<br>=============================================<br>';
            foreach($_POST["live_categories"] as $key => $value){
                $liveStreamsCategory = json_decode(file_get_contents("$dns/player_api.php?username=$user&password=$pass&action=get_live_streams&category_id=" . $value, false), true);
                $liveStreams = array_merge($liveStreams, $liveStreamsCategory);
            }


            //$liveStreams = json_decode(file_get_contents("$dns/player_api.php?username=$user&password=$pass&action=get_live_streams"), true);

            foreach($liveStreams as $key => $value){
                $displayname = clean($value["name"]);

                    echo "Getting picon for: " . $value["name"] . ": " . $value["stream_icon"] . "<br>";
                    if(isset($value["stream_icon"]) && $value["stream_icon"] != ""){

                        $picon = file_get_contents($value["stream_icon"], false);

                        if($picon != false){
                            $url = strtok($value["stream_icon"], "?");
                            $filename = strrchr($url, ".");
                            $filename = strtok($filename, "/");

                            $remotefile='/usr/share/enigma2/picon/' . $displayname . ".png";
                            $localfile='picon/' . $displayname . ".png";
                    
                            file_put_contents('picon/' . $displayname . $filename, $picon);

                            if (exif_imagetype("picon/" . $displayname . $filename) == 2) {
                                $newfilename = ".jpg";
                                rename('picon/' . $displayname . $filename, 'picon/' . $displayname . $newfilename);
                                $filename = $newfilename;
                            }else if (exif_imagetype("picon/" . $displayname . $filename) == 3) {
                                $newfilename = ".png";
                                rename('picon/' . $displayname . $filename, 'picon/' . $displayname . $newfilename);
                                $filename = $newfilename;
                            }


                            if (file_exists("picon/" . $displayname . $filename) && filesize("picon/" . $displayname . $filename) > 0) {
                                if (exif_imagetype("picon/" . $displayname . $filename) != false ) {
                                    resizePicon("picon/" . $displayname . $filename);
                                    if($uploadFTP){
                                        if(!ftp_nlist($ftp_conn, "/")){
                                            $ftp_conn = connectFTP($ftp_server,$ftp_user, $ftp_pass, $login);
                                        }
                                        ftp_put($ftp_conn, $remotefile, $localfile, FTP_BINARY);
                                    }
                                }
                                if($uploadFTP){
                                    unlink($localfile);
                                }
                                
                            }
                        }
                    } 
    
                    ob_flush();
                    flush();
                }

                echo "=============================================<br>Finished!";
                echo '</div>';
                echo '<script>let lb=document.getElementById("loading_button"); lb.removeChild(lb.children[0]);lb.classList.remove("disabled");lb.innerHTML="<< Back to channel list";</script>';    
            
            }
        ?>
        </div>

    </div>


    <script>
        function toggleClearCheckbox(){
            if(document.getElementById("checkFTP").checked){
                document.getElementById("checkClearPicons").disabled = "";
            }else{
                document.getElementById("checkClearPicons").checked = false;
                document.getElementById("checkClearPicons").disabled = "disabled";
            }
        }
    </script>
</body>
</html>