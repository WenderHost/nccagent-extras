(function($){
  let tableSelector = ( '' != wpvars.table_class )? '.' + wpvars.table_class : '#' + wpvars.table_id ;
  var table = $( tableSelector ).DataTable({
    lengthMenu: [10,20,40,100],
    columnDefs: [
      {orderable: false, targets: [0,1,2]}
    ],
    pageLength: 10,
    initComplete: function () {
        var thisTable = this;

        this.api().columns().every( function (colIdx) {
          var column = this;
          var parentTable = $(column).closest('table.datatable');

          if( 2 === colIdx ){
            var select = $('<select class="dt-select" data-colId="3"><option value="">Select a State...</option><option value="AL">Alabama</option><option value="AK">Alaska</option><option value="AZ">Arizona</option><option value="AR">Arkansas</option><option value="CA">California</option><option value="CO">Colorado</option><option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="DC">District Of Columbia</option><option value="FL">Florida</option><option value="GA">Georgia</option><option value="HI">Hawaii</option><option value="ID">Idaho</option><option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option><option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option><option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option><option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option><option value="MO">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option><option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NJ">New Jersey</option><option value="NM">New Mexico</option><option value="NY">New York</option><option value="NC">North Carolina</option><option value="ND">North Dakota</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option><option value="OR">Oregon</option><option value="PA">Pennsylvania</option><option value="RI">Rhode Island</option><option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option><option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option><option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option><option value="WI">Wisconsin</option><option value="WY">Wyoming</option></select>')
              .appendTo( $(column.header()) )
              .on( 'change', function () {
                console.log('thisTable ID = ',$(thisTable).attr('id') );
                var parentTableId = $(thisTable).attr('id');
                var parentDataTable = $( '#' + parentTableId ).dataTable().api();
                console.log('searching... value = ', $(this).val());
                parentDataTable
                  .column(colIdx)
                  .search($(this).val())
                  .draw();
              });
          } else {
            var firstOption = ( 1 === colIdx )? 'Select a Carrier...' : 'Select a Product...' ;
            var select = $(`<select class="dt-select" data-colId="${colIdx}"><option class="first-option" value="">${firstOption}</option></select>`)
              .appendTo( $(column.header()) )
              .on( 'change', function () {
                console.log('searching... value = ', $(this).val());
                var parentTableId = $(thisTable).attr('id');
                var parentDataTable = $( '#' + parentTableId ).dataTable().api();
                parentDataTable
                  .column(colIdx)
                  .search($(this).val())
                  .draw();
              });
            column.data().unique().sort().each( function ( d, j ) {
              var value = stripTags(d);
              console.log('Adding: ', value );
              //select.append( '<option value="'+d+'">'+d+'</option>' );
              select.append(`<option value="${value}">${value}</option>`);
            });
          }
        } );
    }
  });

  // Event handling for DataTable <select> elements
  /*
  $('body').on('change','.dt-select', function(){
    var colId = $(this).attr('data-colId');
    var parentTable = $(this).closest('table.datatable');
    var parentTableId = $(parentTable).attr('id');
    var parentDataTable = $('#' + parentTableId).dataTable().api();
    parentDataTable.column(colId).search( $(this).val() ).draw();
  });
  /**/
})(jQuery);

/**
 * Strips HTML tags from supplied HTML
 *
 * @param      {string}  html    The html
 * @return     {string}  Striped text
 */
function stripTags( html ){
  var div = document.createElement('div');
  div.innerHTML = html;
  var text = div.textContent || div.innerText || '';
  return text;
}