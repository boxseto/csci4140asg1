<?php
session_start();
require('vendor/autoload.php');
$s3 = new Aws\S3\S3Client([
    'version'  => '2006-03-01',
    'region'   => 'ap-southeast-1',
]);
$bucket = getenv('S3_BUCKET');
$access = isset($_REQUEST['access']) ? htmlspecialchars($_REQUEST['access']) : 'public';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['image']['tmp_name'])){
    if (mime_content_type($_FILES['image']['tmp_name']) == 'image/png' ||
        mime_content_type($_FILES['image']['tmp_name']) == 'image/jpeg' ||
        mime_content_type($_FILES['image']['tmp_name']) == 'image/gif'){

         //insert image
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
         $q = "INSERT INTO image (name, creator, access, time) VALUES (?,?,?,now())";
         $sql = $conn->prepare($q);
         $sql->execute([rand().rand(), $_SESSION['user'], $access]);

         $file_tmp = $_FILES['image']['tmp_name'];
         $file_ext = strtolower(end(explode('.',$_FILES['image']['name'])));
         $expensions = array("jpg", "gif", "png");
         if(in_array($file_ext, $expensions) == true){
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
        echo $imagick->getImageBlob();
    }else if($_REQUEST['effect'] == 'lomo'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'lomo', time()+60*60*24*30 , "/");
        $imagick->gammaImage(0.5);
        $imagick->modulateImage(100, 70, 100);
        $pixels = $imagick->getImageWidth() * $imagick->getImageHeight();
        $imagick->linearStretchImage(0.3*$pixels, 0.2*$pixels);
        header("Content-Type: imag/jpg");
        echo $imagick->getImageBlob();
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
        header("Content-Type: imag/jpg");
        echo $imagick2->getImageBlob();
    }else if($_REQUEST['effect'] == 'bw'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'bw', time()+60*60*24*30 , "/");
        $imagick->modulateImage(100, 0, 100);
        header("Content-Type: imag/jpg");
        echo $imagick->getImageBlob();
    }else if($_REQUEST['effect'] == 'blur'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'blur', time()+60*60*24*30 , "/");
        $imagick->blurImage(100, 2);
        header("Content-Type: imag/jpg");
        echo $imagick->getImageBlob();
    }else{
        setcookie('error', 'I dont know what are you doing.', time()+60*5 , "/");
        //header('Location: index.php');
    }
}
if(isset($_REQUEST['config'])){
    if($_REQUEST['config'] == 'save'){
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
        $q = "Update image set temp = 0 WHERE name=\"". $COOKIE['filename'] ."\"";
        $sql = $conn->prepare($q);
        $sql->execute();
        //copy effect
        $imagick = new \Imagick(realpath($_COOKIE['filename']));
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
        $imagick->writeImage(realpath($_COOKIE['filename']));
        rename("img/temp/".$_COOKIE['filename'], "img/upload/".$_COOKIE['filename']);
        header('Location: final.php');
    }else if($_REQUEST['config'] == 'discard'){
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
        $q = "DELETE FROM image WHERE name=\"" . $_COOKIE['filename'] . "\"";
        $sql = $conn->prepare($q);
        $sql->execute();
        unlink(realpath("img/tmp/".$_COOKIE['filename']));
        header('Location: index.php');
    }else if($_REQUEST['config'] == 'cancel'){
        setcookie('lasteffect', $_COOKIE['effect'], time()+60*60*24*30 , "/");
        setcookie('effect', 'blur', time()+60*60*24*30 , "/");
        echo '<img src="img/tmp/'.$_COOKIE['filename'].'"><br>';
    }else{
        $_SESSION['error'] = 'I dont know what are you doing.';
        header('Location: index.php');
    }
}

//display image
    echo '<p>effect</p><br>';
    echo '<a href="editor.php?effect=border">Border</a><br>'.
    '<a href="editor.php?effect=lomo">Lomo</a><br>'.
    '<a href="editor.php?effect=lf"></a>Lens Flare<br>'.
    '<a href="editor.php?effect=bw">Black White</a><br>'.
    '<a href="editor.php?effect=blur">Blur</a><br>'.
    '<br><br><p>Save changes</p><br>'.
    '<a href="editor.php?congfig=save">Save</a><br>'.
    '<a href="editor.php?config=discard">Discard</a><br>'.
    '<a href="editor.php?config=cancel">Cancel change</a><br>';

?>
