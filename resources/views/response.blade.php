
<div class="bd-box bd-box-active mb-4">
    @if(!empty($data))
   

    @php 
        $id=microtime(true);
         @endphp
    <h3>Result for: {{  $data['name']}}</h3>
    <div class="accordion" id="accordion{{ $id }}">
        
          

         @php 
        $section='labels';
         @endphp
        <div class="card">
            <div class="card-header" id="heading{{ $id }}{{ $section }}">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{ $id }}{{ $section }}" aria-expanded="false" aria-controls="collapse{{ $id }}{{ $section }}">
                      {{  !empty($data['labels']) ? count($data['labels']) :"0"}} best guess labels found
                    </button>
                </h5>
            </div>
            <div id="collapse{{ $id }}{{ $section }}" class="collapse" aria-labelledby="heading{{ $id }}{{ $section }}" data-parent="#accordion{{ $id }}" style="">
                <div class="card-body">
                     @if(!empty($data['labels']))
                    @foreach($data['labels'] as $label )
                        <span class="badge badge-primary">{{ $label }}</span>
                    @endforeach
                     @endif
                </div>
            </div>
        </div>
       
        
         @php 
        $section='pages_matching_images';
        @endphp
        <div class="card">
            <div class="card-header" id="heading{{ $id }}{{ $section }}">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{ $id }}{{ $section }}" aria-expanded="false" aria-controls="collapse{{ $id }}{{ $section }}">
                      {{ !empty($data['pages_matching_images']) ? count($data['pages_matching_images']):"0" }} pages with matching images found
                    </button>
                </h5>
            </div>
            <div id="collapse{{ $id }}{{ $section }}" class="collapse" aria-labelledby="heading{{ $id }}{{ $section }}" data-parent="#accordion{{ $id }}" style="">
                <div class="card-body">
                    @if(!empty($data['pages_matching_images']))
                    <ul>
                    @foreach($data['pages_matching_images'] as $label )
                        <li ><a href="{{ $label  }}" target="_blank">{{ $label }}</a></li>
                    @endforeach
                    </ul>
                     @endif
                </div>
            </div>
        </div>
       

         
         @php 
        $section='full_matching_images';
        @endphp
        <div class="card">
            <div class="card-header" id="heading{{ $id }}{{ $section }}">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{ $id }}{{ $section }}" aria-expanded="false" aria-controls="collapse{{ $id }}{{ $section }}">
                      {{ !empty($data['full_matching_images'])?count($data['full_matching_images']):'0' }} full matching images found
                    </button>
                </h5>
            </div>
            <div id="collapse{{ $id }}{{ $section }}" class="collapse" aria-labelledby="heading{{ $id }}{{ $section }}" data-parent="#accordion{{ $id }}" style="">
                <div class="card-body">
                    @if(!empty($data['full_matching_images']))
                    <ul>
                    @foreach($data['full_matching_images'] as $label )
                        <a href="{{ $label  }}" target="_blank"> <img src="{{ $label }}" class="rounded float-left img-item img-thumbnail" alt="..."> </a>
                    @endforeach
                    </ul>
                      @endif
                </div>
            </div>
        </div>



         @php 
        $section='partial_matching_images';
        @endphp
        <div class="card">
            <div class="card-header" id="heading{{ $section }}">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{ $id }}{{ $section }}" aria-expanded="false" aria-controls="collapse{{ $id }}{{ $section }}">
                      {{ !empty($data['partial_matching_images'])?count($data['partial_matching_images']):'0' }} partial matching images found
                    </button>
                </h5>
            </div>
            <div id="collapse{{ $id }}{{ $section }}" class="collapse" aria-labelledby="heading{{ $id }}{{ $section }}" data-parent="#accordion{{ $id }}{{ $id }}" style="">
                <div class="card-body">
                    @if(!empty($data['partial_matching_images']))
                     @foreach($data['partial_matching_images'] as $label )
                        <a href="{{ $label  }}" target="_blank"> <img src="{{ $label }}" class="rounded float-left img-item img-thumbnail" alt="..."> </a>
                    @endforeach
                       @endif
                </div>
            </div>
        </div>


          @php 
        $section='similar_matching_images';
        @endphp
        <div class="card">
            <div class="card-header" id="heading{{ $id }}{{ $section }}">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{ $id }}{{ $section }}" aria-expanded="false" aria-controls="collapse{{ $id }}{{ $section }}">
                      {{ !empty($data['similar_matching_images'])?count($data['similar_matching_images']):'0' }} similar matching images found
                    </button>
                </h5>
            </div>
            <div id="collapse{{ $id }}{{ $section }}" class="collapse" aria-labelledby="heading{{ $id }}{{ $section }}" data-parent="#accordion{{ $id }}" style="">
                <div class="card-body">
                    @if(!empty($data['similar_matching_images']))
                     @foreach($data['similar_matching_images'] as $label )
                        <a href="{{ $label  }}" target="_blank"> <img src="{{ $label }}" class="rounded float-left img-item img-thumbnail" alt="..."> </a>

                    @endforeach
                       @endif
                </div>
            </div>
        </div>


           @php 
        $section='web_entities';
        @endphp
        <div class="card">
            <div class="card-header" id="heading{{ $id }}{{ $section }}">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{ $id }}{{ $section }}" aria-expanded="false" aria-controls="collapse{{ $id }}{{ $section }}">
                      {{ !empty($data['web_entities'])?count($data['web_entities']):'0' }} web entities found
                    </button>
                </h5>
            </div>
            <div id="collapse{{ $id }}{{ $section }}" class="collapse" aria-labelledby="heading{{ $id }}{{ $section }}" data-parent="#accordion{{ $id }}{{ $id }}" style="">
                <div class="card-body">
                    @if(!empty($data['web_entities']))
                    <ul>
                    @foreach($data['web_entities'] as $label )
                        <li > {{ $label[0] }} ( {{ $label[1] }})</li>
                    @endforeach
                    </ul>
                      @endif
                </div>
            </div>
        </div>


         @php 
        $section='text';
        @endphp
        <div class="card">
            <div class="card-header" id="heading{{ $id }}{{ $section }}">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{ $id }}{{ $section }}" aria-expanded="false" aria-controls="collapse{{ $id }}{{ $section }}">
                      Report in Text Format
                    </button>
                </h5>
            </div>
            <div id="collapse{{ $id }}{{ $section }}" class="collapse" aria-labelledby="heading{{ $id }}{{ $section }}" data-parent="#accordion{{ $id }}" style="">
                <div class="card-body">
                    <pre>{{ $data['text'] }}</pre>
                </div>
            </div>
        </div>
         
    </div>
    @else
        <div class="alert alert-danger" role="alert" >No data received</div>
    @endif
</div>
 
