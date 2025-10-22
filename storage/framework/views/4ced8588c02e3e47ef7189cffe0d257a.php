<?php
    $availableLocales = config('locales.available_locales', []);
    $currentLocale = app()->getLocale();
    $currentLocaleData = $availableLocales[$currentLocale] ?? ['flag' => '🌐', 'name' => 'English'];
?>

<?php if(!empty($availableLocales)): ?>
<div class="language-switcher">
    <button class="language-btn" type="button" id="languageSwitcherBtn">
        <span class="language-flag"><?php echo e($currentLocaleData['flag']); ?></span>
        <span class="language-name"><?php echo e($currentLocaleData['name']); ?></span>
        <svg class="language-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="4 6 8 10 12 6"></polyline>
        </svg>
    </button>
    
    <div class="language-dropdown" id="languageDropdown" style="display: none;">
        <?php $__currentLoopData = $availableLocales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $locale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('language.switch', $code)); ?>" 
               class="language-option <?php echo e($currentLocale === $code ? 'active' : ''); ?>"
               data-locale="<?php echo e($code); ?>">
                <span class="language-flag"><?php echo e($locale['flag']); ?></span>
                <span class="language-name"><?php echo e($locale['name']); ?></span>
                <?php if($currentLocale === $code): ?>
                    <svg class="language-check" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 8 6 11 13 4"></polyline>
                    </svg>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<script>
(function() {
    const switcher = document.querySelector('.language-switcher');
    const btn = document.getElementById('languageSwitcherBtn');
    const dropdown = document.getElementById('languageDropdown');
    
    if (!btn || !dropdown) return;
    
    // Toggle dropdown
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        btn.querySelector('.language-arrow').classList.toggle('rotate-180', !isOpen);
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!switcher.contains(e.target)) {
            dropdown.style.display = 'none';
            btn.querySelector('.language-arrow').classList.remove('rotate-180');
        }
    });
    
    const options = dropdown.querySelectorAll('.language-option');
    options.forEach(option => {
        option.addEventListener('click', function(e) {
            // Show loading state
            btn.disabled = true;
            btn.style.opacity = '0.6';
            
            // Add a small delay to ensure session is saved before navigation
            setTimeout(() => {
                // Let the browser navigate to the href naturally
                // The route will set session and redirect back with cache-busting parameter
            }, 100);
        });
    });
})();
</script>

<style>
.language-switcher {
    position: relative;
    display: inline-block;
}

.language-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
    min-height: 44px;
}

.language-btn:hover:not(:disabled) {
    background: var(--bg-secondary);
    border-color: var(--accent-primary);
}

.language-btn:disabled {
    cursor: not-allowed;
}

.language-flag {
    font-size: 1.25rem;
    line-height: 1;
}

.language-name {
    font-weight: 500;
}

.language-arrow {
    transition: transform 0.2s;
    flex-shrink: 0;
}

.language-arrow.rotate-180 {
    transform: rotate(180deg);
}

.language-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 200px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    overflow: hidden;
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.language-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.2s;
    min-height: 44px;
    cursor: pointer;
}

.language-option:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.language-option.active {
    background: var(--accent-primary);
    color: white;
}

.language-option.active .language-flag,
.language-option.active .language-name {
    color: white;
}

.language-check {
    margin-left: auto;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .language-dropdown {
        right: auto;
        left: 0;
    }
}
</style>
<?php endif; ?>
<?php /**PATH /home/fxiasdcg/root/resources/views/components/language-switcher-custom.blade.php ENDPATH**/ ?>