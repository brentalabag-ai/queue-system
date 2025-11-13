document.addEventListener('DOMContentLoaded', function() {

    // Payment Modal Handler
    var paymentModal = document.getElementById('paymentModal');
    paymentModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var queueId = button.getAttribute('data-queue-id');
        var studentName = button.getAttribute('data-student-name');
            
        document.getElementById('modalQueueId').value = queueId;
        document.getElementById('modalStudentName').value = studentName;
    });

    // Auto-refresh every 15 seconds
    setTimeout(function() {
        location.reload();
    }, 15000);
});