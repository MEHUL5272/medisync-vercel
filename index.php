<?php
session_start();

require_once "config/db.php";

$doctors = $db->doctors;

$doctorList = $doctors->find(
    [
        "status" => "active"
    ],
    [
        "sort" => [
            "name" => 1
        ],
        "limit" => 6
    ]
)->toArray();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>MediSync - Healthcare & Appointment System</title>

    <style>

        * {
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f8fa;
            color: #222;
        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #198754;
            color: white;
            padding: 16px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 23px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            white-space: nowrap;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        .login-btn {
            background: white;
            color: #198754 !important;
            padding: 9px 16px;
            border-radius: 6px;
            font-weight: bold;
        }

        .signup-btn {
            background: #0f5132;
            color: white !important;
            padding: 9px 16px;
            border-radius: 6px;
            font-weight: bold;
        }


        /* HAMBURGER BUTTON */

        .menu-btn {
            display: none;
            background: transparent;
            border: none;
            color: white;
            font-size: 30px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }


        /* =========================
           HERO
        ========================= */

        .hero {
            min-height: 560px;
            padding: 70px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            background:
                linear-gradient(
                    135deg,
                    #e8f7ef,
                    #f4f8fa
                );
        }

        .hero-content {
            width: 55%;
        }

        .hero-content h1 {
            font-size: 48px;
            margin: 0 0 20px;
            color: #146c43;
            line-height: 1.15;
        }

        .hero-content h1 span {
            color: #198754;
        }

        .hero-content p {
            font-size: 18px;
            color: #555;
            line-height: 1.7;
            max-width: 650px;
        }

        .hero-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .primary-btn,
        .secondary-btn {
            padding: 13px 22px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        .primary-btn {
            background: #198754;
            color: white;
        }

        .secondary-btn {
            background: white;
            color: #198754;
            border: 1px solid #198754;
        }

        .hero-card {
            width: 330px;
            background: white;
            padding: 35px;
            border-radius: 18px;
            text-align: center;
            box-shadow:
                0 10px 30px rgba(0,0,0,0.1);
        }

        .hero-icon {
            font-size: 80px;
            margin-bottom: 15px;
        }

        .hero-card h2 {
            color: #198754;
            margin-bottom: 10px;
        }

        .hero-card p {
            color: #666;
            line-height: 1.6;
        }


        /* =========================
           SECTION
        ========================= */

        .section {
            padding: 70px 6%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 32px;
            color: #146c43;
            margin-bottom: 10px;
        }

        .section-title p {
            color: #666;
            max-width: 650px;
            margin: auto;
            line-height: 1.6;
        }


        /* =========================
           SERVICES
        ========================= */

        .services {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 25px;
        }

        .service-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);
            transition: 0.2s;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .service-card h3 {
            color: #198754;
        }

        .service-card p {
            color: #666;
            line-height: 1.6;
        }


        /* =========================
           DOCTORS
        ========================= */

        .doctors {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 25px;
        }

        .doctor-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);
        }

        .doctor-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #d1e7dd;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
        }

        .doctor-card h3 {
            margin-bottom: 8px;
        }

        .specialization {
            color: #198754;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .doctor-info {
            color: #666;
            margin: 7px 0;
            font-size: 14px;
        }

        .doctor-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 9px 15px;
            background: #198754;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .view-all {
            text-align: center;
            margin-top: 30px;
        }


        /* =========================
           ABOUT
        ========================= */

        .about {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .about-box {
            flex: 1;
        }

        .about-icon {
            font-size: 100px;
            text-align: center;
            background: #e8f7ef;
            padding: 40px;
            border-radius: 20px;
        }

        .about-box h2 {
            color: #146c43;
            font-size: 32px;
        }

        .about-box p {
            color: #555;
            line-height: 1.8;
        }

        .about-list {
            list-style: none;
            padding: 0;
        }

        .about-list li {
            margin: 13px 0;
            color: #444;
        }


        /* =========================
           CONTACT
        ========================= */

        .contact {
            background: #e8f7ef;
        }

        .contact-grid {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 25px;
        }

        .contact-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .contact-card h3 {
            color: #198754;
        }

        .contact-card p {
            color: #666;
            line-height: 1.6;
        }


        /* =========================
           CTA
        ========================= */

        .cta {
            background: #198754;
            color: white;
            text-align: center;
            padding: 60px 20px;
        }

        .cta h2 {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .cta p {
            margin-bottom: 25px;
            opacity: 0.95;
        }

        .cta a {
            display: inline-block;
            background: white;
            color: #198754;
            padding: 12px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #12372a;
            color: white;
            padding: 30px 6%;
            text-align: center;
        }

        footer p {
            margin: 7px 0;
            color: #d7e7df;
        }


        /* =========================
           TABLET
        ========================= */

        @media(max-width: 900px) {

            .hero {
                flex-direction: column;
                text-align: center;
            }

            .hero-content {
                width: 100%;
            }

            .hero-content p {
                margin: auto;
            }

            .services,
            .doctors,
            .contact-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .about {
                flex-direction: column;
            }

        }


        /* =========================
           MOBILE NAVBAR
        ========================= */

        @media(max-width: 700px) {

            .navbar {
                padding: 14px 5%;
                position: sticky;
                top: 0;
            }

            .navbar-top {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .menu-btn {
                display: block;
            }

            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                padding-top: 15px;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links a {
                width: 100%;
                text-align: center;
                padding: 12px 10px;
                border-radius: 6px;
            }

            .nav-links a:hover {
                background: rgba(255,255,255,0.12);
                text-decoration: none;
            }

            .nav-links .login-btn,
            .nav-links .signup-btn {
                margin-top: 5px;
            }

            .hero {
                padding: 50px 5%;
            }

            .hero-content h1 {
                font-size: 36px;
            }

            .hero-card {
                width: 100%;
            }

        }


        /* =========================
           SMALL MOBILE
        ========================= */

        @media(max-width: 600px) {

            .services,
            .doctors,
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 50px 5%;
            }

            .hero-buttons {
                justify-content: center;
            }

            .primary-btn,
            .secondary-btn {
                width: 100%;
                text-align: center;
            }

            .about-icon {
                font-size: 70px;
                padding: 30px;
            }

            .section-title h2 {
                font-size: 28px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <div class="navbar-top">

        <a
            href="index.php"
            class="logo"
        >
            🩺 MediSync
        </a>


        <!-- MOBILE MENU BUTTON -->

        <button
            type="button"
            class="menu-btn"
            id="menuBtn"
            onclick="toggleMenu()"
            aria-label="Open Menu"
        >
            ☰
        </button>

    </div>


    <div
        class="nav-links"
        id="navLinks"
    >

        <a href="#home">
            Home
        </a>

        <a href="#services">
            Services
        </a>

        <a href="#doctors">
            Doctors
        </a>

        <a href="#about">
            About
        </a>

        <a href="#contact">
            Contact
        </a>


        <?php if (isset($_SESSION["user_id"])): ?>

            <?php if (
                ($_SESSION["role"] ?? "") === "admin"
            ): ?>

                <a href="admin/dashboard.php">
                    Dashboard
                </a>

            <?php else: ?>

                <a href="user/dashboard.php">
                    Dashboard
                </a>

            <?php endif; ?>


            <a
                href="user/logout.php"
                class="login-btn"
            >
                Logout
            </a>

        <?php else: ?>

            <a
                href="user/login.php"
                class="login-btn"
            >
                Login
            </a>

            <a
                href="user/signup.php"
                class="signup-btn"
            >
                Sign Up
            </a>

        <?php endif; ?>

    </div>

</nav>



<!-- =========================
     HERO
========================= -->

<section
    class="hero"
    id="home"
>

    <div class="hero-content">

        <h1>

            Your Health,

            <span>
                Our Priority
            </span>

        </h1>


        <p>

            MediSync makes healthcare management
            simple. Find doctors, manage appointments
            and stay connected with your healthcare
            journey from one convenient platform.

        </p>


        <div class="hero-buttons">

            <a
                href="user/doctors.php"
                class="primary-btn"
            >
                👨‍⚕️ Find a Doctor
            </a>


            <?php if (isset($_SESSION["user_id"])): ?>

                <a
                    href="user/appointments.php"
                    class="secondary-btn"
                >
                    📅 My Appointments
                </a>

            <?php else: ?>

                <a
                    href="user/signup.php"
                    class="secondary-btn"
                >
                    📅 Book Appointment
                </a>

            <?php endif; ?>

        </div>

    </div>


    <div class="hero-card">

        <div class="hero-icon">
            🏥
        </div>

        <h2>
            MediSync
        </h2>

        <p>
            Smart healthcare management
            for patients, doctors and
            administrators.
        </p>

    </div>

</section>



<!-- =========================
     SERVICES
========================= -->

<section
    class="section"
    id="services"
>

    <div class="section-title">

        <h2>
            Our Services
        </h2>

        <p>
            Everything you need to manage
            your healthcare journey.
        </p>

    </div>


    <div class="services">


        <div class="service-card">

            <div class="service-icon">
                👨‍⚕️
            </div>

            <h3>
                Find Doctors
            </h3>

            <p>
                Search and explore available
                doctors by name and specialization.
            </p>

        </div>


        <div class="service-card">

            <div class="service-icon">
                📅
            </div>

            <h3>
                Book Appointments
            </h3>

            <p>
                Schedule appointments with
                your preferred doctor easily.
            </p>

        </div>


        <div class="service-card">

            <div class="service-icon">
                🔔
            </div>

            <h3>
                Appointment Management
            </h3>

            <p>
                Track your appointments and
                stay updated with their status.
            </p>

        </div>


    </div>

</section>



<!-- =========================
     DOCTORS
========================= -->

<section
    class="section"
    id="doctors"
>

    <div class="section-title">

        <h2>
            Our Doctors
        </h2>

        <p>
            Explore our currently available
            healthcare professionals.
        </p>

    </div>


    <?php if (count($doctorList) > 0): ?>

        <div class="doctors">

            <?php foreach ($doctorList as $doctor): ?>

                <div class="doctor-card">

                    <div class="doctor-icon">
                        👨‍⚕️
                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $doctor["name"]
                            ?? "Doctor"
                        ) ?>

                    </h3>


                    <div class="specialization">

                        <?= htmlspecialchars(
                            $doctor["specialization"]
                            ?? "General Physician"
                        ) ?>

                    </div>


                    <div class="doctor-info">

                        💼

                        <?= htmlspecialchars(
                            $doctor["experience"]
                            ?? "-"
                        ) ?>

                    </div>


                    <div class="doctor-info">

                        🕐

                        <?= htmlspecialchars(
                            $doctor["available_time"]
                            ?? "-"
                        ) ?>

                    </div>


                    <?php if (isset($_SESSION["user_id"])): ?>

                        <a
                            href="user/book_appointment.php?doctor=<?= urlencode(
                                (string)$doctor["_id"]
                            ) ?>"
                            class="doctor-btn"
                        >
                            📅 Book Appointment
                        </a>

                    <?php else: ?>

                        <a
                            href="user/login.php"
                            class="doctor-btn"
                        >
                            🔐 Login to Book
                        </a>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>


    <?php else: ?>

        <div
            style="
                background:white;
                padding:40px;
                text-align:center;
                border-radius:10px;
            "
        >

            <h3>
                No doctors available
            </h3>

            <p>
                Please check again later.
            </p>

        </div>

    <?php endif; ?>


    <div class="view-all">

        <a
            href="user/doctors.php"
            class="primary-btn"
        >
            View All Doctors
        </a>

    </div>

</section>



<!-- =========================
     ABOUT
========================= -->

<section
    class="section"
    id="about"
>

    <div class="about">

        <div class="about-box">

            <div class="about-icon">
                🩺
            </div>

        </div>


        <div class="about-box">

            <h2>
                About MediSync
            </h2>


            <p>

                MediSync is a healthcare appointment
                management platform designed to make
                the connection between patients and
                healthcare professionals easier.

            </p>


            <p>

                Patients can explore doctors and
                manage appointments, while
                administrators can manage doctors,
                users and appointments through a
                dedicated dashboard.

            </p>


            <ul class="about-list">

                <li>
                    ✅ Easy doctor discovery
                </li>

                <li>
                    ✅ Simple appointment management
                </li>

                <li>
                    ✅ Secure user accounts
                </li>

                <li>
                    ✅ Admin management system
                </li>

            </ul>

        </div>

    </div>

</section>



<!-- =========================
     CONTACT
========================= -->

<section
    class="section contact"
    id="contact"
>

    <div class="section-title">

        <h2>
            Contact Us
        </h2>

        <p>
            Have a question? Get in touch
            with the MediSync team.
        </p>

    </div>


    <div class="contact-grid">


        <div class="contact-card">

            <div style="font-size:40px;">
                📞
            </div>

            <h3>
                Phone
            </h3>

            <p>
                +91 98765 43210
            </p>

        </div>


        <div class="contact-card">

            <div style="font-size:40px;">
                📧
            </div>

            <h3>
                Email
            </h3>

            <p>
                support@medisync.com
            </p>

        </div>


        <div class="contact-card">

            <div style="font-size:40px;">
                📍
            </div>

            <h3>
                Location
            </h3>

            <p>
                Gujarat, India
            </p>

        </div>

    </div>

</section>



<!-- =========================
     CTA
========================= -->

<section class="cta">

    <h2>
        Take Control of Your Healthcare
    </h2>

    <p>
        Find a doctor and manage your
        appointments with MediSync.
    </p>


    <?php if (isset($_SESSION["user_id"])): ?>

        <a href="user/doctors.php">
            Find a Doctor
        </a>

    <?php else: ?>

        <a href="user/signup.php">
            Get Started
        </a>

    <?php endif; ?>

</section>



<!-- =========================
     FOOTER
========================= -->

<footer>

    <h3>
        🩺 MediSync
    </h3>

    <p>
        Healthcare management made simple.
    </p>

    <p>
        © <?= date("Y") ?> MediSync.
        All rights reserved.
    </p>

</footer>



<!-- =========================
     MOBILE MENU JAVASCRIPT
========================= -->

<script>

function toggleMenu() {

    const navLinks = document.getElementById("navLinks");
    const menuBtn = document.getElementById("menuBtn");

    navLinks.classList.toggle("active");

    if (navLinks.classList.contains("active")) {

        menuBtn.innerHTML = "✕";

    } else {

        menuBtn.innerHTML = "☰";

    }

}


/* Close mobile menu after clicking a link */

document.querySelectorAll(".nav-links a").forEach(function(link) {

    link.addEventListener("click", function() {

        const navLinks = document.getElementById("navLinks");
        const menuBtn = document.getElementById("menuBtn");

        if (window.innerWidth <= 700) {

            navLinks.classList.remove("active");

            menuBtn.innerHTML = "☰";

        }

    });

});


/* Reset menu when screen becomes desktop */

window.addEventListener("resize", function() {

    if (window.innerWidth > 700) {

        const navLinks = document.getElementById("navLinks");
        const menuBtn = document.getElementById("menuBtn");

        navLinks.classList.remove("active");

        menuBtn.innerHTML = "☰";

    }

});

</script>


</body>

</html>