<?php
require_once './config/db.php';
session_start();
$pg = isset($_GET['page']) ? $_GET['page'] : 'index';

$sql = "SELECT * FROM properties ORDER BY id DESC LIMIT 6 OFFSET 0";
$qry = "SELECT * FROM service_apartments ORDER BY id DESC LIMIT 6 OFFSET 0";
if (isset($_GET['view'])) {
    $view = test_input($_GET['view']);
    $offset = ($view - 1) * 6;
    $sql = "SELECT * FROM properties ORDER BY id DESC LIMIT 6 OFFSET $offset";
    $qry = "SELECT * FROM service_apartments ORDER BY id DESC LIMIT 6 OFFSET $offset";
} else {
    $view = 1;
    $offset = 0;
}
$result = mysqli_query($conn, $sql);
$properties = mysqli_fetch_all($result, MYSQLI_ASSOC);
$properties_count = mysqli_num_rows($result);
if ($properties_count == 0) {
    $properties = array();
}

$result = mysqli_query($conn, $qry);
$service_apartments = mysqli_fetch_all($result, MYSQLI_ASSOC);
$service_apartments_count = mysqli_num_rows($result);
if ($service_apartments_count == 0) {
    $service_apartments = array();
}

define('ADDRESS', 'No. 55B Zazzau Road, Lagos, Nigeria');
define('PHONE', '+2347016928568');
define('EMAIL', 'info@arimoridgr.com.ng');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Arimorid - Estate Managers and General Contractors</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <!-- <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div> -->
        <!-- Spinner End -->


        <!-- Navbar Start -->
        <div class="container-fluid nav-bar bg-transparent">
            <nav class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
                <a href="./" class="navbar-brand d-flex align-items-center text-center">
                    <div class="icon p-2 me-2">
                        <img class="img-fluid" src="img/site-icon.png" alt="Icon" style="width: 30px; height: 30px;">
                    </div>
                    <h1 class="m-0 text-primary">Arimorid</h1>
                </a>
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto">
                        <a href="./" class="nav-item nav-link active">Home</a>
                        <a href="?page=about" class="nav-item nav-link">About</a>
                        <a href="?page=service-apartments" class="nav-item nav-link">Service Apartments</a>
                        <a href="?page=property-list" class="nav-item nav-link">Properties</a>
                        <a href="?page=contact" class="nav-item nav-link">Contact</a>
                    </div>
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <a href="./dashboard/" class="btn btn-primary px-3 d-none d-lg-flex">Dashboard</a>
                    <?php else: ?>
                        <a href="./?page=login" class="btn btn-primary px-3 d-none d-lg-flex">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
        <!-- Navbar End -->


        <?php include "pg/$pg.php"; ?>
        

        <!-- Footer Start -->
        <div class="container-fluid bg-dark text-white-50 footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-lg-4 col-md-6">
                        <h5 class="text-white mb-4">Get In Touch</h5>
                        <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i><?= ADDRESS ?></p>
                        <p class="mb-2"><i class="fa fa-phone-alt me-3"></i><?= PHONE ?></p>
                        <p class="mb-2"><i class="fa fa-envelope me-3"></i><?= EMAIL ?></p>
                        <div class="d-flex pt-2">
                            <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-youtube"></i></a>
                            <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="text-white mb-4">Quick Links</h5>
                        <a class="btn btn-link text-white-50" href="./?page=about">About Us</a>
                        <a class="btn btn-link text-white-50" href="./?page=contact">Contact Us</a>
                        <a class="btn btn-link text-white-50" href="./?page=services">Our Services</a>
                        <a class="btn btn-link text-white-50" href="./?page=privacy_policy">Privacy Policy</a>
                        <a class="btn btn-link text-white-50" href="./?page=terms_condition">Terms & Condition</a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="text-white mb-4">Photo Gallery</h5>
                        <div class="row g-2 pt-2">
                            <div class="col-4">
                                <img class="img-fluid rounded bg-light p-1" src="img/property-1.jpg" alt="">
                            </div>
                            <div class="col-4">
                                <img class="img-fluid rounded bg-light p-1" src="img/property-2.jpg" alt="">
                            </div>
                            <div class="col-4">
                                <img class="img-fluid rounded bg-light p-1" src="img/property-3.jpg" alt="">
                            </div>
                            <div class="col-4">
                                <img class="img-fluid rounded bg-light p-1" src="img/property-4.jpg" alt="">
                            </div>
                            <div class="col-4">
                                <img class="img-fluid rounded bg-light p-1" src="img/property-5.jpg" alt="">
                            </div>
                            <div class="col-4">
                                <img class="img-fluid rounded bg-light p-1" src="img/property-6.jpg" alt="">
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-lg-3 col-md-6">
                        <h5 class="text-white mb-4">Newsletter</h5>
                        <p>Dolor amet sit justo amet elitr clita ipsum elitr est.</p>
                        <div class="position-relative mx-auto" style="max-width: 400px;">
                            <input class="form-control bg-transparent w-100 py-3 ps-4 pe-5" type="text" placeholder="Your email">
                            <button type="button" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">SignUp</button>
                        </div>
                    </div> -->
                </div>
            </div>
            <div class="container">
                <div class="copyright">
                    <div class="row">
                        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                            <!-- &copy; <a class="border-bottom" href="#">Your Site Name</a>, All Right Reserved.  -->
							
							<!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
							<!-- Designed By <a class="border-bottom" href="https://htmlcodex.com">HTML Codex</a> -->
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <div class="footer-menu">
                                <a href="./">Home</a>
                                <!-- <a href="">Cookies</a> -->
                                <a href="./?page=contact">Help</a>
                                <a href="./?page=faq">FAQs</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>