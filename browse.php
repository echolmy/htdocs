<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
// 数据库连接代码
$host = '127.0.0.1';
$db = 'auction_system';
$user = 'root';
$pass = '';
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
?>

<div class="container">

<h2 class="my-3">Browse listings</h2>

<div id="searchSpecs">
<!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
<form method="get" action="browse.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
	    <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" id="keyword" placeholder="Search for anything">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat">
          <option selected value="all">All categories</option>
          <option value="fill">Fill me in</option>
          <option value="with">with options</option>
          <option value="populated">populated from a database?</option>
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" id="order_by">
          <option selected value="pricelow">Price (low to high)</option>
          <option value="pricehigh">Price (high to low)</option>
          <option value="date">Soonest expiry</option>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> <!-- end search specs bar -->


</div>

<?php
  // Retrieve these from the URL
  $keyword = $_GET['keyword'] ?? '';
  $category = $_GET['cat'] ?? 'all';
  $ordering = $_GET['order_by'] ?? 'pricelow';
  $curr_page = $_GET['page'] ?? 1;

  // 构造 SQL 查询
  $query = "SELECT * FROM auction_item WHERE status = 'Active'";

  if (!isset($_GET['keyword'])) {
    // TODO: Define behavior if a keyword has not been specified.
    $keyword = ''; 
  }
  else {
    $keyword = $_GET['keyword'];
  }

  if (!isset($_GET['cat'])) {
    // TODO: Define behavior if a category has not been specified.
    $category = 'all';
  }
  else {
    $category = $_GET['cat'];
  }
  
  if (!isset($_GET['order_by'])) {
    // TODO: Define behavior if an order_by value has not been specified.
    $ordering = 'pricelow';
  }
  else {
    $ordering = $_GET['order_by'];
  }
  
  if (!isset($_GET['page'])) {
    $curr_page = 1;
  }
  else {
    $curr_page = $_GET['page'];
  }

  /* TODO: Use above values to construct a query. Use this query to 
     retrieve data from the database. (If there is no form data entered,
     decide on appropriate default value/default query to make. */
     $query = "SELECT * FROM auction_item WHERE status = 'Active'";
  // 如果有关键词
  if (!empty($keyword)) {
    $query .= " AND (title LIKE :keyword OR description LIKE :keyword)";
  }

  // 如果有分类
  if ($category !== 'all') {
    $query .= " AND conditions = :category";
  }

  // 排序逻辑
  switch ($ordering) {
    case 'pricelow':
        $query .= " ORDER BY current_price ASC";
        break;
    case 'pricehigh':
        $query .= " ORDER BY current_price DESC";
        break;
    case 'date':
        $query .= " ORDER BY end_date ASC";
        break;
  }

  // 分页逻辑
  $offset = ($curr_page - 1) * $results_per_page;
  $query .= " LIMIT :limit OFFSET :offset";

  // 准备 SQL 语句
  $stmt = $pdo->prepare($query);

  // 绑定参数
  if (!empty($keyword)) {
    $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
  }
  if ($category !== 'all') {
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
  }
  $stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

  // 执行查询
  $stmt->execute();

  // 获取查询结果
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 获取总结果数（用于分页）
  $total_query = "SELECT COUNT(*) FROM auction_item WHERE status = 'Active'";
  if (!empty($keyword)) {
    $total_query .= " AND (title LIKE :keyword OR description LIKE :keyword)";
  }
  if ($category !== 'all') {
    $total_query .= " AND conditions = :category";
  }
  $total_stmt = $pdo->prepare($total_query);
  if (!empty($keyword)) {
    $total_stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
  }
  if ($category !== 'all') {
    $total_stmt->bindValue(':category', $category, PDO::PARAM_STR);
  }
  $total_stmt->execute();


  /* For the purposes of pagination, it would also be helpful to know the
     total number of results that satisfy the above query */
  // $num_results = 96; // TODO: Calculate me for real
  $num_results = $total_stmt->fetchColumn();
  $results_per_page = 10;
  $max_page = ceil($num_results / $results_per_page);
?>

<div class="container mt-5">

<!-- TODO: If result set is empty, print an informative message. Otherwise... -->
 
<?php
// 如果没有结果，显示提示信息
if (empty($results)) {
    echo '<p class="text-center">No auctions found matching your criteria.</p>';
} else {
    echo '<ul class="list-group">';
    foreach ($results as $item) {
        $item_id = $item['auctionid'];
        $title = $item['title'];
        $description = $item['description'];
        $current_price = $item['current_price'];
        $num_bids = 0; // 如果有竞标表，可以动态统计竞标数量
        $end_date = new DateTime($item['end_date']);
        
        // 使用 utilities.php 中的函数打印 HTML
        print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
    }
    echo '</ul>';
}
?>


<!-- TODO: Use a while loop to print a list item for each auction listing
     retrieved from the query -->

<!-- <?php
  // Demonstration of what listings will look like using dummy data.
  $item_id = "87021";
  $title = "Dummy title";
  $description = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum eget rutrum ipsum. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus feugiat, ipsum vel egestas elementum, sem mi vestibulum eros, et facilisis dui nisi eget metus. In non elit felis. Ut lacus sem, pulvinar ultricies pretium sed, viverra ac sapien. Vivamus condimentum aliquam rutrum. Phasellus iaculis faucibus pellentesque. Sed sem urna, maximus vitae cursus id, malesuada nec lectus. Vestibulum scelerisque vulputate elit ut laoreet. Praesent vitae orci sed metus varius posuere sagittis non mi.";
  $current_price = 30;
  $num_bids = 1;
  $end_date = new DateTime('2020-09-16T11:00:00');
  
  // This uses a function defined in utilities.php
  print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
  
  $item_id = "516";
  $title = "Different title";
  $description = "Very short description.";
  $current_price = 13.50;
  $num_bids = 3;
  $end_date = new DateTime('2020-11-02T00:00:00');
  
  print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
?> -->

</ul>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">
  
<?php

  // Copy any currently-set GET variables to the URL.
  $querystring = "";
  foreach ($_GET as $key => $value) {
    if ($key != "page") {
      $querystring .= "$key=$value&amp;";
    }
  }
  
  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);
  
  if ($curr_page != 1) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }
    
  for ($i = $low_page; $i <= $high_page; $i++) {
    if ($i == $curr_page) {
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }
    
    // Do this in any case
    echo('
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }
  
  if ($curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>


</div>



<?php include_once("footer.php")?>