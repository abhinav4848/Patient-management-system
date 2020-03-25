<?php
session_start();
// add a new patient
include('includes/connect-db.php');
$error='';
if (array_key_exists("submit", $_POST) and $_POST['submit']=='addEntry') {
    if ($_POST['name']=='') {
        $error.='Enter a Name. ';
    }
    if ($_POST['op_number']=='' or $_POST['op_number']==0 or strlen($_POST['op_number'])<=3) {
        $error.='Enter a valid OP Number. ';
    }
    
    //check to see that the op number is unique.
    $query = "SELECT `id`, `name` FROM `dhruv_patient_bio` WHERE op_number = '".mysqli_real_escape_string($link, $_POST['op_number'])."' LIMIT 1";
    $result = mysqli_query($link, $query);
    if (mysqli_num_rows($result)>0) {
        $row = mysqli_fetch_array($result);
        $error.= 'That OP number already exists and belongs to '.$row['name'];
    }
    
    if ($error=='') {
        $query = "INSERT INTO `dhruv_patient_bio` (`name`, `age`, `sex`, `phone`, `op_number`, `doctor_id`, `comments`, `date_added`) 
			VALUES (
			'".mysqli_real_escape_string($link, $_POST['name'])."',
			'".mysqli_real_escape_string($link, $_POST['age'])."',
			'".mysqli_real_escape_string($link, $_POST['sex'])."',
			'".mysqli_real_escape_string($link, $_POST['phone'])."',
			'".mysqli_real_escape_string($link, $_POST['op_number'])."',
			'".mysqli_real_escape_string($link, $_POST['doctor_id'])."',
			'".mysqli_real_escape_string($link, $_POST['comments'])."',
			'".date('Y-m-d H:i:s')."');";
    
        if (mysqli_query($link, $query)) {
            $id = mysqli_insert_id($link);
            header("Location: view.php?id=".$id."&successEdit=1");
        } else {
            echo '<div id="tablediv">';
            echo "failed to insert the entry.";
            echo '</div>';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>Dashboard</title>
</head>

<body>
    <div class="container">
        <?php include('includes/nav.php'); ?>
        <?php
        if ($error!="") {
            echo '<div class="alert alert-danger" role="alert" id="alert">'.$error.'</div>';
        }?>
        <h1>Add new patient</h1>
        <form method="post">
            <table class="table table-responsive-xs mt-2">
                <thead class="thead-dark">
                    <tr>
                        <th>Patient Biodata</th>
                        <th>Values</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Name
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" id="name" value="<?php
                            if (array_key_exists('name', $_POST)) {
                                echo $_POST['name'];
                            }
                            ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Age
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="number" class="form-control" name="age" id="age" value="<?php
                              if (array_key_exists('age', $_POST)) {
                                  echo $_POST['age'];
                              }
                              ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Sex
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" maxlength="1" class="form-control" name="sex" id="sex" value="<?php
                              if (array_key_exists('sex', $_POST)) {
                                  echo $_POST['sex'];
                              }
                              ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Phone
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="number" class="form-control" name="phone" id="phone" value="<?php
                              if (array_key_exists('phone', $_POST)) {
                                  echo $_POST['phone'];
                              }
                              ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            OP Number
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" class="form-control" name="op_number" id="op_number" value="<?php
                              if (array_key_exists('op_number', $_POST)) {
                                  echo $_POST['op_number'];
                              } else {
                                  echo date('y').'/';
                              }
                              ?>">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Doctor ID
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" class="form-control" name="doctor_id" id="doctor_id" readonly value="<?php
                              if (array_key_exists('doctor_id', $_POST)) {
                                  echo $_POST['doctor_id'];
                              } else {
                                  echo $_SESSION['id'];
                              }
                              ?>">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Comments
                        </td>
                        <td>
                            <div class="form-group">
                                <textarea class="form-control" name="comments" id="comments"><?php
                              if (array_key_exists('comments', $_POST)) {
                                  echo $_POST['comments'];
                              }
                              ?></textarea>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <button type="submit" name="submit" class="btn btn-primary" value="addEntry">Add
                                Entry</button>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </form>



    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    </script>
</body>

</html>