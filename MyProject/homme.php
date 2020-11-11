
<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<div id="login">
        <h3 class="text-center text-white pt-5">Login form</h3>
        <div class="container">
            <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <form id="login-form" class="form" action="" method="post">
                            <h3 class="text-center text-info">Login</h3>
                            <div class="form-group">
                                <label for="username" class="text-info">Username:</label><br>
                                <input type="text" name="username" id="username" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="password" class="text-info">Password:</label><br>
                                <input type="text" name="password" id="password" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="remember-me" class="text-info"><span>Remember me</span> <span><input id="remember-me" name="remember-me" type="checkbox"></span></label><br>
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="submit">
                            </div>
                            <div id="register-link" class="text-right">
                                <a href="#" class="text-info">Register here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php


if (isset($_POST["login"])) {

    $email = null;

    $password = null;

    if (isset($_POST["email"])) {

        $email = $_POST["email"];

    }

    if (isset($_POST["password"])) {

        $password = $_POST["password"];

    }

    $isValid = true;

    if (!isset($email) || !isset($password)) {

        $isValid = false;

        flash("Email or password missing");

    }

    if (!strpos($email, "@")) {

        $isValid = false;

        //echo "<br>Invalid email<br>";

        flash("Invalid email");

    }

    if ($isValid) {

        $db = getDB();

        if (isset($db)) {

            $stmt = $db->prepare("SELECT id, email, username, password from Users WHERE email = :email LIMIT 1");




            $params = array(":email" => $email);

            $r = $stmt->execute($params);

            //echo "db returned: " . var_export($r, true);

            $e = $stmt->errorInfo();

            if ($e[0] != "00000") {

                //echo "uh oh something went wrong: " . var_export($e, true);

                flash("Something went wrong, please try again");

            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result["password"])) {

                $password_hash_from_db = $result["password"];

                if (password_verify($password, $password_hash_from_db)) {

                    $stmt = $db->prepare("

SELECT Roles.name FROM Roles JOIN UserRoles on Roles.id = UserRoles.role_id where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");

                    $stmt->execute([":user_id" => $result["id"]]);

                    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);




                    unset($result["password"]);//remove password so we don't leak it beyond this page

                    //let's create a session for our user based on the other data we pulled from the table

                    $_SESSION["user"] = $result;//we can save the entire result array since we removed password

                    if ($roles) {

                        $_SESSION["user"]["roles"] = $roles;

                    }

                    else {

                        $_SESSION["user"]["roles"] = [];

                    }

                    //on successful login let's serve-side redirect the user to the home page.

                    flash("Log in successful");

                    die(header("Location: home.php"));

                }

                else {

                    flash("Invalid password");

                }

            }

            else {

                flash("Invalid user");

            }

        }

    }

    else {

        flash("There was a validation issue");

    }

}

?>

<?php require(__DIR__ . "/partials/flash.php");


