<script src="../js/console_usuario.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><b>RASTREAR TRÁMITE</b></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
              <li class="breadcrumb-item active">TRÁMITE</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- /.col-md-6 -->
          <div class="col-lg-12">
            <div class="card">

                  <div class="card-body">
                  <div class="row">
          
          <!-- /.col-md-6 -->
          <div class="col-lg-12">
            <div class="card">
            <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search"></i>&nbsp;&nbsp;<b>Rastrear Trámites</b></h3>
            </div>
              <div class="card-header bg-primary">
                <h5 class="card-title m-0"><b>Buscador de Trámite</b></h5>
              </div>
              <div class="card-body">
                <div class="row">
                    <div class="col-4 form-group">
                        <label for="" style="font-size:small;">N° Expediente / N° Registro(*):</label>
                        <select type="text" class="form-control js-example-basic-single" id="txt_expediente" style="width:100%"></select>
                    </div>
                    <div class="col-4">
                        <label for="">Número de Documento(*):</label>
                        <input type="text" class="form-control" id="txt_numero" disabled>
                    </div>
                    <div class="col-4">
                        <label for="">Número de DNI(*):</label>
                        <input type="text" class="form-control" id="txt_dni" disabled>
                    </div>
                    <div class="col-12">
                        <label for="">&nbsp;</label><br>
                        <button class="btn btn-success" style="width:100%;font-size:18px" onclick="Traer_Datos_Seguimiento2()"><i class="fa fa-search"></i> Buscar Documento</button>
                    </div>
                    
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-12" id="div_buscador" style="display:none">
            <div class="card">
              <div class="card-header bg-primary">
                <h5 class="card-title m-0" id="lbl_titulo"><b>Seguimiento</b></h5>
              </div>
              <div class="card-body">
                <div class="row">
                <div class="col-md-12" id="div_seguimiento">
            <!-- The time line -->
            
                </div>

              <!-- /.timeline-label -->
              <!-- timeline item -->
                </div>
              </div>
            </div>
            </div>

          </div>
          <!-- /.col-md-6 -->
        </div>
                  </div>
                </div>           
           </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->

    <!-- /.content -->
<div class="modal fade" id="modal_seguimiento" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lb_titulo">Seguimiento del Trámite</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12" style="text-align:center">   
            <table id="tabla_seguimiento" class="display compact" style="width:100%" style="text-align:center">
                <thead style="background-color:#0A5D86;color:#FFFFFF; ">
                  <tr style="text-align:center">
                      <th style="text-align:center">PROCEDENCIA</th>
                      <th style="text-align:center">FECHA</th>
                      <th style="text-align:center">ASUNTO</th>
                      <th style="text-align:center">ARCHIVO ANEXADO</th>
                   </tr>
                  </thead>
                </table>     
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-arrow-right-from-bracket"></i>Cerrar</button>
      </div>
    </div>
  </div>
</div>
    <!-- /MODAL MAS DATOS -->


 <!-- /MODAL DERIVAR-->

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