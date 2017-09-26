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
    $headers['image'] = 'Images';
    $_POST['widget']['Images'] = 'image';
    foreach ($headers as $header) {
        if (empty($_POST['widget'][$header])) {
            trigger_error("Undefined widget for $header", E_USER_ERROR);
        }
    }
    $show = "data";
    $data = [];
    while ($row = fgetcsv($file)) {
        if (!empty($_POST['group'])) {
            if (empty($done)) {
                $done = [];
            }
            foreach ($_POST['group'] as $key => $on) {
                if (empty($done[$key])) {
                    $done[$key] = [];
                }
                if (empty($done[$key][$row[array_search($key, $headers)]])) {
                    $done[$key][$row[array_search($key, $headers)]] = $row[array_search($key, $headers)];
                } else {
                    continue 2;
                }
            }
        }
        $rowX = [];
        $rowX['image'] = [];
        foreach ($row as $key => $cell) {
            if (empty($_POST['required'][$headers[$key]])) {
                continue;
            } elseif ($_POST['widget'][$headers[$key]] == 'image') {
                $rowX['image'][] = $cell;
            } else {
                $rowX[] = $cell;
            }
        }
        if ($rowX) {
            $data[] = $rowX;
        }
    }
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
                                    <option <?php echo (stripos($header, 'image') !== false) ? 'selected' : ''; ?> value="image">Image</option>
                                </select>
                                Required : <input <?php echo (stripos($header, 'image') !== false || stripos($header, 'name') !== false) ? 'checked' : ''; ?> type="checkbox" name="required[<?php echo $header ?>]" />
                                Group : <input <?php echo (stripos($header, 'name') !== false) ? 'checked' : ''; ?> type="checkbox" name="group[<?php echo $header ?>]" />
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
                        <?php foreach ($data as $row) : ?>
                            <?php foreach ($row as $key => $cell) : ?>
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
                                                    <div>
                                                        <?php foreach ($cell as $image) : ?>
                                                            <img style="max-width:350px;max-height:350px;width:auto;height:auto;display:block;float: left;" src="<?php echo $image; ?>" style="color:red;" alt="<?php echo $image ?>" />
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php
                                                    break;
                                                case "url":
                                                    ?>
                                                    <a style="color:<?php echo (filter_var($cell, FILTER_VALIDATE_URL)) ? 'green' : 'red' ?>" href="<?php echo $cell ?>"><?php echo $cell; ?></a>
                                                    <?php
                                                    break;
                                                case "text":
                                                    echo utf8_encode($cell);
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </body>
</html>
