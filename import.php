<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import XML</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles for loading message */
        .loading-message {
            display: none;
            text-align: center;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        .loader {
            width: 50px;
            aspect-ratio: 1;
            --_c: no-repeat radial-gradient(farthest-side, #25b09b 92%, #0000);
            background:
                var(--_c) top,
                var(--_c) left,
                var(--_c) right,
                var(--_c) bottom;
            background-size: 12px 12px;
            animation: l7 1s infinite;
        }

        @keyframes l7 {
            to {
                transform: rotate(.5turn)
            }
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h3 class="mb-4 text-center" id="importTitle">Import XML Feed from Property Finder</h3>

                <div class="mb-3 text-left" id='backBtn'>
                    <a href="index.php" class="btn btn-sm btn-secondary">Back</a>
                </div>

                <!-- Form to upload XML file -->
                <form id="importForm" action="import-action.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="xmlFile">Choose XML File</label>
                        <input type="file" class="form-control-file" id="xmlFile" name="xmlFile" accept=".xml" required>
                        <small class="form-text text-muted">Please upload the XML feed file from Property Finder.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Upload XML</button>
                </form>

                <!-- Loading message -->
                <div id="loadingMessage" class="loading-message">
                    <h4>Loading... Please wait.</h4>
                    <p>You will be redirected in a few seconds.</p>

                    <div class="loader"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('importForm').addEventListener('submit', function() {
            // Show loading message
            document.getElementById('loadingMessage').style.display = 'flex';

            document.getElementById('importForm').style.display = 'none';
            document.getElementById('importTitle').style.display = 'none';
            document.getElementById('backBtn').style.display = 'none';
        });
    </script>

</body>

</html>