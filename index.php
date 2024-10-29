<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XML Data Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Notification Styles */
        .notification {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            font-size: 16px;
            max-width: 300px;
            width: 100%;
            text-align: center;
        }

        .notification.show {
            display: block;
        }

        .notification button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <h2 class="mb-4">XML Data Management</h2>

                <div class="d-grid gap-2">
                    <a href="import.php" class="btn btn-primary btn-block">Import XML</a>
                    <a href="export.php" class="btn btn-secondary btn-block">Export XML</a>
                    <a href="xml.php" class="btn btn-secondary btn-block">XML</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Notification -->
    <div id="notification" class="notification">
        <span id="notification-message"></span>
        <!-- <button id="notification-close">&times;</button> -->
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const successMessage = urlParams.get('success_message');

            if (successMessage) {
                const notification = document.getElementById('notification');
                const messageSpan = document.getElementById('notification-message');
                messageSpan.textContent = decodeURIComponent(successMessage);
                notification.classList.add('show');

                // const closeButton = document.getElementById('notification-close');
                // closeButton.addEventListener('click', function () {
                //     notification.classList.remove('show');
                // });

                // Hide the notification after 5 seconds
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 3000);
            }
        });
    </script>

</body>

</html>