<?php
session_start();
$error="";

if (array_key_exists("id", $_SESSION)) {
    include('includes/connect-db.php');
} else {
    header("Location: index.php?redirect=".$_SERVER['REQUEST_URI']);
}

if (array_key_exists('start_date', $_GET) and array_key_exists("end_date", $_GET)) {
    // if start and end dates provided, use those
    
    if (array_key_exists('completed', $_GET)) {
        //if searching for completed dates, use this query
        $whereclause="WHERE date >= '".date("Y-m-d", strtotime($_GET['start_date']))."' AND date <='".date("Y-m-d", strtotime($_GET['end_date']))."'";
        $orderby = "date";
    } else {
        //else just assume they're looking for today's appointments
        $whereclause="WHERE next_appointment >= '".date("Y-m-d", strtotime($_GET['start_date']))."' AND next_appointment <='".date("Y-m-d", strtotime($_GET['end_date']))."'";
        $orderby = "next_appointment";
    }
    
    $start_date = date("d-M-Y", strtotime($_GET['start_date']));
    $end_date = date('d-M-Y', strtotime($_GET['end_date']));
} else {
    //else set start date to 1st of this month and last date as today
    if (array_key_exists('completed', $_GET)) {
        $whereclause="WHERE date >= '".date("Y-m-01")."' AND date <='".date("Y-m-d")."'";
        $orderby = "date";
    } else {
        $whereclause="WHERE next_appointment >= '".date("Y-m-01")."' AND next_appointment <='".date("Y-m-d")."'";
        $orderby = "next_appointment";
    }
    
    $start_date = date("01-M-Y");
    $end_date = date('d-M-Y');
}

// to decide whether the appointment/past dates list should be of all patients or just your own
if (array_key_exists('full_list', $_GET) and $_GET['full_list'] == 'on') {
    // choose to search all doctors list
} else {
    // by default it searches personal only
    $doctor_id = mysqli_real_escape_string($link, $_SESSION['id']);
    $whereclause.=" AND doctor_id=".$doctor_id;
}


$query="SELECT * FROM `dhruv_procedures` ".$whereclause." ORDER BY ".$orderby." DESC LIMIT 50";
//echo $query;
$result = mysqli_query($link, $query);
// row is displayed in the results section


