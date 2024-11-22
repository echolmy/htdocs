<?php include_once("header.php")?>

<div class="container my-5">

<?php

// This function takes the form data and adds the new auction to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */
// 连接到 MySQL 数据库
$host = '127.0.0.1';
$db = 'auction_system';
$user = 'root'; // 数据库用户名
$pass = ''; // 数据库密码
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */


/* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
         
// 检查表单是否提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $title = $_POST['auctionTitle'] ?? '';
    $details = $_POST['auctionDetails'] ?? '';
    $category = $_POST['auctionCategory'] ?? '';
    $starting_price = $_POST['auctionStartPrice'] ?? 0;
    $reserve_price = $_POST['auctionReservePrice'] ?? 0;
    $end_date = $_POST['auctionEndDate'] ?? '';

    // 验证表单数据
    if (empty($title) || empty($details) || empty($category) || empty($starting_price) || empty($end_date)) {
        echo '<div class="text-danger text-center">Error: Please fill out all required fields!</div>';
    } else {
        // 插入数据到数据库
        $sql = "INSERT INTO auction_item 
                (title, description, conditions, img_url, starting_price, reserve_price, current_price, start_date, end_date, status, current_winnerid) 
                VALUES 
                (:title, :details, :conditions, :img_url, :starting_price, :reserve_price, :current_price, :start_date, :end_date, :status, :current_winnerid)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'details' => $details,
            'conditions' => $category, // 临时使用 category 替代 conditions
            'img_url' => 'placeholder.jpg', // 图片上传功能未实现
            'starting_price' => $starting_price,
            'reserve_price' => $reserve_price,
            'current_price' => $starting_price,
            'start_date' => date('Y-m-d H:i:s'), // 当前时间作为开始时间
            'end_date' => $end_date,
            'status' => 'Active',
            'current_winnerid' => 0,
        ]);

        echo '<div class="text-center">Auction successfully created! <a href="browse.php">View your new listing.</a></div>';
    }
}   

// If all is successful, let user know.
// echo('<div class="text-center">Auction successfully created! <a href="FIXME">View your new listing.</a></div>');



?>

</div>


<?php include_once("footer.php")?>