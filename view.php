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

if (array_key_exists("submit", $_POST)) {
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
        $query = "INSERT INTO `dhruv_procedures` (`date`, `patient_op_number`, `procedure_done`, `misc_details`, `next_appointment`, `files`, `doctor_id`) 
			VALUES (
			'".mysqli_real_escape_string($link, date('Y-m-d', strtotime($_POST['date_of_procedure'])))."',
			'".mysqli_real_escape_string($link, $_POST['patient_op_number'])."',
			'".mysqli_real_escape_string($link, $_POST['procedure_done'])."',
			'".mysqli_real_escape_string($link, $_POST['misc_details'])."',
			'".mysqli_real_escape_string($link, date('Y-m-d', strtotime($_POST['next_appointment'])))."',
            '".mysqli_real_escape_string($link, $_POST['files'])."',
			'".mysqli_real_escape_string($link, $_SESSION['id'])."');";
    
        if (mysqli_query($link, $query)) {
            header("Location: view.php?op_number=".$row['op_number']."&successEdit=1");
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

    <title>Dashboard</title>

    <style>
    @media screen and (min-width: 480px) {
        img {
            margin: 0px 5px 5px 5px !important;
            border: 0px solid #dee2e6;
            width: 200px;
        }

        hr {
            width: 50%;
            margin-left: 0;
        }
    }

    @media screen and (max-width: 480px) {
        img {
            margin-bottom: 20px !important;
        }
    }
    </style>

</head>

<body>
    <div class="container">
        <?php include('includes/nav.php'); ?>
        <?php
        if ($error!="") {
            echo '<div class="alert alert-danger" role="alert" id="alert-error">'.$error.'</div>';
        }
        if (array_key_exists("successEdit", $_GET)) {
            echo '<div class="alert alert-success" role="alert" id="alert-success">Successfully Edited.</div>';
        }
        ?>
        <h1>Patient details</h1>

        <div class="card mb-2 w-100 border-primary">
            <div class="card-body">
                <?php
                    if ($row['files']!='') {
                        echo '<img src="'.$row['files'].'" class="img-thumbnail float-right" alt="Patient Image">';
                    } else {
                        echo '<p class="small text-muted">No Image Uploaded. Add one from the edit option</p>';
                    }
                ?>
                <!-- Edit and Delete Buttons -->
                <div class="float-right">
                    <a class="btn btn-outline-primary btn-sm"
                        href="edit-patient.php?op_number=<?=urlencode($row['op_number'])?>">Edit
                    </a>
                    <a href="#" class="btn btn-outline-danger btn-sm delete-patient"
                        data-opNumber="<?=urlencode($row['op_number'])?>">X</a>
                </div>
                <!-- Patient Biodata -->
                <h5 class="card-title">OP# <span class="text-danger"><?=$row['op_number']?></span></h5>
                <p class="card-text">
                    <b>Name</b>: <?=htmlentities($row['name'])?><br />
                    <b>Age</b>: <?=$row['age']?><br />
                    <b>Sex</b>: <?=$row['sex']?><br />
                    <b>Phone</b>: <?=$row['phone']?><br />
                    <b>Date Added</b>: <?=date("d-M-Y h:i:sa", strtotime($row['date_added']))?><br />
                </p>
                <hr>
                <p class="card-text">
                    <b>Comments:</b><br />
                    <?=htmlentities($row['comments'])?>
                </p>
            </div>
        </div>

        <h2>Add Procedure</h2>
        <form method="post" class="card p-2">
            <input type="hidden" name="patient_op_number" id="patient_op_number" name="patient_op_number"
                value="<?=$row['op_number']?>">
            <div class="form-group">
                <label for="procedure_done">Procedure done</label>
                <input type="text" class="form-control" id="procedure_done" name="procedure_done" <?php
                if (array_key_exists('procedure_done', $_POST)) {
                    echo 'value="'.$_POST['procedure_done'].'"';
                }
                ?>>
            </div>
            <div class="form-group">
                <label for="date_of_procedure">Date of Procedure</label>
                <input type="date" class="form-control" id="date_of_procedure" name="date_of_procedure" <?php
                if (array_key_exists('date_of_procedure', $_POST)) {
                    echo 'value="'.$_POST['date_of_procedure'].'"';
                } else {
                    echo 'value="'.date("Y-m-d", strtotime('today')).'"';
                }
                ?>>
            </div>
            <div class="form-group">
                <label for="misc_details">Misc Details</label>
                <textarea type="date" class="form-control" rows="3" id="misc_details" name="misc_details"><?php
                if (array_key_exists('misc_details', $_POST)) {
                    echo $_POST['misc_details'];
                } else {
                    echo 'Uneventful';
                }
                ?></textarea>
            </div>
            <div class="form-group">
                <label for="next_appointment">Next Appointment</label>
                <input type="date" class="form-control" id="next_appointment" name="next_appointment" <?php
                if (array_key_exists('next_appointment', $_POST)) {
                    echo 'value="'.$_POST['next_appointment'].'"';
                }
                ?>>
            </div>
            <div class="form-group">
                <!-- The whole div below gets replaced by the ajax returned line in case of successful upload -->
                <div class="output mb-1">
                    <!-- Choose File -->
                    <input type="file" name="image" class="image mb-1">
                    <!-- Upload Button -->
                    <button class="btn btn-primary btn-sm upload mb-1">Upload</button>
                    <!-- Progress Bar -->
                    <div class="progress">
                        <div class="progress-bar" style="width: 0%" role="progressbar" id="progress"></div>
                    </div>
                    <!-- Hidden Field containing url of file. Gets replaced with ajax returned file value when successful file upload occurs -->
                    <!-- This is technically useless, cuz ajax input=hidden is used anyway -->
                    <input type="hidden" name="files" id="files" value="">

                </div>
            </div>
            <button type="submit" class="submit btn btn-primary" value="procedure_entry" name="submit">Submit</button>

        </form>

        <h1>Procedures done</h1>
        <?php
            $query_procedure = "SELECT * FROM `dhruv_procedures` WHERE patient_op_number = '".mysqli_real_escape_string($link, $row['op_number'])."' ORDER BY date DESC";
            $result_procedure = mysqli_query($link, $query_procedure);
        ?>
        <table class="table table-striped table-responsive-sm">
            <thead>
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Procedure Done</th>
                    <th scope="col">Misc Details</th>
                    <th scope="col">Next Appointment</th>
                    <th scope="col">File</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row_procedure = mysqli_fetch_array($result_procedure)) {
                    if ($row_procedure['files']!='' or $row_procedure['files']!= null) {
                        $file = '<a href="'.$row_procedure['files'].'" target="_blank">File</a>';
                    } else {
                        $file = 'N/A';
                    }
                    echo '<tr data-id="'.$row_procedure['id'].'">
                    <td>'.date('d-M-Y', strtotime($row_procedure['date'])).'</td>
                    <td>'.$row_procedure['procedure_done'].'</td>
                    <td>'.$row_procedure['misc_details'].'</td>
                    <td>'.date('d-M-Y', strtotime($row_procedure['next_appointment'])).'</td>
                    <td>'.$file.'</td>';
                    if ($row_procedure['doctor_id']== $_SESSION['id']) {
                        echo '<td><a class="btn btn-outline-primary btn-sm m-1" href="edit-procedure.php?id='.$row_procedure['id'].'">Edit</a> <button class="btn btn-outline-danger btn-sm delete-procedure m-1" value="delete" data-id="'.$row_procedure['id'].'">X</button></td>';
                    } else {
                        // don't give edit/delete option if procedure created by another doctor
                        echo '<td></td>';
                    }
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

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
                        document.querySelector('tr[data-id="' + id + '"]').style.display =
                            "none";
                    } else {
                        alert(result);
                    }
                }
            })
        }
    })


    $(".delete-patient").click(function() {
        if (confirm("You Sure?")) {
            var op_number = $(this).attr("data-opNumber");
            $.ajax({
                type: "POST",
                url: "delete.php",
                data: {
                    action: 'deletePatient',
                    op_number: op_number
                },
                success: function(result) {
                    if (result == 'success') {
                        document.location.href = "../";
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