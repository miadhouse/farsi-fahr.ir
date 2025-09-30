<?php
// Get main categories
$grundstoff_main = getCategories($pdo, 1, 0);
$zusatzstoff_main = getCategories($pdo, 2, 1);

// Get all subcategories for JavaScript
$all_subcategories = [];
foreach (array_merge($grundstoff_main, $zusatzstoff_main) as $category) {
    $subcats = getSubcategories($pdo, $category['id']);
    if (!empty($subcats)) {
        $all_subcategories[$category['id']] = $subcats;
    }
}

// Calculate totals
$grundstoff_total = 0;
$zusatzstoff_total = 0;

foreach ($grundstoff_main as $cat) {
    $grundstoff_total += $cat['question_count'];
}

foreach ($zusatzstoff_main as $cat) {
    $zusatzstoff_total += $cat['question_count'];
}
?>
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="main-container">
        <div class="header">
            <h1>
                <i class="fas fa-car car-icon"></i>
                دسته‌بندی امتحان گواهینامه آلمان
            </h1>
            <p>ساختار کامل موضوعات و دسته‌بندی سوالات امتحان تئوری</p>
        </div>

        <div class="row g-4">
            <!-- Grundstoff (Basic Material) -->
            <div class="col-lg-6">
                <div class="category-card grundstoff">
                    <div class="card-title">
                        <div>
                            <div class="total-questions"><?php echo $grundstoff_total; ?> سوال - (Grundstoff) مواد پایه
                            </div>
                            <div style="font-size: 1rem; color: #6b7280; font-weight: normal;">
                                📚 موضوعات اصلی و پایه‌ای
                            </div>
                        </div>
                        <i class="fas fa-book icon"></i>
                    </div>

                    <?php foreach ($grundstoff_main as $category): ?>
                        <div class="category-container">
                            <div class="category-item <?php echo !empty($all_subcategories[$category['id']]) ? 'expandable' : ''; ?>"
                                data-category-id="<?php echo $category['id']; ?>"
                                data-category-title="<?php echo htmlspecialchars($category['title']); ?>"
                                data-question-count="<?php echo $category['question_count']; ?>">
                                <div class="category-text">
                                    <?php echo htmlspecialchars($category['title']); ?>
                                </div>
                                <div class="category-badges">
                                    <span class="question-badge"><?php echo $category['question_count']; ?></span>
                                    <span class="code-badge"><?php echo $category['index_code']; ?></span>
                                    <i class="fas fa-chevron-left arrow"></i>
                                </div>
                            </div>

                            <?php if (!empty($all_subcategories[$category['id']])): ?>
                                <div class="subcategories" id="sub-<?php echo $category['id']; ?>">
                                    <?php foreach ($all_subcategories[$category['id']] as $subcategory): ?>
                                        <div class="subcategory-item" data-subcategory-id="<?php echo $subcategory['id']; ?>"
                                            data-subcategory-title="<?php echo htmlspecialchars($subcategory['title']); ?>"
                                            data-question-count="<?php echo $subcategory['question_count']; ?>">
                                            <div class="subcategory-text">
                                                <?php echo htmlspecialchars($subcategory['title']); ?>
                                            </div>
                                            <div class="subcategory-badges">
                                                <span class="subcategory-badge"><?php echo $subcategory['question_count']; ?></span>
                                                <span class="subcategory-code"><?php echo $subcategory['index_code']; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Zusatzstoff (Additional Material) -->
            <div class="col-lg-6">
                <div class="category-card zusatzstoff">
                    <div class="card-title">
                        <div>
                            <div class="total-questions"><?php echo $zusatzstoff_total; ?> سوال - (Zusatzstoff) مواد
                                تکمیلی</div>
                            <div style="font-size: 1rem; color: #6b7280; font-weight: normal;">
                                📋 موضوعات تخصصی و تکمیلی
                            </div>
                        </div>
                        <i class="fas fa-clipboard-list icon"></i>
                    </div>

                    <?php foreach ($zusatzstoff_main as $category): ?>
                        <div class="category-container">
                            <div class="category-item <?php echo !empty($all_subcategories[$category['id']]) ? 'expandable' : ''; ?>"
                                data-category-id="<?php echo $category['id']; ?>"
                                data-category-title="<?php echo htmlspecialchars($category['title']); ?>"
                                data-question-count="<?php echo $category['question_count']; ?>">
                                <div class="category-text">
                                    <?php echo htmlspecialchars($category['title']); ?>
                                </div>
                                <div class="category-badges">
                                    <span class="question-badge"><?php echo $category['question_count']; ?></span>
                                    <span class="code-badge"><?php echo $category['index_code']; ?></span>
                                    <i class="fas fa-chevron-left arrow"></i>
                                </div>
                            </div>

                            <?php if (!empty($all_subcategories[$category['id']])): ?>
                                <div class="subcategories" id="sub-<?php echo $category['id']; ?>">
                                    <?php foreach ($all_subcategories[$category['id']] as $subcategory): ?>
                                        <div class="subcategory-item" data-subcategory-id="<?php echo $subcategory['id']; ?>"
                                            data-subcategory-title="<?php echo htmlspecialchars($subcategory['title']); ?>"
                                            data-question-count="<?php echo $subcategory['question_count']; ?>">
                                            <div class="subcategory-text">
                                                <?php echo htmlspecialchars($subcategory['title']); ?>
                                            </div>
                                            <div class="subcategory-badges">
                                                <span class="subcategory-badge"><?php echo $subcategory['question_count']; ?></span>
                                                <span class="subcategory-code"><?php echo $subcategory['index_code']; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="category-card" style="text-align: center;">
                    <h3 style="color: #374151; margin-bottom: 20px;">
                        <i class="fas fa-chart-bar"></i>
                        آمار کلی سوالات
                    </h3>
                    <div class="row">
                        <div class="col-md-4">
                            <div style="font-size: 2rem; color: #2563eb; font-weight: bold;">
                                <?php echo $grundstoff_total; ?>
                            </div>
                            <div style="color: #6b7280;">سوالات مواد پایه</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 2rem; color: #7c3aed; font-weight: bold;">
                                <?php echo $zusatzstoff_total; ?>
                            </div>
                            <div style="color: #6b7280;">سوالات مواد تکمیلی</div>
                        </div>
                        <div class="col-md-4">
                            <div style="font-size: 2rem; color: #059669; font-weight: bold;">
                                <?php echo $grundstoff_total + $zusatzstoff_total; ?>
                            </div>
                            <div style="color: #6b7280;">مجموع کل سوالات</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-100 ce" style="position: fixed !important; bottom: 0 !important; right: 0; z-index: 9999;">
                <div class="category-footer" id="categoryFooter" style="display: none;">
                    <div class="selected-category-info">
                        <h4 id="selectedCategoryTitle">دسته‌بندی انتخابی</h4>
                        <div class="selected-category-details">
                            <span id="selectedQuestionCount">0</span> سوال
                        </div>
                    </div>
                    <div class="category-actions">
                        <button class="btn-select-questions" id="selectQuestionsBtn" onclick="openQuestionsModal()">
                            <i class="fas fa-tasks"></i>
                            انتخاب سوالات
                        </button>
                        <button class="btn-clear" onclick="clearSelection()">
                            <i class="fas fa-times"></i>
                            حذف انتخاب
                        </button>
                    </div>
                </div>

                <div class="no-selection-message" id="noSelectionMessage">
                    <div class="message-content">
                        <i class="fas fa-info-circle"></i>
                        لطفا برای شروع یک دسته‌بندی را انتخاب کنید!
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Selection Modal -->
    <div class="modal fade" id="questionsModal" tabindex="-1" aria-labelledby="questionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex justify-content-between w-100">
                        <h5 class="modal-title" id="questionsModalLabel">انتخاب سوالات</h5>
                        <div class="modal-actions">
                            <button class="btn btn-info btn-sm me-2" id="browseQuestionsBtn"
                                onclick="browseQuestions()">
                                <i class="fas fa-list mx-1"></i>
                                مرور سوالات
                            </button>
                            <button class="btn btn-success btn-sm" id="practiceBtn" onclick="startPractice()">
                                <i class="fas fa-play mx-1"></i>
                                تمرین
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fs-6">
                            <span class="badge bg-primary p-1" id="selectedQuestionsCount">0</span>
                            سوال انتخاب شده از
                            <span class="badge bg-secondary p-1" id="totalQuestionsCount">0</span>
                            سوال
                        </div>
                        <div>
                            <button class="btn p-1 btn-sm btn-outline-primary btn-sm" onclick="selectAllQuestions()">
                                <i class="fas fa-check-double mx-1"></i>
                            </button>
                            <button class="btn p-1 btn-sm btn-outline-secondary btn-sm ms-1"
                                onclick="deselectAllQuestions()">
                                <i class="fas fa-times mx-1"></i>
                            </button>
                        </div>
                    </div>
                    <div id="questionsContainer">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">در حال بارگذاری...</span>
                            </div>
                            <div class="mt-2">در حال بارگذاری سوالات...</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <div class="selected-info">
                            <span class="text-muted">
                                <span id="footerSelectedCount">0</span> سوال انتخاب شده
                            </span>
                        </div>
                        <div>
                            <button class="btn btn-info me-2" onclick="browseQuestions()">
                                <i class="fas fa-list mx-1"></i>
                                مرور سوالات
                            </button>
                            <button class="btn btn-success" onclick="startPractice()">
                                <i class="fas fa-play mx-1"></i>
                                تمرین
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* استایل کلی آکاردئون */
        .category-container {
            margin-bottom: 0.5rem;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            user-select: none;
        }

        .category-item:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .category-item.expanded {
            background-color: #dbeafe;
            border-color: #3b82f6;
            color: #1d4ed8;
        }

        .category-item.expandable .arrow {
            transition: transform 0.3s ease;
        }

        .category-item.expandable.expanded .arrow {
            transform: rotate(90deg);
        }

        .category-text {
            flex-grow: 1;
            margin-right: 0.5rem;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .category-badges {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .question-badge,
        .code-badge,
        .subcategory-badge,
        .subcategory-code {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: bold;
        }

        .question-badge {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .code-badge {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        .subcategory-badge {
            background-color: #fef3c7;
            color: #92400e;
        }

        .subcategory-code {
            background-color: #f5f5f5;
            color: #6b7280;
        }

        /* استایل زیرمجموعه‌ها (Subcategories) */
        .subcategories {
            margin-top: 0.5rem;
            padding-left: 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            border-left: 3px solid #e5e7eb;
        }

        .subcategories.expanded {
            max-height: 2000px;
            /* مقدار بالایی برای انعطاف‌پذیری */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            border-left-color: #3b82f6;
        }

        .subcategory-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
            user-select: none;
        }

        .subcategory-item:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .subcategory-item.selected {
            background-color: #dbeafe;
            border-color: #3b82f6;
            color: #1d4ed8;
        }

        .subcategory-text {
            flex-grow: 1;
            margin-right: 0.5rem;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .subcategory-badges {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* استایل برای حالت انتخاب شده */
        .category-item.selected {
            background-color: #dbeafe;
            border-color: #3b82f6;
            color: #1d4ed8;
        }

        /* استایل برای افکت ریپل */
        .category-item,
        .subcategory-item {
            position: relative;
            overflow: hidden;
        }

        .category-item::after,
        .subcategory-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            background: rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.6s ease;
            pointer-events: none;
        }

        .category-item.ripple-effect::after,
        .subcategory-item.ripple-effect::after {
            width: 100%;
            height: 100%;
            transform: scale(1);
        }

        /* استایل برای تغییر جهت پیش‌فرض فونت فارسی */
        .category-text,
        .subcategory-text {
            direction: rtl;
            text-align: right;
        }

        /* استایل برای آیکون‌ها */
        .fas.arrow {
            font-size: 0.875rem;
            color: #6b7280;
            transition: transform 0.3s ease;
        }

        .fas.arrow:hover {
            color: #111827;
        }

        /* استایل برای کارت‌های دسته‌بندی */
        .category-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            transition: transform 0.2s ease;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

        .card-title i.icon {
            font-size: 1.5rem;
            color: #6b7280;
        }

        .total-questions {
            font-weight: bold;
            font-size: 1.1rem;
            color: #111827;
        }

        /* استایل برای فوتر انتخاب */
        #categoryFooter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .selected-category-info h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .selected-category-details {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .category-actions button {
            margin-left: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-select-questions {
            background: #10b981;
            color: white;
        }

        .btn-select-questions:hover {
            background: #059669;
        }

        .btn-clear {
            background: #ef4444;
            color: white;
        }

        .btn-clear:hover {
            background: #dc2626;
        }

        /* استایل پیام بدون انتخاب */
        .no-selection-message {
            background: #f3f4f6;
            color: #6b7280;
            padding: 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .message-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .message-content i {
            color: #3b82f6;
        }
    </style>

    <script>
        // Cookie helper functions
        function setCookie(name, value, days = 30) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function deleteCookie(name) {
            document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }

        // Global variables for selected category
        let selectedCategoryId = null;
        let selectedCategoryTitle = '';
        let selectedQuestionCount = 0;
        let selectedType = ''; // 'category' or 'subcategory'
        let loadedQuestions = [];

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            // Load saved category from cookie
            loadSavedCategory();

            // Initialize animations
            const cards = document.querySelectorAll('.category-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });

        // Load saved category from cookie
        function loadSavedCategory() {
            const savedCategory = getCookie('selectedCategory');
            if (savedCategory) {
                try {
                    const categoryData = JSON.parse(decodeURIComponent(savedCategory));
                    selectedCategoryId = categoryData.id;
                    selectedCategoryTitle = categoryData.title;
                    selectedQuestionCount = categoryData.questionCount;
                    selectedType = categoryData.type;

                    updateFooterDisplay();
                } catch (e) {
                    console.error('خطا در بارگذاری دسته‌بندی ذخیره شده:', e);
                    deleteCookie('selectedCategory');
                }
            }
        }

        // Save category to cookie
        function saveCategoryToCookie() {
            const categoryData = {
                id: selectedCategoryId,
                title: selectedCategoryTitle,
                questionCount: selectedQuestionCount,
                type: selectedType
            };
            setCookie('selectedCategory', encodeURIComponent(JSON.stringify(categoryData)));
        }

        // Update footer display
        function updateFooterDisplay() {
            const footer = document.getElementById('categoryFooter');
            const noSelectionMessage = document.getElementById('noSelectionMessage');
            const titleElement = document.getElementById('selectedCategoryTitle');
            const countElement = document.getElementById('selectedQuestionCount');

            if (selectedCategoryId) {
                footer.style.display = 'flex';
                noSelectionMessage.style.display = 'none';
                titleElement.textContent = selectedCategoryTitle;
                countElement.textContent = selectedQuestionCount;
            } else {
                footer.style.display = 'none';
                noSelectionMessage.style.display = 'block';
            }
        }

        // Select category function
        function selectCategory(id, title, questionCount, type) {
            selectedCategoryId = id;
            selectedCategoryTitle = title;
            selectedQuestionCount = questionCount;
            selectedType = type;

            updateFooterDisplay();
            saveCategoryToCookie();
        }

        // Clear selection
        function clearSelection() {
            selectedCategoryId = null;
            selectedCategoryTitle = '';
            selectedQuestionCount = 0;
            selectedType = '';

            updateFooterDisplay();
            deleteCookie('selectedCategory');

            closeAllAccordions();
        }

        // Open questions modal
        function openQuestionsModal() {
            if (!selectedCategoryId) return;

            const modalElement = document.getElementById('questionsModal');
            const modal = new bootstrap.Modal(modalElement);
            const modalTitle = document.getElementById('questionsModalLabel');

            modalTitle.textContent = `انتخاب سوالات - ${selectedCategoryTitle}`;

            // Show the modal
            modal.show();

            // Add event listeners to the modal element, not the Modal instance
            modalElement.addEventListener('shown.bs.modal', () => {
                document.getElementById('categoryFooter').hidden = true;;
            });

            modalElement.addEventListener('hidden.bs.modal', () => {
                document.getElementById('categoryFooter').hidden = false;;

            });

            loadQuestions();
        }
        // Load questions via AJAX
        function loadQuestions() {
            const container = document.getElementById('questionsContainer');

            // Show loading spinner
            container.innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                    </div>
                    <div class="mt-2">در حال بارگذاری سوالات...</div>
                </div>
            `;

            const url = selectedType === 'subcategory'
                ? `pages/load_questions.php?subcategory_id=${selectedCategoryId}`
                : `pages/load_questions.php?category_id=${selectedCategoryId}`;

            fetch(url)
                .then(response => response.text())
                .then(data => {
                    container.innerHTML = data;
                    updateQuestionCounts();
                    setupQuestionCheckboxes();
                })
                .catch(error => {
                    console.error('خطا در بارگذاری سوالات:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle"></i>
                            خطا در بارگذاری سوالات. لطفا دوباره تلاش کنید.
                        </div>
                    `;
                });
        }

        // Setup question checkboxes
        function setupQuestionCheckboxes() {
            const checkboxes = document.querySelectorAll('#questionsContainer input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateQuestionCounts);
            });
        }

        // Update question counts
        function updateQuestionCounts() {
            const checkboxes = document.querySelectorAll('#questionsContainer input[type="checkbox"]');
            const selectedCheckboxes = document.querySelectorAll('#questionsContainer input[type="checkbox"]:checked');

            const totalCount = checkboxes.length;
            const selectedCount = selectedCheckboxes.length;

            document.getElementById('totalQuestionsCount').textContent = totalCount;
            document.getElementById('selectedQuestionsCount').textContent = selectedCount;
            document.getElementById('footerSelectedCount').textContent = selectedCount;
        }

        // Select all questions
        function selectAllQuestions() {
            const checkboxes = document.querySelectorAll('#questionsContainer input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            updateQuestionCounts();
        }

        // Deselect all questions
        function deselectAllQuestions() {
            const checkboxes = document.querySelectorAll('#questionsContainer input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateQuestionCounts();
        }

        // Get selected question IDs
        function getSelectedQuestionIds() {
            const selectedCheckboxes = document.querySelectorAll('#questionsContainer input[type="checkbox"]:checked');
            return Array.from(selectedCheckboxes).map(checkbox => checkbox.id);
        }

        // Navigate to test page with selected questions
        function navigateToTest(mode = 'browse') {
            const selectedQuestions = getSelectedQuestionIds();

            if (selectedQuestions.length === 0) {
                alert('لطفا حداقل یک سوال را انتخاب کنید.');
                return;
            }

            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../app/index.php';

            // Add selected questions as hidden inputs
            selectedQuestions.forEach(questionId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_questions[]';
                input.value = questionId;
                form.appendChild(input);
            });

            // Add mode parameter
            const modeInput = document.createElement('input');
            modeInput.type = 'hidden';
            modeInput.name = 'mode';
            modeInput.value = mode;
            form.appendChild(modeInput);

            // Add category info
            const categoryInput = document.createElement('input');
            categoryInput.type = 'hidden';
            categoryInput.name = selectedType === 'subcategory' ? 'subcategory_id' : 'category_id';
            categoryInput.value = selectedCategoryId;
            form.appendChild(categoryInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Browse questions function
        function browseQuestions() {
            navigateToTest('browse');
        }

        // Start practice function
        function startPractice() {
            navigateToTest('practice');
        }

        // Function to close all accordions
        function closeAllAccordions() {
            document.querySelectorAll('.subcategories.expanded').forEach(sub => {
                sub.classList.remove('expanded');
            });
            document.querySelectorAll('.category-item.expanded').forEach(item => {
                item.classList.remove('expanded');
            });
        }

        // Add click functionality to expandable category items
        // Add click functionality to expandable category items
        document.querySelectorAll('.category-item.expandable').forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const categoryId = this.getAttribute('data-category-id');
                const categoryTitle = this.getAttribute('data-category-title');
                const questionCount = parseInt(this.getAttribute('data-question-count'));
                const subcategoriesDiv = document.getElementById('sub-' + categoryId);

                // همیشه دسته‌بندی را انتخاب کن
                selectCategory(categoryId, categoryTitle, questionCount, 'category');

                // اگر زیرمجموعه وجود دارد، آکاردئون را toggle کن
                if (subcategoriesDiv) {
                    const isExpanded = subcategoriesDiv.classList.contains('expanded');

                    if (isExpanded) {
                        // بستن
                        subcategoriesDiv.classList.remove('expanded');
                        this.classList.remove('expanded');
                    } else {
                        // باز کردن (و بستن سایر آکاردئون‌ها)
                        closeAllAccordions();
                        subcategoriesDiv.classList.add('expanded');
                        this.classList.add('expanded');
                    }
                }
            });
        });
        // Add click functionality to subcategory items
        document.querySelectorAll('.subcategory-item').forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const subcategoryId = this.getAttribute('data-subcategory-id');
                const subcategoryTitle = this.getAttribute('data-subcategory-title');
                const questionCount = parseInt(this.getAttribute('data-question-count'));

                selectCategory(subcategoryId, subcategoryTitle, questionCount, 'subcategory');
            });
        });

        // Add click functionality to non-expandable category items
        document.querySelectorAll('.category-item:not(.expandable)').forEach(item => {
            item.addEventListener('click', function () {
                const categoryId = this.getAttribute('data-category-id');
                const categoryTitle = this.getAttribute('data-category-title');
                const questionCount = parseInt(this.getAttribute('data-question-count'));

                selectCategory(categoryId, categoryTitle, questionCount, 'category');
            });
        });

        // Add ripple effect to category items
        document.querySelectorAll('.category-item, .subcategory-item').forEach(item => {
            item.addEventListener('click', function (e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(59, 130, 246, 0.3);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    pointer-events: none;
                `;

                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>

</div>