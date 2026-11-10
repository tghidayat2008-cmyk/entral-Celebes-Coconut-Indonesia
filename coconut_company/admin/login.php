<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Username dan password wajib diisi.";

    } else {

        $query = "SELECT * FROM users WHERE username = ?";

        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user["password"])) {

                $_SESSION["admin_id"] = $user["id"];
                $_SESSION["admin_username"] = $user["username"];
                $_SESSION["admin_role"] = $user["role"];

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Username atau password salah.";

            }

            mysqli_stmt_close($stmt);

        } else {

            $error = "Terjadi kesalahan pada sistem.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login | CCCI</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

    <style>

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(
                    135deg,
                    #0A3F27,
                    #0F5132
                );
            font-family: "DM Sans", Arial, sans-serif;
        }

        .login-wrapper {
            width: 100%;
            max-width: 430px;
            padding: 24px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 42px 40px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, .20);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-logo img {
            width: 92px;
            height: 92px;
            object-fit: contain;
            display: block;
            margin: 0 auto 18px;
        }

        .login-logo h1 {
            margin: 0;
            color: #17231C;
            font-size: 24px;
            font-weight: 700;
        }

        .login-logo p {
            margin: 7px 0 0;
            color: #6B756F;
            font-size: 14px;
        }

        .login-form {
            margin-top: 30px;
        }

        .login-form .form-group {
            margin-bottom: 20px;
        }

        .login-form label {
            display: block;
            margin-bottom: 8px;
            color: #17231C;
            font-size: 14px;
            font-weight: 600;
        }

        .login-form input {
            width: 100%;
            height: 48px;
            padding: 0 15px;
            border: 1px solid #D9DED9;
            border-radius: 10px;
            outline: none;
            background: #ffffff;
            color: #17231C;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            transition: .2s ease;
        }

        .login-form input:focus {
            border-color: #0F5132;
            box-shadow: 0 0 0 3px rgba(15, 81, 50, .10);
        }

        .login-button {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 10px;
            background: #0F5132;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: .2s ease;
        }

        .login-button:hover {
            background: #0A3F27;
            transform: translateY(-1px);
        }

        .login-error {
            margin-bottom: 20px;
            padding: 12px 14px;
            border-radius: 9px;
            background: #FDECEC;
            border: 1px solid #F3C5C5;
            color: #A52A2A;
            font-size: 13px;
            line-height: 1.5;
        }

        .login-footer {
            margin-top: 26px;
            text-align: center;
            color: #8A938D;
            font-size: 12px;
        }

        @media (max-width: 480px) {

            .login-wrapper {
                padding: 18px;
            }

            .login-card {
                padding: 34px 24px;
                border-radius: 16px;
            }

            .login-logo img {
                width: 80px;
                height: 80px;
            }

            .login-logo h1 {
                font-size: 21px;
            }

        }

    </style>

</head>

<body>

    <div class="login-wrapper">

        <div class="login-card">

            <!-- LOGO -->

            <div class="login-logo">

                <img
                    src="../assets/img/logo.png"
                    alt="CCCI Logo"
                >

                <h1>
                    CCCI Admin
                </h1>

                <p>
                    PT. Central Celebes Coconut Indonesia
                </p>

            </div>


            <!-- LOGIN FORM -->

            <form
                method="POST"
                class="login-form"
            >

                <?php if ($error !== ""): ?>

                    <div class="login-error">
                        <?= htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($_POST["username"] ?? ""); ?>"
                        autocomplete="username"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>

            </form>


            <div class="login-footer">
                CCCI Management System
            </div>

        </div>

    </div>

</body>

</html>