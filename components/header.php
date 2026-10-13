<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : htmlspecialchars(getSetting('site_name', 'Orion Advent')) . ' - ' . htmlspecialchars(getSetting('site_tagline', 'Your Passport to Adventure')); ?></title>

    <!-- Google Font (Space Grotesk) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 (Vector Icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Website Logo -->
<link rel="icon" type="image/jpeg" href="assets/images/logo/fabicon.jpg">

<!-- Optional: Browser tab icon -->
<link rel="shortcut icon" href="assets/images/logo/fabicon.jpg" type="image/jpeg">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        space: ["'Space Grotesk'", 'sans-serif'],
                        sans: ["'Space Grotesk'", 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#0d9488',
                            600: '#068285',  // Exact Orion Advent Teal
                            700: '#07696c',
                            800: '#095557',
                            900: '#0a4648',
                            dark: '#0a272e', // Deep petrol navy for top utility bar
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-space bg-gray-50 text-gray-800 min-h-screen flex flex-col overflow-x-hidden w-full relative">
