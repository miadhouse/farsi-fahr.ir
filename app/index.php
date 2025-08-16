<?php
require_once __DIR__ . '/../incloud/questions.php';

if (!isset($_POST['selected_questions'])) {
    die('پارامترهای لازم ارسال نشده‌اند');
}

// تبدیل به آرایه در صورت نیاز
$selectedQuestions = is_array($_POST['selected_questions'])
    ? $_POST['selected_questions']
    : explode(',', $_POST['selected_questions']);

if (empty($selectedQuestions)) {
    echo '<div class="alert alert-warning">هیچ سوالی یافت نشد</div>';
    exit;
}

// تعداد کل سوال‌ها
$totalQuestions = count($selectedQuestions);

// مدیریت صفحه‌بندی با session
$questionsPerPage = 10;
$currentQuestionIndex = 0;
$currentPage = 1;

// بررسی وجود question_id در session
if (isset($_SESSION['current_question_id']) && in_array($_SESSION['current_question_id'], $selectedQuestions)) {
    // پیدا کردن index سوال فعلی
    $currentQuestionIndex = array_search($_SESSION['current_question_id'], $selectedQuestions);
    $currentPage = floor($currentQuestionIndex / $questionsPerPage) + 1;
} else {
    // اگر session وجود ندارد، از سوال اول شروع کن
    $currentQuestionIndex = 0;
    $currentPage = 1;
    $_SESSION['current_question_id'] = $selectedQuestions[0];
}

// محاسبه محدوده صفحه فعلی
$startIndex = ($currentPage - 1) * $questionsPerPage;
$endIndex = min($startIndex + $questionsPerPage - 1, $totalQuestions - 1);

// محاسبه تعداد کل صفحات
$totalPages = ceil($totalQuestions / $questionsPerPage);

// سوال فعلی
$currentQuestion = $selectedQuestions[$currentQuestionIndex];

