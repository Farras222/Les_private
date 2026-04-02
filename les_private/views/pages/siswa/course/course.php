<?php 
include '../index.php'; 

// Pagination settings
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM courses";
if (!empty($search)) {
    $count_query .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}
$count_result = mysqli_query($mysqli, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan pagination
$query = "SELECT * FROM courses";
if (!empty($search)) {
    $query .= " WHERE (title LIKE '%$search%' OR description LIKE '%$search%')";
}
$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($mysqli, $query);

function initials($name) {
    $words = explode(' ', trim($name));
    $initials = '';

    foreach ($words as $word) {
        if ($word !== '') {
            $initials .= strtoupper($word[0]);
        }
    }

    return $initials;
}

?>


<style>
    /* Agar form search tetap bagus di berbagai posisi */
#navbar .input-group {
    min-width: 300px;
    max-width: 500px;
}

/* Responsif */
@media (max-width: 768px) {
    #navbar .input-group {
        min-width: 100%;
        max-width: 100%;
    }
    
    #navbar .btn-primary {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Agar tombol dan input sejajar */
#navbar .input-group-text,
#navbar .form-control,
#navbar .btn {
    height: 38px;
}
.avatar-initial {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #FF8C5A; /* bebas, sesuaikan tema */
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  padding: 8px;
  margin-right: 10px;
}

   </style>

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
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Course</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Course</h6>
          </nav>
          <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                <form method="GET" action="" class="ms-md-auto pe-md-3 d-flex align-items-center">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                            placeholder="Cari judul course..." 
                            value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-warning" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->

        <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Course Data (<?php echo $total_data; ?>)</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2 m-4">
    <div class="row g-4">
        <?php while($course = mysqli_fetch_assoc($result)) { ?>
        <div class="col-md-4 col-lg-3">
            <div class="card" style="width: 18rem; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;">
                <div class="card-body">
                    <h5 class="card-title fw-bold" style="color: #FF8C5A;">
                        <?php echo htmlspecialchars($course['title']); ?>
                    </h5>
                    <p class="card-text text-muted" style="font-size: 0.9rem;">
                        <?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="detail.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning" style="background: linear-gradient(135deg, #FF8C5A 0%, #FF8C5A 100%); border: none;">
                            <i class="bi bi-eye"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>

    </main>
</body>