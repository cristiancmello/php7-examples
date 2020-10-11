<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP Forms</title>
</head>
<body>
  <form action="action.php" method="post">
    <label for="name">Name: </label>
    <input id="name" name="name" type="text" placeholder="Insert your name" /><br />

    <label for="lastname">Last Name: </label>
    <input id="lastname" name="lastname" type="text" placeholder="Insert your last name" /><br />

    <label for="age">Age:</label>
    <input type="text" id="age" name="age" placeholder="Insert your age" /><br />

    <input type="submit" value="Send" />
  </form>
</body>
</html>