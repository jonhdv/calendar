<!-- login.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        form > img {
            margin: 10px auto 30px auto;
            width: 140px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<form action="authenticate.php" method="POST">
    <img alt="Logo HEY" src="../icon/logo_agencia_hey.svg">
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" value="juan@heyagencia.com" required>
    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" value="juan_hey_2024" required>
    <button type="submit">Login</button>
</form>
</body>
</html>
