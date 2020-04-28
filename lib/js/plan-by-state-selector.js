(function($){
  // Initialize Select2
  const planSelector = $('#states').select2({
    placeholder: 'Select a state...'
  });

  // Get the DataTables saved state
  const defaultDtState = {
    "time": Date.now(),
    "start":0,
    "length":20,
    "order":[[0,"asc"]],
    "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true},
    "columns":[
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":true,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      }
    ]
  };
  const dtState = window.localStorage.getItem('DataTables_datatable_/plans/');
  let dtStateObj = {};
  if( dtState === null ){
    console.log(`🔔 dtState is null. Initializing dtState with our default object...`);
    dtStateObj = defaultDtState;
  } else {
    dtStateObj = JSON.parse(dtState);
  }

  // If we have a "State" in our saved state, pre-select that "State":
  let currentState = dtStateObj.columns[1].search.search;
  if( typeof currentState !== 'undefined' ){
    let currentStateValue = currentState + '-' + wpvars.stateOptionData[currentState]
    const planSelectorId = $(planSelector).attr('id')
    $('#' + planSelectorId ).val(currentStateValue).trigger('change')
  }

  // Listener for the "Select a State..." drop down
  $('#plan-by-state-selector').on('change','#states',function(e){
    let selection = $(this).val();
    let valueArray = selection.split('-');
    let state = valueArray[0];
    let termId = valueArray[1];

    // Set the `State`
    dtStateObj.columns[1].search.search = state;
    // Set the `Product`
    dtStateObj.columns[2].search.search = wpvars.product;
    // Update the DataTables saved state:
    window.localStorage.setItem( 'DataTables_datatable_/plans/', JSON.stringify(dtStateObj) );
  });

  // Click handler for the "View Plans" button
  $('#plan-by-state-selector').on('click','#selector-button',function(e){
    e.preventDefault();
    let savedState = dtStateObj.columns[1].search.search;
    let savedProduct = dtStateObj.columns[2].search.search;

    // If the user hasn't updated the selector and the user has "clicked"
    // "View Plans", update the saved "Product" to match the current page.
    if( '' === savedProduct || savedProduct != wpvars.product ){
      dtStateObj.columns[2].search.search = wpvars.product;
      window.localStorage.setItem('DataTables_datatable_/plans/', JSON.stringify(dtStateObj) );
      savedProduct = wpvars.product;
    }
    let selectedState = $('#states').val();

    if( '' != selectedState && '' != savedState && '' != savedProduct ){
      window.location.href = wpvars.product_finder_url;
      $('#plan-by-state-selector .notification > div').html('<i class="fas fa-info-circle"></i> Redirecting. One moment...');
    } else {
      $('#plan-by-state-selector .notification > div').html('<i class="fas fa-arrow-circle-up"></i> Please select a state.');
    }
  });
})(jQuery);