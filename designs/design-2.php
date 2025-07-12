<?php
include_once(__DIR__ . '/../includes/header-open.php');

echo '<title>' . $school_name . ' - Official Website</title>';

// Fetch carousel images
$stmt = $pdo->prepare("SELECT * FROM carousel_images");
$stmt->execute();
$carousel_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all notices
$stmt = $pdo->prepare("SELECT * FROM notices ORDER BY notice_date DESC LIMIT 30");
$stmt->execute();
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch classes and class-wise fees
$stmt = $pdo->prepare("SELECT classes.id, classes.class_name, class_wise_fee.amount FROM classes INNER JOIN class_wise_fee ON class_wise_fee.class_id = classes.id;");
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch teachers
$stmt = $pdo->prepare("SELECT * FROM teachers ORDER BY rand()");
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch gallery images
$stmt = $pdo->prepare("SELECT * FROM gallery ORDER BY event_date DESC");
$stmt->execute();
$galley_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch gallery videos
$stmt = $pdo->prepare("SELECT * FROM gallery_videos ORDER BY event_date DESC");
$stmt->execute();
$galley_videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Google Fonts: Nunito and Balsamiq Sans for a playful feel -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&family=Balsamiq+Sans:wght@700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- Custom CSS for the Kiddy Theme -->
<style>
    :root {
        --primary-color: <?= $theme_color ?>;
        --secondary-color: #ffc107;
        --accent-green: #198754;
        --accent-pink: #d63384;
        --bg-light: #f8f9fa;
        --bg-sky: #eef8ff;
        --text-dark: #343a40;
        --text-light: #FFFFFF;
        --border-radius-lg: 20px;
        --border-radius-md: 12px;
        --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --font-heading: 'Balsamiq Sans', cursive;
        --font-body: 'Nunito', sans-serif;
    }

    .main-container {
        font-family: var(--font-body);
        background-color: var(--text-light);
        color: var(--text-dark);
        overflow-x: hidden;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: var(--font-heading);
        color: var(--primary-color);
        font-weight: 700;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 900;
        margin-bottom: 40px;
        text-align: center;
        text-shadow: 2px 2px 0 var(--bg-sky);
    }

    .btn-fun {
        border-radius: 50px;
        padding: 12px 30px;
        font-family: var(--font-heading);
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-decoration: none;
    }

    .btn-fun:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-primary.btn-fun {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-secondary.btn-fun {
        background-color: var(--primary-color);
        color: var(--text-white);
    }

    .btn-light.btn-fun {
        background-color: #FFFFFF;
        color: var(--text-dark);
    }

    .btn-login {
        color: #FFFFFF;
    }

    .btn-login:hover {
        color: var(--primary-color);
    }

    /* Custom Navigation */
    .custom-nav-container {
        background: white;
        padding: 10px 0;
        box-shadow: var(--shadow);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .custom-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .school-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .school-logo img {
        height: 50px;
        width: 50px;
        border-radius: 50%;
    }

    .school-logo .logo-text {
        font-family: "Montserrat", sans-serif;
        font-optical-sizing: auto;
        font-weight: 900;
        font-style: normal;
        font-size: 2rem;
        color: var(--primary-color);
        text-transform: uppercase;
        text-align: center;
        margin-left: 15px;
    }

    .nav-links {
        display: flex;
        gap: 10px;
    }

    .nav-links a {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: var(--text-dark);
        padding: 10px;
        border-radius: var(--border-radius-md);
        transition: all 0.3s ease;
    }

    .nav-links a i {
        font-size: 1.5rem;
        margin-bottom: 5px;
        transition: transform 0.3s ease;
    }

    .nav-links a span {
        font-family: var(--font-heading);
        font-size: 0.9rem;
    }

    .nav-links a:hover {
        background-color: var(--bg-sky);
    }

    .nav-links a:hover i {
        transform: scale(1.2) rotate(5deg);
    }

    .nav-links .btn-login {
        align-self: center;
    }

    /* Mobile Nav Toggler */
    .mobile-nav-toggle {
        display: none;
        font-size: 1.5rem;
        color: var(--primary-color);
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px 10px;
    }

    .nav-dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 200px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-radius: var(--border-radius-md);
        z-index: 1001;
        padding: 10px 0;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
    }

    .nav-dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-menu a {
        display: flex !important;
        flex-direction: row !important;
        align-items: center;
        padding: 10px 20px !important;
        white-space: nowrap;
        border-radius: 0 !important;
        width: 100% !important;
        text-align: left;
    }

    .dropdown-menu a:hover {
        background-color: var(--bg-sky) !important;
    }

    .dropdown-menu a i {
        margin-right: 10px;
        font-size: 1rem !important;
        transform: none !important;
    }

    /* Hero Slider */
    .hero-slide {
        background-size: cover;
        background-position: center;
        color: white;
        height: 80vh;
        min-height: 500px;
        display: flex;
        align-items: center;
        position: relative;
    }

    .hero-slide::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        width: 100%;
        height: 100%;
    }

    .hero-content h1 {
        font-family: var(--font-heading);
        font-size: 3.5rem;
        font-weight: 900;
        color: white;
        text-shadow: 3px 3px 0 rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
    }

    .hero-content .lead {
        font-size: 1.5rem;
        color: white;
        margin-bottom: 30px;
        max-width: 800px;
    }

    .hero-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Slick Slider Overrides */
    .slick-dots {
        bottom: 20px;
    }

    .slick-dots li button:before {
        font-size: 12px;
        color: white;
        opacity: 0.5;
    }

    .slick-dots li.slick-active button:before {
        color: white;
        opacity: 1;
    }

    /* Teachers Carousel */
    .teachers-carousel {
        margin: 0 -15px;
    }

    .teachers-carousel .slick-slide {
        padding: 0 15px;
    }

    .teachers-carousel .slick-prev,
    .teachers-carousel .slick-next {
        width: 50px;
        height: 50px;
        background-color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .teachers-carousel .slick-prev::before,
    .teachers-carousel .slick-next::before {
        content: none !important;
    }

    .teachers-carousel .slick-prev:hover,
    .teachers-carousel .slick-next:hover {
        background-color: var(--secondary-color);
        /* transform: scale(1.1); */
    }

    .teachers-carousel .slick-prev {
        left: -60px;
    }

    .teachers-carousel .slick-next {
        right: -60px;
    }

    .teachers-carousel .slick-prev i,
    .teachers-carousel .slick-next i {
        color: white;
        font-size: 1.5rem;
    }

    /* Wavy Section Divider */
    .wavy-divider {
        position: relative;
        width: 100%;
        height: 100px;
        overflow: hidden;
    }

    .wavy-divider svg {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100px;
    }

    /* Feature Cards */
    .feature-card {
        background: var(--text-light);
        border-radius: var(--border-radius-lg);
        padding: 30px;
        text-align: center;
        box-shadow: var(--shadow);
        border: 3px solid transparent;
        transition: all 0.4s ease-in-out;
        height: 100%;
    }

    .feature-card:hover {
        transform: translateY(-10px) rotate(2deg);
        border-color: var(--primary-color);
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        display: inline-block;
        padding: 20px;
        border-radius: 50%;
        color: white;
    }

    .feature-card:nth-child(1) .feature-icon {
        background-color: var(--primary-color);
    }

    .feature-card:nth-child(2) .feature-icon {
        background-color: var(--accent-green);
    }

    .feature-card:nth-child(3) .feature-icon {
        background-color: var(--accent-pink);
    }

    /* Notice Board Section */
    #notices {
        background-color: var(--bg-sky);
        padding: 80px 0;
    }

    .notice-board {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }

    .notice-pin {
        background: white;
        padding: 25px;
        border-radius: var(--border-radius-md);
        box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.05);
        position: relative;
        transition: transform 0.3s ease;
    }

    .notice-pin:hover {
        transform: scale(1.05);
    }

    .notice-pin::before {
        content: '\f08d';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 2rem;
        color: var(--primary-color);
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
    }

    .notice-pin h4 {
        color: var(--accent-pink);
    }

    .notice-pin p {
        display: -webkit-box;
        line-clamp: 4;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notice-date {
        font-size: 0.8rem;
        font-weight: 700;
        color: #999;
        margin-bottom: 10px;
        display: block;
    }

    /* About Section */
    .about-img {
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow);
        transform: rotate(-3deg);
        width: 100%;
        height: auto;
        object-fit: cover;
    }

    /* Teachers Section */
    .teacher-card {
        text-align: center;
        background-color: var(--bg-sky);
        padding: 30px;
        border-radius: var(--border-radius-lg);
        transition: all 0.3s ease;
        height: 100%;
    }

    .teacher-card img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 0 0 5px var(--primary-color);
        margin: 0 auto;
        margin-bottom: 25px;
        object-fit: cover;
    }

    .teacher-card h4 {
        color: var(--text-dark);
    }

    .teacher-card .qualification {
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    .teacher-card .subject_specialization {
        color: var(--text-dark);
        font-style: italic;
    }

    /* Gallery Section */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .gallery-item {
        border-radius: var(--border-radius-md);
        overflow: hidden;
        box-shadow: var(--shadow);
        position: relative;
        transition: transform 0.3s ease;
        aspect-ratio: 1/1;
    }

    .gallery-item:hover {
        transform: scale(1.05) rotate(1deg);
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Contact Section */
    .contact-form .form-control {
        border-radius: 50px;
        padding: 15px 20px;
        /* border: 2px solid grey; */
        background-color: var(--bg-sky);
        transition: border-color 0.3s ease;
        border-width: 2px;
    }

    .contact-form textarea.form-control {
        border-radius: 20px;
    }

    .contact-form .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: none;
    }

    /* Footer */
    .site-footer {
        background-color: var(--text-dark);
        color: white;
        padding: 80px 0 30px;
    }

    .site-footer h5 {
        color: var(--secondary-color);
    }

    .site-footer a {
        color: white;
        text-decoration: none;
    }

    .site-footer a:hover {
        color: var(--secondary-color);
    }

    .footer-social-icons a {
        color: var(--text-dark);
        background-color: white;
        width: 40px;
        height: 40px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        font-size: 1.2rem;
        margin-right: 10px;
        transition: all 0.3s ease;
    }

    .footer-social-icons a:hover {
        transform: translateY(-5px);
        background-color: var(--primary-color);
        color: var(--text-white);
    }

    /* Responsive Adjustments */
    @media (max-width: 1199px) {
        .school-logo .logo-text {
            font-size: 1.5rem;
        }

        .teachers-carousel .slick-prev {
            left: -30px;
        }

        .teachers-carousel .slick-next {
            right: -30px;
        }
    }

    @media (max-width: 991px) {
        .school-logo .logo-text {
            font-size: 2rem;
            color: var(--primary-color);
            /* background: #1800CF;
            background: linear-gradient(to right, #1800CF 0%, #FF6E20 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent; */
        }

        .nav-links {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 20px;
            min-width: 50%;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            background-color: #FFFFFF;
            display: none;
            flex-direction: column;
            align-items: center;
            z-index: 999;
            width: max-content;
            /* shrink to fit content */
        }

        .nav-links.active {
            display: flex !important;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: row;
            width: auto !important;
            /* Shrink to fit */
            gap: 10px;
            padding: 10px 0;
            text-decoration: none;
        }

        .nav-links a i,
        .nav-links a span {
            display: inline-block;
        }

        .nav-links.active {
            display: flex !important;
            flex-direction: column;
        }


        .mobile-nav-toggle {
            display: block;
            cursor: pointer;
        }

        .nav-dropdown {
            width: 100%;
        }

        .dropdown-menu {
            position: static;
            transform: none;
            width: 100%;
            box-shadow: none;
            display: none;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-dropdown:hover .dropdown-menu,
        .nav-dropdown.active .dropdown-menu {
            display: block;
        }

        .nav-dropdown .dropdown-toggle {
            gap: 8px !important;
            /* Reduce the gap between icon and text */
            padding: 10px 0 !important;
            /* Adjust padding */
        }

        .nav-dropdown .dropdown-toggle i {
            margin-bottom: 0 !important;
            /* Remove the bottom margin that creates space */
        }

        .nav-dropdown .dropdown-toggle span {
            margin-left: 0 !important;
            /* Remove any left margin */
        }

        .dropdown-menu a {
            padding-left: 15px !important;
            /* Reduce left padding for dropdown items */
            gap: 8px !important;
            /* Consistent gap for dropdown items */
        }

        .dropdown-menu a i {
            font-size: 1rem !important;
            min-width: 20px;
            /* Ensure consistent icon width */
        }

        .section-title {
            font-size: 2rem;
        }

        .hero-content h1 {
            font-size: 2.5rem;
        }

        .hero-content .lead {
            font-size: 1.2rem;
        }

        .teachers-carousel .slick-prev,
        .teachers-carousel .slick-next {
            width: 40px;
            height: 40px;
        }
    }

    @media (max-width: 767px) {
        .school-logo .logo-text {
            font-size: 1.5rem;
        }

        .hero-slide {
            height: 70vh;
            min-height: 400px;
        }

        .teachers-carousel .row {
            flex-direction: column;
        }

        .teachers-carousel .col-lg-4 {
            margin-bottom: 20px;
        }

        .teachers-carousel .slick-prev {
            left: -15px;
        }

        .teachers-carousel .slick-next {
            right: -15px;
        }

        .section-title {
            font-size: 1.8rem;
        }

        .feature-card {
            padding: 20px;
        }

        /* .teacher-card {
            padding: 20px;
        } */

        .contact-form .form-control {
            padding: 12px 15px;
        }

        .footer-social-icons {
            justify-content: center;
        }

        .site-footer .text-center.text-lg-start {
            text-align: center !important;
            margin-top: 20px;
        }
    }

    @media (max-width: 575px) {
        .hero-slide {
            height: 60vh;
            min-height: 350px;
        }

        .hero-content h1 {
            font-size: 1.8rem;
        }

        .hero-buttons {
            flex-direction: column;
        }

        .school-logo .logo-text {
            font-size: 1.5rem;
        }

        .teachers-carousel .slick-prev,
        .teachers-carousel .slick-next {
            display: none !important;
        }
    }
</style>

<?php include_once(__DIR__ . '/../includes/header-close.php'); ?>

<div class="main-container">

    <!-- Custom Navigation Bar -->
    <header class="custom-nav-container">
        <div class="container custom-nav">
            <a href="#" class="school-logo">
                <img src="uploads/school/logo-square.png" alt="School Logo" onerror="this.style.display='none'">
                <span class="logo-text"><?= $schoolInfo['name']; ?></span>
            </a>
            <nav class="nav-links" id="navLinks">
                <a href="#about"><i class="fas fa-school text-primary"></i> <span>About</span></a>
                <a href="#academics"><i class="fas fa-book-open text-primary"></i> <span>Academics</span></a>
                <!-- Teachers dropdown -->
                <div class="nav-dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-chalkboard-teacher text-primary"></i>
                        <span>Teachers</span>
                    </a>
                    <div class="dropdown-menu">
                        <a href="#teachers"><i class="fas fa-users text-primary"></i> <span>All Teachers</span></a>
                        <?php if ($websiteConfig['teacher_application'] == 'yes'): ?>
                            <a href="enquiries/form/teacher-application.php"><i class="fas fa-graduation-cap text-primary"></i> <span>Apply as Teacher</span></a>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Gallery dropdown -->
                <div class="nav-dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-images text-primary"></i>
                        <span>Gallery</span>
                    </a>
                    <div class="dropdown-menu">
                        <a href="pages/photo-gallery.php"><i class="fas fa-camera-retro text-primary"></i> <span>Photo Gallery</span></a>
                        <a href="pages/video-gallery.php"><i class="fas fa-video text-primary"></i> <span>Video Gallery</span></a>
                    </div>
                </div>
                <a href="#notices"><i class="fas fa-bullhorn text-primary"></i> <span>Notices</span></a>
                <a href="#contact"><i class="fas fa-paper-plane text-primary"></i> <span>Contact</span></a>
                <?php if ($websiteConfig['admin_login_option_show'] == 'yes'): ?>
                    <div class="nav-dropdown">
                        <a class="btn-fun btn-primary btn-login dropdown-toggle" href="#" role="button" id="loginDropdownBtn"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-lock"></i> Login
                        </a>
                        <div class="dropdown-menu" aria-labelledby="loginDropdownBtn">
                            <a href="parent/"><i class="fa-solid fa-user-shield text-primary"></i> <span>Parent Login</span></a>
                            <a href="management/"><i class="fa-solid fa-cog text-primary"></i> <span>Admin Login</span></a>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="btn-fun btn-primary btn-login" href="parent/"><i class="fa-solid fa-lock"></i>Login</a>
                <?php endif; ?>
            </nav>
            <button class="mobile-nav-toggle" id="mobileNavToggle"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- Hero Carousel Section -->
    <section class="hero-slider">
        <?php if (!empty($carousel_images)): ?>
            <?php foreach ($carousel_images as $image): ?>
                <div class="hero-slide" style="background-image: url('<?= $image['image_url'] ?>');">
                    <div class="hero-content">
                        <h1><?= safe_htmlspecialchars($image['caption'] ?? 'Welcome to Our School') ?></h1>
                        <div class="hero-buttons">
                            <?php if (strtolower($websiteConfig['admission_open']) == 'yes'): ?>
                                <a href="enquiries/form/admission-enquiry.php" class="btn-fun btn-secondary">Apply For Admission</a>
                            <?php endif; ?>
                            <a href="#contact" class="btn-fun btn-light">Contact Us</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback hero content -->
            <div class="hero-slide" style="background-color: var(--primary-color);">
                <div class="hero-content">
                    <h1>Welcome to <?= $schoolInfo['name'] ?></h1>
                    <div class="hero-buttons">
                        <?php if (strtolower($websiteConfig['admission_open']) == 'yes'): ?>
                            <a href="enquiries/form/admission-enquiry.php" class="btn-fun btn-secondary">Apply For Admission</a>
                        <?php endif; ?>
                        <a href="#contact" class="btn-fun btn-light">Contact Us</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Wavy Divider -->
    <!-- <div class="wavy-divider">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path fill="#ffffff" fill-opacity="1" d="M0,64L48,80C96,96,192,128,288,128C384,128,480,96,576,85.3C672,75,768,85,864,96C960,107,1056,117,1152,106.7C1248,96,1344,64,1392,48L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
        </svg>
    </div> -->

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
                        <h4>Quality Education</h4>
                        <p>We provide excellent education with modern teaching methods and well-equipped classrooms.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-user-friends"></i></div>
                        <h4>Experienced Teachers</h4>
                        <p>Our qualified and dedicated teachers are committed to student success and development.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <h4>Safe & Fun Place</h4>
                        <p>Our school offers a safe and stimulating environment with modern facilities.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Notices Section -->
    <section id="notices" class="py-5">
        <div class="container">
            <h2 class="section-title">Notice Board</h2>
            <div class="notice-board">
                <?php
                $i = 0;
                foreach ($notices as $notice) {
                    if ($i == 3) break;
                    $i++;
                ?>
                    <div class="notice-pin">
                        <h4><?= safe_htmlspecialchars($notice['title']) ?></h4>
                        <span class="notice-date"><i class="far fa-calendar-alt me-2"></i><?= (new DateTime($notice['notice_date']))->format('d F Y'); ?></span>
                        <p><?= safe_htmlspecialchars($notice['content']) ?></p>
                        <a href="#" class="text-primary fw-bold">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                <?php } ?>
            </div>
            <div class="text-center mt-5">
                <a href="pages/notices.php" class="btn-fun btn-primary">View All Notices</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 my-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <img src="<?= $carousel_images[random_int(0, count($carousel_images) - 1)]['image_url'] ?>" alt="School Fun" class="img-fluid about-img">
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title text-start">About Our Fun School</h2>
                    <p class="fs-5 text-muted"><?= $schoolInfo['description']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Academics Section -->
    <section id="academics" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Our Academics</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="card-title text-center mb-4 fs-2">Fee Structure</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover text-center" style="border-radius: var(--border-radius-md); overflow:hidden;">
                                    <thead>
                                        <tr>
                                            <th class="py-3 fs-5 bg-primary text-white">Class</th>
                                            <th class="py-3 fs-5 bg-primary text-white">Monthly Fee</th>
                                            <th class="py-3 fs-5 bg-primary text-white">Annual Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($classes as $class) { ?>
                                            <tr>
                                                <td class="py-3 fs-5"><?= $class['class_name'] ?></td>
                                                <td class="py-3 fs-5">₹<?= number_format($class['amount']) ?></td>
                                                <td class="py-3 fs-5">₹<?= number_format($class['amount'] * 12) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Teachers Section -->
    <section id="teachers" class="py-5">
        <div class="container">
            <h2 class="section-title">Our Friendly Teachers</h2>
            <div class="teachers-carousel">
                <?php
                $i = 0;
                foreach ($teachers as $index => $teacher) {
                    // Start a new slide every 6 teachers
                    if ($i % 6 === 0) {
                        echo '<div><div class="row g-4">';
                    }
                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="teacher-card">
                            <img src="https://placehold.co/120x120/<?= str_replace('#', '', $theme_color) ?>/FFFFFF?text=<?= substr($teacher['name'], 0, 1) ?>" alt="<?= $teacher['name'] ?>">
                            <h4><?= $teacher['name'] ?></h4>
                            <p class="qualification fs-5"><?= $teacher['qualification'] ?></p>
                            <p class="subject_specialization text-muted"><?= $teacher['subject_specialization'] ?></p>
                        </div>
                    </div>
                <?php
                    $i++;
                    // Close the row and slide after every 6 teachers or at the end
                    if ($i % 6 === 0 || $i === count($teachers)) {
                        echo '</div></div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">School Gallery</h2>
            <div class="gallery-grid">
                <?php
                $i = 1;
                foreach ($galley_images as $image) {
                ?>
                    <a href="<?= $image['photo_url'] ?>" data-fancybox="gallery" data-caption="<?= $image['caption'] ?>" class="gallery-item">
                        <img src="<?= $image['thumbnail_url'] ?>" alt="<?= $image['caption'] ?>">
                    </a>
                <?php
                    if ($i == 6) break;
                    $i++;
                } ?>
            </div>
            <div class="text-center mt-5">
                <a href="pages/photo-gallery.php" class="btn-fun btn-primary">View More Photos</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="p-4 p-md-5 rounded-3" style="background-color: var(--bg-sky);">
                        <h4 class="mb-4">Send us a Message!</h4>
                        <form class="contact-form" action="mailto:<?= $schoolInfo['email'] ?>" method="post" enctype="text/plain">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control" name="phone-number" placeholder="Your Phone" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" rows="5" name="message" placeholder="Your Message" required></textarea>
                            </div>
                            <button type="submit" class="btn-fun btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 p-md-5">
                        <h4 class="mb-4">Find Us Here!</h4>
                        <p class="fs-5 mb-4"><i class="fas fa-map-marker-alt text-primary me-3"></i><?= $schoolInfo['address']; ?></p>
                        <p class="fs-5 mb-4"><i class="fas fa-phone-alt text-primary me-3"></i><?= $schoolInfo['phone']; ?></p>
                        <p class="fs-5 mb-4"><i class="fas fa-envelope text-primary me-3"></i><?= $schoolInfo['email']; ?></p>
                        <iframe src="<?= $schoolInfo['google_map_link']; ?>" width="100%" height="300" style="border:0; border-radius: var(--border-radius-md);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0 text-center text-lg-start">
                    <h5 class="mb-3"><?= $schoolInfo['name']; ?></h5>
                    <p>Building a bright future, one child at a time.</p>
                    <div class="footer-social-icons mt-4">
                        <a href="<?= $schoolInfo['facebook'] ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= $schoolInfo['whatsapp'] ?>"><i class="fab fa-whatsapp"></i></a>
                        <a href="<?= $schoolInfo['instagram'] ?>"><i class="fab fa-instagram"></i></a>
                        <a href="<?= $schoolInfo['youtube'] ?>"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 text-center mb-3">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="pages/about-us.php">About Us</a></li>
                        <li class="mb-2"><a href="pages/contact-us.php">Contact Us</a></li>
                        <li class="mb-2"><a href="pages/privacy-and-policy.php">Privacy & Policy</a></li>
                        <li class="mb-2"><a href="pages/terms-and-conditions.php">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="pages/refund-and-cancellation.php">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 text-center mb-3">
                    <h5 class="mb-3">Contact</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="tel:<?= $schoolInfo['phone']; ?>"><?= $schoolInfo['phone']; ?></a></li>
                        <li class="mb-2"><a href="mailto:<?= $schoolInfo['email']; ?>"><?= $schoolInfo['email']; ?></a></li>
                    </ul>
                </div>
                <div class="col-lg-3 text-center text-lg-start">
                    <a target="_blank" href="<?= $schoolInfo['app_download_link']; ?>" class="btn-fun bg-success"><i class="fa-brands fa-google-play me-2"></i> Download App</a>
                </div>
            </div>
            <div class="text-center mt-5 pt-4 border-top border-secondary">
                <p>&copy; <?php echo date("Y"); ?> <?= $schoolInfo['name']; ?> | Developed with <i class="fas fa-heart text-danger"></i> by <a href="https://benojir.github.io/portfolio" class="text-warning">Dahuk Technology</a></p>
            </div>
        </div>
    </footer>
</div>

<!-- Custom JS -->
<script>
    $(document).ready(function() {
        // Mobile Nav Toggle
        // Modify your existing mobile nav toggle code to close dropdowns when menu closes
        $('#mobileNavToggle').on('click', function() {
            $('#navLinks').slideToggle();
            $(this).find('i').toggleClass('fa-bars fa-times');
            $('.nav-dropdown').removeClass('active');
            $('.dropdown-menu').slideUp();
        });

        // Smooth scrolling for navigation links
        $('a[href*="#"]').on('click', function(e) {
            if ($(this).attr('href') === '#') {
                e.preventDefault();
                return;
            }

            var target = $($(this).attr('href'));
            var screenWidth = $(window).width();
            if (target.length && screenWidth <= 991) {
                // Close mobile menu if open
                if ($('#navLinks').is(':visible')) {
                    $('#navLinks').slideUp();
                    $('#mobileNavToggle').find('i').toggleClass('fa-bars fa-times');
                }

                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 500, 'linear');
            }
        });

        // Mobile dropdown toggle
        $('.dropdown-toggle').on('click', function(e) {
            if ($(window).width() <= 991) {
                e.preventDefault();
                $(this).parent().toggleClass('active');
                $(this).next('.dropdown-menu').slideToggle();

                // Close other open dropdowns
                $('.nav-dropdown').not($(this).parent()).removeClass('active');
                $('.dropdown-menu').not($(this).next()).slideUp();
            }
        });

        // Hero Slider with Slick
        $('.hero-slider').slick({
            dots: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            autoplay: true,
            autoplaySpeed: 5000,
            arrows: false
        });

        // Teachers Carousel with Slick
        $('.teachers-carousel').slick({
            dots: true,
            infinite: true,
            speed: 300,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>',
            nextArrow: '<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>',
            responsive: [{
                    breakpoint: 992,
                    settings: {
                        arrows: true
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        arrows: false,
                        dots: true
                    }
                }
            ]
        });
    });
</script>

<?php include_once(__DIR__ . '/../includes/body-close.php'); ?>