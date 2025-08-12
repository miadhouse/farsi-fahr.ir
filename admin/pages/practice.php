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
                 <div class="w-100 ce" style="    position: fixed !important;
    bottom: 0 !important;
    right: 0;
    z-index: 9999;
}">
                <div class="category-footer" id="categoryFooter" style="display: none;">
                    <div class="selected-category-info">
                        <h4 id="selectedCategoryTitle">دسته‌بندی انتخابی</h4>
                        <div class="selected-category-details">
                            <span id="selectedQuestionCount">0</span> سوال
                        </div>
                    </div>
                    <div class="category-actions">
                        <button class="btn-browse" id="browseQuestionsBtn" onclick="browseQuestions()">
                            <i class="fas fa-list"></i>
                            مرور سوالات
                        </button>
                        <button class="btn-practice" id="practiceBtn" onclick="startPractice()">
                            <i class="fas fa-play"></i>
                            تمرین
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

        .btn-browse {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(10px);
        }

        .btn-browse:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .btn-practice {
            background: #10b981;
            color: white;
        }

        .btn-practice:hover {
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
                    // Don't auto-highlight/expand on page load to keep interface clean
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

        // Highlight selected category
        function highlightSelectedCategory() {
            // Remove previous selections (but don't change accordion colors)
            document.querySelectorAll('.category-item, .subcategory-item').forEach(item => {
                item.classList.remove('selected');
            });

            // We don't need to highlight anything visually since the selection is shown in footer
            // The accordion expansion state is handled separately
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
            
            // Close all accordions when clearing selection
            closeAllAccordions();
        }

        // Navigation functions
        function browseQuestions() {
            if (selectedCategoryId) {
                const url = selectedType === 'subcategory' 
                    ? `test .php?id=${selectedCategoryId}` 
                    : `test .php?id=${selectedCategoryId}`;
                window.location.href = url;
            }
        }

        function startPractice() {
            if (selectedCategoryId) {
                const url = selectedType === 'subcategory'
                    ? `test.php?subcategory_id=${selectedCategoryId}`
                    : `test.php?category_id=${selectedCategoryId}`;
                window.location.href = url;
            }
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
                
                // Check if clicked on arrow (expand/collapse) or on the category itself
                const isArrowClick = e.target.classList.contains('arrow') || 
                                   e.target.closest('.arrow');
                
                if (isArrowClick && subcategoriesDiv) {
                    // Toggle expanded state when clicking arrow only
                    const isExpanded = subcategoriesDiv.classList.contains('expanded');
                    
                    if (isExpanded) {
                        // Collapse this accordion
                        subcategoriesDiv.classList.remove('expanded');
                        this.classList.remove('expanded');
                    } else {
                        // Close all other accordions first
                        closeAllAccordions();
                        // Expand this accordion
                        subcategoriesDiv.classList.add('expanded');
                        this.classList.add('expanded');
                    }
                } else {
                    // Select category and expand accordion when clicking on the category content
                    selectCategory(categoryId, categoryTitle, questionCount, 'category');
                    
                    // Close all other accordions first
                    closeAllAccordions();
                    
                    // Expand this category's accordion
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
<!-- Category Footer -->
