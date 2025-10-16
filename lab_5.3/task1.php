<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document</title>
</head>
<body>
    <form action="/result.php" method="GET">
        <input type="text" name="name" placeholder="Введите имя">
        <input type="submit" value="Отправить">
    </form>

    <?php
    if (isset($_GET['name'])) {
        $name = htmlspecialchars($_GET['name']);
        echo "<p>Привет, $name!</p>";
    }
    ?>
</body>
</html>
