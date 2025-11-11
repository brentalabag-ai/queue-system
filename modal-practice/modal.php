<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Example</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <main>
        <h1 style="margin-bottom: 2rem;">Modal Example</h1>
        <button 
            id="openModalBtn" 
            class="btn"
        >
            Open Modal
        </button>
    </main>

    <!-- Modal Backdrop -->
    <div id="modalBackdrop" class="modal-backdrop" style="display: none;">
        <!-- Modal -->
        <div id="modal" class="modal" role="dialog">
            <!-- Modal Header -->
            <header class="modal-header">
                <button 
                    id="closeModalBtn" 
                    class="close-btn" 
                    aria-label="close"
                >
                </button>
            </header>
            
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Modal Title -->
                <p class="modal-title">
                    Modal Header
                </p>
                <!-- Modal Description -->
                <p class="modal-description">
                    Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nostrum et
                    eligendi repudiandae voluptatem tempore!
                </p>
            </div>
            
            <!-- Modal Footer -->
            <footer class="modal-footer">
                <button 
                    id="cancelBtn" 
                    class="footer-btn cancel-btn"
                >
                    Cancel
                </button>
                <button 
                    id="acceptBtn" 
                    class="footer-btn accept-btn"
                >
                    Accept
                </button>
            </footer>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>