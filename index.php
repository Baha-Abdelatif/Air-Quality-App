<?php
require_once __DIR__ . "/views/header.html";
require_once __DIR__ . "/inc/functions.php";
$cities = json_decode(
  file_get_contents(__DIR__ . '/data/index.json'),
  true
);
// $data = json_decode(
//   file_get_contents('compress.bzip2://' . __DIR__ . "/data/cancun.json.bz2"),
//   true,
// );
?>
<main>
  <ul>
    <?php foreach ($cities as $city) : ?>
      <li>
        <a href="/pages/city.php?<?php echo http_build_query(['city' => $city['city'], 'country' => $city['country']]) ?>">
          <span class="<?php e("fi fi-" . $city["flag"]) ?>"></span>
          <span> - <?php e($city["city"]) ?>, </span>
          <span><?php e($city["country"]) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</main>
<?php
require_once __DIR__ . "/views/footer.html";
?>
