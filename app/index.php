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
                        <div id="answers">

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
                        <button class="btn btn-warning mx-1 btn-sm p-1 btn-danger"><i
                                class="fas fa-star fa-close"></i></button>
                        <button class="btn btn-success mx-1 btn-sm p-1" onclick="nextQuestion()">Weiter</button>
                        <button class="btn btn-warning mx-1 btn-sm p-1"><i class="fas fa-star"></i></button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const selectedQuestions = <?= json_encode($selectedQuestions) ?>;
        let currentQuestionIndex = <?= $currentQuestionIndex ?>;
        let questionsPerPage;

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
                    console.log(data['question']['picture']);
                    updateQuestionDisplay(data);

                    // به‌روزرسانی session
                    updateSession(questionId);
                })
                .catch(error => {
                    console.error("خطا:", error);
                });
        }
        let imageUrl = '';
        let videoUrl = '';
        function updateQuestionDisplay(data) {
            const fileName = data['question']['picture'] || '';
const extension = fileName.split('.').pop().toLowerCase();

if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
    imageUrl = 'https://t24.theorie24.de/2025-01-v400/data/img/images/' + fileName;
    document.getElementById("media").innerHTML = '<img id="image" src="' + imageUrl + '" alt="" class="w-100">';
} else if (['mp4', 'm4v', 'webm'].includes(extension)) {
    videoUrl = 'https://www.theorie24.de/live_images/_current_ws_2024-10-01_2025-04-01/videos/' + fileName;
    document.getElementById("media").innerHTML = '<video id="video" src="' + videoUrl + '" controls class="w-100"></video>';
} else {
    document.getElementById("media").innerHTML = ''; // فایل نامعتبر یا خالی
}

            document.getElementById("text").innerText = data['question']['text'];
            document.getElementById("code").innerText = data['question']['number'];
            document.getElementById("punkt").innerText = data['question']['points'];
            answerBuilder(data['answers']);
        }

        function answerBuilder(answers = null) {
            if (answers) {
                let answersText = "";
                let status = "";
                console.log(answers);
                answers.forEach(answer => {
                    if (answer['asw_corr'] == 1) {
                        status = 'checked';
                    } else {
                        status = '';
                    }
                    answersText += '<div class="d-flex mb-5  align-items-center">' +
                        '<label class="form-label me-2 custom-checkbox">' +
                        '<input type="checkbox" class="checkbox" ' + status + '>' +
                        '<span class="checkmark"></span>' +
                        '</label>' +
                        '<span class="fw-bold" name="text" status="richtig">' + answer['text'] + '</span>' +
                        '<span class="d-none" name="help_text"></span>' +
                        '</div>';
                });
                document.getElementById("answers").innerHTML = answersText;
            } else {
                console.log('خطا در استخراج پاسخ');
                return "";
            }
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
            container.innerHTML = ''; // پاک کردن دکمه‌های قبلی
            function getQuestionsPerPage() {
                if (window.innerWidth < 768) {
                    // موبایل
                    return 5;
                } else if (window.innerWidth < 992) {
                    // تبلت
                    return 10;
                } else {
                    // دسکتاپ
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
            // به‌روزرسانی دکمه‌های صفحه
            const buttons = document.querySelectorAll('[id^="btn"]');
            buttons.forEach(btn => {
                btn.className = 'btn p-2  btn-success';
            });

            const currentBtn = document.getElementById('btn' + (currentQuestionIndex + 1));
            if (currentBtn) {
                currentBtn.className = 'btn p-2  btn-dark';
            }
        }
    </script>
</body>

</html>