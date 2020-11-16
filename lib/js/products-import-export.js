var importCounter = 0;
var numColsToDisplay = 5;
var table = document.createElement('table');
table.setAttribute('class','wp-list-table widefat striped fixed');
table.setAttribute('id','csv-display');
var thead = document.createElement('thead');
var tbody = document.createElement('tbody');

jQuery(document).ready(function($){
  console.log(`product-import-export.js is loaded`);

  document.getElementById('the_form').addEventListener('submit', handleFileSelect, false);
  document.getElementById('the_file').addEventListener('change', fileInfo, false);

  $('#download-carriers').click(function(e){
    e.preventDefault();
    console.log('🔔 Clicked #download-carriers');
    var carrierId = $('#carriers').val();
    console.log(`carrierId = ${carrierId}`);

    var downloadUrl = wpvars.downloadUrl + carrierId;
    $.fileDownload( downloadUrl,{
      preparingMessageHtml: 'Downloading your CSV. Please wait...',
      failMessageHtml: 'There was a problem generating your CSV. To fix this problem, simply goto <a href="' + wpvars.permalinkUrl + '" target="_blank">SETTINGS &gt; PERMALINKS</a> (<em>IMPORTANT: Do not change any settings or click any buttons, just visit that page</em>). Then return to this page and attempt your download again.',
    });
  });
});
/* END jQuery(document).ready(); */

/**
 * Displays info from the user's selected file.
 *
 * @param      {obj}  e       The file object.
 */
function fileInfo(e){
  var file = e.target.files[0];
  if (file.name.split(".")[1].toUpperCase() != "CSV"){
    alert('Invalid csv file !');
    e.target.parentNode.reset();
    return;
  } else {
    jQuery(thead).empty();
    jQuery(tbody).empty();
    jQuery('#csv-display').remove();
    document.getElementById('file_info').innerHTML = `<p>File: <code>${file.name}</code> (${file.size} bytes).</p>`;
  }
}

/**
 * Logic after user has selected a file.
 */
function handleFileSelect() {
  // Hide the form and show the upload counter:
  jQuery('#the_form').slideUp();
  jQuery('#uploadstatus').fadeIn();

  var file = document.getElementById("the_file").files[0];

  var reader = new FileReader();
  reader.onload = function(file) {
    var content = file.target.result;
    var csvJSON = Papa.parse( content, {header: true} );

    var header = csvJSON.meta.fields;
    var rows = csvJSON.data;
    window.uploadTotal = rows.length;

    // Add our column headings:
    var tr = document.createElement('tr');
    for( var i = 0; i < numColsToDisplay; i++ ){
      var td = document.createElement('th');
      var heading = header[i];
      td.innerHTML = heading;
      switch( heading ){
        case 'ID':
        case 'Row_ID':
          td.setAttribute('style','width: 10%;');
          break;

        case 'Carrier':
          td.setAttribute('style','width: 20%;');
          break;

        case 'Product':
        case 'Alternate_Product_Name':
          td.setAttribute('style','width: 30%');
          break;
      }
      tr.appendChild(td);
    }
    thead.appendChild(tr);
    table.appendChild(thead);

    // Add the table body:
    table.appendChild(tbody);

    window.uploadTotal = rows.length;
    jQuery('#uploadstatus #uploadtotal').html( uploadTotal );
    for( var i = 0; i < rows.length; i++ ){
      importProduct( rows[i] );
    }

    document.getElementById('list').appendChild(table);
  };
  reader.readAsText(file);
}

/**
 * Imports a product.
 *
 * @param      {number}  product  The product
 */
function importProduct(product){
  jQuery.post(wpvars.restUrl, {product: product},function(response){
    importCounter++;
    var colCount = 0;

    var tr = document.createElement('tr');
    tr.setAttribute('class',`product-row success`);

    for( const [key,value] of Object.entries(product) ){
      if( colCount < numColsToDisplay ){
        var td = document.createElement('td');
        td.innerHTML = value;
        tr.appendChild(td);
      } else if ( colCount === numColsToDisplay ){
        tbody.appendChild(tr);
      }
      colCount++;
    }

    if( importCounter === uploadTotal ){
      jQuery('#uploadstatus').removeClass('notice-warning').addClass('notice-success').html('<p><strong>COMPLETE!</strong> Your upload is complete.</p>');
      jQuery('#the_file').val('');
      window.setTimeout(function(){
        jQuery('#uploadstatus').fadeOut().addClass('notice-warning').html('<p><strong>Importing</strong> <span id="uploadrow">0</span> of <span id="uploadtotal">0</span>. (<em>IMPORTANT: Do not close/reload this window until complete!</em>)</p>');
        jQuery('#the_form').slideDown();
        jQuery('#file_info').html('');
        importCounter = 0;
      }, 3000 );
    } else {
      jQuery('#uploadstatus #uploadrow').html(importCounter);
    }

  });
}
