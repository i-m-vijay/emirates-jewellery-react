import { ChevronLeft, ChevronRight } from 'lucide-react';

function getPaginationPages(currentPage, totalPages) {
  if (totalPages <= 7) {
    return Array.from({ length: totalPages }, (_, i) => i + 1);
  }

  const pages = [1];
  const left = Math.max(2, currentPage - 1);
  const right = Math.min(totalPages - 1, currentPage + 1);

  if (left > 2) pages.push('left-ellipsis');
  for (let i = left; i <= right; i += 1) pages.push(i);
  if (right < totalPages - 1) pages.push('right-ellipsis');
  pages.push(totalPages);

  return pages;
}

export default function Pagination({ page, totalPages, onPageChange, loading = false }) {
  if (!totalPages || totalPages <= 1) return null;

  return (
    <div className="cat-page__pagination" role="navigation" aria-label="Pagination">
      <button
        className="cat-page__pagination-btn"
        type="button"
        onClick={() => onPageChange(page - 1)}
        disabled={page === 1 || loading}
        aria-label="Previous page"
      >
        <ChevronLeft size={16} />
      </button>

      {getPaginationPages(page, totalPages).map((pageNum, index) => (
        pageNum === 'left-ellipsis' || pageNum === 'right-ellipsis' ? (
          <span key={`${pageNum}-${index}`} className="cat-page__pagination-ellipsis">…</span>
        ) : (
          <button
            key={pageNum}
            type="button"
            className={`cat-page__pagination-btn${pageNum === page ? ' cat-page__pagination-btn--active' : ''}`}
            onClick={() => onPageChange(pageNum)}
            disabled={pageNum === page || loading}
            aria-current={pageNum === page ? 'page' : undefined}
          >
            {pageNum}
          </button>
        )
      ))}

      <button
        className="cat-page__pagination-btn"
        type="button"
        onClick={() => onPageChange(page + 1)}
        disabled={page === totalPages || loading}
        aria-label="Next page"
      >
        <ChevronRight size={16} />
      </button>
    </div>
  );
}
