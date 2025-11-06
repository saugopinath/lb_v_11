<script type="text/javascript">
$(document).ready(function(){
  var rural_urban='{{$rural_urban}}';
  var district_name='{{$district_name}}';
  var assembly_name='{{$assembly_name}}';
  var rural_urban='{{$rural_urban}}';
  var block_name='{{$block_name}}';
  var gp_name='{{$gp_name}}';
  $("#modal_state").text("West Bengal");
  $("#modal_urban_code").text(rural_urban);
  $("#modal_district").text(district_name);
  $("#modal_asmb_cons").text(assembly_name);
  $("#modal_block").text(block_name);
  $("#modal_gp_ward").text(gp_name);
  var aadhar_no=$("#modal_aadhar_no").text();
  var aadhar_no=$.trim(aadhar_no);
  if(aadhar_no!=''){
    //console.log(aadhar_no);
    $(".aadhar-text").show();
  }
  else{
    $(".aadhar-text").hide();
  }
});
</script>