<?php
namespace Footup\Paginator\Views;

/**
 * @var \Footup\Paginator\Paginator $paginator
 */
$paginator;

if ($paginator->hasPages()):
?>
    <ul class="pagination">
        <?php if ($paginator->isOnFirstPage() === true): ?>
            <li class="page-item disabled">
				<span class="page-link">&laquo;</span>
			</li>
            <li class="page-item disabled">
				<span class="page-link">&lsaquo;</span>
			</li>
        <?php else: ?>
            <li class="page-item">
				<a class="page-link" href="<?= $paginator->getFirstPageUrl() ?>" rel="prev" title="Previous">&laquo;</a>
			</li>
            <li class="page-item">
				<a class="page-link" href="<?= $paginator->getPreviousPageUrl() ?>" rel="prev" title="Previous">&lsaquo;</a>
			</li>
		<?php endif;
		
			$hiddenRanges = $paginator->getHiddenRanges();
		?>

        <?php foreach ($paginator->getPages() as $page): ?>
            <?php // Three Dots Separator
			
            if ((isset($hiddenRanges[0]) && $page->getNumber() === $hiddenRanges[0]['start']) ||
            (isset($hiddenRanges[1]) && $page->getNumber() === $hiddenRanges[1]['start'])):

			?>
                <li class="page-item disabled">
					<span class="page-link">...</span>
				</li>
			<?php

			elseif((isset($hiddenRanges[0]) && $page->getNumber() > $hiddenRanges[0]['start'] && $page->getNumber() <= $hiddenRanges[0]['finish']) ||
            (isset($hiddenRanges[1]) && $page->getNumber() > $hiddenRanges[1]['start'] && $page->getNumber() <= $hiddenRanges[1]['finish'])):
                continue;
			else:
                if ($page->isCurrent()):
				?>
                    <li class="page-item active">
						<span class="page-link"><?= $page->getNumber() ?></span>
					</li>
                <?php else: ?>
                    <li class="page-item"><a class="page-link" href="<?= $page->getUrl() ?>" title="Page"><?= $page->getNumber() ?></a></li>
                <?php endif ?>
			<?php endif ?>
		<?php endforeach;

        if ($paginator->isOnLastPage() === false): ?>
            <li class="page-item">
				<a class="page-link" href="<?= $paginator->getNextPageUrl() ?>" rel="next" title="Next">&rsaquo;</a>
			</li>
            <li class="page-item">
				<a class="page-link" href="<?= $paginator->getLastPageUrl() ?>" rel="next" title="Next">&raquo;</a>
			</li>
        <?php else: ?>
            <li class="page-item disabled">
				<span class="page-link">&rsaquo;</span>
			</li>
            <li class="page-item disabled">
				<span class="page-link">&raquo;</span>
			</li>
        <?php endif ?>
    </ul>
<?php endif ?>