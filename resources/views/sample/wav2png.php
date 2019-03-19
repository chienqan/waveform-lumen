
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>wav2png</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        .b-hide {
            display: none;
        }
    </style>
</head>

<body>

<header>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Sample</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="/dev/samples/wav2png">Wav2png</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dev/samples/primitive">Primitive</a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<main role="main" class="container">
    <section class="mt-5">
        <form id="form-upload">
            <div class="form-group row">
                <label for="fft-size" class="col-sm-2 col-form-label">File</label>
                <div class="col-sm-10">
                    <div class="custom-file">
                        <input type="file" name="media" class="custom-file-input" id="customFile">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <a data-toggle="collapse" data-target="#options" href="javascript:void(0)">Show Options</a>
            </div>
            <div id="options" class="collapse">
                <div class="form-group row">
                    <label for="height" class="col-sm-2 col-form-label">Height</label>
                    <div class="col-sm-10">
                        <input type="text" name="h" class="form-control" id="height" placeholder="image height in pixels (default 51)">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="height" class="col-sm-2 col-form-label">Width</label>
                    <div class="col-sm-10">
                        <input type="text" name="w" class="form-control" id="width" placeholder="image width in pixels (default 800)">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fft-size" class="col-sm-2 col-form-label">FFT Size</label>
                    <div class="col-sm-10">
                        <input type="text" name="f" class="form-control" id="fft-size" placeholder="fft size, power of 2 for increased performance (default 2048)">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fft-size" class="col-sm-2 col-form-label">Color Scheme</label>
                    <div class="col-sm-10">
                        <input type="text" name="c" class="form-control" id="color-scheme" placeholder="name of the color scheme to use (one of: 'Freesound2' (default), 'FreesoundBeastWhoosh', 'Cyberpunk', 'Rainforest')">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
            <div class="alert alert-success b-hide" role="alert"></div>
            <div class="alert alert-danger b-hide" role="alert"></div>
            <div class="alert alert-info b-hide" role="alert"></div>
        </form>

    </section>
</main>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script>
    const $browse = $('.custom-file-input');
    const $label = $('.custom-file-label');
    const $form = $('#form-upload');
    const $file = $('input[type="file"]');

    const handleFileUploadChange = () => {
        $label.text($file[0].files[0].name);
    };

    const base = () => {
        const href = window.location.href;
        const url = href.replace('/samples/wav2png', '').replace('/samples/primitive', '');
        return url;
    };

    const hideAllAlert = () => {
        $('.alert').hide();
    };

    const infoMessage = (message) => {
        hideAllAlert();
        const $alert = $('.alert-info');
        $alert.show();
        $alert.text(message);
    };

    const successMessage = (message) => {
        hideAllAlert();
        const $alert = $('.alert-success');
        $alert.show();
        $alert.html(message);
    };

    const errorMessage = (message) => {
        hideAllAlert();
        const $alert = $('.alert-danger');
        $alert.show();
        $alert.text(message);
    };

    const getPresignedUrl = filename => {
        return $.ajax({
            url: base() + '/s3/presigned',
            data: {
                filename: filename
            },
        });
    };

    const uploadFileToS3 = (presignedUrl, file) => {
        return $.ajax({
            url: presignedUrl,
            method: 'PUT',
            contentType: 'binary/octet-stream',
            processData: false,
            data: file,
        });
    };

    const handleFormSubmit = async (e) => {
        e.preventDefault();

        const file = $file[0].files[0];
        const fileName = $label.text();

        // Show Info Message
        infoMessage('Getting presigned url (1/3)');

        // Get presigned url
        let presignedUrl = null;
        try {
            const response = await getPresignedUrl(fileName);
            presignedUrl = response.url;
        } catch (e) {
            errorMessage(e.message);
        }

        // Show Info Message
        infoMessage('Uploading file into s3 (2/3)');

        // Process upload file into s3
        try {
            await uploadFileToS3(presignedUrl, file);
        } catch (e) {
            errorMessage(e.message);
        }

        // Show Info Message
        infoMessage('Converting audio file to wave image (3/3)');

        // send file name to api to process image
        $.ajax({
            url: base() + '/transforms/wav2png',
            method: 'POST',
            data: {
                file: fileName,
                h: $('#height').val(),
                w: $('#width').val(),
                f: $('#fft-size').val(),
                c: $('#color-scheme').val()
            },
        }).done(response => {
            const result = response.result;
            if(result) {
                const message = `Successfully! click <a href="${response.link}">here</a> to download`;
                successMessage(message);
            } else {
                errorMessage(response.message);
            }
        }).fail(error => {
            errorMessage(error);
        });
    };

    $(function () {
        $form.on('submit', handleFormSubmit);
        $browse.on('change', handleFileUploadChange);
    })
</script>

</body>
</html>
