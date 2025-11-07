<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farm Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add your CSS files here -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Farm Management System</h1>
        <nav>
            <ul>
                <li><a href="/dashboard">Dashboard</a></li>
                <li><a href="/crops">Crops</a></li>
                <li><a href="/livestock">Livestock</a></li>
                <li><a href="/inventory">Inventory</a></li>
                <li><a href="/reports">Reports</a></li>
                <li><a href="/logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php if (isset($content)) echo $content; ?>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Farm Management System</p>
    </footer>
</body>
</html>