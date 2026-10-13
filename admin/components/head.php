<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/settings.php';
$adminSiteName = getSetting('site_name', 'Orion Advent');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (isset($pageTitle) && !empty($pageTitle)) ? htmlspecialchars($pageTitle) . ' - ' . htmlspecialchars($adminSiteName) . ' Admin' : htmlspecialchars($adminSiteName) . ' Admin'; ?></title>
    <link rel="icon" href="../assets/images/logo/favicon.png" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["'Plus Jakarta Sans'", 'sans-serif'],
                        space: ["'Space Grotesk'", 'sans-serif'],
                    },
                    colors: {
                        sage: {
                            50: '#f4f7f4',
                            100: '#e6ede6',
                            200: '#cfe0cf',
                            300: '#a9c7aa',
                            400: '#7fa982',
                            500: '#5e8e62',
                            600: '#48734c',  /* Primary Accent Sage */
                            700: '#3a5c3d',
                            800: '#304a32',
                            900: '#283d2a',
                        },
                        cream: {
                            50: '#fbfbf8',
                            100: '#f6f5ef',
                            200: '#eeece0',
                            300: '#e1ddc9',
                            400: '#d0c8ae',
                            500: '#b8ad8f',
                        },
                        brand: {
                            50: '#f4f7f4',
                            100: '#e6ede6',
                            200: '#cfe0cf',
                            500: '#5e8e62',
                            600: '#48734c',
                            700: '#3a5c3d',
                            800: '#304a32',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Chart.js & SortableJS (for Drag and Drop customizable widgets) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <style>
        /* Clean minimal scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #d8ded8;
            border-radius: 2px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #b5c2b5;
        }
        
        /* Drag handle & sorting styling */
        .sortable-ghost {
            opacity: 0.35;
            background: #e6ede6 !important;
            border: 1px dashed #48734c !important;
        }
        .sortable-chosen {
            cursor: grabbing !important;
        }
        .drag-handle {
            cursor: grab;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
    </style>
</head>
<body class="bg-cream-100/60 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
