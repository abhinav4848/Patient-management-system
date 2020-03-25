<?php
session_start();
$error="";

if (array_key_exists("id", $_SESSION)) {
    include('includes/connect-db.php');
} else {
    header("Location: index.php?redirect=".$_SERVER['REQUEST_URI']);
}

if (array_key_exists("id", $_GET) and $_GET['id']!='' and is_numeric($_GET['id'])) {
    // make sure the provided procedure id is valid.
    // then find it
    $query = "SELECT * FROM `dhruv_procedures` WHERE id=".mysqli_real_escape_string($link, $_GET['id'])." LIMIT 1";
    $result = mysqli_query($link, $query) or die(mysql_error());

    if (mysqli_num_rows($result)!=0) {
        //only if the entry is found, proceed
        $row = mysqli_fetch_array($result);

        if ($row['doctor_id']==$_SESSION['id']) {
            // if correct doctor is editing, get all patient details
            $query_search_patient = "SELECT * FROM `dhruv_patient_bio` WHERE op_number='".$row['patient_op_number']."' LIMIT 1";
            $result_search_patient = mysqli_query($link, $query_search_patient);
            $row_search_patient = mysqli_fetch_array($result_search_patient);
        } else {
            $query_doctor_name = "SELECT * FROM `dhruv_users` WHERE id=".mysqli_real_escape_string($link, $row['doctor_id'])." LIMIT 1";
            $result_doctor_name = mysqli_query($link, $query_doctor_name);
            $row_doctor_name = mysqli_fetch_assoc($result_doctor_name);
            echo "Sorry, the procedure was done by <b>Dr. ".$row_doctor_name['name']." (ID#: ".$row['doctor_id'].")</b>. Request edit/deletion from them.<br />";

            echo 'Phone: '.$row_doctor_name['phone'];
            die();
        }
    } else {
        echo "Procedure entry doesn't exist.";
        die();
    }
} else {
    echo "You didn't specify a procedure id";
    die();
}

if (array_key_exists("submit", $_POST)) {
    //check for all errors before updating database
    if (!array_key_exists("procedure_done", $_POST) or $_POST['procedure_done']=='') {
        $error.='Please enter a procedure';
    }
    if (!array_key_exists("next_appointment", $_POST) or $_POST['next_appointment']=='') {
        // if next appointment not decided, enter same date as date of procedure done.
        $_POST['next_appointment'] = date('Y-m-d', strtotime($_POST['date_of_procedure']));
        $_POST['misc_details'].=' 
        (No Next appointment date was fixed.)';
    }

    if ($error=='') {
        $query = "UPDATE `dhruv_procedures` SET 
        date = '".mysqli_real_escape_string($link, $_POST['date_of_procedure'])."',
        procedure_done = '".mysqli_real_escape_string($link, $_POST['procedure_done'])."',
        misc_details = '".mysqli_real_escape_string($link, $_POST['misc_details'])."',
        next_appointment = '".mysqli_real_escape_string($link, date('Y-m-d', strtotime($_POST['next_appointment'])))."',
        files = '".mysqli_real_escape_string($link, $_POST['files'])."'
        WHERE id = '".mysqli_real_escape_string($link, $_GET['id'])."' LIMIT 1";
        
        if (mysqli_query($link, $query)) {
            header("Location: view.php?op_number=".$row['patient_op_number']."&successEdit=1");
        } else {
            echo '<div id="tablediv">';
            echo "failed to insert the entry.";
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

    <title>Update Procedure #<?=$row['id']?></title>
</head>

<body>
    <div class="container">
        <?php
        include('includes/nav.php');
        if ($error!="") {
            echo '<div class="alert alert-danger" role="alert" id="alert-error">'.$error.'</div>';
        }
        ?>
        <h2>Edit Procedure #<?=$row['id']?></h2>
        <button class="btn btn-outline-danger btn-sm float-right delete-procedure" value="delete"
            data-id="<?=$row['id']?>">X</button>
        <p class="text-muted">Patient Name:
            <?php echo '<a href="view.php?op_number='.urlencode($row_search_patient['op_number']).'">'.$row_search_patient['name'].'</a>';?>
        </p>

        <form method="post" class="card p-2">
            <div class="form-group">
                <label for="procedure_done">Procedure done</label>
                <input type="text" class="form-control" id="procedure_done" name="procedure_done" <?php
                if (array_key_exists('procedure_done', $_POST)) {
                    echo 'value="'.$_POST['procedure_done'].'"';
                } else {
                    echo 'value="'.$row['procedure_done'].'"';
                }
                ?>>
            </div>
            <div class="form-group">
                <label for="date_of_procedure">Date of Procedure</label>
                <input type="date" class="form-control" id="date_of_procedure" name="date_of_procedure" <?php
                if (array_key_exists('date_of_procedure', $_POST)) {
                    echo 'value="'.$_POST['date_of_procedure'].'"';
                } else {
                    echo 'value="'.date("Y-m-d", strtotime($row['date'])).'"';
                }
                ?>>
            </div>
            <div class="form-group">
                <label for="misc_details">Misc Details</label>
                <textarea type="date" class="form-control" rows="3" id="misc_details" name="misc_details"><?php
                if (array_key_exists('misc_details', $_POST)) {
                    echo $_POST['misc_details'];
                } else {
                    echo $row['misc_details'];
                }
                ?></textarea>
            </div>
            <div class="form-group">
                <label for="next_appointment">Next Appointment</label>
                <input type="date" class="form-control" id="next_appointment" name="next_appointment" <?php
                if (array_key_exists('next_appointment', $_POST)) {
                    echo 'value="'.$_POST['next_appointment'].'"';
                } else {
                    echo 'value="'.$row['next_appointment'].'"';
                }
                ?>>
            </div>

            <div class="form-group">
                <!-- The whole div below gets replaced by the ajax returned line in case of successful upload -->
                <div class="output mb-1">

                    <!-- Choose File -->
                    <input type="file" name="image" class="image">
                    <!-- Upload Button -->
                    <button class="btn btn-primary btn-sm upload mb-1">Upload</button>
                    <!-- Progress Bar -->
                    <div class="progress">
                        <div class="progress-bar" style="width: 0%" role="progressbar" id="progress"></div>
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

            <button type="submit" class="btn btn-primary" value="procedure_entry" name="submit">Submit</button>
        </form>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

    <script>
    $(".delete-procedure").click(function() {
        if (confirm("You Sure?")) {
            var id = $(this).attr("data-id");
            $.ajax({
                type: "POST",
                url: "delete.php",
                data: {
                    action: 'deleteProcedure',
                    id: id
                },
                success: function(result) {
                    if (result == 'success') {
                        document.location.href = "index.php";
                    } else {
                        alert(result);
                    }
                }
            })
        }
    })


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