

<div>
<div class="row">
    <div  class="col-sm-10">
        <h3 class="m-0">Step 4: Connect your pieces to create a processing structure</h3>


        <p>A processing structure is created coupling two or more pieces. This processing structure will be exposed as a service.</p>
    </div>
    <div  class="col-sm-2">
        <a class="btn btn-app bg-primary" data-toggle="modal" data-target="#modal-default">
            <i class="fas fa-save"></i> Save
        </a>
        <a class="btn btn-app bg-danger" onclick="clearStructure()">
            <i class="fas fa-eraser"></i> Clear
        </a>
    </div>
</div>
</diV>
<div class="row">
    <div  class="col-sm-2">
        <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-puzzle-piece"></i>
                  Pieces
                </h3><br>
                <strong>Drag and drop your pieces to couple them.</strong>
              </div>
              <!-- /.card-header -->
              <div id="blocklist" class="card-body">
                
              </div>
              <!-- /.card-body -->
        </div>
            <!-- /.card -->
    </div>
    <div class="col-sm-10">
        <div class="card card-primary card-outline" id="canvas" >
        
        </div>
    </div>
    
</div>