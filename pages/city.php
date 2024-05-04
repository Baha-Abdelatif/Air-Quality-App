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
  $labels = array_keys($stats);
  sort($labels);
  $pm25 = [];
  $pm10 = [];
  foreach ($labels as $label) {
    $measurements = $stats[$label];
    if (count($measurements['pm10']) > 0) {
      $pm10[] = round(array_sum($measurements['pm10']) / count($measurements['pm10']), 2);
    }
    if (count($measurements['pm25']) > 0) {
      $pm25[] = round(array_sum($measurements['pm25']) / count($measurements['pm25']), 2);
    }
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
    <div id="aqi-chart">
      <canvas>Please enable Javascript & be sure your browser is up to date.</canvas>
    </div>
    <script src="../scripts/chart.umd.js"></script>
    <script>
      Chart.defaults.scales.linear.min = 0;
      document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.querySelector('#aqi-chart canvas');
        const labels = <?php echo json_encode($labels); ?>;
        const data = {
          labels: labels,
          datasets: [{
              label: 'pm25 µg/m³',
              data: <?php echo json_encode($pm25); ?>,
              fill: true,
              borderColor: 'rgb(75, 192, 192)',
              tension: 0.1
            },
            {
              label: 'pm10 µg/m³',
              data: <?php echo json_encode($pm10); ?>,
              fill: true,
              borderColor: 'rgb(192, 75, 192)',
              tension: 0.1
            }
          ],
          scales: {
            y: {
              suggestedMin: 0,
            }
          }
        };
        const chart = new Chart(ctx, {
          type: 'line',
          data: data,
        });
      })
    </script>
  <?php endif; ?>
</main>

<?php
require_once __DIR__ . "/../views/footer.html";
?>
