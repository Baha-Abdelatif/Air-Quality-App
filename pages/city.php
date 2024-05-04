<?php
require_once __DIR__ . "/../views/header.html";
require_once __DIR__ . "/../inc/functions.php";
$filename = null;
$results = null;
if (!empty($_GET['city']) && !empty($_GET['country'])) {
  // Load & prepare the datas.
  $city = $_GET['city'];
  $country = $_GET['country'];
  $flag = "";
  $cities = json_decode(file_get_contents(__DIR__ . '/../data/index.json'), true);
  foreach ($cities as $citydata) {
    if ($citydata["city"] === $city && $citydata["country"] === $country) {
      $filename = $citydata["filename"];
      $flag = $citydata["flag"];
    }
  }
} else {
  $city = null;
  $country = null;
}
if (!empty($filename)) {
  $results = json_decode(
    file_get_contents('compress.bzip2://' . __DIR__ . '/../data/' . $filename),
    true,
  )['results'];

  $stats = [];
  $units = [
    'pm25' => null,
    'pm10' => null,
  ];

  foreach ($results as $result) {
    if (!empty($units['pm25']) && !empty($units['pm10'])) break;
    if ($result['parameter'] === 'pm25') $units['pm25'] = $result['unit'];
    if ($result['parameter'] === 'pm10') $units['pm10'] = $result['unit'];
  }

  foreach ($results as $result) {
    if ($result['parameter'] !== 'pm25' && $result['parameter'] !== 'pm10') continue;
    if ($result['value'] < 0) continue;
    $month = substr($result['date']['local'], 0, 7);
    if (!isset($stats[$month])) $stats[$month] = ['pm25' => [], 'pm10' => []];
    $stats[$month][$result['parameter']][] = $result['value'];
  }
}
?>

<main>
  <?php if (empty($city) || empty($country) || empty($filename) || empty($results) || empty($stats)) : ?>
    <div>
      <h2>The city datas could not be loaded.</h2>
      <a href="/"><- Back to the homepage.</a>
    </div>
  <?php else : ?>
    <h2>
      <span class="<?php e("fi fi-" . $flag) ?>"></span>
      <span> - <?php e($city) ?>, </span>
      <span><?php e($country) ?>:</span>
    </h2>
    <div>
      <table>
        <thead>
          <tr>
            <th>Month</th>
            <th>pm25</th>
            <th>pm10</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($stats as $month => $measurements) : ?>
            <tr>
              <th><?php e($month); ?></th>
              <td><?php e(round(array_sum($measurements['pm25']) / count($measurements['pm25']), 2) . ' ' . $units['pm25']); ?> </td>
              <td><?php e(round(array_sum($measurements['pm10']) / count($measurements['pm10']), 2) . ' ' . $units['pm10']); ?> </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th>Month</th>
            <th>pm25</th>
            <th>pm10</th>
          </tr>
        </tfoot>
      </table>
      <!-- Fetched datas here -->
    </div>
  <?php endif; ?>
</main>

<?php
require_once __DIR__ . "/../views/footer.html";
?>
