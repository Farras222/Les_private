<?php   
include '../index.php'; 

$total_users_query = "SELECT COUNT(*) AS total_users FROM user";
$total_users_result = mysqli_query($mysqli, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total_users'];

$total_siswa_query = "SELECT COUNT(*) AS total_siswa FROM user WHERE role = 'siswa'";
$total_siswa_result = mysqli_query($mysqli, $total_siswa_query);
$total_siswa = mysqli_fetch_assoc($total_siswa_result)['total_siswa'];

$total_guru_query = "SELECT COUNT(*) AS total_guru FROM user WHERE role = 'guru'";
$total_guru_result = mysqli_query($mysqli, $total_guru_query);
$total_guru = mysqli_fetch_assoc($total_guru_result)['total_guru'];

$total_course_query = "SELECT COUNT(*) AS total_course FROM courses";
$total_course_result = mysqli_query($mysqli, $total_course_query);
$total_course = mysqli_fetch_assoc($total_course_result)['total_course'];

$yesterday = date('Y-m-d', strtotime('-1 day'));
$years = date('Y');

$total_siswa_yesterday_query = "SELECT COUNT(*) AS total_siswa_yesterday FROM user WHERE role = 'siswa' AND DATE(created_at) = '$yesterday'";
$total_siswa_yesterday_result = mysqli_query($mysqli, $total_siswa_yesterday_query);
$total_siswa_yesterday = mysqli_fetch_assoc($total_siswa_yesterday_result)['total_siswa_yesterday'];

$total_guru_yesterday_query = "SELECT COUNT(*) AS total_guru_yesterday FROM user WHERE role = 'guru' AND DATE(created_at) = '$yesterday'";
$total_guru_yesterday_result = mysqli_query($mysqli, $total_guru_yesterday_query);
$total_guru_yesterday = mysqli_fetch_assoc($total_guru_yesterday_result)['total_guru_yesterday']; 

if ($total_siswa_yesterday > 0) {
    $growth_rate = (($total_siswa - $total_siswa_yesterday) / $total_siswa_yesterday) * 100;
} else {
    $growth_rate = 0;
}

if ($total_guru_yesterday > 0) {
    $growth_rate_guru = (($total_guru - $total_guru_yesterday) / $total_guru_yesterday) * 100;
} else {
    $growth_rate_guru = 0;
}



$query = "
SELECT 
    cp.completed,
    COUNT(DISTINCT cp.siswa_id) AS total
FROM student_progress cp
JOIN user u ON u.id = cp.siswa_id
WHERE u.role = 'siswa'
GROUP BY cp.completed
";

$dummyLabels = ['Course A', 'Course B', 'Course C', 'Course D', 'Course E'];
$dummyValues = [300, 400, 200, 500, 250];

$isDummy = false;

// setelah query

$result = mysqli_query($mysqli, $query);

$labels = [];
$values = [];

while ($row = mysqli_fetch_assoc($result)) {
  $labels[] = ucfirst($row['completed']);
  $values[] = $row['total'];
}
  
if (empty($labels) || empty($values)) {
    $labels = $dummyLabels;
    $values = $dummyValues;
    $isDummy = true;
}

$ranking = "SELECT u.name, COUNT(sp.material_id) AS courses_completed
FROM student_progress sp
JOIN user u ON u.id = sp.siswa_id
GROUP BY u.name
ORDER BY courses_completed DESC
LIMIT 5";

$totalMateri = "(
    SELECT COUNT(*) 
    FROM materials m
    JOIN courses c ON m.course_id = c.id
    WHERE c.id IN (
        SELECT DISTINCT course_id 
        FROM student_progress sp 
        WHERE sp.siswa_id = u.id
    )
) AS total_materi";

$ranking_result = mysqli_query($mysqli, $ranking);

?>


