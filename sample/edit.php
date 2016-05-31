<?php $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 'null'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Edit Configuration</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/dashboard.css" rel="stylesheet">
    <link href="datatable/css/bootstrap-datatable.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]>
    <script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">Card Table Visualizer</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active">
                    <a href="#"><?php if ($id == 'null') : echo 'Add'; else : echo 'Edit'; endif;?> Table</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="main">
            <div class="table-responsive" id="main-content">
                <form class="form-inline">
                    <div class="form-group">
                        <label for="exampleInputName2">Name : </label>
                        <input type="text" class="form-control" id="configuration-name" placeholder="Name">
                    </div>
                </form>
                <br/>


                <div class="row" style="width: 1100px;">
                    <div class="col-md-10">
                        <canvas id="canvasOne" width="900" height="500">
                            Your browser does not support HTML5 canvas.
                        </canvas>
                    </div>


                    <div class="col-md-2">
                        <div class="btn-group-vertical" role="group" aria-label="...">
                            <button class="btn btn-default ctv-export-frame" type="button">Frame</button>
                            <button class="btn btn-default ctv-clear" type="button">Clear</button>
                            <button class="btn btn-default ctv-export" type="button">Export</button>
                            <button class="btn btn-default ctv-load-saved" type="button">Load Saved</button>
                            <button class="btn btn-default ctv-save-as" type="button">Save</button>
                            <button class="btn btn-default ctv-close" type="button">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <p class="text-center">© Company 2016</p>
    </div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="dialog-confirm-title"></h4>
            </div>
            <div class="modal-body">
                <p id="dialog-confirm-text"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="dialog-confirm-ok">Confirm</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/jquery-1.11.1.js" type="text/javascript"></script>
<script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="datatable/js/jquery.dataTables.js" type="text/javascript"></script>
<script src="datatable/js/dataTables.bootstrap.js" type="text/javascript"></script>
<script src="../js/jquery.ctv.1.0.js" type="text/javascript"></script>
<script src="js/edit.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
       initCardTable({
           id: <?php echo $id ?>,
           state: JSON.stringify([]) // empty array []
       })
    });
</script>

</body>
</html>
