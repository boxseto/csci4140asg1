<?php
session_start();
$access = isset($_REQUEST['access']) ? htmlspecialchars($_REQUEST['access']) : 'public';
if(isset($_FILES['image'])){
    if (mime_content_type($_FILES['image']) == 'image/png' ||
        mime_content_type($_FILES['image']) == 'image/jpeg' ||
        mime_content_type($_FILES['image']) == 'image/gif'){

         //insert image
         $conn = new mysqli("localhost", "user", "user", "CSCI4140");
         $q = "INSERT INTO image (name, creator, access, time) VALUES (?,?,?,now())";
         $sql = $conn->prepare($q);
         $sql->bind_param('sss', $_FILES['image']['name'], $_SESSION['user'], $access);
         $sql->execute();

         $file_tmp = $_FILES['image']['tmp_name'];
         $file_ext = strtolower(end(explode('.',$_FILES['image']['name'])));
         $expensions = array("jpg", "gif", "png");
         if(in_array($file_ext, $expensions) == true){
             $pid = $conn->insert_id;
             $file_name = $pid . '.' . $file_ext;
             $q = "UPDATE image SET name=? WHERE id=?";
             $sql = $conn->prepare($q);
             $sql->bind_param('si', $filename, $pid);
             $sql->execute();
             move_uploaded_file($file_tmp, "img/temp/" . $file_name);
         }else{
             $_SESSION['error'] = 'file format different';
             header('Location: index.php');
         }

         $conn->close();

         setcookie('filename',$filename, NULL, NULL, NULL, NULL, TRUE);
         setcookie('effect', 'none', NULL, NULL, NULL, NULL, TRUE);
         setcookie('lasteffect', 'none', NULL, NULL, NULL, NULL, TRUE);
         echo '<img src=\"img/tmp/'.$filename.'\"><br>';
    }else{
        $_SESSION['error'] = 'file format different';
        header('Location: index.php');
    }


}
if(isset($_REQUEST['effect'])){
    $imagick = new \Imagick(realpath($_COOKIE['filename']));

    if($_COOKIE['effect'] == 'border'){
        setcookie('lasteffect', $_COOKIE['effect'], NULL, NULL, NULL, NULL, TRUE);
        setcookie('effect', 'border', NULL, NULL, NULL, NULL, TRUE);
        $imagick->borderImage('black', 10, 10);
        header("Content-Type: imag/jpg");
        echo $imagick->getImageBlob();
    }else if($_COOKIE['effect'] == 'lomo'){
        setcookie('lasteffect', $_COOKIE['effect'], NULL, NULL, NULL, NULL, TRUE);
        setcookie('effect', 'lomo', NULL, NULL, NULL, NULL, TRUE);
        $imagick->gammaImage(0.5);
        $imagick->modulateImage(100, 70, 100);
        $pixels = $imagick->getImageWidth() * $imagick->getImageHeight();
        $imagick->linearStretchImage(0.3*$pixels, 0.2*$pixels);
        header("Content-Type: imag/jpg");
        echo $imagick->getImageBlob();
    }else if($_COOKIE['effect'] == 'lf'){
        setcookie('lasteffect', $_COOKIE['effect'], NULL, NULL, NULL, NULL, TRUE);
        setcookie('effect', 'lf', NULL, NULL, NULL, NULL, TRUE);
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
    }else if($_COOKIE['effect'] == 'bw'){
        setcookie('lasteffect', $_COOKIE['effect'], NULL, NULL, NULL, NULL, TRUE);
        setcookie('effect', 'bw', NULL, NULL, NULL, NULL, TRUE);
        $imagick->modulateImage(100, 0, 100);
        header("Content-Type: imag/jpg");
        echo $imagick->getImageBlob();
    }else if($_COOKIE['effect'] == 'blur'){
        setcookie('lasteffect', $_COOKIE['effect'], NULL, NULL, NULL, NULL, TRUE);
        setcookie('effect', 'blur', NULL, NULL, NULL, NULL, TRUE);
        $imagick->blurImage(100, 2);
        header("Content-Type: imag/jpg");
        echo $imagick->getImageBlob();
    }else{
        $_SESSION['error'] = 'I dont know what are you doing.';
        header('Location: index.php');
    }
}
if(isset($_REQUEST['config'])){
    if($_REQUEST['config'] == 'save'){
        $conn = new mysqli("localhost", "user", "user", "CSCI4140");
        $q = "Update image set temp = 0 WHERE name=\"". $COOKIE['filename'] ."\"";
        $conn->query($q);
        //copy effect
        $imagick = new \Imagick(realpath($_COOKIE['filename']));
        if($_COOKIE['effect'] == 'border'){
            setcookie('effect', 'border', NULL, NULL, NULL, NULL, TRUE);
            $imagick->borderImage('black', 10, 10);
        }else if($_COOKIE['effect'] == 'lomo'){
            setcookie('effect', 'lomo', NULL, NULL, NULL, NULL, TRUE);
            $imagick->gammaImage(0.5);
            $imagick->modulateImage(100, 70, 100);
            $pixels = $imagick->getImageWidth() * $imagick->getImageHeight();
            $imagick->linearStretchImage(0.3*$pixels, 0.2*$pixels);
        }else if($_COOKIE['effect'] == 'lf'){
            setcookie('effect', 'lf', NULL, NULL, NULL, NULL, TRUE);
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
            setcookie('effect', 'bw', NULL, NULL, NULL, NULL, TRUE);
            $imagick->modulateImage(100, 0, 100);
        }else if($_COOKIE['effect'] == 'blur'){
            setcookie('effect', 'blur', NULL, NULL, NULL, NULL, TRUE);
            $imagick->blurImage(100, 2);
        }
        $imagick->writeImage(realpath($_COOKIE['filename']));
        rename("img/temp/"$_COOKIE['filename'], "img/upload/".$_COOKIE['filename']);
        header('Location: final.php');
    }else if($_REQUEST['config'] == 'discard'){
        $conn = new mysqli("localhost", "user", "user", "CSCI4140");
        $q = "DELETE FROM image WHERE name=\"" . $_COOKIE['filename'] . "\"";
        $conn->query($q);
        unlink(realpath("img/tmp/".$_COOKIE['filename']));
        header('Location: index.php');
    }else if($_REQUEST['config'] == 'cancel'){
        setcookie('lasteffect', $_COOKIE['effect'], NULL, NULL, NULL, NULL, TRUE);
        setcookie('effect', 'blur', NULL, NULL, NULL, NULL, TRUE);
        echo '<img src=\"img/tmp/'.$_COOKIE['filename'].'\"><br>'.
    }else{
        $_SESSION['error'] = 'I dont know what are you doing.';
        header('Location: index.php');
    }
}

//display image
    echo '<p>effect</p><br>'.
    echo '<a href=\"editor.php?effect=border\">Border</a><br>'.
    '<a href=\"editor.php?effect=lomo\">Lomo</a><br>'.
    '<a href=\"editor.php?effect=lf\"></a>Lens Flare<br>'.
    '<a href=\"editor.php?effect=bw\">Black White</a><br>'.
    '<a href=\"editor.php?effect=blur\">Blur</a><br>'.
    '<br><br><p>Save changes</p><br>'.
    '<a href=\"editor.php?congfig=save\">Save</a><br>'.
    '<a href=\"editor.php?config=discard\">Discard</a><br>'.
    '<a href=\"editor.php?config=cancel\">Cancel change</a><br>';

?>
