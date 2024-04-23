<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <title>Quran Verses</title>
  <style>
    body {
      padding-bottom: 60px;
      padding-top: 30px;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
    }

    .verse {
      text-align: right;
      margin-bottom: 20px;
      font-size: 32px;
    }

    .verse:first-child {
      text-align: center;
    }

    .divider {
      border-top: 1px solid #ccc;
      margin-top: 10px;
    }

    .play-button {
      display: inline-block;
      width: 38px;
      height: 38px;
      text-align: center;
      border-radius: 50%;
      /* background-color: #00acc1; */
      margin-right: 10px;
      cursor: pointer;
      color: #fff;
      padding: 2px 0 1px 5px;
    }

    #audio-player {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #00acc1;
      text-align: center;
      padding: 10px 0;
      z-index: 999;
    }

    #quran-audio {
      display: block;
      margin: 0 auto;
    }

    .translation {
      font-size: 16px;
    }

    .word {
      position: relative;
    }

    .word::before {
      content: attr(title);
      position: absolute;
      top: -25px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #00acc1;
      color: #fff;
      padding: 5px;
      border-radius: 3px;
      font-size: 12px;
      white-space: nowrap;
      opacity: 0;
      transition: opacity 0.3s ease-in;
    }

    .tafsir-text {
      color: #666;
      font-size: 18px;
      margin-right: 10px;
    }

    .fa-book-open {
      color: #00acc1;
      font-size: 20px;
    }

    .word:hover {
      color: #00acc1;
    }

    .word:hover::before {
      opacity: 1;
    }

    .open-modal-button {
      border: none;
      background: none;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php
    // Check if the chapter ID is provided in the URL
    if (isset($_GET['chapter_id'])) {
      $chapterId = $_GET['chapter_id'];

      // Call the API to fetch verses for the specified chapter
      $curl = curl_init();

      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => 'https://api.quran.com/api/v4/verses/by_chapter/' . $chapterId . '?words=1&language=ar&word_fields=text_imlaei&per_page=300',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept: application/json'
          ),
        )
      );
      $response = curl_exec($curl);
      curl_close($curl);

      // Decode the JSON response
      $versesData = json_decode($response, true);

      // Check if verses are found for the specified chapter
      if (isset($versesData['verses'])) {
        $verses = $versesData['verses'];

        // Loop through the verses and display them, skipping the first one
        for ($i = 0; $i < count($verses); $i++) {
          $verseNumber = $verses[$i]['verse_number'];
          $verseText = '';
          $verseTrans = '';

          // Loop through the words and concatenate them
          $words = $verses[$i]['words'];
          foreach ($words as $word) {
            // Concatenate the word text and its translation
            $verseText .= '<span class="word" title="' . $word['translation']['text'] . '">' . $word['text_imlaei'] . '</span> ';
            $verseTrans .= $word['translation']['text'] . ' ';
          }

          // Extract the number from the end of the verse
          preg_match('/[٠-٩]+$/', $verseText, $matches);
          $verseNumberAtEnd = isset($matches[0]) ? $matches[0] : '';

          // Remove the number from the end of the verse text
          $verseText = preg_replace('/[٠-٩]+$/', '', $verseText);

          // Add the numeral extracted from the end of the verse in a circle at the end
          $verseText .= " <br><span class='translation'>" . $verseTrans . "</span>";
          if ($i == 0) {
            // Align verses to the right and add a divider
            echo "<p class='verse'><button class='open-modal-button' onclick='openModal(\"$verseNumber\")'><span class='fas fa-book-open'></span><span class='tafsir-text'> Tafsir </span></button><span class='play-button' onclick='playAudio(\"$chapterId\", \"$verseNumber\", this)'>▶</span><span class=''>$verseNumberAtEnd</span> $verseText</p>";
            echo "<button class='open-modal-button btn btn-info' onclick='selectQari()'>Select Qari</button>";
          } else {
            echo "<p class='verse'><button class='open-modal-button' onclick='openModal(\"$verseNumber\")'><span class='fas fa-book-open'></span><span class='tafsir-text'> Tafsir </span></button><span class=''>$verseNumberAtEnd</span> $verseText</p>";
          }
          echo "<div class='divider'></div>";
        }
      } else {
        echo "<p>No verses found for this chapter.</p>";
      }
    } else {
      echo "<p>No chapter ID provided.</p>";
    }
    ?>
  </div>

  <div id="audio-player">
    <audio id="quran-audio" controls></audio>
  </div>

  <!-- The Modal -->
  <!-- Bootstrap Modal -->
  <div class="modal" id="tafsirModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title">Select Tafsir</h5>
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body">
          <ul id="tafsirList" class="list-group">
            <!-- Tafsir list will be populated here -->
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for selecting Qari -->
  <div class="modal" id="qariModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title">Select Qari</h5>
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body" id="qariList">
          <!-- Qari list will be populated here -->
        </div>
      </div>
    </div>
  </div>

  <script>
    function playAudio(chapterId, verseNumber, button) {
      const audio = document.getElementById('quran-audio');
      const isPlaying = !audio.paused;

      // Remove the clicked play button
      button.innerHTML = '';
      button.style.width = 'auto';
      button.style.height = 'auto';
      button.style.backgroundColor = 'transparent';

      // Check if audio is currently playing
      if (isPlaying) {
        audio.pause(); // Pause the audio
      } else {
        // Call the API to fetch audio file URL
        fetch(`https://api.quran.com/api/v4/chapter_recitations/${verseNumber}/${chapterId}`)
          .then(response => response.json())
          .then(data => {
            // Play the audio file
            audio.src = data.audio_file.audio_url;
            audio.play();
          })
          .catch(error => console.error('Error fetching audio:', error));
      }

      // Update play/pause button icon for the clicked button
      button.textContent = isPlaying ? '▶️' : '⏸️';
    }

    // Function to open the modal and fetch Tafsirs
    function openModal(verseNumber) {
      $('#tafsirModal').modal('show');
      fetchTafsirs(verseNumber); // Fetch Tafsirs for the specified verse
    }
    var verseno = 0;

    // Function to fetch Tafsirs for a specific verse
    function fetchTafsirs(verseNumber) {
      // Check if Tafsirs are already fetched
      verseno = verseNumber;
      if (!tafsirsFetched) {

        fetch('http://api.quran-tafseer.com/tafseer/')
          .then(response => response.json())
          .then(data => {
            const tafsirList = document.getElementById('tafsirList');
            data.forEach(tafsir => {
              const listItem = document.createElement('li');
              listItem.classList.add('list-group-item');

              // Create tafsir name element
              const tafsirName = document.createElement('button');
              tafsirName.classList.add('btn', 'btn-link', 'fw-bold', 'me-2');
              tafsirName.textContent = tafsir.name;

              tafsirName.onclick = () => {
                openTafsirPage(tafsir.id); // Pass verseNumber as argument
              };
              tafsirName.style.color = '#00acc1'; // Set font color to blue
              tafsirName.style.textDecoration = 'none'; // Remove text decoration
              listItem.appendChild(tafsirName);

              // Create author and language information
              const info = document.createElement('div');
              info.classList.add('text-muted'); // Add class for gray color
              const authorInfo = document.createElement('span');
              authorInfo.innerHTML = `<strong>Author:</strong> ${tafsir.author}`;
              authorInfo.style.fontSize = '12px'; // Lower font size for author
              info.appendChild(authorInfo);

              const languageInfo = document.createElement('span');
              const languageName = getLanguageName(tafsir.language); // Function to get language name from code
              languageInfo.innerHTML = ` <strong>Language:</strong> ${languageName}`;
              languageInfo.style.fontSize = '14px'; // Lower font size for language
              info.appendChild(languageInfo);

              listItem.appendChild(info);

              tafsirList.appendChild(listItem);
            });

            // Set tafsirsFetched to true to avoid fetching again
            tafsirsFetched = true;
          })
          .catch(error => console.error('Error fetching Tafsirs:', error));
      }
    }

    function selectQari() {
  fetch('https://api.quran.com/api/v4/resources/recitations')
    .then(response => response.json())
    .then(data => {
      const qariModalBody = document.getElementById('qariList');
      data.recitations.forEach(recitation => {
        const qariButton = document.createElement('button');
        qariButton.textContent = recitation.translated_name.name;
        qariButton.classList.add('btn', 'btn-primary', 'mb-2');
        qariButton.style.backgroundColor = '#00acc1'; // Set background color
        qariButton.style.color = '#fff'; // Set text color to white
        qariButton.style.display = 'block'; // Display each button on a separate line
        qariButton.onclick = () => {
          // Fetch audio URL from API
          fetch(`https://api.quran.com/api/v4/chapter_recitations/${recitation.id}/${getChapterIdFromURL()}`)
            .then(response => response.json())
            .then(data => {
              // Play the audio file
              const audio = document.getElementById('quran-audio');
              audio.src = data.audio_file.audio_url;
              audio.play();
              $('#qariModal').modal('hide'); // Hide the modal when audio starts playing
            })
            .catch(error => console.error('Error fetching audio:', error));
        };
        qariModalBody.appendChild(qariButton);
      });

      $('#qariModal').modal('show');
    })
    .catch(error => console.error('Error fetching recitations:', error));
}



    function openTafsirPage(tafsirId) {
      const chapterId = getChapterIdFromURL();

      const url = `tafsir.php?tafsir_id=${tafsirId}&chapter_id=${chapterId}&verse_number=${verseno}`;
      window.open(url, '_blank');
    }


    // Function to get chapterId from URL
    function getChapterIdFromURL() {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get('chapter_id');
    }

    // Function to get language name from language code
    function getLanguageName(code) {
      switch (code) {
        case 'ar':
          return 'Arabic';
        case 'nl':
          return 'Dutch';
        case 'en':
          return 'English';
        default:
          return code;
      }
    }

    let tafsirsFetched = false;

    // Call the fetchTafsirs function when the page loads
    window.onload = fetchTafsirs;

  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
    integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
    crossorigin="anonymous"></script>
</body>

</html>