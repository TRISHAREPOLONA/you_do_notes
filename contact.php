<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact - YOU DO NOTES</title>
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

    /* Contact Section */
    .contact-container {
      max-width: 900px;
      margin: 80px auto;
      padding: 20px;
      text-align: center;
    }

    .contact-container h2 {
      font-size: 2.2rem;
      margin-bottom: 20px;
      color: #5a4b41;
    }

    .contact-container p {
      font-size: 1.1rem;
      margin-bottom: 20px;
      color: #444;
      line-height: 1.6;
    }

    /* Contact form */
    form {
      max-width: 600px;
      margin: 0 auto;
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: #5a4b41;
    }

    input, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    textarea {
      height: 120px;
      resize: none;
    }

    button {
      background: #b08968;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      transition: background 0.2s;
    }

    button:hover {
      background: #a0765b;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <h1>YOU DO NOTES</h1>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="about.php">About</a>
      <a href="contact.php">Contact</a>
      <a href="products.php">Catalog</a>
    </div>
  </div>

  <!-- Contact Section -->
  <div class="contact-container">
    <h2>Contact Us</h2>
    <p>
      Have questions, feedback, or ideas?  
      We’d love to hear from you! Fill out the form below or reach out directly to our team.
    </p>

    <form action="send_message.php" method="POST">
      <label for="name">Your Name</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Your Email</label>
      <input type="email" id="email" name="email" required>

      <label for="message">Your Message</label>
      <textarea id="message" name="message" required></textarea>

      <button type="submit">Send Message</button>
    </form>
  </div>
</body>
</html>
