<?php
ob_start();
session_start();
require_once '../config/db.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}
// Include database connection file
$pg = isset($_GET['page']) ? $_GET['page'] : 'index';

// Array ( [user_id] => 1 
// [user_role] => admin 
// [user_verified] => 0 
// [user_email] => admin@arimoridgr.com.ng 
// [user_name] => Administrator 
// [logged_in] => 1 )
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Home | Estate Managers and General Contractors</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Arimorid is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
  <meta name="keywords" content="Arimorid, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
  <meta name="author" content="CodedThemes">

  <!-- [Favicon] icon -->
  <link rel="icon" href="../img/site-icon.png" type="image/x-icon"> <!-- [Google Font] Family -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
<!-- [Tabler Icons] https://tablericons.com -->
<link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" >
<!-- [Feather Icons] https://feathericons.com -->
<link rel="stylesheet" href="../assets/fonts/feather.css" >
<!-- [Font Awesome Icons] https://fontawesome.com/icons -->
<link rel="stylesheet" href="../assets/fonts/fontawesome.css" >
<!-- [Material Icons] https://fonts.google.com/icons -->
<link rel="stylesheet" href="../assets/fonts/material.css" >
<!-- [Template CSS Files] -->
<link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" >
<link rel="stylesheet" href="../assets/css/style-preset.css" >

</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
  <!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>
<!-- [ Pre-loader ] End -->
 <!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="../" class="b-brand text-primary">
        <img src="../img/site-icon.png" class="img-fluid" alt="logo" style="width: 40px; height: 40px;">
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item">
          <a href="../" class="pc-link">
            <span class="pc-micon"><i class="ti ti-dots"></i></span>
            <span class="pc-mtext">Visit Site</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="./" class="pc-link">
            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="?page=service-apartments" class="pc-link">
            <span class="pc-micon"><i class="ti ti-building"></i></span>
            <span class="pc-mtext">Service Apartments</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="?page=properties" class="pc-link">
            <span class="pc-micon"><i class="ti ti-building"></i></span>
            <span class="pc-mtext">Other Properties</span>
          </a>
        </li>
        <?php if ($user_role == 'owner'): ?>
        <li class="pc-item">
          <a href="?page=withdraw" class="pc-link">
            <span class="pc-micon"><i class="ti ti-cash"></i></span>
            <span class="pc-mtext">Withdraw</span>
          </a>
        </li>
        <?php endif; ?>
        <?php if ($user_role == 'admin'): ?>
        <li class="pc-item">
          <a href="?page=owners" class="pc-link">
            <span class="pc-micon"><i class="ti ti-users"></i></span>
            <span class="pc-mtext">Owners</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="?page=users" class="pc-link">
            <span class="pc-micon"><i class="ti ti-users"></i></span>
            <span class="pc-mtext">Users</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="?page=addons" class="pc-link">
            <span class="pc-micon"><i class="ti ti-list"></i></span>
            <span class="pc-mtext">Additional Services</span>
          </a>
        </li>
        <?php endif; ?>
        <li class="pc-item">
          <a href="?page=orders" class="pc-link">
            <span class="pc-micon"><i class="ti ti-shopping-cart"></i></span>
            <span class="pc-mtext">Orders</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="?page=transactions" class="pc-link">
            <span class="pc-micon"><i class="ti ti-credit-card"></i></span>
            <span class="pc-mtext">Transactions</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="?page=chat" class="pc-link">
            <span class="pc-micon"><i class="ti ti-messages"></i></span>
            <span class="pc-mtext">Messages</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end --> <!-- [ Header Topbar ] start -->
<header class="pc-header">
  <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
<div class="me-auto pc-mob-drp">
  <ul class="list-unstyled">
    <!-- ======= Menu collapse Icon ===== -->
    <li class="pc-h-item pc-sidebar-collapse">
      <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
    <li class="pc-h-item pc-sidebar-popup">
      <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
    <li class="dropdown pc-h-item d-inline-flex d-md-none">
      <a
        class="pc-head-link dropdown-toggle arrow-none m-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ti ti-search"></i>
      </a>
      <div class="dropdown-menu pc-h-dropdown drp-search">
        <form class="px-3">
          <div class="form-group mb-0 d-flex align-items-center">
            <i data-feather="search"></i>
            <input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . .">
          </div>
        </form>
      </div>
    </li>
    <li class="pc-h-item d-none d-md-inline-flex">
      <form class="header-search">
        <i data-feather="search" class="icon-search"></i>
        <input type="search" class="form-control" placeholder="Search here. . .">
      </form>
    </li>
  </ul>
</div>
<!-- [Mobile Media Block end] -->
<div class="ms-auto">
  <ul class="list-unstyled">
    <li class="dropdown pc-h-item">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <!-- <i class="ti ti-mail"></i> -->
      </a>
