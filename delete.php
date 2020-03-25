<?php
session_start();
$error="";

if (array_key_exists("id", $_SESSION)) {
    include('includes/connect-db.php');
} else {
    header("Location: index.php?redirect=".$_SERVER['REQUEST_URI']);
}

if (array_key_exists('action', $_POST)) {
    if ($_POST['action']=='deleteProcedure') {
        // check to see if the procedure exists
        $query = "SELECT * FROM `dhruv_procedures` WHERE id=".mysqli_real_escape_string($link, $_POST['id'])." LIMIT 1";
        $result = mysqli_query($link, $query) or die(mysql_error());

        if (mysqli_num_rows($result)!=0) {
            // once the procedure confirmed exists
            // check to make sure the same doctor who created the procedure is accessing it. (only they can only delete the procedure)
            $query_check_doc = "SELECT `doctor_id` FROM `dhruv_procedures` WHERE id=".mysqli_real_escape_string($link, $_POST['id'])." LIMIT 1";
            $result_check_doc = mysqli_query($link, $query_check_doc);
            $row_check_doc = mysqli_fetch_assoc($result_check_doc);
            
            if ($row_check_doc['doctor_id'] == $_SESSION['id']) {
                $query = "DELETE FROM `dhruv_procedures` WHERE id=".mysqli_real_escape_string($link, $_POST['id'])." LIMIT 1";
                if (mysqli_query($link, $query)) {
                    echo 'success';
                } else {
                    echo "failed to delete Procedure. ";
                    echo $query;
                }
            } else {
                $query_doctor_name = "SELECT * FROM `dhruv_users` WHERE id=".mysqli_real_escape_string($link, $row_check_doc['doctor_id'])." LIMIT 1";
                $result_doctor_name = mysqli_query($link, $query_doctor_name);
                $row_doctor_name = mysqli_fetch_assoc($result_doctor_name);
                echo "Sorry, the procedure was done by Dr. ".$row_doctor_name['name']." (ID#: ".$row_check_doc['doctor_id']."). Request deletion from them. ";
                echo 'Phone: '.$row_doctor_name['phone'];
            }
        } else {
            echo 'The procedure does not exist';
        }
    }

    if ($_POST['action']=='deletePatient') {
        // check to see if the patient exists
        $query = "SELECT * FROM `dhruv_patient_bio` WHERE op_number='".mysqli_real_escape_string($link, urldecode($_POST['op_number']))."' LIMIT 1";
        $result = mysqli_query($link, $query) or die(mysql_error());

        if (mysqli_num_rows($result)!=0) {
            // once the patient confirmed exists
            // check to make sure the same doctor who created the patient entry is accessing it. (only they can only delete the procedure)
            $query_check_doc = "SELECT `doctor_id` FROM `dhruv_patient_bio` WHERE op_number='".mysqli_real_escape_string($link, urldecode($_POST['op_number']))."' LIMIT 1";
            $result_check_doc = mysqli_query($link, $query_check_doc);
            $row_check_doc = mysqli_fetch_assoc($result_check_doc);

            if ($row_check_doc['doctor_id'] == $_SESSION['id']) {
                // once the doctor id matches, proceed with deletion
                $query_delete_patient = "DELETE FROM `dhruv_patient_bio` WHERE op_number='".mysqli_real_escape_string($link, urldecode($_POST['op_number']))."' LIMIT 1";
                
                if (mysqli_query($link, $query_delete_patient)) {
                    // now delete all his procedures
                    $query_delete_procedures = "DELETE FROM `dhruv_procedures` WHERE patient_op_number='".mysqli_real_escape_string($link, urldecode($_POST['op_number']))."'";
                    if (mysqli_query($link, $query_delete_procedures)) {
                        echo 'success';
                    } else {
                        echo "failed to delete the procedures. FATAL ERROR. PATIENT REMOVED BUT PROCEDURES LEFT BEHIND. ";
                        echo $query_delete_procedures;
                    }
                } else {
                    echo "failed to delete patient. ";
                    echo $query_delete_patient;
                }
            } else {
                $query_doctor_name = "SELECT * FROM `dhruv_users` WHERE id=".mysqli_real_escape_string($link, $row_check_doc['doctor_id'])." LIMIT 1";
                $result_doctor_name = mysqli_query($link, $query_doctor_name);
                $row_doctor_name = mysqli_fetch_assoc($result_doctor_name);
                echo "Sorry, the patient entry was done by Dr. ".$row_doctor_name['name']." (ID#: ".$row_check_doc['doctor_id']."). Request deletion from them. ";
                echo 'Phone: '.$row_doctor_name['phone'];
            }
        } else {
            echo 'Sorry, the patient entry does not exist';
        }
    }
}
die();