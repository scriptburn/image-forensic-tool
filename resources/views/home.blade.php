@extends('default')
@section('content')

<style type="text/css">
    #status {
        display: none;

    }
 #result {
        display: none;

    }
    #status .progress {
        display: none;
        width: 100%;
    }

    #status .progress .progress-bar {
        height: 100%;
    }
    
    #status .progress-container{width:80%;min-width:80%;float:left; text-align: left;height: 38px;vertical-align: middle;height: 38px; vertical-align: middle; padding-top: 12px;}
     #status .abort-container {width:20%;min-width:20%;float:left;    text-align: right;}
    </style>

<div class="py-5 text-center">
</div>
<div class="row" id="status">
                                    <div class="col-lg-12" >
                                        <div class="alert alert-danger" role="alert" style="display:none">
                                        </div>
                                         <div class="alert alert-success" role="alert" style="display:none">
                                        </div>
                                    </div>
                                </div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Select an image to process</h5>
                <form class="needs-validation">
                    <div class="row" id="file-input">
                        <div class="col-lg-12">
                            <form class="needs-validation1" id="file-input-form">
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="files" name="files">
                                            <label class="custom-file-label" for="name">Select file</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <br/>
        <div class="card" id="result">
            <div class="card-body">
            </div>
        </div>
    </div>
</div>


@endsection