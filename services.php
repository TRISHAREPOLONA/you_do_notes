<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta charset="UTF-8">
  <title>Services - YOU DO NOTES</title>
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

    /* Services Section */
    .services-container {
      max-width: 1100px;
      margin: 80px auto;
      padding: 20px;
      text-align: center;
    }

    .services-container h2 {
      font-size: 2.2rem;
      margin-bottom: 20px;
      color: #5a4b41;
    }

    .services-container p {
      font-size: 1.1rem;
      margin-bottom: 40px;
      color: #444;
      line-height: 1.6;
    }

    /* Service cards */
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
    }

    .service-card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }

    .service-card:hover {
      transform: translateY(-5px);
    }

    .service-card h3 {
      margin-bottom: 10px;
      color: #5a4b41;
      font-size: 1.3rem;
    }

    .service-card p {
      font-size: 1rem;
      color: #555;
      line-height: 1.5;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <h1>YOU DO NOTES</h1>
    <div class="nav-links">
      <a href="products.php">Home</a>
      <a href="about.php">About</a>
      <a href="contact.php">Contact</a>
      
    </div>
  </div>

  <!-- Services Section -->
  <div class="services-container">
    <h2>Our Services</h2>
    <p>
      At YOU DO NOTES, we provide high-quality academic and creative services 
      designed to help students and professionals succeed. Here’s what we offer:
    </p>

    <div class="services-grid">
      <div class="service-card">
        <h3>Custom Note-Making</h3>
        <p>We create personalized, well-structured notes tailored to your course or subject requirements.</p>
      </div>

      <div class="service-card">
        <h3>Study Guides</h3>
        <p>Concise and easy-to-understand study materials to help you prepare for exams efficiently.</p>
      </div>

      <div class="service-card">
        <h3>Summarization</h3>
        <p>We summarize long lectures, articles, or readings into digestible key points.</p>
      </div>

      <div class="service-card">
        <h3>Project Assistance</h3>
        <p>Guidance and structured notes for projects, presentations, and research papers.</p>
      </div>
    </div>
  </div>
</body>
</html>
