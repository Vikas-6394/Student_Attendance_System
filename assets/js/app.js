// Theme toggle could be server-side; keep minimal client UX polish.
// Smooth small animations for cards can be added via CSS transitions.
document.addEventListener('click', (e) => {
  if (e.target.matches('[data-copy]')) {
    navigator.clipboard.writeText(e.target.getAttribute('data-copy'));
    alert('Copied!');
  }
});