 $(document).ready(function(e) {

     $(document).on('change', '#files', function(e) {
         $($('#file-input').parent()).submit()
     });


     $($('#file-input').parent()).on('submit', (function(e) {
         e.preventDefault();
         var dt = new FormData(this)
         $.ajax({
             url: urls.visionapi,
             type: "POST",
             data: new FormData(this),
             contentType: false,
             cache: false,
             processData: false,
             beforeSend: function() {
                 $("#files").prop('disabled', true);
                 $('#status ').show();
                 $('#status .alert ,#status .alert-danger').hide();
                 $('#status .alert-danger ').hide();
                 $('#status .alert-success ').show();
                 $('#status .alert-success ').html("Please wait while processing image :<strong>" + dt.entries().next().value[1].name +"</strong>");


             },
             success: function(response) {
                 $("#files").prop('disabled', false);

                 if (response && response.data) {
                     $('#status ').hide();
                     $('#result ').show();


                     $('#result .bd-box').removeClass('bd-box-active');
                     $('#result .card-body').prepend(response.data);
                     
                     setTimeout(function() {
                         $('.grid').packery({
                             itemSelector: '.grid-item',
                             gutter: 10
                         });
                     }, 1000);
                    
                 } else {

                     $('#status ').show();
                     $('#status .alert ').hide();
                     $('#status .alert-danger ').show();
                     $('#status .alert-success ').hide();
                     $('#status .alert-danger ').html("Invalid response");
                 }
             },
             error: function(result, status, message) {
                 $("#files").prop('disabled', false);
                 $('#status ').show();
                 $('#status .alert ').hide();
                 $('#status .alert-success ').hide();
                 $('#status .alert-danger ').show();
                 if (message && status != 'status') {
                     $('#status .alert-danger ').html("Error: " + message);
                 } else if (result && result.message) {
                     $('#status .alert-danger ').html("Error: " + result.message);
                 } else {
                     $('#status .alert-danger ').html("Unlnown error");

                 }
             }
         });
     }));
 });
