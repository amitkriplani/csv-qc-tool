<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
set_time_limit(300);
ini_set('memory_limit', '128M');

if (empty($_GET['csv'])) {
    trigger_error("No file specified.", E_USER_ERROR);
}

$csv = $_GET['csv'];

if (!file_exists($csv) || !is_readable($csv)) {
    trigger_error("Could not open file", E_USER_ERROR);
}

$file = fopen($csv, 'r+');

$headers = fgetcsv($file);

if (empty($headers)) {
    trigger_error("Empty CSV file", E_USER_ERROR);
}

$show = "mapping";

if (!empty($_POST)) {
    foreach ($headers as $header) {
        if (empty($_POST['widget'][$header])) {
            trigger_error("Undefined widget for $header", E_USER_ERROR);
        }
    }
    $show = "data";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>
            CSV QC Tool
        </title>
        <meta charset="UTF-8">
    </head>
    <body>
        <?php if ($show == "mapping"): ?>
            <div class="form-container">
                <form action="" method="post">
                    <?php foreach ($headers as $header): ?>
                        <div class="row">
                            <label>
                                <?php echo $header; ?> :
                                <select name="widget[<?php echo $header ?>]">
                                    <option value="text">Text</option>
                                    <option value="url">URL</option>
                                    <option value="image">Image</option>
                                </select>
                                Required : <input type="checkbox" name="required[<?php echo $header ?>]" />
                                Group : <input type="checkbox" name="group[<?php echo $header ?>]" />
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <input type="submit" name="submit" value="submit" />
                </form>
            </div>
        <?php endif; ?>
        <?php if ($show == "data"): ?>
            <div class="data-container">
                <table>
                    <tbody>
                        <?php while ($row = fgetcsv($file)): ?>
							<?php
								if (empty($done)) $done = [];
								foreach ($_POST['group'] as $key => $value) {
									if (empty($done[$key])) $done[$key] = [];
									if (!empty($done[$key][$row[$key]])) continue;
									$done[$key][$row[$key]] = $row[$key];
								}
								foreach ($row as $key => $cell) :
									if (empty($_POST['required'][$headers[$key]])) continue;
							?>
                                <tr>
                                    <td>
                                        <?php echo $headers[$key]; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($cell)) : ?>
                                            <span style="color: <?php echo (!empty($_POST['required'][$headers[$key]])) ? 'red' : 'green' ?>">MISSING VALUE!</span>
                                            <?php
                                        else:
                                            switch ($_POST['widget'][$headers[$key]]):
                                                case "image":
                                                    ?>
                                                    <img style="max-width:500px;max-height:500px;width:auto;height:auto;display:block;" src="<?php echo $cell; ?>" style="color:red;" alt="<?php echo $cell ?>" />
                                                    <?php
                                                    break;
                                                case "url":
                                                    ?>
                                                    <a style="color:<?php echo (filter_var($cell, FILTER_VALIDATE_URL)) ? 'green' : 'red' ?>" href="<?php echo $cell ?>"><?php echo $cell; ?></a>
                                                    <?php
                                                    break;
                                                case "text":
                                                    ?>
                                                    <?php echo utf8_encode($cell) ?>
                                                    <?php
                                                    break;
                                            endswitch;
                                        endif;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td><hr /></td>
                                <td><hr /></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </body>
</html>
