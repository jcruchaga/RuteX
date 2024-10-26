<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTMLError</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color:  rgba(0, 0, 0, 0.6); /*#f8d7da;*/
            color: #721c24;
        }
        .error-container {
            text-align: center;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .error-code {
            font-size: 5em;
            font-weight: bold;
        }
        .error-message {
            margin-top: 20px;
            font-size: 1.5em;
        }
        .home-link {
            margin-top: 30px;
            display: inline-block;
            padding: 10px 20px;
            font-size: 1em;
            color: #ffffff;
            background-color: #721c24;
            text-decoration: none;
            border-radius: 5px;
        }
        .home-link:hover {
            background-color: #a94442;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?=$code?></div>
        <div class="error-message"><?="$msg $uri"?></div>
        <a href="#" onclick="history.back()" class="home-link">Volver</a>
    </div>
</body>
</html>