<li class="dropdown pc-h-item">
    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
        <i class="ti ti-message-circle"></i>
        <?php 
        $unread = $conn->query("SELECT COUNT(*) FROM messages WHERE receiver_id = {$_SESSION['user_id']} AND is_read = 0")->fetch_row()[0];
        if ($unread > 0): ?>
            <span class="badge bg-danger rounded-pill"><?= $unread ?></span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header d-flex align-items-center justify-content-between">
            <h5 class="m-0">Messages</h5>
            <a href="?page=chat" class="pc-head-link">View All</a>
        </div>
        <div class="dropdown-divider"></div>
        <div class="dropdown-header px-0 text-wrap header-notification-scroll position-relative" style="max-height: calc(100vh - 215px)">
            <?php
            $recent_messages = $conn->query("
                SELECT m.*, u.name as sender_name 
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.receiver_id = {$_SESSION['user_id']}
                ORDER BY m.created_at DESC LIMIT 5
            ");
            
            if ($recent_messages->num_rows > 0): ?>
                <div class="list-group list-group-flush w-100">
                    <?php while($msg = $recent_messages->fetch_assoc()): ?>
                        <a href="?page=chat&with=<?= $msg['sender_id'] ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avtar avtar-s rounded-circle bg-light-primary">
                                        <i class="ti ti-user f-18"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($msg['sender_name']) ?></h6>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars(substr($msg['message'], 0, 50)) ?><?= strlen($msg['message']) > 50 ? '...' : '' ?></p>
                                    <small class="text-muted"><?= date('M j, h:i A', strtotime($msg['created_at'])) ?></small>
                                </div>
                                <?php if (!$msg['is_read']): ?>
                                    <span class="badge bg-danger rounded-pill">New</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-3">
                    <i class="ti ti-message-off fs-4 text-muted"></i>
                    <p class="mt-2">No messages yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</li>
    </li>
    <li class="dropdown pc-h-item header-user-profile">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        data-bs-auto-close="outside"
        aria-expanded="false"
      >
        <img src="../assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar">
        <span><?= $_SESSION['user_name'] ?></span>
      </a>
      <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header">
          <div class="d-flex mb-1">
            <div class="flex-shrink-0">
              <img src="../assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar wid-35">
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="mb-1"><?= $_SESSION['user_name'] ?></h6>
              <span><?= ucwords($_SESSION['user_role']) ?></span>
            </div>
            <a href="../ac/action-logout.php" class="pc-head-link bg-transparent"><i class="ti ti-power text-danger"></i></a>
          </div>
        </div>
        <ul class="nav drp-tabs nav-fill nav-tabs" id="mydrpTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button
              class="nav-link active"
              id="drp-t1"
              data-bs-toggle="tab"
              data-bs-target="#drp-tab-1"
              type="button"
              role="tab"
              aria-controls="drp-tab-1"
              aria-selected="true"
              ><i class="ti ti-user"></i> Profile</button
            >
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              id="drp-t2"
              data-bs-toggle="tab"
              data-bs-target="#drp-tab-2"
              type="button"
              role="tab"
              aria-controls="drp-tab-2"
              aria-selected="false"
              ><i class="ti ti-settings"></i> Setting</button
            >
          </li>
        </ul>
        <div class="tab-content" id="mysrpTabContent">
          <div class="tab-pane fade show active" id="drp-tab-1" role="tabpanel" aria-labelledby="drp-t1" tabindex="0">
            <!-- <a href="#!" class="dropdown-item">
              <i class="ti ti-edit-circle"></i>
              <span>Edit Profile</span>
            </a> -->
            <a href="./?page=profile" class="dropdown-item">
              <i class="ti ti-user"></i>
              <span>View Profile</span>
            </a>
            <!-- <a href="#!" class="dropdown-item">
              <i class="ti ti-wallet"></i>
              <span>Billing</span>
            </a> -->
            <a href="../ac/action-logout.php" class="dropdown-item">
              <i class="ti ti-power"></i>
              <span>Logout</span>
            </a>
          </div>
          <div class="tab-pane fade" id="drp-tab-2" role="tabpanel" aria-labelledby="drp-t2" tabindex="0">
            <a href="#!" class="dropdown-item">
              <i class="ti ti-help"></i>
              <span>Support</span>
            </a>
            <a href="#!" class="dropdown-item">
              <i class="ti ti-user"></i>
              <span>Account Settings</span>
            </a>
            <a href="#!" class="dropdown-item">
              <i class="ti ti-lock"></i>
              <span>Privacy Center</span>
            </a>
            <a href="#!" class="dropdown-item">
              <i class="ti ti-messages"></i>
              <span>Feedback</span>
            </a>
            <a href="#!" class="dropdown-item">
              <i class="ti ti-list"></i>
              <span>History</span>
            </a>
          </div>
        </div>
      </div>
    </li>
  </ul>
</div>
 </div>
</header>
<!-- [ Header] end -->



  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">
      <?php include "pg/$pg.php"; ?>
    </div>
  </div>
  <!-- [ Main Content ] end -->
  <footer class="pc-footer">
    <div class="footer-wrapper container-fluid">
      <div class="row">
        <div class="col-sm my-1">
          <!-- <p class="m-0"
            >Arimorid &#9829; crafted by Team <a href="https://themeforest.net/user/codedthemes" target="_blank">Codedthemes</a> Distributed by <a href="https://themewagon.com/">ThemeWagon</a>.</p
          > -->
        </div>
        <div class="col-auto my-1">
          <ul class="list-inline footer-link mb-0">
            <li class="list-inline-item"><a href="../index.html">Home</a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <!-- [Page Specific JS] start -->
  <script src="../assets/js/plugins/apexcharts.min.js"></script>
  <script src="../assets/js/pages/dashboard-default.js"></script>
  <!-- [Page Specific JS] end -->
  <!-- Required Js -->
  <script src="../assets/js/plugins/popper.min.js"></script>
  <script src="../assets/js/plugins/simplebar.min.js"></script>
  <script src="../assets/js/plugins/bootstrap.min.js"></script>
  <script src="../assets/js/fonts/custom-font.js"></script>
  <script src="../assets/js/pcoded.js"></script>
  <script src="../assets/js/plugins/feather.min.js"></script>

  
  
  
  
  <script>layout_change('light');</script>
  
  
  
  
  <script>change_box_container('false');</script>
  
  
  
  <script>layout_rtl_change('false');</script>
  
  
  <script>preset_change("preset-1");</script>
  
  
  <script>font_change("Public-Sans");</script>
  
    

</body>
<!-- [Body] end -->

</html>
<?php
ob_end_flush();
?>