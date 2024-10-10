<?php
// تنظیمات و توابع برای آپلود و لیست کردن فایل‌ها و پوشه‌ها

// مسیر پایه (حداکثر پوشه‌ای که می‌توان به آن دسترسی داشت)
$baseDir = __DIR__;

// گرفتن پارامتر dir و تعیین مسیر پوشه
$dir = isset($_GET['dir']) ? realpath($baseDir.'/'.$_GET['dir']) : $baseDir;

// اطمینان از اینکه کاربر نمی‌تواند به بیرون از مسیر baseDir برود
if (strpos($dir, $baseDir) !== 0) {
    $dir = $baseDir; // اگر مسیر خارج از محدوده باشد، به پوشه اصلی بازگردیم
}

$dir = rtrim($dir, '/').'/'; // اطمینان از داشتن / در انتهای پوشه

// لیست کردن فایل‌ها و پوشه‌ها از پوشه مشخص شده
$items = array_diff(scandir($dir), ['.', '..']); // حذف . و .. از لیست

// مدیریت آپلود فایل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = $dir;
    $uploadFile = $uploadDir.basename($_FILES['file']['name']);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo 'File has been uploaded successfully.';
    } else {
        echo 'Failed to upload file.';
    }
}

// حذف فایل در صورت ارسال درخواست حذف
if (isset($_GET['delete'])) {
    $fileToDelete = $dir.basename($_GET['delete']);
    if (is_file($fileToDelete)) {
        unlink($fileToDelete);
        header("Location: ?dir=".urlencode(str_replace($baseDir, '', $dir)));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload and List</title>
    <link rel="icon" href="https://www.directorylister.com/images/favicon.png">

    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">

    <style>
        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background-color: #1e1e1e; /* پس‌زمینه تیره */
            color: #f0f0f0; /* متن روشن */
        }

        h1 {
            text-align: center;
        }

        /* افکت هاور (زمانی که موس روی لینک قرار می‌گیرد) */
        a:hover {
            color: #ff6347; /* تغییر رنگ به نارنجی هنگام هاور */
            border-bottom: 2px solid #ff6347; /* خط زیر لینک نارنجی می‌شود */
        }

        /* استایل لینک‌های غیرفعال (در صورت نیاز) */
        a:visited {
            color: #6c757d; /* رنگ خاکستری برای لینک‌هایی که بازدید شده‌اند */
        }

        #file-list {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        #file-list th, #file-list td {
            padding: 10px;
            border: 1px solid #4caf50;
        }

        #file-list th {
            background-color: #4caf50;
        }

        .dropzone {
            border: 2px dashed #4caf50; /* رنگ سبز برای حاشیه */
            border-radius: 5px;
            height: 100px; /* ارتفاع مشخص */
            background-color: #333; /* پس‌زمینه تیره برای Dropzone */
            color: #f0f0f0; /* متن روشن */
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .dropzone .dz-message {
            font-size: 16px;
            color: #4caf50; /* رنگ سبز برای متن */
        }

        .dropzone:hover {
            background-color: #444; /* تغییر رنگ پس‌زمینه هنگام هاور */
        }

        /* استایل لینک‌ها برای تم دارک */
        a {
            text-decoration: none;
            color: #4caf50; /* رنگ ثابت سبز برای لینک‌ها */
            border-bottom: 2px solid transparent;
            padding-bottom: 3px;
            transition: all 0.3s ease-in-out;
        }

        a:hover {
            color: #ff6347; /* تغییر رنگ به نارنجی هنگام هاور */
            border-bottom: 2px solid #ff6347; /* خط زیر لینک نارنجی می‌شود */
        }

        a:visited {
            color: #a1a1a1; /* رنگ خاکستری برای لینک‌هایی که بازدید شده‌اند */
        }
    </style>
</head>
<body>

<h1>File Upload and List</h1>

<!-- Dropzone for file upload -->
<form action="?dir=<?php echo urlencode(str_replace($baseDir, '', $dir)); ?>" class="dropzone" id="file-upload-zone" method="POST" enctype="multipart/form-data">
    <div class="dz-message">
        Drag & Drop files here or click to upload
    </div>
</form>

<!-- Back link to parent directory -->
<?php if ($dir !== $baseDir): ?>
    <p><a href="?dir=<?php echo urlencode(str_replace($baseDir, '', dirname($dir))); ?>">Back to Parent Directory</a></p>
<?php endif; ?>

<!-- Table for listing files and directories -->
<table id="file-list">
    <thead>
    <tr>
        <th>Name</th>
        <th>Size</th>
        <!-- <th>Action</th> -->
    </tr>
    </thead>
    <tbody id="file-table-body">
    <?php foreach ($items as $item): ?>
        <?php
        $itemPath = $dir.$item;
        $relativePath = str_replace($baseDir, '', $itemPath);
        if (is_dir($itemPath)): ?>
            <tr>
                <td><a href="?dir=<?php echo urlencode($relativePath); ?>"><?php echo htmlspecialchars($item); ?></a></td>
                <td>--</td>
                <!-- <td>--</td> -->
            </tr>
        <?php elseif (is_file($itemPath)): ?>
            <tr>
                <td><a href="<?php echo htmlspecialchars($relativePath); ?>" download><?php echo htmlspecialchars($item); ?></a></td>
                <td><?php echo number_format(filesize($itemPath) / 1024 / 1024, 2).' MB'; ?></td>
                <!-- <td><a href="?dir=<?php echo
                urlencode(str_replace($baseDir, '', $dir));
                ?>&delete=<?php echo urlencode($item); ?>"
                        onclick="return confirm('Are you
                        sure?')">Delete</a></td> -->
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Dropzone.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script>
    // Dropzone configuration
    Dropzone.options.fileUploadZone = {
        paramName: 'file', // The name that will be used to transfer the file
        maxFilesize: 200, // MB
        success: function (file, response) {
            window.location.reload(); // Reload the page after a successful upload
        }
    };
</script>

</body>
</html>
