<?php
session_start();
require_once 'db.php'; // Connexion à la base de données

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id_utilisateur, mot_de_passe, id_role, est_actif FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['est_actif']) {
                // Solution optimale avec vérification améliorée
                if (
                    password_verify($password, $user['mot_de_passe']) ||
                    (strlen($user['mot_de_passe']) === 60 && hash_equals($user['mot_de_passe'], crypt($password, $user['mot_de_passe'])))
                ) {

                    $_SESSION['user_id'] = $user['id_utilisateur'];
                    $_SESSION['role_id'] = $user['id_role'];
                    $_SESSION['loggedin'] = true;

                    // Mettre à jour la date du dernier accès
                    $pdo->prepare("UPDATE utilisateur SET dernier_acces = NOW() WHERE id_utilisateur = ?")
                        ->execute([$user['id_utilisateur']]);

                    // Redirection selon le rôle
                    if ($user['id_role'] == 1) {
                        header("Location: admin/dashboard.html");
                    } else if ($user['id_role'] == 3) {
                        header("Location: student/dashboard.html");
                    } else {
                        // Default fallback
                        header("Location: dashboard.php");
                    }
                    exit;
                } else {
                    $error = "Mot de passe incorrect.";
                }
            } else {
                $error = "Votre compte est désactivé.";
            }
        } else {
            $error = "Aucun utilisateur trouvé avec cet email.";
        }
    } catch (PDOException $e) {
        $error = "Erreur de base de données : " . $e->getMessage();
    }
}

