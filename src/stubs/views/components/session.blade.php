@if ( session('message') )
   <script>
       new Noty({
           text: "{{session('message')}}",
           type: 'success',
           theme: 'bootstrap-v4',
           timeout: 2500,
           layout: 'topRight'
       }).show();

   </script>
@endif

@if ( session('error') )
   <script>
       new Noty({
           text: "{{session('error')}}",
           type: 'error',
           theme: 'bootstrap-v4',
           timeout: 2500,
           layout: 'topRight'
       }).show();

   </script>
@endif
