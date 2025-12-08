<script src="../js/console_usuario.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

<style>
.search-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.search-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem 2rem;
}

.search-card-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1.3rem;
}

.search-card-header i {
    margin-right: 0.5rem;
}

.search-card-body {
    padding: 2rem;
}

.form-group-custom {
    margin-bottom: 1.5rem;
}

.form-group-custom label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group-custom label i {
    color: #667eea;
}

.form-control-custom {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control-custom:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.btn-search-custom {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1.1rem;
    width: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-search-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    color: white;
}

.btn-search-custom i {
    margin-right: 0.5rem;
}

.results-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.results-card-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 1.5rem 2rem;
}

.results-card-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1.2rem;
}

.results-card-body {
    padding: 2rem;
}

/* Timeline Mejorado */
.timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.timeline > div {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 80px;
}

.timeline > div > i {
    position: absolute;
    left: 14px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 1;
}

.timeline-item {
    background: #f7fafc;
    padding: 1.5rem;
    border-radius: 12px;
    border-left: 4px solid;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.timeline-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.timeline-item .time {
    display: inline-block;
    background: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.timeline-item .time i {
    margin-right: 0.5rem;
}

.timeline-header {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.timeline-header a {
    text-decoration: none;
    transition: all 0.3s ease;
}

.timeline-header a:hover {
    text-decoration: underline;
}

.timeline-body {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    color: #4a5568;
    line-height: 1.6;
    margin-top: 0.75rem;
}

.time-label {
    margin-bottom: 2rem;
    padding-left: 80px;
}

.time-label span {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

/* Badges de área */
.area-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin: 0.25rem;
}

.area-badge-origin {
    background: #3b82f6;
    color: white;
}

.area-badge-destination {
    background: #10b981;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .search-card-body {
        padding: 1.5rem;
    }
    
    .results-card-body {
        padding: 1.5rem;
    }
    
    .timeline::before {
        left: 15px;
    }
    
    .timeline > div {
        padding-left: 60px;
    }
    
    .timeline > div > i {
        left: 0;
        width: 32px;
        height: 32px;
    }
    
    .time-label {
        padding-left: 60px;
    }
}
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-search-location"></i> <b>RASTREAR TRÁMITE</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
                    <li class="breadcrumb-item active">TRÁMITE</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Search Card -->
                <div class="search-card">
                    <div class="search-card-header">
                        <h5><i class="fas fa-search"></i> Buscador de Trámite</h5>
                    </div>
                    <div class="search-card-body">
                        <div class="row">
                            <div class="col-md-4 form-group-custom">
                                <label>
                                    <i class="fas fa-file-alt"></i>
                                    N° Expediente / N° Registro
                                    <span style="color: #e53e3e;">*</span>
                                </label>
                                <select class="form-control form-control-custom js-example-basic-single" id="txt_expediente" style="width:100%"></select>
                            </div>
                            <div class="col-md-4 form-group-custom">
                                <label>
                                    <i class="fas fa-hashtag"></i>
                                    Número de Documento
                                    <span style="color: #e53e3e;">*</span>
                                </label>
                                <input type="text" class="form-control form-control-custom" id="txt_numero" disabled>
                            </div>
                            <div class="col-md-4 form-group-custom">
                                <label>
                                    <i class="fas fa-id-card"></i>
                                    Número de DNI
                                    <span style="color: #e53e3e;">*</span>
                                </label>
                                <input type="text" class="form-control form-control-custom" id="txt_dni" disabled>
                            </div>
                            <div class="col-12">
                                <button class="btn-search-custom" onclick="Traer_Datos_Seguimiento2()">
                                    <i class="fa fa-search"></i> Buscar Documento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Card -->
                <div class="results-card" id="div_buscador" style="display:none">
                    <div class="results-card-header">
                        <h5 id="lbl_titulo"><i class="fas fa-route"></i> Seguimiento</h5>
                    </div>
                    <div class="results-card-body">
                        <div id="div_seguimiento"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('.js-example-basic-single').select2();
    Cargar_Select_Expedientes();
    
    $("#txt_id").change(function(){
        var id=$("#txt_id").val();
        Traerhoras(id);
    });
});

$("#txt_expediente").change(function(){
    var id=$("#txt_expediente").val();
    Traerrdatosexpediente(id);
});
</script>