// Fonction pour migrer les anciens hashs (à exécuter une fois)
function migrateOldHashes($pdo)
{
    $users = $pdo->query("SELECT id_utilisateur, mot_de_passe FROM utilisateur")->fetchAll();

    foreach ($users as $user) {
        // Si le hash n'est pas au format BCRYPT (60 caractères)
        if (strlen($user['mot_de_passe']) !== 60) {
            $newHash = password_hash($user['mot_de_passe'], PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?")
                ->execute([$newHash, $user['id_utilisateur']]);
        }
    }
}

// Décommenter pour migrer une seule fois (puis recommenter)
// migrateOldHashes($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - E6SCHOOL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Votre CSS complet ici */
        body {
            padding-top: 70px;
            font-family: 'Montserrat', sans-serif;
            background: #f0f2f5;
        }

        .header-bar {
            background-color: #4c4040;
            width: 100%;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
        }

        .site-title {
            font-size: 32px;
            font-weight: 900;
            font-style: italic;
        }

        :root {
            --primary: #1c1112a9;
            --primary-dark: #48282d7f;
            --secondary: #5007075d;
            --secondary-dark: #e22727;
            --light: #f8f9fa;
            --text-light: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .navbar.scrolled {
            padding: 1rem 5%;
            backdrop-filter: blur(10px);
            background: rgba(109, 10, 18, 0.619);
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            letter-spacing: 0.5px;
        }

        .logo i {
            margin-right: 0.75rem;
            color: var(--secondary);
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }

        .nav-links a:hover {
            color: var(--secondary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary);
            transition: all 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-cta {
            background-color: var(--secondary);
            color: #4d061996;
            padding: 0.75rem 1.75rem;
            border-radius: 50px;
            font-weight: 600;
            margin-left: 1.5rem;
            transition: all 0.3s ease;
        }

        .nav-cta:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }

        .mobile-menu-toggle {
            display: none;
            font-size: 1.5rem;
            color: white;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 5rem;
                left: 0;
                width: 100%;
                background-color: var(--primary-dark);
                flex-direction: column;
                gap: 1.5rem;
                padding: 2rem;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                transform: translateY(-150%);
                transition: transform 0.4s ease;
                z-index: 999;
            }

            .nav-links.active {
                transform: translateY(0);
            }

            .mobile-menu-toggle {
                display: block;
            }

            .navbar.scrolled {
                padding: 1rem 5%;
            }

            .nav-cta {
                margin-left: 0;
                margin-top: 1rem;
            }
        }

        .background-img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 0px;
        }

        .login-box {
            position: absolute;
            top: 150px;
            left: 500px;
            background-color: rgba(207, 191, 191, 0.8);
            padding: 40px;
            width: 500px;
            border-radius: 12px;
        }

        .login-title {
            font-size: 48px;
            font-weight: 900;
            font-style: italic;
            margin-bottom: 20px;
        }

        .label {
            display: block;
            margin-top: 15px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #333;
        }

        .input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .label-role {
            display: block;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
            color: #333;
        }

        .select-role {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
        }

        .input-field {
            background-color: #d9d9d9;
            height: 40px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .forgot {
            font-size: 14px;
            color: #444;
            text-align: right;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            background-color: #1e0000cc;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 30px;
        }

        .register-box {
            background-color: #494343b0;
            padding: 30px;
            border-radius: 8px;
            color: white;
        }

        .register-title {
            font-size: 24px;
            font-weight: 900;
            font-style: italic;
            margin-bottom: 10px;
        }

        .register-description {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .highlight {
            font-weight: 700;
            font-style: italic;
        }

        .register-button {
            background-color: #1e0000;
            color: white;
            padding: 12px 20px;
            font-size: 18px;
            font-weight: 900;
            font-style: italic;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .footer-section {
            background: linear-gradient(135deg, #7d5b5ba5, #e0dadd);
            color: rgb(203, 190, 190);
            position: relative;
            margin-top: 80px;
        }

        .footer-wave {
            position: absolute;
            top: -100px;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            transform: rotate(180deg);
        }

        .footer-wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 100px;
        }

        .footer-wave .shape-fill {
            fill: #2c0b0b;
        }

        .footer-content {
            position: relative;
            z-index: 1;
        }

        .footer-widget {
            margin-bottom: 30px;
        }

        .widget-title {
            color: rgb(65, 15, 15);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .widget-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background: #e0dadd;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            letter-spacing: 0.5px;
        }

        .logo i {
            margin-right: 0.75rem;
            color: #5007075d;
            font-size: 1.8rem;
        }

        .footer-text {
            color: rgba(93, 37, 37, 0.8);
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(83, 19, 19, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
        }

        .footer-links a:hover {
            color: rgb(76, 19, 19);
            transform: translateX(5px);
        }

        .footer-links a::before {
            content: '\f054';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 10px;
            position: absolute;
            left: -15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .footer-links a:hover::before {
            opacity: 1;
            left: -10px;
        }

        .footer-contact {
            list-style: none;
            padding: 0;
        }

        .contact-item {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .contact-item i {
            margin-right: 10px;
            color: #e0dadd;
            font-size: 16px;
            margin-top: 3px;
        }

        .contact-item span {
            color: rgba(106, 57, 78, 0.8);
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(106, 16, 16, 0.826);
            color: rgb(209, 191, 191);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background: #e0dadd;
            transform: translateY(-3px);
        }

        .newsletter h4 {
            font-size: 18px;
            margin-bottom: 15px;
            color: white;
        }

        .newsletter-form {
            display: flex;
            position: relative;
        }

        .newsletter-form input {
            width: 100%;
            padding: 10px 15px;
            border: none;
            border-radius: 30px;
            background: rgba(119, 47, 47, 0.688);
            color: rgb(73, 10, 10);
            outline: none;
        }

        .newsletter-form input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .newsletter-form button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e0dadd;
            color: rgb(73, 25, 25);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .newsletter-form button:hover {
            background: #17a673;
        }

        .footer-bottom {
            border-top: 1px solid rgba(146, 53, 53, 0.664);
        }

        .copyright-text {
            color: rgba(91, 7, 7, 0.7);
            font-size: 14px;
        }

        .footer-menu {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .footer-menu a {
            color: rgba(105, 27, 27, 0.7);
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-menu a:hover {
            color: #e0dadd;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #7d5b5ba5;
            color: rgb(53, 21, 21);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(125, 70, 70, 0.663);
        }

        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #e0dadd;
            transform: translateY(-3px);
        }

        @media (max-width: 991px) {
            .footer-wave {
                top: -70px;
            }

            .footer-wave svg {
                height: 70px;
            }
        }

        @media (max-width: 767px) {
            .footer-wave {
                top: -50px;
            }

            .footer-wave svg {
                height: 50px;
            }

            .footer-menu {
                justify-content: center;
                margin-top: 10px;
            }

            .copyright-text,
            .footer-menu a {
                font-size: 13px;
            }

            .back-to-top {
                width: 40px;
                height: 40px;
                font-size: 16px;
                bottom: 20px;
                right: 20px;
            }
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a href="#" class="logo">
            <i class="fas fa-graduation-cap"></i>
            E6SCHOOL
        </a>
        <div class="nav-links">
            <a href="page d'acceuil.html">Home</a>
            <a href="#programs">Programmes</a>
            <a href="#about">About</a>
            <a href="#Guide">Guide</a>
            <a href="contact.html">Contact</a>
            <a href="login.php" class="login-button"><i class="fas fa-sign-in-alt"></i> Login</a>
        </div>
        <div class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </nav>

    <div class="login-container">
        <img class="background-img" src="imgprjt5.jpg" alt="Background" />
        <div class="login-box">
            <form method="POST" action="">
                <h1 class="login-title">LOGIN</h1>
                <label for="role" class="label-role">Qui êtes-vous ?</label>
                <select id="role" name="role" class="select-role" style="display:none;">
                    <option value="">-- Sélectionnez votre rôle --</option>
                    <option value="admin">Administrateur</option>
                    <option value="student">Étudiant</option>
                </select>

                <label for="email" class="label">Adresse e-mail</label>
                <input type="email" id="email" name="email" class="input" placeholder="Entrez votre e-mail" required />

                <label for="password" class="label">Mot de passe</label>
                <input type="password" id="password" name="password" class="input" placeholder="Entrez votre mot de passe" required />

                <p class="forgot">Mot de passe oublié ?</p>

                <?php if (!empty($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <button type="submit" class="login-button">Se connecter</button>

                <div class="register-box">
                    <h2 class="register-title">Pas encore membre ?</h2>
                    <p class="register-description">
                        Inscrivez-vous maintenant et commencez votre parcours avec <span class="highlight">E6SCHOOL !</span>
                    </p>
                    <a href="./student/register.html" class="register-button">
                        <i class="fas fa-user-plus"></i> S'INSCRIRE MAINTENANT
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer-section">
        <div class="footer-wave">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
        <div class="container">
            <div class="footer-content pt-5 pb-4">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                        <div class="footer-widget">
                            <div class="logo-wrapper mb-3">
                                <span class="logo">
                                    <i class="fas fa-graduation-cap"></i>
                                    E6SCHOOL
                                </span>
                            </div>
                            <p class="footer-text">E6SCHOOL est une plateforme éducative innovante offrant des cours de qualité pour les élèves de tous niveaux.</p>
                            <div class="social-links mt-4">
                                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                        <div class="footer-widget">
                            <h3 class="widget-title">Liens rapides</h3>
                            <ul class="footer-links">
                                <li><a href="#">Home</a></li>
                                <li><a href="#">Courses</a></li>
                                <li><a href="#">About us</a></li>
                                <li><a href="#">News</a></li>
                                <li><a href="#">Events</a></li>
                                <li><a href="#">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                        <div class="footer-widget">
                            <h3 class="widget-title">Nos cours</h3>
                            <ul class="footer-links">
                                <li><a href="#">Management</a></li>
                                <li><a href="#">Gestion</a></li>
                                <li><a href="#">Comptabilité</a></li>
                                <li><a href="#">Analyse</a></li>
                                <li><a href="#">English</a></li>
                                <li><a href="#">Math</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget">
                            <h3 class="widget-title">Contactez-nous</h3>
                            <ul class="footer-contact">
                                <li class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>123 Rue Mohamed, Meknes 75000</span>
                                </li>
                                <li class="contact-item">
                                    <i class="fas fa-phone-alt"></i>
                                    <span>+212 1 23 45 67 89</span>
                                </li>
                                <li class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>contact@e6school.com</span>
                                </li>
                            </ul>
                            <div class="newsletter mt-3">
                                <h4>Newsletter</h4>
                                <form class="newsletter-form">
                                    <input type="email" placeholder="your email" required>
                                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom py-3">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="copyright-text mb-0">&copy; <span id="year"></span> E6SCHOOL. Tous droits réservés.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="footer-menu">
                            <a href="#">Politique de confidentialité</a>
                            <a href="#">Conditions d'utilisation</a>
                            <a href="#">FAQ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="back-to-top">
            <i class="fas fa-arrow-up"></i>
        </div>
    </footer>

    <!-- JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Votre JavaScript complet ici
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            this.innerHTML = navLinks.classList.contains('active') ?
                '<i class="fas fa-times"></i>' :
                '<i class="fas fa-bars"></i>';
        });

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    navLinks.classList.remove('active');
                    mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('year').textContent = new Date().getFullYear();

            const backToTopButton = document.querySelector('.back-to-top');
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('active');
                } else {
                    backToTopButton.classList.remove('active');
                }
            });

            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            const footerLinks = document.querySelectorAll('.footer-links a');
            footerLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            const newsletterForm = document.querySelector('.newsletter-form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const emailInput = this.querySelector('input[type="email"]');
                    if (emailInput.value.trim() !== '') {
                        alert('Merci pour votre inscription à notre newsletter!');
                        emailInput.value = '';
                    } else {
                        alert('Veuillez entrer une adresse email valide.');
                    }
                });
            }

            const socialIcons = document.querySelectorAll('.social-icon');
            socialIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            const footerWidgets = document.querySelectorAll('.footer-widget');
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            footerWidgets.forEach((widget, index) => {
                widget.style.opacity = '0';
                widget.style.transform = 'translateY(20px)';
                widget.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
                observer.observe(widget);
            });
        });
    </script>
</body>

</html>