let tableSelector = ( '' != wpvars.table_class )? '.' + wpvars.table_class : '#' + wpvars.table_id ;
let init = 0;
const dtSelects = [];
let appendedValues = [];
let selectedState = '';

console.log('API: ', wpvars.productFinderApi );

(function($){
  // START DT Options
  const dtOptions = {
    responsive: false,
    lengthMenu: [10,40,60,100],
    pageLength: 40,
    ajaxSource: wpvars.productFinderApi,
    stateSave: true,
    stateSaveCallback: function(settings,data){
      localStorage.setItem(`DataTables_datatable_/${wpvars.product_finder_slug}/`, JSON.stringify(data));
    },
    stateLoadCallback: function(settings,callback){
      const savedState = JSON.parse( localStorage.getItem(`DataTables_datatable_/${wpvars.product_finder_slug}/`) );
      return savedState;
    },
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
          return `<h2 class="row-title"><a href="#" class="details-link">${d.carrier.name} ${d.product.alt_name}<i class="fas fa-chevron-down"></i></a></h2>`;
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
                console.log(`Selected State ${termId} = `, wpvars.stateLibrary[state])
                selectedState = wpvars.stateLibrary[state];
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
              var otherOptions = [];

              // Add any `Medicare` options to our tmp `medicareArray`
              for( var i = 0; i < options.length; i++ ){
                var text = options[i].text;
                var value = options[i].value;
                if( -1 != text.indexOf('Medicare') || -1 != text.indexOf('Prescription') ){
                  medicareArray.push({text: text, value: value, index: i});
                } else {
                  if( -1 === text.indexOf('Select'))
                    otherOptions.push({text: text, value: value, index: i});
                }
              }

              // Sort/Alphabetize our options:
              medicareArray.sort( (a,b) => (a.text < b.text) ? 1 : -1 )
              otherOptions.sort( (a,b) => (a.text < b.text) ? 1 : -1 )

              // Insert `Medicare` options at the top of `options`
              if( 0 < medicareArray.length ){

                // 1) Remove all options
                for( var i = options.length - 1; i >= 0; i-- ){
                  if( -1 === options[i].text.indexOf('Select') )
                    options[i] = null;
                }

                // 2) Add `Medicare` options first
                for( var i = medicareArray.length - 1; i >= 0; i-- ){
                  var op = new Option(medicareArray[i]['text'], medicareArray[i]['value']);
                  options.add(op)
                }

                // 3) Restore other options
                for( var i = otherOptions.length - 1; i >= 0; i-- ){
                  var op = new Option(otherOptions[i]['text'], otherOptions[i]['value']);
                  options.add(op)
                }
              } // if( 0 < medicareArray.length )

            } // Re-sort the Products drop down with Medicare at the top

          });
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
            console.log('selectedState =', wpvars.stateLibrary[ state.columns[1].search.search ])
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
          //console.log(`Setting ${id} to ${value}`)
          $('#' + id ).val(value).trigger('change')
        }
      });
      /**/
    }
  }
  // END DT Options

  // Add listener for OPEN/CLOSE child row button
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
      var state = table.state();
      var selectedState = wpvars.stateLibrary[ state.columns[1].search.search ] ;
      var d = row.data();

      var statesReviewDate = '';
      if( typeof d.states_review_date !== 'undefined' && '' !== d.states_review_date ){
        statesReviewDate = `<p class="review-date">Last review date: ${d.states_review_date}`;
        if( typeof d.plan_year !== 'undefined' && '' !== d.plan_year )
          statesReviewDate+= ` &ndash; <span class="plan-year">Plan Year ${d.plan_year}</span>`;
        statesReviewDate+= `</p>`;
      }

      var descReviewDate = '';
      /*
      if( typeof d.desc_review_date !== 'undefined' && '' !== d.desc_review_date ){
        descReviewDate = `<p class="review-date">Current as of ${d.desc_review_date}`;
        if( typeof d.plan_year !== 'undefined' && '' !== d.plan_year )
          descReviewDate+= ` &ndash; <span class="plan-year">Plan Year ${d.plan_year}</span>`;
        descReviewDate+= `</p>`;
      }
      */

      var issueAges = ( '' !== d.lower_issue_age && '' !== d.upper_issue_age )? `<p>Issue ages ${d.lower_issue_age}&ndash;${d.upper_issue_age}.</p>` : '' ;
      var extraLinks = `<h3>Quick Links</h3><ul><li><a href="${d.online_contracting_url}">${d.carrier.name} Contracting Online</a></li><li><a href="${d.carrier.url}">${d.carrier.name} Main Page with All Products</a></li><li><a href="${d.product.url}">View this plan information as a web page</a></li></ul>`;
      var medicareNote = ( -1 < d.product.name.indexOf('Medicare') || -1 < d.product.name.indexOf('Prescription Drug Plan') || -1 < d.product.name.indexOf('PDP') )? `<p><em>Some information may vary by state. <a href="../tools/medicare-quote-engine/">See state-specific information and rates</a>.</em></p>` : '';

      var stateAvailabilityHeading = ( typeof selectedState !== 'undefined' )? `State Availability in addition to ${selectedState}` : 'State Availability' ;

      var productKitVerbiage = '';
      if( typeof selectedState !== 'undefined' ){
        productKitVerbiage = `We’ll email you a kit with brochures, commissions, and rates for ${selectedState} or another state.`;
      } else {
        productKitVerbiage = `We’ll email you a kit with brochures, commissions, and rates, all specific to the state of your choice.`;
      }

      row.child( $(`<tr><td class="child-details" colspan="4"><p><a href="${d.product.url}">View this information as a web page.</a></p><h3>${stateAvailabilityHeading}</h3>${statesReviewDate}<div class="states">${d.states}</div><h3>Plan Information</h3>${descReviewDate}${issueAges}${d.description}${medicareNote}${extraLinks}<div class="kit-request"><h3>Request a Product Kit for ${d.carrier.name} ${d.product.alt_name}</h3><p class="kit-details">${productKitVerbiage}</p><p><a class="elementor-button" href="${d.kit_request_url}">Request a Kit</a></p></div><!-- .kit-request --></td></tr>`) ).show();
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
  var savedStateOpt = ( state )? state.columns[1].search.search : null ;
  var savedProductOpt = ( state )? state.columns[2].search.search : null ;
  var savedCarrierOpt = ( state )? state.columns[3].search.search : null ;
  if(
    (null === savedStateOpt && null === savedProductOpt && null === savedCarrierOpt)
    ||
    ('' === savedStateOpt && '' === savedProductOpt && '' === savedCarrierOpt)
  ){
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

  /**
   * Loads a marketer.
   *
   * @param      {string}  termId  The term identifier
   */
  function loadMarketer( termId ){
    var getUrl = wpvars.marketerUrl;
    $.get( getUrl, { per_page: 1, orderby: 'rand', state: termId } )
     .done( function(response){
      if( 0 === response.length ){
        console.log('No marketer found for termId = ' + termId );
      } else {
        const marketer = response[0].team_member_details;
        console.log(`marketer = `, marketer);
        marketer.chat = '';

        if( marketer.chat_query_parameter )
          marketer.chat = ` • <a href="#" class="chat-link" data-chat-query-parameter="${marketer.chat_query_parameter}">Chat with ${marketer.firstname}</a>`;

        $('#ncc-staff').html(`<div class="marketer row"><div class="col-xs team-member-photo"><a href="${marketer.permalink}"><div class="stretchy-wrapper"><div class="photo" style="background-image: url(${marketer.photo});"></div></div></a></div><div class="col-xs-10 team-member-details"><h3 class="team-member-name"><a href="${marketer.permalink}">${marketer.name}</a></h3><h4 class="team-member-title">${marketer.states[termId]} ${marketer.title} • <a href="tel:${marketer.phone}">${marketer.phone}</a> • <a href="mailto:${marketer.email}">${marketer.email}</a>${marketer.chat}</h4><div class="team-member-read-more"><a href="${marketer.permalink}">Read testimonials from ${marketer.firstname}'s agents</a></div></div></div>`);
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