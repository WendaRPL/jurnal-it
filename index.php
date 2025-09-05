<?php
session_start();
require_once "direct/config.php"; // koneksi database

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password, role_id, name FROM User WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user["password"])) {
                // Set session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role_id"] = $user["role_id"];
                $_SESSION["name"] = $user["name"];

                // Update last_online_datetime saat login sukses
                $update = $conn->prepare("UPDATE User SET last_online_datetime = NOW() WHERE id = ?");
                $update->bind_param("i", $user["id"]);
                $update->execute();
                $update->close();

                // Redirect sesuai role
                if ($user["role_id"] == 1) {
                    header("Location: home.php");
                } elseif ($user["role_id"] == 2) {
                    header("Location: home.php");
                } else {
                    header("Location: home.php");
                }
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    } else {
        $error = "Harap isi semua field!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Jurnal IT</title>
<style>
    :root {
        --dark: #1D1A1A;
        --cyan: #00FFFF;
        --yellow: #FFEB3B;
        --white: #FFFFFF;
        --gray: #2A2A2A;
        --light-gray: #3E3E3E;
    }
    
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        background: var(--dark);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
        color: var(--white);
        padding: 20px;
    }
    
    .login-box {
        background: var(--gray);
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }
    
    h2 {
        margin-bottom: 1.5rem;
        color: var(--cyan);
        border-bottom: 2px solid var(--cyan);
        padding-bottom: 10px;
        font-size: 1.8rem;
    }
    
    input {
        width: 100%;
        padding: 15px;
        margin-bottom: 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
    }
    
    input[type="text"], 
    input[type="password"] {
        background: var(--light-gray);
        color: var(--white);
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    input:focus {
        outline: 2px solid var(--cyan);
    }
    
    button {
        background: var(--yellow);
        color: var(--dark);
        border: none;
        padding: 15px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    
    button:hover {
        box-shadow: 0 4px 15px rgba(255,235,59,0.4);
        transform: translateY(-2px);
    }
    
    .error {
        color: #ff6b6b;
        margin-bottom: 1.5rem;
        font-size: 1rem;
        padding: 10px;
        background: rgba(255,0,0,0.1);
        border-radius: 5px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 480px) {
        .login-box {
            padding: 1.5rem;
        }
        
        h2 {
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
        }
        
        input {
            padding: 12px;
            margin-bottom: 1.2rem;
        }
        
        button {
            padding: 12px;
        }
    }
    
    @media (max-width: 360px) {
        body {
            padding: 10px;
        }
        
        .login-box {
            padding: 1.2rem;
        }
        
        h2 {
            font-size: 1.3rem;
        }
        
        input, button {
            font-size: 0.95rem;
        }
    }
</style>
</head>
<body>
    <div class="login-box">
        <h2>Jurnal IT - Login</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>