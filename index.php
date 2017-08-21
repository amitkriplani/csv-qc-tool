<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);
set_time_limit(300);
ini_set('memory_limit', '128M');

$csvDirPath = './csv/';

if (!is_writable($csvDirPath)) {
    trigger_error("Could not open csv folder", E_USER_ERROR);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>CSV QC Tool</title>
    </head>
    <body>
        <div id="form-container">
            <form action="" method="post" enctype="multipart/form-data">
                <label>
                    CSV File :
                    <input type="file" required="1" name="CSV_File" accept=".csv" />
                </label>
                <label>
                    <input type="submit" name="submit" value="Submit" />
                </label>
            </form>
        </div>
        <?php
        if (!empty($_FILES['CSV_File'])) :
            $fileName = $csvDirPath . time() . '-' . $_FILES['CSV_File']['name'];
            move_uploaded_file($_FILES['CSV_File']['tmp_name'], $fileName);
            ?>
            <iframe src="loadCSV.php?csv=<?php echo $fileName ?>" width="100%" height="800" />
        <?php endif; ?>
    </body>
</html>