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
                                        <div class="subcategory-item" 
                                             data-subcategory-id="<?php echo $subcategory['id']; ?>"
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
                                        <div class="subcategory-item" 
                                             data-subcategory-id="<?php echo $subcategory['id']; ?>"
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
                            <button class="btn btn-info btn-sm me-2" id="browseQuestionsBtn" onclick="browseQuestions()">
                                <i class="fas fa-list"></i>
                                مرور سوالات
                            </button>
                            <button class="btn btn-success btn-sm" id="practiceBtn" onclick="startPractice()">
                                <i class="fas fa-play"></i>
                                تمرین
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-primary" id="selectedQuestionsCount">0</span>
                            سوال انتخاب شده از 
                            <span class="badge bg-secondary" id="totalQuestionsCount">0</span>
                            سوال
                        </div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" onclick="selectAllQuestions()">
                                <i class="fas fa-check-double"></i>
                                انتخاب همه
                            </button>
                            <button class="btn btn-outline-secondary btn-sm ms-1" onclick="deselectAllQuestions()">
                                <i class="fas fa-times"></i>
                                حذف انتخاب همه
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
                                <i class="fas fa-list"></i>
                                مرور سوالات
                            </button>
                            <button class="btn btn-success" onclick="startPractice()">
                                <i class="fas fa-play"></i>
                                تمرین
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .category-footer {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding:10px 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            animation: slideUp 0.5s ease-out;
        }

        .selected-category-info h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }

        .selected-category-details {
            font-size: 1rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        .category-actions {
            display: flex;
            gap: 15px;
        }

        .category-actions button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-select-questions {
            background: #10b981;
            color: white;
        }

        .btn-select-questions:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-clear {
            background: rgba(239, 68, 68, 0.8);
            color: white;
        }

        .btn-clear:hover {
            background: rgba(239, 68, 68, 1);
            transform: translateY(-2px);
        }

        .no-selection-message {
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            color: #6b7280;
        }

        .message-content {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .message-content i {
            color: #3b82f6;
            font-size: 1.5rem;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .category-item.selected,
        .subcategory-item.selected {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            transform: translateX(-5px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            position: relative;
        }

        .category-item.selected::after {
            content: '✓';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .subcategory-item.selected::after {
            content: '✓';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-weight: bold;
            font-size: 1rem;
        }

        .category-item.selected .question-badge,
        .category-item.selected .code-badge,
        .subcategory-item.selected .subcategory-badge,
        .subcategory-item.selected .subcategory-code {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .category-item.selected .arrow {
            color: white;
        }

        /* Modal Styles */
        .modal-actions {
            display: flex;
            align-items: center;
        }

        .form-check {
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .form-check:hover {
            background-color: #f8f9fa;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .selected-info {
            color: #6c757d;
            font-size: 0.9rem;
        }

        #questionsContainer {
            max-height: 400px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .category-footer {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .category-actions {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .category-actions button {
                flex: 1;
                min-width: 120px;
            }

            .modal-header .d-flex {
                flex-direction: column;
                gap: 10px;
            }

            .modal-actions {
                justify-content: center;
            }

            .modal-footer .d-flex {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
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
            for(let i = 0; i < ca.length; i++) {
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
        document.addEventListener('DOMContentLoaded', function() {
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

            const modal = new bootstrap.Modal(document.getElementById('questionsModal'));
            const modalTitle = document.getElementById('questionsModalLabel');
            modalTitle.textContent = `انتخاب سوالات - ${selectedCategoryTitle}`;
            
            modal.show();
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
            form.action = 'test.php';
            
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
        document.querySelectorAll('.category-item.expandable').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const categoryId = this.getAttribute('data-category-id');
                const categoryTitle = this.getAttribute('data-category-title');
                const questionCount = parseInt(this.getAttribute('data-question-count'));
                const subcategoriesDiv = document.getElementById('sub-' + categoryId);
                
                const isArrowClick = e.target.classList.contains('arrow') || 
                                   e.target.closest('.arrow');
                
                if (isArrowClick && subcategoriesDiv) {
                    const isExpanded = subcategoriesDiv.classList.contains('expanded');
                    
                    if (isExpanded) {
                        subcategoriesDiv.classList.remove('expanded');
                        this.classList.remove('expanded');
                    } else {
                        closeAllAccordions();
                        subcategoriesDiv.classList.add('expanded');
                        this.classList.add('expanded');
                    }
                } else {
                    selectCategory(categoryId, categoryTitle, questionCount, 'category');
                    
                    closeAllAccordions();
                    
                    if (subcategoriesDiv) {
                        subcategoriesDiv.classList.add('expanded');
                        this.classList.add('expanded');
                    }
                }
            });
        });
        
        // Add click functionality to subcategory items
        document.querySelectorAll('.subcategory-item').forEach(item => {
            item.addEventListener('click', function(e) {
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
            item.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                const categoryTitle = this.getAttribute('data-category-title');
                const questionCount = parseInt(this.getAttribute('data-question-count'));
                
                selectCategory(categoryId, categoryTitle, questionCount, 'category');
            });
        });
        
        // Add ripple effect to category items
        document.querySelectorAll('.category-item, .subcategory-item').forEach(item => {
            item.addEventListener('click', function(e) {
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