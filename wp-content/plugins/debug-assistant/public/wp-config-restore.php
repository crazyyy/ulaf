<?php
  $tempDir = $_SERVER["DOCUMENT_ROOT"] . "/wp-content/plugins/debug-assistant/backup-config/";
  $searchVersion = scandir($tempDir, 1);

  $searchedFile  = "imlt-wp-config-" . $_GET['imltKey'] . ".php";
  $imlt_restore_msg = "";

    if ( isset($_POST['imlt-restore']) )
    {
          if ( in_array($searchedFile, $searchVersion ) )
          {
            $templFile =  $_SERVER["DOCUMENT_ROOT"] . "/wp-content/plugins/debug-assistant/backup-config/" . $searchedFile;
            $templStringFile = file_get_contents($templFile);

            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php", $templStringFile);
            $imlt_class_restr_button = "imlt-class-hide-form";
              $imlt_restore_msg = "<div class='alert alert-success'>You restore with this version created in " . date("F d Y H:i:s", $_GET['imltKey']) . "</div>";
          }
    }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="/wp-content/plugins/wp-assistant/assets/css/imlt-style.css">
  <title>WP Assistant Restore file</title>

</head>

<body>
  <div class="jumbotron">
  <h3>Restore wp-config file</h3>
  </div>
  <div class="container-fluid">
  <?php echo $imlt_restore_msg; ?>
  <form class="<?php echo $imlt_class_restr_button; ?>" method="post" action="">
  <input class="imlt-restore-button btn btn-success" type="submit" name="imlt-restore" value="Restore to previous version" />
</form>
</div>

</body>
</html>