?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en" style="height: 100%;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>questions-page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <style>
        .custom-checkbox .checkmark:after {
            border: solid #fff;
            left: 7px;
            top: -2px;
            width: 9px;
            height: 18px;
            font-weight: bolder;
            border-width: 0 5px 5px 0;
            transform: rotate(45deg);
            box-shadow: 2px 2px 0 0 #000
        }

        .custom-checkbox input:checked~.checkmark:after {
            display: block
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none
        }

        .custom-checkbox input:checked~.checkmark {
            background-color: #4caf50
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            border: 2px solid #000;
            background-color: #fff;
            border-radius: 4px;
            width: 23px;
            height: 23px
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer
        }

        .custom-checkbox {
            position: relative;
            padding-left: 30px;
            cursor: pointer;
            display: inline-block
        }

        .form-label {
            margin-bottom: 1.2rem;
        }

        .video-placeholder {
            position: relative;
            cursor: pointer;
            border-radius: 10px;
            overflow: hidden;
        }

        .video-placeholder img {
            width: 100%;
            height: auto;
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            border: none;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            color: white;
            font-size: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .video-counter {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
        }

        .modal-dialog-xl {
            max-width: 90%;
        }

        .disabled-button {
            opacity: 0.6;
            cursor: not-allowed !important;
        }

        .video-question-mode .answers-section {
            display: none;
        }

        .video-question-mode .question-content {
            display: none;
        }

        .answers-mode .video-controls {
            display: none;
        }

        .keypad-btn {
            height: 50px;
            font-size: 18px;
            font-weight: bold;
        }

        .numeric-keypad {
            user-select: none;
        }
    </style>
</head>

<body style="height: 100%;background-color: #d3f5da;">
    <div class="container" style="height: 100%;">
        <div class="text-white bg-success d-flex justify-content-between align-items-center p-2 px-4"
            style="border-bottom-right-radius: 30px;border-bottom-left-radius: 30px;position: sticky;top: 0;">
            <div class="d-flex align-items-center"><i class="fas fa-info-circle me-2"></i><span id="code"></span></div>
            <a class="fas fa-adjust fa-lightbulb-o" style="font-size: 20px;">راهنما</a><span>Punkte: <span
                    id="punkt"></span></span>
        </div>
        <div class="mt-4 p-4" style="height: 100%;/*float: none;*/display: in;">
            <h1 id="text" class="fw-bold h5 mb-4 question-text"></h1>
            <div class="row">
                <div class="col-12 col-md-6 " id="media">
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center">
                            <span id="common_text"><!-- اگر موجود بود --> </span>
                        </div>

                        <!-- Video Controls Section -->
                        <div id="video-controls" class="video-controls" style="display: none;">
                            <div class="text-center">
                                <button id="video-start-btn" class="btn btn-primary btn-lg mb-3" onclick="playVideo()">
                                    Video starten
                                </button>
                                <div class="mb-3">
                                    <span class="video-counter">
                                        Sie können das Video insgesamt <span id="remaining-views">5</span> Mal ansehen.
                                    </span>
                                </div>
                                <button id="zur-aufgabe-btn" class="btn btn-success" style="display: none;"
                                    onclick="showAnswers()">
                                    Zur Aufgabenstellung
                                </button>
                            </div>
                        </div>

                        <!-- Answers Section -->
                        <div id="answers" class="answers-section">

                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="fixed-bottom container-fluid"
            style="margin-bottom: 50px;padding: 5px;background-color: #aad7aa;border-radius: 30px 30px 0 0;;width: 92%;">
            <div class="d-flex align-items-center"></div>
            <div class="row px-4 py-2">
                <div class="col-4 fw-bold text-start p-0">
                    <span class="badge bg-warning text-dark"> سوال ها: <?= $totalQuestions ?></span>

                    <span>صفحه <?= $currentPage ?> از <?= $totalPages ?></span>
                </div>
                <div class="col-8 text-end">
                    <div class="text-end">
                        <a class="btn btn-warning mx-1 btn-sm p-1 btn-danger" href="../admin/practice.php"> <i
                                class="fas fa-times"></i> Abbrechen</a>
                        <button class="btn btn-success mx-1 btn-sm p-1" onclick="nextQuestion()">Weiter <i
                                class="fas fa-arrow-right"></i></button>
              <button id="bookmark-btn" class="btn btn-warning mx-1 btn-sm p-1 bookmark-btn" onclick="toggleBookmark()">
                            <i id="bookmark-icon" class="far fa-star"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fixed-bottom container-fluid p-0">
            <div class="d-flex justify-content-between align-items-center p-2 px-4"
                style="background: var(--bs-success);/*border-top-left-radius: 30px;*//*border-top-right-radius: 30px;*/">
                <button class="btn btn-light text-success" onclick="previousQuestion()">
                    <i class="fas fa-step-backward"></i>
                </button>
                <div class="d-md-block d-flex gap-1">
                    <?php
                    for ($i = $startIndex; $i <= $endIndex; $i++) {
                        $questionNumber = $i + 1;
                        $isActive = ($i == $currentQuestionIndex) ? 'btn-dark' : 'btn-success';
                        ?>
                        <button id="btn<?= $questionNumber ?>" class="btn  <?= $isActive ?>"
                            onclick="goToQuestion(<?= $i ?>)">
                            <?= $questionNumber ?>
                        </button>
                        <?php
                    }
                    ?>
                </div>

                <button class="btn btn-light text-success" onclick="nextQuestion()">
                    <i class="fas fa-step-forward"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <video id="modal-video" width="100%" controls>
                        <source src="" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
   <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="bookmarkToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-star text-warning me-2"></i>
                <strong class="me-auto">نشانه‌گذاری</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <!-- Message will be inserted here -->
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const selectedQuestions = <?= json_encode($selectedQuestions) ?>;
        let currentQuestionIndex = <?= $currentQuestionIndex ?>;
        let questionsPerPage;
        let currentQuestionData = null;
        let isVideoQuestion = false;
        let videoViewCount = 0;
        let maxVideoViews = 5;
        let hasWatchedVideo = false;
        let showingAnswers = false;

        function updateQuestionsPerPage() {
            const width = window.innerWidth;
            if (width >= 992) {       // دسکتاپ
                questionsPerPage = 20;
            } else if (width >= 768) { // تبلت
                questionsPerPage = 10;
            } else {                  // موبایل
                questionsPerPage = 5;
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            loadCurrentQuestion();
            renderPageButtons();
        });

        window.addEventListener('resize', () => {
            renderPageButtons();
        });

        function loadCurrentQuestion() {
            // Reset video state for new question
            resetVideoState();

            const questionId = selectedQuestions[currentQuestionIndex];

            fetch("../incloud/get_question.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "question_id=" + questionId
            })
                .then(response => response.json())
                .then(data => {
                    currentQuestionData = data;
                    updateQuestionDisplay(data);
                    updateSession(questionId);
                })
                .catch(error => {
                    console.error("خطا:", error);
                });
            checkBookmarkStatus(questionId);
            }

        function resetVideoState() {
            videoViewCount = 0;
            hasWatchedVideo = false;
            showingAnswers = false;
            isVideoQuestion = false;
        }

        let imageUrl = '';
        let videoUrl = '';

        function updateQuestionDisplay(data) {
            const fileName = data['question']['picture'] || '';
            const extension = fileName.split('.').pop().toLowerCase();
            const fileNameWithoutExt = fileName.replace(/\.[^/.]+$/, "");

            // Check if it's a video question
            isVideoQuestion = ['mp4', 'm4v', 'webm'].includes(extension);

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                // Regular image question
                imageUrl = 'https://t24.theorie24.de/2025-01-v400/data/img/images/' + fileName;
                document.getElementById("media").innerHTML = '<img id="image" src="' + imageUrl + '" alt="" class="w-100">';
                showRegularQuestion(data);
            } else if (isVideoQuestion) {
                // Video question
                videoUrl = 'https://www.theorie24.de/live_images/_current_ws_2024-10-01_2025-04-01/videos/' + fileName;
                showVideoQuestion(data, fileNameWithoutExt);
            } else {
                document.getElementById("media").innerHTML = '';
                showRegularQuestion(data);
            }

            document.getElementById("code").innerText = data['question']['number'];
            document.getElementById("punkt").innerText = data['question']['points'];
        }

        function showVideoQuestion(data, fileNameWithoutExt) {
            // Set video question text
            document.getElementById("text").innerText = "Bitte starten Sie den Film, um sich mit der Situation vertraut zu machen.";

            // Show video controls, hide answers
            document.getElementById("video-controls").style.display = "block";
            document.getElementById("answers").style.display = "none";

            // Set initial video image
            updateVideoPlaceholder(fileNameWithoutExt);
            updateVideoControls();
        }

        function showRegularQuestion(data) {
            document.getElementById("text").innerText = data['question']['text'];
            document.getElementById("video-controls").style.display = "none";
            document.getElementById("answers").style.display = "block";
            answerBuilder(data['answers']);
        }

        function updateVideoPlaceholder(fileNameWithoutExt) {
            let imageName;
            if (hasWatchedVideo) {
                imageName = fileNameWithoutExt + '_ende.jpg';
            } else {
                imageName = fileNameWithoutExt + '_anfang.jpg';
            }

            const imageUrl = 'https://t24.theorie24.de/2025-01-v400/data/img/images/' + imageName;

            let playButtonHtml = '';
            if (videoViewCount < maxVideoViews && !showingAnswers) {
                playButtonHtml = '<button class="play-button" onclick="playVideo()"><i class="fas fa-play"></i></button>';
            }

            document.getElementById("media").innerHTML =
                '<div class="video-placeholder" onclick="' + (videoViewCount < maxVideoViews && !showingAnswers ? 'playVideo()' : '') + '">' +
                '<img src="' + imageUrl + '" alt="Video Preview" class="w-100">' +
                playButtonHtml +
                '</div>';
        }

        function updateVideoControls() {
            const remainingViews = maxVideoViews - videoViewCount;
            document.getElementById("remaining-views").innerText = remainingViews;

            const startBtn = document.getElementById("video-start-btn");
            const zurAufgabeBtn = document.getElementById("zur-aufgabe-btn");

            if (videoViewCount >= maxVideoViews) {
                startBtn.style.display = "none";
                zurAufgabeBtn.style.display = "block";
            } else if (hasWatchedVideo) {
                startBtn.style.display = "inline-block";
                zurAufgabeBtn.style.display = "inline-block";
            } else {
                startBtn.style.display = "inline-block";
                zurAufgabeBtn.style.display = "none";
            }

            if (showingAnswers) {
                startBtn.style.display = "none";
                zurAufgabeBtn.style.display = "none";
            }
        }

        function playVideo() {
            if (videoViewCount >= maxVideoViews || showingAnswers) {
                return;
            }

            videoViewCount++;
            hasWatchedVideo = true;

            // Update video placeholder
            if (currentQuestionData) {
                const fileName = currentQuestionData['question']['picture'] || '';
                const fileNameWithoutExt = fileName.replace(/\.[^/.]+$/, "");
                updateVideoPlaceholder(fileNameWithoutExt);
            }

            // Show video in modal
            const modal = new bootstrap.Modal(document.getElementById('videoModal'));
            const modalVideo = document.getElementById('modal-video');
            modalVideo.src = videoUrl;

            modal.show();

            // Update controls
            updateVideoControls();

            // Handle video end event
            modalVideo.onended = function () {
                modal.hide();
            };

            // Clean up when modal is hidden
            document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
                modalVideo.pause();
                modalVideo.src = '';
            });
        }

        function showAnswers() {
            if (!currentQuestionData) return;

            showingAnswers = true;

            // Hide video controls
            document.getElementById("video-controls").style.display = "none";

            // Show answers
            document.getElementById("answers").style.display = "block";

            // Update question text to actual question
            document.getElementById("text").innerText = currentQuestionData['question']['text'];

            // Build answers
            answerBuilder(currentQuestionData['answers']);

            // Update video placeholder to show initial image without play button
            if (currentQuestionData) {
                const fileName = currentQuestionData['question']['picture'] || '';
                const fileNameWithoutExt = fileName.replace(/\.[^/.]+$/, "");
                const imageUrl = 'https://t24.theorie24.de/2025-01-v400/data/img/images/' + fileNameWithoutExt + '_anfang.jpg';

                document.getElementById("media").innerHTML =
                    '<div class="video-placeholder">' +
                    '<img src="' + imageUrl + '" alt="Video Preview" class="w-100">' +
                    '</div>';
            }
        }

        function answerBuilder(answers = null) {
            if (answers && answers.length > 0) {
                let answersText = "";
                const answerType = answers[0]['asw_type'] || 1; // Default to type 1 if not specified

                console.log(answers);
                console.log('Answer Type:', answerType);

                if (answerType == 2) {
                    // Type 2: Input field with numeric keypad
                    const answer = answers[0]; // For type 2, typically there's one answer
                    const hint = answer['asw_hint'] || '';
                    const correctAnswer = answer['text'] || '';

                    answersText = `
                <div class="text-center">
                    <div class="mb-4">
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                            <input type="text" 
                                   id="numeric-answer" disabled value="${correctAnswer}"
                                   class="form-control text-center" 
                                   style="width: 150px; font-size: 18px; font-weight: bold;" 
                                   readonly>
                            <span class="fw-bold fs-5">${hint}</span>
                        </div>
                    </div>
                    
                    <!-- Numeric Keypad -->
                    <div class="numeric-keypad mx-auto" style="max-width: 300px;">
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('0')">0</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('1')">1</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('2')">2</button>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('3')">3</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('4')">4</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('5')">5</button>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('6')">6</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('7')">7</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('8')">8</button>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('9')">9</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addComma()">,</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="addNumber('C')">C</button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-12">
                                <button class="btn btn-outline-danger w-100" onclick="clearAnswer()">Löschen</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden correct answer for validation -->
                    <input type="hidden" id="correct-answer" value="${correctAnswer}">
                </div>
            `;
                } else {
                    // Type 1: Regular checkbox answers
                    answers.forEach(answer => {
                        let status = "";
                        if (answer['asw_corr'] == 1) {
                            status = 'checked';
                        }
                        answersText += `
                    <div class="d-flex mb-5 align-items-center">
                        <label class="form-label me-2 custom-checkbox">
                            <input type="checkbox" class="checkbox" ${status}>
                            <span class="checkmark"></span>
                        </label>
                        <span class="fw-bold" name="text" status="richtig">${answer['text']}</span>
                        <span class="d-none" name="help_text"></span>
                    </div>
                `;
                    });
                }

                document.getElementById("answers").innerHTML = answersText;
            } else {
                console.log('خطا در استخراج پاسخ');
                return "";
            }
        }

        // Helper functions for numeric keypad
        function addNumber(num) {
            const input = document.getElementById('numeric-answer');
            if (num === 'C') {
                // Clear last character
                input.value = input.value.slice(0, -1);
            } else {
                input.value += num;
            }
        }

        function addComma() {
            const input = document.getElementById('numeric-answer');
            // Only add comma if there isn't one already and input is not empty
            if (input.value && !input.value.includes(',')) {
                input.value += ',';
            }
        }

        function clearAnswer() {
            const input = document.getElementById('numeric-answer');
            input.value = '';
        }

        // Function to check if the numeric answer is correct
        function checkNumericAnswer() {
            const input = document.getElementById('numeric-answer');
            const correctAnswer = document.getElementById('correct-answer');

            if (input && correctAnswer) {
                return input.value.trim() === correctAnswer.value.trim();
            }
            return false;
        }

        function updateSession(questionId) {
            fetch("../incloud/update_session.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "current_question_id=" + questionId
            });
        }

        function nextQuestion() {
            if (currentQuestionIndex < selectedQuestions.length - 1) {
                currentQuestionIndex++;
                loadCurrentQuestion();
                renderPageButtons();
            }
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                loadCurrentQuestion();
                renderPageButtons();
            }
        }

        function goToQuestion(index) {
            currentQuestionIndex = index;
            loadCurrentQuestion();
            renderPageButtons();
        }

        function renderPageButtons() {
            const container = document.querySelector('.d-md-block.d-flex.gap-1');
            container.innerHTML = '';

            function getQuestionsPerPage() {
                if (window.innerWidth < 768) {
                    return 5;
                } else if (window.innerWidth < 992) {
                    return 10;
                } else {
                    return 15;
                }
            }

            const questionsPerPage = getQuestionsPerPage();
            const currentPage = Math.floor(currentQuestionIndex / questionsPerPage) + 1;
            const totalPages = Math.ceil(selectedQuestions.length / questionsPerPage);

            const startIndex = (currentPage - 1) * questionsPerPage;
            const endIndex = Math.min(startIndex + questionsPerPage - 1, selectedQuestions.length - 1);

            for (let i = startIndex; i <= endIndex; i++) {
                const questionNumber = i + 1;
                const isActive = (i === currentQuestionIndex) ? 'btn-dark' : 'btn-success';

                const btn = document.createElement('button');
                btn.id = `btn${questionNumber}`;
                btn.className = `btn ${isActive}`;
                btn.textContent = questionNumber;
                btn.onclick = () => goToQuestion(i);

                container.appendChild(btn);
            }
        }

        function updatePageButtons() {
            const buttons = document.querySelectorAll('[id^="btn"]');
            buttons.forEach(btn => {
                btn.className = 'btn p-2  btn-success';
            });

            const currentBtn = document.getElementById('btn' + (currentQuestionIndex + 1));
            if (currentBtn) {
                currentBtn.className = 'btn p-2  btn-dark';
            }
        }
        // Bookmark Functions
        function checkBookmarkStatus(questionId) {
            fetch('../incloud/bookmark_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'question_id=' + questionId + '&action=check'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isCurrentQuestionBookmarked = data.is_bookmarked;
                    updateBookmarkUI();
                }
            })
            .catch(error => {
                console.error('خطا در بررسی وضعیت نشانه‌گذاری:', error);
            });
        }

        function toggleBookmark() {
            const questionId = selectedQuestions[currentQuestionIndex];
            const action = isCurrentQuestionBookmarked ? 'remove' : 'add';
            
            // Disable button during request
            const bookmarkBtn = document.getElementById('bookmark-btn');
            bookmarkBtn.disabled = true;
            
            fetch('../incloud/bookmark_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'question_id=' + questionId + '&action=' + action
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'add') {
                        isCurrentQuestionBookmarked = true;
                        showToast('سوال نشانه‌گذاری شد', 'success');
                    } else {
                        isCurrentQuestionBookmarked = false;
                        showToast('نشانه‌گذاری حذف شد', 'info');
                    }
                    updateBookmarkUI();
                } else {
                    showToast('خطا: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('خطا در تغییر وضعیت نشانه‌گذاری:', error);
                showToast('خطا در ارتباط با سرور', 'error');
            })
            .finally(() => {
                // Re-enable button
                bookmarkBtn.disabled = false;
            });
        }

        function updateBookmarkUI() {
            const bookmarkIcon = document.getElementById('bookmark-icon');
            const bookmarkBtn = document.getElementById('bookmark-btn');
            
            if (isCurrentQuestionBookmarked) {
                bookmarkIcon.className = 'fas fa-star bookmarked';
                bookmarkBtn.title = 'حذف نشانه‌گذاری';
            } else {
                bookmarkIcon.className = 'far fa-star not-bookmarked';
                bookmarkBtn.title = 'نشانه‌گذاری سوال';
            }
        }

        function showToast(message, type = 'info') {
            const toast = document.getElementById('bookmarkToast');
            const toastBody = toast.querySelector('.toast-body');
            const toastHeader = toast.querySelector('.toast-header');
            
            // Set message
            toastBody.textContent = message;
            
            // Set colors based on type
            toastHeader.className = 'toast-header';
            if (type === 'success') {
                toastHeader.classList.add('bg-success', 'text-white');
            } else if (type === 'error') {
                toastHeader.classList.add('bg-danger', 'text-white');
            } else {
                toastHeader.classList.add('bg-info', 'text-white');
            }
            
            // Show toast
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
</script>
</body>

</html>