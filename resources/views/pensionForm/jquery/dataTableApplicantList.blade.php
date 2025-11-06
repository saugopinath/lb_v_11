<script type="text/javascript">
var table=""; 
function fill_datatable(){
  var scheme_code=$("#scheme_code").val();
  var district_code=$("#district_code_fk").val();
  var application_type=$("#application_type").val();
  var district_code_fk=$("#district_code_fk").val();
  var from_date=$("#from_date").val();
  var to_date=$("#to_date").val();
  var check_status=$("#check_status").val();
  var mapping_column=$("#mapping_column").val();
  var mapping_column_val=$("#mapping_column_val").val();
  var role_code=$("#role_code").val();
  var is_bulk=$("#is_bulk").val();
  var export_excel=$("#export_excel").val();
  if(scheme_code!='' && role_code!='' && application_type!='' && check_status!='' && mapping_column!='' && mapping_column_val!=''){
      if(table!=null && table != ''){
    $('#example').DataTable().destroy();
   
  }
  // alert(application_type);
    //alert(is_bulk);
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
   var columns=[];
   columns.push({ "data": "check"});
    
     columns.push({ "data": "id","defaultContent":"" },
        { "data": "full_name","defaultContent":"" },
        { "data": "ben_age"},
        { "data": "gender"},   
        { "data": "assembly_name"}
     );
       if(application_type=='R'){
       columns.push({ "data": "comments"});
     } 
    columns.push({ "data": "action"});
   // console.log(columns);
    var ExportExcel='@lang('lang.ExportExcel')';

    table=$('#example').DataTable( { 
      "paging": true,
      "pageLength":10,
      "lengthMenu": [[10, 25, 50, -1], [10, 25, 50]],
      "serverSide": true,
      "deferRender": true,
      "processing":true,
      "bRetrieve": true,
      "ordering":false,
      "searching": true,
      "scrollX": false,
      "language": {
        "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>',
         "search": 'Search By Application Id/Bank Account No./Block or Municipality Name'
      },
      "ajax": {
        url: "{{ url('ajaxgetApplicationList') }}",
        type: "GET",
        data:{
          scheme_code:scheme_code,
          district_code_fk:district_code,
          role_code:role_code,
          application_type:application_type,
          mapping_column:mapping_column,
          mapping_column_val:mapping_column_val,
          check_status:check_status,
          from_date:from_date,
          to_date:to_date,
          export_excel:export_excel,
          is_bulk:is_bulk
        },
        complete: function (json, type) {
          if (type == "parsererror") {
             //location.reload();
          }
          else{
              var rowCount = $('#example >tbody >tr').length;
              //alert(rowCount);
              if(rowCount>=1){
                 $("#btn-export-excel").show();
              }
             else{
                $("#btn-export-excel").hide();
             }
             if(is_bulk && application_type=='P'){
             // var rowCount = $('#example >tbody >tr').length;
              //alert(rowCount);
              if(rowCount>=1){
                 $(".clsbulk_blkchange").show();
                  $(".checkSingle").click(function () {
                  setButtn();
                  //setButtn();
                });
                 
              }
                 else{
                    $(".clsbulk_blkchange").hide();
                    //$("#btn-export-excel").hide();
                 }
              }
              else{
                    $(".clsbulk_blkchange").hide();
                   // $("#btn-export-excel").hide();
              }
            
          }
        }       
      } ,
      "columns": columns
    }); 
    //console.log( table.data() );
  }
  else{
    alert('Please Select Proper Selection from above selections');
  }
}
</script>