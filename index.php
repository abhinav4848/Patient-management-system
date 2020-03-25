<?php
session_start();
$error=$success="";

if (array_key_exists("logout", $_GET)) {
    session_unset();
} elseif (array_key_exists("id", $_SESSION) and $_SESSION['id'] >0) {
    //if there is a session id indicating person is already logged in, take them to their appropriate page.
    header("Location: dashboard.php");
}

if (array_key_exists("submit", $_POST)) {
    include 'includes/connect-db.php';

    //gathering errors if any
    if (!$_POST['username']) {
        $error .= "A Username is needed. <br />";
    }
    if (array_key_exists("password", $_POST)) {
        if (!$_POST['password']) {
            $error .= "A Password is needed. <br />";
        }
    }
    
    if ($error !="") {
        $error = "<p><strong>There were errors in you form:</strong></p>".$error;
    } else {
        //if there is no error
        if ($_POST["loginactive"]=='1') {
            //login
            $query = "SELECT * FROM `dhruv_users` WHERE username = '".mysqli_real_escape_string($link, $_POST['username'])."'";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_array($result);
            if (isset($row)) {
                if ($_POST['password'] == $row['password']) {
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['name'] = $row['name'];
                    if (array_key_exists("redirect", $_GET)) {
                        header("Location: ".$_GET['redirect']);
                    } else {
                        header("Location: dashboard.php");
                    }
                } else {
                    $error= "That password is incorrect.";
                }
            } else {
                $error= "That username doesn't exist.";
            }
        }

        if ($_POST["loginactive"]=='0') {
            //signup

            //check if username already taken
            $query = "SELECT * FROM `dhruv_users` WHERE username = '".mysqli_real_escape_string($link, $_POST['username'])."'";
            $result = mysqli_query($link, $query) or die(mysql_error());

            if (mysqli_num_rows($result)!=0) {
                $row = mysqli_fetch_array($result);
                $error.= 'That username already taken by '.$row['name'];
            } else {
                //if free, check for other details
                if (!$_POST['name']) {
                    $error .= "A Name is needed. <br />";
                }
        
                if (!$_POST['phone']) {
                    $error .= "A phone number is needed. <br />";
                }
        
                if (!$_POST['regno']) {
                    $error .= "A registration Number is needed. <br />";
                }

                if (!$_POST['password'] or !$_POST['conf_password']) {
                    // either of password fields are empty
                    $error .= "Please enter both password fields. <br />";
                } elseif ($_POST['password'] != $_POST['conf_password']) {
                    // whether both password fields match
                    $error .= "Passwords don't match <br />";
                    $_POST['conf_password']='';
                }
        
                if ($error !="") {
                    $error = "<p><strong>There were errors in you form:</strong></p>".$error;
                } else {
                    $query="INSERT INTO `dhruv_users` (`username`, `password`, `name`, `phone`, `regno`) VALUES (
                    '".mysqli_real_escape_string($link, $_POST['username'])."',
                    '".mysqli_real_escape_string($link, $_POST['password'])."',
                    '".mysqli_real_escape_string($link, $_POST['name'])."',
                    '".mysqli_real_escape_string($link, $_POST['phone'])."',
                    '".mysqli_real_escape_string($link, $_POST['regno'])."')";

                    if (mysqli_query($link, $query)) {
                        $id = mysqli_insert_id($link);
                        $success = 'Inserted as ID# '.$id;
                    } else {
                        echo '<div id="tablediv">';
                        echo "failed to insert the entry. Query: <br><br> ".$query;
                        echo '</div>';
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.95, shrink-to-fit=no">
    <meta name="theme-color" content="#28a745">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Dhruv Patient admin</title>

    <style type="text/css">
    body {
        background: none;
    }

    .container {
        text-align: center;
        width: 400px;
        margin-top: 130px;
    }

    @media only screen and (max-width: 600px) {
        .container {
            text-align: center;
            width: 380px;
            margin-top: 130px;
        }
    }

    html {
        background: url() no-repeat center center fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
    }

    #signup,
    #name,
    #phone,
    #regno,
    #conf_password {
        display: none;
    }
    </style>
</head>

<body>
    <div class="container">
        <div id="error">
            <?php
            if ($error!="") {
                echo '<div class="alert alert-danger" role="alert">'.$error.'</div>';
            }

            if ($success!='') {
                echo '<div class="alert alert-success" role="alert">'.$success.'</div>';
            }
            ?>
        </div>
        <form method="post" id="login">
            <h3>Dhruv Patient system. Please <span id="formTitle">Log In!</span></h3>
            <p>Enter your username and password</p>
            <input type="hidden" id="loginActive" name="loginactive" value="1">
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username" maxlength="20" value="<?php if (array_key_exists('submit', $_POST) and $_POST['username']!='') {
                echo $_POST['username'];
            } ?>" autofocus>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" value="<?php if (array_key_exists('submit', $_POST) and $_POST['password']!='') {
                echo $_POST['password'];
            } ?>">
            </div>

            <div class="form-group">
                <input type="password" class="form-control" id="conf_password" name="conf_password"
                    placeholder="Retype Password" value="<?php if (array_key_exists('submit', $_POST) and $_POST['conf_password']!='') {
                echo $_POST['conf_password'];
            } ?>">
            </div>

            <div class="form-group">
                <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="<?php if (array_key_exists('submit', $_POST) and $_POST['name']!='') {
                echo $_POST['name'];
            } ?>">
            </div>

            <div class="form-group">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone" value="<?php if (array_key_exists('submit', $_POST) and $_POST['phone']!='') {
                echo $_POST['phone'];
            } ?>">
            </div>

            <div class="form-group">
                <input type="text" class="form-control" id="regno" name="regno" placeholder="Registration Number" value="<?php if (array_key_exists('submit', $_POST) and $_POST['regno']!='') {
                echo $_POST['regno'];
            } ?>">
            </div>

            <button type="submit" name="submit" id="loginSignupButton" class="btn btn-success">Log In</button>

            <a href="#" id="toggleLogin">Signup</a>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js">
    </script>

    <script>
    $("#toggleLogin").click(function() {
        if ($("#loginActive").val() == "0") {

            $("#loginActive").val("1");
            $("#formTitle").html("Log In");
            $("#toggleLogin").html("Sign Up");
            $("#loginSignupButton").html("Log In");

            $("#conf_password").hide();
            $("#name").hide();
            $("#phone").hide();
            $("#regno").hide();

        } else {

            $("#loginActive").val("0");
            $("#formTitle").html("Sign Up");
            $("#toggleLogin").html("Log in");
            $("#loginSignupButton").html("Sign Up");

            $("#conf_password").show();
            $("#name").show();
            $("#phone").show();
            $("#regno").show();

        }
    })
    </script>
</body>

</html>