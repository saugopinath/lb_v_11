<script type="text/javascript">
$(document).ready(function(){
    $(".aadhar-text").hide();
    $(".aadhar-text-modal").hide();
    $("#submitting").hide();
    $("#submit_loader").hide();
    $("#passport_image_view").hide(); 
    $("#spouse_section").hide(); 
  var old_districtValue="";
  var old_assemblyValue="";
  var old_blockValue="";
  var old_gpValue="";
  var old_urbanValue="";
  var event = new Event('change');
  @if (old('district'))
  old_districtValue={{old('district')}};
  @endif
  @if (old('asmb_cons'))
  old_assemblyValue={{old('asmb_cons')}};
  @endif
  @if (old('urban_code'))
  old_urbanValue={{old('urban_code')}};
  @endif
  @if (old('block'))
  old_blockValue={{old('block')}};
  @endif
  @if (old('gp_ward'))
  old_gpValue={{old('gp_ward')}};
  @endif
  @if (old('district'))
  var event = new Event('change');
  $("#district").val(old_districtValue);
  var element = document.getElementById('district');
  element.dispatchEvent(event);

  $("#asmb_cons").val(old_assemblyValue);

  $("#urban_code").val(old_urbanValue);
  var element1 = document.getElementById('urban_code');
  element1.dispatchEvent(event);

  $("#block").val(old_blockValue);
  var element2 = document.getElementById('block');
  element2.dispatchEvent(event);

  $("#gp_ward").val(old_gpValue);
  @endif
  var cur_step_id=$('#cur_step_id').val(); 
  var next_step_id=$("#next_step_id").val();
  var pre_step_id=$("#pre_step_id").val();
  showhidePreNext(cur_step_id,next_step_id,pre_step_id);
});

 $("#doc_{{$profile_img}}").change(function() {
       // $("#passport_image_view").show();
          readURL(this);
        });
     function readURL(input) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          
          reader.onload = function(e) {
              $('#passport_image_view').attr('src', e.target.result);
             $('#passport_image_view_modal').attr('src', e.target.result);
          }
          
          reader.readAsDataURL(input.files[0]);
        }
      }
