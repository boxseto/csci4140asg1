<?php
session_start();
require('vendor/autoload.php');
$s3 = new Aws\S3\S3Client([
    'version'  => '2006-03-01',
    'region'   => 'ap-southeast-1',
]);
$bucket = getenv('S3_BUCKET');
$db = parse_url(getenv("DATABASE_URL"));
$dbpath = ltrim($db["path"], "/");
$conn = new PDO("pgsql:" . sprintf(
"host=%s;port=%s;user=%s;password=%s;dbname=%s",
    $db["host"],
    $db["port"],
    $db["user"],
    $db["pass"],
    $dbpath
    ));
$access = isset($_REQUEST['access']) ? htmlspecialchars($_REQUEST['access']) : 'public';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['image']['tmp_name'])){
    if (mime_content_type($_FILES['image']['tmp_name']) == 'image/png' ||
        mime_content_type($_FILES['image']['tmp_name']) == 'image/jpeg' ||
        mime_content_type($_FILES['image']['tmp_name']) == 'image/gif'){

         //insert image
         $q = "INSERT INTO image (name, creator, access, time) VALUES (?,?,?,now())";
         $sql = $conn->prepare($q);
         $sql->execute([rand().rand(), $_SESSION['user'], $access]);

         $file_tmp = $_FILES['image']['tmp_name'];
         $file_ext = strtolower(end(explode('.',$_FILES['image']['name'])));
         $expensions = array("jpg", "gif", "png");
         if(in_array($file_ext, $expensions) == true){
             $mime_ext = strtolower(end(explode('/',mime_content_type($_FILES['image']['tmp_name']))));
             if( $mime_ext == $file_ext || (($mime_ext == 'jpeg') && ($file_ext == 'jpg')) ){
               echo 'image is ok';
             }
             $pid = $conn->lastInsertId();
             $file_name = rand().rand() . '.' . $file_ext;
             $q = "UPDATE image SET name=? WHERE id=?";
             $sql = $conn->prepare($q);
             $sql->execute([$file_name, $pid]);
             try{
                $upload = $s3->upload($bucket, $file_name, fopen($file_tmp, 'rb'), 'public-read');
               $filepath = htmlspecialchars($upload->get('ObjectURL'));
             }catch(Exception $e){echo 'Cannot upload';}
         }else{
             setcookie('error', 'file format different.', time()+60*5 , "/");
             header('Location: index.php');
         }

         setcookie('effect', 'none', time()+60*60*24*30 , "/");
         setcookie('filename',$file_name, time()+60*60*24*30 , "/");
         setrawcookie('filepath', rawurlencode($filepath), time()+60*60*24*30 , "/"); 
         setcookie('lasteffect', 'none', time()+60*60*24*30 , "/");
         setcookie('filetype', $mime_ext, time()+60*60*24*30 , "/");
         echo '<img src="'.$filepath.'"><br>';
    }else{
        echo 'receieved type: ' . mime_content_type($_FILES['image']['tmp_name']);
        setcookie('error', 'file format different.', time()+60*5 , "/");
        header('Location: index.php');
    }
}
if(isset($_REQUEST['effect'])){
    $imagick = new \Imagick();
    $image = file_get_contents($_COOKIE['filepath']);
    $imagick -> readImageBlob($image);

    if($_REQUEST['effect'] == 'border'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'border', time()+60*60*24*30, "/");
        $imagick->borderImage('black', 10, 10);
        echo '<img src="data:image/' . $_COOKIE['filetype'] . ';base64,'.base64_encode($imagick->getImageBlob()).'"/>';
    }else if($_REQUEST['effect'] == 'lomo'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'lomo', time()+60*60*24*30 , "/");
        $imagick->gammaImage(0.5);
        $imagick->modulateImage(100, 70, 100);
        $pixels = $imagick->getImageWidth() * $imagick->getImageHeight();
        $imagick->linearStretchImage(0.3*$pixels, 0.2*$pixels);
        echo '<img src="data:image/' . $_COOKIE['filetype'] . ';base64,'.base64_encode($imagick->getImageBlob()).'"/>';
    }else if($_REQUEST['effect'] == 'none'){
        echo '<img src="data:image/' . $_COOKIE['filetype'] . ';base64,'.base64_encode($imagick->getImageBlob()).'"/>';
    }else if($_REQUEST['effect'] == 'lf'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'lf', time()+60*60*24*30 , "/");
        $imagick2 = new \Imagick();
        $imagick2->readImage(realpath("img/protected/flare.png"));
        $imagick2->resizeimage(
          $imagick->getImageWidth(),
          $imagick->getImageHeight(),
          \Imagick::FILTER_LANCZOS,
          1);
        $opacity = new \Imagick();
        $opacity->newPseudoImage(
          $imagick2->getImageHeight(),
          $imagick2->getImageWidth(),
          "gradient:gray(10%)-gray(90%)"
        );
        $opacity->rotateimage('black', 90);
        $imagick->compositeImage($opacity, \Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $imagick2->compositeImage($imagick, \Imagick::COMPOSITE_ATOP, 0, 0);
        echo '<img src="data:image/' . $_COOKIE['filetype'] . ';base64,'.base64_encode($imagick->getImageBlob()).'"/>';
    }else if($_REQUEST['effect'] == 'bw'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'bw', time()+60*60*24*30 , "/");
        $imagick->modulateImage(100, 0, 100);
        echo '<img src="data:image/' . $_COOKIE['filetype'] . ';base64,'.base64_encode($imagick->getImageBlob()).'"/>';
    }else if($_REQUEST['effect'] == 'blur'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'blur', time()+60*60*24*30 , "/");
        $imagick->blurImage(100, 2);
        echo '<img src="data:image/' . $_COOKIE['filetype'] . ';base64,'.base64_encode($imagick->getImageBlob()).'"/>';
    }else{
        setcookie('error', 'I dont know what are you doing.', time()+60*5 , "/");
        header('Location: index.php');
    }
}
if(isset($_REQUEST['config'])){
    if($_REQUEST['config'] == 'save'){
        echo 'process save query';
        $q = "Update image set temp = 0 WHERE name=?";
        $sql = $conn->prepare($q);
        $sql->execute([$COOKIE['filename']]);
      echo 'process save query end';
        //copy effect
        $imagick = new \Imagick();
        $image = file_get_contents($_COOKIE['filepath']);
        $imagick -> readImageBlob($image);
        if($_COOKIE['effect'] == 'border'){
            setcookie('effect', 'border', time()+60*60*24*30 , "/");
            $imagick->borderImage('black', 10, 10);
        }else if($_COOKIE['effect'] == 'lomo'){
            setcookie('effect', 'lomo', time()+60*60*24*30 , "/");
            $imagick->gammaImage(0.5);
            $imagick->modulateImage(100, 70, 100);
            $pixels = $imagick->getImageWidth() * $imagick->getImageHeight();
            $imagick->linearStretchImage(0.3*$pixels, 0.2*$pixels);
        }else if($_COOKIE['effect'] == 'lf'){
            setcookie('effect', 'lf', time()+60*60*24*30 , "/");
            $imagick2 = new \Imagick();
            $imagick2->readImage(realpath("img/protected/flare.png"));
            $imagick2->resizeimage(
              $imagick->getImageWidth(),
              $imagick->getImageHeight(),
              \Imagick::FILTER_LANCZOS,
              1);
            $opacity = new \Imagick();
            $opacity->newPseudoImage(
              $imagick2->getImageHeight(),
              $imagick2->getImageWidth(),
              "gradient:gray(10%)-gray(90%)"
            );
            $opacity->rotateimage('black', 90);
            $imagick->compositeImage($opacity, \Imagick::COMPOSITE_COPYOPACITY, 0, 0);
            $imagick2->compositeImage($imagick, \Imagick::COMPOSITE_ATOP, 0, 0);
        }else if($_COOKIE['effect'] == 'bw'){
            setcookie('effect', 'bw', time()+60*60*24*30 , "/");
            $imagick->modulateImage(100, 0, 100);
        }else if($_COOKIE['effect'] == 'blur'){
            setcookie('effect', 'blur', time()+60*60*24*30 , "/");
            $imagick->blurImage(100, 2);
        }
        //delete origional file in s3
        echo 'deleting object';
        try{
           $s3->deleteObject([
             'Bucket' => $bucket,
             'Key'    => $_COOKIE['filename']
             ]);
        }catch(Exception $e){echo 'Cannot delete';}
        //write image
        echo 'recreating temp object: ';
        $tmp = tempnam('/tmp', 'upload_');
        echo $tmp;
        echo 'writing image to temp object       ';
        $imagick->writeImage($tmp);
        echo var_dump($tmp);
        echo '      s3 create object';
        /*$result = $s3->create_object($bucket, $_COOKIE['filename'],array(
            'fileUpload' => $tmp,
            'acl' => AmazonS3::ACL_PUBLIC,
            'contentType' => 'image/' . $_COOKIE['filetype'],
            ));
        */    
        try{
          $upload = $s3->upload($bucket, $_COOKIE['filename'], fopen($tmp, 'rb'), 'public-read');
          echo htmlspecialchars($upload->get('ObjectURL'));
        }catch(Exception $e){echo $e->getMessage();}
        echo 's3 create object finished';
        //header('Location: final.php');
    }else if($_REQUEST['config'] == 'discard'){
        $q = "DELETE FROM image WHERE name='" . $_COOKIE['filename'] . "'";
        $sql = $conn->prepare($q);
        $sql->execute();
        //delete file in s3
        try{
           $s3->deleteObject([
             'Bucket' => $bucket,
             'Key'    => $_COOKIE['filename']
             ]);
        }catch(Exception $e){echo 'Cannot delete';}
        header('Location: index.php');
    }else if($_REQUEST['config'] == 'cancel'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'none', time()+60*60*24*30 , "/");
        header('Location: editor.php?effect=none');
    }else{
        $_SESSION['error'] = 'I dont know what are you doing.';
        header('Location: index.php');
    }
}

//display image
    echo '<p>effect</p><br>';
    echo '<a href="editor.php?effect=border">Border</a><br>'.
    '<a href="editor.php?effect=lomo">Lomo</a><br>'.
    '<a href="editor.php?effect=lf">Lens Flare</a><br>'.
    '<a href="editor.php?effect=bw">Black White</a><br>'.
    '<a href="editor.php?effect=blur">Blur</a><br>'.
    '<br><br><p>Save changes</p><br>'.
    '<a href="editor.php?config=save">Save</a><br>'.
    '<a href="editor.php?config=discard">Discard</a><br>'.
    '<a href="editor.php?config=cancel">Cancel change</a><br>';

?>