if (array_key_exists('submit', $_POST) and $_POST['submit']=='ajaxsubmission') {
    // if an ajax search request is made
    if (array_key_exists('patientid', $_POST) and $_POST['patientid']!='') {
        //use the patient search system
        $patientid= mysqli_real_escape_string($link, $_POST['patientid']);
        
        $query_search_patient = "SELECT * FROM `dhruv_patient_bio` WHERE name LIKE '%".$patientid."%' OR op_number LIKE '%".$patientid."%' OR phone LIKE '%".$patientid."%' LIMIT 20";
        $result_search_patient = mysqli_query($link, $query_search_patient);
        echo '<table class="table table-striped table-responsive-sm"><thead><tr><th scope="col">OP Number</th><th scope="col">Name</th><th scope="col">Age</th><th scope="col">Phone</th></thead><tbody>';
        while ($row_search_patient = mysqli_fetch_array($result_search_patient)) {
            echo '<tr>';
            echo '<td>'.$row_search_patient['op_number'].'</td>';
            echo '<td><a href="view.php?op_number='.urlencode($row_search_patient['op_number']).'">'.$row_search_patient['name'].'</a></td>';
            echo '<td>'.$row_search_patient['age'].'</td>';
            echo '<td>'.$row_search_patient['phone'].'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    if (array_key_exists('procedure', $_POST) and $_POST['procedure']!='') {
        // use the procedure search system
        $procedure= mysqli_real_escape_string($link, $_POST['procedure']);
        
        $query_search_procedure = "SELECT * FROM `dhruv_procedures` WHERE procedure_done LIKE '%".$procedure."%' OR patient_op_number LIKE '%".$procedure."%' ORDER BY date DESC LIMIT 20";
        $result_search_procedure = mysqli_query($link, $query_search_procedure);

        procedure_tables_generate($link, $result_search_procedure);
    }
    die();
}

function procedure_tables_generate($link, $result)
{
    // function to generate an HTML table based on the results of a query
    echo '<table class="table table-striped table-responsive-sm"><thead><tr><th scope="col">Date</th><th scope="col">OP #</th><th scope="col">Procedure Done</th><th scope="col">Misc Details</th><th scope="col">Next Appointment</th><th scope="col">Actions</th></thead><tbody>';
    while ($row = mysqli_fetch_array($result)) {
        // Find patient name from procedure op number
        $query_patient_name = "SELECT `name` FROM `dhruv_patient_bio` WHERE op_number='".$row['patient_op_number']."' LIMIT 1";
        $result_patient_name = mysqli_query($link, $query_patient_name);
        $row_patient_name = mysqli_fetch_array($result_patient_name);

        echo '<tr>';
        echo '<td>'.date('d-M-Y', strtotime($row['date'])).'</td>';
        echo '<td>'.$row_patient_name['name'].' (<a href="view.php?op_number='.urlencode($row['patient_op_number']).'">'.$row['patient_op_number'].'</a>)</td>';
        echo '<td>'.$row['procedure_done'].'</td>';
        echo '<td>'.$row['misc_details'].'</td>';
        echo '<td>'.date('d-M-Y', strtotime($row['next_appointment'])).'</td>';
        echo '<td><a href="edit-procedure.php?id='.$row['id'].'">Edit</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
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

    <!-- Datepicker -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <!--Hardcoded CSS for this page-->
    <style type="text/css">
    .card {
        margin: 4px 4px;
        width: 21rem;
    }
    </style>

    <title>Dashboard</title>
</head>

<body>
    <div class="container">

        <?php include('includes/nav.php'); ?>
        <h1>Search Patients</h1>
        <div class="row center">
            <div class="column mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Search visits by date range</h5>
                        <form>
                            <div class="custom-control custom-checkbox mr-sm-2">
                                <input type="checkbox" class="custom-control-input" id="disableDirectEdit"
                                    onclick="document.getElementsByClassName('datepick')[0].readOnly=this.checked; document.getElementsByClassName('datepick')[1].readOnly=this.checked;">

                                <label class="custom-control-label" for="disableDirectEdit">Disable Direct Edit</label>
                            </div>
                            <div class="input-daterange">
                                <input type="text" class="form-control datepick" name="start_date"
                                    value="<?=$start_date?>">
                                <p class="text-center mb-0">to</p>
                                <input type="text" class="form-control datepick" name="end_date" value="<?=$end_date?>">
                            </div>

                            <div class="custom-control custom-checkbox mr-sm-2">
                                <input type="checkbox" name="completed" id="completed" class="custom-control-input" <?php
                                    if (array_key_exists('completed', $_GET) and $_GET['completed']=='on') {
                                        echo 'checked';
                                    }
                                    ?>>
                                <label class="custom-control-label" for="completed">Past dates? <small
                                        class="text-muted">Default: Next Appointment</small>
                                </label>
                            </div>

                            <div class="custom-control custom-checkbox mr-sm-2">
                                <input type="checkbox" name="full_list" id="full_list" class="custom-control-input" <?php
                                    if (array_key_exists('full_list', $_GET) and $_GET['full_list']=='on') {
                                        echo 'checked';
                                    }
                                    ?>>
                                <label class="custom-control-label" for="full_list">Full List? <small
                                        class="text-muted">Default: Personal</small>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary mt-1">Refresh patient list</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="column mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Search by Name/OP#/Phone#</h5>
                        <input type="text" class="form-control" name="patientid" id="patientid"
                            placeholder="Enter Search" autocomplete="off" autofocus>
                    </div>
                </div>
            </div>
            <div class="column mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Search by procedure name</h5>
                        <input type="text" class="form-control" name="procedure" id="procedure"
                            placeholder="Enter Search" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <h2>Results</h2>
        <div id="results">
            <?php
                procedure_tables_generate($link, $result);
            ?>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js">
    </script>

    <script>
    document.querySelector('#patientid').addEventListener('keyup', search_patient, false);

    function search_patient() {
        // ajax search for patient by name/id
        var patientid = $("#patientid").val();
        $.ajax({
            type: "POST",
            url: "dashboard.php",
            data: {
                patientid: patientid,
                submit: 'ajaxsubmission'
            },
            success: function(result) {
                if (result != '') {
                    $("#results").show();
                    $("#results").html("<b>Ajax Results</b>:<br/>" + result);
                } else {
                    $("#results").hide();
                }
            }
        })
    }

    document.querySelector('#procedure').addEventListener('keyup', search_procedure, false);

    function search_procedure() {
        // ajax search for patient by name/id
        var procedure = $("#procedure").val();
        $.ajax({
            type: "POST",
            url: "dashboard.php",
            data: {
                procedure: procedure,
                submit: 'ajaxsubmission'
            },
            success: function(result) {
                if (result != '') {
                    $("#results").show();
                    $("#results").html("<b>Ajax Results</b>:<br/>" + result);
                } else {
                    $("#results").hide();
                }
            }
        })
    }

    // datepicker
    $('.input-daterange').datepicker({
        format: "dd-M-yyyy",
        todayBtn: "linked",
        multidate: false,
        autoclose: true,
        todayHighlight: true
    });
    </script>


</body>

</html>