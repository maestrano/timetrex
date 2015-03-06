(function( $ ) {

    $.jgrid.extend( {

        invisibleCheckBox: function() {
            return this.each( function() {
//                 var gridId = $(this).attr("id")
//                 var grid = $("#gbox_"+ gridId);
//                 grid.find("#"+gridId+"_cb").css("display","none");
////                 alert("td[aria-describedby="+gridId+"_cb]")
//                alert( grid.find("td[aria-describedby=grid_cb]").length )
//                 $(grid.find(".jqgfirstrow").find("td")[0]).css("display","none");
            } )

//             alert(this.find("#grid_cb").length);
        }
    } );

})( jQuery );