<body class="g-sidenav-show bg-warning">
  <!-- sidebar -->
  <?php include '../../../components/sidebar.php'; ?>  
    <main class="main-content position-relative border-radius-lg ">
      <!-- Navbar -->
      <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
        <div class="container-fluid py-1 px-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
              <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages / Guru</a></li>
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Dashboard</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Dashboard</h6>
          </nav>
        </div>
      </nav>
      <!-- End Navbar -->

      <!-- Info boxes -->
      <div class="container-fluid py-4">
        <div class="row">
          <div class="col-xl-6 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-8">
                    <div class="numbers">
                      <p class="text-sm mb-0 text-uppercase font-weight-bold">Students</p>
                      <h5 class="font-weight-bolder">
                        <?php echo $total_siswa; ?>
                      </h5>
                      <p class="mb-0">
                        <span class="text-success text-sm font-weight-bolder"><?php echo number_format($growth_rate, 2); ?>%</span>
                        since yesterday
                      </p>
                    </div>
                  </div>
                  <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                      <i class="ni ni-circle-08 text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 col-sm-6">
            <div class="card">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-8">
                    <div class="numbers">
                      <p class="text-sm mb-0 text-uppercase font-weight-bold">Course</p>
                      <h5 class="font-weight-bolder">
                        <?php echo $total_course; ?>
                      </h5>
                      <p class="mb-0">
                        in today's
                      </p>
                    </div>
                  </div>
                  <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                      <i class="ni ni-books text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <!-- End Info boxes -->

      <!-- Grafik -->
       <div class="row mt-4">
        <div class="col-lg-15 mb-lg-0 mb-4">
          <div class="card z-index-2 h-100">
            <div class="card-header pb-0 pt-3 bg-transparent">
              <h6 class="text-capitalize">Courses completed overview</h6>
              <p class="text-sm mb-0"></p>
                <i class="fa fa-arrow-up text-success"></i>
                <?php if ($isDummy): ?>
                  <span id="dummyNote" style="display: none;">This is dummy data. No course completions recorded yet.</span>
                <?php else: ?>
                  <span>Just updated</span>
                <?php endif; ?>
              </p>
            </div>
            <div class="card-body p-3">
              <div class="chart">
                <canvas id="chart-line" class="chart-canvas" height="300"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- End Grafik -->

      <!-- Top Students -->
       <div class="row mt-4">
        <div class="col-lg-15 mb-lg-0 mb-4">
          <div class="card ">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between">
                <h6 class="mb-2">Leaderboard</h6>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center ">
                <tbody>
                  <?php 
                  while ($rank = mysqli_fetch_assoc($ranking_result)) {
                  ?>
                  <tr>
                    <td class="w-30">
                      <div class="d-flex px-2 py-1 align-items-center">
                        <div class="ms-4">
                          <p class="text-xs font-weight-bold mb-0">Name:</p>
                          <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['name']); ?></h6>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="text-center">
                        <p class="text-xs font-weight-bold mb-0">Status:</p>
                        <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['completed']); ?></h6>
                      </div>
                    </td>
                    <td>
                      <div class="text-center">
                        <p class="text-xs font-weight-bold mb-0">Total Materi:</p>
                        <h6 class="text-sm mb-0"><?php echo htmlspecialchars($totalMateri); ?></h6>
                      </div>
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- End Top Students -->
      </div>

      <br>

      <!-- footer -->
       <?php include '../../../components/footer.php'; ?>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://demos.creative-tim.com/argon-dashboard/assets/js/plugins/chartjs.min.js"></script>
    <script>
    const labels = <?= json_encode($labels) ?>;
    const values = <?= json_encode($values) ?>;
    const isDummy = <?= json_encode($isDummy) ?>;
    </script>
    <script>
    var ctx1 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
    if (labels.length === 0 || values.length === 0) {
    document.getElementById('dummyNote').style.display = 'block';
} else {
    new Chart(ctx1, {
      type: "line",
      data: {
        labels: labels,
        datasets: [{
          label: "Jumlah Siswa",
          tension: 0.4,
          borderWidth: 0,
          pointRadius: 0,
          borderColor: "#5e72e4",
          backgroundColor: gradientStroke1,
          borderWidth: 3,
          fill: true,
          data: values,
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#fbfbfb',
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
              beginAtZero: true,
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#ccc',
              padding: 20,
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });
}
  </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script src="https://demos.creative-tim.com/argon-dashboard/assets/js/argon-dashboard.min.js?v=2.1.0"></script>
  </body>
</html>