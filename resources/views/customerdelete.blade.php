<!DOCTYPE html>
<html>

<head>
    <title>Delete Process</title>
    <style>
        /* Basic reset and font styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }

        /* Content container for better centering */
        .container {
            width: 90%;
            max-width: 500px;
        }

        /* Logo and heading styling */
        .logo img {
            width: 150px; /* Default logo size for desktop */
            height: auto;
            margin-top: 20px;
        }

        h2 {
            font-size: 2em; /* Default heading font size */
            margin-top: 10px;
        }

        p {
            font-size: 1.2em; /* Default paragraph font size */
            margin: 15px 0;
        }

        ol {
            font-size: 1.1em; /* Default list font size */
            padding-left: 0;
            text-align: left;
            list-style-position: inside;
        }

        ol li {
            margin: 10px 0;
        }

        /* Mobile-specific styling */
        @media (max-width: 768px) {
            .logo img {
                width: 70%; /* Larger logo for mobile */
            }

            h2 {
                font-size: 1.8em; /* Larger heading font for mobile */
            }

            p {
                font-size: 1.5em; /* Larger paragraph font for mobile */
            }

            ol {
                font-size: 1.4em; /* Larger list font for mobile */
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Logo Section -->
        <div class="logo">
            <img src="{{ asset('assets/img/logo_1024x1024.png') }}" alt="Podpals Logo">
        </div>

        <!-- Heading Section -->
        <h1 style="font-size:50px">Delete Process</h1>

        <!-- Steps Section -->
        <p style="font-size:35px">To delete your profile, please follow these steps:</p>
        <ol style="font-size:30px">
            <li>Go to the <strong>Home</strong> page.</li>
            <li>Select the <strong>Profile</strong> option.</li>
            <li>Scroll to the <strong>Delete</strong> option.</li>
        </ol>
    </div>

    <!-- Deep Link Script -->
    <script>
        // Generate the deep link
        var deepLink = 'com.podpals://podpals.neosao.online/deleteprofile';

        // Open the deep link
        window.location.href = deepLink; 
    </script>
</body>

</html>
