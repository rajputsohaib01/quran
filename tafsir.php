<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tafsir</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Set desktop width of content to 50% */
        @media (min-width: 768px) {
            .content {
                width: 50%;
                margin: 0 auto;
            }
        }

        body {
            padding-top: 30px;
        }

        #title {
            font-size: 28px;
        }

        #tafsir {
            font-size: 20px;
            transition: color 0.3s; /* Add transition for smooth color change */
        }
        #tafsir:hover {
            color: #00acc1; /* Change font color on hover */
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 content"> <!-- Adjust col-lg-6 to match your desired width -->
                <div id="tafsir-info">
                    <!-- Tafsir information will be displayed here -->
                </div>

                <?php
                // Function to fetch tafsir data from the API
                function fetchTafsirData($tafsirId, $suraNumber, $ayahNumber)
                {
                    $apiUrl = "http://api.quran-tafseer.com/tafseer/{$tafsirId}/{$suraNumber}/{$ayahNumber}";
                    $response = @file_get_contents($apiUrl); // Suppress warnings
                    if ($response === false) {
                        // Check if the error was HTTP 404 Not Found
                        if (strpos($http_response_header[0], '404') !== false) {
                            return false; // Return false to indicate HTTP 404 error
                        } else {
                            // Handle other types of errors (e.g., network issues)
                            return false;
                        }
                    }
                    return json_decode($response, true);
                }

                function fetchAyatData($suraNumber, $ayahNumber)
                {
                    $apiUrl = "http://api.quran-tafseer.com/quran/{$suraNumber}/{$ayahNumber}";
                    $response = @file_get_contents($apiUrl); // Suppress warnings
                    if ($response === false) {
                        // Check if the error was HTTP 404 Not Found
                        if (strpos($http_response_header[0], '404') !== false) {
                            return false; // Return false to indicate HTTP 404 error
                        } else {
                            // Handle other types of errors (e.g., network issues)
                            return false;
                        }
                    }
                    return json_decode($response, true);
                }

                // Extract parameters from URL
                $tafsirId = $_GET['tafsir_id'];
                $suraNumber = $_GET['chapter_id'];
                $ayahNumber = $_GET['verse_number'];

                // Fetch tafsir data
                $tafsirData = fetchTafsirData($tafsirId, $suraNumber, $ayahNumber);
                $ayatData = fetchAyatData($suraNumber, $ayahNumber);

                // Check if data retrieval was successful
                if ($tafsirData !== false && $ayatData !== false) {
                    // Display tafsir information
                    echo "<div id='tafsir-info'>";
                    echo "<p id='title'><strong>{$tafsirData['tafseer_name']}</strong></p>";
                    echo "<hr>";
                    echo "<p id='title' style='text-align: right;'><strong>{$ayatData['text']}</strong></p>";
                    echo "<hr>";
                    echo "<p id='tafsir'>{$tafsirData['text']}</p>";
                    echo "</div>";
                } else {
                    // Handle the case where data retrieval failed due to HTTP 404
                    echo "The requested tafsir or verse was not found.";
                }
                ?>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>