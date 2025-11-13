document.addEventListener('DOMContentLoaded', function() {
    const othersCheckbox = document.getElementById('others');
    const otherSpecify = document.getElementById('otherSpecify');
            
    // Handle "Others" checkbox
    othersCheckbox.addEventListener('change', function() {
        otherSpecify.disabled = !this.checked;
        if (this.checked) {
            otherSpecify.focus();
        } else {
            otherSpecify.value = '';
        }
    });

    // Form validation
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener('submit', function(e) {
        const amount = document.getElementById('amount').value;
        const checkboxes = document.querySelectorAll('.payment-type:checked');
                
        if (!amount || amount <= 0) {
            e.preventDefault();
            alert('Please enter a valid amount.');
            return;
        }
                
        if (checkboxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one payment type.');
            return;
        }
                
        // If others is checked but no specification
        if (othersCheckbox.checked && !otherSpecify.value.trim()) {
             e.preventDefault();
            alert('Please specify the other payment type.');
            return;
        }
    });
});