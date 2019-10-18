<?php
$time_start = microtime(true);
header('Content-Type: application/json');

require_once('../lib/config.php');

function takePicture($filename)
{
    global $config;

    if ($config['dev'] === false) {
        $dir = dirname($filename);
        chdir($dir); //gphoto must be executed in a dir with write permission
        $cmd = sprintf($config['take_picture']['cmd'], $filename);

        exec($cmd, $output, $returnValue);

        if ($returnValue) {
            die(json_encode([
                'error' => 'Gphoto returned with an error code',
                'cmd' => $cmd,
                'returnValue' => $returnValue,
                'output' => $output,
            ]));
        } elseif (!file_exists($filename)) {
            die(json_encode([
                'error' => 'File was not created',
                'cmd' => $cmd,
                'returnValue' => $returnValue,
                'output' => $output,
            ]));
        }
    } else {
        $demoFolder = __DIR__ . '/../resources/img/demo/';
        $devImg = array_diff(scandir($demoFolder), array('.', '..'));
        copy(
            $demoFolder . $devImg[array_rand($devImg)],
            $filename
        );
    }
}

if (!empty($_POST['file']) && preg_match('/^[a-z0-9_]+\.jpg$/', $_POST['file'])) {
    $file = $_POST['file'];
} elseif ($config['file_format_date']) {
    $file = date('Ymd_His').'.jpg';
} else {
    $file = md5(time()).'.jpg';
}

$filename_tmp = $config['foldersAbs']['tmp'] . DIRECTORY_SEPARATOR . $file;

if (!isset($_POST['style'])) {
    die(json_encode([
        'error' => 'No style provided'
    ]));
}

if ($_POST['style'] === 'photo') {
    takePicture($filename_tmp);
} elseif ($_POST['style'] === 'collage') {
    if (!is_numeric($_POST['collageNumber'])) {
        die(json_encode([
            'error' => 'No or invalid collage number provided',
        ]));
    }

    $number = $_POST['collageNumber'] + 0;

    if ($number > 3) {
        die(json_encode([
            'error' => 'Collage consists only of 4 pictures',
        ]));
    }

    $basename = substr($filename_tmp, 0, -4);
    $filename = $basename . '-' . $number . '.jpg';

    takePicture($filename);

    die(json_encode([
        'success' => 'collage',
        'file' => $file,
        'current' => $number,
        'limit' => 4,
    ]));
} else {
    die(json_encode([
        'error' => 'Invalid photo style provided',
    ]));
}

// send imagename to frontend
echo json_encode([
    'success' => 'image',
    'file' => $file,
    'time' => microtime(true) - $time_start,
]);