function showhidePreNext(cur_step_id,next_step_id,pre_step_id){
  var total_step=$('#total_step').val();
if(cur_step_id==1){
  $('#btn_prev').hide();
}
else{
  $('#btn_prev').show();
}
if(cur_step_id==total_step){
  $('#btn_submit_preview').show();
  $('#btn_next').hide();
}
else{
  $('#btn_submit_preview').hide();
  $('#btn_next').show();
}
}
 $('.next').click(function(){ 
  var cur_step_id=$('#cur_step_id').val(); 
  var next_step_id=$("#next_step_id").val();
  var pre_step_id=$("#pre_step_id").val();
  // console.log('cur_step_id:'+cur_step_id);
   var valid=1;
   var IsRequiredText='@lang('lang.IsRequired')';
  $("#formElements_"+cur_step_id).children('.row').each(function() 
        { 
          $(this).children('.form-group').each(function() 
           { 
           var LabelClass = $(this).find("label").attr('class');
           var LabelText = $(this).find("label").html();
           var LabelTextError=LabelText+" "+IsRequiredText;
           var inputOrSelectval = $(this).find("input").val();
           
           var id = $(this).find("input").attr('id');
           //console.log('id='+id);
          // console.log('val='+inputOrSelectval);
           if(id===undefined){
           var id = $(this).find("select").attr('id');
           }
           //console.log('LabelClass:'+LabelClass);
           //console.log("inputOrSelectval:"+inputOrSelectval);
           if(inputOrSelectval===undefined){
            //console.log('inputOrSelectval:'+inputOrSelectval);
            var inputOrSelectval = $(this).find("select").val();
           }
            //console.log('inputOrSelectval:'+inputOrSelectval);
           // console.log("id:"+id);
           if(LabelClass=='required-field'){
             if(inputOrSelectval=='' || inputOrSelectval=='undefined'){
              $('#'+id).addClass('has-error');
              $('#error_'+id).text(LabelTextError);
              valid=0;
             }
             else{
              $('#'+id).removeClass('has-error');
              $('#error_'+id).text('');
              //valid=0;
             }
           }
          });
  }); 
  var valid=1;
  if(valid){
    $('#tab_'+cur_step_id).removeClass('active active_tab1');
    $('#tab_'+cur_step_id).removeAttr('href data-toggle');
    $('#tabForm_'+cur_step_id).removeClass('active');
    $('#tab_'+cur_step_id).addClass('inactive_tab1');
    $('#tab_'+next_step_id).removeClass('inactive_tab1');
    $('#tab_'+next_step_id).addClass('active_tab1 active');
    $('#tab_'+next_step_id).attr('href', '#tabForm_'+next_step_id);
    $('#tab_'+next_step_id).attr('data-toggle', 'tab');
    $('#tabForm_'+next_step_id).addClass('active in');
    var new_pre_step_id=parseInt(cur_step_id);
    var new_cur_step_id=parseInt(cur_step_id)+1;
    var new_next_step_id=parseInt(cur_step_id)+2;
    $('#cur_step_id').val(new_cur_step_id); 
    $("#next_step_id").val(new_next_step_id);
    $("#pre_step_id").val(new_pre_step_id);
    showhidePreNext(new_cur_step_id,new_next_step_id,new_pre_step_id);
  }
 });
 $('.prev').click(function(){ 
  var cur_step_id=$('#cur_step_id').val(); 
  var next_step_id=$("#next_step_id").val();
  var pre_step_id=$("#pre_step_id").val();
  
    $('#tab_'+cur_step_id).removeClass('active active_tab1');
    $('#tab_'+cur_step_id).removeAttr('href data-toggle');
    $('#tabForm_'+cur_step_id).removeClass('active');
    $('#tab_'+cur_step_id).addClass('inactive_tab1');
    $('#tab_'+pre_step_id).removeClass('inactive_tab1');
    $('#tab_'+pre_step_id).addClass('active_tab1 active');
    $('#tab_'+pre_step_id).attr('href', '#tabForm_'+pre_step_id);
    $('#tab_'+pre_step_id).attr('data-toggle', 'tab');
    $('#tabForm_'+pre_step_id).addClass('active in');
    var new_cur_step_id=parseInt(cur_step_id)-1;
    var new_pre_step_id=parseInt(cur_step_id)-2;
    var new_next_step_id=parseInt(cur_step_id);
    $('#cur_step_id').val(new_cur_step_id); 
    $("#next_step_id").val(new_next_step_id);
    $("#pre_step_id").val(new_pre_step_id);
    showhidePreNext(new_cur_step_id,new_next_step_id,new_pre_step_id);
  
 });
 $('#btn_submit_preview').click(function() {
  $('#register_form input, #register_form select').each(
    function(index){  
        var input = $(this);
        var type=input.prop('type');
        
        if(type!='hidden'){
          var id=input.attr('id');
          var className=input.attr('class');
          //console.log(id);
          if(type=='select-one')
          var val=$("#"+id+" :selected").text()
          else if(type=='checkbox'){
            var selectedArr = new Array();
            var n1=0;
            var n1 = jQuery("."+className+":checked").length;
            console.log('length:'+n1);
            if (n1 > 0){
            
                jQuery("."+className+":checked").each(function(){
                  selectedArr.push( $(this).val());
                });
                //console.log(selectedArr);
                var val=selectedArr; 
            } 
            else{
              var val='NA'; 
            }
            
          }
          else
          var val=input.val();
          $('#modal_'+id).text(val); 
        }
        //console.log('Id: ' + input.attr('id') + 'Name: ' + input.attr('name') + 'Value: ' + input.val());
    }
);
$('#modal_hidden_age').text($("#hidden_age").val()); 
$("#confirm-submit").modal("show");
 });
 $('.modal-submit').on('click',function(){
    $(".modal-submit").hide();
    $("#submitting").show();
    $("#submit_loader").show();
});
</script>