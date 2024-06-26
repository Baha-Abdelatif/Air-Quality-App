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
    } else {
      $pm10[] = 0;
    }
    if (count($measurements['pm25']) > 0) {
      $pm25[] = round(array_sum($measurements['pm25']) / count($measurements['pm25']), 2);
    } else {
      $pm25[] = 0;
    }
  }
  $datasets = [];
  if (count($measurements['pm10']) > 0) {
    $datasets[] = [
      "label" => "AQI, PM10 in {$units['pm10']}",
      "data" => $pm10,
      "borderColor" => 'rgb(192, 75, 192)',
    ];
  }
  if (count($measurements['pm25']) > 0) {
    $datasets[] = [
      "label" => "AQI, PM2.5 in {$units['pm25']}",
      "data" => $pm25,
      "borderColor" => 'rgb(75, 192, 192)',
    ];
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
          datasets: <?php echo json_encode($datasets); ?>,

        };
        const chart = new Chart(ctx, {
          type: 'line',
          data: data,
          options: {
            plugins: {
              legend: {
                display: true,
                labels: {
                  padding: 25,
                }
              }
            },
            scales: {
              y: {
                suggestedMin: 0,
              }
            }
          }
        });
      })
    </script>
    <table>
      <thead>
        <tr>
          <th>Month</th>
          <th>PM 2.5 concentration</th>
          <th>PM 10 concentration</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($stats as $month => $measurements) : ?>
          <tr>
            <th><?php e($month); ?></th>
            <td>
              <?php if (count($measurements['pm25']) !== 0) : ?>
                <?php e(round(array_sum($measurements['pm25']) / count($measurements['pm25']), 2)); ?>
                <?php e($units['pm25']); ?>
              <?php else : ?>
                <i>No data available</i>
              <?php endif; ?>
            </td>
            <td>
              <?php if (count($measurements['pm10']) !== 0) : ?>
                <?php e(round(array_sum($measurements['pm10']) / count($measurements['pm10']), 2)); ?>
                <?php e($units['pm10']); ?>
              <?php else : ?>
                <i>No data available</i>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>

<?php
require_once __DIR__ . "/../views/footer.html";
?>
