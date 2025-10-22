<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Pagination Navigation" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-top: 1px solid var(--border-color);">
        <div style="display: flex; gap: 0.5rem;">
            <?php if($paginator->onFirstPage()): ?>
                <span style="padding: 0.5rem 1rem; background: var(--bg-tertiary); color: var(--text-muted); border-radius: 0.5rem; cursor: not-allowed;">
                    &laquo; Previous
                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" style="padding: 0.5rem 1rem; background: var(--bg-tertiary); color: var(--text-primary); border-radius: 0.5rem; text-decoration: none; transition: all 0.2s;">
                    &laquo; Previous
                </a>
            <?php endif; ?>

            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" style="padding: 0.5rem 1rem; background: var(--accent-primary); color: white; border-radius: 0.5rem; text-decoration: none; transition: all 0.2s;">
                    Next &raquo;
                </a>
            <?php else: ?>
                <span style="padding: 0.5rem 1rem; background: var(--bg-tertiary); color: var(--text-muted); border-radius: 0.5rem; cursor: not-allowed;">
                    Next &raquo;
                </span>
            <?php endif; ?>
        </div>

        <div style="color: var(--text-secondary); font-size: 0.875rem;">
            Showing <?php echo e($paginator->firstItem()); ?> to <?php echo e($paginator->lastItem()); ?> of <?php echo e($paginator->total()); ?> results
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH /home/fxiasdcg/root/resources/views/vendor/pagination/custom.blade.php ENDPATH**/ ?>