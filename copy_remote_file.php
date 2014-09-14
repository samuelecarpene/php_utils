<?php
//TODO: protect page with authentication
$valid_users = array("admin" => "password2014");

$auth = false;
//AUTH
if (isset($_COOKIE["copy_remote_file"]) && $_COOKIE["copy_remote_file"] == true) {
    $auth = true;
}
if (isset($_POST) && isset($_POST["login_username"]) && $_POST["login_username"] != "" && isset($_POST["login_password"]) && $_POST["login_password"] != "") {
    foreach ($valid_users as $username => $pwd) {
        if ($username == $_POST["login_username"] && $pwd == $_POST["login_password"]) {
            $auth = true;
            setcookie("copy_remote_file", true, time() + 3600);
        }
    }
}


//REMOTE FILE COPY
$errors = true;
$error_message = "Generic errors: inform samuelecarpene@gmail.com";
$remote_file_url = "";
$copy_action = false;
if (isset($_POST) && isset($_POST["remote_file_url"]) && $_POST["remote_file_url"] != "") {
    $copy_action = true;
    set_time_limit(0);
    ini_set('max_execution_time', 300000000);

    try {
        $remote_file_url = $_POST["remote_file_url"];
        $file_name = basename($remote_file_url);
        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $file_name;
        if(file_exists($filePath )){
            $error_message = "Local file '" . $filePath . "' already exist!";
        }

        if (filter_var($remote_file_url, FILTER_VALIDATE_URL) === FALSE) {
            $error_message = "Url '" . $remote_file_url . "' not valid!";
        } else {
            $fh = fopen($file_name, "wb");
            $curl = curl_init($remote_file_url);

            curl_setopt($curl, CURLOPT_FILE, $fh);
            curl_setopt($curl, CURLOPT_NOBODY, true);

            $exec_result = curl_exec($curl);

            if ($exec_result !== false) {
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if ($statusCode == 404) {
                    $error_message = "Remote file '" . $remote_file_url . "' responded with 404!";
                }
                if ($statusCode == 200) {
                    $errors = false;
                }
            }

            curl_close($curl);


        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">

        <?php
        if (!$auth){
            ?>
            <div class="col-md-6 col-md-offset-3 login">
                <br>
                <br>

                <div class="jumbotron">
                    <h1 style="text-align:center;">Login</h1>
                    <br>

                    <form name="form-login" method="POST" class="form-horizontal">
                        <fieldset>
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="username">Email</label>

                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-user"></span>
                                        </span>
                                        <input type="text" id="login_username" name="login_username" class="form-control" placeholder="Insert here the username">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-3" for="password">Password</label>

                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-lock"></span>
                                        </span>
                                        <input type="password" id="login_password" name="login_password" class="form-control" placeholder="Insert here the password">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-9 col-sm-offset-3">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-log-in"></span> Login
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>

        <?php
        }
        else{
        ?>
        <div class="col-xs-12">
            <br>
            <br>

            <div class="jumbotron">
                <h1>Copy remote file to local</h1>

                <p>
                    Copy remote file to local inserting remote url of file only!
                </p>


                <form role="form" method="post">
                    <div class="form-group">
                        <label for="remote_file_url">Remote file url</label>
                        <input
                            name="remote_file_url"
                            class="form-control"
                            placeholder="File url like https://github.com/twbs/bootstrap/archive/v3.2.0.zip"
                            value="<?php echo $remote_file_url; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <div>
                    <br>
                    <br>
                    <?php
                    if ($copy_action) {
                        if ($errors) {
                            ?>
                            <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-remove"></span> File not copied. Errors: <b><?php echo $error_message; ?></b></div>
                        <?php
                        } else {
                            ?>
                            <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok"></span> File copied in <b><?php echo $filePath; ?></b></div>
                        <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
            }
            ?>


        </div>
    </div>
</div>

<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</body>
</html>

