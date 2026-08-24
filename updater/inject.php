<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Updater</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
    <?php
    // replace front.php
    @unlink('../../routes/web.php');
    @copy("web.php", '../../routes/web.php');

    // place UpdaterController.php
    @unlink('../../app/Http/Controllers/UpdateController.php');
    @copy("UpdateController.php", '../../app/Http/Controllers/UpdateController.php');
    ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 pt-5">
                <div class="card">
                    <form action="../update/version">
                        <div class="card-body">
                            <div class="alert alert-success">
                                Updater codes has been injected
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-success" type="submit">Upgrade to 4.0</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
