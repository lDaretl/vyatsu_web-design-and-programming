<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document</title>
</head>
<body>
    <form action="/result.php" method="POST">
        <input type="number" name="age" placeholder="Введите возраст">
        <input type="submit" value="Отправить">
    </form>

    <?php
    if (isset($_POST['age'])) {
        $age = (int) $_POST['age'];
        echo "<p>Ваш возраст: $age лет.</p>";
    }
    ?>
</body>
</html>
