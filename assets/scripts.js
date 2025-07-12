document.addEventListener('DOMContentLoaded', () => {
  const refreshInterval = parseInt(document.body.dataset.refreshInterval || "0");
  const timerEl = document.getElementById('refresh-timer');
  if (!refreshInterval || !timerEl) return;

  let counter = refreshInterval;

  const intervalId = setInterval(() => {
    counter -= 1;
    timerEl.textContent = counter;
    if (counter <= 0) {
      clearInterval(intervalId);
      location.reload();
    }
  }, 1000);
});
