jQuery(document).ready(function(){
   jQuery('#csv-up-form').on('submit' , function(event){
    event.preventDefault();
     var formdata  = new FormData(this);
      jQuery.ajax({
         url: csvup_object.ajaxurl,
         data: formdata,
         dataType: "json",
         method: "POST",
         processData: false,
         contentType: false,
         success: function (response){
            console.log(response);
         }
      });

   });
});