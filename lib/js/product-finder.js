let tableSelector = ( '' != wpvars.table_class )? '.' + wpvars.table_class : '#' + wpvars.table_id ;
let init = 0;
const dtSelects = [];
let appendedValues = [];

console.log('API: ', wpvars.productFinderApi );

(function($){
  // START DT Options
  const dtOptions = {
    responsive: false,
    lengthMenu: [10,20,40,100],
    pageLength: 20,
    ajaxSource: wpvars.productFinderApi,
    stateSave: true,
    columns: [
      {
        className: 'details-control',
        data: null,
        orderable: false,
        defaultContent: '',
        visible: false
      },
      {
        data: 'states',
        orderable: false,
        visible: false
      },
      {
        data: function(d){
          return `<a href="${d.product.url}" target="_blank">${d.product.alt_name}</a> <span>${d.product.name}</span>`;
        },
        createdCell: function(td, cellData, rowData, row, col){
          var html = $.parseHTML(cellData);
          $(td).html(html[0]);
        },
        orderable: false,
        visible: false
      },
      {
        data: function(d){
          return `<a href="${d.carrier.url}" target="_blank">${d.carrier.name}</a>`;
        },
        orderable: false,
        visible: false
      },
      {
        data: function(d){
          return `<h4 class="row-title"><a href="${d.product.url}">${d.carrier.name} ${d.product.alt_name}</a></h4><div class="states">${d.states}</div><a href="#" class="details-link">See more information right here <i class="fas fa-chevron-down"></i></a>`;
        },
        orderable: false
      }
    ],
    language: {
      'zeroRecords': 'Select one or more options to find products.'
    },
    initComplete: function () {
      var thisTable = this;
      const totalColumns = 5;

      this.api().columns().every( function (colIdx) {
        var column = this;
        if( 0 === colIdx )
          return;

        // Add all dtSelects to the last column
        if( colIdx === (totalColumns - 1) ){
          for( var i = 0; i < dtSelects.length; i++ ){
            if( typeof dtSelects[i] !== 'undefined' )
              dtSelects[i].appendTo( $(column.header()) );
          }
          return;
        }

        if( colIdx === (totalColumns - 4) ){
          var select = $(wpvars.stateOptions)
            .on( 'change', function () {
              // Reset the table when all <select/> values are empty
              var filtered = isTableFiltered();
              if( ! filtered )
                resetCarriersProductsTable();

              // Run the search...
              var parentTableId = $(thisTable).attr('id');
              var parentDataTable = $( '#' + parentTableId ).dataTable().api();
              var parentSearch = parentDataTable.search();
              if( 'InitialSearchValueToZeroOutResults' == parentSearch && '' != $(this).val() ){
                parentDataTable.search('');
              }

              // Get `state` term_id for retrieving the Marketer
              var optionValue = $(this).val();
              if( 0 < optionValue.indexOf('-') ){
                const valueArray = optionValue.split('-');
                const state = valueArray[0];
                const termId = valueArray[1];
                loadMarketer( termId );
                parentDataTable
                  .column(colIdx)
                  .search(state)
                  .draw();
              } else {
                parentDataTable
                  .column(colIdx)
                  .search(optionValue)
                  .draw();
              }
            });

            dtSelects[colIdx] = select;
        } else if( 0 < colIdx ){
          var firstOption = ( 2 === colIdx )? 'Select a Product...' : 'Select a Carrier...' ;
          var select = $(`<select class="dt-select" id="option_${colIdx}" data-colId="${colIdx}"><option class="first-option" value="">${firstOption}</option></select>`)
          .on( 'change', function () {
            // Reset the table when all <select/> values are empty
            var filtered = isTableFiltered();
            if( ! filtered )
              resetCarriersProductsTable();

            // Reset table.search when we start searching
            var parentTableId = $(thisTable).attr('id');
            var parentDataTable = $( '#' + parentTableId ).dataTable().api();
            var parentSearch = parentDataTable.search();
            if( 'InitialSearchValueToZeroOutResults' == parentSearch && '' != $(this).val() ){
              parentDataTable.search('');
            }

            // Run the search...
            parentDataTable
              .column(colIdx)
              .search($(this).val())
              .draw();
          });

          // Add the unique options to the <select>
          column.data().unique().sort().each( function ( data, index ) {
            if( 3 === colIdx ){
              var value = stripTags(data);
              select.append(`<option value="${value}">${value}</option>`);
            } else if( 2 === colIdx ) {
              var html = $.parseHTML(data);
              var value = html[2].innerHTML;
              if( 0 > appendedValues.indexOf(value) && '' != value ){
                select.append(`<option value="${value}">${value}</option>`);
                appendedValues.push(value);
              }
            }

            /**
             * Re-sort the Products drop down with Medicare at the top:
             */
            if( ( index + 1 ) === this.length && 2 === colIdx ){
              var options = select[0].options;
              var medicareArray = [];

              // Add any `Medicare` options to our tmp `medicareArray`
              for( var i = 0; i < options.length; i++ ){
                var text = options[i].text;
                var value = options[i].value;
                if( -1 != text.indexOf('Medicare') )
                  medicareArray.push({text: text, value: value, index: i});
              }

              // Insert `Medicare` options at the top of `options`
              if( 0 < medicareArray.length ){
                // Remove original `Medicare` options
                for( var i = medicareArray.length - 1; i >= 0; i-- ){
                  options.remove( medicareArray[i]['index'] );
                }
                // Add `Medicare` options before option[1]
                for( var i = medicareArray.length - 1; i >= 0; i-- ){
                  var op = new Option(medicareArray[i]['text'], medicareArray[i]['value']);
                  options.add(op,1);
                }
              } // if( 0 < medicareArray.length )

            } // Re-sort the Products drop down with Medicare at the top:

          });

          //dtSelects[colIdx] = `<label>Testing ${select}</label>`;
          dtSelects[colIdx] = select;
        }
      });

      var state = thisTable.api().state()
      var savedState = []
      savedState.push( state.columns[1].search.search )
      savedState.push( state.columns[2].search.search )
      savedState.push( state.columns[3].search.search )
      console.log("savedState:\n" + '👉 State = ', savedState[0], "\n👉 Product = ", savedState[1], "\n👉 Carrier = ", savedState[2] )

      /**
       * Add Select2 to each <select/>
       */
      //*
      $('.dt-select').each(function(index){
        var firstOption = $(this).find('.first-option').html()
        var labelName = firstOption.replace('Select a','')
        labelName = labelName.replace('...','')
        var select2 = $(this).select2({
          allowClear: true,
          placeholder: firstOption
        })
        $(select2).wrap(`<label>${labelName}:</label>`)
        // Set the Select2 with the saved state:
        var id = $(select2).attr('id')
        var state = table.state()
        var value = ''
        switch(index){
          case 0:
          // The `State` option is a combination of the 2 letter abbr + the WP Term_ID.
            if( typeof state.columns[1].search.search !== 'undefined' )
              value = state.columns[1].search.search + '-' + wpvars.stateOptionData[state.columns[1].search.search]
            break;

          case 1:
            value = state.columns[2].search.search
            break;

          case 2:
            value = state.columns[3].search.search
            break;
        }
        if( '' !== value && '-undefined' != value ){
          console.log(`Setting ${id} to ${value}`)
          $('#' + id ).val(value).trigger('change')
        }
      });
      /**/
    }
  }
  // END DT Options

  // Add listener for OPEN/CLOSE child row button
  $(`#${wpvars.table_id}`).on('click', 'td.details-control', function(){
    var tr = $(this).closest('tr');
    var row = table.row( tr );

    if( row.child.isShown() ){
      row.child.hide();
      tr.removeClass('shown');
    } else {
      var d = row.data();
      row.child( $(`<tr><td colspan="4"><h5>${d.carrier.name} ${d.product.alt_name}</h5>${d.description}</td></tr>`) ).show();
      tr.addClass('shown');
    }
  });
  $(`#${wpvars.table_id}`).on('click', 'a.details-link', function(e){
    e.preventDefault();
    var tr = $(this).closest('tr');
    var row = table.row( tr );

    if( row.child.isShown() ){
      row.child.hide();
      $(this).removeClass('shown')
      $(this).children('i.fas').toggleClass('fa-chevron-down fa-chevron-up')
      $(tr).removeClass('shown')
    } else {
      var d = row.data();
      row.child( $(`<tr><td class="child-details" colspan="4">${d.description}<p>Permalink: <a href="${d.product.url}">${d.carrier.name} ${d.product.alt_name}</a></p></td></tr>`) ).show();
      $(this).addClass('shown')
      $(this).children('i.fas').toggleClass('fa-chevron-down fa-chevron-up')
      $(tr).addClass('shown')
    }
  });

  // Add listener for `reset form` link
  $(`.product-finder-header`).on('click', 'a#reset-form', function(e){
    e.preventDefault()
    resetSelects()
  })

  // Initialize the DataTable
  var table = $( tableSelector ).DataTable(dtOptions)

  // Scroll to Top Upon Page Change
  table.on('page.dt', function(){
    $('html, body').animate({
      scrollTop: $('.dataTables_wrapper').offset().top
    }, 'slow')
  })

  // Check for saved state, don't unset search value if saved state
  var state = table.state()
  var savedStateOpt = state.columns[1].search.search
  var savedProductOpt = state.columns[2].search.search
  var savedCarrierOpt = state.columns[3].search.search
  if( '' == savedStateOpt && '' == savedProductOpt && '' == savedCarrierOpt ){
    table.search('InitialSearchValueToZeroOutResults').draw()
  } else {
    table.draw()
  }

  var data = table.data();

  /**
   * Update the text for zeroResults after user makes filter selections.
   */
  table.on('draw.dt', function(){
    var empty = $('#datatable').find('.dataTables_empty');
    if( empty ){
      var filtered = isTableFiltered();
      if( filtered ){
        empty.html('Your search did not return any results. Please try again.')
      } else {
        if( '' !== wpvars.helpGraphic ){
          empty.html('');
          empty.css({'background-image': `url(${wpvars.helpGraphic})`, 'background-repeat': 'no-repeat', 'background-size': 'contain'});
        } else {
          empty.html('Select one or more options to find products.');
        }
      }
    }
  });

  function loadMarketer( termId ){
    //getUrl = wpvars.marketerUrl + '?per_page=1&orderby=rand&state=' + termId;
    var getUrl = wpvars.marketerUrl;
    console.log('🔔 loadMarketer(' + termId + ') calling ' + getUrl);
    $.get( getUrl, { per_page: 1, orderby: 'rand', state: termId } )
     .done( function(response){
      if( 0 === response.length ){
        console.log('No marketer found for termId = ' + termId );
      } else {
        const marketer = response[0].team_member_details;
        console.log(`marketer = `, marketer);
        $('#ncc-staff').html(`<div class="marketer row"><div class="col-xs team-member-photo"><div class="stretchy-wrapper"><div class="photo" style="background-image: url(${marketer.photo});"></div></div></div><div class="col-xs-10 team-member-details"><h3 class="team-member-name"><a href="${marketer.permalink}">${marketer.name}</a></h3><h4 class="team-member-title">${marketer.states[termId]} ${marketer.title} • ${marketer.phone} • <a href="mailto:${marketer.email}">${marketer.email}</a></h4><div class="team-member-read-more"><a href="${marketer.permalink}">Read testimonials from ${marketer.firstname}'s agents</a></div></div></div>`);
      }
    });
  }

  /**
   * Resets our Carriers and Products table
   */
  function resetCarriersProductsTable(){
    console.log('🚨 RESETTING Carriers & Products table:');
    table.search('InitialSearchValueToZeroOutResults').draw();
    $('#ncc-staff').html('');
  }

  function resetSelects(){
    $('.dt-select').each(function(index){
      $(this).val('').trigger('change')
    });
  }

  /**
   * Checks our drop down options to see if the table is filtered.
   *
   * @return     {boolean}  True if table filtered, False otherwise.
   */
  function isTableFiltered(){
    var allSelectValues = '';
    $('.dt-select').each(function(i){
      allSelectValues+= $(this).val();
    });
    var filtered = ( '' == allSelectValues || null == allSelectValues )? false : true;
    if( filtered ){
      $('#reset-form').fadeIn()
    } else {
      $('#reset-form').fadeOut()
    }
    return filtered;
  }
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