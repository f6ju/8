/* ─── INDEX: søk ────────────────────────────────────── */
const searchInput = document.getElementById('search');
if (searchInput) {
  const cards = document.querySelectorAll('.card');
  const noResults = document.getElementById('no-results');

  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase().trim();
    let visible = 0;
    cards.forEach(card => {
      const match = card.dataset.name.includes(q) || card.dataset.subject.includes(q);
      card.style.display = match ? '' : 'none';
      if (match) visible++;
    });
    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
  });
}

/* ─── TEACHER: stjernevelger ─────────────────────────── */
const starBtns = document.querySelectorAll('.star-btn');
const starsInput = document.getElementById('stars-value');
const starHint = document.getElementById('star-hint');

const starLabels = ['', 'Veldig dårlig', 'Dårlig', 'OK', 'Bra', 'Veldig bra'];

if (starBtns.length) {
  starBtns.forEach(btn => {
    btn.addEventListener('mouseenter', () => highlightStars(btn.dataset.value, false));
    btn.addEventListener('mouseleave', () => highlightStars(starsInput?.value || 0, true));
    btn.addEventListener('click', () => {
      starsInput.value = btn.dataset.value;
      highlightStars(btn.dataset.value, true);
      if (starHint) {
        starHint.textContent = starLabels[btn.dataset.value];
        starHint.classList.add('chosen');
      }
    });
  });
}

function highlightStars(val, persist) {
  starBtns.forEach(btn => {
    btn.classList.toggle('active', parseInt(btn.dataset.value) <= parseInt(val));
  });
}

/* ─── TEACHER: send review ───────────────────────────── */
const reviewForm = document.getElementById('review-form');
const submitBtn  = document.getElementById('submit-btn');
const formError  = document.getElementById('form-error');
const successState = document.getElementById('success-state');
const anotherBtn = document.getElementById('another-btn');

if (reviewForm) {
  reviewForm.addEventListener('submit', async e => {
    e.preventDefault();

    if (!starsInput.value) {
      formError.textContent = 'Velg antall stjerner først.';
      starHint.style.color = 'var(--red)';
      starHint.textContent = 'Obligatorisk';
      return;
    }

    formError.textContent = '';
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sender…';

    try {
      const res = await fetch('api/submit.php', {
        method: 'POST',
        body: new FormData(reviewForm)
      });

      const data = await res.json();

      if (data.success) {
        reviewForm.style.display = 'none';
        successState.style.display = 'block';
        addReviewToList(data.review);
        updateHeroRating(data.new_avg, data.new_count);
      } else {
        formError.textContent = data.error || 'Noe gikk galt. Prøv igjen.';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send vurdering';
      }
    } catch {
      formError.textContent = 'Nettverksfeil. Prøv igjen.';
      submitBtn.disabled = false;
      submitBtn.textContent = 'Send vurdering';
    }
  });
}

if (anotherBtn) {
  anotherBtn.addEventListener('click', () => {
    reviewForm.reset();
    starsInput.value = '';
    starHint.textContent = 'Velg antall stjerner';
    starHint.classList.remove('chosen');
    starHint.style.color = '';
    highlightStars(0, true);
    submitBtn.disabled = false;
    submitBtn.textContent = 'Send vurdering';
    reviewForm.style.display = '';
    successState.style.display = 'none';
  });
}

/* ─── Legg til review i DOM uten reload ──────────────── */
function addReviewToList(review) {
  const list = document.getElementById('reviews-list');

  // Fjern "ingen vurderinger"-melding hvis den finnes
  const empty = document.querySelector('.empty-reviews');
  if (empty) empty.remove();

  // Lag reviews-list hvis den ikke finnes
  let container = list;
  if (!container) {
    container = document.createElement('div');
    container.className = 'reviews-list';
    container.id = 'reviews-list';
    document.querySelector('.reviews-section').appendChild(container);
  }

  const filled = '★'.repeat(review.stars);
  const empty_s = '★'.repeat(5 - review.stars);

  const card = document.createElement('div');
  card.className = 'review-card';
  card.style.animationDelay = '0s';
  card.innerHTML = `
    <div class="review-top">
      <div class="review-stars">
        <span class="filled">${filled}</span><span class="empty">${empty_s}</span>
      </div>
      <span class="review-date">${review.date}</span>
    </div>
    ${review.pros ? `<div class="review-row pros"><span class="pill pros-pill">✅ Bra</span><p>${escHtml(review.pros)}</p></div>` : ''}
    ${review.cons ? `<div class="review-row cons"><span class="pill cons-pill">❌ Minus</span><p>${escHtml(review.cons)}</p></div>` : ''}
  `;

  container.prepend(card);
}

/* ─── Oppdater snittrating i hero ────────────────────── */
function updateHeroRating(avg, count) {
  const avgEl = document.querySelector('.avg-big');
  const metaEl = document.querySelector('.review-meta');
  const starsEl = document.querySelector('.hero-rating .stars-display');

  if (avgEl) avgEl.textContent = avg > 0 ? avg.toFixed(1) : '–';
  if (metaEl) metaEl.textContent = `${count} ${count === 1 ? 'vurdering' : 'vurderinger'}`;
  if (starsEl) {
    const rounded = Math.round(avg);
    starsEl.innerHTML = [...Array(5)].map((_, i) =>
      `<span class="${i < rounded ? 'filled' : 'empty'}">★</span>`
    ).join('');
  }
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}