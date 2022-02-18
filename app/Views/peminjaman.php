<?= $this->extend('layout/page_layout') ?>

<?= $this->section('style') ?>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<!-- SweetAlert -->
<link rel="stylesheet" href="<?= base_url('plugins/sweetalert/sweetalert2.css') ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Layanan Peminjaman Mandiri</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                        <li class="breadcrumb-item active">Peminjaman Mandiri</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- left column -->
                <div class="col-md-4">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Form Peminjaman Mandiri</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <form id="formPeminjaman">
                            <?= csrf_field(); ?>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="barcode">Masukkan Kode Eksemplar/Barkod :</label>
                                    <div class="input-group mb-3">
                                        <input type="text" name="barcode" class="form-control" id="barcode" autofocus
                                            autocomplete="off">
                                        <div class="input-group-prepend">
                                            <button type="submit" id="btnPinjam" class="btn btn-primary">PINJAM</button>
                                        </div>
                                        <!-- /btn-group -->
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </form>
                    </div>
                    <!-- /.card -->
                </div>
                <!--/.col (left) -->
                <!-- right column -->
                <div class="col-md-8">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Data Peminjaman Koleksi</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div id="example1_wrapper" class="dataTables_wrapper dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="myTable"
                                            class="table table-bordered table-striped dataTable dtr-inline"
                                            aria-describedby="example1_info">
                                            <thead>
                                                <tr>
                                                    <th>Kode Eksemplar</th>
                                                    <th>Judul</th>
                                                    <th>Tanggal Pinjam</th>
                                                    <th>Tanggal Harus Kembali</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
                <!--/.col (right) -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<!-- jquery-validation -->
<script src="<?= base_url('plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
<script src="<?= base_url('plugins/jquery-validation/additional-methods.min.js') ?>"></script>
<!-- SweetAlert -->
<script src="<?= base_url('plugins/sweetalert/sweetalert2.all.min.js') ?>"></script>
<script>
$(function() {
    $('#formPeminjaman').validate({
        rules: {
            barcode: {
                required: true,
            },
        },
        messages: {
            barcode: {
                required: "Field Tidak Boleh Kosong!!!",
            },
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: (form, e) => {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: '/peminjaman/new',
                data: $('#formPeminjaman').serialize(),
                dataType: 'json',
                success: (data) => {
                    document.getElementById('formPeminjaman').reset();
                    const msg = JSON.parse(JSON.stringify(data));
                    swal.fire({
                        title: 'Berhasil',
                        text: msg.pesan,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#myTable').DataTable().ajax.reload(null, false);
                    });
                },
                error: (data) => {
                    swal.fire({
                        title: 'Gagal',
                        text: 'Data Gagal Disimpan',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
            return false;
        }
    });
});
</script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    const site_url = "<?php echo site_url(); ?>";

    let table = $('#myTable').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": site_url + "/peminjaman/data",
            "type": "POST"
        },
        "columns": [{
                "data": "item_code",
                "className": "text-center"
            },
            {
                "data": "title",
                "className": "text-center"
            },
            {
                "data": "loan_date",
                "className": "text-center"
            },
            {
                "data": "due_date",
                "className": "text-center"
            }
        ]
    });
});
</script>
<?= $this->endSection() ?>