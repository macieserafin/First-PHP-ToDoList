document.addEventListener('DOMContentLoaded', function () {
    const feedbackDiv = document.getElementById('feedback'); // Element z komunikatem
    const closeFeedbackBtn = document.getElementById('close-feedback'); // Przycisk zamknięcia

    // Nasłuchiwanie kliknięcia w przycisk zamknięcia
    if (closeFeedbackBtn && feedbackDiv) {
        closeFeedbackBtn.addEventListener('click', function () {
            feedbackDiv.classList.add('hidden'); // Dodanie klasy "hidden", aby ukryć feedback
        });
    }

    // Sprawdzenie, czy istnieje treść w elemencie `#feedback`
    if (feedbackDiv && feedbackDiv.textContent.trim() === '') {
        feedbackDiv.classList.add('hidden'); // Ukrycie feedback (brak zawartości)
    }
});
