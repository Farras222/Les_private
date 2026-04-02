<?php 
include '../index.php'; 

// Pagination settings
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM user";
if (!empty($search)) {
    $count_query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
}
$count_result = mysqli_query($mysqli, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan pagination
$query = "SELECT * FROM user";
if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
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
  background: #5e72e4; /* bebas, sesuaikan tema */
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
              <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
              <li class="breadcrumb-item text-sm text-white active" aria-current="page">Users</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">Users</h6>
          </nav>
          <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                <form method="GET" action="" class="ms-md-auto pe-md-3 d-flex align-items-center">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                            placeholder="Cari nama atau email..." 
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
                        <h6>Users Data (<?php echo $total_data; ?>)</h6>
                        <a href="tambah.php" class="btn btn-info btn-sm btn-action bg-warning">
                            <i class="fas fa-edit"></i> Tambah User
                        </a>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <?php if ($total_data > 0): ?>
                        <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Hp</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Alamat</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($user = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div class="avatar-initial">
                                        <?php echo initials($user['name']); ?>
                                    </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm"><?php echo $user['name']; ?></h6>
                                    <p class="text-xs text-secondary mb-0"><?php echo $user['email']; ?></p>
                                </div>
                                </div>
                            </td>
                            <td>
                                 <p class="text-xs font-weight-bold mb-0"><?php echo $user['role']; ?></p>
                            </td>
                            <td class="align-middle text-center text-sm">
                                <p class="text-xs font-weight-bold mb-0"><?php echo $user['no_hp']; ?></p>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold"><?php echo $user['alamat']; ?></span>
                            </td>
                            <td class="align-middle">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm btn-action bg-success">
                                    Edit
                                </a>
                                <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm btn-action bg-danger"  onclick="return confirm('Yakin hapus user <?php echo addslashes($user['name']); ?>?')">
                                    Delete
                                </a>
                            </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        </table>
                        <?php else: ?>
                            <!-- Tidak Ada Data -->
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h4 class="text-dark mb-2">Tidak Ada Data User</h4>
                                <p class="text-muted mb-3">Belum ada data user yang terdaftar di sistem.</p>
                                <a href="tambah_user.php" class="btn btn-warning">
                                    <i class="fas fa-plus me-2"></i> Tambah User
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <!-- footer -->
        <?php include '../../../components/footer.php'; ?>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>