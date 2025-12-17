<?php
// Set session path to local project folder to avoid perm issues
$s_path = __DIR__ . '/sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusLive - Premium Bus Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="index.php" class="nav-brand">Terai bus service</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="#">Routes</a>
                <a href="#">Contact</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="user/dashboard.php">Dashboard</a>
                    <a href="logout.php" class="btn btn-primary" style="padding: 0.5rem 1rem;">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php" class="btn btn-primary" style="padding: 0.5rem 1rem;">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <h1>Travel with Comfort & Style</h1>
            <p style="color: var(--text-muted); font-size: 1.2rem;">Book your bus tickets instantly with the most modern platform.</p>
            
            <form action="search.php" method="GET" class="search-widget">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label>From</label>
                    <select name="from" required>
                        <option value="">Select Origin</option>
                        <?php 
                        $starts = $conn->query("SELECT DISTINCT start_location FROM routes");
                        while($s = $starts->fetch_assoc()) {
                            echo "<option value='".$s['start_location']."'>".$s['start_location']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label>To</label>
                    <select name="to" required>
                        <option value="">Select Destination</option>
                        <?php 
                        $ends = $conn->query("SELECT DISTINCT end_location FROM routes");
                        while($e = $ends->fetch_assoc()) {
                            echo "<option value='".$e['end_location']."'>".$e['end_location']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Date (Optional)</label>
                    <input type="date" name="date">
                </div>
                <button type="submit" class="btn btn-primary" style="height: 50px; margin-top: auto;">Search Buses</button>
            </form>
        </div>
    </header>

    <section class="container" style="padding: 4rem 1.5rem;">
        <h2 class="text-center mb-4">Why Choose Us?</h2>
        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div class="card text-center">
                <h3>Safe Travel</h3>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Sanitized buses and strict safety protocols.</p>
            </div>
            <div class="card text-center">
                <h3>On Time</h3>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Punctual departures and live tracking.</p>
            </div>
            <div class="card text-center">
                <h3>Best Prices</h3>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Affordable luxury with no hidden fees.</p>
            </div>
        </div>
    </section>

    <footer style="background: var(--card-bg); padding: 2rem 0; border-top: 1px solid var(--border-color); margin-top: 2rem;">
        <div class="container text-center">
            <p style="color: var(--text-muted);">&copy; <?php echo date('Y'); ?> BusLive System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
