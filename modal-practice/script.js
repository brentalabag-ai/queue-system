document.addEventListener('DOMContentLoaded', function() {
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const acceptBtn = document.getElementById('acceptBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');

    // Open modal when button is clicked
    openModalBtn.addEventListener('click', function() {
        modalBackdrop.style.display = 'block';
    });

    // Close modal when the outside of the card is clicked
    closeModalBtn.addEventListener('click', function() {
        modalBackdrop.style.display = 'none';
    });

    // Close modal when Cancel button is clicked
    cancelBtn.addEventListener('click', function() {
        modalBackdrop.style.display = 'none';
    });

    // Accept button functionality
    acceptBtn.addEventListener('click', function() {
        alert('Accepted!');
        modalBackdrop.style.display = 'none';
    });

    // Close modal when clicking outside the modal
    modalBackdrop.addEventListener('click', function(e) {
        if (e.target === modalBackdrop) {
            modalBackdrop.style.display = 'none';
        }
    });
});