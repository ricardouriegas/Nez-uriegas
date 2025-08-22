<?php

?>

<h3 class="m-0">Step 3: Choose the data to process with your processing structure</h3>
<p>Below are listed the catalogs that you uploaded. You can also choose to create a new catalog, if you want to process
    data that is not in the storage system.</p>


<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row">
            <div class="col-5 col-sm-3">
                <div class="nav flex-column nav-tabs h-100" id="vert-tabs-data" role="tablist"
                    aria-orientation="vertical">
                    <a class="nav-link active" id="ver-tabs-pub-tab" data-toggle="pill" href="#ver-tabs-pub" role="tab"
                        aria-controls="ver-tabs-pub" aria-selected="true">Catalogs published</a>
                    <a class="nav-link" id="vert-tabs-sub-tab" data-toggle="pill" href="#vert-tabs-sub" role="tab"
                        aria-controls="vert-tabs-sub" aria-selected="false">Catalogs suscribed</a>
                    <a class="nav-link" id="vert-tabs-mydata-tab" data-toggle="pill" href="#vert-tabs-mydata" role="tab"
                        aria-controls="vert-tabs-mydata" aria-selected="false">Create catalog</a>
                    <a class="nav-link" id="vert-tabs-local-tab" data-toggle="pill" href="#vert-tabs-local" role="tab"
                        aria-controls="vert-tabs-local" aria-selected="false">Local system</a>
                </div>
            </div>
            <div class="col-7 col-sm-9">
                <div class="tab-content" id="vert-tabs-tabContent">
                    <div class="tab-pane text-left fade show active" id="ver-tabs-pub" role="tabpanel"
                        aria-labelledby="ver-tabs-pub-tab">
                        <div class="row" id="catalogs_list"></div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-sub" role="tabpanel" aria-labelledby="vert-tabs-sub-tab">
                        <div class="row" id="catalogs_list_suscribed"></div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-mydata" role="tabpanel"
                        aria-labelledby="vert-tabs-mydata-tab">
                        <div class="row" id="upload_my_data">
                            <p>Please enter the name of your new data source. After the creation of the processing
                                structure you will able to upload data to the catalog created for your data source. To
                                upload the data, you must use a SkyCDS upload client.</p>
                            <?php include(VISTAS . "/forms/create_catalog.php"); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-local" role="tabpanel"
                        aria-labelledby="vert-tabs-mydata-tab">
                        <div class="row">
                            <div>
                                <p>Choose a directory on the shared filesystem.</p>
                            </div>

                            <br>
                            <br>
                        </div>
                        <div class="row" id="localsystem">

                            <div height="300px" id="dirsonservers"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card -->
</div>