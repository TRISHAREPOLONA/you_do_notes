<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Codes - YOU DO NOTES</title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f9f9f9;
      color: #333;
    }

    /* Navbar */
    .navbar {
      background: #fff;
      padding: 15px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .navbar h1 {
      margin: 0;
      font-size: 1.8rem;
      color: #5a4b41;
    }

    .nav-links {
      display: flex;
      gap: 30px;
    }

    .nav-links a {
      text-decoration: none;
      color: #333;
      font-weight: 500;
      font-size: 1rem;
      transition: color 0.2s;
    }

    .nav-links a:hover {
      color: #5a4b41;
    }

    /* Codes Section */
    .codes-container {
      max-width: 1000px;
      margin: 80px auto;
      padding: 20px;
    }

    .codes-container h2 {
      text-align: center;
      font-size: 2.2rem;
      color: #5a4b41;
      margin-bottom: 20px;
    }

    .codes-container p {
      text-align: center;
      font-size: 1.1rem;
      color: #444;
      margin-bottom: 40px;
    }

    /* Code blocks */
    pre {
      background: #2d2d2d;
      color: #f8f8f2;
      padding: 20px;
      border-radius: 8px;
      overflow-x: auto;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .code-card {
      margin-bottom: 40px;
    }

    .code-card h3 {
      margin-bottom: 10px;
      color: #5a4b41;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <h1>YOU DO NOTES</h1>
    <div class="nav-links">
      
      <a href="about.php">About</a>
      <a href="services.php">Services</a>
      <a href="contact.php">Contact</a>
      <a href="products.php">Home</a>
    </div>
  </div>

  <!-- Codes Section -->
  <div class="codes-container">
    <h2>Sample Codes</ h2>
    <p>Here are some of our ready-to-use code snippets and templates to help you learn and build faster.</p>

    <div class="code-card">
      <h3>1. Hello World in Python</h3>
      <pre>
print("Hello, World!")
      </pre>
    </div>

    <div class="code-card">
      <h3>2. Simple HTML Page</h3>
      <pre>
<!DOCTYPE html>
<html>
<head>
  <title>My First Page</title>
</head>
<body>
  <h1>Welcome to My Website</h1>
  <p>This is a sample HTML page.</p>
</body>
</html>
      </pre>
    </div>

    <div class="code-card">
      <h3>3. Basic JavaScript Alert</h3>
      <pre>
<script>
  alert("This is a JavaScript alert!");
</script>
      </pre>
    </div>
  </div>
</body>
</html>
