// console.log( ns_category_widget_ajax_object) ;

jQuery(document).ready( function($){
  //Some event will trigger the ajax call, you can push whatever data to the server, simply passing it to the "data" object in ajax call

  jQuery('body').on('change','.nscw-taxonomy',function(){

    var tthis = $(this);

    var our_data = new Object();
    our_data.action = 'populate_categories';
    our_data.taxonomy = $(this).val();
    our_data.name = $(this).data('name');
    our_data.id = $(this).data('id');

    jQuery.ajax({
      url: ns_category_widget_ajax_object.ajaxurl,
      type: 'POST',
      data: our_data,
      success: function( result ){
        //Do something with the result from server
        if ( 1 == result.status) {
          // console.log( result );
          our_html = result.html;
          var target = $(tthis).parent().parent().find('.nscw-cat-list');
          $(target).html(our_html);
          // console.log(  );


        }
      }
    });

  }); // end change .nscw-taxonomy



  //////////////////////////////
});
