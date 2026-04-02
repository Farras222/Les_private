<?php 
include '../index.php'; 

$user = $_SESSION['user_id'];
$user_query = "SELECT * FROM user WHERE id = '$user'";
$user_result = mysqli_query($mysqli, $user_query);
$user = mysqli_fetch_assoc($user_result);

$ranking = "SELECT 
    c.id,
    c.title,
    c.description,
    COUNT(DISTINCT sp.material_id) AS materi_diselesaikan,
    (SELECT COUNT(*) FROM materials m WHERE m.course_id = c.id) AS total_materi,
    CASE 
        WHEN (SELECT COUNT(*) FROM materials m WHERE m.course_id = c.id) > 0 
        THEN ROUND((COUNT(DISTINCT sp.material_id) / (SELECT COUNT(*) FROM materials m WHERE m.course_id = c.id)) * 100, 2)
        ELSE 0
    END AS progress_percentage,
    MAX(sp.completed_at) AS terakhir_dikerjakan,
    (SELECT COUNT(*) FROM quizzes q WHERE q.course_id = c.id) AS total_quiz
FROM student_progress sp
JOIN materials m ON sp.material_id = m.id
JOIN courses c ON m.course_id = c.id
WHERE sp.siswa_id = $siswa_id
  AND sp.completed = 1
GROUP BY c.id, c.title, c.description
ORDER BY MAX(sp.completed_at) DESC";

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
              <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages / Siswa</a></li>
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
          <div class="col-xl-6 col-sm-6">
            <div class="card">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-8">
                    <div class="numbers">
                      <p class="text-sm mb-0 text-uppercase font-weight-bold">Hi</p>
                      <h5 class="font-weight-bolder">
                        <?php echo $user['name']; ?>
                      </h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <!-- End Info boxes -->

      <!-- Top Students -->
       <div class="row mt-4">
        <div class="col-lg-15 mb-lg-0 mb-4">
          <div class="card ">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between">
                <h6 class="mb-2">Course Progress</h6>
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
                          <p class="text-xs font-weight-bold mb-0">Course:</p>
                          <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['title']); ?></h6>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="text-center">
                        <p class="text-xs font-weight-bold mb-0">Materi Diselesaikan:</p>
                        <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['materi_diselesaikan']); ?></h6>
                      </div>
                    </td>
                    <td>
                      <div class="text-center">
                        <p class="text-xs font-weight-bold mb-0">Total Materi:</p>
                        <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['total_materi']); ?></h6>
                      </div>
                    </td>
                    <td>
                      <div class="text-center">
                        <p class="text-xs font-weight-bold mb-0">Progress:</p>
                        <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['progress_percentage']); ?>%</h6>
                      </div>
                    </td>
                    <td>
                      <div class="text-center">
                        <p class="text-xs font-weight-bold mb-0">Terakhir Dikerjakan:</p>
                        <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['terakhir_dikerjakan']); ?></h6>
                      </div>
                    </td>
                    <td>
                      <div class="text-center">
                        <p class="text-xs font-weight-bold mb-0">Total Quiz:</p>
                        <h6 class="text-sm mb-0"><?php echo htmlspecialchars($rank['total_quiz']); ?></h6>
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

    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>