var importCounter = 0;

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
    var rows = file.target.result.split(/[\r\n|\n]+/);
    var table = document.createElement('table');
    table.setAttribute('class','wp-list-table widefat striped fixed');
    table.setAttribute('id','csv-display');
    var thead = document.createElement('thead');
    var tbody = document.createElement('tbody');
    var headings = [];

    // Process rows:
    window.uploadTotal = rows.length - 1;
    jQuery('#uploadstatus #uploadtotal').html( uploadTotal );

    for (var i = 0; i < rows.length; i++) {

      var tr = document.createElement('tr');
      tr.setAttribute('class',`product-row`);
      tr.setAttribute('id',`product-row-${i}`);
      var rowString = rows[i];
      var firstChar = rowString.slice(0,1);
      var rowData = [];

      if( '"' != firstChar ){
        alert('ERROR: Your CSV must use double quotes (i.e. ") around each field. Please re-export your CSV and try again.');
        break;
      }
      rowString = rowString.slice(0,-1);
      rowString = rowString.slice(1,rowString.length);
      var arr = rowString.split('","');

      // Process fields:
      for (var j = 0; j < arr.length; j++) {
        if (i == 0 && j < 5){
          var td = document.createElement('th');
          switch( j ){
            case 0:
            case 2:
              td.setAttribute('style','width: 10%;');
              break;

            case 1:
              td.setAttribute('style','width: 20%;');
              break;

            case 3:
            case 4:
              td.setAttribute('style','width: 30%');
              break;
          }
        } else if (j < 5){
          var td = document.createElement('td');
          switch(j){
            case 0:
            case 2:
              td.setAttribute('style','text-align: right; white-space: nowrap;');
              break;

            default:
              // nothing
          }
        }

        if( i === 0 ){
          headings.push(arr[j]);
        } else {
          rowData.push(arr[j]);
        }

        if( j < 5 ){
          td.innerHTML = arr[j];
          tr.appendChild(td);
        }
      }

      if( 0 === i ){
        thead.appendChild(tr);
        table.appendChild(thead);
      } else {
        tbody.appendChild(tr);
        importProduct( headings, rowData, i );
      }
    }
    table.appendChild(tbody);

    document.getElementById('list').appendChild(table);
  };
  reader.readAsText(file);
}

/**
 * Imports a Product.
 *
 * @param      {array}  headings  The headings
 * @param      {array}  product   The product
 * @param      {int}    rowId     The row identifier
 */
function importProduct( headings, product, rowId ){
  jQuery.post( wpvars.restUrl, {fields: headings, product: product}, function(response){
    jQuery(`#product-row-${rowId}`).addClass('success');
    importCounter++;

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