

<h3 class="m-0">Step 1: Choose the pieces for your service</h3>
<p>A piece is the software to process your data. A piece can be composed by one or multiple virtual containers. You can select a piece from the catalog or create a new one from scratch.</p>

<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row">
            <div class="col-5 col-sm-3">
            <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link active" id="vert-tabs-home-tab" data-toggle="pill" href="#vert-tabs-home" role="tab" aria-controls="vert-tabs-home" aria-selected="true">My pieces</a>
                <a class="nav-link" id="vert-tabs-profile-tab" data-toggle="pill" href="#vert-tabs-profile" role="tab" aria-controls="vert-tabs-profile" aria-selected="false">Create a piece</a>
                <a class="nav-link" id="vert-tabs-repository-tab" data-toggle="pill" href="#vert-tabs-repository" role="tab" aria-controls="vert-tabs-repository" aria-selected="false">Pieces repository</a>
            </div>
            </div>
            <div class="col-7 col-sm-9">
            <div class="tab-content" id="vert-tabs-tabContent">
                <div class="tab-pane text-left fade show active" id="vert-tabs-home" role="tabpanel" aria-labelledby="vert-tabs-home-tab">
                    <div class="row" id="grid-pieces" ></div>
                </div>
                <div class="tab-pane fade" id="vert-tabs-profile" role="tabpanel" aria-labelledby="vert-tabs-profile-tab">
                    <div id="form-new-piece" ><?php include_once(VISTAS . "/forms/create_piece.php"); ?> </div>
                </div>
                <div class="tab-pane fade" id="vert-tabs-repository" role="tabpanel" aria-labelledby="vert-tabs-repository-tab">
                    
                </div>
            </div>
            </div>
        </div>
    </div>
<!-- /.card -->
</div>

<!--<div class="card" style="width: 18rem;">
  <div class="card-body">
    <h5 class="card-title">Card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
    <a href="#" class="card-link">Card link</a>
    <a href="#" class="card-link">Another link</a>
  </div>
</div>-->