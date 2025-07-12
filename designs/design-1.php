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

<!-- Custom CSS -->
<style>
    .main-container {
        overflow: hidden;
    }

    .top-bar {
        background-color: var(--primary-color);
        color: white;
    }

    .top-bar a {
        color: #FFFFFF;
        text-decoration: none;
        padding: 0.45rem 0.45rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #FFFFFF;
    }

    .top-bar a:hover,
    .top-bar a:focus,
    .top-bar a:active {
        background-color: #FFFFFF;
        color: var(--primary-color);
    }

    .navbar {
        box-shadow: none;
    }

    .navbar-brand img {
        height: 50px;
        width: 50px;
        object-fit: cover;
    }

    .logo-container {
        align-items: center;
        text-align: center;
        padding: 0.8rem;
    }

    .logo-container .navbar-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
    }

    .navbar-brand-text {
        color: var(--secondary-color);
        text-transform: uppercase;
        font-size: 1.5rem;
        white-space: normal;
        /* Allows word wrapping */
        word-wrap: break-word;
        /* Breaks long words if needed */
        text-align: center;
        /* Optional: center-align text */
        max-width: 100%;
        /* Prevent overflow */
        font-family: "Oswald", sans-serif;
        font-optical-sizing: auto;
        font-weight: 700;
        font-style: normal;
    }

    .navbar-toggler {
        margin-left: auto;
        margin-right: auto;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

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

    .section-title {
        position: relative;
        margin-bottom: 30px;
        padding-bottom: 15px;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background-color: var(--primary-color);
    }

    .feature-box {
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 30px;
        transition: all 0.3s ease;
        border: 1px solid #eee;
        border-top: 4px solid var(--primary-color);
    }

    .feature-box:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
        border-top: 4px solid var(--primary-color);
    }

    .feature-icon {
        font-size: 40px;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    /* New Notice Card Design */
    .notice-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 20px;
        border: none;
    }

    .notice-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .notice-card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 15px;
        font-weight: 600;
    }

    .notice-card-body {
        padding: 20px;
        background-color: white;
    }

    .notice-card-body p {
        display: -webkit-box;
        line-clamp: 3;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notice-date-badge {
        background-color: var(--secondary-color);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
        margin-bottom: 10px;
    }

    /* Teachers Carousel */
    .teachers-carousel .teacher-card {
        margin: 0 15px;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border-left: 4px solid var(--primary-color);
        height: 100%;
    }

    .teacher-name {
        color: var(--primary-color);
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .teacher-qualification {
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .teacher-subject {
        color: var(--secondary-color);
        font-style: italic;
        font-size: 0.9rem;
    }

    .gallery-images-container {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
        width: 100%;
    }

    .gallery-images-container .gallery-item {
        border-radius: 0.8rem;
        overflow: hidden;
        width: 100%;
    }

    .gallery-images-container img {
        width: 100%;
        cursor: pointer;
    }

    /* Video Gallery */
    .video-gallery-item {
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .video-gallery-item iframe {
        width: 100%;
        height: 250px;
        border: none;
    }

    .video-caption {
        padding: 15px;
        background: white;
    }

    .video-caption h5 {
        margin-bottom: 5px;
    }

    /* Fee Structure Table */
    .fee-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    .fee-table th,
    .fee-table td {
        padding: 12px 15px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .fee-table th {
        background-color: var(--primary-color);
        color: white;
    }

    .fee-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .fee-table tr:hover {
        background-color: #f1f1f1;
    }

    .footer {
        background-color: var(--secondary-color);
        color: white;
        padding: 25px 0;
        text-align: center;
    }

    .social-icons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 10px;
    }

    .social-icons a {
        text-decoration: none;
        font-size: 1rem;
        transition: color 0.3s ease;
        transition: background-color 0.3s ease;
        color: #FFFFFF;
        padding: 0.5rem 0.75rem;
        border-radius: 50%;
        border: 1px solid #FFFFFF;
    }

    .social-icons a:hover {
        border-color: #FFFFFF;
        background-color: #FFFFFF;
        color: var(--primary-color);
    }

    .footer-links-container {
        margin-left: 2rem;
    }

    .footer-links {
        text-align: left;
        word-wrap: break-word;
        overflow: hidden;
        max-width: fit-content;
    }

    .footer-ul {
        padding: 0;
        margin: 0px 20px;
        text-align: left;
        word-wrap: break-word;
        overflow: hidden;
    }

    .footer-ul li {
        list-style: none;
        margin: 5px 0;
    }

    .footer-ul a {
        color: #3ca4e9;
        text-decoration: none;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .gallery-images-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 440px) {
        .gallery-images-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {

        .logo-container .navbar-brand {
            flex-direction: column;
            align-items: center;
        }

        .hero-slide {
            height: 60vh;
            min-height: 400px;
        }

        .teachers-carousel .teacher-card {
            margin: 0 5px;
            padding: 15px;
        }

        .video-gallery-item iframe {
            height: 200px;
        }

        .top-bar {
            background-color: white;
            color: #212529;
        }

        .hide-in-smsc {
            display: none !important;
        }

        .top-bar a {
            color: var(--primary-color);
            text-decoration: none;
            padding: 0.45rem 0.45rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--primary-color);
        }

        .top-bar a:hover,
        .top-bar a:focus,
        .top-bar a:active {
            background-color: var(--primary-color);
            color: white;
        }
    }
</style>

<?php include_once(__DIR__ . '/../includes/header-close.php'); ?>

<div class="main-container">

    <!-- Top Bar -->
    <div class="top-bar py-2">
        <div class="container">
            <div class="row align-items-center text-center text-md-start">
                <!-- Contact Info for Desktop -->
                <div class="hide-in-smsc col-md-6 mb-2 mb-md-0">
                    <i class="fas fa-phone-alt me-2"></i> <?= $schoolInfo['phone']; ?>
                    <span class="ms-3">
                        <i class="fas fa-envelope me-2"></i> <?= $schoolInfo['email']; ?>
                    </span>
                </div>

                <!-- Social Icons (including mobile contact) -->
                <div
                    class="col-md-6 text-center text-md-end d-flex justify-content-center justify-content-md-end flex-wrap gap-2">
                    <a href="tel:<?= $schoolInfo['phone']; ?>"><i class="fas fa-phone-alt"></i></a>
                    <a href="mailto:<?= $schoolInfo['email']; ?>"><i class="fas fa-envelope"></i></a>
                    <a href="<?= $schoolInfo['facebook']; ?>"><i class="fab fa-facebook"></i></a>
                    <a href="<?= $schoolInfo['youtube']; ?>"><i class="fab fa-youtube"></i></a>
                    <a href="<?= $schoolInfo['instagram']; ?>"><i class="fab fa-instagram"></i></a>
                    <a href="<?= $schoolInfo['whatsapp']; ?>"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>


    <!-- School Name & Logo Section -->
    <div class="logo-container container">
        <a class="navbar-brand" href="#">
            <img src="uploads/school/logo-square.png" alt="School Logo" onerror="this.style.display='none'"
                class="rounded">
            <div class="navbar-brand-text"><?= $schoolInfo['name']; ?></div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container d-flex flex-column align-items-center">

            <!-- Centered toggler -->
            <button class="navbar-toggler mx-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Collapsible nav content -->
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Your nav items -->
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="#about"><i class="fa-solid fa-circle-info"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="#academics"><i class="fa-solid fa-school"></i> Academics</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link text-primary dropdown-toggle" href="#" id="teachersDropdown" role="button">
                            <i class="fa-solid fa-graduation-cap"></i> Teachers
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="teachersDropdown">
                            <li><a class="dropdown-item" href="#teachers"><i class="fa-solid fa-graduation-cap me-1"></i> Our Teachers</a></li>
                            <?php if ($websiteConfig['teacher_application'] == 'yes'): ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="enquiries/form/teacher-application.php"><i class="fa-solid fa-user-graduate me-1"></i> Apply as Teacher</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="#gallery"><i class="fa-solid fa-photo-film"></i> Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="#video-gallery"><i class="fa-solid fa-video"></i> Videos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="#notices"><i class="fa-solid fa-volume-low"></i> Notices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="#contact"><i class="fa-solid fa-inbox"></i> Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <?php if ($websiteConfig['admin_login_option_show'] == 'yes'): ?>
                            <div class="dropdown">
                                <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="loginDropdownBtn"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-lock"></i> Login
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="loginDropdownBtn">
                                    <li><a class="dropdown-item" href="parent/"><i class="fa-solid fa-user-shield me-1"></i> Parent Login</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="management/"><i class="fa-solid fa-cog me-1"></i> Admin Login</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a class="btn btn-primary" href="parent/"><i class="fa-solid fa-lock"></i> Login</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>



    <!-- Hero Slider -->
    <section class="hero-slider">
        <?php
        foreach ($carousel_images as $carousel_image) { ?>
            <div class="hero-slide" style="background-image: url('<?= $carousel_image['image_url'] ?>');">
                <div class="hero-content">
                    <div class="container">
                        <h1 class="display-4 fw-bold mb-4"><?= ucwords($carousel_image['caption']) ?></h1>
                        <?php
                        if (strtolower($websiteConfig['admission_open']) == 'yes') {
                            echo '<a href="enquiries/form/admission-enquiry.php" class="btn btn-primary btn-lg me-2">Apply Admission</a>';
                        }
                        ?>
                        <a href="#contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                    </div>
                </div>
            </div>
        <?php }
        ?>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box shadow-sm">
                        <div class="feature-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4>Quality Education</h4>
                        <p>We provide excellent education with modern teaching methods and well-equipped classrooms.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box shadow-sm">
                        <div class="feature-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4>Experienced Teachers</h4>
                        <p>Our qualified and dedicated teachers are committed to student success and development.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box shadow-sm">
                        <div class="feature-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <h4>Modern Facilities</h4>
                        <p>Our school offers a safe and stimulating environment with modern facilities.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Notices Section (Moved Above About Section) -->
    <section id="notices" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Latest Notices</h2>
            <div class="row">
                <?php
                $i = 0;
                foreach ($notices as $notice) {
                    if ($i == 3) {
                        break;
                    }
                    $i++; ?>

                    <div class="col-md-4 mb-4">
                        <div class="notice-card h-100">
                            <div class="notice-card-header">
                                <?= safe_htmlspecialchars($notice['title']) ?>
                            </div>
                            <div class="notice-card-body">
                                <span class="notice-date-badge"><?= (new DateTime($notice['notice_date']))->format('d F Y'); ?></span>
                                <p><?= safe_htmlspecialchars($notice['content']) ?></p>
                                <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                            </div>
                        </div>
                    </div>

                <?php }
                ?>
            </div>
            <div class="text-center mt-3">
                <a href="pages/notices.php" class="btn btn-primary">View All Notices</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0 px-4">
                    <h2 class="section-title">About Our School</h2>
                    <p><?= $schoolInfo['description']; ?></p>
                    <div class="row mt-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary me-2"></i>
                                <span>Qualified Teachers</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary me-2"></i>
                                <span>Safe Environment</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary me-2"></i>
                                <span>Modern Facilities</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary me-2"></i>
                                <span>Extracurricular Activities</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="<?= $carousel_images[random_int(0, count($carousel_images) - 1)]['image_url'] ?>" alt="School Building"
                        class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Academics Section -->
    <section id="academics" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Our Academics</h2>
            <div class="row justify-content-center">
                <div class="col-lg-6 mb-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-school text-primary mb-3" style="font-size: 2.5rem;"></i>
                            <h4 class="card-title">Classes Offered</h4>
                            <p class="card-text">We offer classes from Nursery to Class Four with well-structured
                                curriculum.</p>
                            <ul class="list-group list-group-flush">
                                <?php
                                foreach ($classes as $class) {
                                    echo '<li class="list-group-item">' . $class['class_name'] . '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h4 class="card-title text-center mb-4">Fee Structure</h4>
                            <div class="table-responsive">
                                <table class="fee-table">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Monthly Fee</th>
                                            <th>Annual Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($classes as $class) { ?>
                                            <tr>
                                                <td><?= $class['class_name'] ?></td>
                                                <td>₹<?= number_format($class['amount']) ?></td>
                                                <td>₹<?= number_format($class['amount'] * 12) ?></td>
                                            </tr>
                                        <?php }
                                        ?>

                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted small text-center">* Additional charges may apply for transportation and
                                extracurricular activities</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Teachers Section as Carousel -->
    <section id="teachers" class="py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Our Teachers</h2>
            <div class="teachers-carousel">

                <?php
                $i = 0;

                foreach ($teachers as $index => $teacher) {
                    // Start a new slide every 6 teachers
                    if ($i % 6 === 0) {
                        echo '<div><div class="row">';
                    }

                    echo <<<HTML
                        <div class="col-md-4 mb-4">
                            <div class="teacher-card">
                                <h3 class="teacher-name">{$teacher['name']}</h3>
                                <p class="teacher-qualification">{$teacher['qualification']}</p>
                                <p class="teacher-subject">{$teacher['subject_specialization']}</p>
                            </div>
                        </div>
                    HTML;

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
            <h2 class="section-title text-center mb-5">School Gallery</h2>
            <div class="gallery-images-container">
                <?php
                $i = 1;
                foreach ($galley_images as $image) {
                    $formattedDate = (new DateTime($image['event_date']))->format('d F Y');
                    echo <<<HTML
                    <div class="gallery-item shadow" data-caption="{$image['caption']} ({$formattedDate})" data-fancybox data-src="{$image['photo_url']}">
                        <img src="{$image['thumbnail_url']}" alt="{$image['caption']}">
                    </div>
                    HTML;

                    if ($i == 6) {
                        break;
                    }
                    $i++;
                }
                ?>
            </div>
            <div class="text-center mt-4">
                <a href="pages/photo-gallery.php" class="btn btn-primary">View More</a>
            </div>
        </div>
    </section>

    <!-- Video Gallery Section -->
    <section id="video-gallery" class="py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Video Gallery</h2>
            <div class="row">
                <?php
                $i = 1;
                foreach ($galley_videos as $video) {
                    $formattedDate = (new DateTime($video['event_date']))->format('d F Y');
                    echo <<<HTML
                    <div class="col-md-6 mb-4">
                        <div class="video-gallery-item">
                            <iframe src="https://www.youtube.com/embed/{$video['video_yt_id']}" allowfullscreen></iframe>
                            <div class="video-caption">
                                <h5>{$video['video_title']}</h5>
                                <p class="text-muted small">Published: {$formattedDate}</p>
                            </div>
                        </div>
                    </div>
                    HTML;

                    if ($i == 4) {
                        break;
                    }

                    $i++;
                }
                ?>
            </div>
            <div class="text-center mt-4">
                <a href="pages/video-gallery.php" class="btn btn-primary">View More Videos</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Contact Us</h2>
            <div class="row">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-5">
                            <h4 class="mb-4">Get In Touch</h4>
                            <form action="mailto:<?= $schoolInfo['email'] ?>" method="post" enctype="text/plain">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="name" placeholder="Your Name">
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="phone-number" placeholder="Your Phone Number">
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" placeholder="Subject">
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="5" name="message" placeholder="Your Message"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-5">
                            <h4 class="mb-4">Contact Information</h4>
                            <div class="d-flex mb-4">
                                <i class="fas fa-map-marker-alt text-primary mt-1 me-3"></i>
                                <div>
                                    <h5 class="mb-1">Address</h5>
                                    <p class="mb-0"><?= $schoolInfo['address']; ?></p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <i class="fas fa-phone-alt text-primary mt-1 me-3"></i>
                                <div>
                                    <h5 class="mb-1">Phone</h5>
                                    <p class="mb-0"><?= $schoolInfo['phone']; ?></p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <i class="fas fa-envelope text-primary mt-1 me-3"></i>
                                <div>
                                    <h5 class="mb-1">Email</h5>
                                    <p class="mb-0"><?= $schoolInfo['email']; ?></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <iframe src="<?=$schoolInfo['google_map_link']?>" width="100%" height="260"
                                    style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <img src="uploads/school/logo-square.png" alt="School Logo" class="mb-3 rounded"
                        style="height: 60px; width: 60px; object-fit: cover;">
                    <h4 class="text-white mb-3"><?= $schoolInfo['name']; ?></h4>
                    <div class="social-icons mt-4">
                        <a href="<?= $schoolInfo['facebook'] ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= $schoolInfo['whatsapp'] ?>"><i class="fab fa-whatsapp"></i></a>
                        <a href="<?= $schoolInfo['instagram'] ?>"><i class="fab fa-instagram"></i></a>
                        <a href="<?= $schoolInfo['youtube'] ?>"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links-container col-lg-2 col-md-6 mb-5 mb-md-0">
                    <h5 class="footer-links">Quick Links</h5>
                    <ul class="footer-ul">
                        <li><a href="pages/about-us.php">About Us</a></li>
                        <li><a href="pages/contact-us.php">Contact Us</a></li>
                        <li><a href="pages/privacy-and-policy.php">Privacy & Policy</a></li>
                        <li><a href="pages/terms-and-conditions.php">Terms & Conditions</a></li>
                        <li><a href="pages/refund-and-cancellation.php">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="footer-links-container col-lg-3 col-md-6">
                    <h5 class="footer-links">Contact Info</h5>
                    <ul class="footer-ul">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> <?= $schoolInfo['address']; ?></li>
                        <li class="mb-2"><i class="fas fa-phone-alt me-2"></i> <?= $schoolInfo['phone']; ?></li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> <?= $schoolInfo['email']; ?></li>
                    </ul>
                </div>
            </div>
            <div class="mt-5 d-flex justify-content-center gap-3">
                <a target="_blank" href="<?= $schoolInfo['google_map_link'];?>">
                    <button class="btn btn-primary fw-bold"><i class="fa-solid fa-map-location-dot"></i>
                        Google Map</button>
                </a>
                <a target="_blank" href="<?= $schoolInfo['app_download_link']; ?>">
                    <button class="btn btn-success fw-bold"><i class="fa-brands fa-google-play"></i> Play
                        Store</button>
                </a>
            </div>
            <div class="copyright mt-4 text-center">
                <p class="mb-0 fw-bold fs-5">&copy; <?php echo date("Y"); ?> <?= $schoolInfo['name']; ?>. All Rights Reserved.</p><br> <i
                    class="fa-solid fa-code"></i> <span>Developed by: </span><a target="_blank" href="https://benojir.github.io/portfolio" class="mb-0 text-decoration-none text-warning">Dahuk
                    Technology</a>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="btn btn-primary back-to-top" style="position: fixed; bottom: 20px; right: 20px; display: none;">
        <i class="fas fa-arrow-up"></i>
    </a>

</div>

<!-- Custom JS -->
<script>
    $(document).ready(function() {
        // Hero Slider
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

        // Teachers Carousel
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
                breakpoint: 768,
                settings: {
                    arrows: false
                }
            }]
        });

        // Back to Top Button
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('.back-to-top').fadeIn();
            } else {
                $('.back-to-top').fadeOut();
            }
        });

        $('.back-to-top').click(function() {
            $('html, body').animate({
                scrollTop: 0
            }, 800);
            return false;
        });

        // Smooth scrolling for navigation links
        $('a[href*="#"]').on('click', function(e) {
            e.preventDefault();

            $('html, body').animate({
                    scrollTop: $($(this).attr('href')).offset().top - 70,
                },
                500,
                'linear'
            );
        });

        // Navbar background change on scroll
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('.navbar').addClass('shadow-sm');
                $('.navbar').css('background', 'rgba(255, 255, 255, 0.95)');
            } else {
                $('.navbar').removeClass('shadow-sm');
                $('.navbar').css('background', 'white');
            }
        });
    });
</script>
<?php include_once(__DIR__ . '/../includes/body-close.php'); ?>