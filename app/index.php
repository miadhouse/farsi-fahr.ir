<?php
require_once __DIR__ . '/../incloud/questions.php';
// در ابتدای فایل PHP بعد از require_once
// فقط token موجود در session را دریافت می‌کنیم (که موقع لاگین ساخته شده)
$csrf_token = $_SESSION['csrf_token'] ?? '';

// اگر token وجود نداشت (کاربر لاگین نیست)، خطا نمایش دهید
if (empty($csrf_token)) {
    die('لطفاً مجدداً وارد شوید');
}
if (!isset($_POST['selected_questions'])) {
    die('پارامترهای لازم ارسال نشده‌اند');
}

// Get mode parameter (default to browse if not specified)
$mode = $_POST['mode'] ?? 'browse';

// تبدیل به آرایه در صورت نیاز
$selectedQuestions = is_array($_POST['selected_questions'])
    ? $_POST['selected_questions']
    : explode(',', $_POST['selected_questions']);

if (empty($selectedQuestions)) {
    echo '<div class="alert alert-warning">هیچ سوالی یافت نشد</div>';
    exit;
}

// Initialize or get user answers from session
if (!isset($_SESSION['user_answers'])) {
    $_SESSION['user_answers'] = [];
}

if (!isset($_SESSION['solved_questions'])) {
    $_SESSION['solved_questions'] = [];
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

        /* استایل برای دکمه بوک مارک */
        .bookmark-btn {
            transition: all 0.3s ease;
        }

        .bookmark-btn:hover {
            transform: scale(1.1);
        }

        .bookmark-loading {
            opacity: 0.6;
            cursor: wait !important;
        }

        /* استایل برای پیام‌های toast */
        .bookmark-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Practice Mode Styles */
        .answer-item {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .answer-correct-selected {
            background-color: #d4edda !important;
            border: 2px solid #28a745 !important;
        }

        .answer-incorrect-selected {
            background-color: #f8d7da !important;
            border: 2px solid #dc3545 !important;
        }

        .answer-correct-unselected {
            background-color: #f8d7da !important;
            border: 2px solid #dc3545 !important;
        }

        .answer-incorrect-unselected {
            background-color: #d4edda !important;
            border: 2px solid #28a745 !important;
        }

        .question-btn-correct {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }

        .question-btn-incorrect {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .practice-mode .custom-checkbox input:disabled {
            pointer-events: none;
        }

        .practice-mode .answer-item.disabled {
            pointer-events: none;
            opacity: 0.8;
        }

        /* استایل‌های جدید برای دایره‌های رنگی وضعیت */
        .question-status-indicator {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: .5px solid white;
            z-index: 10;
        }

        .status-gray {
            background-color: #6c757d;
        }

        .status-blue {
            background-color: #0d6efd;
        }

        .status-green {
            background-color: #198754;
        }

        .status-yellow {
            background-color: #ffc107;
        }

        .status-red {
            background-color: #dc3545;
        }

        .question-btn-container {
            position: relative;
            display: inline-block;
        }

        /* استایل برای انتخاب متن */
        .vocabulary-selection {
            position: relative;
            user-select: text;
        }

        /* استایل برای متن انتخاب شده */
        .selected-word {
            background-color: #fff3cd;
            border-radius: 3px;
            padding: 1px 2px;
        }

        /* کنتینر آیکون‌ها */
        .vocab-icons {
            position: absolute;
            background: #fff;
            border: 2px solid #007bff;
            border-radius: 25px;
            padding: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            display: none;
            animation: fadeIn 0.2s ease-in;
            pointer-events: auto;
            /* اضافه کردن این خط */
        }

        .vocab-icons::before {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #007bff;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .vocab-icon {
            display: inline-block;
            width: 35px;
            height: 35px;
            margin: 0 2px;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .vocab-icon:hover {
            transform: scale(1.1);
        }

        .vocab-icon.translate {
            background: linear-gradient(45deg, #28a745, #20c997);
        }

        .vocab-icon.save {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
        }

        .vocab-icon:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Loading animation */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Translation popup */
        .translation-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 2px solid #007bff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            z-index: 1100;
            min-width: 300px;
            max-width: 90%;
        }

        .translation-popup .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            color: #666;
            cursor: pointer;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1099;
        }

        /* Toast notifications */
        .vocab-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1200;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body style="height: 100%;background-color: #d3f5da;" class="<?= $mode === 'practice' ? 'practice-mode' : '' ?>">
    <div class="container" style="height: 100%;">
        <div class="text-white bg-success d-flex justify-content-between align-items-center p-2 px-4"
            style="border-bottom-right-radius: 30px;border-bottom-left-radius: 30px;position: sticky;top: 0;">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <span id="code"></span>
                <!-- <?php if ($mode === 'practice'): ?>
                    <span class="ms-3 badge bg-warning text-dark">Practice Mode</span>
                <?php endif; ?> -->
            </div>
            <span>Punkte: <span id="punkt"></span></span>
        </div>
        <div class="mt-4 p-4" style="height: 100%;/*float: none;*/display: in;">
            <h1 id="text" class="fw-bold h6 mb-4 question-text"></h1>
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
                    <span class="badge bg-warning text-dark" style="direction: rtl;"> <?= $totalQuestions ?> سوال
                    </span>
                    <!-- //<span>صفحه <?= $currentPage ?> از <?= $totalPages ?></span> -->
                </div>
                <div class="col-8 text-end">
                    <div class="text-end">
                        <a class="btn btn-warning btn-sm btn-danger" href="../admin/practice.php"> <i
                                class="fas fa-times"></i></a>

                        <?php if ($mode === 'practice'): ?>
                            <button id="solve-btn" class="btn btn-warning btn-sm p-1" onclick="solveQuestion()"
                                style="display: none;">
                                <i class="fas fa-lightbulb"></i>
                            </button>
                            <button id="next-btn" class="btn btn-success mx-1 btn-sm p-1" onclick="nextQuestion()"
                                style="display: none;">
                                Weiter <i class="fas fa-arrow-right"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-success mx-1 btn-sm p-1" onclick="nextQuestion()">Weiter <i
                                    class="fas fa-arrow-right"></i></button>
                        <?php endif; ?>

                        <button id="bookmark-btn" class="btn btn-primary mx-1 btn-sm p-1 bookmark-btn"
                            onclick="toggleBookmark()" title="علامت گذاری سوال">
                            <i id="bookmark-icon" class="far fa-star text-warning"></i>
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
                <div class="d-md-block d-flex gap-1" id="question-buttons">
                    <!-- Question buttons will be rendered here -->
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
    <div id="translation-popup" class="translation-popup" style="display: none;">
        <button class="close-btn" onclick="closeTranslationPopup()">&times;</button>
        <div class="text-center">
            <div class="mb-3">
                <h5 class="text-primary">ترجمه</h5>
                <div class="border rounded p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong id="original-word" class="text-dark"></strong>
                        <small class="text-muted">آلمانی</small>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong id="translated-word" class="text-success"></strong>
                        <small class="text-muted">فارسی</small>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <button id="save-word-btn" class="btn btn-warning" onclick="saveWord()">
                    <i class="fas fa-save"></i> ذخیره کلمه
                </button>
                <button class="btn btn-secondary" onclick="closeTranslationPopup()">
                    <i class="fas fa-times"></i> بستن
                </button>
            </div>
        </div>
    </div>

    <div id="popup-overlay" class="popup-overlay" style="display: none;" onclick="closeTranslationPopup()"></div>

    <!-- Vocabulary Icons -->
    <div id="vocab-icons" class="vocab-icons">
        <button class="vocab-icon translate" title="ترجمه" onclick="event.stopPropagation(); translateWord();">
            <i class="fas fa-language"></i>
        </button>
        <button class="vocab-icon save" title="ذخیره (ابتدا ترجمه کنید)" onclick="event.stopPropagation(); saveWord();"
            disabled>
            <i class="fas fa-save"></i>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const csrfToken = '<?= $csrf_token ?>';

        const selectedQuestions = <?= json_encode($selectedQuestions) ?>;
        const mode = '<?= $mode ?>';
        let currentQuestionIndex = <?= $currentQuestionIndex ?>;
        let questionsPerPage;
        let currentQuestionData = null;
        let isVideoQuestion = false;
        let videoViewCount = 0;
        let maxVideoViews = 5;
        let hasWatchedVideo = false;
        let showingAnswers = false;
        let questionSolved = false;
        let userAnswers = {};
        let hasUserAnswer = false;
        let solvedQuestions = {}; // Track solved questions in memory
        let questionStatuses = {}; // برای ذخیره وضعیت رنگی سوالات
        let selectedText = '';
        let selectedRange = null;
        let currentTranslation = '';
        let currentWord = '';

        let vocabularyState = {
            translated: false,
            canSave: false
        };
        // Variables to track current context
        let currentQuestionId = null;
        let currentCategoryId = null;



        function createFormDataWithCSRF(data = {}) {
            const formData = new URLSearchParams();
            formData.append('csrf_token', csrfToken);

            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }

            return formData;
        }

        function updateQuestionsPerPage() {
            const width = window.innerWidth;
            if (width >= 992) {       // دسکتاپ
                questionsPerPage = 20;
            } else if (width >= 768) { // تبلت
                questionsPerPage = 8;
            } else {                  // موبایل
                questionsPerPage = 3;
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            loadQuestionStatuses(() => {
                loadCurrentQuestion();
                renderPageButtons();
            });
            initVocabularySystem();
            setupVocabIconsEvents();
        });

        window.addEventListener('resize', () => {
            renderPageButtons();
        });
        function initializeVocabularySystem() {
            // Initialize vocabulary system
            initVocabularySystem();

            // Get current question and category IDs
            if (typeof selectedQuestions !== 'undefined' && typeof currentQuestionIndex !== 'undefined') {
                currentQuestionId = selectedQuestions[currentQuestionIndex];
            }
        }
        // Vocabulary System Functions
        function initVocabularySystem() {
            // Add vocabulary selection class to content areas
            const textElement = document.getElementById('text');
            const answersElement = document.getElementById('answers');

            if (textElement) {
                textElement.classList.add('vocabulary-selection');
                addTextSelectionListeners(textElement);
            }

            if (answersElement) {
                answersElement.classList.add('vocabulary-selection');
                addTextSelectionListeners(answersElement);
            }

            // Hide icons when clicking outside - with delay to prevent immediate closure
            document.addEventListener('click', function (e) {
                // اگر روی vocab icons یا محتوای انتخاب شده کلیک شده، چیزی نکن
                if (e.target.closest('#vocab-icons') ||
                    e.target.closest('.vocab-icons') ||
                    e.target.closest('.vocabulary-selection')) {
                    return;
                }

                // تاخیر برای اطمینان از اینکه selection event اول اجرا شده
                setTimeout(() => {
                    hideVocabIcons();
                }, 150);
            }, true); // استفاده از capture mode

            // Update vocabulary context when question changes
            document.addEventListener('questionChanged', function (e) {
                updateVocabularyContext(e.detail.questionId, e.detail.categoryId);
            });
        }



        function addTextSelectionListeners(element) {
            // Mouse selection with improved handling
            element.addEventListener('mouseup', handleTextSelection);

            // Touch selection for mobile
            element.addEventListener('touchend', handleTextSelection);

            // Prevent immediate hide when clicking on vocabulary content
            element.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        let selectionTimeout;

        function handleTextSelection(event) {
            // پاک کردن timeout قبلی اگر وجود داشته باشد
            if (selectionTimeout) {
                clearTimeout(selectionTimeout);
            }

            // تاخیر بیشتر برای دسکتاپ
            const delay = window.innerWidth > 768 ? 200 : 100;

            selectionTimeout = setTimeout(() => {
                const selection = window.getSelection();
                const text = selection.toString().trim();

                if (text && isValidWord(text)) {
                    selectedText = text;
                    selectedRange = selection.getRangeAt(0);
                    showVocabIcons(event);
                } else {
                    // فقط اگر هیچ متن انتخاب نشده باشد، آیکون‌ها را مخفی کن
                    if (!text) {
                        hideVocabIcons();
                    }
                }
            }, delay);
        }

        function isValidWord(text) {
            // Check if it's a single word (no spaces, reasonable length)
            const trimmed = text.trim();
            const words = trimmed.split(/\s+/);

            // Only allow single words, 2-100 characters, containing letters
            return words.length === 1 &&
                trimmed.length >= 2 &&
                trimmed.length <= 100 &&
                /[a-zA-ZäöüßÄÖÜ]/.test(trimmed);
        }


        function showVocabIcons(event) {
            const iconsDiv = document.getElementById('vocab-icons');
            const selection = window.getSelection();

            if (selection.rangeCount === 0) return;

            const range = selection.getRangeAt(0);
            const rect = range.getBoundingClientRect();

            // Calculate position
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

            const top = rect.top + scrollTop - 60; // Above the selection
            const left = rect.left + scrollLeft + (rect.width / 2) - 45; // Center horizontally

            // Reset state
            vocabularyState.translated = false;
            vocabularyState.canSave = false;
            currentTranslation = '';
            currentWord = selectedText;

            // Update UI
            updateVocabButtons();

            // Position and show
            iconsDiv.style.top = top + 'px';
            iconsDiv.style.left = left + 'px';
            iconsDiv.style.display = 'block';

            // جلوگیری از event propagation
            event.stopPropagation();
        }

        function hideVocabIcons() {
            const iconsDiv = document.getElementById('vocab-icons');
            if (iconsDiv.style.display === 'block') {
                iconsDiv.style.display = 'none';
                clearSelection();
            }
        }


        function clearSelection() {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                selection.removeAllRanges();
            }
            selectedText = '';
            selectedRange = null;
        }

        function updateVocabButtons() {
            const translateBtn = document.querySelector('.vocab-icon.translate');
            const saveBtn = document.querySelector('.vocab-icon.save');

            // Update save button state
            if (vocabularyState.translated && vocabularyState.canSave) {
                saveBtn.disabled = false;
                saveBtn.title = 'افزودن به کلکشن';
            } else {
                saveBtn.disabled = true;
                saveBtn.title = vocabularyState.translated ? 'در کلکشن موجود است' : 'ابتدا ترجمه کنید';
            }
        }

        // 10. به‌روزرسانی توابع vocabulary system:
        function translateWord() {
            if (!selectedText) return;

            const translateBtn = document.querySelector('.vocab-icon.translate');
            translateBtn.classList.add('loading');
            translateBtn.disabled = true;

            event.stopPropagation();

            const formData = createFormDataWithCSRF({
                word: selectedText
            });

            fetch('../incloud/get_translation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.translation) {
                        currentWordId = data.word_id;
                        showTranslation(selectedText, data.translation, data.in_user_collection);
                    } else {
                        return googleTranslate(selectedText);
                    }
                })
                .catch(error => {
                    console.error('Database translation error:', error);
                    return googleTranslate(selectedText);
                })
                .finally(() => {
                    translateBtn.classList.remove('loading');
                    translateBtn.disabled = false;
                });
        }

        function googleTranslate(text) {
            const formData = createFormDataWithCSRF({
                text: text,
                from: 'de',
                to: 'fa'
            });

            return fetch('../incloud/google_translate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentWordId = data.word_id;
                        showTranslation(text, data.translation, false);
                    } else {
                        showVocabToast('خطا در ترجمه: ' + (data.error || 'نامشخص'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Google Translate error:', error);
                    showVocabToast('خطا در ارتباط با سرویس ترجمه', 'error');
                });
        }


        function showTranslation(originalWord, translation, inUserCollection) {
            currentWord = originalWord;
            currentTranslation = translation;
            vocabularyState.translated = true;
            vocabularyState.canSave = !inUserCollection; // فقط اگر در کلکشن کاربر نیست قابل ذخیره است

            // Update UI
            document.getElementById('original-word').textContent = originalWord;
            document.getElementById('translated-word').textContent = translation;

            // Show popup
            document.getElementById('translation-popup').style.display = 'block';
            document.getElementById('popup-overlay').style.display = 'block';

            // Update buttons
            updateVocabButtons();

            // Update save button in popup
            const saveBtn = document.getElementById('save-word-btn');
            if (inUserCollection) {
                saveBtn.innerHTML = '<i class="fas fa-check"></i> در کلکشن موجود است';
                saveBtn.disabled = true;
                saveBtn.className = 'btn btn-success';
            } else {
                saveBtn.innerHTML = '<i class="fas fa-plus"></i> افزودن به کلکشن';
                saveBtn.disabled = false;
                saveBtn.className = 'btn btn-warning';
            }

            hideVocabIcons();
        }

        function closeTranslationPopup() {
            document.getElementById('translation-popup').style.display = 'none';
            document.getElementById('popup-overlay').style.display = 'none';
        }
        // تابع ذخیره کلمه اصلاح شده
        function saveWord() {
            if (!vocabularyState.translated || !vocabularyState.canSave || !currentTranslation) {
                if (!vocabularyState.translated) {
                    showVocabToast('ابتدا کلمه را ترجمه کنید', 'error');
                } else {
                    showVocabToast('این کلمه در کلکشن واژگان شما موجود است', 'info');
                }
                return;
            }

            const saveBtn = document.getElementById('save-word-btn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال افزودن...';
            saveBtn.disabled = true;

            const contextData = {
                word: currentWord,
                translation: currentTranslation,
                question_id: currentQuestionId,
                category_id: currentCategoryId
            };

            const formData = createFormDataWithCSRF(contextData);

            fetch('../incloud/save_vocabulary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        vocabularyState.canSave = false;
                        saveBtn.innerHTML = '<i class="fas fa-check"></i> به کلکشن افزوده شد';
                        saveBtn.className = 'btn btn-success';
                        showVocabToast('کلمه با موفقیت به کلکشن واژگان افزوده شد', 'success');

                        setTimeout(() => {
                            closeTranslationPopup();
                        }, 2000);
                    } else {
                        saveBtn.innerHTML = originalText;
                        saveBtn.disabled = false;
                        showVocabToast('خطا: ' + (data.error || 'نامشخص'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Save vocabulary error:', error);
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                    showVocabToast('خطا در ارتباط با سرور', 'error');
                });
        }
        function showVocabToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} vocab-toast alert-dismissible`;
            toast.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <span><i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>${message}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;

            document.body.appendChild(toast);

            // Auto remove after 4 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 4000);
        }

        // Update current question context when question changes
        function updateVocabularyContext(questionId, categoryId = null) {
            currentQuestionId = questionId;
            currentCategoryId = categoryId;
        }
        // Re-initialize vocabulary selection for newly loaded content
        function reinitializeVocabularySelection() {
            const answersElement = document.getElementById('answers');
            if (answersElement) {
                answersElement.classList.add('vocabulary-selection');
                addTextSelectionListeners(answersElement);
            }
        }

        function initializeVocabularySystem() {
            // Initialize vocabulary system
            initVocabularySystem();

            // Get current question and category IDs
            if (typeof selectedQuestions !== 'undefined' && typeof currentQuestionIndex !== 'undefined') {
                currentQuestionId = selectedQuestions[currentQuestionIndex];
            }
        }        // بارگذاری وضعیت رنگی تمام سوالات

        function loadQuestionStatuses(callback) {
            const formData = createFormDataWithCSRF({
                question_ids: JSON.stringify(selectedQuestions)
            });

            fetch("../incloud/get_question_statuses.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        questionStatuses = data.data;
                    } else {
                        console.error('خطا در دریافت وضعیت سوالات:', data.error);
                        selectedQuestions.forEach(id => {
                            questionStatuses[id] = { color: 'gray', correct: 0, incorrect: 0 };
                        });
                    }
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('خطا در ارتباط با سرور:', error);
                    selectedQuestions.forEach(id => {
                        questionStatuses[id] = { color: 'gray', correct: 0, incorrect: 0 };
                    });
                    if (callback) callback();
                });
        }

        // ثبت پاسخ کاربر و به‌روزرسانی وضعیت
        function updateAnswerStatus(questionId, isCorrect) {
            const formData = createFormDataWithCSRF({
                question_id: questionId,
                is_correct: isCorrect ? 1 : 0
            });

            fetch("../incloud/update_answer_status.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        questionStatuses[questionId] = {
                            color: data.color,
                            correct: data.correct,
                            incorrect: data.incorrect
                        };
                        renderPageButtons();
                    } else {
                        console.error('خطا در ثبت وضعیت پاسخ:', data.error);
                    }
                })
                .catch(error => {
                    console.error('خطا در ارتباط با سرور:', error);
                });
        }

        function loadCurrentQuestion() {
            resetQuestionState();

            const questionId = selectedQuestions[currentQuestionIndex];

            if (!questionId) {
                console.error('No question ID found at current index:', currentQuestionIndex);
                return;
            }

            const formData = createFormDataWithCSRF({
                question_id: questionId
            });

            fetch("../incloud/get_question.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData
            }).then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
                .then(data => {
                    // باقی کد تابع loadCurrentQuestion بدون تغییر
                    if (data && data.success === false) {
                        console.error('Backend error:', data.message);
                        showErrorMessage(data.message || 'خطا در دریافت اطلاعات سوال');
                        markQuestionAsProblematic(questionId);
                        return;
                    }

                    if (!data || typeof data !== 'object') {
                        console.error('Invalid data received:', data);
                        showErrorMessage('خطا در دریافت اطلاعات سوال');
                        return;
                    }

                    if (!data.question) {
                        console.error('Question data is missing in response:', data);
                        showErrorMessage('اطلاعات سوال یافت نشد');
                        return;
                    }

                    currentQuestionData = data;
                    updateQuestionDisplay(data);
                    updateSession(questionId);
                    checkBookmarkStatus(questionId);

                    if (mode === 'practice') {
                        loadUserAnswersFromMemory(questionId);
                        updatePracticeButtons();
                    }
                })
                .catch(error => {
                    console.error("خطا در بارگذاری سوال:", error);
                    showErrorMessage('خطا در بارگذاری سوال: ' + error.message);
                });

            updateVocabularyContext(questionId, currentCategoryId);
            document.dispatchEvent(new CustomEvent('questionChanged', {
                detail: { questionId: questionId, categoryId: currentCategoryId }
            }));
        }

        function showErrorMessage(message) {
            const mediaElement = document.getElementById("media");
            const textElement = document.getElementById("text");
            const answersElement = document.getElementById("answers");
            const codeElement = document.getElementById("code");
            const punktElement = document.getElementById("punkt");

            if (mediaElement) {
                mediaElement.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + message + '</div>';
            }

            if (textElement) {
                textElement.innerText = 'سوال دارای مشکل است - پاسخ‌ها یافت نشد';
            }

            if (answersElement) {
                answersElement.innerHTML = '<div class="alert alert-warning">این سوال فاقد پاسخ است. لطفاً به سوال بعدی بروید.</div>';
            }

            if (codeElement) {
                const questionId = selectedQuestions[currentQuestionIndex];
                codeElement.innerText = questionId || 'N/A';
            }

            if (punktElement) {
                punktElement.innerText = '0';
            }
        }
        // Enhanced function to log and display problematic question details
        function markQuestionAsProblematic(questionId) {
            console.warn(`Question ${questionId} has no answers - marked as problematic`);

            // Log to console for immediate viewing
            console.log(`🚨 PROBLEMATIC QUESTION ID: ${questionId}`);

            // Show alert with question ID
            if (confirm(`سوال با کد ${questionId} فاقد پاسخ است. آیا می‌خواهید این کد را کپی کنید؟`)) {
                // Copy to clipboard if user confirms
                navigator.clipboard.writeText(questionId).then(() => {
                    alert(`کد سوال ${questionId} کپی شد`);
                }).catch(() => {
                    // Fallback for older browsers
                    prompt('کد سوال (Ctrl+C برای کپی):', questionId);
                });
            }

            // Store in localStorage for tracking
            let problematicQuestions = JSON.parse(localStorage.getItem('problematicQuestions') || '[]');
            if (!problematicQuestions.includes(questionId)) {
                problematicQuestions.push(questionId);
                localStorage.setItem('problematicQuestions', JSON.stringify(problematicQuestions));
            }

            // Also store with timestamp and more details
            let detailedProblematicQuestions = JSON.parse(localStorage.getItem('detailedProblematicQuestions') || '[]');
            const existingEntry = detailedProblematicQuestions.find(item => item.questionId === questionId);

            if (!existingEntry) {
                detailedProblematicQuestions.push({
                    questionId: questionId,
                    timestamp: new Date().toISOString(),
                    error: 'پاسخی جهت نمایش وجود ندارد',
                    questionIndex: currentQuestionIndex + 1,
                    totalQuestions: selectedQuestions.length
                });
                localStorage.setItem('detailedProblematicQuestions', JSON.stringify(detailedProblematicQuestions));
            }
        }

        // 7. به‌روزرسانی تابع toggleBookmark:
        function toggleBookmark() {
            const bookmarkBtn = document.getElementById('bookmark-btn');
            const bookmarkIcon = document.getElementById('bookmark-icon');
            const questionId = selectedQuestions[currentQuestionIndex];

            bookmarkBtn.classList.add('bookmark-loading');
            bookmarkBtn.disabled = true;

            const formData = createFormDataWithCSRF({
                question_id: questionId
            });

            fetch("../incloud/toggle_bookmark.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateBookmarkIcon(data.bookmarked);
                        showBookmarkToast(data.message, 'success');
                    } else {
                        showBookmarkToast(data.error || 'خطا در انجام عملیات', 'error');
                    }
                })
                .catch(error => {
                    console.error('Bookmark error:', error);
                    showBookmarkToast('خطا در ارتباط با سرور', 'error');
                })
                .finally(() => {
                    bookmarkBtn.classList.remove('bookmark-loading');
                    bookmarkBtn.disabled = false;
                });
        }

        function checkBookmarkStatus(questionId) {
            fetch("../incloud/check_bookmark.php?question_id=" + questionId + "&csrf_token=" + encodeURIComponent(csrfToken))
                .then(response => response.json())
                .then(data => {
                    updateBookmarkIcon(data.bookmarked);
                })
                .catch(error => {
                    console.error('Check bookmark error:', error);
                    updateBookmarkIcon(false);
                });
        }

        function updateBookmarkIcon(isBookmarked) {
            const bookmarkIcon = document.getElementById('bookmark-icon');

            if (isBookmarked) {
                // ستاره پر (علامت گذاری شده)
                bookmarkIcon.className = 'fas fa-star text-warning';
            } else {
                // ستاره خالی (علامت گذاری نشده)
                bookmarkIcon.className = 'far fa-star text-warning';
            }
        }

        function showBookmarkToast(message, type) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} bookmark-toast alert-dismissible`;
            toast.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>${message}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;

            document.body.appendChild(toast);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 3000);
        }

        function resetQuestionState() {
            resetVideoState();
            questionSolved = false;
            hasUserAnswer = false;
            userAnswers = {};
            hideVocabIcons();
        }

        function resetVideoState() {
            videoViewCount = 0;
            hasWatchedVideo = false;
            showingAnswers = false;
            isVideoQuestion = false;
        }

        function loadUserAnswersFromMemory(questionId) {
            // Load user's previous answers from localStorage only
            const savedData = localStorage.getItem('userAnswers_' + questionId);
            if (savedData) {
                try {
                    const parsedData = JSON.parse(savedData);
                    userAnswers = parsedData.answers || {};
                    hasUserAnswer = Object.keys(userAnswers).length > 0;
                    questionSolved = solvedQuestions[questionId] || false;

                    // Apply user answers to checkboxes
                    setTimeout(() => {
                        applyUserAnswers();
                    }, 100);

                    updatePracticeButtons();
                } catch (e) {
                    console.error('Error parsing saved data:', e);
                    userAnswers = {};
                    hasUserAnswer = false;
                }
            }
        }

        function applyUserAnswers() {
            if (Object.keys(userAnswers).length === 0) return;

            const checkboxes = document.querySelectorAll('.checkbox');
            checkboxes.forEach((checkbox, index) => {
                const answerId = checkbox.getAttribute('data-answer-id');
                if (userAnswers[answerId]) {
                    checkbox.checked = true;
                }
            });

            // Apply saved numeric answer if exists
            const numericInput = document.getElementById('numeric-answer');
            if (numericInput && userAnswers.numeric_value) {
                numericInput.value = userAnswers.numeric_value;
            }

            if (questionSolved) {
                showAnswerResults();
            }
        }

        function updatePracticeButtons() {
            const solveBtn = document.getElementById('solve-btn');
            const nextBtn = document.getElementById('next-btn');

            if (mode !== 'practice') return;

            if (questionSolved) {
                // Question is already solved
                solveBtn.style.display = 'none';
                nextBtn.style.display = 'inline-block';
            } else if (hasUserAnswer) {
                // User has answered but not solved yet
                solveBtn.style.display = 'inline-block';
                nextBtn.style.display = 'none';
            } else {
                // No answer yet
                solveBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            }
        }

        function solveQuestion() {
            if (mode !== 'practice' || questionSolved) return;

            questionSolved = true;

            // بررسی صحت پاسخ کاربر
            const isCorrect = checkUserAnswer();

            // ثبت وضعیت پاسخ در دیتابیس
            const questionId = selectedQuestions[currentQuestionIndex];
            updateAnswerStatus(questionId, isCorrect);

            showAnswerResults();

            // Save solution state in memory only
            solvedQuestions[questionId] = isCorrect;

            // Save to localStorage
            localStorage.setItem('solvedQuestion_' + questionId, JSON.stringify({
                solved: true,
                correct: isCorrect,
                timestamp: Date.now()
            }));

            updatePracticeButtons();
            renderPageButtons();
        }

        // تابع جدید برای بررسی صحت پاسخ کاربر
        function checkUserAnswer() {
            if (!currentQuestionData || !currentQuestionData.answers) return false;

            const answerType = currentQuestionData.answers[0]['asw_type'] || 1;

            if (answerType == 2) {
                // پاسخ عددی
                return checkNumericAnswer();
            } else {
                // پاسخ چندگزینه‌ای
                return checkMultipleChoiceAnswer();
            }
        }

        function checkMultipleChoiceAnswer() {
            let isCorrect = true;

            currentQuestionData.answers.forEach((answer) => {
                const checkbox = document.querySelector(`input[data-answer-id="${answer.id}"]`);
                if (!checkbox) return;

                const isAnswerCorrect = answer.asw_corr == 1;
                const isSelected = checkbox.checked;

                // اگر پاسخ صحیح انتخاب نشده یا پاسخ غلط انتخاب شده باشد
                if ((isAnswerCorrect && !isSelected) || (!isAnswerCorrect && isSelected)) {
                    isCorrect = false;
                }
            });

            return isCorrect;
        }

        function checkNumericAnswer() {
            const input = document.getElementById('numeric-answer');
            const correctAnswer = document.getElementById('correct-answer');

            if (input && correctAnswer) {
                return input.value.trim() === correctAnswer.value.trim();
            }
            return false;
        }

        function showAnswerResults() {
            if (!currentQuestionData || !currentQuestionData.answers) return;

            const answerType = currentQuestionData.answers[0]['asw_type'] || 1;

            if (answerType == 2) {
                // Handle numeric answers
                showNumericAnswerResult();
            } else {
                // Handle checkbox answers
                showCheckboxAnswerResults();
            }
        }

        function showNumericAnswerResult() {
            const input = document.getElementById('numeric-answer');
            const correctAnswer = document.getElementById('correct-answer');

            if (input && correctAnswer) {
                const isCorrect = input.value.trim() === correctAnswer.value.trim();

                // Show correct answer
                if (!isCorrect) {
                    input.value = correctAnswer.value;
                }

                input.style.backgroundColor = isCorrect ? '#d4edda' : '#f8d7da';
                input.style.borderColor = isCorrect ? '#28a745' : '#dc3545';
                input.readOnly = true;

                // Hide keypad
                const keypad = document.querySelector('.numeric-keypad');
                if (keypad) {
                    keypad.style.display = 'none';
                }
            }
        }

        function showCheckboxAnswerResults() {
            const answerItems = document.querySelectorAll('.answer-item');

            currentQuestionData.answers.forEach((answer, index) => {
                const checkbox = document.querySelector(`input[data-answer-id="${answer.id}"]`);
                const answerItem = answerItems[index];

                if (!checkbox || !answerItem) return;

                const isCorrect = answer.asw_corr == 1;
                const isSelected = checkbox.checked;

                // Remove existing classes
                answerItem.classList.remove('answer-correct-selected', 'answer-incorrect-selected',
                    'answer-correct-unselected', 'answer-incorrect-unselected');

                if (isCorrect && isSelected) {
                    // Correct answer selected - Green background
                    answerItem.classList.add('answer-correct-selected');
                } else if (!isCorrect && isSelected) {
                    // Wrong answer selected - Red background, uncheck
                    answerItem.classList.add('answer-incorrect-selected');
                    checkbox.checked = false;
                } else if (isCorrect && !isSelected) {
                    // Correct answer not selected - Red background, check it
                    answerItem.classList.add('answer-correct-unselected');
                    checkbox.checked = true;
                } else if (!isCorrect && !isSelected) {
                    // Wrong answer not selected - Green background
                    answerItem.classList.add('answer-incorrect-unselected');
                }

                // Disable further interaction
                checkbox.disabled = true;
                answerItem.classList.add('disabled');
            });
        }

        let imageUrl = '';
        let videoUrl = '';
        function updateQuestionDisplay(data) {
            // Add comprehensive null/undefined checks
            if (!data) {
                console.error('No data provided to updateQuestionDisplay');
                return;
            }

            if (!data.question) {
                console.error('Question data is missing:', data);
                return;
            }

            const question = data.question;
            const fileName = question.picture || '';
            const extension = fileName ? fileName.split('.').pop().toLowerCase() : '';
            const fileNameWithoutExt = fileName ? fileName.replace(/\.[^/.]+$/, "") : '';

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

            // Safely update question details with fallback values
            document.getElementById("code").innerText = question.number || 'N/A';
            document.getElementById("punkt").innerText = question.points || '0';
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
        // اضافه کردن تابع رمزگشایی ROT13
        function rot13Decode(str) {
            return str.replace(/[a-zA-Z]/g, function (a) {
                return String.fromCharCode((a <= "Z" ? 90 : 122) >= (a = a.charCodeAt(0) + 13) ? a : a - 26);
            });
        }
        // تابع answerBuilder بهبود یافته
        function answerBuilder(answers = null) {
            if (answers && answers.length > 0) {
                let answersText = "";
                const answerType = answers[0]['asw_type'] || 1;

                console.log(answers);
                console.log('Answer Type:', answerType);

                if (answerType == 2) {
                    // Type 2: Input field with numeric keypad
                    const answer = answers[0];
                    const hint = answer['asw_hint'] || '';
                    // رمزگشایی متن پاسخ عددی
                    let correctAnswer = rot13Decode(answer['text'] || '');

                    // In practice mode, don't show the correct answer initially
                    let displayValue = '';
                    if (mode === 'browse' || questionSolved) {
                        displayValue = correctAnswer;
                    }

                    answersText = `
        <div class="text-center">
            <div class="mb-4">
                <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                    <input type="text" 
                           id="numeric-answer" 
                           value="${displayValue}"
                           class="form-control text-center" 
                           style="width: 150px; font-size: 18px; font-weight: bold;" 
                           ${mode === 'browse' || questionSolved ? 'readonly' : ''}>
                    <span class="fw-bold fs-5">${hint}</span>
                </div>
            </div>
            
            <!-- Numeric Keypad -->
            <div class="numeric-keypad mx-auto" style="max-width: 300px; ${mode === 'browse' || questionSolved ? 'display: none;' : ''}">
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
                        <button class="btn btn-outline-secondary w-100 keypad-btn" onclick="clearLastChar()">⌫</button>
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
                    answers.forEach((answer, index) => {
                        let status = "";
                        let disabled = "";

                        if (mode === 'browse') {
                            // In browse mode, show correct answers
                            if (answer['asw_corr'] == 1) {
                                status = 'checked';
                            }
                            disabled = 'disabled';
                        } else if (mode === 'practice' && questionSolved) {
                            // In practice mode after solving, disable all
                            disabled = 'disabled';
                        }

                        // Check if this answer is an image
                        const isImage = answer['is_image'] == 1;
                        let answerContent = '';

                        if (isImage) {
                            // Extract filename from original_content or construct from text
                            let imageName = '';
                            let isEtcImage = false;

                            if (answer['original_content']) {
                                // بررسی برای %IMG_ETC% (مانند div10mult3.png)
                                const etcMatch = answer['original_content'].match(/%IMG_ETC%\/([^"']+)/);
                                if (etcMatch) {
                                    imageName = etcMatch[1];
                                    isEtcImage = true;
                                } else {
                                    // بررسی برای %IMG_ANSWER% (حالت عادی)
                                    const answerMatch = answer['original_content'].match(/%IMG_ANSWER%\/([^"']+)/);
                                    if (answerMatch) {
                                        imageName = answerMatch[1];
                                        isEtcImage = false;
                                    }
                                }
                            }

                            // If no filename found in original_content, try to extract from text
                            if (!imageName && answer['text']) {
                                // Extract number from text like "[تصویر: 121]" and use it with some pattern
                                const numberMatch = answer['text'].match(/\d+/);
                                if (numberMatch) {
                                    // You might need to adjust this pattern based on your actual file naming convention
                                    imageName = `answer_${answer['id']}.png`; // fallback pattern
                                    isEtcImage = false;
                                }
                            }

                            // If we still don't have imageName, try using the original_content pattern
                            if (!imageName) {
                                imageName = `answer_${answer['id']}.png`; // ultimate fallback
                                isEtcImage = false;
                            }

                            // تعیین آدرس بر اساس نوع تصویر
                            let imageUrl = '';
                            let maxWidth = '';
                            if (isEtcImage || imageName.toLowerCase().startsWith('div')) {
                                // اگر از %IMG_ETC% آمده یا با div شروع شود، از آدرس etc استفاده کن
                                imageUrl = `https://t24.theorie24.de/2025-01-v400/data/img/etc/de/${imageName}`;
                                maxWidth = 300;
                            } else {
                                // در غیر اینصورت از آدرس answers استفاده کن
                                imageUrl = `https://t24.theorie24.de/2025-01-v400/data/img/answers/${imageName}`;
                                maxWidth = 90;

                            }

                            answerContent = `<img src="${imageUrl}" alt="Answer ${index + 1}" class="img-fluid" style="max-width:${maxWidth}px; height: auto;">`;
                        } else {
                            // Regular text answer - رمزگشایی متن پاسخ
                            const decodedText = rot13Decode(answer['text']);
                            answerContent = `<span class="fw-bold vocabulary-selection" name="text" status="richtig">${decodedText}</span>`;
                        }

                        answersText += `
            <div class="d-flex mb-3 align-items-center answer-item" data-answer-index="${index}">
                <label class="form-label me-2 custom-checkbox">
                    <input type="checkbox" class="checkbox" data-answer-id="${answer['id']}" 
                           ${status} ${disabled} 
                           ${mode === 'practice' && !questionSolved ? 'onchange="handleAnswerChange(this)"' : ''}>
                    <span class="checkmark"></span>
                </label>
                ${answerContent}
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
        } function handleAnswerChange(checkbox) {
            if (mode !== 'practice' || questionSolved) return;

            const questionId = selectedQuestions[currentQuestionIndex];
            const answerId = checkbox.getAttribute('data-answer-id');

            // Update user answers in memory
            if (checkbox.checked) {
                userAnswers[answerId] = true;
            } else {
                delete userAnswers[answerId];
            }

            // Check if user has any answer
            hasUserAnswer = Object.keys(userAnswers).length > 0;

            // Save to localStorage only (no database)
            localStorage.setItem('userAnswers_' + questionId, JSON.stringify({
                answers: userAnswers,
                timestamp: Date.now()
            }));

            // Update buttons
            updatePracticeButtons();

            setTimeout(() => {
                reinitializeVocabularySelection();
            }, 100);
        }

        // Helper functions for numeric keypad
        function addNumber(num) {
            const input = document.getElementById('numeric-answer');
            if (!input) return;

            input.value += num;

            // Check if user has entered something and save to localStorage
            if (mode === 'practice' && !questionSolved) {
                hasUserAnswer = input.value.trim().length > 0;

                const questionId = selectedQuestions[currentQuestionIndex];
                userAnswers.numeric_value = input.value;

                localStorage.setItem('userAnswers_' + questionId, JSON.stringify({
                    answers: userAnswers,
                    timestamp: Date.now()
                }));

                updatePracticeButtons();
            }
        }

        function addComma() {
            const input = document.getElementById('numeric-answer');
            if (!input) return;

            // Only add comma if there isn't one already and input is not empty
            if (input.value && !input.value.includes(',')) {
                input.value += ',';

                // Save to localStorage
                if (mode === 'practice' && !questionSolved) {
                    const questionId = selectedQuestions[currentQuestionIndex];
                    userAnswers.numeric_value = input.value;

                    localStorage.setItem('userAnswers_' + questionId, JSON.stringify({
                        answers: userAnswers,
                        timestamp: Date.now()
                    }));
                }
            }
        }

        function clearLastChar() {
            const input = document.getElementById('numeric-answer');
            if (!input) return;

            input.value = input.value.slice(0, -1);

            // Update practice buttons and save to localStorage
            if (mode === 'practice' && !questionSolved) {
                hasUserAnswer = input.value.trim().length > 0;

                const questionId = selectedQuestions[currentQuestionIndex];
                userAnswers.numeric_value = input.value;

                localStorage.setItem('userAnswers_' + questionId, JSON.stringify({
                    answers: userAnswers,
                    timestamp: Date.now()
                }));

                updatePracticeButtons();
            }
        }

        function clearAnswer() {
            const input = document.getElementById('numeric-answer');
            if (!input) return;

            input.value = '';

            // Update practice buttons and save to localStorage
            if (mode === 'practice' && !questionSolved) {
                hasUserAnswer = false;

                const questionId = selectedQuestions[currentQuestionIndex];
                delete userAnswers.numeric_value;

                localStorage.setItem('userAnswers_' + questionId, JSON.stringify({
                    answers: userAnswers,
                    timestamp: Date.now()
                }));

                updatePracticeButtons();
            }
        }

        // 9. به‌روزرسانی تابع updateSession:
        function updateSession(questionId) {
            const formData = createFormDataWithCSRF({
                current_question_id: questionId
            });

            fetch("../incloud/update_session.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData
            });
        }

        // Enhanced nextQuestion function to auto-skip problematic questions
        function nextQuestion() {
            if (currentQuestionIndex < selectedQuestions.length - 1) {
                currentQuestionIndex++;
                loadCurrentQuestion();
                renderPageButtons();
            } else {
                // Reached the end of questions
                showEndOfQuestionsMessage();
            }
        }
        function showEndOfQuestionsMessage() {
            const mediaElement = document.getElementById("media");
            const textElement = document.getElementById("text");
            const answersElement = document.getElementById("answers");

            if (mediaElement) {
                mediaElement.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> تمام سوالات تمام شد</div>';
            }

            if (textElement) {
                textElement.innerText = 'تبریک! تمام سوالات را مشاهده کردید';
            }

            if (answersElement) {
                answersElement.innerHTML = '<div class="text-center"><a href="../admin/practice.php" class="btn btn-primary">بازگشت به صفحه اصلی</a></div>';
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
            const container = document.getElementById('question-buttons');
            container.innerHTML = '';

            function getQuestionsPerPage() {
                if (window.innerWidth < 768) {
                    return 3;
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
                const questionId = selectedQuestions[i];
                let buttonClass = 'btn-success';

                if (i === currentQuestionIndex) {
                    buttonClass = 'btn-dark';
                } else if (mode === 'practice') {
                    // Check question status from localStorage only
                    const solvedData = localStorage.getItem('solvedQuestion_' + questionId);
                    if (solvedData) {
                        try {
                            const parsed = JSON.parse(solvedData);
                            if (parsed.solved) {
                                buttonClass = parsed.correct ? 'btn-success question-btn-correct' : 'btn-danger question-btn-incorrect';
                            }
                        } catch (e) {
                            console.error('Error parsing solved question data:', e);
                        }
                    }
                }

                // ایجاد container برای دکمه و دایره رنگی
                const btnContainer = document.createElement('div');
                btnContainer.className = 'question-btn-container';

                const btn = document.createElement('button');
                btn.id = `btn${questionNumber}`;
                btn.className = `btn ${buttonClass}`;
                btn.textContent = questionNumber;
                btn.onclick = () => goToQuestion(i);

                // اضافه کردن دایره رنگی وضعیت
                const statusIndicator = document.createElement('div');
                statusIndicator.className = 'question-status-indicator';

                // تعیین رنگ بر اساس وضعیت سوال
                const questionStatus = questionStatuses[questionId];
                if (questionStatus) {
                    statusIndicator.classList.add(`status-${questionStatus.color}`);
                } else {
                    statusIndicator.classList.add('status-gray');
                }

                btnContainer.appendChild(btn);
                btnContainer.appendChild(statusIndicator);
                container.appendChild(btnContainer);
            }
        }
        function setupVocabIconsEvents() {
            const vocabIcons = document.getElementById('vocab-icons');
            if (vocabIcons) {
                vocabIcons.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
        }
    </script>
</body>

</html>