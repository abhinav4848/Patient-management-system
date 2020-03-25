<?php
session_start();
$error="";

if (array_key_exists("id", $_SESSION)) {
    include('includes/connect-db.php');
} else {
    header("Location: index.php?redirect=".$_SERVER['REQUEST_URI']);
}

if (array_key_exists("id", $_GET) or array_key_exists("op_number", $_GET)) {
    if (array_key_exists("id", $_GET)) {
        $query = "SELECT * FROM `dhruv_patient_bio` WHERE id=".mysqli_real_escape_string($link, $_GET['id'])." LIMIT 1";
    } elseif (array_key_exists("op_number", $_GET)) {
        $query = "SELECT * FROM `dhruv_patient_bio` WHERE op_number='".mysqli_real_escape_string($link, $_GET['op_number'])."' LIMIT 1";
    }

    $result = mysqli_query($link, $query) or die(mysql_error());

    if (mysqli_num_rows($result)!=0) {
        $row = mysqli_fetch_array($result);
    } else {
        echo "Patient doesn't exist.";
        die();
    }
} else {
    echo "You didn't specify a patient";
    die();
}

if (array_key_exists("submit", $_POST) and $_POST['submit']=='updateEntry') {
    if ($_POST['name']=='') {
        $error.='Enter a Name. ';
    }
    if ($_POST['op_number']=='' or $_POST['op_number']==0 or strlen($_POST['op_number'])<=3) {
        $error.='OP Number is empty. ';
    }

    //check to see that the op number is unique.
    $query_check_unique = "SELECT * FROM `dhruv_patient_bio` WHERE op_number = '".mysqli_real_escape_string($link, $_POST['op_number'])."' LIMIT 1";
    $result_check_unique = mysqli_query($link, $query_check_unique);
    $row_check_unique = mysqli_fetch_array($result_check_unique);

    if (mysqli_num_rows($result_check_unique)>0) {
        // if there was a successful match for the provided patient OP number
        if ($row_check_unique['id']!=$row['id']) {
            //check id of the match and if it's not the same patient we're editing already
            $error.= 'That OP number already exists and belongs to '.$row_check_unique['name'];
        }
    }
    
    if ($error=='') {
        $query = "UPDATE `dhruv_patient_bio` SET 
        name = '".mysqli_real_escape_string($link, $_POST['name'])."',
        age = '".mysqli_real_escape_string($link, $_POST['age'])."',
        sex = '".mysqli_real_escape_string($link, $_POST['sex'])."',
        phone = '".mysqli_real_escape_string($link, $_POST['phone'])."',
        op_number = '".mysqli_real_escape_string($link, $_POST['op_number'])."',
        doctor_id = '".mysqli_real_escape_string($link, $_POST['doctor_id'])."',
        files = '".mysqli_real_escape_string($link, $_POST['files'])."',
        comments = '".mysqli_real_escape_string($link, $_POST['comments'])."'
        WHERE op_number = '".mysqli_real_escape_string($link, $_GET['op_number'])."' LIMIT 1";
    
        if (mysqli_query($link, $query)) {
            $query_update_procedures = "UPDATE `dhruv_procedures` SET 
                patient_op_number = '".mysqli_real_escape_string($link, $_POST['op_number'])."'
                WHERE patient_op_number = '".$row['op_number']."'";

            if (mysqli_query($link, $query_update_procedures)) {
                header("Location: view.php?op_number=".urlencode($_POST['op_number'])."&successEdit=1");
            } else {
                echo '<div id="tablediv">';
                echo "failed to update the procedure OP numbers. FATAL ERROR. ALL procedures detached from patient Records";
                echo $query_update_procedures;
                echo '</div>';
            }
        } else {
            echo '<div id="tablediv">';
            echo "failed to update the Patient entry.";
            echo $query;
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
        <h1>Update patient details</h1>
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
                            } else {
                                echo $row['name'];
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
                              } else {
                                  echo $row['age'];
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
                              } else {
                                  echo $row['sex'];
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
                              } else {
                                  echo $row['phone'];
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
                                  echo $row['op_number'];
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
                                  echo $row['doctor_id'];
                              }
                              ?>">
                            </div>

                            <?php
                            $query_doctor_name = "SELECT * FROM `dhruv_users` WHERE id=".mysqli_real_escape_string($link, $row['doctor_id'])." LIMIT 1";
                            $result_doctor_name = mysqli_query($link, $query_doctor_name);
                            $row_doctor_name = mysqli_fetch_assoc($result_doctor_name);
                            
                            echo '<small>Dr. '.$row_doctor_name['name'].'. (ID#: '.$row['doctor_id'].') Phone: '.$row_doctor_name['phone'].'</small>';
                            ?>
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
                              } else {
                                  echo $row['comments'];
                              }
                              ?></textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Upload Image</td>
                        <td>
                            <div class="form-group">
                                <!-- The whole div below gets replaced by the ajax returned line in case of successful upload -->
                                <div class="output mb-1">

                                    <!-- Choose File -->
                                    <input type="file" name="image" class="image">
                                    <!-- Upload Button -->
                                    <button class="btn btn-primary btn-sm upload mb-1">Upload</button>
                                    <!-- Progress Bar -->
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 0%" role="progressbar" id="progress">
                                        </div>
                                    </div>

                                    <!-- Hidden Filed containing url of file. Gets replaced with ajax returned file value when successful file upload occurs -->
                                    <?php
                                        if ($row['files']!='' or $row['files']!= null) {
                                            echo '<span class="text-danger bold">WARNING!</span> Previously uploaded file: <a class="badge badge-warning" href="'.$row['files'].'" target="_blank">'.$row['files'].'</a>. If you upload a new file, AND click Submit, this file will get replaced.';
                                            echo '<input type="hidden" name="files" id="files" value="'.$row['files'].'">';
                                        } else {
                                            echo 'No File was uploaded previously';
                                            echo '<input type="hidden" name="files" id="files" value="">';
                                        }
                                    ?>

                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <button type="submit" name="submit" class="btn btn-primary" value="updateEntry">Update
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


    <script>
    $(function() {
        $('.upload').on('click', function(e) {
            e.preventDefault();
            var file_data = $('.image').prop('files')[0];

            if (file_data != undefined) {
                var form_data = new FormData();
                form_data.append('file', file_data);
                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);

                                document.getElementById("progress").style.width =
                                    percentComplete + '%';

                                if (percentComplete === 100) {
                                    document.getElementById("progress").style
                                        .width = '0%';
                                }
                            }
                        }, false);
                        return xhr;
                    },
                    type: 'POST',
                    url: 'includes/ajax-upload-receiver.php',
                    contentType: false,
                    processData: false,
                    data: form_data,
                    success: function(response) {
                        if (response == 'type') {
                            alert('Invalid file type');
                        } else if (response == 'exists') {
                            alert('File already exists');
                        } else {
                            $(".output").html("<p class='mb-0'>" + response + "</p>");
                        }

                        $('.image').val('');
                    }
                });
            }
            // allow the form to continue submitting
            return true;
        });
    });
    </script>
</body>

</html>