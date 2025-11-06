<script type="text/javascript">
$(".rationCardEpic").on('blur',function(){ 
   var id= this.id;
   var val= this.value;
   epicOrvoter(id,val);
});
function epicOrvoter(id,value){
 if(id=='ration_card_no'){
    var otherId='epic_voter_id';
     $('#error_epic_voter_id').text('');
     $('#epic_voter_id').removeClass('has-error');
      if(value!=''){
      $("label[for='" +otherId + "']").removeClass('required-field');
    //  $("#"+otherId).removeClass('required-field');
    }
    else{
      $("label[for='" +otherId + "']").addClass('required-field');
      //$("#"+otherId).addClass('required-field');
    }
   }
   else if(id=='epic_voter_id'){
    var otherId1='ration_card_no';
    var otherId2='ration_card_cat';
     $('#error_ration_card_cat').text('');
     $('#ration_card_cat').removeClass('has-error');
     $('#error_ration_card_no').text('');
     $('#ration_card_no').removeClass('has-error');
    if(value!=''){
     $("label[for='" +otherId1 + "']").removeClass('required-field');
     $("label[for='" +otherId2 + "']").removeClass('required-field');
  //  $("#"+otherId).removeClass('required-field');
  }
  else{
    $("label[for='" +otherId1 + "']").addClass('required-field');
    $("label[for='" +otherId2 + "']").addClass('required-field');
    //$("#"+otherId).addClass('required-field');
  }
   }
   
  //$("label[for='" +otherId + "']").addClass("TESTTTT");
}
$(document).ready(function(){
  var scheme_shorCode='{{$scheme_shorCode}}';
  if(scheme_shorCode=='purohit_monthly' || scheme_shorCode=='purohit_housing'){
     var ration_card_no=$('#ration_card_no').val();
     if(ration_card_no!='')
     epicOrvoter('ration_card_no',ration_card_no);
     else {
       var epic_voter_id=$('#epic_voter_id').val();
       epicOrvoter('epic_voter_id',epic_voter_id);
     }
    // var epic_voter_id=$('#epic_voter_id').val();
  }
});
</script>